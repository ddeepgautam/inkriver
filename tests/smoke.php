<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$db = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'inkriver-test-smoke.sqlite';
if (is_file($db)) unlink($db);
putenv('DATABASE_PATH=' . $db);
putenv('APP_ENV=production');
putenv('APP_ORIGIN=https://inkriver.test');
putenv('APP_SECRET=smoke-test-secret-that-is-at-least-32-characters');

require_once $root . '/app/Api.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$pdo = Database::pdo();

assert_true(public_root() === $root . DIRECTORY_SEPARATOR . 'public', 'public document root is isolated from application source');
$publicTraversalRejected = false;
try {
    public_path('../app/config.php');
} catch (InvalidArgumentException) {
    $publicTraversalRejected = true;
}
assert_true($publicTraversalRejected, 'public path resolver rejects directory traversal');
assert_true(deployment_current_status(false)['enabled'], 'Git updater uses git rev-parse instead of requiring a .git directory');

$sanitized = sanitize_story_html('<p onclick="alert(1)">Safe <a href="javascript:alert(2)">link</a><img src="/uploads/example.jpg" onerror="alert(3)"></p><script>alert(4)</script>');
assert_true(!str_contains($sanitized, 'onclick') && !str_contains($sanitized, 'onerror') && !str_contains($sanitized, 'javascript:') && !str_contains($sanitized, '<script'), 'server rich HTML sanitizer removes executable markup');

$trustedCheckout = authoritative_payment_checkout([
    'amount' => 1,
    'currency' => 'INR',
    'metadata' => ['planId' => 'starter', 'planName' => 'Attacker Plan', 'period' => 'year'],
]);
assert_true(
    ($trustedCheckout['payment']['amount'] ?? 0) === 29900
    && ($trustedCheckout['metadata']['planName'] ?? '') === 'Reader'
    && ($trustedCheckout['metadata']['period'] ?? '') === 'month',
    'membership checkout ignores client-controlled amount, name, and duration'
);

record_auth_rate_limit_failure('smoke-login', 'smoke@example.com', 5, 900, 900);
$rateLimitKey = auth_rate_limit_key('smoke-login', 'smoke@example.com');
$rateLimitStmt = $pdo->prepare('SELECT attempts FROM login_attempts WHERE key = ?');
$rateLimitStmt->execute([$rateLimitKey]);
assert_true((int) $rateLimitStmt->fetchColumn() === 1, 'authentication failures persist rate-limit state');
clear_auth_rate_limit('smoke-login', 'smoke@example.com');

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
$pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, subscription, status, email_verified, created_at, updated_at) VALUES ('USR-SMOKE-WRITER', 'Smoke Writer', 'smoke-writer@example.com', ?, 'writer', 'Pro', 'active', 1, ?, ?)")
    ->execute([hash_password_value('SmokePassword!24'), $now, $now]);
$writerSession = ['user' => ['id' => 'USR-SMOKE-WRITER', 'name' => 'Smoke Writer', 'email' => 'smoke-writer@example.com', 'role' => 'writer', 'subscription' => 'Pro']];
$resourceTables = array_column($pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name LIKE 'resource_%'")->fetchAll(), 'name');
assert_true(in_array('resource_entitlements', $resourceTables, true) && in_array('resource_access_tokens', $resourceTables, true) && in_array('resource_access_logs', $resourceTables, true), 'resource marketplace creates entitlement, temporary-token, and access-log tables');
$pdo->prepare("INSERT INTO resources (id, slug, name, short_description, description, category, resource_type, access_kind, version, price_type, regular_price, discounted_price, currency, protected_storage_key, original_filename, mime_type, file_size, status, created_by_user_id, created_at, updated_at, published_at) VALUES ('RES-SMOKE-PAID', 'secure-resource', 'Secure Resource', 'Protected resource', 'A protected smoke resource.', 'Business', 'pdf', 'download', '1.0', 'paid', 10000, 7500, 'INR', 'resources/private-random.bin', 'friendly-name.pdf', 'application/pdf', 1234, 'published', 'USR-BIZ-ADMIN', ?, ?, ?)")
    ->execute([$now, $now, $now]);
$resourceRow = resource_find_by_slug_or_id('secure-resource');
$resourceCheckout = authoritative_payment_checkout(['amount' => 1, 'currency' => 'INR', 'metadata' => ['kind' => 'resource', 'resourceId' => 'RES-SMOKE-PAID']]);
assert_true(($resourceCheckout['payment']['amount'] ?? 0) === 7500, 'resource checkout uses server-owned discounted pricing instead of client amount');
$publicResource = public_resource($resourceRow, null);
assert_true(!array_key_exists('protectedStorageKey', $publicResource) && !array_key_exists('originalFilename', $publicResource) && !str_contains(json_encode($publicResource), 'private-random.bin'), 'public resource payload never exposes protected storage paths or original filenames');
$pdo->prepare("INSERT INTO payments (id, user_id, provider, purpose, amount, currency, status, metadata, created_at, updated_at) VALUES ('PAY-RESOURCE-SMOKE', 'USR-SMOKE-WRITER', 'razorpay', 'Resource: Secure Resource', 7500, 'INR', 'paid', ?, ?, ?)")
    ->execute([json_encode(['kind' => 'resource', 'resourceId' => 'RES-SMOKE-PAID']), $now, $now]);
resource_grant_paid_entitlement(['id' => 'PAY-RESOURCE-SMOKE', 'user_id' => 'USR-SMOKE-WRITER', 'amount' => 7500, 'currency' => 'INR'], ['kind' => 'resource', 'resourceId' => 'RES-SMOKE-PAID']);
$paidEntitlement = resource_entitlement('RES-SMOKE-PAID', 'USR-SMOKE-WRITER');
assert_true(($paidEntitlement['status'] ?? '') === 'active' && ($paidEntitlement['payment_id'] ?? '') === 'PAY-RESOURCE-SMOKE', 'verified resource payments create a user-bound entitlement');
$pendingProfile = business_save_profile('company', ['name' => 'Unreviewed Writer Company'], $writerSession);
assert_true(($pendingProfile['status'] ?? '') === 'draft', 'non-staff business profiles require moderation before publication');
assert_true(business_get_profile('company', (string) $pendingProfile['id'], null) === null, 'draft business profiles are hidden from public requests');
$submissionPage = business_admin_paginated_profiles(['status' => 'draft'], 12);
assert_true(($submissionPage['pagination']['total'] ?? 0) === 1, 'new profile submissions are available in the paginated moderation queue');
$approval = business_review_submission('company', (string) $pendingProfile['id'], ['status' => 'approved', 'reviewNote' => 'Profile details verified.'], $adminSession);
assert_true(($approval['status'] ?? '') === 'approved' && business_get_profile('company', (string) $pendingProfile['id'], null) !== null, 'approving a profile submission publishes it');
$approvalNotification = $pdo->query("SELECT title, body FROM notifications WHERE user_id = 'USR-SMOKE-WRITER' AND type = 'business_submission' ORDER BY created_at DESC LIMIT 1")->fetch();
assert_true(str_contains((string) ($approvalNotification['title'] ?? ''), 'approved'), 'profile approval creates a user notification');

$rejectedProfile = business_save_profile('person', ['full_name' => 'Rejected Smoke Founder'], $writerSession);
$rejection = business_review_submission('person', (string) $rejectedProfile['id'], ['status' => 'rejected', 'reviewNote' => 'Please add more background.'], $adminSession);
assert_true(($rejection['status'] ?? '') === 'rejected' && (business_get_raw_profile('person', (string) $rejectedProfile['id'])['status'] ?? '') === 'rejected' && business_get_profile('person', (string) $rejectedProfile['id'], null) === null, 'rejecting a profile submission keeps it private and outside the profiles list');
$approvedProfilesOnly = business_admin_paginated_profiles(['excludeDrafts' => 1, 'q' => 'Rejected Smoke Founder'], 12);
assert_true(($approvedProfilesOnly['pagination']['total'] ?? 0) === 0, 'rejected submissions do not move into the admin profiles list');
$rejectionNotification = $pdo->query("SELECT title, body FROM notifications WHERE user_id = 'USR-SMOKE-WRITER' AND type = 'business_submission' AND title LIKE '%rejected%' LIMIT 1")->fetch();
assert_true(str_contains((string) ($rejectionNotification['title'] ?? ''), 'rejected'), 'profile rejection creates a user notification');
$resubmittedProfile = business_save_profile('person', ['full_name' => 'Rejected Smoke Founder', 'biography' => 'Expanded background for a second review.'], $writerSession, (string) $rejectedProfile['id']);
assert_true(($resubmittedProfile['status'] ?? '') === 'draft', 'editing a rejected profile returns it to the submission queue');
$pdo->prepare('DELETE FROM business_companies WHERE id = ?')->execute([$pendingProfile['id']]);
$pdo->prepare('DELETE FROM business_people WHERE id = ?')->execute([$rejectedProfile['id']]);
$pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?)')->execute([
    'site-seo-public',
    json_encode(['siteTitle' => 'Smoke Gazette'], JSON_UNESCAPED_SLASHES),
    'USR-BIZ-ADMIN',
    $now,
]);
assert_true(configured_site_name() === 'Smoke Gazette', 'configured site title is the server-side platform name');
$likeStoryResult = create_or_update_story_from_payload($adminSession, [
    'title' => 'A Story Worth Liking',
    'publication' => 'Smoke Gazette',
    'status' => 'published',
    'contentHtml' => '<p>A complete smoke-test article.</p>',
], 'smoke');
$likeStory = $likeStoryResult['story'];
$firstLike = toggle_story_like($likeStory['slug'], 'USR-BIZ-ADMIN');
$removedLike = toggle_story_like($likeStory['slug'], 'USR-BIZ-ADMIN');
$writerLike = toggle_story_like($likeStory['slug'], 'USR-SMOKE-WRITER');
$secondAdminLike = toggle_story_like($likeStory['slug'], 'USR-BIZ-ADMIN');
assert_true($firstLike['liked'] && $firstLike['count'] === 1, 'first story like is stored once');
assert_true(!$removedLike['liked'] && $removedLike['count'] === 0, 'second story like click removes the same user reaction');
assert_true($writerLike['count'] === 1 && $secondAdminLike['count'] === 2, 'story like count represents unique users');
assert_true((int) $pdo->query("SELECT COUNT(*) FROM story_likes WHERE story_slug = 'a-story-worth-liking'")->fetchColumn() === 2, 'story likes enforce one row per user and story');
$writerStoryResult = create_or_update_story_from_payload($writerSession, [
    'title' => 'Writer Studio Feature Test',
    'publication' => 'Smoke Gazette',
    'status' => 'review',
    'featuredImageUrl' => '/uploads/writer-feature.webp',
    'contentHtml' => '<h2>Writer editor</h2><p>Rich content.</p>',
    'interactiveBlocks' => [['type' => 'poll', 'question' => 'Useful?', 'options' => ['Yes', 'No']]],
    'seo' => ['seoTitle' => 'Writer Studio SEO Title', 'metaDescription' => 'Writer studio metadata test.'],
], 'smoke');
assert_true(
    ($writerStoryResult['story']['status'] ?? '') === 'review'
    && ($writerStoryResult['story']['authorUserId'] ?? '') === 'USR-SMOKE-WRITER'
    && ($writerStoryResult['story']['imageUrl'] ?? '') === '/uploads/writer-feature.webp'
    && count($writerStoryResult['story']['interactiveBlocks'] ?? []) === 1
    && ($writerStoryResult['story']['seo']['seoTitle'] ?? '') === 'Writer Studio SEO Title',
    'writer publishing API stores image, rich content, interactive blocks, and SEO without staff approval rights'
);
$writerCouldOverwriteAdminStory = true;
try {
    create_or_update_story_from_payload($writerSession, ['id' => $likeStory['id'], 'title' => 'Unauthorized overwrite'], 'smoke');
} catch (RuntimeException) {
    $writerCouldOverwriteAdminStory = false;
}
assert_true(!$writerCouldOverwriteAdminStory, 'writers cannot overwrite another author or admin story');
$paginationStories = document_value('stories', []);
for ($index = 1; $index <= 43; $index++) {
    $paginationStories[] = [
        'id' => 'SMOKE-PAGE-' . $index,
        'slug' => 'homepage-pagination-story-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        'title' => 'Homepage Pagination Story ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        'dek' => 'A published story used to verify twenty-item list pages.',
        'author' => 'Business Admin',
        'authorUserId' => 'USR-BIZ-ADMIN',
        'role' => 'Editorial desk',
        'publication' => 'Smoke Gazette',
        'topic' => 'Marketing',
        'readTime' => '4 min read',
        'premium' => false,
        'status' => 'published',
        'color' => 'mint',
        'imageUrl' => '',
        'tags' => ['pagination'],
        'body' => ['Pagination smoke content.'],
        'contentHtml' => '<p>Pagination smoke content.</p>',
        'interactiveBlocks' => [],
        'seo' => default_post_seo_payload(['title' => 'Homepage Pagination Story ' . $index, 'dek' => 'Pagination test.']),
        'claps' => 0,
        'comments' => 0,
        'reads' => 0,
        'revenue' => 0,
        'publishedAt' => $now,
        'createdAt' => $now,
        'updatedAt' => $now,
    ];
}
$pdo->prepare("UPDATE platform_documents SET value_json = ?, updated_at = ? WHERE key = 'stories'")
    ->execute([json_encode($paginationStories, JSON_UNESCAPED_SLASHES), $now]);
assert_true(count(array_filter(document_value('stories', []), fn($story) => ($story['status'] ?? '') === 'published')) === 44, 'smoke catalog includes enough published stories for three homepage pages');
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

$pdo->exec("UPDATE business_companies SET status = 'draft' WHERE name = 'Pagination Company 24'");
$firstAdminBusinessPage = business_admin_paginated_profiles(['page' => 1], 12);
$founderAdminResults = business_admin_paginated_profiles(['profileType' => 'person'], 12);
$searchedAdminResults = business_admin_paginated_profiles(['q' => 'Pagination Company 07'], 12);
$industryAdminResults = business_admin_paginated_profiles(['profileType' => 'company', 'industry' => 'Services'], 12);
$statusAdminResults = business_admin_paginated_profiles(['status' => 'draft'], 12);
assert_true(
    count($firstAdminBusinessPage['profiles']) === 12
    && ($firstAdminBusinessPage['pagination']['total'] ?? 0) === 26
    && ($firstAdminBusinessPage['pagination']['totalPages'] ?? 0) === 3,
    'admin business network returns 12 combined profiles with page metadata'
);
assert_true(
    count($founderAdminResults['profiles']) === 1
    && ($founderAdminResults['profiles'][0]['profile_type'] ?? '') === 'person',
    'admin business network filters founder profiles'
);
assert_true(
    ($searchedAdminResults['pagination']['total'] ?? 0) === 1
    && ($searchedAdminResults['profiles'][0]['display_name'] ?? '') === 'Pagination Company 07',
    'admin business network searches profile names'
);
assert_true(
    ($industryAdminResults['pagination']['total'] ?? 0) === 12,
    'admin business network filters company profiles by industry'
);
assert_true(
    ($statusAdminResults['pagination']['total'] ?? 0) === 1
    && ($statusAdminResults['profiles'][0]['status'] ?? '') === 'draft',
    'admin business network filters profiles by status'
);

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
$mcpImagePath = $root . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, (string) $mcpImage['asset']['url']);
if (is_file($mcpImagePath)) unlink($mcpImagePath);

echo "Smoke tests passed\n";
