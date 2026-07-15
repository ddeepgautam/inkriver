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

$jobId = enqueue_background_job('payouts.execute', ['limit' => 1]);
assert_true(str_starts_with($jobId, 'JOB-'), 'payout execution job can be queued');

echo "Smoke tests passed\n";
