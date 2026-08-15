<?php
declare(strict_types=1);

const CAPABILITY_PAID_ARTICLES = 'content.paid_articles.read';
const CAPABILITY_BUSINESS_CONTACTS = 'business.contacts.reveal';
const CAPABILITY_INCLUDED_RESOURCES = 'resources.included.access';

function entitlement_catalog_bootstrap(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = Database::pdo();
    $now = now_iso();
    $capabilities = [
        [CAPABILITY_PAID_ARTICLES, 'Paid articles', 'quota', 'Read distinct paid articles.'],
        [CAPABILITY_BUSINESS_CONTACTS, 'Business contact reveals', 'quota', 'Reveal contact details for distinct business profiles.'],
        [CAPABILITY_INCLUDED_RESOURCES, 'Included resources', 'scope', 'Access resources included with a subscription.'],
    ];
    foreach ($capabilities as $row) {
        $pdo->prepare('INSERT OR IGNORE INTO capabilities (key, name, value_type, description, created_at) VALUES (?, ?, ?, ?, ?)')->execute([...$row, $now]);
    }
    foreach (['reader' => 'Reader', 'writer' => 'Writer', 'moderator' => 'Moderator', 'admin' => 'Administrator'] as $key => $name) {
        $pdo->prepare('INSERT OR IGNORE INTO roles (key, name, created_at) VALUES (?, ?, ?)')->execute([$key, $name, $now]);
    }
    foreach (['admin', 'moderator'] as $role) {
        $pdo->prepare("INSERT OR IGNORE INTO role_capabilities (role_key, capability_key, mode, created_at) VALUES (?, ?, 'unlimited', ?)")
            ->execute([$role, CAPABILITY_BUSINESS_CONTACTS, $now]);
    }
    $pdo->prepare("INSERT OR IGNORE INTO role_capabilities (role_key, capability_key, mode, created_at) VALUES ('admin', ?, 'unlimited', ?)")
        ->execute([CAPABILITY_PAID_ARTICLES, $now]);
}

function entitlement_default_plan_capabilities(string $planId): array
{
    $contactLimits = ['starter' => 5, 'annual' => 25, 'patron' => 100];
    $resourceScope = $planId === 'patron' ? ['kind' => 'all_eligible'] : ['kind' => 'selected', 'resourceIds' => []];
    return [
        CAPABILITY_PAID_ARTICLES => ['mode' => 'unlimited', 'limit' => null, 'period' => 'subscription_cycle', 'scope' => []],
        CAPABILITY_BUSINESS_CONTACTS => ['mode' => 'quota', 'limit' => $contactLimits[$planId] ?? 0, 'period' => 'month', 'scope' => []],
        CAPABILITY_INCLUDED_RESOURCES => ['mode' => $planId === 'patron' ? 'allowed' : 'denied', 'limit' => null, 'period' => 'subscription_cycle', 'scope' => $resourceScope],
    ];
}

function entitlement_normalize_plan_capabilities(array $plan): array
{
    $defaults = entitlement_default_plan_capabilities((string) ($plan['id'] ?? ''));
    $configured = is_array($plan['capabilities'] ?? null) ? $plan['capabilities'] : [];
    $result = [];
    foreach ($defaults as $key => $fallback) {
        $value = is_array($configured[$key] ?? null) ? $configured[$key] : $fallback;
        $mode = in_array(($value['mode'] ?? ''), ['denied', 'allowed', 'quota', 'unlimited'], true) ? $value['mode'] : $fallback['mode'];
        $limit = $mode === 'quota' ? max(0, (int) ($value['limit'] ?? $fallback['limit'] ?? 0)) : null;
        if ($mode === 'quota' && $limit === 0) $mode = 'denied';
        $period = in_array(($value['period'] ?? ''), ['subscription_cycle', 'month', 'year', 'lifetime'], true) ? $value['period'] : $fallback['period'];
        $scope = is_array($value['scope'] ?? null) ? $value['scope'] : $fallback['scope'];
        $result[$key] = compact('mode', 'limit', 'period', 'scope');
    }
    ksort($result);
    return $result;
}

function entitlement_sync_plans(array $plans, ?string $actorId = null): void
{
    entitlement_catalog_bootstrap();
    $pdo = Database::pdo();
    $now = now_iso();
    foreach ($plans as $plan) {
        if (!is_array($plan)) continue;
        $id = strtolower(trim((string) ($plan['id'] ?? '')));
        $name = trim((string) ($plan['name'] ?? ''));
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,79}$/', $id) || $name === '') continue;
        $price = max(1, (int) round((float) ($plan['price'] ?? 0)));
        $period = in_array(($plan['period'] ?? ''), ['month', 'quarter', 'year'], true) ? $plan['period'] : 'month';
        $note = substr(trim((string) ($plan['note'] ?? '')), 0, 300);
        $features = array_values(array_filter(array_map('strval', is_array($plan['features'] ?? null) ? $plan['features'] : [])));
        $caps = entitlement_normalize_plan_capabilities($plan);
        $pdo->prepare("INSERT INTO subscription_plans (id, name, status, created_at, updated_at) VALUES (?, ?, 'active', ?, ?) ON CONFLICT(id) DO UPDATE SET name = excluded.name, status = 'active', updated_at = excluded.updated_at")
            ->execute([$id, substr($name, 0, 120), $now, $now]);
        $latest = $pdo->prepare("SELECT * FROM subscription_plan_versions WHERE plan_id = ? AND status = 'published' ORDER BY version DESC LIMIT 1");
        $latest->execute([$id]);
        $row = $latest->fetch();
        $same = $row && (int) $row['price'] === $price && $row['billing_period'] === $period && $row['note'] === $note
            && parse_json_field($row['features_json'], []) === $features;
        if ($same) {
            $existingCaps = $pdo->prepare('SELECT capability_key, mode, limit_value, reset_period, scope_json FROM plan_capabilities WHERE plan_version_id = ? ORDER BY capability_key');
            $existingCaps->execute([$row['id']]);
            $existing = [];
            foreach ($existingCaps->fetchAll() as $cap) $existing[$cap['capability_key']] = ['mode' => $cap['mode'], 'limit' => $cap['limit_value'] === null ? null : (int) $cap['limit_value'], 'period' => $cap['reset_period'], 'scope' => parse_json_field($cap['scope_json'], [])];
            $same = $existing === $caps;
        }
        if ($same) {
            $pdo->prepare('UPDATE subscriptions SET plan_version_id = ? WHERE plan_id = ? AND plan_version_id IS NULL')->execute([$row['id'], $id]);
            continue;
        }
        $version = $row ? (int) $row['version'] + 1 : 1;
        $versionId = 'PV-' . $id . '-' . $version;
        $pdo->prepare("INSERT OR IGNORE INTO subscription_plan_versions (id, plan_id, version, price, currency, billing_period, note, features_json, status, effective_at, created_by, created_at) VALUES (?, ?, ?, ?, 'INR', ?, ?, ?, 'published', ?, ?, ?)")
            ->execute([$versionId, $id, $version, $price, $period, $note, json_encode($features, JSON_UNESCAPED_SLASHES), $now, $actorId, $now]);
        foreach ($caps as $key => $cap) {
            $pdo->prepare('INSERT INTO plan_capabilities (plan_version_id, capability_key, mode, limit_value, reset_period, scope_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(plan_version_id, capability_key) DO UPDATE SET mode = excluded.mode, limit_value = excluded.limit_value, reset_period = excluded.reset_period, scope_json = excluded.scope_json, updated_at = excluded.updated_at')
                ->execute([$versionId, $key, $cap['mode'], $cap['limit'], $cap['period'], json_encode($cap['scope'], JSON_UNESCAPED_SLASHES), $now, $now]);
        }
        $pdo->prepare('UPDATE subscriptions SET plan_version_id = ? WHERE plan_id = ? AND plan_version_id IS NULL')->execute([$versionId, $id]);
    }
}

function entitlement_active_subscription(string $userId): ?array
{
    entitlement_catalog_bootstrap();
    $now = now_iso();
    $stmt = Database::pdo()->prepare("SELECT s.*, COALESCE(s.current_period_start, s.starts_at, s.created_at) AS period_start, COALESCE(s.current_period_end, s.ends_at) AS period_end FROM subscriptions s WHERE s.user_id = ? AND (s.status = 'active' OR (s.status = 'past_due' AND s.grace_ends_at > ?)) AND (COALESCE(s.current_period_end, s.ends_at) IS NULL OR COALESCE(s.current_period_end, s.ends_at) > ?) ORDER BY s.created_at DESC LIMIT 1");
    $stmt->execute([$userId, $now, $now]);
    return $stmt->fetch() ?: null;
}

function entitlement_plan_version(array $subscription): ?array
{
    $pdo = Database::pdo();
    if (!empty($subscription['plan_version_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM subscription_plan_versions WHERE id = ? AND status != 'retired'");
        $stmt->execute([$subscription['plan_version_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM subscription_plan_versions WHERE plan_id = ? AND status = 'published' ORDER BY version DESC LIMIT 1");
        $stmt->execute([$subscription['plan_id']]);
    }
    return $stmt->fetch() ?: null;
}

function entitlement_period(array $grant, ?array $subscription): array
{
    $period = (string) ($grant['reset_period'] ?? 'subscription_cycle');
    $now = time();
    if ($period === 'month') return [gmdate('Y-m-01\T00:00:00.000\Z', $now), gmdate('Y-m-01\T00:00:00.000\Z', strtotime('first day of next month', $now))];
    if ($period === 'year') return [gmdate('Y-01-01\T00:00:00.000\Z', $now), gmdate('Y-01-01\T00:00:00.000\Z', strtotime('+1 year', strtotime(gmdate('Y-01-01'), $now)))];
    if ($period === 'lifetime') return ['1970-01-01T00:00:00.000Z', '9999-12-31T23:59:59.999Z'];
    return [(string) ($subscription['period_start'] ?? now_iso()), (string) ($subscription['period_end'] ?? '9999-12-31T23:59:59.999Z')];
}

function entitlement_decision(array $session, string $capabilityKey): array
{
    entitlement_catalog_bootstrap();
    $user = $session['user'] ?? [];
    if (($user['status'] ?? 'active') !== 'active') return ['allowed' => false, 'reason' => 'ACCOUNT_INACTIVE'];
    $pdo = Database::pdo();
    $now = now_iso();
    $override = $pdo->prepare("SELECT * FROM user_capability_overrides WHERE user_id = ? AND capability_key = ? AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at > ?) ORDER BY created_at DESC LIMIT 1");
    $override->execute([$user['id'], $capabilityKey, $now, $now]);
    if ($grant = $override->fetch()) return entitlement_format_decision($grant, null, 'user_override');
    $roles = array_unique(array_filter([(string) ($user['role'] ?? '')]));
    $roleStmt = $pdo->prepare('SELECT role_key FROM user_roles WHERE user_id = ?');
    $roleStmt->execute([$user['id']]);
    $roles = array_unique(array_merge($roles, array_column($roleStmt->fetchAll(), 'role_key')));
    if ($roles) {
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $pdo->prepare("SELECT * FROM role_capabilities WHERE capability_key = ? AND role_key IN ($placeholders) ORDER BY CASE mode WHEN 'denied' THEN 0 WHEN 'unlimited' THEN 1 ELSE 2 END LIMIT 1");
        $stmt->execute(array_merge([$capabilityKey], $roles));
        if ($grant = $stmt->fetch()) return entitlement_format_decision($grant, null, 'role');
    }
    $subscription = entitlement_active_subscription((string) $user['id']);
    if (!$subscription) return ['allowed' => false, 'reason' => 'SUBSCRIPTION_REQUIRED'];
    $version = entitlement_plan_version($subscription);
    if (!$version) return ['allowed' => false, 'reason' => 'PLAN_CONFIGURATION_MISSING'];
    $stmt = $pdo->prepare('SELECT * FROM plan_capabilities WHERE plan_version_id = ? AND capability_key = ?');
    $stmt->execute([$version['id'], $capabilityKey]);
    $grant = $stmt->fetch();
    if (!$grant) return ['allowed' => false, 'reason' => 'CAPABILITY_NOT_INCLUDED'];
    return entitlement_format_decision($grant, $subscription, 'subscription', $version);
}

function entitlement_format_decision(array $grant, ?array $subscription, string $source, ?array $version = null): array
{
    $mode = (string) ($grant['mode'] ?? 'denied');
    [$periodStart, $periodEnd] = entitlement_period($grant, $subscription);
    $limit = $grant['limit_value'] === null ? null : (int) $grant['limit_value'];
    return [
        'allowed' => in_array($mode, ['allowed', 'quota', 'unlimited'], true), 'reason' => $mode === 'denied' ? 'CAPABILITY_NOT_INCLUDED' : 'ALLOWED',
        'mode' => $mode, 'limit' => $limit, 'scope' => parse_json_field($grant['scope_json'] ?? '{}', []),
        'periodStart' => $periodStart, 'periodEnd' => $periodEnd, 'source' => $source,
        'subscription' => $subscription, 'planVersion' => $version,
    ];
}

function entitlement_subject_used(array $decision, string $userId, string $capabilityKey, string $subjectType, string $subjectId): bool
{
    $stmt = Database::pdo()->prepare('SELECT 1 FROM entitlement_usage_events WHERE user_id = ? AND capability_key = ? AND subject_type = ? AND subject_id = ? AND period_start = ? LIMIT 1');
    $stmt->execute([$userId, $capabilityKey, $subjectType, $subjectId, $decision['periodStart']]);
    return (bool) $stmt->fetchColumn();
}

function entitlement_consume(array $session, string $capabilityKey, string $subjectType, string $subjectId): array
{
    $decision = entitlement_decision($session, $capabilityKey);
    if (empty($decision['allowed'])) return $decision;
    if (($decision['mode'] ?? '') !== 'quota') return array_merge($decision, ['used' => 0, 'remaining' => null]);
    $pdo = Database::pdo();
    $userId = (string) $session['user']['id'];
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->exec('BEGIN IMMEDIATE');
    try {
        if (!entitlement_subject_used($decision, $userId, $capabilityKey, $subjectType, $subjectId)) {
            $count = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM entitlement_usage_events WHERE user_id = ? AND capability_key = ? AND period_start = ?');
            $count->execute([$userId, $capabilityKey, $decision['periodStart']]);
            if ((int) $count->fetchColumn() >= (int) $decision['limit']) {
                if ($ownsTransaction) $pdo->exec('ROLLBACK');
                return array_merge($decision, ['allowed' => false, 'reason' => 'QUOTA_EXHAUSTED', 'remaining' => 0]);
            }
            $key = hash('sha256', implode('|', [$userId, $capabilityKey, $subjectType, $subjectId, $decision['periodStart']]));
            $pdo->prepare('INSERT OR IGNORE INTO entitlement_usage_events (id, user_id, subscription_id, capability_key, subject_type, subject_id, quantity, period_start, period_end, idempotency_key, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?)')
                ->execute([uuid_value('USE-'), $userId, $decision['subscription']['id'] ?? null, $capabilityKey, $subjectType, $subjectId, $decision['periodStart'], $decision['periodEnd'], $key, now_iso()]);
        }
        $count = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM entitlement_usage_events WHERE user_id = ? AND capability_key = ? AND period_start = ?');
        $count->execute([$userId, $capabilityKey, $decision['periodStart']]);
        $used = (int) $count->fetchColumn();
        if ($ownsTransaction) $pdo->exec('COMMIT');
        return array_merge($decision, ['used' => $used, 'remaining' => max(0, (int) $decision['limit'] - $used)]);
    } catch (Throwable $error) {
        if ($ownsTransaction) {
            try { $pdo->exec('ROLLBACK'); } catch (Throwable) {}
        }
        throw $error;
    }
}

function entitlement_resource_scope_allows(array $decision, array $resource): bool
{
    if (empty($decision['allowed'])) return false;
    $scope = is_array($decision['scope'] ?? null) ? $decision['scope'] : [];
    $kind = (string) ($scope['kind'] ?? 'selected');
    if ($kind === 'all_published') return ($resource['status'] ?? '') === 'published';
    if ($kind === 'all_eligible') return !empty($resource['subscription_eligible']);
    if ($kind === 'categories') return in_array((string) ($resource['category'] ?? ''), $scope['categories'] ?? [], true);
    return in_array((string) ($resource['id'] ?? ''), $scope['resourceIds'] ?? [], true);
}

function entitlement_story_payload(array $story, ?array $session): array
{
    if (empty($story['premium'])) return array_merge($story, ['accessLocked' => false]);
    $allowed = false;
    if ($session) {
        $decision = entitlement_decision($session, CAPABILITY_PAID_ARTICLES);
        $allowed = !empty($decision['allowed']) && (($decision['mode'] ?? '') !== 'quota'
            || entitlement_subject_used($decision, (string) $session['user']['id'], CAPABILITY_PAID_ARTICLES, 'story', (string) ($story['slug'] ?? '')));
    }
    if ($allowed) return array_merge($story, ['accessLocked' => false]);
    $story['contentHtml'] = '';
    $story['body'] = [];
    $story['interactiveBlocks'] = [];
    $story['accessLocked'] = true;
    return $story;
}

function entitlement_usage_summary(array $session): array
{
    $summary = [];
    foreach ([CAPABILITY_PAID_ARTICLES, CAPABILITY_BUSINESS_CONTACTS, CAPABILITY_INCLUDED_RESOURCES] as $key) {
        $decision = entitlement_decision($session, $key);
        $used = 0;
        if (!empty($decision['allowed']) && ($decision['mode'] ?? '') === 'quota') {
            $stmt = Database::pdo()->prepare('SELECT COALESCE(SUM(quantity), 0) FROM entitlement_usage_events WHERE user_id = ? AND capability_key = ? AND period_start = ?');
            $stmt->execute([$session['user']['id'], $key, $decision['periodStart']]);
            $used = (int) $stmt->fetchColumn();
        }
        $summary[$key] = array_merge($decision, ['used' => $used, 'remaining' => ($decision['mode'] ?? '') === 'quota' ? max(0, (int) $decision['limit'] - $used) : null]);
        unset($summary[$key]['subscription']);
    }
    return $summary;
}
