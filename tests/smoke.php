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
    'image_url' => '/uploads/avatars/test-founder.png',
    'expertise' => ['Product', 'Operations'],
    'contact_email' => 'founder@example.com',
], $adminSession);
assert_true(($founder['slug'] ?? '') === 'smoke-founder', 'founder profile slug is generated');
assert_true(($founder['image_url'] ?? '') === '/uploads/avatars/test-founder.png', 'founder profile image is stored');

$company = business_save_profile('company', [
    'name' => 'Smoke Ventures',
    'logo_url' => '/uploads/avatars/test-company.png',
    'industry' => 'Technology',
    'tagline' => 'A test business profile',
    'contact_email' => 'hello@smoke.example',
    'people' => [['personId' => $founder['id'], 'roleTitle' => 'Founder & CEO', 'isFounder' => true]],
], $adminSession);
assert_true(count($company['people'] ?? []) === 1, 'company links to an existing founder profile');
assert_true(($company['logo_url'] ?? '') === '/uploads/avatars/test-company.png', 'company profile logo is stored');
for ($index = 1; $index <= 24; $index++) {
    business_save_profile('company', [
        'name' => 'Pagination Company ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        'industry' => $index % 2 === 0 ? 'Technology' : 'Services',
        'tagline' => 'Pagination test profile',
    ], $adminSession);
}
$firstBusinessPage = business_paginated_profiles('company', ['page' => 1], null, 12);
$thirdBusinessPage = business_paginated_profiles('company', ['page' => 3], null, 12);
assert_true(
    count($firstBusinessPage['profiles']) === 12
    && ($firstBusinessPage['pagination']['total'] ?? 0) === 25
    && ($firstBusinessPage['pagination']['totalPages'] ?? 0) === 3,
    'business network returns 12 profiles with total page metadata'
);
assert_true(count($thirdBusinessPage['profiles']) === 1 && ($thirdBusinessPage['pagination']['page'] ?? 0) === 3, 'business network returns the final partial page');

$publicCompany = business_get_profile('company', $company['slug'], null);
assert_true(($publicCompany['contactLocked'] ?? false) && ($publicCompany['contact_email'] ?? '') === '', 'public company contact details stay hidden');

$memberSession = ['user' => ['id' => 'USR-BIZ-ADMIN', 'name' => 'Business Admin', 'email' => 'business-admin@example.com', 'role' => 'subscriber', 'subscription' => 'Pro']];
$memberCompany = business_get_profile('company', $company['slug'], $memberSession);
assert_true(($memberCompany['contact_email'] ?? '') === 'hello@smoke.example', 'subscribed members can access contact details');

$fieldMap = business_mcp_field_map();
assert_true(in_array('contact_email', $fieldMap['company']['fields'], true) && in_array('companies', $fieldMap['person']['fields'], true), 'MCP schema maps private contacts and founder-company relationships');
$mcpToolNames = array_column(business_mcp_tool_definitions(), 'name');
assert_true(in_array('upload_business_profile_image', $mcpToolNames, true), 'MCP exposes business profile image upload tool');
$requiredBusinessTools = [
    'get_company_profile_schema',
    'get_founder_profile_schema',
    'list_company_profiles',
    'list_founder_profiles',
    'upload_profile_image',
    'create_or_update_company_profile',
    'create_or_update_founder_profile',
    'link_founder_to_company',
];
assert_true(!array_diff($requiredBusinessTools, $mcpToolNames), 'MCP advertises dedicated company and founder profile actions');
$toolsListResponse = mcp_handle_request(['jsonrpc' => '2.0', 'id' => 7, 'method' => 'tools/list', 'params' => new stdClass()]);
$advertisedToolNames = array_column($toolsListResponse['result']['tools'] ?? [], 'name');
assert_true(!array_diff($requiredBusinessTools, $advertisedToolNames), 'MCP tools/list includes dedicated business profile actions');
$companySchema = business_mcp_call_tool('get_company_profile_schema', [], $adminSession);
$founderSchema = business_mcp_call_tool('get_founder_profile_schema', [], $adminSession);
assert_true(in_array('logo_url', $companySchema['fields'] ?? [], true) && in_array('image_url', $founderSchema['fields'] ?? [], true), 'dedicated MCP schemas expose company logo and founder photo fields');
$companyList = business_mcp_call_tool('list_company_profiles', ['q' => 'Smoke Ventures'], $adminSession);
$founderList = business_mcp_call_tool('list_founder_profiles', ['q' => 'Smoke Founder'], $adminSession);
assert_true(count($companyList['profiles'] ?? []) === 1 && count($founderList['profiles'] ?? []) === 1, 'dedicated MCP list actions find existing profiles');
$linkedProfiles = business_mcp_call_tool('link_founder_to_company', [
    'founderId' => $founder['id'],
    'companyId' => $company['id'],
    'roleTitle' => 'Board member',
    'isFounder' => false,
], $adminSession);
assert_true(($linkedProfiles['link']['companyId'] ?? '') === $company['id'], 'MCP links a founder to a company without replacing existing relationships');
$updatedCompany = business_mcp_call_tool('create_or_update_company_profile', [
    'id' => $company['id'],
    'name' => 'Smoke Ventures',
    'tagline' => 'Updated through the dedicated MCP profile action',
], $adminSession);
assert_true(($updatedCompany['profile']['tagline'] ?? '') === 'Updated through the dedicated MCP profile action', 'dedicated MCP company action updates a profile');
$updatedFounder = business_mcp_call_tool('create_or_update_founder_profile', [
    'id' => $founder['id'],
    'full_name' => 'Smoke Founder',
    'headline' => 'Founder profile updated through MCP',
], $adminSession);
assert_true(($updatedFounder['profile']['headline'] ?? '') === 'Founder profile updated through MCP', 'dedicated MCP founder action updates a profile');
$mcpImage = business_mcp_call_tool('upload_profile_image', [
    'profileType' => 'founder',
    'dataBase64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    'filename' => 'smoke-founder.png',
    'altText' => 'Smoke founder headshot',
], $adminSession);
assert_true(($mcpImage['assignToField'] ?? '') === 'image_url' && !empty($mcpImage['asset']['url']), 'MCP uploads founder image and maps it to image_url');
$mcpImagePath = $root . str_replace('/', DIRECTORY_SEPARATOR, (string) $mcpImage['asset']['url']);
if (is_file($mcpImagePath)) unlink($mcpImagePath);

echo "Smoke tests passed\n";
