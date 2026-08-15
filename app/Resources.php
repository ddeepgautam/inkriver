<?php
declare(strict_types=1);

function resource_slug(string $value): string
{
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($value)), '-');
    return substr($slug !== '' ? $slug : 'resource-' . time(), 0, 120);
}

function resource_json_list(?string $value): array
{
    $items = parse_json_field($value ?? '[]', []);
    return is_array($items) ? array_values($items) : [];
}

function resource_effective_price(array $row): int
{
    if (($row['price_type'] ?? 'free') === 'free') return 0;
    $discount = $row['discounted_price'];
    return $discount !== null && (int) $discount >= 0 && (int) $discount < (int) $row['regular_price']
        ? (int) $discount
        : (int) $row['regular_price'];
}

function resource_entitlement(string $resourceId, string $userId): ?array
{
    $stmt = Database::pdo()->prepare('SELECT * FROM resource_entitlements WHERE resource_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$resourceId, $userId]);
    return $stmt->fetch() ?: null;
}

function resource_subscription_access(array $resource, ?array $session): ?array
{
    if (!$session || ($resource['status'] ?? '') !== 'published' || !empty($resource['access_disabled'])) return null;
    $decision = entitlement_decision($session, CAPABILITY_INCLUDED_RESOURCES);
    if (!entitlement_resource_scope_allows($decision, $resource)) return null;
    return ['acquisition_type' => 'subscription', 'status' => 'active', 'subscription_id' => $decision['subscription']['id'] ?? null, 'decision' => $decision];
}

function public_resource(array $row, ?array $session = null, bool $admin = false): array
{
    $entitlement = $session ? resource_entitlement((string) $row['id'], (string) $session['user']['id']) : null;
    $subscriptionAccess = !$entitlement || $entitlement['status'] !== 'active' ? resource_subscription_access($row, $session) : null;
    $owned = ($entitlement && $entitlement['status'] === 'active') || $subscriptionAccess;
    $regular = (int) $row['regular_price'];
    $effective = resource_effective_price($row);
    $item = [
        'id' => $row['id'], 'slug' => $row['slug'], 'name' => $row['name'],
        'shortDescription' => $row['short_description'], 'description' => $row['description'],
        'category' => $row['category'], 'type' => $row['resource_type'], 'accessKind' => $row['access_kind'],
        'thumbnailUrl' => resource_public_url((string) $row['thumbnail_url']), 'previewImages' => resource_json_list($row['preview_images_json']),
        'tags' => resource_json_list($row['tags_json']), 'includes' => resource_json_list($row['includes_json']),
        'instructions' => $row['instructions'], 'audience' => $row['audience'], 'version' => $row['version'],
        'priceType' => $row['price_type'], 'regularPrice' => $regular, 'price' => $effective,
        'discountedPrice' => $row['discounted_price'] === null ? null : (int) $row['discounted_price'],
        'currency' => $row['currency'], 'sampleUrl' => resource_public_url((string) $row['sample_url']), 'status' => $row['status'],
        'accessDisabled' => (bool) $row['access_disabled'], 'fileSize' => (int) $row['file_size'],
        'hasProtectedFile' => !empty($row['protected_storage_key']), 'hasExternalAccess' => !empty($row['external_url']),
        'owned' => (bool) $owned, 'entitlementStatus' => $entitlement['status'] ?? null,
        'acquiredAt' => $entitlement['acquired_at'] ?? null, 'acquisitionType' => $entitlement['acquisition_type'] ?? ($subscriptionAccess ? 'subscription' : null),
        'subscriptionEligible' => (bool) ($row['subscription_eligible'] ?? false),
        'createdAt' => $row['created_at'], 'updatedAt' => $row['updated_at'], 'publishedAt' => $row['published_at'],
    ];
    if ($admin) {
        $item += [
            'originalFilename' => $row['original_filename'], 'mimeType' => $row['mime_type'],
            'singleUseLinks' => (bool) $row['single_use_links'],
            'downloadLimitPerHour' => (int) $row['download_limit_per_hour'],
            'externalUrl' => $row['external_url'] ?? '',
        ];
    }
    return $item;
}

function resource_access_log(?string $resourceId, ?string $userId, string $action, string $outcome, array $metadata = []): void
{
    Database::pdo()->prepare('INSERT INTO resource_access_logs (id, resource_id, user_id, action, outcome, ip_address, user_agent, metadata_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value('RAL-'), $resourceId, $userId, $action, $outcome, $_SERVER['REMOTE_ADDR'] ?? '', substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500), json_encode($metadata, JSON_UNESCAPED_SLASHES), now_iso()]);
}

function resource_store_private_upload(string $field = 'resourceFile'): ?array
{
    if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) json_response(['error' => 'UPLOAD_FAILED', 'message' => 'The protected resource upload failed.'], 400);
    $max = max(1024 * 1024, (int) (env_value('RESOURCE_FILE_MAX_BYTES', '524288000') ?? 524288000));
    if ((int) $file['size'] < 1 || (int) $file['size'] > $max) json_response(['error' => 'FILE_TOO_LARGE', 'message' => 'The resource file exceeds the configured upload limit.'], 413);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: 'application/octet-stream';
    $dirRelative = 'resources' . DIRECTORY_SEPARATOR . gmdate('Y') . DIRECTORY_SEPARATOR . gmdate('m');
    $dir = private_storage_path($dirRelative);
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) json_response(['error' => 'PRIVATE_STORAGE_UNAVAILABLE', 'message' => 'Private resource storage is unavailable.'], 500);
    @chmod($dir, 0700);
    $stored = bin2hex(random_bytes(24)) . '.bin';
    $target = $dir . DIRECTORY_SEPARATOR . $stored;
    if (!move_uploaded_file($file['tmp_name'], $target)) json_response(['error' => 'UPLOAD_STORE_FAILED', 'message' => 'Could not store the resource file.'], 500);
    @chmod($target, 0600);
    return ['key' => str_replace(DIRECTORY_SEPARATOR, '/', $dirRelative . DIRECTORY_SEPARATOR . $stored), 'name' => substr(basename((string) $file['name']), 0, 240), 'mime' => substr($mime, 0, 160), 'size' => (int) $file['size']];
}

function resource_store_thumbnail_upload(string $field = 'thumbnailFile'): ?string
{
    if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) return null;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) json_response(['error' => 'THUMBNAIL_UPLOAD_FAILED', 'message' => 'The thumbnail upload failed.'], 400);
    if ((int) $file['size'] < 1 || (int) $file['size'] > 8 * 1024 * 1024) json_response(['error' => 'THUMBNAIL_TOO_LARGE', 'message' => 'Thumbnail images must be 8 MB or smaller.'], 413);
    $info = getimagesize($file['tmp_name']);
    if (!$info || !image_dimensions_are_safe($info)) json_response(['error' => 'INVALID_THUMBNAIL', 'message' => 'Choose a valid image with safe dimensions.'], 400);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = (string) ($info['mime'] ?? '');
    if (!isset($allowed[$mime])) json_response(['error' => 'UNSUPPORTED_THUMBNAIL', 'message' => 'Use JPG, PNG, WebP, or GIF for resource thumbnails.'], 400);
    $relativeDir = 'uploads/resources/' . gmdate('Y/m');
    $absoluteDir = public_path($relativeDir);
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) json_response(['error' => 'THUMBNAIL_STORAGE_UNAVAILABLE', 'message' => 'Thumbnail storage is unavailable.'], 500);
    $filename = uuid_value('resource-thumb-') . '.' . $allowed[$mime];
    $target = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) json_response(['error' => 'THUMBNAIL_STORE_FAILED', 'message' => 'Could not store the thumbnail image.'], 500);
    return '/' . str_replace('\\', '/', $relativeDir) . '/' . $filename;
}

function resource_public_url(string $value): string
{
    $value = trim($value);
    if ($value === '') return '';
    if (preg_match('#^https?://#i', $value)) return substr($value, 0, 1000);
    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) return substr($value, 0, 1000);
    return '';
}

function resource_request_body(): array
{
    return str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'multipart/form-data') ? $_POST : read_json();
}

function resource_clean_fields(array $body, ?array $existing = null): array
{
    if ($existing) $existing = [
        'slug' => $existing['slug'], 'name' => $existing['name'], 'shortDescription' => $existing['short_description'],
        'description' => $existing['description'], 'category' => $existing['category'], 'type' => $existing['resource_type'],
        'accessKind' => $existing['access_kind'], 'thumbnailUrl' => $existing['thumbnail_url'],
        'previewImages' => resource_json_list($existing['preview_images_json']), 'tags' => resource_json_list($existing['tags_json']),
        'includes' => resource_json_list($existing['includes_json']), 'instructions' => $existing['instructions'],
        'audience' => $existing['audience'], 'version' => $existing['version'], 'priceType' => $existing['price_type'],
        'regularPrice' => $existing['regular_price'], 'discountedPrice' => $existing['discounted_price'], 'currency' => $existing['currency'],
        'externalUrl' => $existing['external_url'], 'sampleUrl' => $existing['sample_url'], 'status' => $existing['status'],
        'accessDisabled' => $existing['access_disabled'], 'singleUseLinks' => $existing['single_use_links'],
        'downloadLimitPerHour' => $existing['download_limit_per_hour'],
        'subscriptionEligible' => $existing['subscription_eligible'] ?? 0,
    ];
    $get = fn(string $key, mixed $fallback = '') => array_key_exists($key, $body) ? $body[$key] : ($existing[$key] ?? $fallback);
    $name = trim((string) $get('name'));
    if (strlen($name) < 2 || strlen($name) > 180) json_response(['error' => 'INVALID_RESOURCE_NAME', 'message' => 'Resource name must be 2 to 180 characters.'], 400);
    $type = substr(strtolower(trim((string) $get('type', 'file'))), 0, 60) ?: 'file';
    $accessKind = strtolower(trim((string) $get('accessKind', 'download')));
    if (!in_array($accessKind, ['download', 'view', 'watch', 'copy', 'open'], true)) $accessKind = 'download';
    $priceType = $get('priceType', 'free') === 'paid' ? 'paid' : 'free';
    $regular = $priceType === 'paid' ? max(1, min(1000000000, (int) $get('regularPrice', 0))) : 0;
    $discountRaw = $get('discountedPrice', null);
    $discount = $discountRaw === '' || $discountRaw === null ? null : max(0, (int) $discountRaw);
    if ($discount !== null && $discount >= $regular) $discount = null;
    $status = strtolower((string) $get('status', 'draft'));
    if (!in_array($status, ['draft', 'published', 'unpublished', 'archived'], true)) $status = 'draft';
    $list = function (mixed $value): string {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value) ?: []));
        }
        return json_encode(array_values(array_slice(is_array($value) ? $value : [], 0, 50)), JSON_UNESCAPED_SLASHES);
    };
    return [
        'slug' => resource_slug((string) $get('slug', $name)), 'name' => $name,
        'short_description' => substr(trim((string) $get('shortDescription')), 0, 320),
        'description' => substr(trim((string) $get('description')), 0, 30000),
        'category' => substr(trim((string) $get('category', 'General')) ?: 'General', 0, 100),
        'resource_type' => $type, 'access_kind' => $accessKind,
        'thumbnail_url' => resource_public_url((string) $get('thumbnailUrl')),
        'preview_images_json' => $list($get('previewImages', [])), 'tags_json' => $list($get('tags', [])),
        'includes_json' => $list($get('includes', [])), 'instructions' => substr(trim((string) $get('instructions')), 0, 20000),
        'audience' => substr(trim((string) $get('audience')), 0, 4000), 'version' => substr(trim((string) $get('version', '1.0')) ?: '1.0', 0, 40),
        'price_type' => $priceType, 'regular_price' => $regular, 'discounted_price' => $discount,
        'currency' => preg_match('/^[A-Z]{3}$/', strtoupper((string) $get('currency', 'INR'))) ? strtoupper((string) $get('currency', 'INR')) : 'INR',
        'external_url' => (($external = substr(trim((string) $get('externalUrl')), 0, 2000)) && preg_match('#^https?://#i', $external)) ? $external : null,
        'sample_url' => resource_public_url((string) $get('sampleUrl')), 'status' => $status,
        'access_disabled' => filter_var($get('accessDisabled', false), FILTER_VALIDATE_BOOL) ? 1 : 0,
        'single_use_links' => filter_var($get('singleUseLinks', false), FILTER_VALIDATE_BOOL) ? 1 : 0,
        'download_limit_per_hour' => max(1, min(500, (int) $get('downloadLimitPerHour', 20))),
        'subscription_eligible' => filter_var($get('subscriptionEligible', false), FILTER_VALIDATE_BOOL) ? 1 : 0,
    ];
}

function resource_grant_paid_entitlement(array $payment, array $metadata): void
{
    if (($metadata['kind'] ?? '') !== 'resource') return;
    $resourceId = (string) ($metadata['resourceId'] ?? '');
    $stmt = Database::pdo()->prepare("SELECT * FROM resources WHERE id = ? AND price_type = 'paid'");
    $stmt->execute([$resourceId]);
    $resource = $stmt->fetch();
    if (!$resource || empty($payment['user_id'])) return;
    $now = now_iso();
    Database::pdo()->prepare("INSERT INTO resource_entitlements (id, resource_id, user_id, payment_id, acquisition_type, status, price_paid, currency, acquired_version, acquired_at) VALUES (?, ?, ?, ?, 'purchase', 'active', ?, ?, ?, ?) ON CONFLICT(resource_id, user_id) DO UPDATE SET payment_id = excluded.payment_id, acquisition_type = 'purchase', status = 'active', price_paid = excluded.price_paid, currency = excluded.currency, acquired_version = excluded.acquired_version, acquired_at = excluded.acquired_at, revoked_at = NULL, revoked_by_user_id = NULL, revoke_reason = ''")
        ->execute([uuid_value('ENT-'), $resourceId, $payment['user_id'], $payment['id'], (int) $payment['amount'], $payment['currency'], $resource['version'], $now]);
    create_notification($payment['user_id'], 'resource_purchased', 'Resource added to your library', (string) $resource['name'], '/dashboard');
    audit_log($payment['user_id'], 'resource.purchase_granted', 'resource', $resourceId, ['paymentId' => $payment['id']]);
}

function resource_find_by_slug_or_id(string $value, bool $admin = false): ?array
{
    $where = $admin ? '' : " AND status = 'published'";
    $stmt = Database::pdo()->prepare("SELECT * FROM resources WHERE (id = ? OR slug = ?)$where LIMIT 1");
    $stmt->execute([$value, $value]);
    return $stmt->fetch() ?: null;
}

function resource_require_access(array $resource, array $session, string $action): array
{
    if (($session['user']['status'] ?? '') !== 'active' || !empty($resource['access_disabled'])) {
        resource_access_log($resource['id'], $session['user']['id'], $action, 'denied_disabled');
        json_response(['error' => 'RESOURCE_ACCESS_DISABLED', 'message' => 'Access to this resource is currently unavailable.'], 403);
    }
    $entitlement = resource_entitlement($resource['id'], $session['user']['id']);
    if (!$entitlement || $entitlement['status'] !== 'active') $entitlement = resource_subscription_access($resource, $session);
    if (!$entitlement || $entitlement['status'] !== 'active') {
        resource_access_log($resource['id'], $session['user']['id'], $action, 'denied_no_entitlement');
        json_response(['error' => 'RESOURCE_NOT_OWNED', 'message' => 'This resource is not in your library.'], 403);
    }
    if ($entitlement['acquisition_type'] === 'purchase') {
        $stmt = Database::pdo()->prepare("SELECT status FROM payments WHERE id = ? AND user_id = ?");
        $stmt->execute([$entitlement['payment_id'], $session['user']['id']]);
        if (($stmt->fetchColumn() ?: '') !== 'paid') {
            resource_access_log($resource['id'], $session['user']['id'], $action, 'denied_payment');
            json_response(['error' => 'PAYMENT_NOT_VALID', 'message' => 'A completed payment is required for this resource.'], 403);
        }
    }
    $limit = max(1, (int) $resource['download_limit_per_hour']);
    $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM resource_access_logs WHERE resource_id = ? AND user_id = ? AND outcome = 'success' AND created_at >= ?");
    $stmt->execute([$resource['id'], $session['user']['id'], gmdate('Y-m-d\TH:i:s.v\Z', time() - 3600)]);
    if ((int) $stmt->fetchColumn() >= $limit) {
        resource_access_log($resource['id'], $session['user']['id'], $action, 'rate_limited', ['limit' => $limit]);
        json_response(['error' => 'RESOURCE_RATE_LIMITED', 'message' => 'Too many resource access attempts. Please try again later.'], 429);
    }
    return $entitlement;
}

function resource_send_authorized_token(string $rawToken): never
{
    $stmt = Database::pdo()->prepare('SELECT r.*, t.id AS token_id, t.user_id AS token_user_id, t.action AS token_action, t.single_use AS token_single_use, t.used_at AS token_used_at FROM resource_access_tokens t JOIN resources r ON r.id = t.resource_id WHERE t.token_hash = ? AND t.expires_at > ? LIMIT 1');
    $stmt->execute([hash('sha256', $rawToken), now_iso()]);
    $row = $stmt->fetch();
    $session = current_session();
    if (!$session) {
        resource_access_log($row['id'] ?? null, null, 'token', 'denied_unauthenticated');
        json_response(['error' => 'AUTH_REQUIRED', 'message' => 'Sign in to use this secure resource link.'], 401);
    }
    if (!$row || !hash_equals((string) $row['token_user_id'], (string) $session['user']['id']) || (!empty($row['token_single_use']) && !empty($row['token_used_at']))) {
        resource_access_log($row['id'] ?? null, $session['user']['id'], 'token', 'denied_invalid_token');
        json_response(['error' => 'INVALID_ACCESS_TOKEN', 'message' => 'This secure access link is invalid or expired.'], 403);
    }
    resource_require_access($row, $session, (string) $row['token_action']);
    if (!empty($row['token_single_use'])) {
        $consume = Database::pdo()->prepare('UPDATE resource_access_tokens SET used_at = ? WHERE id = ? AND used_at IS NULL');
        $consume->execute([now_iso(), $row['token_id']]);
        if ($consume->rowCount() !== 1) json_response(['error' => 'ACCESS_TOKEN_USED', 'message' => 'This single-use access link has already been used.'], 403);
    }
    Database::pdo()->prepare('UPDATE resource_entitlements SET acquired_version = ? WHERE resource_id = ? AND user_id = ? AND status = \'active\'')->execute([$row['version'], $row['id'], $session['user']['id']]);
    resource_access_log($row['id'], $session['user']['id'], (string) $row['token_action'], 'success', ['tokenId' => $row['token_id']]);
    if (!empty($row['external_url'])) redirect_response((string) $row['external_url']);
    $key = (string) ($row['protected_storage_key'] ?? '');
    $path = $key !== '' ? private_storage_path($key) : '';
    $root = realpath(private_storage_root());
    $real = $path !== '' ? realpath($path) : false;
    if (!$real || !$root || !path_is_within($real, $root) || !is_file($real)) json_response(['error' => 'RESOURCE_FILE_UNAVAILABLE', 'message' => 'The protected file is unavailable.'], 404);
    $inline = in_array($row['token_action'], ['view', 'watch', 'copy'], true);
    $filename = preg_replace('/[^A-Za-z0-9._ -]+/', '_', (string) ($row['original_filename'] ?: $row['name'])) ?: 'resource-file';
    foreach (security_headers() + [
        'Content-Type' => (string) ($row['mime_type'] ?: 'application/octet-stream'),
        'Content-Length' => (string) filesize($real),
        'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . addcslashes($filename, '"\\') . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
        'Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache', 'X-Robots-Tag' => 'noindex, nofollow, noarchive',
    ] as $keyName => $value) header($keyName . ': ' . $value);
    readfile($real);
    exit;
}

function handle_resources_api(string $path, string $method): void
{
    $pdo = Database::pdo();
    if ($method === 'GET' && preg_match('#^/api/resources/access/([A-Za-z0-9_-]+)$#', $path, $m)) resource_send_authorized_token($m[1]);

    if ($method === 'GET' && $path === '/api/resources') {
        $session = current_session();
        $where = ["status = 'published'"];
        $args = [];
        foreach (['category' => 'category', 'type' => 'resource_type', 'price' => 'price_type'] as $query => $column) {
            if (!empty($_GET[$query])) { $where[] = "$column = ?"; $args[] = substr((string) $_GET[$query], 0, 100); }
        }
        if (!empty($_GET['q'])) { $where[] = '(name LIKE ? OR short_description LIKE ? OR description LIKE ? OR tags_json LIKE ?)'; $q = '%' . substr((string) $_GET['q'], 0, 120) . '%'; array_push($args, $q, $q, $q, $q); }
        $stmt = $pdo->prepare('SELECT * FROM resources WHERE ' . implode(' AND ', $where) . ' ORDER BY updated_at DESC LIMIT 200');
        $stmt->execute($args);
        $rows = $stmt->fetchAll();
        $categories = array_values(array_unique(array_column($rows, 'category')));
        json_response(['resources' => array_map(fn($row) => public_resource($row, $session), $rows), 'categories' => $categories]);
    }

    if ($method === 'GET' && preg_match('#^/api/resources/([^/]+)$#', $path, $m)) {
        $session = current_session();
        $row = resource_find_by_slug_or_id(urldecode($m[1]));
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        $pdo->prepare('INSERT INTO resource_views (id, resource_id, user_id, anonymous_id, created_at) VALUES (?, ?, ?, ?, ?)')->execute([uuid_value('RVW-'), $row['id'], $session['user']['id'] ?? null, $_COOKIE['inkriver_anon'] ?? null, now_iso()]);
        json_response(['resource' => public_resource($row, $session)]);
    }

    if ($method === 'POST' && preg_match('#^/api/resources/([^/]+)/claim$#', $path, $m)) {
        $session = require_auth();
        $row = resource_find_by_slug_or_id(urldecode($m[1]));
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        if ($row['price_type'] !== 'free') json_response(['error' => 'PURCHASE_REQUIRED', 'message' => 'This resource must be purchased.'], 400);
        if (!empty($row['access_disabled'])) json_response(['error' => 'RESOURCE_ACCESS_DISABLED', 'message' => 'This resource is currently unavailable.'], 403);
        $existing = resource_entitlement($row['id'], $session['user']['id']);
        if ($existing && $existing['status'] === 'revoked') json_response(['error' => 'RESOURCE_ACCESS_REVOKED', 'message' => 'Access to this resource was revoked. Contact support if you believe this is an error.'], 403);
        $now = now_iso();
        $pdo->prepare("INSERT INTO resource_entitlements (id, resource_id, user_id, acquisition_type, status, price_paid, currency, acquired_version, acquired_at) VALUES (?, ?, ?, 'free_claim', 'active', 0, ?, ?, ?) ON CONFLICT(resource_id, user_id) DO UPDATE SET status = 'active', acquired_version = excluded.acquired_version, revoked_at = NULL, revoked_by_user_id = NULL, revoke_reason = ''")
            ->execute([uuid_value('ENT-'), $row['id'], $session['user']['id'], $row['currency'], $row['version'], $now]);
        audit_log($session['user']['id'], 'resource.free_claimed', 'resource', $row['id']);
        json_response(['resource' => public_resource($row, $session)], 201);
    }

    if ($method === 'POST' && preg_match('#^/api/resources/([^/]+)/access$#', $path, $m)) {
        $session = require_auth();
        $row = resource_find_by_slug_or_id(urldecode($m[1]));
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        $action = (string) ($row['access_kind'] ?: 'download');
        resource_require_access($row, $session, $action);
        $raw = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expires = gmdate('Y-m-d\TH:i:s.v\Z', time() + max(30, min(600, (int) (env_value('RESOURCE_ACCESS_TOKEN_TTL', '120') ?? 120))));
        $tokenId = uuid_value('RAT-');
        $pdo->prepare('INSERT INTO resource_access_tokens (id, token_hash, resource_id, user_id, action, expires_at, single_use, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$tokenId, hash('sha256', $raw), $row['id'], $session['user']['id'], $action, $expires, (int) $row['single_use_links'], now_iso()]);
        resource_access_log($row['id'], $session['user']['id'], $action, 'token_issued', ['expiresAt' => $expires]);
        json_response(['accessUrl' => '/api/resources/access/' . $raw, 'expiresAt' => $expires, 'action' => $action]);
    }

    if ($method === 'GET' && $path === '/api/me/resources') {
        $session = require_auth();
        $stmt = $pdo->prepare("SELECT r.*, e.acquisition_type, e.acquired_at, e.acquired_version, e.price_paid, e.currency AS entitlement_currency, e.status AS entitlement_status, e.payment_id,
            (SELECT MAX(created_at) FROM resource_access_logs l WHERE l.resource_id = r.id AND l.user_id = e.user_id AND l.outcome = 'success') AS last_accessed_at,
            (SELECT COUNT(*) FROM resource_access_logs l WHERE l.resource_id = r.id AND l.user_id = e.user_id AND l.outcome = 'success') AS access_count
            FROM resource_entitlements e JOIN resources r ON r.id = e.resource_id WHERE e.user_id = ? AND e.status = 'active' ORDER BY e.acquired_at DESC");
        $stmt->execute([$session['user']['id']]);
        $items = array_map(function ($row) use ($session) { $item = public_resource($row, $session); $item['lastAccessedAt'] = $row['last_accessed_at']; $item['accessCount'] = (int) $row['access_count']; $item['newVersionAvailable'] = version_compare((string) $row['version'], (string) $row['acquired_version'], '>'); return $item; }, $stmt->fetchAll());
        json_response(['resources' => $items]);
    }

    if ($method === 'GET' && $path === '/api/me/resource-orders') {
        $session = require_auth();
        $stmt = $pdo->prepare("SELECT p.id AS order_number, p.created_at, p.amount, p.currency, p.status, p.provider, r.id AS resource_id, r.slug, r.name FROM payments p JOIN resources r ON r.id = json_extract(p.metadata, '$.resourceId') WHERE p.user_id = ? AND json_extract(p.metadata, '$.kind') = 'resource' ORDER BY p.created_at DESC");
        $stmt->execute([$session['user']['id']]);
        json_response(['orders' => $stmt->fetchAll()]);
    }

    if ($method === 'GET' && $path === '/api/admin/resources') {
        $session = require_auth(['admin']);
        $rows = $pdo->query("SELECT r.*, (SELECT COUNT(*) FROM resource_entitlements e WHERE e.resource_id = r.id AND e.status = 'active') AS owners, (SELECT COUNT(*) FROM resource_access_logs l WHERE l.resource_id = r.id AND l.outcome = 'success') AS downloads, (SELECT COUNT(*) FROM resource_views v WHERE v.resource_id = r.id) AS views, (SELECT COALESCE(SUM(e.price_paid), 0) FROM resource_entitlements e WHERE e.resource_id = r.id AND e.acquisition_type = 'purchase' AND e.status = 'active') AS revenue FROM resources r ORDER BY r.updated_at DESC")->fetchAll();
        $items = array_map(function ($row) use ($session) { return public_resource($row, $session, true) + ['owners' => (int) $row['owners'], 'downloads' => (int) $row['downloads'], 'views' => (int) $row['views'], 'revenue' => (int) $row['revenue']]; }, $rows);
        json_response(['resources' => $items]);
    }

    if ($method === 'GET' && preg_match('#^/api/admin/resources/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin']);
        $row = resource_find_by_slug_or_id(urldecode($m[1]), true);
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        $owners = $pdo->prepare('SELECT e.*, u.name, u.email FROM resource_entitlements e JOIN users u ON u.id = e.user_id WHERE e.resource_id = ? ORDER BY e.acquired_at DESC');
        $owners->execute([$row['id']]);
        $versions = $pdo->prepare('SELECT id, version, original_filename, mime_type, file_size, release_notes, created_at FROM resource_versions WHERE resource_id = ? ORDER BY created_at DESC');
        $versions->execute([$row['id']]);
        $logs = $pdo->prepare('SELECT id, user_id, action, outcome, ip_address, metadata_json, created_at FROM resource_access_logs WHERE resource_id = ? ORDER BY created_at DESC LIMIT 200');
        $logs->execute([$row['id']]);
        json_response(['resource' => public_resource($row, $session, true), 'owners' => $owners->fetchAll(), 'versions' => $versions->fetchAll(), 'accessLogs' => $logs->fetchAll()]);
    }

    if ($method === 'POST' && $path === '/api/admin/resources') {
        $session = require_auth(['admin']);
        $body = resource_request_body();
        $fields = resource_clean_fields($body);
        $upload = resource_store_private_upload();
        $thumbnailUrl = resource_store_thumbnail_upload();
        if ($thumbnailUrl !== null) $fields['thumbnail_url'] = $thumbnailUrl;
        if (!$upload && empty($fields['external_url'])) json_response(['error' => 'RESOURCE_ACCESS_TARGET_REQUIRED', 'message' => 'Upload a protected file or provide an external access URL.'], 400);
        $slugCheck = $pdo->prepare('SELECT id FROM resources WHERE slug = ? LIMIT 1'); $slugCheck->execute([$fields['slug']]);
        if ($slugCheck->fetch()) json_response(['error' => 'RESOURCE_SLUG_EXISTS', 'message' => 'Choose a unique resource slug.'], 409);
        $id = uuid_value('RES-'); $now = now_iso();
        $columns = array_keys($fields);
        $sql = 'INSERT INTO resources (id, ' . implode(', ', $columns) . ', protected_storage_key, original_filename, mime_type, file_size, created_by_user_id, created_at, updated_at, published_at) VALUES (' . implode(', ', array_fill(0, count($columns) + 9, '?')) . ')';
        $pdo->prepare($sql)->execute(array_merge([$id], array_values($fields), [$upload['key'] ?? null, $upload['name'] ?? null, $upload['mime'] ?? null, $upload['size'] ?? 0, $session['user']['id'], $now, $now, $fields['status'] === 'published' ? $now : null]));
        if ($upload) $pdo->prepare('INSERT INTO resource_versions (id, resource_id, version, protected_storage_key, original_filename, mime_type, file_size, created_by_user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([uuid_value('VER-'), $id, $fields['version'], $upload['key'], $upload['name'], $upload['mime'], $upload['size'], $session['user']['id'], $now]);
        audit_log($session['user']['id'], 'resource.created', 'resource', $id, ['status' => $fields['status']]);
        $row = resource_find_by_slug_or_id($id, true);
        json_response(['resource' => public_resource($row, $session, true)], 201);
    }

    if (($method === 'PATCH' || $method === 'POST') && preg_match('#^/api/admin/resources/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin']);
        $row = resource_find_by_slug_or_id(urldecode($m[1]), true);
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        $body = resource_request_body();
        $fields = resource_clean_fields($body, $row);
        $upload = resource_store_private_upload();
        $thumbnailUrl = resource_store_thumbnail_upload();
        if ($thumbnailUrl !== null) $fields['thumbnail_url'] = $thumbnailUrl;
        if (!$upload && empty($fields['external_url']) && empty($row['protected_storage_key'])) json_response(['error' => 'RESOURCE_ACCESS_TARGET_REQUIRED', 'message' => 'Keep a protected file or provide an external access URL.'], 400);
        $slugCheck = $pdo->prepare('SELECT id FROM resources WHERE slug = ? AND id != ? LIMIT 1'); $slugCheck->execute([$fields['slug'], $row['id']]);
        if ($slugCheck->fetch()) json_response(['error' => 'RESOURCE_SLUG_EXISTS', 'message' => 'Choose a unique resource slug.'], 409);
        $assignments = array_map(fn($column) => "$column = ?", array_keys($fields));
        $values = array_values($fields);
        if ($upload) { array_push($assignments, 'protected_storage_key = ?', 'original_filename = ?', 'mime_type = ?', 'file_size = ?'); array_push($values, $upload['key'], $upload['name'], $upload['mime'], $upload['size']); }
        $assignments[] = 'updated_at = ?'; $values[] = now_iso();
        if ($fields['status'] === 'published' && empty($row['published_at'])) { $assignments[] = 'published_at = ?'; $values[] = now_iso(); }
        $values[] = $row['id'];
        $pdo->prepare('UPDATE resources SET ' . implode(', ', $assignments) . ' WHERE id = ?')->execute($values);
        if ($upload) $pdo->prepare("INSERT INTO resource_versions (id, resource_id, version, protected_storage_key, original_filename, mime_type, file_size, release_notes, created_by_user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(resource_id, version) DO UPDATE SET protected_storage_key = excluded.protected_storage_key, original_filename = excluded.original_filename, mime_type = excluded.mime_type, file_size = excluded.file_size, release_notes = excluded.release_notes, created_at = excluded.created_at")
            ->execute([uuid_value('VER-'), $row['id'], $fields['version'], $upload['key'], $upload['name'], $upload['mime'], $upload['size'], substr((string) ($body['releaseNotes'] ?? ''), 0, 5000), $session['user']['id'], now_iso()]);
        audit_log($session['user']['id'], 'resource.updated', 'resource', $row['id'], ['version' => $fields['version'], 'fileReplaced' => (bool) $upload]);
        $row = resource_find_by_slug_or_id((string) $row['id'], true);
        json_response(['resource' => public_resource($row, $session, true)]);
    }

    if ($method === 'DELETE' && preg_match('#^/api/admin/resources/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin']);
        $row = resource_find_by_slug_or_id(urldecode($m[1]), true);
        if (!$row) json_response(['error' => 'RESOURCE_NOT_FOUND', 'message' => 'Resource not found.'], 404);
        $pdo->prepare("UPDATE resources SET status = 'archived', access_disabled = 1, updated_at = ? WHERE id = ?")->execute([now_iso(), $row['id']]);
        $pdo->prepare('UPDATE resource_access_tokens SET expires_at = ? WHERE resource_id = ? AND used_at IS NULL')->execute([now_iso(), $row['id']]);
        audit_log($session['user']['id'], 'resource.archived', 'resource', $row['id']);
        json_response(['ok' => true]);
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/resources/([^/]+)/entitlements/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin']); $body = read_json();
        $status = ($body['status'] ?? '') === 'active' ? 'active' : 'revoked';
        $pdo->prepare("UPDATE resource_entitlements SET status = ?, revoked_at = CASE WHEN ? = 'revoked' THEN ? ELSE NULL END, revoked_by_user_id = CASE WHEN ? = 'revoked' THEN ? ELSE NULL END, revoke_reason = ? WHERE resource_id = ? AND user_id = ?")
            ->execute([$status, $status, now_iso(), $status, $session['user']['id'], substr((string) ($body['reason'] ?? ''), 0, 500), urldecode($m[1]), urldecode($m[2])]);
        if ($status === 'revoked') $pdo->prepare('UPDATE resource_access_tokens SET expires_at = ? WHERE resource_id = ? AND user_id = ? AND used_at IS NULL')->execute([now_iso(), urldecode($m[1]), urldecode($m[2])]);
        audit_log($session['user']['id'], 'resource.entitlement_' . $status, 'resource', urldecode($m[1]), ['userId' => urldecode($m[2])]);
        json_response(['ok' => true, 'status' => $status]);
    }
}
