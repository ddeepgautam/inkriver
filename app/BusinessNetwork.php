<?php
declare(strict_types=1);

const BUSINESS_COMPANY_JSON_FIELDS = [
    'industries' => 'industries_json',
    'products' => 'products_json',
    'technologies' => 'technologies_json',
    'markets' => 'markets_json',
    'keywords' => 'keywords_json',
    'milestones' => 'milestones_json',
];

const BUSINESS_PERSON_JSON_FIELDS = [
    'expertise' => 'expertise_json',
    'education' => 'education_json',
    'achievements' => 'achievements_json',
    'languages' => 'languages_json',
];

function business_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim(substr($value, 0, 100), '-');
}

function business_profile_config(string $type): array
{
    if ($type === 'company' || $type === 'companies') {
        return [
            'type' => 'company',
            'table' => 'business_companies',
            'prefix' => 'COM-',
            'nameColumn' => 'name',
            'jsonFields' => BUSINESS_COMPANY_JSON_FIELDS,
            'fields' => [
                'name', 'legal_name', 'tagline', 'description', 'mission', 'vision', 'founded_on',
                'industry', 'company_type', 'business_model', 'operating_status', 'funding_stage',
                'funding_total', 'employee_range', 'revenue_range', 'headquarters', 'city',
                'state_region', 'country', 'website', 'linkedin_url', 'x_url', 'facebook_url',
                'logo_url', 'cover_url', 'contact_name', 'contact_role', 'contact_email',
                'contact_phone', 'contact_address', 'status', 'verified',
            ],
        ];
    }
    if ($type === 'person' || $type === 'founder' || $type === 'people' || $type === 'founders') {
        return [
            'type' => 'person',
            'table' => 'business_people',
            'prefix' => 'PER-',
            'nameColumn' => 'full_name',
            'jsonFields' => BUSINESS_PERSON_JSON_FIELDS,
            'fields' => [
                'full_name', 'headline', 'biography', 'founder_story', 'location', 'city',
                'state_region', 'country', 'website', 'linkedin_url', 'x_url', 'image_url',
                'contact_email', 'contact_phone', 'status', 'verified',
            ],
        ];
    }
    throw new RuntimeException('Use company or person as the profile type.');
}

function business_is_staff(?array $session): bool
{
    return in_array($session['user']['role'] ?? '', ['admin', 'moderator'], true);
}

function business_has_contact_access(?array $session): bool
{
    if (!$session) return false;
    if (business_is_staff($session)) return true;
    if (($session['user']['subscription'] ?? 'Free') !== 'Free') return true;
    $stmt = Database::pdo()->prepare("SELECT 1 FROM subscriptions WHERE user_id = ? AND status = 'active' AND (ends_at IS NULL OR ends_at > ?) LIMIT 1");
    $stmt->execute([$session['user']['id'], now_iso()]);
    return (bool) $stmt->fetch();
}

function business_profile_can_manage(array $row, ?array $session): bool
{
    if (!$session) return false;
    if (business_is_staff($session)) return true;
    $userId = (string) $session['user']['id'];
    return ($row['claimed_owner_user_id'] ?? '') === $userId || ($row['created_by_user_id'] ?? '') === $userId;
}

function business_decode_row(array $row, string $type, ?array $session = null): array
{
    $config = business_profile_config($type);
    foreach ($config['jsonFields'] as $public => $column) {
        $row[$public] = parse_json_field($row[$column] ?? '[]', []);
        unset($row[$column]);
    }
    $row['verified'] = (bool) ($row['verified'] ?? false);
    $row['claimed'] = !empty($row['claimed_owner_user_id']);
    $row['canManage'] = business_profile_can_manage($row, $session);
    $row['contactLocked'] = !business_has_contact_access($session) && !$row['canManage'];
    if ($row['contactLocked']) {
        foreach (['contact_name', 'contact_role', 'contact_email', 'contact_phone', 'contact_address'] as $field) {
            if (array_key_exists($field, $row)) $row[$field] = '';
        }
    }
    unset($row['claimed_owner_user_id'], $row['created_by_user_id']);
    return $row;
}

function business_links_for_profile(string $type, string $id, ?array $session = null): array
{
    if ($type === 'company') {
        $stmt = Database::pdo()->prepare(
            "SELECT l.*, p.slug, p.full_name, p.headline, p.image_url, p.verified
             FROM business_person_company_links l
             JOIN business_people p ON p.id = l.person_id
             WHERE l.company_id = ? AND p.status = 'published'
             ORDER BY l.is_founder DESC, l.is_current DESC, p.full_name"
        );
    } else {
        $stmt = Database::pdo()->prepare(
            "SELECT l.*, c.slug, c.name, c.tagline, c.logo_url, c.industry, c.operating_status, c.verified
             FROM business_person_company_links l
             JOIN business_companies c ON c.id = l.company_id
             WHERE l.person_id = ? AND c.status = 'published'
             ORDER BY l.is_current DESC, c.name"
        );
    }
    $stmt->execute([$id]);
    return array_map(function (array $row) use ($type) {
        $row['is_founder'] = (bool) $row['is_founder'];
        $row['is_current'] = (bool) $row['is_current'];
        return $row;
    }, $stmt->fetchAll());
}

function business_get_profile(string $type, string $identifier, ?array $session = null, bool $includeUnpublished = false): ?array
{
    $config = business_profile_config($type);
    $statusSql = $includeUnpublished ? '' : " AND status = 'published'";
    $stmt = Database::pdo()->prepare("SELECT * FROM {$config['table']} WHERE (id = ? OR slug = ?){$statusSql} LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $decoded = business_decode_row($row, $config['type'], $session);
    $decoded[$config['type'] === 'company' ? 'people' : 'companies'] = business_links_for_profile($config['type'], $row['id'], $session);
    return $decoded;
}

function business_unique_slug(string $type, string $requested, string $name, ?string $excludeId = null): string
{
    $config = business_profile_config($type);
    $base = business_slug($requested !== '' ? $requested : $name);
    if ($base === '') $base = $config['type'] . '-' . substr(strtolower(uuid_value()), 0, 8);
    $candidate = $base;
    $suffix = 2;
    while (true) {
        $sql = "SELECT id FROM {$config['table']} WHERE slug = ?" . ($excludeId ? ' AND id != ?' : '') . ' LIMIT 1';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($excludeId ? [$candidate, $excludeId] : [$candidate]);
        if (!$stmt->fetch()) return $candidate;
        $candidate = $base . '-' . $suffix++;
    }
}

function business_clean_list(mixed $value): array
{
    if (is_string($value)) $value = preg_split('/[\r\n,]+/', $value) ?: [];
    if (!is_array($value)) return [];
    return array_values(array_unique(array_filter(array_map(fn($item) => trim((string) $item), $value))));
}

function business_save_profile(string $type, array $payload, array $session, ?string $identifier = null, bool $automation = false): array
{
    $config = business_profile_config($type);
    $pdo = Database::pdo();
    $existing = $identifier ? business_get_raw_profile($config['type'], $identifier) : null;
    if ($existing && !$automation && !business_profile_can_manage($existing, $session)) {
        json_response(['error' => 'FORBIDDEN', 'message' => 'You cannot manage this profile.'], 403);
    }
    $nameKey = $config['nameColumn'];
    $name = trim((string) ($payload[$nameKey] ?? ($payload[$config['type'] === 'company' ? 'companyName' : 'name'] ?? ($existing[$nameKey] ?? ''))));
    if (strlen($name) < 2) json_response(['error' => 'INVALID_NAME', 'message' => 'Enter a valid profile name.'], 400);
    $id = $existing['id'] ?? uuid_value($config['prefix']);
    $isStaff = business_is_staff($session) || $automation;
    $values = [];
    foreach ($config['fields'] as $field) {
        if (array_key_exists($field, $payload)) $values[$field] = is_bool($payload[$field]) ? (int) $payload[$field] : trim((string) $payload[$field]);
        elseif ($existing) $values[$field] = $existing[$field] ?? '';
        else $values[$field] = in_array($field, ['verified'], true) ? 0 : '';
    }
    $values[$nameKey] = $name;
    if (!$isStaff) {
        $values['status'] = $existing['status'] ?? 'draft';
        $values['verified'] = $existing['verified'] ?? 0;
    }
    if (!in_array($values['status'] ?? '', ['draft', 'published', 'unpublished'], true)) $values['status'] = 'published';
    foreach ($config['jsonFields'] as $public => $column) {
        $values[$column] = json_encode(
            array_key_exists($public, $payload) ? business_clean_list($payload[$public]) : parse_json_field($existing[$column] ?? '[]', []),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
    $slug = business_unique_slug($config['type'], trim((string) ($payload['slug'] ?? ($existing['slug'] ?? ''))), $name, $existing['id'] ?? null);
    $now = now_iso();
    if ($existing) {
        $sets = ['slug = ?'];
        $args = [$slug];
        foreach ($values as $field => $value) {
            $sets[] = "{$field} = ?";
            $args[] = $value;
        }
        $sets[] = 'updated_at = ?';
        $args[] = $now;
        if (($values['status'] ?? '') === 'published' && empty($existing['published_at'])) {
            $sets[] = 'published_at = ?';
            $args[] = $now;
        }
        $args[] = $existing['id'];
        $pdo->prepare("UPDATE {$config['table']} SET " . implode(', ', $sets) . ' WHERE id = ?')->execute($args);
    } else {
        $values['slug'] = $slug;
        $values['id'] = $id;
        $values['created_by_user_id'] = $session['user']['id'];
        $values['created_at'] = $now;
        $values['updated_at'] = $now;
        $values['published_at'] = ($values['status'] ?? '') === 'published' ? $now : null;
        $columns = array_keys($values);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $pdo->prepare("INSERT INTO {$config['table']} (" . implode(',', $columns) . ") VALUES ({$placeholders})")->execute(array_values($values));
    }
    business_sync_links($config['type'], $id, $payload);
    audit_log($session['user']['id'], $existing ? 'business.profile_updated' : 'business.profile_created', $config['type'], $id, ['source' => $automation ? 'mcp' : 'web']);
    return business_get_profile($config['type'], $id, $session, true) ?? [];
}

function business_get_raw_profile(string $type, string $identifier): ?array
{
    $config = business_profile_config($type);
    $stmt = Database::pdo()->prepare("SELECT * FROM {$config['table']} WHERE id = ? OR slug = ? LIMIT 1");
    $stmt->execute([$identifier, $identifier]);
    return $stmt->fetch() ?: null;
}

function business_sync_links(string $type, string $profileId, array $payload): void
{
    $pdo = Database::pdo();
    if ($type === 'company' && array_key_exists('people', $payload)) {
        $pdo->prepare('DELETE FROM business_person_company_links WHERE company_id = ?')->execute([$profileId]);
        foreach ((array) $payload['people'] as $link) {
            if (is_string($link)) $link = ['personId' => $link];
            $personId = trim((string) ($link['personId'] ?? $link['id'] ?? ''));
            if ($personId === '') continue;
            $pdo->prepare('INSERT OR IGNORE INTO business_person_company_links (id, person_id, company_id, role_title, is_founder, is_current, started_on, ended_on, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([uuid_value('LNK-'), $personId, $profileId, trim((string) ($link['roleTitle'] ?? 'Founder')), !empty($link['isFounder']) ? 1 : 0, array_key_exists('isCurrent', $link) ? (int) (bool) $link['isCurrent'] : 1, $link['startedOn'] ?? null, $link['endedOn'] ?? null, now_iso()]);
        }
    }
    if ($type === 'person' && array_key_exists('companies', $payload)) {
        $pdo->prepare('DELETE FROM business_person_company_links WHERE person_id = ?')->execute([$profileId]);
        foreach ((array) $payload['companies'] as $link) {
            if (is_string($link)) $link = ['companyId' => $link];
            $companyId = trim((string) ($link['companyId'] ?? $link['id'] ?? ''));
            if ($companyId === '') continue;
            $pdo->prepare('INSERT OR IGNORE INTO business_person_company_links (id, person_id, company_id, role_title, is_founder, is_current, started_on, ended_on, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([uuid_value('LNK-'), $profileId, $companyId, trim((string) ($link['roleTitle'] ?? 'Founder')), array_key_exists('isFounder', $link) ? (int) (bool) $link['isFounder'] : 1, array_key_exists('isCurrent', $link) ? (int) (bool) $link['isCurrent'] : 1, $link['startedOn'] ?? null, $link['endedOn'] ?? null, now_iso()]);
        }
    }
}

function business_notify_staff(string $title, string $body, string $url): void
{
    $stmt = Database::pdo()->query("SELECT id FROM users WHERE role IN ('admin','moderator') AND status = 'active'");
    $insert = Database::pdo()->prepare('INSERT INTO notifications (id, user_id, type, title, body, url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($stmt->fetchAll() as $user) $insert->execute([uuid_value('NOT-'), $user['id'], 'business_network', $title, $body, $url, now_iso()]);
}

function business_profile_query_filters(array $config, array $filters, ?array $session, bool $admin): array
{
    $q = trim((string) ($filters['q'] ?? ''));
    $industry = trim((string) ($filters['industry'] ?? ''));
    $status = trim((string) ($filters['status'] ?? ($admin ? '' : 'published')));
    $mine = !empty($filters['mine']) && $session;
    $where = [];
    $args = [];
    if ($status !== '') { $where[] = 'p.status = ?'; $args[] = $status; }
    if ($q !== '') {
        $where[] = $config['type'] === 'company'
            ? '(p.name LIKE ? OR p.tagline LIKE ? OR p.description LIKE ? OR p.industry LIKE ?)'
            : '(p.full_name LIKE ? OR p.headline LIKE ? OR p.biography LIKE ?)';
        $count = $config['type'] === 'company' ? 4 : 3;
        for ($i = 0; $i < $count; $i++) $args[] = '%' . $q . '%';
    }
    if ($industry !== '' && $config['type'] === 'company') { $where[] = 'p.industry = ?'; $args[] = $industry; }
    if ($mine) {
        $where[] = '(p.created_by_user_id = ? OR p.claimed_owner_user_id = ?)';
        $args[] = $session['user']['id'];
        $args[] = $session['user']['id'];
    }
    return ['where' => $where, 'args' => $args];
}

function business_list_profiles(string $type, array $filters, ?array $session = null, bool $admin = false): array
{
    $config = business_profile_config($type);
    $query = business_profile_query_filters($config, $filters, $session, $admin);
    $limit = max(1, min(200, (int) ($filters['limit'] ?? 200)));
    $offset = max(0, (int) ($filters['offset'] ?? 0));
    $whereSql = $query['where'] ? ' WHERE ' . implode(' AND ', $query['where']) : '';
    $sql = "SELECT p.* FROM {$config['table']} p{$whereSql} ORDER BY p.verified DESC, p.updated_at DESC, p.{$config['nameColumn']} ASC LIMIT {$limit} OFFSET {$offset}";
    $stmt = Database::pdo()->prepare($sql);
    $stmt->execute($query['args']);
    return array_map(fn($row) => business_decode_row($row, $config['type'], $session), $stmt->fetchAll());
}

function business_paginated_profiles(string $type, array $filters, ?array $session = null, int $perPage = 12): array
{
    $config = business_profile_config($type);
    $query = business_profile_query_filters($config, $filters, $session, false);
    $whereSql = $query['where'] ? ' WHERE ' . implode(' AND ', $query['where']) : '';
    $count = Database::pdo()->prepare("SELECT COUNT(*) FROM {$config['table']} p{$whereSql}");
    $count->execute($query['args']);
    $total = (int) $count->fetchColumn();
    $perPage = max(1, min(48, $perPage));
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($totalPages, (int) ($filters['page'] ?? 1)));
    $profiles = business_list_profiles($config['type'], array_merge($filters, [
        'limit' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ]), $session);
    return [
        'profiles' => $profiles,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ],
    ];
}

function business_admin_queue(string $kind): array
{
    $table = $kind === 'claims' ? 'business_profile_claims' : 'business_profile_suggestions';
    $stmt = Database::pdo()->query("SELECT q.*, u.name AS user_name FROM {$table} q LEFT JOIN users u ON u.id = " . ($kind === 'claims' ? 'q.claimant_user_id' : 'q.submitter_user_id') . ' ORDER BY q.created_at DESC LIMIT 300');
    return array_map(function ($row) {
        if (isset($row['proposed_changes_json'])) $row['proposed_changes'] = parse_json_field($row['proposed_changes_json'], []);
        unset($row['proposed_changes_json']);
        return $row;
    }, $stmt->fetchAll());
}

function business_admin_counts(): array
{
    $pdo = Database::pdo();
    return [
        'profiles' => (int) $pdo->query('SELECT (SELECT COUNT(*) FROM business_companies) + (SELECT COUNT(*) FROM business_people)')->fetchColumn(),
        'claims' => (int) $pdo->query("SELECT COUNT(*) FROM business_profile_claims WHERE status = 'pending'")->fetchColumn(),
        'suggestions' => (int) $pdo->query("SELECT COUNT(*) FROM business_profile_suggestions WHERE status = 'pending'")->fetchColumn(),
    ];
}

function business_admin_paginated_profiles(array $filters, int $perPage = 12): array
{
    $profileType = trim((string) ($filters['profileType'] ?? 'all'));
    if (!in_array($profileType, ['all', 'company', 'person'], true)) $profileType = 'all';
    $parts = [];
    if ($profileType !== 'person') {
        $parts[] = "SELECT id, slug, name AS display_name, tagline AS meta, industry, logo_url AS profile_image, 'company' AS profile_type, status, verified, CASE WHEN claimed_owner_user_id IS NULL OR claimed_owner_user_id = '' THEN 0 ELSE 1 END AS claimed, updated_at FROM business_companies";
    }
    if ($profileType !== 'company') {
        $parts[] = "SELECT id, slug, full_name AS display_name, headline AS meta, '' AS industry, image_url AS profile_image, 'person' AS profile_type, status, verified, CASE WHEN claimed_owner_user_id IS NULL OR claimed_owner_user_id = '' THEN 0 ELSE 1 END AS claimed, updated_at FROM business_people";
    }
    $where = [];
    $args = [];
    $q = trim((string) ($filters['q'] ?? ''));
    $industry = trim((string) ($filters['industry'] ?? ''));
    $status = trim((string) ($filters['status'] ?? ''));
    if ($q !== '') {
        $where[] = 'display_name LIKE ?';
        $args[] = '%' . $q . '%';
    }
    if ($industry !== '') {
        $where[] = 'industry = ?';
        $args[] = $industry;
    }
    if (in_array($status, ['draft', 'published', 'unpublished'], true)) {
        $where[] = 'status = ?';
        $args[] = $status;
    }
    $sourceSql = implode(' UNION ALL ', $parts);
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $count = Database::pdo()->prepare("SELECT COUNT(*) FROM ({$sourceSql}) profiles{$whereSql}");
    $count->execute($args);
    $total = (int) $count->fetchColumn();
    $perPage = max(1, min(48, $perPage));
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($totalPages, (int) ($filters['page'] ?? 1)));
    $offset = ($page - 1) * $perPage;
    $stmt = Database::pdo()->prepare("SELECT * FROM ({$sourceSql}) profiles{$whereSql} ORDER BY verified DESC, updated_at DESC, display_name ASC LIMIT {$perPage} OFFSET {$offset}");
    $stmt->execute($args);
    $profiles = array_map(function (array $profile): array {
        $profile['verified'] = (bool) $profile['verified'];
        $profile['claimed'] = (bool) $profile['claimed'];
        return $profile;
    }, $stmt->fetchAll());
    return [
        'profiles' => $profiles,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ],
    ];
}

function business_review_queue_item(string $kind, string $id, array $body, array $session): array
{
    $isClaim = $kind === 'claims';
    $table = $isClaim ? 'business_profile_claims' : 'business_profile_suggestions';
    $stmt = Database::pdo()->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) json_response(['error' => 'NOT_FOUND', 'message' => 'Review item not found.'], 404);
    $status = trim((string) ($body['status'] ?? ''));
    if (!in_array($status, ['approved', 'rejected'], true)) json_response(['error' => 'INVALID_STATUS', 'message' => 'Choose approved or rejected.'], 400);
    $now = now_iso();
    Database::pdo()->prepare("UPDATE {$table} SET status = ?, review_note = ?, reviewed_by_user_id = ?, reviewed_at = ?, updated_at = ? WHERE id = ?")
        ->execute([$status, trim((string) ($body['reviewNote'] ?? '')), $session['user']['id'], $now, $now, $id]);
    if ($status === 'approved') {
        if ($isClaim) {
            $config = business_profile_config($item['profile_type']);
            Database::pdo()->prepare("UPDATE {$config['table']} SET claimed_owner_user_id = ?, verified = 1, updated_at = ? WHERE id = ?")
                ->execute([$item['claimant_user_id'], $now, $item['profile_id']]);
        } elseif (!empty($body['applyChanges'])) {
            $profile = business_get_raw_profile($item['profile_type'], $item['profile_id']);
            if ($profile) {
                $changes = parse_json_field($item['proposed_changes_json'] ?? '{}', []);
                business_save_profile($item['profile_type'], $changes, $session, $profile['id']);
            }
        }
    }
    $email = $isClaim ? $item['claimant_email'] : $item['submitter_email'];
    send_email_message($email, 'Your ' . configured_site_name() . ' profile ' . ($isClaim ? 'claim' : 'suggestion') . ' was ' . $status, [
        'type' => 'business_profile_review',
        'status' => $status,
        'reviewNote' => trim((string) ($body['reviewNote'] ?? '')),
        'profileType' => $item['profile_type'],
    ]);
    if ($isClaim) {
        Database::pdo()->prepare('INSERT INTO notifications (id, user_id, type, title, body, url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('NOT-'), $item['claimant_user_id'], 'business_claim', 'Profile claim ' . $status, 'Your profile claim has been ' . $status . '.', '/dashboard', now_iso()]);
    }
    audit_log($session['user']['id'], 'business.' . ($isClaim ? 'claim' : 'suggestion') . '_' . $status, $item['profile_type'], $item['profile_id']);
    return ['ok' => true, 'status' => $status];
}

function handle_business_api(string $path, string $method): bool
{
    $session = current_session();
    if ($method === 'POST' && $path === '/api/business-network/media') {
        $auth = require_auth();
        $profileType = business_profile_config((string) ($_POST['profileType'] ?? 'company'))['type'];
        if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            json_response(['error' => 'NO_FILE', 'message' => 'Choose a company logo or founder photo to upload.'], 400);
        }
        $url = store_profile_avatar_upload('file');
        audit_log($auth['user']['id'], 'business.profile_image_uploaded', $profileType, null, ['url' => $url]);
        json_response(['url' => $url, 'profileType' => $profileType], 201);
    }
    if ($method === 'GET' && $path === '/api/business-network') {
        $type = (string) ($_GET['type'] ?? 'companies');
        $listing = business_paginated_profiles($type, $_GET, $session, 12);
        json_response([
            'type' => $type,
            'profiles' => $listing['profiles'],
            'pagination' => $listing['pagination'],
            'industries' => array_values(array_filter(array_column(Database::pdo()->query("SELECT DISTINCT industry FROM business_companies WHERE status = 'published' ORDER BY industry")->fetchAll(), 'industry'))),
            'contactAccess' => business_has_contact_access($session),
        ]);
    }
    if ($method === 'GET' && $path === '/api/business-network/suggest') {
        $type = (string) ($_GET['type'] ?? 'companies');
        $profiles = business_list_profiles($type, ['q' => (string) ($_GET['q'] ?? '')], $session);
        json_response(['profiles' => array_slice(array_map(fn($row) => [
            'id' => $row['id'],
            'slug' => $row['slug'],
            'name' => $row[($type === 'companies' || $type === 'company') ? 'name' : 'full_name'],
            'meta' => $row[($type === 'companies' || $type === 'company') ? 'industry' : 'headline'],
        ], $profiles), 0, 12)]);
    }
    if ($method === 'GET' && preg_match('#^/api/(companies|founders)/([^/]+)$#', $path, $m)) {
        $profile = business_get_profile($m[1] === 'companies' ? 'company' : 'person', rawurldecode($m[2]), $session);
        if (!$profile) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        json_response(['profile' => $profile, 'contactAccess' => business_has_contact_access($session)]);
    }
    if ($method === 'GET' && preg_match('#^/api/me/(companies|founders)$#', $path, $m)) {
        $auth = require_auth();
        json_response(['profiles' => business_list_profiles($m[1], ['mine' => 1, 'status' => ''], $auth, true)]);
    }
    if ($method === 'POST' && preg_match('#^/api/me/(companies|founders)$#', $path, $m)) {
        $auth = require_auth();
        json_response(['profile' => business_save_profile($m[1], read_json(), $auth)], 201);
    }
    if ($method === 'GET' && preg_match('#^/api/me/(companies|founders)/([^/]+)$#', $path, $m)) {
        $auth = require_auth();
        $type = $m[1] === 'companies' ? 'company' : 'person';
        $raw = business_get_raw_profile($type, rawurldecode($m[2]));
        if (!$raw) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        if (!business_profile_can_manage($raw, $auth)) json_response(['error' => 'FORBIDDEN', 'message' => 'You cannot manage this profile.'], 403);
        json_response(['profile' => business_get_profile($type, $raw['id'], $auth, true)]);
    }
    if (in_array($method, ['PATCH', 'DELETE'], true) && preg_match('#^/api/me/(companies|founders)/([^/]+)$#', $path, $m)) {
        $auth = require_auth();
        $type = $m[1] === 'companies' ? 'company' : 'person';
        $raw = business_get_raw_profile($type, rawurldecode($m[2]));
        if (!$raw) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        if (!business_profile_can_manage($raw, $auth)) json_response(['error' => 'FORBIDDEN', 'message' => 'You cannot manage this profile.'], 403);
        if ($method === 'DELETE') {
            $config = business_profile_config($type);
            Database::pdo()->prepare("UPDATE {$config['table']} SET status = 'unpublished', updated_at = ? WHERE id = ?")->execute([now_iso(), $raw['id']]);
            json_response(['ok' => true]);
        }
        json_response(['profile' => business_save_profile($type, read_json(), $auth, $raw['id'])]);
    }
    if ($method === 'POST' && $path === '/api/business-network/claims') {
        $auth = require_auth();
        $body = read_json();
        $type = business_profile_config((string) ($body['profileType'] ?? ''))['type'];
        $profile = business_get_raw_profile($type, (string) ($body['profileId'] ?? ''));
        if (!$profile) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        if (!empty($profile['claimed_owner_user_id'])) json_response(['error' => 'ALREADY_CLAIMED', 'message' => 'This profile is already claimed.'], 409);
        $existing = Database::pdo()->prepare("SELECT id FROM business_profile_claims WHERE profile_type = ? AND profile_id = ? AND claimant_user_id = ? AND status = 'pending'");
        $existing->execute([$type, $profile['id'], $auth['user']['id']]);
        if ($existing->fetch()) json_response(['error' => 'PENDING_CLAIM', 'message' => 'Your claim is already awaiting review.'], 409);
        $id = uuid_value('CLM-');
        $now = now_iso();
        Database::pdo()->prepare('INSERT INTO business_profile_claims (id, profile_type, profile_id, claimant_user_id, claimant_name, claimant_email, claimant_role, proof_url, evidence, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $type, $profile['id'], $auth['user']['id'], trim((string) ($body['name'] ?? $auth['user']['name'])), trim((string) ($body['email'] ?? $auth['user']['email'])), trim((string) ($body['role'] ?? '')), trim((string) ($body['proofUrl'] ?? '')), trim((string) ($body['evidence'] ?? '')), $now, $now]);
        business_notify_staff('New profile claim', $auth['user']['name'] . ' requested control of ' . $profile[business_profile_config($type)['nameColumn']] . '.', '/admin/business-network/claims');
        json_response(['id' => $id, 'status' => 'pending'], 201);
    }
    if ($method === 'POST' && $path === '/api/business-network/suggestions') {
        $body = read_json();
        $type = business_profile_config((string) ($body['profileType'] ?? ''))['type'];
        $profile = business_get_raw_profile($type, (string) ($body['profileId'] ?? ''));
        if (!$profile) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        $name = trim((string) ($body['name'] ?? ($session['user']['name'] ?? '')));
        $email = trim((string) ($body['email'] ?? ($session['user']['email'] ?? '')));
        $summary = trim((string) ($body['summary'] ?? ''));
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($summary) < 10) {
            json_response(['error' => 'INVALID_SUGGESTION', 'message' => 'Enter your name, a valid email, and a clear change summary.'], 400);
        }
        $id = uuid_value('SUG-');
        $now = now_iso();
        Database::pdo()->prepare('INSERT INTO business_profile_suggestions (id, profile_type, profile_id, submitter_user_id, submitter_name, submitter_email, summary, proposed_changes_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $type, $profile['id'], $session['user']['id'] ?? null, $name, $email, $summary, json_encode($body['proposedChanges'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $now, $now]);
        business_notify_staff('New profile correction', $name . ' suggested a change to ' . $profile[business_profile_config($type)['nameColumn']] . '.', '/admin/business-network/suggestions');
        json_response(['id' => $id, 'status' => 'pending'], 201);
    }
    if ($method === 'GET' && $path === '/api/admin/business-network') {
        require_auth(['admin', 'moderator']);
        $view = in_array((string) ($_GET['view'] ?? 'profiles'), ['profiles', 'claims', 'suggestions'], true) ? (string) $_GET['view'] : 'profiles';
        $response = ['view' => $view, 'counts' => business_admin_counts()];
        if ($view === 'profiles') {
            $listing = business_admin_paginated_profiles($_GET, 12);
            $response += [
                'profiles' => $listing['profiles'],
                'pagination' => $listing['pagination'],
                'industries' => array_values(array_filter(array_column(Database::pdo()->query("SELECT DISTINCT industry FROM business_companies WHERE industry != '' ORDER BY industry")->fetchAll(), 'industry'))),
            ];
        } elseif ($view === 'claims') {
            $response['claims'] = business_admin_queue('claims');
        } else {
            $response['suggestions'] = business_admin_queue('suggestions');
        }
        json_response($response);
    }
    if ($method === 'GET' && preg_match('#^/api/admin/business-network/(companies|founders)/([^/]+)$#', $path, $m)) {
        $auth = require_auth(['admin', 'moderator']);
        $type = $m[1] === 'companies' ? 'company' : 'person';
        $profile = business_get_profile($type, rawurldecode($m[2]), $auth, true);
        if (!$profile) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        json_response(['profile' => $profile]);
    }
    if ($method === 'PATCH' && preg_match('#^/api/admin/business-network/(companies|founders)/([^/]+)$#', $path, $m)) {
        $auth = require_auth(['admin', 'moderator']);
        json_response(['profile' => business_save_profile($m[1], read_json(), $auth, rawurldecode($m[2]))]);
    }
    if ($method === 'DELETE' && preg_match('#^/api/admin/business-network/(companies|founders)/([^/]+)$#', $path, $m)) {
        $auth = require_auth(['admin', 'moderator']);
        $config = business_profile_config($m[1]);
        $raw = business_get_raw_profile($config['type'], rawurldecode($m[2]));
        if (!$raw) json_response(['error' => 'NOT_FOUND', 'message' => 'Profile not found.'], 404);
        Database::pdo()->prepare("DELETE FROM {$config['table']} WHERE id = ?")->execute([$raw['id']]);
        audit_log($auth['user']['id'], 'business.profile_deleted', $config['type'], $raw['id']);
        json_response(['ok' => true]);
    }
    if ($method === 'PATCH' && preg_match('#^/api/admin/business-network/(claims|suggestions)/([^/]+)$#', $path, $m)) {
        $auth = require_auth(['admin', 'moderator']);
        json_response(business_review_queue_item($m[1], rawurldecode($m[2]), read_json(), $auth));
    }
    return false;
}

function business_mcp_field_map(): array
{
    return [
        'company' => [
            'required' => ['name'],
            'fields' => array_merge(
                ['id', 'slug', 'name', 'legal_name', 'tagline', 'description', 'mission', 'vision', 'founded_on', 'industry'],
                array_keys(BUSINESS_COMPANY_JSON_FIELDS),
                ['company_type', 'business_model', 'operating_status', 'funding_stage', 'funding_total', 'employee_range', 'revenue_range', 'headquarters', 'city', 'state_region', 'country', 'website', 'linkedin_url', 'x_url', 'facebook_url', 'logo_url', 'cover_url', 'contact_name', 'contact_role', 'contact_email', 'contact_phone', 'contact_address', 'status', 'verified', 'people']
            ),
            'relationshipShape' => ['personId', 'roleTitle', 'isFounder', 'isCurrent', 'startedOn', 'endedOn'],
        ],
        'person' => [
            'required' => ['full_name'],
            'fields' => array_merge(
                ['id', 'slug', 'full_name', 'headline', 'biography', 'founder_story', 'location', 'city', 'state_region', 'country', 'website', 'linkedin_url', 'x_url', 'image_url'],
                array_keys(BUSINESS_PERSON_JSON_FIELDS),
                ['contact_email', 'contact_phone', 'status', 'verified', 'companies']
            ),
            'relationshipShape' => ['companyId', 'roleTitle', 'isFounder', 'isCurrent', 'startedOn', 'endedOn'],
        ],
        'statuses' => ['draft', 'published', 'unpublished'],
        'privacy' => 'Contact fields are returned only to active subscribers, profile managers, moderators, and administrators.',
    ];
}

function business_mcp_link_founder_to_company(array $arguments, array $session): array
{
    $founderId = trim((string) ($arguments['founderId'] ?? $arguments['personId'] ?? ''));
    $companyId = trim((string) ($arguments['companyId'] ?? ''));
    $roleTitle = trim((string) ($arguments['roleTitle'] ?? 'Founder')) ?: 'Founder';
    $founder = $founderId !== '' ? business_get_raw_profile('person', $founderId) : null;
    $company = $companyId !== '' ? business_get_raw_profile('company', $companyId) : null;
    if (!$founder || !$company) throw new RuntimeException('Provide valid founderId and companyId values from list_founder_profiles and list_company_profiles.');
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT id FROM business_person_company_links WHERE person_id = ? AND company_id = ? AND role_title = ? LIMIT 1');
    $stmt->execute([$founder['id'], $company['id'], $roleTitle]);
    $existingId = (string) (($stmt->fetch()['id'] ?? ''));
    $isFounder = array_key_exists('isFounder', $arguments) ? (int) (bool) $arguments['isFounder'] : 1;
    $isCurrent = array_key_exists('isCurrent', $arguments) ? (int) (bool) $arguments['isCurrent'] : 1;
    if ($existingId !== '') {
        $pdo->prepare('UPDATE business_person_company_links SET is_founder = ?, is_current = ?, started_on = ?, ended_on = ? WHERE id = ?')
            ->execute([$isFounder, $isCurrent, $arguments['startedOn'] ?? null, $arguments['endedOn'] ?? null, $existingId]);
        $linkId = $existingId;
    } else {
        $linkId = uuid_value('LNK-');
        $pdo->prepare('INSERT INTO business_person_company_links (id, person_id, company_id, role_title, is_founder, is_current, started_on, ended_on, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$linkId, $founder['id'], $company['id'], $roleTitle, $isFounder, $isCurrent, $arguments['startedOn'] ?? null, $arguments['endedOn'] ?? null, now_iso()]);
    }
    audit_log($session['user']['id'], 'mcp.business_profiles_linked', 'business_link', $linkId, ['founderId' => $founder['id'], 'companyId' => $company['id']]);
    return [
        'link' => [
            'id' => $linkId,
            'founderId' => $founder['id'],
            'founderName' => $founder['full_name'],
            'companyId' => $company['id'],
            'companyName' => $company['name'],
            'roleTitle' => $roleTitle,
            'isFounder' => (bool) $isFounder,
            'isCurrent' => (bool) $isCurrent,
        ],
    ];
}

function business_mcp_tool_definitions(): array
{
    $companyProperties = [];
    foreach (business_mcp_field_map()['company']['fields'] as $field) {
        $companyProperties[$field] = in_array($field, array_merge(array_keys(BUSINESS_COMPANY_JSON_FIELDS), ['people']), true)
            ? ['type' => 'array', 'items' => in_array($field, ['people'], true) ? ['type' => 'object', 'additionalProperties' => true] : ['type' => 'string']]
            : ['type' => in_array($field, ['verified'], true) ? 'boolean' : 'string'];
    }
    $personProperties = [];
    foreach (business_mcp_field_map()['person']['fields'] as $field) {
        $personProperties[$field] = in_array($field, array_merge(array_keys(BUSINESS_PERSON_JSON_FIELDS), ['companies']), true)
            ? ['type' => 'array', 'items' => in_array($field, ['companies'], true) ? ['type' => 'object', 'additionalProperties' => true] : ['type' => 'string']]
            : ['type' => in_array($field, ['verified'], true) ? 'boolean' : 'string'];
    }
    $emptyInput = ['type' => 'object', 'properties' => new stdClass()];
    $listProperties = [
        'q' => ['type' => 'string'],
        'status' => ['type' => 'string', 'enum' => ['', 'draft', 'published', 'unpublished']],
    ];
    $imageProperties = [
        'profileType' => ['type' => 'string', 'enum' => ['company', 'founder']],
        'sourceUrl' => ['type' => 'string', 'description' => 'HTTPS URL of the image to import.'],
        'dataBase64' => ['type' => 'string', 'description' => 'Base64-encoded JPG, PNG, WebP, or GIF data.'],
        'filename' => ['type' => 'string'],
        'altText' => ['type' => 'string'],
    ];
    $tools = [
        [
            'name' => 'get_company_profile_schema',
            'description' => 'Return every supported company profile field, relationship shape, profile status, logo field, and contact privacy rule.',
            'inputSchema' => $emptyInput,
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'get_founder_profile_schema',
            'description' => 'Return every supported founder profile field, relationship shape, profile status, photo field, and contact privacy rule.',
            'inputSchema' => $emptyInput,
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'list_company_profiles',
            'description' => 'Find existing company profiles and ids before creating, updating, or linking a company.',
            'inputSchema' => ['type' => 'object', 'properties' => $listProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'list_founder_profiles',
            'description' => 'Find existing founder profiles and ids before creating, updating, or linking a founder.',
            'inputSchema' => ['type' => 'object', 'properties' => $listProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'upload_profile_image',
            'description' => 'Upload a company logo or founder headshot from a URL or base64 image. The response identifies whether asset.url belongs in logo_url or image_url.',
            'inputSchema' => ['type' => 'object', 'required' => ['profileType'], 'properties' => $imageProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'create_or_update_company_profile',
            'description' => 'Create a new company profile or update one by id/slug, including logo, company details, private contacts, status, founders, and key people.',
            'inputSchema' => ['type' => 'object', 'required' => ['name'], 'properties' => $companyProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'create_or_update_founder_profile',
            'description' => 'Create a new founder profile or update one by id/slug, including photo, biography, designation, location, social links, achievements, companies, and status.',
            'inputSchema' => ['type' => 'object', 'required' => ['full_name'], 'properties' => $personProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'link_founder_to_company',
            'description' => 'Link an existing founder profile to an existing company profile without replacing either profile’s other relationships.',
            'inputSchema' => ['type' => 'object', 'required' => ['founderId', 'companyId'], 'properties' => [
                'founderId' => ['type' => 'string'],
                'companyId' => ['type' => 'string'],
                'roleTitle' => ['type' => 'string'],
                'isFounder' => ['type' => 'boolean'],
                'isCurrent' => ['type' => 'boolean'],
                'startedOn' => ['type' => 'string'],
                'endedOn' => ['type' => 'string'],
            ]],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'get_business_profile_schema',
            'description' => 'Compatibility action returning the combined company and founder profile schema.',
            'inputSchema' => $emptyInput,
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'list_business_profiles',
            'description' => 'List existing companies or founders before linking or updating a profile.',
            'inputSchema' => ['type' => 'object', 'properties' => [
                'type' => ['type' => 'string', 'enum' => ['companies', 'founders']],
                'q' => ['type' => 'string'],
                'status' => ['type' => 'string', 'enum' => ['', 'draft', 'published', 'unpublished']],
            ]],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'upload_business_profile_image',
            'description' => 'Compatibility alias for upload_profile_image.',
            'inputSchema' => ['type' => 'object', 'required' => ['profileType'], 'properties' => $imageProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'create_or_update_company',
            'description' => 'Create or update a complete company profile and link founders/key people by their profile ids.',
            'inputSchema' => ['type' => 'object', 'required' => ['name'], 'properties' => $companyProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
        [
            'name' => 'create_or_update_founder',
            'description' => 'Create or update a complete founder profile and link companies by their profile ids.',
            'inputSchema' => ['type' => 'object', 'required' => ['full_name'], 'properties' => $personProperties],
            'outputSchema' => ['type' => 'object', 'additionalProperties' => true],
        ],
    ];
    return $tools;
}

function business_mcp_call_tool(string $name, array $arguments, array $session): ?array
{
    if ($name === 'get_company_profile_schema') return ['profileType' => 'company'] + business_mcp_field_map()['company'] + ['statuses' => business_mcp_field_map()['statuses'], 'privacy' => business_mcp_field_map()['privacy']];
    if ($name === 'get_founder_profile_schema') return ['profileType' => 'founder'] + business_mcp_field_map()['person'] + ['statuses' => business_mcp_field_map()['statuses'], 'privacy' => business_mcp_field_map()['privacy']];
    if ($name === 'get_business_profile_schema') return business_mcp_field_map();
    if ($name === 'list_company_profiles') return ['profiles' => business_list_profiles('company', $arguments, $session, true)];
    if ($name === 'list_founder_profiles') return ['profiles' => business_list_profiles('person', $arguments, $session, true)];
    if ($name === 'list_business_profiles') {
        $type = (string) ($arguments['type'] ?? 'companies');
        return ['profiles' => business_list_profiles($type, $arguments, $session, true)];
    }
    if (in_array($name, ['upload_profile_image', 'upload_business_profile_image'], true)) {
        $profileType = (string) ($arguments['profileType'] ?? 'company');
        if (!in_array($profileType, ['company', 'founder'], true)) throw new RuntimeException('profileType must be company or founder.');
        return [
            'asset' => store_mcp_image_asset($arguments, $session['user']['id']),
            'assignToField' => $profileType === 'company' ? 'logo_url' : 'image_url',
            'profileType' => $profileType,
        ];
    }
    if (in_array($name, ['create_or_update_company_profile', 'create_or_update_company'], true)) {
        $identifier = trim((string) ($arguments['id'] ?? $arguments['slug'] ?? ''));
        return ['profile' => business_save_profile('company', $arguments, $session, $identifier !== '' ? $identifier : null, true)];
    }
    if (in_array($name, ['create_or_update_founder_profile', 'create_or_update_founder'], true)) {
        $identifier = trim((string) ($arguments['id'] ?? $arguments['slug'] ?? ''));
        return ['profile' => business_save_profile('person', $arguments, $session, $identifier !== '' ? $identifier : null, true)];
    }
    if ($name === 'link_founder_to_company') return business_mcp_link_founder_to_company($arguments, $session);
    return null;
}
