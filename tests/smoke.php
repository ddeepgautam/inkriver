<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$db = $root . '/data/test-smoke.sqlite';
if (is_file($db)) unlink($db);
putenv('DATABASE_PATH=' . $db);
putenv('APP_ENV=production');

require_once $root . '/app/Api.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$pdo = Database::pdo();

$columns = array_column($pdo->query('PRAGMA table_info(feature_flags)')->fetchAll(), 'name');
assert_true(in_array('environment', $columns, true), 'feature_flags.environment column exists');

$historyColumns = array_column($pdo->query('PRAGMA table_info(feature_flag_history)')->fetchAll(), 'name');
assert_true(in_array('environment', $historyColumns, true), 'feature_flag_history.environment column exists');

$suppressionTable = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'newsletter_suppressions'")->fetch();
assert_true((bool) $suppressionTable, 'newsletter_suppressions table exists');

$discountColumns = array_column($pdo->query('PRAGMA table_info(discount_codes)')->fetchAll(), 'name');
assert_true(in_array('deleted_at', $discountColumns, true), 'discount codes support non-destructive deletion');

$csv = "title,slug,contentHtml\nSmoke Story,smoke-story,<p>Hello</p>\n";
$items = imported_story_items('csv', $csv);
assert_true(count($items) === 1 && $items[0]['slug'] === 'smoke-story', 'CSV importer parses story rows');

$wp = '<?xml version="1.0"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"><channel><item><title>RSS Story</title><content:encoded><![CDATA[<p>Body</p>]]></content:encoded></item></channel></rss>';
$rssItems = imported_story_items('wordpress', $wp);
assert_true(count($rssItems) === 1 && $rssItems[0]['title'] === 'RSS Story', 'WordPress/RSS importer parses item rows');

$pdo->prepare("INSERT INTO feature_flags (key, enabled, rollout_percent, roles_json, environment, description, updated_at) VALUES ('smoke_feature', 1, 100, '[]', 'production', 'Smoke', ?)")
    ->execute([now_iso()]);
assert_true(feature_flag_enabled('smoke_feature', false), 'production feature flag evaluates on');

$pdo->prepare("INSERT INTO feature_flags (key, enabled, rollout_percent, roles_json, environment, description, updated_at) VALUES ('staging_only', 1, 100, '[]', 'staging', 'Smoke', ?)")
    ->execute([now_iso()]);
assert_true(!feature_flag_enabled('staging_only', false), 'environment-scoped feature flag stays off');

$now = now_iso();
$pdo->prepare("INSERT INTO discount_codes (id, code, description, discount_type, discount_value, audience, active, created_at, updated_at) VALUES ('DISC-SMOKE', 'SMOKE20', 'Smoke discount', 'percent', 20, 'All readers', 1, ?, ?)")
    ->execute([$now, $now]);
assert_true(payment_amount_for_checkout(['amount' => 10000], ['discountCode' => 'SMOKE20']) === 8000, 'active percentage coupon changes checkout amount');
$pdo->prepare("UPDATE discount_codes SET deleted_at = ?, active = 0 WHERE id = 'DISC-SMOKE'")->execute([$now]);
assert_true(payment_amount_for_checkout(['amount' => 10000], ['discountCode' => 'SMOKE20']) === 10000, 'deleted coupon no longer changes checkout amount');

$jobId = enqueue_background_job('payouts.execute', ['limit' => 1]);
assert_true(str_starts_with($jobId, 'JOB-'), 'payout execution job can be queued');

$pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, subscription, status, email_verified, created_at, updated_at) VALUES ('USR-BIZ-ADMIN', 'Business Admin', 'business-admin@example.com', ?, 'admin', 'Pro', 'active', 1, ?, ?)")
    ->execute([hash_password_value('SmokePassword!23'), $now, $now]);
$adminSession = ['user' => ['id' => 'USR-BIZ-ADMIN', 'name' => 'Business Admin', 'email' => 'business-admin@example.com', 'role' => 'admin', 'subscription' => 'Pro']];
$founder = business_save_profile('person', [
    'full_name' => 'Smoke Founder',
    'headline' => 'Founder and builder',
    'expertise' => ['Product', 'Operations'],
    'contact_email' => 'founder@example.com',
], $adminSession);
assert_true(($founder['slug'] ?? '') === 'smoke-founder', 'founder profile slug is generated');

$company = business_save_profile('company', [
    'name' => 'Smoke Ventures',
    'industry' => 'Technology',
    'tagline' => 'A test business profile',
    'contact_email' => 'hello@smoke.example',
    'people' => [['personId' => $founder['id'], 'roleTitle' => 'Founder & CEO', 'isFounder' => true]],
], $adminSession);
assert_true(count($company['people'] ?? []) === 1, 'company links to an existing founder profile');

$publicCompany = business_get_profile('company', $company['slug'], null);
assert_true(($publicCompany['contactLocked'] ?? false) && ($publicCompany['contact_email'] ?? '') === '', 'public company contact details stay hidden');

$memberSession = ['user' => ['id' => 'USR-BIZ-ADMIN', 'name' => 'Business Admin', 'email' => 'business-admin@example.com', 'role' => 'subscriber', 'subscription' => 'Pro']];
$memberCompany = business_get_profile('company', $company['slug'], $memberSession);
assert_true(($memberCompany['contact_email'] ?? '') === 'hello@smoke.example', 'subscribed members can access contact details');

$fieldMap = business_mcp_field_map();
assert_true(in_array('contact_email', $fieldMap['company']['fields'], true) && in_array('companies', $fieldMap['person']['fields'], true), 'MCP schema maps private contacts and founder-company relationships');

echo "Smoke tests passed\n";
