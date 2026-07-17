<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';

const PUBLIC_DOCUMENTS = ['stories', 'categories', 'plans', 'site-seo-public'];
const ADMIN_DOCUMENTS = ['stories', 'categories', 'plans', 'site-seo', 'creator-tools', 'operations', 'platform-settings'];
const USER_DOCUMENTS = ['saved', 'following', 'reading-history', 'interactive-responses', 'recommendation-profile', 'preferences'];

function provider_status(): array
{
    return [
        'payments' => [
            'razorpay' => (bool) (provider_config_value('RAZORPAY_KEY_ID', 'razorpay', 'key_id') && provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret')),
            'paypal' => (bool) (provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') && provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')),
            'payu' => (bool) (provider_config_value('PAYU_MERCHANT_KEY', 'payu', 'merchant_key') && provider_config_value('PAYU_SALT', 'payu', 'salt')),
            'cashfree' => (bool) (provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id') && provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret')),
        ],
        'social' => [
            'google' => (bool) (provider_config_value('GOOGLE_CLIENT_ID', 'google', 'client_id') && provider_config_value('GOOGLE_CLIENT_SECRET', 'google', 'client_secret')),
            'facebook' => (bool) (provider_config_value('FACEBOOK_APP_ID', 'facebook', 'app_id') && provider_config_value('FACEBOOK_APP_SECRET', 'facebook', 'app_secret')),
        ],
        'ai' => (bool) (provider_config_value('OPENAI_API_KEY', 'openai', 'api_key') || (provider_config_value('AI_API_URL', 'ai', 'api_url') && provider_config_value('AI_API_KEY', 'ai', 'api_key'))),
        'email' => (bool) (provider_config_value('EMAIL_API_URL', 'email', 'api_url') && provider_config_value('EMAIL_API_KEY', 'email', 'api_key')),
        'push' => (bool) ((provider_config_value('VAPID_PUBLIC_KEY', 'push', 'vapid_public_key') && provider_config_value('VAPID_PRIVATE_KEY', 'push', 'vapid_private_key')) || (provider_config_value('WEB_PUSH_API_URL', 'push', 'api_url') && provider_config_value('WEB_PUSH_API_KEY', 'push', 'api_key'))),
        'geoip' => (bool) (provider_config_value('IP_INTELLIGENCE_API_URL', 'geoip', 'api_url') && provider_config_value('IP_INTELLIGENCE_API_KEY', 'geoip', 'api_key')),
    ];
}

function clean_username_value(string $value): string
{
    return strtolower(trim($value));
}

function username_error(string $username): ?string
{
    if ($username === '') return null;
    if (strlen($username) < 3 || strlen($username) > 30) return 'Username must be 3 to 30 characters.';
    if (!preg_match('/^[a-z0-9_]+$/', $username)) return 'Username can use lowercase letters, numbers, and underscores only.';
    $reserved = ['about', 'admin', 'administrator', 'api', 'auth', 'billing', 'blog', 'category', 'dashboard', 'help', 'lists', 'login', 'logout', 'me', 'moderator', 'pricing', 'privacy', 'publications', 'publication-invites', 'root', 'search', 'security', 'settings', 'signup', 'stories', 'support', 'terms', 'write'];
    if (in_array($username, $reserved, true)) return 'That username is reserved.';
    return null;
}

function feature_flag_active_for_row(array $row, ?array $session = null): bool
{
    if (empty($row['enabled'])) return false;
    $environment = strtolower(trim((string) ($row['environment'] ?? 'all')));
    $currentEnvironment = strtolower(trim((string) (env_value('APP_ENV') ?: provider_config_value('APP_ENV', 'platform', 'environment', 'production') ?: 'production')));
    if ($environment !== '' && $environment !== 'all' && $environment !== $currentEnvironment) return false;
    $now = time();
    if (!empty($row['starts_at']) && strtotime((string) $row['starts_at']) > $now) return false;
    if (!empty($row['ends_at']) && strtotime((string) $row['ends_at']) < $now) return false;
    $roles = parse_json_field($row['roles_json'] ?? '[]', []);
    if (is_array($roles) && $roles && !in_array((string) ($session['user']['role'] ?? 'guest'), $roles, true)) return false;
    $percent = max(0, min(100, (int) ($row['rollout_percent'] ?? 100)));
    if ($percent >= 100) return true;
    if ($percent <= 0) return false;
    $basis = (string) ($session['user']['id'] ?? ($_COOKIE['inkriver_anon'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'guest')));
    $bucket = hexdec(substr(hash('sha256', ($row['key'] ?? '') . ':' . $basis), 0, 8)) % 100;
    return $bucket < $percent;
}

function public_feature_flags(?array $session = null): array
{
    try {
        $rows = Database::pdo()->query('SELECT * FROM feature_flags')->fetchAll();
        $flags = [];
        foreach ($rows as $row) $flags[$row['key']] = feature_flag_active_for_row($row, $session);
        return $flags;
    } catch (Throwable) {
        return [];
    }
}

function feature_flag_enabled(string $key, bool $fallback = true): bool
{
    $flags = public_feature_flags(current_session());
    return array_key_exists($key, $flags) ? (bool) $flags[$key] : $fallback;
}

function document_value(string $key, mixed $fallback): mixed
{
    $stmt = Database::pdo()->prepare('SELECT value_json FROM platform_documents WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? parse_json_field($row['value_json'], $fallback) : $fallback;
}

function app_url(string $path = '/'): string
{
    $configured = rtrim((string) (env_value('APP_URL') ?? ''), '/');
    if ($configured !== '') return $configured . '/' . ltrim($path, '/');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function credential_crypto_key(): string
{
    return hash('sha256', env_value('APP_SECRET', env_value('APP_KEY', database_path())) ?? database_path(), true);
}

function encrypt_secret_value(string $value): string
{
    $iv = random_bytes(16);
    $cipher = openssl_encrypt($value, 'aes-256-cbc', credential_crypto_key(), OPENSSL_RAW_DATA, $iv);
    if ($cipher === false) throw new RuntimeException('Unable to encrypt credential.');
    $mac = hash_hmac('sha256', $iv . $cipher, credential_crypto_key(), true);
    return base64_encode(json_encode([
        'iv' => base64_encode($iv),
        'mac' => base64_encode($mac),
        'value' => base64_encode($cipher),
    ], JSON_UNESCAPED_SLASHES));
}

function decrypt_secret_value(string $stored): string
{
    $decoded = json_decode(base64_decode($stored, true) ?: '', true);
    if (!is_array($decoded)) return '';
    $iv = base64_decode((string) ($decoded['iv'] ?? ''), true) ?: '';
    $mac = base64_decode((string) ($decoded['mac'] ?? ''), true) ?: '';
    $cipher = base64_decode((string) ($decoded['value'] ?? ''), true) ?: '';
    if (!$iv || !$cipher || !hash_equals($mac, hash_hmac('sha256', $iv . $cipher, credential_crypto_key(), true))) return '';
    $plain = openssl_decrypt($cipher, 'aes-256-cbc', credential_crypto_key(), OPENSSL_RAW_DATA, $iv);
    return is_string($plain) ? $plain : '';
}

function provider_config_value(string $envKey, string $provider, string $key, ?string $fallback = null): ?string
{
    $env = env_value($envKey);
    if ($env !== null) return $env;
    try {
        $stmt = Database::pdo()->prepare('SELECT value_encrypted FROM provider_credentials WHERE provider = ? AND key = ? AND enabled = 1');
        $stmt->execute([$provider, $key]);
        $row = $stmt->fetch();
        if ($row) {
            $value = decrypt_secret_value((string) $row['value_encrypted']);
            if ($value !== '') return $value;
        }
    } catch (Throwable) {
        return $fallback;
    }
    return $fallback;
}

function provider_credential_summary(): array
{
    $rows = Database::pdo()->query('SELECT provider, key, environment, enabled, updated_at FROM provider_credentials ORDER BY provider, key')->fetchAll();
    return array_map(fn($row) => [
        'provider' => $row['provider'],
        'key' => $row['key'],
        'environment' => $row['environment'],
        'enabled' => (bool) $row['enabled'],
        'configured' => true,
        'updatedAt' => $row['updated_at'],
    ], $rows);
}

function request_ip_address(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
        $value = trim((string) ($_SERVER[$key] ?? ''));
        if ($value === '') continue;
        $ip = trim(explode(',', $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
    }
    return '';
}

function india_detection_signals(array $clientHints = []): array
{
    $signals = [];
    $countryHeaders = [
        'HTTP_CF_IPCOUNTRY',
        'HTTP_X_VERCEL_IP_COUNTRY',
        'HTTP_CLOUDFRONT_VIEWER_COUNTRY',
        'HTTP_X_APP_COUNTRY',
        'HTTP_X_COUNTRY_CODE',
        'GEOIP_COUNTRY_CODE',
    ];
    foreach ($countryHeaders as $header) {
        $value = strtoupper(trim((string) ($_SERVER[$header] ?? '')));
        if ($value !== '') $signals[] = ['source' => $header, 'value' => $value, 'india' => $value === 'IN'];
    }

    $acceptLanguage = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    if ($acceptLanguage !== '') $signals[] = ['source' => 'HTTP_ACCEPT_LANGUAGE', 'value' => $acceptLanguage, 'india' => (bool) preg_match('/\b[a-z]{2}-in\b|\bhi\b|\bta-in\b|\bte-in\b|\bnb-in\b|\bmr-in\b/', $acceptLanguage)];

    $locale = strtolower((string) ($clientHints['locale'] ?? ''));
    if ($locale !== '') $signals[] = ['source' => 'client.locale', 'value' => $locale, 'india' => str_ends_with($locale, '-in') || $locale === 'hi'];

    $languages = strtolower(implode(',', is_array($clientHints['languages'] ?? null) ? $clientHints['languages'] : []));
    if ($languages !== '') $signals[] = ['source' => 'client.languages', 'value' => $languages, 'india' => (bool) preg_match('/\b[a-z]{2}-in\b|\bhi\b|\bta-in\b|\bte-in\b|\bnb-in\b|\bmr-in\b/', $languages)];

    $timezone = strtolower((string) ($clientHints['timezone'] ?? ''));
    if ($timezone !== '') $signals[] = ['source' => 'client.timezone', 'value' => $timezone, 'india' => $timezone === 'asia/kolkata' || $timezone === 'asia/calcutta'];

    $geoUrl = provider_config_value('IP_INTELLIGENCE_API_URL', 'geoip', 'api_url');
    $geoKey = provider_config_value('IP_INTELLIGENCE_API_KEY', 'geoip', 'api_key');
    $ip = request_ip_address();
    if ($geoUrl && $geoKey && $ip) {
        try {
            $result = http_request_json(str_replace('{ip}', rawurlencode($ip), $geoUrl), [
                'headers' => ['Authorization' => 'Bearer ' . $geoKey],
                'timeout' => 4,
            ]);
            $country = strtoupper((string) ($result['country'] ?? $result['country_code'] ?? $result['countryCode'] ?? ''));
            $isProxy = (bool) ($result['proxy'] ?? $result['vpn'] ?? $result['hosting'] ?? $result['threat']['is_proxy'] ?? false);
            if ($country !== '') $signals[] = ['source' => 'ip_intelligence', 'value' => $country . ($isProxy ? ':proxy' : ''), 'india' => $country === 'IN'];
        } catch (Throwable) {
            $signals[] = ['source' => 'ip_intelligence', 'value' => 'unavailable', 'india' => false];
        }
    }
    return $signals;
}

function paypal_restricted_for_india(?array $session = null, array $clientHints = []): array
{
    if (($session['user']['role'] ?? '') === 'admin') return ['restricted' => false, 'adminExempt' => true, 'signals' => []];
    $signals = india_detection_signals($clientHints);
    $india = array_values(array_filter($signals, fn($signal) => !empty($signal['india'])));
    return ['restricted' => count($india) > 0, 'adminExempt' => false, 'signals' => $signals];
}

function active_ads(): array
{
    $rows = Database::pdo()->query("
        SELECT ad_campaigns.*,
          SUM(CASE WHEN ad_events.event_type = 'impression' THEN 1 ELSE 0 END) AS impressions,
          SUM(CASE WHEN ad_events.event_type = 'click' THEN 1 ELSE 0 END) AS clicks
        FROM ad_campaigns
        LEFT JOIN ad_events ON ad_events.campaign_id = ad_campaigns.id
        WHERE ad_campaigns.status = 'active'
        GROUP BY ad_campaigns.id
        ORDER BY ad_campaigns.updated_at DESC
    ")->fetchAll();
    return array_map(fn($row) => [
        'id' => $row['id'],
        'name' => $row['name'],
        'placement' => $row['placement'],
        'sponsor' => $row['sponsor'],
        'headline' => $row['headline'],
        'targetUrl' => $row['target_url'],
        'creativeUrl' => $row['creative_url'],
        'targetTopics' => parse_json_field($row['target_topics'] ?? '[]', []),
        'status' => $row['status'],
        'budget' => (int) $row['budget'],
        'cpm' => (int) $row['cpm'],
        'impressions' => (int) ($row['impressions'] ?? 0),
        'clicks' => (int) ($row['clicks'] ?? 0),
    ], $rows);
}

function stored_translations(): array
{
    $rows = Database::pdo()->query("SELECT * FROM story_translations WHERE status = 'ready'")->fetchAll();
    $map = [];
    foreach ($rows as $row) {
        $map[$row['story_slug']] ??= [];
        $map[$row['story_slug']][$row['locale']] = [
            'storySlug' => $row['story_slug'],
            'locale' => $row['locale'],
            'title' => $row['title'],
            'dek' => $row['dek'],
            'body' => parse_json_field($row['body_json'], []),
            'contentHtml' => $row['content_html'],
            'seo' => parse_json_field($row['seo_json'], []),
            'status' => $row['status'],
            'updatedAt' => $row['updated_at'],
        ];
    }
    return $map;
}

function configured_translation_languages(): array
{
    $settings = document_value('platform-settings', []);
    $configured = is_array($settings['translationLanguages'] ?? null) ? $settings['translationLanguages'] : [];
    $clean = [];
    foreach ($configured as $item) {
        $locale = trim((string) ($item['locale'] ?? ''));
        $language = trim((string) ($item['language'] ?? $item['label'] ?? ''));
        if ($locale !== 'en-IN' && preg_match('/^[a-z]{2,3}(-[A-Z]{2})?$/', $locale) && $language) {
            $clean[] = ['locale' => $locale, 'language' => $language];
        }
    }
    return $clean ?: [
        ['locale' => 'hi-IN', 'language' => 'Hindi'],
        ['locale' => 'es-ES', 'language' => 'Spanish'],
        ['locale' => 'ar-SA', 'language' => 'Arabic'],
    ];
}

function base64url_encode_value(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64url_decode_value(string $value): string
{
    $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    return is_string($decoded) ? $decoded : '';
}

function webauthn_expected_origin(): string
{
    $origin = parse_url(app_origin());
    $scheme = $origin['scheme'] ?? (is_production() ? 'https' : 'http');
    $host = $origin['host'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $port = isset($origin['port']) ? ':' . $origin['port'] : '';
    return $scheme . '://' . $host . $port;
}

function webauthn_rp_id(): string
{
    $host = parse_url(app_origin(), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return preg_replace('/:\d+$/', '', (string) $host) ?: 'localhost';
}

function cbor_read_length(string $data, int &$offset, int $additional): int
{
    if ($additional < 24) return $additional;
    $sizes = [24 => 1, 25 => 2, 26 => 4, 27 => 8];
    $size = $sizes[$additional] ?? 0;
    if (!$size || $offset + $size > strlen($data)) throw new RuntimeException('Invalid CBOR length.');
    $bytes = substr($data, $offset, $size);
    $offset += $size;
    $value = 0;
    for ($i = 0; $i < strlen($bytes); $i++) $value = ($value << 8) | ord($bytes[$i]);
    return $value;
}

function cbor_decode_value(string $data, int &$offset): mixed
{
    if ($offset >= strlen($data)) throw new RuntimeException('Invalid CBOR data.');
    $initial = ord($data[$offset++]);
    $major = $initial >> 5;
    $additional = $initial & 0x1f;
    return match ($major) {
        0 => cbor_read_length($data, $offset, $additional),
        1 => -1 - cbor_read_length($data, $offset, $additional),
        2 => (function () use ($data, &$offset, $additional) {
            $length = cbor_read_length($data, $offset, $additional);
            $value = substr($data, $offset, $length);
            $offset += $length;
            return $value;
        })(),
        3 => (function () use ($data, &$offset, $additional) {
            $length = cbor_read_length($data, $offset, $additional);
            $value = substr($data, $offset, $length);
            $offset += $length;
            return $value;
        })(),
        4 => (function () use ($data, &$offset, $additional) {
            $length = cbor_read_length($data, $offset, $additional);
            $items = [];
            for ($i = 0; $i < $length; $i++) $items[] = cbor_decode_value($data, $offset);
            return $items;
        })(),
        5 => (function () use ($data, &$offset, $additional) {
            $length = cbor_read_length($data, $offset, $additional);
            $map = [];
            for ($i = 0; $i < $length; $i++) $map[cbor_decode_value($data, $offset)] = cbor_decode_value($data, $offset);
            return $map;
        })(),
        7 => $additional === 20 ? false : ($additional === 21 ? true : null),
        default => throw new RuntimeException('Unsupported CBOR value.'),
    };
}

function extract_webauthn_public_key(string $attestationObject): array
{
    $offset = 0;
    $attestation = cbor_decode_value($attestationObject, $offset);
    $authData = is_array($attestation) ? (string) ($attestation['authData'] ?? '') : '';
    if (strlen($authData) < 55) throw new RuntimeException('Passkey attestation is missing authenticator data.');
    $flags = ord($authData[32]);
    if (($flags & 0x40) === 0) throw new RuntimeException('Passkey attestation does not include credential data.');
    $credentialIdLength = unpack('n', substr($authData, 53, 2))[1];
    $credentialOffset = 55;
    $credentialId = substr($authData, $credentialOffset, $credentialIdLength);
    $coseOffset = $credentialOffset + $credentialIdLength;
    $coseData = substr($authData, $coseOffset);
    $nestedOffset = 0;
    $cose = cbor_decode_value($coseData, $nestedOffset);
    if (!is_array($cose) || ($cose[1] ?? null) !== 2 || ($cose[3] ?? null) !== -7 || ($cose[-1] ?? null) !== 1) {
        throw new RuntimeException('Only ES256 platform passkeys are supported.');
    }
    if (strlen((string) ($cose[-2] ?? '')) !== 32 || strlen((string) ($cose[-3] ?? '')) !== 32) {
        throw new RuntimeException('Passkey public key is invalid.');
    }
    return [
        'alg' => -7,
        'credentialId' => base64url_encode_value($credentialId),
        'x' => base64url_encode_value((string) $cose[-2]),
        'y' => base64url_encode_value((string) $cose[-3]),
    ];
}

function webauthn_public_key_pem(array $publicKey): string
{
    $x = base64url_decode_value((string) ($publicKey['x'] ?? ''));
    $y = base64url_decode_value((string) ($publicKey['y'] ?? ''));
    if (strlen($x) !== 32 || strlen($y) !== 32) throw new RuntimeException('Stored passkey public key is invalid.');
    $spki = hex2bin('3059301306072a8648ce3d020106082a8648ce3d03010703420004') . $x . $y;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

function security_policy_value(string $key, mixed $fallback): mixed
{
    try {
        $stmt = Database::pdo()->prepare('SELECT value_json FROM security_policies WHERE key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? parse_json_field($row['value_json'], $fallback) : $fallback;
    } catch (Throwable) {
        return $fallback;
    }
}

function password_policy_error(string $password): ?string
{
    $policy = security_policy_value('password', ['minLength' => 10, 'uppercase' => false, 'number' => false, 'symbol' => false]);
    $minLength = max(10, (int) ($policy['minLength'] ?? $policy['minimumLength'] ?? 10));
    if (strlen($password) < $minLength) return "Use at least {$minLength} characters for the password.";
    if (!empty($policy['uppercase']) && !preg_match('/[A-Z]/', $password)) return 'Add at least one uppercase letter.';
    if (!empty($policy['number']) && !preg_match('/[0-9]/', $password)) return 'Add at least one number.';
    if (!empty($policy['symbol']) && !preg_match('/[^A-Za-z0-9]/', $password)) return 'Add at least one symbol.';
    return null;
}

function http_request_json(string $url, array $options = []): array
{
    $method = strtoupper((string) ($options['method'] ?? 'GET'));
    $headers = $options['headers'] ?? [];
    $body = $options['body'] ?? null;
    $timeout = (int) ($options['timeout'] ?? 30);
    $headerLines = [];
    foreach ($headers as $key => $value) $headerLines[] = is_int($key) ? $value : $key . ': ' . $value;

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_TIMEOUT => $timeout,
        ]);
        if ($body !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, is_string($body) ? $body : http_build_query($body));
        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
    } else {
        $context = stream_context_create(['http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'content' => $body === null ? '' : (is_string($body) ? $body : http_build_query($body)),
            'timeout' => $timeout,
            'ignore_errors' => true,
        ]]);
        $raw = file_get_contents($url, false, $context);
        $status = 0;
        $error = '';
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('/^HTTP\/\S+\s+(\d+)/', $line, $m)) {
                $status = (int) $m[1];
                break;
            }
        }
    }

    if ($raw === false) throw new RuntimeException($error ?: 'HTTP request failed');
    $decoded = json_decode($raw, true);
    if ($status >= 400) {
        $message = is_array($decoded) ? json_encode($decoded, JSON_UNESCAPED_SLASHES) : substr($raw, 0, 500);
        throw new RuntimeException('Provider request failed: ' . $message);
    }
    return is_array($decoded) ? $decoded : ['raw' => $raw, 'status' => $status];
}

function base32_encode_value(string $bytes): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($bytes) as $char) $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    $output = '';
    foreach (str_split($bits, 5) as $chunk) $output .= $alphabet[bindec(str_pad($chunk, 5, '0'))];
    return $output;
}

function base32_decode_value(string $value): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $clean = preg_replace('/[^A-Z2-7]/', '', strtoupper($value));
    $bits = '';
    foreach (str_split($clean ?: '') as $char) {
        $index = strpos($alphabet, $char);
        if ($index === false) continue;
        $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
    }
    $bytes = '';
    foreach (str_split($bits, 8) as $chunk) {
        if (strlen($chunk) === 8) $bytes .= chr(bindec($chunk));
    }
    return $bytes;
}

function totp_code(string $secret, ?int $time = null): string
{
    $counter = intdiv($time ?? time(), 30);
    $key = base32_decode_value($secret);
    $binaryCounter = pack('N*', 0, $counter);
    $hash = hash_hmac('sha1', $binaryCounter, $key, true);
    $offset = ord(substr($hash, -1)) & 0x0f;
    $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;
    return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
}

function verify_totp_value(string $secret, string $code): bool
{
    $clean = preg_replace('/\s+/', '', $code);
    if (!preg_match('/^\d{6}$/', $clean)) return false;
    foreach ([-30, 0, 30] as $offset) {
        if (hash_equals(totp_code($secret, time() + $offset), $clean)) return true;
    }
    return false;
}

function send_email_message(string $to, string $subject, array $payload): bool
{
    if (!provider_status()['email']) return false;
    $templateKey = (string) ($payload['templateKey'] ?? $payload['type'] ?? '');
    if ($templateKey !== '') {
        try {
            $stmt = Database::pdo()->prepare('SELECT subject, body FROM email_templates WHERE key = ? AND enabled = 1 LIMIT 1');
            $stmt->execute([$templateKey]);
            if ($template = $stmt->fetch()) {
                $replacements = [];
                foreach ($payload as $key => $value) {
                    if (is_scalar($value) || $value === null) $replacements['{{' . $key . '}}'] = (string) $value;
                }
                $subject = strtr($template['subject'] ?: $subject, $replacements);
                $payload['html'] = strtr($template['body'], $replacements);
                $payload['templateApplied'] = $templateKey;
            }
        } catch (Throwable) {
        }
    }
    http_request_json((string) provider_config_value('EMAIL_API_URL', 'email', 'api_url'), [
        'method' => 'POST',
        'headers' => [
            'Authorization' => 'Bearer ' . provider_config_value('EMAIL_API_KEY', 'email', 'api_key'),
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode(['to' => $to, 'subject' => $subject, 'data' => $payload], JSON_UNESCAPED_SLASHES),
    ]);
    return true;
}

function public_media_asset(array $row): array
{
    $variants = [];
    try {
        $stmt = Database::pdo()->prepare('SELECT variant, url, mime_type, width, height, size_bytes FROM media_variants WHERE media_asset_id = ? ORDER BY width ASC');
        $stmt->execute([$row['id']]);
        $variants = $stmt->fetchAll();
    } catch (Throwable) {
        $variants = [];
    }
    return [
        'id' => $row['id'],
        'originalName' => $row['original_name'],
        'url' => $row['url'],
        'mimeType' => $row['mime_type'],
        'sizeBytes' => (int) $row['size_bytes'],
        'width' => $row['width'] === null ? null : (int) $row['width'],
        'height' => $row['height'] === null ? null : (int) $row['height'],
        'altText' => $row['alt_text'],
        'variants' => $variants,
        'createdAt' => $row['created_at'],
    ];
}

function create_notification(?string $userId, string $type, string $title, string $body = '', string $url = '/'): void
{
    if (!$userId) return;
    try {
        Database::pdo()->prepare('INSERT INTO notifications (id, user_id, type, title, body, url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('NTF-'), $userId, substr($type, 0, 80), substr($title, 0, 180), substr($body, 0, 500), substr($url, 0, 500), now_iso()]);
    } catch (Throwable) {
    }
}

function create_invoice_for_payment(array $payment, array $user, array $metadata = []): ?array
{
    try {
        $pdo = Database::pdo();
        $exists = $pdo->prepare('SELECT * FROM billing_invoices WHERE payment_id = ?');
        $exists->execute([$payment['id']]);
        if ($row = $exists->fetch()) return $row;
        $issuedAt = now_iso();
        $taxSettings = document_value('tax-settings', ['gstin' => '', 'taxRate' => provider_config_value('INVOICE_TAX_RATE', 'billing', 'tax_rate', '0'), 'invoicePrefix' => 'INV']);
        $invoicePrefix = preg_replace('/[^A-Za-z0-9-]/', '', (string) ($taxSettings['invoicePrefix'] ?? 'INV')) ?: 'INV';
        $invoiceNumber = $invoicePrefix . '-' . gmdate('Ymd') . '-' . substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $payment['id']), -8);
        $taxRate = max(0, min(100, (float) ($taxSettings['taxRate'] ?? 0)));
        $taxAmount = (int) round((int) $payment['amount'] * $taxRate / 100);
        $metadata = array_merge(['taxRate' => $taxRate, 'gstin' => (string) ($taxSettings['gstin'] ?? '')], $metadata);
        $pdo->prepare('INSERT INTO billing_invoices (id, invoice_number, payment_id, user_id, amount, currency, tax_amount, billing_name, billing_email, status, issued_at, metadata_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('INV-'), $invoiceNumber, $payment['id'], $payment['user_id'] ?? $user['id'], (int) $payment['amount'], (string) $payment['currency'], $taxAmount, (string) ($metadata['billingName'] ?? $user['name'] ?? ''), (string) ($metadata['billingEmail'] ?? $user['email'] ?? ''), 'issued', $issuedAt, json_encode($metadata, JSON_UNESCAPED_SLASHES)]);
        $stmt = $pdo->prepare('SELECT * FROM billing_invoices WHERE invoice_number = ?');
        $stmt->execute([$invoiceNumber]);
        return $stmt->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

function record_story_revision(array $story, ?string $authorUserId, string $note = ''): void
{
    try {
        $pdo = Database::pdo();
        $slug = (string) ($story['slug'] ?? '');
        if ($slug === '') return;
        $stmt = $pdo->prepare('SELECT COALESCE(MAX(revision_number), 0) + 1 AS next_revision FROM post_revisions WHERE story_slug = ?');
        $stmt->execute([$slug]);
        $revision = (int) ($stmt->fetch()['next_revision'] ?? 1);
        $pdo->prepare('INSERT INTO post_revisions (id, story_id, story_slug, revision_number, author_user_id, snapshot_json, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('REV-'), (string) ($story['id'] ?? $slug), $slug, $revision, $authorUserId, json_encode($story, JSON_UNESCAPED_SLASHES), substr($note, 0, 500), now_iso()]);
    } catch (Throwable) {
    }
}

function current_publication_rows(): array
{
    $rows = Database::pdo()->query("SELECT * FROM publications WHERE status != 'archived' ORDER BY name")->fetchAll();
    if ($rows) return $rows;
    $seen = [];
    foreach (document_value('stories', []) as $story) {
        $name = trim((string) ($story['publication'] ?? ''));
        if ($name === '' || isset($seen[$name])) continue;
        $seen[$name] = [
            'id' => 'derived-' . strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')),
            'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-')),
            'name' => $name,
            'description' => 'Publication derived from published stories.',
            'logo_url' => '',
            'status' => 'active',
            'owner_user_id' => null,
            'created_at' => $story['createdAt'] ?? now_iso(),
            'updated_at' => $story['updatedAt'] ?? now_iso(),
        ];
    }
    return array_values($seen);
}

function publication_member_role(string $publicationNameOrSlug, string $userId): ?string
{
    if ($publicationNameOrSlug === '' || $userId === '') return null;
    $stmt = Database::pdo()->prepare('SELECT publication_members.role FROM publication_members JOIN publications ON publications.id = publication_members.publication_id WHERE publication_members.user_id = ? AND (publications.slug = ? OR publications.name = ?) AND publications.status != ? LIMIT 1');
    $stmt->execute([$userId, $publicationNameOrSlug, $publicationNameOrSlug, 'archived']);
    $row = $stmt->fetch();
    return $row ? (string) $row['role'] : null;
}

function can_manage_publication_content(array $session, string $publication): bool
{
    if (($session['user']['role'] ?? '') === 'admin') return true;
    return in_array(publication_member_role($publication, (string) ($session['user']['id'] ?? '')), ['owner', 'editor'], true);
}

function sitemap_xml(): string
{
    $origin = rtrim(app_origin(), '/');
    $urls = [['loc' => $origin . '/', 'lastmod' => now_iso()], ['loc' => $origin . '/pricing', 'lastmod' => now_iso()]];
    foreach (document_value('stories', []) as $story) {
        if (($story['status'] ?? '') !== 'published') continue;
        $urls[] = ['loc' => $origin . '/stories/' . rawurlencode((string) $story['slug']), 'lastmod' => (string) ($story['updatedAt'] ?? $story['publishedAt'] ?? now_iso())];
    }
    foreach (document_value('categories', []) as $category) {
        $urls[] = ['loc' => $origin . '/category/' . rawurlencode((string) $category['slug']), 'lastmod' => (string) ($category['updatedAt'] ?? now_iso())];
    }
    foreach (current_publication_rows() as $publication) {
        if (($publication['status'] ?? '') === 'active') {
            $urls[] = ['loc' => $origin . '/publications/' . rawurlencode((string) $publication['slug']), 'lastmod' => (string) ($publication['updated_at'] ?? now_iso())];
        }
    }
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $url) $xml .= "  <url><loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc><lastmod>" . htmlspecialchars($url['lastmod'], ENT_XML1) . "</lastmod></url>\n";
    return $xml . "</urlset>\n";
}

function seo_artifact_content(string $key): ?array
{
    try {
        $stmt = Database::pdo()->prepare('SELECT content, mime_type FROM seo_artifacts WHERE key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (!$row || trim((string) ($row['content'] ?? '')) === '') return null;
        return ['content' => (string) $row['content'], 'mimeType' => (string) ($row['mime_type'] ?? 'text/plain; charset=utf-8')];
    } catch (Throwable) {
        return null;
    }
}

function create_password_reset_token(array $user): array
{
    $pdo = Database::pdo();
    $token = base64url_encode_value(random_bytes(32));
    $now = now_iso();
    $expires = gmdate('Y-m-d\TH:i:s.v\Z', time() + 3600);
    $pdo->prepare("UPDATE security_tokens SET consumed_at = ? WHERE user_id = ? AND type = 'password_recovery' AND consumed_at IS NULL")
        ->execute([$now, $user['id']]);
    $pdo->prepare("INSERT INTO security_tokens (id, user_id, token_hash, type, expires_at, created_at) VALUES (?, ?, ?, 'password_recovery', ?, ?)")
        ->execute([uuid_value('TOK-'), $user['id'], hash('sha256', $token), $expires, $now]);
    return ['token' => $token, 'url' => app_origin() . '/?reset_token=' . rawurlencode($token), 'expiresAt' => $expires];
}

function process_paid_provider_event(string $provider, string $paymentId, string $providerPaymentId = ''): bool
{
    $pdo = Database::pdo();
    if ($paymentId === '' && $providerPaymentId !== '') {
        $stmt = $pdo->prepare('SELECT id FROM payments WHERE provider = ? AND (provider_payment_id = ? OR provider_order_id = ?)');
        $stmt->execute([$provider, $providerPaymentId, $providerPaymentId]);
        $paymentId = (string) (($stmt->fetch()['id'] ?? ''));
    }
    if ($paymentId === '') return false;
    return activate_paid_payment($paymentId, $providerPaymentId) !== null;
}

function process_subscription_provider_event(string $provider, string $eventType, string $providerSubscriptionId, array $payload): bool
{
    if ($providerSubscriptionId === '') return false;
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE provider = ? AND provider_subscription_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$provider, $providerSubscriptionId]);
    $subscription = $stmt->fetch();
    if (!$subscription) {
        $paymentStmt = $pdo->prepare('SELECT * FROM payments WHERE provider = ? AND provider_order_id = ? ORDER BY created_at DESC LIMIT 1');
        $paymentStmt->execute([$provider, $providerSubscriptionId]);
        $payment = $paymentStmt->fetch();
        if ($payment && preg_match('/active|authenticated|charged|paid|completed|success/i', $eventType . ' ' . json_encode($payload))) {
            activate_paid_payment($payment['id'], $providerSubscriptionId);
            return true;
        }
        return false;
    }

    $nextStatus = null;
    $text = strtolower($eventType . ' ' . json_encode($payload));
    if (preg_match('/cancel|cancelled|canceled|expired/', $text)) $nextStatus = 'cancelled';
    elseif (preg_match('/past_due|payment_failed|failed|halted|suspended/', $text)) $nextStatus = 'past_due';
    elseif (preg_match('/active|authenticated|charged|paid|renewed|completed|success/', $text)) $nextStatus = 'active';
    if (!$nextStatus) return false;

    $now = now_iso();
    $endsAt = $subscription['ends_at'];
    if ($nextStatus === 'active' && preg_match('/charged|renewed|paid|completed|success/', $text)) {
        $endsAt = gmdate('Y-m-d\TH:i:s.v\Z', time() + 30 * 86400);
    }
    $pdo->prepare('UPDATE subscriptions SET status = ?, ends_at = ?, updated_at = ? WHERE id = ?')->execute([$nextStatus, $endsAt, $now, $subscription['id']]);
    $pdo->prepare('INSERT INTO subscription_events (id, subscription_id, user_id, provider, event_type, status_before, status_after, payload_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value('SEV-'), $subscription['id'], $subscription['user_id'], $provider, substr($eventType, 0, 120), $subscription['status'], $nextStatus, json_encode($payload, JSON_UNESCAPED_SLASHES), $now]);
    if ($nextStatus === 'active') {
        $planName = (string) ($payload['planName'] ?? $subscription['plan_id']);
        $pdo->prepare('UPDATE users SET subscription = CASE WHEN subscription = ? THEN ? ELSE subscription END, updated_at = ? WHERE id = ?')
            ->execute(['Free', $planName ?: 'Premium', $now, $subscription['user_id']]);
        create_notification($subscription['user_id'], 'subscription_renewed', 'Membership renewed', 'Your membership renewal was confirmed.', '/dashboard');
    } elseif (in_array($nextStatus, ['cancelled', 'past_due'], true)) {
        if ($nextStatus === 'cancelled') $pdo->prepare("UPDATE users SET subscription = 'Free', updated_at = ? WHERE id = ?")->execute([$now, $subscription['user_id']]);
        create_notification($subscription['user_id'], 'subscription_' . $nextStatus, $nextStatus === 'cancelled' ? 'Membership cancelled' : 'Payment issue needs attention', $nextStatus === 'cancelled' ? 'Your recurring membership was cancelled by the payment provider.' : 'Your membership renewal failed. Please update your payment method.', $nextStatus === 'cancelled' ? '/dashboard' : '/pricing');
    }
    return true;
}

function store_payment_webhook(string $provider, string $eventType, string $paymentId, string $providerPaymentId, bool $valid, array $payload, string $status): void
{
    Database::pdo()->prepare('INSERT INTO payment_webhooks (id, provider, event_type, payment_id, provider_payment_id, signature_valid, payload_json, processed_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value('WH-'), $provider, $eventType, $paymentId ?: null, $providerPaymentId ?: null, $valid ? 1 : 0, json_encode($payload, JSON_UNESCAPED_SLASHES), now_iso(), $status]);
}

function ecdsa_der_to_raw(string $der, int $partLength = 32): string
{
    $offset = 3;
    if (ord($der[1]) & 0x80) $offset = 3 + (ord($der[1]) & 0x7f);
    if (ord($der[$offset]) !== 0x02) return $der;
    $rLength = ord($der[$offset + 1]);
    $r = substr($der, $offset + 2, $rLength);
    $offset += 2 + $rLength;
    if (ord($der[$offset]) !== 0x02) return $der;
    $sLength = ord($der[$offset + 1]);
    $s = substr($der, $offset + 2, $sLength);
    $r = str_pad(ltrim($r, "\x00"), $partLength, "\x00", STR_PAD_LEFT);
    $s = str_pad(ltrim($s, "\x00"), $partLength, "\x00", STR_PAD_LEFT);
    return $r . $s;
}

function send_push_message(array $subscription, array $payload): bool
{
    $subscriptionJson = parse_json_field($subscription['subscription_json'] ?? '{}', []);
    $endpoint = (string) ($subscriptionJson['endpoint'] ?? $subscription['endpoint'] ?? '');
    if ($endpoint === '') return false;
    $pushApiUrl = provider_config_value('WEB_PUSH_API_URL', 'push', 'api_url');
    $pushApiKey = provider_config_value('WEB_PUSH_API_KEY', 'push', 'api_key');
    if ($pushApiUrl && $pushApiKey) {
        http_request_json($pushApiUrl, [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $pushApiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['subscription' => $subscriptionJson, 'payload' => $payload], JSON_UNESCAPED_SLASHES),
        ]);
        return true;
    }
    $vapidPrivate = provider_config_value('VAPID_PRIVATE_KEY', 'push', 'vapid_private_key');
    $vapidPublic = provider_config_value('VAPID_PUBLIC_KEY', 'push', 'vapid_public_key');
    if (!$vapidPrivate || !$vapidPublic) return false;
    $audience = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);
    $privateKey = str_replace(['-', '_'], ['+', '/'], $vapidPrivate);
    $privateKey = str_pad($privateKey, strlen($privateKey) + (4 - strlen($privateKey) % 4) % 4, '=');
    $pem = "-----BEGIN EC PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . "-----END EC PRIVATE KEY-----\n";
    $jwtHeader = base64url_encode_value(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $jwtBody = base64url_encode_value(json_encode(['aud' => $audience, 'exp' => time() + 3600, 'sub' => 'mailto:' . (getenv('SUPPORT_EMAIL') ?: 'admin@inkriver.local')]));
    $signature = '';
    if (!openssl_sign($jwtHeader . '.' . $jwtBody, $signature, $pem, OPENSSL_ALGO_SHA256)) return false;
    $jwt = $jwtHeader . '.' . $jwtBody . '.' . base64url_encode_value(ecdsa_der_to_raw($signature));
    $headers = [
        'Authorization' => 'WebPush ' . $jwt,
        'Crypto-Key' => 'p256ecdsa=' . $vapidPublic,
        'TTL' => '3600',
        'Content-Length' => '0',
    ];
    http_request_json($endpoint, ['method' => 'POST', 'headers' => $headers, 'timeout' => 10]);
    return true;
}

function openai_text_response(string $prompt, string $system = 'You are a careful editorial assistant.'): string
{
    $openaiKey = provider_config_value('OPENAI_API_KEY', 'openai', 'api_key');
    if ($openaiKey) {
        $payload = [
            'model' => provider_config_value('OPENAI_MODEL', 'openai', 'model', 'gpt-4.1-mini'),
            'input' => [
                ['role' => 'system', 'content' => [['type' => 'input_text', 'text' => $system]]],
                ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => $prompt]]],
            ],
        ];
        $result = http_request_json(provider_config_value('OPENAI_API_URL', 'openai', 'api_url', 'https://api.openai.com/v1/responses') ?: 'https://api.openai.com/v1/responses', [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'timeout' => 60,
        ]);
        if (isset($result['output_text'])) return trim((string) $result['output_text']);
        $text = '';
        foreach (($result['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text') $text .= (string) ($content['text'] ?? '');
            }
        }
        return trim($text);
    }

    $result = http_request_json((string) provider_config_value('AI_API_URL', 'ai', 'api_url'), [
        'method' => 'POST',
        'headers' => [
            'Authorization' => 'Bearer ' . provider_config_value('AI_API_KEY', 'ai', 'api_key'),
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode(['prompt' => $prompt, 'system' => $system], JSON_UNESCAPED_SLASHES),
        'timeout' => 60,
    ]);
    return trim((string) ($result['result'] ?? $result['text'] ?? $result['output'] ?? ''));
}

function story_source_hash(array $story): string
{
    return hash('sha256', json_encode([
        'title' => $story['title'] ?? '',
        'dek' => $story['dek'] ?? $story['subtitle'] ?? '',
        'body' => $story['body'] ?? [],
        'contentHtml' => $story['contentHtml'] ?? '',
        'seo' => $story['seo'] ?? [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function translate_story_with_openai(array $story, array $language): array
{
    $prompt = "Translate this article to {$language['language']} ({$language['locale']}). Return strict JSON with keys title, dek, body, contentHtml, seo. Preserve HTML tags and URLs.\n\n" .
        json_encode([
            'title' => $story['title'] ?? '',
            'dek' => $story['dek'] ?? $story['subtitle'] ?? '',
            'body' => $story['body'] ?? [],
            'contentHtml' => $story['contentHtml'] ?? '',
            'seo' => $story['seo'] ?? [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $text = openai_text_response($prompt, 'You translate articles faithfully. Return only valid JSON.');
    $clean = trim(preg_replace('/^```(?:json)?|```$/m', '', $text));
    $decoded = json_decode($clean, true);
    if (!is_array($decoded)) throw new RuntimeException('Translation response was not valid JSON.');
    return $decoded;
}

function ensure_story_translations(array $story): array
{
    $pdo = Database::pdo();
    $slug = (string) ($story['slug'] ?? '');
    if ($slug === '') return ['slug' => '', 'created' => 0, 'failed' => 0];
    $hash = story_source_hash($story);
    $created = 0;
    $failed = 0;
    foreach (configured_translation_languages() as $language) {
        $stmt = $pdo->prepare('SELECT source_hash, status FROM story_translations WHERE story_slug = ? AND locale = ?');
        $stmt->execute([$slug, $language['locale']]);
        $existing = $stmt->fetch();
        if ($existing && $existing['source_hash'] === $hash && $existing['status'] === 'ready') continue;
        $now = now_iso();
        try {
            $translated = translate_story_with_openai($story, $language);
            $pdo->prepare("INSERT INTO story_translations (story_slug, locale, title, dek, body_json, content_html, seo_json, status, source_hash, provider, error, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'ready', ?, 'openai', '', ?, ?) ON CONFLICT(story_slug, locale) DO UPDATE SET title = excluded.title, dek = excluded.dek, body_json = excluded.body_json, content_html = excluded.content_html, seo_json = excluded.seo_json, status = 'ready', source_hash = excluded.source_hash, provider = 'openai', error = '', updated_at = excluded.updated_at")
                ->execute([$slug, $language['locale'], (string) ($translated['title'] ?? $story['title'] ?? ''), (string) ($translated['dek'] ?? $story['dek'] ?? ''), json_encode($translated['body'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), (string) ($translated['contentHtml'] ?? ''), json_encode($translated['seo'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $hash, $now, $now]);
            $created++;
        } catch (Throwable $error) {
            $pdo->prepare("INSERT INTO story_translations (story_slug, locale, title, dek, status, source_hash, provider, error, created_at, updated_at) VALUES (?, ?, '', '', 'failed', ?, 'openai', ?, ?, ?) ON CONFLICT(story_slug, locale) DO UPDATE SET status = 'failed', source_hash = excluded.source_hash, error = excluded.error, updated_at = excluded.updated_at")
                ->execute([$slug, $language['locale'], $hash, substr($error->getMessage(), 0, 1000), $now, $now]);
            $failed++;
        }
    }
    return ['slug' => $slug, 'created' => $created, 'failed' => $failed];
}

function rec_add_weight(array &$map, string $key, float $amount): void
{
    $key = trim($key);
    if ($key === '') return;
    $map[$key] = max(-100, min(180, (float) ($map[$key] ?? 0) + $amount));
}

function rec_story_tags(array $story): array
{
    return array_values(array_unique(array_filter(array_merge([(string) ($story['topic'] ?? '')], is_array($story['tags'] ?? null) ? $story['tags'] : []))));
}

function rec_signal_weight(string $eventType): float
{
    return [
        'open' => 1.0,
        'read_midpoint' => 2.2,
        'read_complete' => 5.0,
        'complete' => 5.0,
        'long_read' => 3.4,
        'clap' => 3.8,
        'save' => 7.0,
        'share' => 4.0,
        'follow' => 5.5,
        'interest_added' => 10.0,
        'interest_removed' => -12.0,
        'unsave' => -5.0,
        'not_interested' => -14.0,
        'less_like_this' => -9.0,
        'more_like_this' => 9.0,
        'hide_topic' => -16.0,
        'unfollow' => -7.0,
    ][$eventType] ?? 0.8;
}

function recommendation_story_catalog(): array
{
    $stories = array_values(array_filter(document_value('stories', []), fn($story) => ($story['status'] ?? 'published') === 'published'));
    if ($stories) return $stories;
    return [
        ['slug' => 'inside-the-new-reader-economy', 'title' => 'Inside the new reader economy', 'topic' => 'Marketing', 'author' => 'Meera Iyer', 'tags' => ['publishing', 'memberships', 'audience'], 'reads' => 21800, 'claps' => 1840, 'publishedAt' => '2026-06-01T00:00:00.000Z'],
        ['slug' => 'building-a-practical-ai-content-desk', 'title' => 'Building a practical AI content desk', 'topic' => 'AI', 'author' => 'Arjun Rao', 'tags' => ['artificial intelligence', 'workflow', 'content'], 'reads' => 14600, 'claps' => 920, 'publishedAt' => '2026-06-02T00:00:00.000Z'],
        ['slug' => 'why-small-publications-win-loyalty', 'title' => 'Why small publications win loyalty', 'topic' => 'Startups', 'author' => 'Naina Kapoor', 'tags' => ['community', 'publishing', 'loyalty'], 'reads' => 17400, 'claps' => 1320, 'publishedAt' => '2026-06-03T00:00:00.000Z'],
        ['slug' => 'the-clean-dashboard-test', 'title' => 'The clean dashboard test', 'topic' => 'Design', 'author' => 'Dev Shah', 'tags' => ['user experience', 'dashboards', 'systems'], 'reads' => 9800, 'claps' => 760, 'publishedAt' => '2026-06-04T00:00:00.000Z'],
        ['slug' => 'the-indian-creator-business-stack', 'title' => 'The Indian creator business stack', 'topic' => 'India', 'author' => 'Kavya Menon', 'tags' => ['india', 'creators', 'payments'], 'reads' => 15800, 'claps' => 1160, 'publishedAt' => '2026-06-05T00:00:00.000Z'],
        ['slug' => 'a-calm-system-for-personal-finance', 'title' => 'A calm system for personal finance', 'topic' => 'Money', 'author' => 'Rohan Sen', 'tags' => ['personal finance', 'habits', 'planning'], 'reads' => 19600, 'claps' => 1480, 'publishedAt' => '2026-06-06T00:00:00.000Z'],
        ['slug' => 'what-communities-remember', 'title' => 'What communities remember', 'topic' => 'Culture', 'author' => 'Zoya Khan', 'tags' => ['community', 'media', 'culture'], 'reads' => 12100, 'claps' => 880, 'publishedAt' => '2026-06-07T00:00:00.000Z'],
        ['slug' => 'designing-ai-products-people-trust', 'title' => 'Designing AI products people trust', 'topic' => 'Design', 'author' => 'Leena Bose', 'tags' => ['artificial intelligence', 'trust', 'user experience'], 'reads' => 20400, 'claps' => 1730, 'publishedAt' => '2026-06-08T00:00:00.000Z'],
        ['slug' => 'the-seo-brief-that-writers-use', 'title' => 'The SEO brief writers actually use', 'topic' => 'Marketing', 'author' => 'Ishita Verma', 'tags' => ['seo', 'content strategy', 'writing'], 'reads' => 13900, 'claps' => 1040, 'publishedAt' => '2026-06-09T00:00:00.000Z'],
        ['slug' => 'from-side-project-to-small-company', 'title' => 'From side project to small company', 'topic' => 'Startups', 'author' => 'Kabir Malhotra', 'tags' => ['founders', 'operations', 'growth'], 'reads' => 16900, 'claps' => 1260, 'publishedAt' => '2026-06-10T00:00:00.000Z'],
        ['slug' => 'small-language-models-at-work', 'title' => 'Where small language models fit at work', 'topic' => 'AI', 'author' => 'Arjun Rao', 'tags' => ['artificial intelligence', 'business', 'automation'], 'reads' => 15100, 'claps' => 970, 'publishedAt' => '2026-06-11T00:00:00.000Z'],
        ['slug' => 'the-new-shape-of-indian-internet-culture', 'title' => 'The new shape of Indian internet culture', 'topic' => 'India', 'author' => 'Aditi Nair', 'tags' => ['india', 'culture', 'creators'], 'reads' => 18300, 'claps' => 1390, 'publishedAt' => '2026-06-12T00:00:00.000Z'],
    ];
}

function story_plain_text(array $story): string
{
    $body = is_array($story['body'] ?? null) ? implode(' ', $story['body']) : (string) ($story['body'] ?? '');
    $html = trim((string) ($story['contentHtml'] ?? ''));
    return trim($body . ' ' . strip_tags($html));
}

function fts_query(string $query): string
{
    $terms = preg_split('/\s+/', trim($query));
    $clean = [];
    foreach ($terms ?: [] as $term) {
        $term = preg_replace('/[^\p{L}\p{N}_-]+/u', '', $term);
        if ($term !== '') $clean[] = $term . '*';
    }
    return implode(' ', array_slice($clean, 0, 12));
}

function story_catalog_hash(array $stories): string
{
    $compact = [];
    foreach ($stories as $story) {
        if (($story['status'] ?? 'published') !== 'published' || empty($story['slug'])) continue;
        $compact[] = [
            'slug' => (string) $story['slug'],
            'title' => (string) ($story['title'] ?? ''),
            'dek' => (string) ($story['dek'] ?? ''),
            'author' => (string) ($story['author'] ?? ''),
            'publication' => (string) ($story['publication'] ?? $story['role'] ?? ''),
            'topic' => (string) ($story['topic'] ?? ''),
            'tags' => is_array($story['tags'] ?? null) ? array_values($story['tags']) : [],
            'body' => story_plain_text($story),
        ];
    }
    usort($compact, fn($a, $b) => $a['slug'] <=> $b['slug']);
    return hash('sha256', json_encode($compact, JSON_UNESCAPED_SLASHES));
}

function rebuild_story_search_index(?array $stories = null): int
{
    $pdo = Database::pdo();
    $stories ??= recommendation_story_catalog();
    try {
        $pdo->exec("CREATE VIRTUAL TABLE IF NOT EXISTS story_search_index USING fts5(slug UNINDEXED, title, dek, author, publication, topic, tags, body, tokenize='unicode61 remove_diacritics 2')");
        $pdo->exec('DELETE FROM story_search_index');
        $stmt = $pdo->prepare('INSERT INTO story_search_index (slug, title, dek, author, publication, topic, tags, body) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $count = 0;
        foreach ($stories as $story) {
            if (($story['status'] ?? 'published') !== 'published' || empty($story['slug'])) continue;
            $stmt->execute([
                (string) $story['slug'],
                (string) ($story['title'] ?? ''),
                (string) ($story['dek'] ?? ''),
                (string) ($story['author'] ?? ''),
                (string) ($story['publication'] ?? $story['role'] ?? ''),
                (string) ($story['topic'] ?? ''),
                implode(' ', is_array($story['tags'] ?? null) ? $story['tags'] : []),
                story_plain_text($story),
            ]);
            $count++;
        }
        $now = now_iso();
        $meta = ['hash' => story_catalog_hash($stories), 'count' => $count, 'rebuiltAt' => $now];
        $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute(['search-index-meta', json_encode($meta, JSON_UNESCAPED_SLASHES), null, $now]);
        return $count;
    } catch (Throwable) {
        return 0;
    }
}

function ensure_story_search_index(array $stories): void
{
    $pdo = Database::pdo();
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) AS count FROM story_search_index")->fetch()['count'];
        $meta = document_value('search-index-meta', []);
        if ($count > 0 && is_array($meta) && ($meta['hash'] ?? '') === story_catalog_hash($stories)) return;
    } catch (Throwable) {
        // Missing or incompatible FTS table. Rebuild once, then let the normal query path continue.
    }
    rebuild_story_search_index($stories);
}

function search_stories(array $params): array
{
    $pdo = Database::pdo();
    $stories = recommendation_story_catalog();
    $bySlug = [];
    foreach ($stories as $story) if (!empty($story['slug']) && (($story['status'] ?? 'published') === 'published')) $bySlug[$story['slug']] = $story;
    ensure_story_search_index($stories);

    $query = trim((string) ($params['q'] ?? ''));
    $topic = (string) ($params['topic'] ?? 'all');
    $author = (string) ($params['author'] ?? 'all');
    $access = (string) ($params['access'] ?? 'all');
    $maxMinutes = (string) ($params['maxMinutes'] ?? 'all');
    $sort = (string) ($params['sort'] ?? 'relevance');
    $limit = max(1, min(80, (int) ($params['limit'] ?? 40)));
    $scores = [];
    $slugs = [];

    try {
        if ($query !== '') {
            $fts = fts_query($query);
            if ($fts !== '') {
                $stmt = $pdo->prepare('SELECT slug, bm25(story_search_index, 8.0, 4.0, 2.0, 1.5, 2.0, 1.2, 1.0, 1.0) AS rank FROM story_search_index WHERE story_search_index MATCH ? ORDER BY rank LIMIT 200');
                $stmt->execute([$fts]);
                foreach ($stmt->fetchAll() as $row) {
                    $slugs[] = $row['slug'];
                    $scores[$row['slug']] = abs((float) $row['rank']);
                }
            }
        } else {
            $slugs = array_keys($bySlug);
        }
    } catch (Throwable) {
        $needle = strtolower($query);
        foreach ($bySlug as $slug => $story) {
            $haystack = strtolower(($story['title'] ?? '') . ' ' . ($story['dek'] ?? '') . ' ' . ($story['author'] ?? '') . ' ' . ($story['topic'] ?? '') . ' ' . implode(' ', $story['tags'] ?? []) . ' ' . story_plain_text($story));
            if ($needle === '' || str_contains($haystack, $needle)) {
                $slugs[] = $slug;
                $scores[$slug] = substr_count($haystack, $needle ?: ' ') + (str_contains(strtolower((string) ($story['title'] ?? '')), $needle) ? 10 : 0);
            }
        }
    }

    $results = [];
    foreach (array_values(array_unique($slugs)) as $slug) {
        if (!isset($bySlug[$slug])) continue;
        $story = $bySlug[$slug];
        if ($topic !== 'all' && ($story['topic'] ?? '') !== $topic) continue;
        if ($author !== 'all' && ($story['author'] ?? '') !== $author) continue;
        if ($access === 'free' && !empty($story['premium'])) continue;
        if ($access === 'premium' && empty($story['premium'])) continue;
        $minutes = (int) preg_replace('/\D+/', '', (string) ($story['readTime'] ?? '5'));
        if ($maxMinutes !== 'all' && $minutes > (int) $maxMinutes) continue;
        $results[] = [
            'slug' => $slug,
            'title' => (string) ($story['title'] ?? ''),
            'dek' => (string) ($story['dek'] ?? ''),
            'author' => (string) ($story['author'] ?? ''),
            'publication' => (string) ($story['publication'] ?? $story['role'] ?? 'InkRiver'),
            'topic' => (string) ($story['topic'] ?? ''),
            'readTime' => (string) ($story['readTime'] ?? '5 min read'),
            'premium' => (bool) ($story['premium'] ?? false),
            'tags' => is_array($story['tags'] ?? null) ? $story['tags'] : [],
            'imageUrl' => (string) ($story['imageUrl'] ?? ''),
            'publishedAt' => (string) ($story['publishedAt'] ?? ''),
            'reads' => (int) ($story['reads'] ?? 0),
            'claps' => (int) ($story['claps'] ?? 0),
            'searchScore' => (float) ($scores[$slug] ?? 0),
        ];
    }
    usort($results, function ($a, $b) use ($sort) {
        return match ($sort) {
            'popular' => ($b['reads'] + $b['claps']) <=> ($a['reads'] + $a['claps']),
            'newest' => strtotime($b['publishedAt'] ?: '1970-01-01') <=> strtotime($a['publishedAt'] ?: '1970-01-01'),
            'shortest' => ((int) $a['readTime']) <=> ((int) $b['readTime']),
            default => $b['searchScore'] <=> $a['searchScore'],
        };
    });
    return array_slice($results, 0, $limit);
}

function train_recommendations_for_user(string $userId): array
{
    $pdo = Database::pdo();
    $stories = recommendation_story_catalog();
    $storyBySlug = [];
    foreach ($stories as $story) if (!empty($story['slug'])) $storyBySlug[$story['slug']] = $story;

    $docStmt = $pdo->prepare('SELECT key, value_json FROM user_documents WHERE user_id = ?');
    $docStmt->execute([$userId]);
    $docs = [];
    foreach ($docStmt->fetchAll() as $row) $docs[$row['key']] = parse_json_field($row['value_json'], null);

    $profileDoc = is_array($docs['recommendation-profile'] ?? null) ? $docs['recommendation-profile'] : [];
    $following = is_array($docs['following'] ?? null) ? $docs['following'] : [];
    $saved = is_array($docs['saved'] ?? null) ? $docs['saved'] : [];
    $history = is_array($docs['reading-history'] ?? null) ? $docs['reading-history'] : [];

    $topicWeights = [];
    $tagWeights = [];
    $authorWeights = [];
    $storyWeights = [];
    $hiddenStories = [];
    $hiddenTopics = [];
    $hiddenAuthors = [];
    $signals = 0;

    foreach (($profileDoc['selectedInterests'] ?? []) as $topic) rec_add_weight($topicWeights, (string) $topic, 18);
    foreach (($profileDoc['topicScores'] ?? []) as $topic => $score) rec_add_weight($topicWeights, (string) $topic, (float) $score * 1.4);
    foreach (($profileDoc['tagScores'] ?? []) as $tag => $score) rec_add_weight($tagWeights, (string) $tag, (float) $score);
    foreach (($profileDoc['authorScores'] ?? []) as $author => $score) rec_add_weight($authorWeights, (string) $author, (float) $score);
    foreach (($profileDoc['storyScores'] ?? []) as $slug => $score) rec_add_weight($storyWeights, (string) $slug, (float) $score * 1.2);
    foreach (($profileDoc['hiddenStories'] ?? []) as $slug) $hiddenStories[(string) $slug] = true;

    foreach (($following['topics'] ?? []) as $topic) rec_add_weight($topicWeights, (string) $topic, 16);
    foreach (($following['tags'] ?? []) as $tag) rec_add_weight($tagWeights, (string) $tag, 11);
    foreach (($following['writers'] ?? []) as $author) rec_add_weight($authorWeights, (string) $author, 13);
    foreach (($saved ?: []) as $slug) {
        if (!isset($storyBySlug[$slug])) continue;
        $story = $storyBySlug[$slug];
        rec_add_weight($storyWeights, (string) $slug, 11);
        rec_add_weight($topicWeights, (string) ($story['topic'] ?? ''), 4);
        rec_add_weight($authorWeights, (string) ($story['author'] ?? ''), 2.5);
        foreach (rec_story_tags($story) as $tag) rec_add_weight($tagWeights, $tag, 2.5);
    }
    foreach ($history as $entry) {
        $slug = (string) ($entry['slug'] ?? '');
        if (!isset($storyBySlug[$slug])) continue;
        $progress = (float) ($entry['progress'] ?? 0);
        $weight = $progress >= 95 ? 6 : ($progress >= 50 ? 3 : 1);
        $story = $storyBySlug[$slug];
        rec_add_weight($storyWeights, $slug, $weight);
        rec_add_weight($topicWeights, (string) ($story['topic'] ?? ''), $weight * 0.8);
        foreach (rec_story_tags($story) as $tag) rec_add_weight($tagWeights, $tag, $weight * 0.4);
    }

    $eventStmt = $pdo->prepare('SELECT story_slug, event_type, metadata, value, created_at FROM engagement_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 1000');
    $eventStmt->execute([$userId]);
    foreach ($eventStmt->fetchAll() as $event) {
        $signals++;
        $type = (string) $event['event_type'];
        $weight = rec_signal_weight($type);
        $metadata = parse_json_field($event['metadata'] ?? '{}', []);
        $slug = (string) ($event['story_slug'] ?? '');
        $story = $storyBySlug[$slug] ?? null;
        $topic = (string) ($metadata['topic'] ?? ($story['topic'] ?? ''));
        $author = (string) ($metadata['author'] ?? ($story['author'] ?? ''));
        if ($type === 'interest_added' || $type === 'interest_removed') $topic = (string) ($metadata['topic'] ?? '');
        if ($type === 'hide_topic' && $topic) $hiddenTopics[$topic] = true;
        if ($type === 'unfollow' && ($metadata['type'] ?? '') === 'writers') $hiddenAuthors[(string) ($metadata['value'] ?? '')] = true;
        if (in_array($type, ['not_interested', 'less_like_this'], true) && $slug) $hiddenStories[$slug] = true;
        if ($type === 'more_like_this' && $slug) unset($hiddenStories[$slug]);
        rec_add_weight($topicWeights, $topic, $weight);
        rec_add_weight($authorWeights, $author, $weight * 0.45);
        if ($slug) rec_add_weight($storyWeights, $slug, $weight);
        $tags = is_array($metadata['tags'] ?? null) ? $metadata['tags'] : ($story ? rec_story_tags($story) : []);
        foreach ($tags as $tag) rec_add_weight($tagWeights, (string) $tag, $weight * 0.55);
    }

    $now = now_iso();
    $version = 2;
    $pdo->prepare('INSERT INTO recommendation_profiles (user_id, topic_weights_json, tag_weights_json, author_weights_json, story_weights_json, hidden_topics_json, hidden_authors_json, hidden_stories_json, model_version, signals_count, last_trained_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(user_id) DO UPDATE SET topic_weights_json = excluded.topic_weights_json, tag_weights_json = excluded.tag_weights_json, author_weights_json = excluded.author_weights_json, story_weights_json = excluded.story_weights_json, hidden_topics_json = excluded.hidden_topics_json, hidden_authors_json = excluded.hidden_authors_json, hidden_stories_json = excluded.hidden_stories_json, model_version = excluded.model_version, signals_count = excluded.signals_count, last_trained_at = excluded.last_trained_at, updated_at = excluded.updated_at')
        ->execute([$userId, json_encode($topicWeights), json_encode($tagWeights), json_encode($authorWeights), json_encode($storyWeights), json_encode(array_keys($hiddenTopics)), json_encode(array_keys($hiddenAuthors)), json_encode(array_keys($hiddenStories)), $version, $signals, $now, $now]);

    $pdo->prepare('DELETE FROM recommendation_story_scores WHERE user_id = ?')->execute([$userId]);
    $insert = $pdo->prepare('INSERT INTO recommendation_story_scores (user_id, story_slug, score, reason, factors_json, model_version, computed_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($stories as $story) {
        $slug = (string) ($story['slug'] ?? '');
        if ($slug === '') continue;
        $topic = (string) ($story['topic'] ?? '');
        $author = (string) ($story['author'] ?? '');
        $factors = [];
        $score = 0.0;
        if (isset($hiddenStories[$slug]) || isset($hiddenTopics[$topic]) || isset($hiddenAuthors[$author])) $score -= 250;
        $topicScore = (float) ($topicWeights[$topic] ?? 0);
        $authorScore = (float) ($authorWeights[$author] ?? 0);
        $storyScore = (float) ($storyWeights[$slug] ?? 0);
        $tagScore = 0.0;
        foreach (rec_story_tags($story) as $tag) $tagScore += (float) ($tagWeights[$tag] ?? 0);
        $popularity = log10(max(10, (int) ($story['reads'] ?? 10))) * 2 + log10(max(10, (int) ($story['claps'] ?? 10)));
        $freshness = !empty($story['publishedAt']) ? max(0, 8 - min(8, (time() - strtotime((string) $story['publishedAt'])) / 86400 / 30)) : 2;
        $score += $topicScore * 2.5 + $tagScore * 0.65 + $authorScore * 0.9 + $storyScore * 1.4 + $popularity + $freshness;
        if ($topicScore > 0) $factors[] = ['label' => $topic, 'kind' => 'topic', 'weight' => round($topicScore, 2)];
        if ($authorScore > 0) $factors[] = ['label' => $author, 'kind' => 'author', 'weight' => round($authorScore, 2)];
        if ($tagScore > 0) $factors[] = ['label' => 'Related tags', 'kind' => 'tags', 'weight' => round($tagScore, 2)];
        if ($storyScore > 0) $factors[] = ['label' => 'Prior engagement', 'kind' => 'behavior', 'weight' => round($storyScore, 2)];
        $reason = $factors[0]['kind'] ?? '' ? match ($factors[0]['kind']) {
            'topic' => 'Matched to your interest in ' . $factors[0]['label'],
            'author' => 'More from a writer you engage with',
            'tags' => 'Related to tags you read',
            'behavior' => 'Similar to stories you engaged with',
            default => 'Ranked by reader engagement',
        } : 'Ranked by reader engagement';
        $insert->execute([$userId, $slug, $score, $reason, json_encode(array_slice($factors, 0, 5), JSON_UNESCAPED_SLASHES), $version, $now]);
    }
    return ['signals' => $signals, 'stories' => count($stories), 'trainedAt' => $now, 'modelVersion' => $version];
}

function recommendation_feed_for_user(string $userId, int $limit = 24): array
{
    $pdo = Database::pdo();
    $profile = $pdo->prepare('SELECT * FROM recommendation_profiles WHERE user_id = ?');
    $profile->execute([$userId]);
    $profileRow = $profile->fetch();
    if (!$profileRow || strtotime((string) $profileRow['last_trained_at']) < time() - 300) train_recommendations_for_user($userId);
    $stmt = $pdo->prepare('SELECT * FROM recommendation_story_scores WHERE user_id = ? ORDER BY score DESC LIMIT ?');
    $stmt->bindValue(1, $userId);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(fn($row) => [
        'storySlug' => $row['story_slug'],
        'score' => round((float) $row['score'], 3),
        'reason' => $row['reason'],
        'factors' => parse_json_field($row['factors_json'], []),
        'computedAt' => $row['computed_at'],
        'modelVersion' => (int) $row['model_version'],
    ], $stmt->fetchAll());
}

function payment_amount_for_checkout(array $payment, array $metadata): int
{
    $amount = (int) $payment['amount'];
    $code = strtoupper(trim((string) ($metadata['discountCode'] ?? $metadata['discount_code'] ?? '')));
    if ($code === '') return $amount;
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT * FROM discount_codes WHERE code = ? AND active = 1 AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at >= ?) AND (max_redemptions IS NULL OR redeemed_count < max_redemptions)");
    $stmt->execute([$code, now_iso(), now_iso()]);
    $discount = $stmt->fetch();
    if (!$discount) return $amount;
    if (($discount['applies_to_plan_id'] ?? '') && ($metadata['planId'] ?? '') !== $discount['applies_to_plan_id']) return $amount;
    $off = $discount['discount_type'] === 'percent'
        ? (int) floor($amount * min(100, (int) $discount['discount_value']) / 100)
        : min($amount, (int) $discount['discount_value']);
    return max(0, $amount - $off);
}

function active_discount_for_payment(array $payment, array $metadata): ?array
{
    $code = strtoupper(trim((string) ($metadata['discountCode'] ?? $metadata['discount_code'] ?? '')));
    if ($code === '') return null;
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT * FROM discount_codes WHERE code = ? AND active = 1 AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at >= ?) AND (max_redemptions IS NULL OR redeemed_count < max_redemptions)");
    $stmt->execute([$code, now_iso(), now_iso()]);
    $discount = $stmt->fetch();
    if (!$discount) return null;
    if (($discount['applies_to_plan_id'] ?? '') && ($metadata['planId'] ?? '') !== $discount['applies_to_plan_id']) return null;
    $original = (int) ($metadata['originalAmount'] ?? $payment['amount']);
    $off = $discount['discount_type'] === 'percent'
        ? (int) floor($original * min(100, (int) $discount['discount_value']) / 100)
        : min($original, (int) $discount['discount_value']);
    return ['row' => $discount, 'amount' => $off];
}

function normalize_segment_text(string $value): string
{
    return trim(strtolower(preg_replace('/\s+/', ' ', $value)));
}

function segment_rule_parts(mixed $rules): array
{
    if (is_array($rules)) {
        $parts = [];
        foreach ($rules as $rule) {
            if (is_array($rule)) {
                $kind = trim((string) ($rule['kind'] ?? $rule['field'] ?? ''));
                $value = trim((string) ($rule['value'] ?? $rule['condition'] ?? ''));
                if ($kind || $value) $parts[] = trim($kind . ' is ' . $value);
            } elseif (trim((string) $rule) !== '') {
                $parts[] = trim((string) $rule);
            }
        }
        return $parts;
    }
    $text = trim((string) $rules);
    if ($text === '') return [];
    return array_values(array_filter(array_map('trim', preg_split('/[·;,]|(?:\s+\bAND\b\s+)/i', $text) ?: [])));
}

function segment_user_context(array $user, array $docs, array $events, ?array $subscription): array
{
    $profile = is_array($docs['recommendation-profile'] ?? null) ? $docs['recommendation-profile'] : [];
    $preferences = is_array($docs['preferences'] ?? null) ? $docs['preferences'] : [];
    $following = is_array($docs['following'] ?? null) ? $docs['following'] : [];
    $history = is_array($docs['reading-history'] ?? null) ? $docs['reading-history'] : [];
    $selectedInterests = is_array($profile['selectedInterests'] ?? null) ? $profile['selectedInterests'] : [];
    $eventTypes = array_map(fn($event) => (string) ($event['event_type'] ?? ''), $events);
    $eventBlob = json_encode($events, JSON_UNESCAPED_SLASHES) ?: '';
    $docBlob = json_encode($docs, JSON_UNESCAPED_SLASHES) ?: '';
    return [
        'haystack' => normalize_segment_text(implode(' ', [
            $user['id'] ?? '',
            $user['name'] ?? '',
            $user['email'] ?? '',
            $user['role'] ?? '',
            $user['subscription'] ?? '',
            $subscription['plan_id'] ?? '',
            $subscription['status'] ?? '',
            $docBlob,
            $eventBlob,
        ])),
        'interests' => array_map(fn($value) => normalize_segment_text((string) $value), $selectedInterests),
        'subscription' => normalize_segment_text(($user['subscription'] ?? '') . ' ' . ($subscription['plan_id'] ?? '') . ' ' . ($subscription['status'] ?? '')),
        'locale' => normalize_segment_text((string) ($preferences['locale'] ?? '')),
        'eventTypes' => $eventTypes,
        'completionCount' => count(array_filter($eventTypes, fn($type) => in_array($type, ['complete', 'read_complete'], true))),
        'openCount' => count(array_filter($eventTypes, fn($type) => $type === 'open')),
        'saveCount' => count(array_filter($eventTypes, fn($type) => $type === 'save')),
        'shareCount' => count(array_filter($eventTypes, fn($type) => $type === 'share')),
        'historyCount' => count($history),
        'followingCount' => array_sum(array_map(fn($items) => is_array($items) ? count($items) : 0, $following)),
        'lastEventAt' => $events[0]['created_at'] ?? null,
    ];
}

function segment_rule_matches_context(string $rule, array $context): bool
{
    $rule = normalize_segment_text($rule);
    if ($rule === '') return true;

    if (preg_match('/^interest\s+(?:is|=)\s+(.+)$/', $rule, $m)) {
        $needle = normalize_segment_text($m[1]);
        return in_array($needle, $context['interests'], true) || str_contains($context['haystack'], $needle);
    }
    if (preg_match('/^plan\s+(?:is|=)\s+(.+)$/', $rule, $m)) {
        return str_contains($context['subscription'], normalize_segment_text($m[1]));
    }
    if (str_contains($rule, 'free plan')) return str_contains($context['subscription'], 'free');
    if (str_contains($rule, 'annual plan') || str_contains($rule, 'yearly plan')) return str_contains($context['subscription'], 'annual') || str_contains($context['subscription'], 'year');
    if (preg_match('/^location\s+(?:is\s+)?(.+)$/', $rule, $m)) {
        $needle = normalize_segment_text($m[1]);
        return str_contains($context['haystack'], $needle) || ($needle === 'india' && str_contains($context['locale'], 'in'));
    }
    if (preg_match('/^source\s+(?:is\s+)?(.+)$/', $rule, $m)) return str_contains($context['haystack'], normalize_segment_text($m[1]));
    if (preg_match('/^custom tag\s+(?:is\s+)?(.+)$/', $rule, $m)) return str_contains($context['haystack'], normalize_segment_text($m[1]));
    if (preg_match('/(\d+)\s*\+\s*(completion|completions|complete reads)/', $rule, $m)) return $context['completionCount'] >= (int) $m[1];
    if (preg_match('/(\d+)\s*\+\s*(open|opens|visits|reads)/', $rule, $m)) return max($context['openCount'], $context['historyCount']) >= (int) $m[1];
    if (preg_match('/(\d+)\s*\+\s*(save|saves)/', $rule, $m)) return $context['saveCount'] >= (int) $m[1];
    if (preg_match('/no visit in\s+(\d+)\s+days/', $rule, $m)) {
        if (!$context['lastEventAt']) return true;
        return strtotime((string) $context['lastEventAt']) <= time() - ((int) $m[1] * 86400);
    }
    if (str_contains($rule, 'engagement')) return ($context['openCount'] + $context['completionCount'] + $context['saveCount'] + $context['shareCount']) > 0;
    if (str_contains($rule, 'reading behavior')) return $context['historyCount'] > 0 || $context['completionCount'] > 0;
    return str_contains($context['haystack'], $rule);
}

function preview_audience_segment(mixed $rules): array
{
    $parts = segment_rule_parts($rules);
    $pdo = Database::pdo();
    $users = $pdo->query("SELECT * FROM users WHERE status = 'active' ORDER BY created_at DESC")->fetchAll();
    $matched = [];
    foreach ($users as $user) {
        $docStmt = $pdo->prepare('SELECT key, value_json FROM user_documents WHERE user_id = ?');
        $docStmt->execute([$user['id']]);
        $docs = [];
        foreach ($docStmt->fetchAll() as $row) $docs[$row['key']] = parse_json_field($row['value_json'], null);

        $eventStmt = $pdo->prepare('SELECT event_type, story_slug, value, metadata, created_at FROM engagement_events WHERE user_id = ? ORDER BY created_at DESC LIMIT 250');
        $eventStmt->execute([$user['id']]);
        $events = $eventStmt->fetchAll();

        $subStmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $subStmt->execute([$user['id']]);
        $subscription = $subStmt->fetch() ?: null;

        $context = segment_user_context($user, $docs, $events, $subscription);
        $matches = true;
        foreach ($parts as $part) {
            if (!segment_rule_matches_context($part, $context)) {
                $matches = false;
                break;
            }
        }
        if ($matches) {
            $matched[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'subscription' => $user['subscription'],
                'signals' => [
                    'interests' => $context['interests'],
                    'opens' => $context['openCount'],
                    'completions' => $context['completionCount'],
                    'saves' => $context['saveCount'],
                    'lastEventAt' => $context['lastEventAt'],
                ],
            ];
        }
    }
    return ['count' => count($matched), 'users' => array_slice($matched, 0, 50), 'rules' => $parts, 'evaluatedAt' => now_iso()];
}

function admin_analytics_summary(): array
{
    $pdo = Database::pdo();
    $stories = document_value('stories', []);
    $storyTitles = [];
    foreach ($stories as $story) {
        if (!empty($story['slug'])) $storyTitles[(string) $story['slug']] = (string) ($story['title'] ?? $story['slug']);
    }

    $eventCounts = $pdo->query("
        SELECT event_type, COUNT(*) AS count, COALESCE(AVG(value), 0) AS average_value
        FROM engagement_events
        GROUP BY event_type
        ORDER BY count DESC
    ")->fetchAll();

    $storyCounts = $pdo->query("
        SELECT story_slug, event_type, COUNT(*) AS count, COALESCE(AVG(value), 0) AS average_value
        FROM engagement_events
        WHERE story_slug IS NOT NULL AND story_slug != ''
        GROUP BY story_slug, event_type
        ORDER BY count DESC
        LIMIT 500
    ")->fetchAll();
    foreach ($storyCounts as &$row) $row['title'] = $storyTitles[$row['story_slug']] ?? $row['story_slug'];
    unset($row);

    $dailyEvents = $pdo->query("
        SELECT substr(created_at, 1, 10) AS day, event_type, COUNT(*) AS count
        FROM engagement_events
        GROUP BY day, event_type
        ORDER BY day DESC, count DESC
        LIMIT 180
    ")->fetchAll();

    $revenueByStory = $pdo->query("
        SELECT wt.story_slug, COALESCE(SUM(wt.gross_amount), 0) AS amount, COUNT(*) AS tips
        FROM writer_tips wt
        WHERE wt.status = 'paid'
        GROUP BY wt.story_slug
        ORDER BY amount DESC
        LIMIT 100
    ")->fetchAll();
    foreach ($revenueByStory as &$row) $row['title'] = $storyTitles[$row['story_slug']] ?? $row['story_slug'];
    unset($row);

    $revenue = (int) $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE status = 'paid'")->fetch()['total'];
    $activeSubscriptions = (int) $pdo->query("SELECT COUNT(*) AS count FROM subscriptions WHERE status = 'active'")->fetch()['count'];
    $tickets = $pdo->query("
        SELECT status, priority, category, COUNT(*) AS count
        FROM support_tickets
        GROUP BY status, priority, category
        ORDER BY count DESC
    ")->fetchAll();
    $moderation = $pdo->query("
        SELECT status, kind, risk, COUNT(*) AS count
        FROM moderation_cases
        GROUP BY status, kind, risk
        ORDER BY count DESC
    ")->fetchAll();

    return compact('eventCounts', 'storyCounts', 'dailyEvents', 'revenueByStory', 'revenue', 'activeSubscriptions', 'tickets', 'moderation');
}

function payu_response_hash_valid(array $payload): bool
{
    $salt = (string) provider_config_value('PAYU_SALT', 'payu', 'salt', '');
    $received = strtolower(trim((string) ($payload['hash'] ?? '')));
    if ($salt === '' || $received === '') return false;
    $fields = [
        $salt,
        (string) ($payload['status'] ?? ''),
        (string) ($payload['udf10'] ?? ''),
        (string) ($payload['udf9'] ?? ''),
        (string) ($payload['udf8'] ?? ''),
        (string) ($payload['udf7'] ?? ''),
        (string) ($payload['udf6'] ?? ''),
        (string) ($payload['udf5'] ?? ''),
        (string) ($payload['udf4'] ?? ''),
        (string) ($payload['udf3'] ?? ''),
        (string) ($payload['udf2'] ?? ''),
        (string) ($payload['udf1'] ?? ''),
        (string) ($payload['email'] ?? ''),
        (string) ($payload['firstname'] ?? ''),
        (string) ($payload['productinfo'] ?? ''),
        (string) ($payload['amount'] ?? ''),
        (string) ($payload['txnid'] ?? ''),
        (string) ($payload['key'] ?? provider_config_value('PAYU_MERCHANT_KEY', 'payu', 'merchant_key')),
    ];
    return hash_equals(hash('sha512', implode('|', $fields)), $received);
}

function create_provider_order(string $provider, array $payment, array $user): array
{
    $amount = (int) $payment['amount'];
    $currency = strtoupper((string) $payment['currency']);
    $origin = app_origin();
    if ($provider === 'razorpay') {
        $keyId = (string) provider_config_value('RAZORPAY_KEY_ID', 'razorpay', 'key_id');
        $keySecret = (string) provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret');
        $result = http_request_json('https://api.razorpay.com/v1/orders', [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($keyId . ':' . $keySecret),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['amount' => $amount, 'currency' => $currency, 'receipt' => $payment['id'], 'payment_capture' => 1, 'notes' => ['paymentId' => $payment['id']]], JSON_UNESCAPED_SLASHES),
        ]);
        return ['providerOrderId' => $result['id'] ?? '', 'checkout' => ['key' => $keyId, 'orderId' => $result['id'] ?? '']];
    }
    if ($provider === 'paypal') {
        $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $clientId = (string) provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id');
        $clientSecret = (string) provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret');
        $token = http_request_json($base . '/v1/oauth2/token', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret), 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => ['grant_type' => 'client_credentials'],
        ]);
        $units = [['reference_id' => $payment['id'], 'amount' => ['currency_code' => $currency, 'value' => number_format($amount / 100, 2, '.', '')]]];
        $order = http_request_json($base . '/v2/checkout/orders', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            'body' => json_encode(['intent' => 'CAPTURE', 'purchase_units' => $units, 'application_context' => ['return_url' => $origin . '/api/payments/paypal/return?paymentId=' . rawurlencode($payment['id']), 'cancel_url' => $origin . '/pricing?payment=cancelled']], JSON_UNESCAPED_SLASHES),
        ]);
        $approve = '';
        foreach (($order['links'] ?? []) as $link) if (($link['rel'] ?? '') === 'approve') $approve = (string) $link['href'];
        return ['providerOrderId' => $order['id'] ?? '', 'checkout' => ['approveUrl' => $approve]];
    }
    if ($provider === 'cashfree') {
        $base = provider_config_value('CASHFREE_ENVIRONMENT', 'cashfree', 'environment', 'sandbox') === 'production' ? 'https://api.cashfree.com/pg/orders' : 'https://sandbox.cashfree.com/pg/orders';
        $order = http_request_json($base, [
            'method' => 'POST',
            'headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret'), 'Content-Type' => 'application/json'],
            'body' => json_encode(['order_id' => $payment['id'], 'order_amount' => $amount / 100, 'order_currency' => $currency, 'customer_details' => ['customer_id' => $user['id'], 'customer_name' => $user['name'], 'customer_email' => $user['email'], 'customer_phone' => '9999999999'], 'order_meta' => ['return_url' => $origin . '/api/payments/cashfree/return?paymentId=' . rawurlencode($payment['id']) . '&order_id={order_id}']], JSON_UNESCAPED_SLASHES),
        ]);
        return ['providerOrderId' => $order['order_id'] ?? $payment['id'], 'checkout' => ['paymentSessionId' => $order['payment_session_id'] ?? '']];
    }
    if ($provider === 'payu') {
        $txnid = $payment['id'];
        $product = preg_replace('/[^A-Za-z0-9 _.-]/', '', (string) ($payment['purpose'] ?? 'InkRiver payment'));
        $fields = [
            'key' => provider_config_value('PAYU_MERCHANT_KEY', 'payu', 'merchant_key'),
            'txnid' => $txnid,
            'amount' => number_format($amount / 100, 2, '.', ''),
            'productinfo' => $product,
            'firstname' => $user['name'],
            'email' => $user['email'],
            'surl' => $origin . '/api/payments/payu/return',
            'furl' => $origin . '/api/payments/payu/return',
            'udf1' => $payment['id'],
        ];
        $fields['hash'] = hash('sha512', implode('|', [$fields['key'], $fields['txnid'], $fields['amount'], $fields['productinfo'], $fields['firstname'], $fields['email'], $fields['udf1'], '', '', '', '', '', '', '', '', '', provider_config_value('PAYU_SALT', 'payu', 'salt')]));
        $action = provider_config_value('PAYU_ENVIRONMENT', 'payu', 'environment', 'sandbox') === 'production' ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment';
        return ['providerOrderId' => $txnid, 'checkout' => ['action' => $action, 'fields' => $fields]];
    }
    throw new RuntimeException('Unsupported payment provider.');
}

function provider_plan_config_key(string $prefix, string $planId): string
{
    return strtoupper($prefix . '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $planId));
}

function create_provider_subscription_checkout(string $provider, array $payment, array $user, array $metadata): array
{
    $origin = app_origin();
    $planId = (string) ($metadata['planId'] ?? 'premium');
    $period = strtolower((string) ($metadata['period'] ?? 'monthly'));
    $totalCount = str_contains($period, 'year') ? 10 : 120;

    if ($provider === 'razorpay') {
        $keyId = (string) provider_config_value('RAZORPAY_KEY_ID', 'razorpay', 'key_id');
        $keySecret = (string) provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret');
        $razorpayPlanId = (string) (provider_config_value(provider_plan_config_key('RAZORPAY_PLAN_ID', $planId), 'razorpay', 'plan_' . strtolower($planId), '') ?: provider_config_value('RAZORPAY_PLAN_ID', 'razorpay', 'plan_id', ''));
        if ($razorpayPlanId === '') throw new RuntimeException('Razorpay recurring plan ID is not configured for this package.');
        $subscription = http_request_json('https://api.razorpay.com/v1/subscriptions', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode($keyId . ':' . $keySecret), 'Content-Type' => 'application/json'],
            'body' => json_encode([
                'plan_id' => $razorpayPlanId,
                'total_count' => $totalCount,
                'customer_notify' => 1,
                'notes' => ['paymentId' => $payment['id'], 'localPlanId' => $planId, 'userId' => $user['id']],
            ], JSON_UNESCAPED_SLASHES),
        ]);
        return ['providerOrderId' => (string) ($subscription['id'] ?? ''), 'providerSubscriptionId' => (string) ($subscription['id'] ?? ''), 'checkout' => ['key' => $keyId, 'subscriptionId' => (string) ($subscription['id'] ?? '')]];
    }

    if ($provider === 'paypal') {
        $paypalPlanId = (string) (provider_config_value(provider_plan_config_key('PAYPAL_PLAN_ID', $planId), 'paypal', 'plan_' . strtolower($planId), '') ?: provider_config_value('PAYPAL_PLAN_ID', 'paypal', 'plan_id', ''));
        if ($paypalPlanId === '') throw new RuntimeException('PayPal recurring plan ID is not configured for this package.');
        $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $token = http_request_json($base . '/v1/oauth2/token', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => ['grant_type' => 'client_credentials'],
        ]);
        $subscription = http_request_json($base . '/v1/billing/subscriptions', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            'body' => json_encode([
                'plan_id' => $paypalPlanId,
                'custom_id' => $payment['id'],
                'application_context' => [
                    'brand_name' => 'InkRiver',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => $origin . '/api/payments/paypal/return?paymentId=' . rawurlencode($payment['id']) . '&mode=subscription',
                    'cancel_url' => $origin . '/pricing?payment=cancelled',
                ],
            ], JSON_UNESCAPED_SLASHES),
        ]);
        $approve = '';
        foreach (($subscription['links'] ?? []) as $link) if (($link['rel'] ?? '') === 'approve') $approve = (string) $link['href'];
        return ['providerOrderId' => (string) ($subscription['id'] ?? ''), 'providerSubscriptionId' => (string) ($subscription['id'] ?? ''), 'checkout' => ['approveUrl' => $approve, 'subscriptionId' => (string) ($subscription['id'] ?? '')]];
    }

    if ($provider === 'cashfree') {
        $base = provider_config_value('CASHFREE_SUBSCRIPTIONS_API_URL', 'cashfree', 'subscriptions_api_url', '');
        $planReference = (string) (provider_config_value(provider_plan_config_key('CASHFREE_PLAN_ID', $planId), 'cashfree', 'plan_' . strtolower($planId), '') ?: provider_config_value('CASHFREE_PLAN_ID', 'cashfree', 'plan_id', ''));
        if ($base === '' || $planReference === '') throw new RuntimeException('Cashfree recurring subscription API URL and plan ID are required.');
        $subscription = http_request_json(rtrim($base, '/'), [
            'method' => 'POST',
            'headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret'), 'Content-Type' => 'application/json'],
            'body' => json_encode([
                'subscription_id' => $payment['id'],
                'plan_id' => $planReference,
                'customer_details' => ['customer_id' => $user['id'], 'customer_name' => $user['name'], 'customer_email' => $user['email']],
                'subscription_meta' => ['return_url' => $origin . '/api/payments/cashfree/return?paymentId=' . rawurlencode($payment['id'])],
            ], JSON_UNESCAPED_SLASHES),
        ]);
        return ['providerOrderId' => (string) ($subscription['subscription_id'] ?? $payment['id']), 'providerSubscriptionId' => (string) ($subscription['subscription_id'] ?? $payment['id']), 'checkout' => ['paymentSessionId' => (string) ($subscription['payment_session_id'] ?? ''), 'subscriptionId' => (string) ($subscription['subscription_id'] ?? $payment['id'])]];
    }

    if ($provider === 'payu') {
        $action = provider_config_value('PAYU_SUBSCRIPTION_CREATE_URL', 'payu', 'subscription_create_url', '');
        $planReference = (string) (provider_config_value(provider_plan_config_key('PAYU_PLAN_ID', $planId), 'payu', 'plan_' . strtolower($planId), '') ?: provider_config_value('PAYU_PLAN_ID', 'payu', 'plan_id', ''));
        if ($action === '' || $planReference === '') throw new RuntimeException('PayU recurring subscription create URL and plan ID are required.');
        $fields = [
            'key' => provider_config_value('PAYU_MERCHANT_KEY', 'payu', 'merchant_key'),
            'txnid' => $payment['id'],
            'amount' => number_format(((int) $payment['amount']) / 100, 2, '.', ''),
            'productinfo' => preg_replace('/[^A-Za-z0-9 _.-]/', '', (string) ($payment['purpose'] ?? 'InkRiver membership')),
            'firstname' => $user['name'],
            'email' => $user['email'],
            'plan_id' => $planReference,
            'surl' => $origin . '/api/payments/payu/return',
            'furl' => $origin . '/api/payments/payu/return',
            'udf1' => $payment['id'],
            'udf2' => 'subscription',
        ];
        $fields['hash'] = hash('sha512', implode('|', [$fields['key'], $fields['txnid'], $fields['amount'], $fields['productinfo'], $fields['firstname'], $fields['email'], $fields['udf1'], $fields['udf2'], '', '', '', '', '', '', '', '', provider_config_value('PAYU_SALT', 'payu', 'salt')]));
        return ['providerOrderId' => $payment['id'], 'providerSubscriptionId' => $payment['id'], 'checkout' => ['action' => $action, 'fields' => $fields, 'subscriptionId' => $payment['id']]];
    }

    throw new RuntimeException('Unsupported recurring subscription provider.');
}

function cancel_provider_subscription(array $subscription): array
{
    $provider = strtolower((string) ($subscription['provider'] ?? ''));
    $providerSubscriptionId = trim((string) ($subscription['provider_subscription_id'] ?? ''));
    if ($providerSubscriptionId === '') {
        return ['remote' => false, 'status' => 'not_provider_managed', 'message' => 'No provider subscription reference is stored.'];
    }
    if ($provider === 'razorpay') {
        $keyId = (string) provider_config_value('RAZORPAY_KEY_ID', 'razorpay', 'key_id');
        $keySecret = (string) provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret');
        $response = http_request_json('https://api.razorpay.com/v1/subscriptions/' . rawurlencode($providerSubscriptionId) . '/cancel', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode($keyId . ':' . $keySecret), 'Content-Type' => 'application/json'],
            'body' => json_encode(['cancel_at_cycle_end' => 1], JSON_UNESCAPED_SLASHES),
        ]);
        return ['remote' => true, 'status' => (string) ($response['status'] ?? 'cancel_requested'), 'response' => $response];
    }
    if ($provider === 'paypal') {
        $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $token = http_request_json($base . '/v1/oauth2/token', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => ['grant_type' => 'client_credentials'],
        ]);
        http_request_json($base . '/v1/billing/subscriptions/' . rawurlencode($providerSubscriptionId) . '/cancel', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            'body' => json_encode(['reason' => 'Customer requested cancellation from InkRiver.'], JSON_UNESCAPED_SLASHES),
        ]);
        return ['remote' => true, 'status' => 'cancelled'];
    }
    if ($provider === 'cashfree') {
        $base = provider_config_value('CASHFREE_SUBSCRIPTIONS_API_URL', 'cashfree', 'subscriptions_api_url', '');
        if ($base === '') throw new RuntimeException('Cashfree subscription cancellation API URL is not configured.');
        $response = http_request_json(rtrim($base, '/') . '/' . rawurlencode($providerSubscriptionId) . '/cancel', [
            'method' => 'POST',
            'headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret'), 'Content-Type' => 'application/json'],
            'body' => json_encode(['cancel_at_cycle_end' => true], JSON_UNESCAPED_SLASHES),
        ]);
        return ['remote' => true, 'status' => (string) ($response['status'] ?? 'cancel_requested'), 'response' => $response];
    }
    if ($provider === 'payu') {
        $url = provider_config_value('PAYU_SUBSCRIPTION_CANCEL_URL', 'payu', 'subscription_cancel_url', '');
        if ($url === '') throw new RuntimeException('PayU subscription cancellation URL is not configured.');
        $body = ['subscriptionId' => $providerSubscriptionId, 'merchantKey' => provider_config_value('PAYU_MERCHANT_KEY', 'payu', 'merchant_key')];
        $body['hash'] = hash('sha512', implode('|', [$body['merchantKey'], $providerSubscriptionId, provider_config_value('PAYU_SALT', 'payu', 'salt')]));
        $response = http_request_json($url, ['method' => 'POST', 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($body, JSON_UNESCAPED_SLASHES)]);
        return ['remote' => true, 'status' => (string) ($response['status'] ?? 'cancel_requested'), 'response' => $response];
    }
    throw new RuntimeException('Unsupported subscription provider: ' . $provider);
}

function activate_paid_payment(string $paymentId, string $providerPaymentId = ''): ?array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT payments.*, users.name, users.email, users.role, users.subscription, users.status, users.email_verified, users.created_at, users.updated_at, users.last_login_at FROM payments JOIN users ON users.id = payments.user_id WHERE payments.id = ?');
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
    if (!$payment) return null;
    if ($payment['status'] === 'paid') return public_user($payment);
    $metadata = parse_json_field($payment['metadata'] ?? '{}', []);
    $now = now_iso();
    $providerSubscriptionId = (string) ($metadata['providerSubscriptionId'] ?? '');
    $pdo->prepare("UPDATE payments SET status = 'paid', provider_payment_id = COALESCE(NULLIF(?, ''), provider_payment_id), updated_at = ? WHERE id = ?")->execute([$providerPaymentId, $now, $paymentId]);
    create_invoice_for_payment($payment, $payment, $metadata);
    create_notification($payment['user_id'] ?? null, 'payment_paid', 'Payment received', 'Your payment has been captured and your receipt is ready.', '/dashboard');
    $discount = active_discount_for_payment($payment, $metadata);
    if ($discount && $discount['amount'] > 0) {
        $pdo->prepare('INSERT INTO discount_redemptions (id, discount_id, user_id, payment_id, amount_discounted, created_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('DSR-'), $discount['row']['id'], $payment['user_id'], $paymentId, $discount['amount'], $now]);
        $pdo->prepare('UPDATE discount_codes SET redeemed_count = redeemed_count + 1, updated_at = ? WHERE id = ?')->execute([$now, $discount['row']['id']]);
    }

    if (($metadata['kind'] ?? '') === 'tip') {
        $commission = max(0, min(90, (int) ($metadata['commission'] ?? 10)));
        $fee = (int) floor((int) $payment['amount'] * $commission / 100);
        $pdo->prepare("INSERT INTO writer_tips (id, payment_id, sender_user_id, story_slug, writer_name, gross_amount, platform_fee, writer_amount, currency, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?)")
            ->execute([uuid_value('TIP-'), $paymentId, $payment['user_id'], $metadata['storySlug'] ?? '', $metadata['writerName'] ?? '', (int) $payment['amount'], $fee, (int) $payment['amount'] - $fee, $payment['currency'], $now]);
        return public_user($payment);
    }

    if (($metadata['kind'] ?? '') === 'gift') {
        $pdo->prepare("INSERT INTO gift_memberships (id, payment_id, purchaser_user_id, recipient_email, plan_id, months, message, deliver_at, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?) ON CONFLICT(id) DO UPDATE SET status = 'paid', updated_at = excluded.updated_at")
            ->execute([uuid_value('GFT-'), $paymentId, $payment['user_id'], strtolower((string) ($metadata['recipientEmail'] ?? '')), (string) ($metadata['planId'] ?? 'premium'), max(1, (int) ($metadata['months'] ?? 1)), substr((string) ($metadata['message'] ?? ''), 0, 500), (string) ($metadata['deliverAt'] ?? $now), $now, $now]);
        return public_user($payment);
    }

    $planId = (string) ($metadata['planId'] ?? 'premium');
    $months = str_contains(strtolower((string) ($metadata['period'] ?? 'monthly')), 'year') ? 12 : 1;
    $subId = uuid_value('SUB-');
    $ends = gmdate('Y-m-d\TH:i:s.v\Z', time() + $months * 30 * 86400);
    $pdo->prepare("INSERT INTO subscriptions (id, user_id, plan_id, provider, provider_subscription_id, currency, amount, status, starts_at, ends_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)")
        ->execute([$subId, $payment['user_id'], $planId, $payment['provider'], $providerSubscriptionId ?: $providerPaymentId, $payment['currency'], (int) $payment['amount'], $now, $ends, $now, $now]);
    $pdo->prepare('UPDATE payments SET subscription_id = ? WHERE id = ?')->execute([$subId, $paymentId]);
    $pdo->prepare('UPDATE users SET subscription = ?, updated_at = ? WHERE id = ?')->execute([$metadata['planName'] ?? 'Premium', $now, $payment['user_id']]);
    $pdo->prepare('INSERT INTO subscription_events (id, subscription_id, user_id, provider, event_type, status_before, status_after, payload_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([uuid_value('SEV-'), $subId, $payment['user_id'], $payment['provider'], 'activated', null, 'active', json_encode($metadata, JSON_UNESCAPED_SLASHES), $now]);
    create_notification($payment['user_id'] ?? null, 'subscription_active', 'Membership activated', 'Your subscription is active until ' . $ends, '/dashboard');
    $payment['subscription'] = $metadata['planName'] ?? 'Premium';
    $payment['updated_at'] = $now;
    return public_user($payment);
}

function enqueue_background_job(string $type, array $payload = [], ?string $availableAt = null): string
{
    $id = uuid_value('JOB-');
    $now = now_iso();
    Database::pdo()->prepare("INSERT INTO background_jobs (id, type, status, payload_json, available_at, created_at, updated_at) VALUES (?, ?, 'queued', ?, ?, ?, ?)")
        ->execute([$id, $type, json_encode($payload, JSON_UNESCAPED_SLASHES), $availableAt ?: $now, $now, $now]);
    return $id;
}

function create_media_variants_for_asset(array $asset): array
{
    if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) return ['created' => 0, 'reason' => 'GD_WEBP_UNAVAILABLE'];
    $sourcePath = dirname(__DIR__) . (string) $asset['url'];
    if (!is_file($sourcePath)) return ['created' => 0, 'reason' => 'SOURCE_MISSING'];
    $source = @imagecreatefromstring(file_get_contents($sourcePath) ?: '');
    if (!$source) return ['created' => 0, 'reason' => 'UNSUPPORTED_IMAGE'];
    $width = imagesx($source);
    $height = imagesy($source);
    $variants = [
        'thumb' => 320,
        'medium' => 960,
        'large' => 1600,
    ];
    $created = 0;
    foreach ($variants as $name => $targetWidth) {
        $ratio = min(1, $targetWidth / max(1, $width));
        $nextWidth = max(1, (int) round($width * $ratio));
        $nextHeight = max(1, (int) round($height * $ratio));
        $canvas = imagecreatetruecolor($nextWidth, $nextHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $nextWidth, $nextHeight, $width, $height);
        $stored = preg_replace('/\.[^.]+$/', '', basename((string) $asset['stored_name'])) . '-' . $name . '.webp';
        $variantDir = dirname($sourcePath);
        $path = $variantDir . '/' . $stored;
        $variantUrl = rtrim(dirname((string) $asset['url']), '/\\') . '/' . $stored;
        if (imagewebp($canvas, $path, 82)) {
            Database::pdo()->prepare('INSERT INTO media_variants (id, media_asset_id, variant, url, mime_type, width, height, size_bytes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(media_asset_id, variant) DO UPDATE SET url = excluded.url, mime_type = excluded.mime_type, width = excluded.width, height = excluded.height, size_bytes = excluded.size_bytes, created_at = excluded.created_at')
                ->execute([uuid_value('MVAR-'), $asset['id'], $name, $variantUrl, 'image/webp', $nextWidth, $nextHeight, filesize($path) ?: 0, now_iso()]);
            $created++;
        }
        imagedestroy($canvas);
    }
    imagedestroy($source);
    return ['created' => $created];
}

function sync_subscription_lifecycle(): array
{
    $pdo = Database::pdo();
    $now = now_iso();
    $expired = $pdo->prepare("SELECT * FROM subscriptions WHERE status = 'active' AND ends_at IS NOT NULL AND ends_at < ?");
    $expired->execute([$now]);
    $expiredRows = $expired->fetchAll();
    foreach ($expiredRows as $row) {
        $pdo->prepare("UPDATE subscriptions SET status = 'expired', updated_at = ? WHERE id = ?")->execute([$now, $row['id']]);
        $pdo->prepare("INSERT INTO subscription_events (id, subscription_id, user_id, provider, event_type, status_before, status_after, payload_json, created_at) VALUES (?, ?, ?, ?, 'expired', 'active', 'expired', '{}', ?)")
            ->execute([uuid_value('SEV-'), $row['id'], $row['user_id'], $row['provider'], $now]);
        create_notification($row['user_id'], 'subscription_expired', 'Membership expired', 'Your membership period ended. Update payment to continue premium access.', '/pricing');
    }
    $pastDue = $pdo->query("SELECT * FROM subscriptions WHERE status IN ('past_due', 'failed')")->fetchAll();
    foreach ($pastDue as $row) {
        $existing = $pdo->prepare("SELECT id FROM subscription_dunning WHERE subscription_id = ? AND status IN ('open', 'notified') LIMIT 1");
        $existing->execute([$row['id']]);
        if ($existing->fetch()) {
            $pdo->prepare('UPDATE subscription_dunning SET updated_at = ? WHERE subscription_id = ?')->execute([$now, $row['id']]);
        } else {
            $pdo->prepare("INSERT INTO subscription_dunning (id, subscription_id, user_id, status, attempts, next_attempt_at, created_at, updated_at) VALUES (?, ?, ?, 'open', 0, ?, ?, ?)")
                ->execute([uuid_value('DUN-'), $row['id'], $row['user_id'], gmdate('Y-m-d\TH:i:s\Z', time() + 86400), $now, $now]);
        }
    }
    $dunning = $pdo->query("SELECT * FROM subscription_dunning WHERE status = 'open' AND (next_attempt_at IS NULL OR next_attempt_at <= datetime('now'))")->fetchAll();
    foreach ($dunning as $row) {
        $pdo->prepare("UPDATE subscription_dunning SET status = 'notified', attempts = attempts + 1, next_attempt_at = ?, updated_at = ? WHERE id = ?")
            ->execute([gmdate('Y-m-d\TH:i:s\Z', time() + 3 * 86400), $now, $row['id']]);
    }
    return ['expired' => count($expiredRows), 'dunningQueued' => count($pastDue), 'dunningNotified' => count($dunning)];
}

function execute_payout_transfer(array $payout): array
{
    $snapshot = parse_json_field($payout['payout_account_snapshot'] ?? '{}', []);
    $method = strtolower((string) ($snapshot['payout_method'] ?? 'manual'));
    $provider = $method === 'paypal' ? 'paypal' : (provider_config_value('PAYOUT_PROVIDER', 'payouts', 'provider', 'manual') ?: 'manual');
    $request = [
        'payoutId' => $payout['id'],
        'amount' => (int) $payout['amount'],
        'currency' => $payout['currency'],
        'account' => $snapshot,
    ];
    if ($provider === 'manual') throw new RuntimeException('No automatic payout provider is configured.');
    if ($provider === 'paypal') {
        $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $token = http_request_json($base . '/v1/oauth2/token', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => ['grant_type' => 'client_credentials'],
        ]);
        return http_request_json($base . '/v1/payments/payouts', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            'body' => json_encode([
                'sender_batch_header' => ['sender_batch_id' => $payout['id'], 'email_subject' => 'InkRiver writer payout'],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'receiver' => $snapshot['account_reference'] ?? '',
                    'amount' => ['value' => number_format(((int) $payout['amount']) / 100, 2, '.', ''), 'currency' => $payout['currency']],
                    'note' => 'InkRiver writer payout',
                    'sender_item_id' => $payout['id'],
                ]],
            ], JSON_UNESCAPED_SLASHES),
        ]);
    }
    $url = provider_config_value('PAYOUT_API_URL', 'payouts', 'api_url');
    $key = provider_config_value('PAYOUT_API_KEY', 'payouts', 'api_key');
    if (!$url || !$key) throw new RuntimeException('Payout API credentials are missing.');
    return http_request_json($url, [
        'method' => 'POST',
        'headers' => ['Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'],
        'body' => json_encode($request, JSON_UNESCAPED_SLASHES),
    ]);
}

function execute_payout_by_id(string $payoutId): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare('SELECT * FROM writer_payouts WHERE id = ?');
    $stmt->execute([$payoutId]);
    $payout = $stmt->fetch();
    if (!$payout) throw new RuntimeException('Payout not found.');
    if (in_array((string) $payout['status'], ['paid', 'processing'], true)) {
        return ['payoutId' => $payoutId, 'status' => (string) $payout['status']];
    }
    $transferId = uuid_value('PTR-');
    $provider = provider_config_value('PAYOUT_PROVIDER', 'payouts', 'provider', 'manual') ?: 'manual';
    $now = now_iso();
    $pdo->prepare("INSERT INTO payout_transfers (id, payout_id, provider, status, request_json, created_at, updated_at) VALUES (?, ?, ?, 'processing', ?, ?, ?)")
        ->execute([$transferId, $payoutId, $provider, json_encode(['payoutId' => $payoutId], JSON_UNESCAPED_SLASHES), $now, $now]);
    $pdo->prepare("UPDATE writer_payouts SET status = 'processing', updated_at = ? WHERE id = ?")->execute([$now, $payoutId]);
    try {
        $response = execute_payout_transfer($payout);
        $providerTransferId = (string) ($response['batch_header']['payout_batch_id'] ?? $response['id'] ?? $response['transferId'] ?? '');
        $pdo->prepare("UPDATE payout_transfers SET status = 'paid', provider_transfer_id = ?, response_json = ?, updated_at = ? WHERE id = ?")
            ->execute([$providerTransferId, json_encode($response, JSON_UNESCAPED_SLASHES), now_iso(), $transferId]);
        $pdo->prepare("UPDATE writer_payouts SET status = 'paid', paid_at = ?, updated_at = ? WHERE id = ?")->execute([now_iso(), now_iso(), $payoutId]);
        return ['transferId' => $transferId, 'providerTransferId' => $providerTransferId, 'status' => 'paid'];
    } catch (Throwable $error) {
        $pdo->prepare("UPDATE payout_transfers SET status = 'failed', error = ?, updated_at = ? WHERE id = ?")->execute([$error->getMessage(), now_iso(), $transferId]);
        $pdo->prepare("UPDATE writer_payouts SET status = 'failed', updated_at = ? WHERE id = ?")->execute([now_iso(), $payoutId]);
        throw $error;
    }
}

function execute_due_payouts(int $limit = 20): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT id FROM writer_payouts WHERE status IN ('pending', 'failed') ORDER BY created_at ASC LIMIT ?");
    $stmt->execute([max(1, min(100, $limit))]);
    $rows = $stmt->fetchAll();
    $summary = ['processed' => 0, 'paid' => 0, 'failed' => 0, 'results' => []];
    foreach ($rows as $row) {
        $summary['processed']++;
        try {
            $result = execute_payout_by_id((string) $row['id']);
            $summary['paid'] += ($result['status'] ?? '') === 'paid' ? 1 : 0;
            $summary['results'][] = ['payoutId' => $row['id'], 'status' => $result['status'] ?? 'processing'];
        } catch (Throwable $error) {
            $summary['failed']++;
            $summary['results'][] = ['payoutId' => $row['id'], 'status' => 'failed', 'error' => $error->getMessage()];
        }
    }
    return $summary;
}

function publish_scheduled_stories(): array
{
    $stories = document_value('stories', []);
    if (!is_array($stories)) return ['published' => 0];
    $now = time();
    $changed = false;
    $published = 0;
    foreach ($stories as &$story) {
        if (!is_array($story) || ($story['status'] ?? '') !== 'scheduled') continue;
        $scheduledAt = trim((string) ($story['scheduledAt'] ?? $story['scheduled_at'] ?? ''));
        if ($scheduledAt === '') continue;
        $due = strtotime($scheduledAt);
        if ($due !== false && $due <= $now) {
            $story['status'] = 'published';
            $story['publishedAt'] = now_iso();
            $story['updatedAt'] = now_iso();
            $story['scheduledAt'] = '';
            record_story_revision($story, null, 'Scheduled publishing job');
            ensure_story_translations($story);
            $published++;
            $changed = true;
        }
    }
    unset($story);
    if ($changed) {
        Database::pdo()->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, NULL, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
            ->execute(['stories', json_encode($stories, JSON_UNESCAPED_SLASHES), now_iso()]);
        rebuild_story_search_index($stories);
    }
    return ['published' => $published];
}

function newsletter_audience_users(string $audience): array
{
    $pdo = Database::pdo();
    $audience = strtolower(trim($audience ?: 'all'));
    if ($audience === 'subscribers' || $audience === 'paid') {
        return $pdo->query("SELECT DISTINCT users.id, users.email, users.name FROM users LEFT JOIN subscriptions ON subscriptions.user_id = users.id AND subscriptions.status = 'active' WHERE users.status = 'active' AND (users.subscription != 'Free' OR subscriptions.id IS NOT NULL)")->fetchAll();
    }
    if (str_starts_with($audience, 'topic:')) {
        $topic = trim(substr($audience, 6));
        $users = $pdo->query("SELECT id, email, name FROM users WHERE status = 'active'")->fetchAll();
        $matches = [];
        foreach ($users as $user) {
            $profileStmt = $pdo->prepare('SELECT topic_weights_json FROM recommendation_profiles WHERE user_id = ?');
            $profileStmt->execute([$user['id']]);
            $profile = $profileStmt->fetch();
            $topicWeights = $profile ? parse_json_field($profile['topic_weights_json'], []) : [];
            $docStmt = $pdo->prepare("SELECT value_json FROM user_documents WHERE user_id = ? AND key = 'recommendation-profile'");
            $docStmt->execute([$user['id']]);
            $doc = $docStmt->fetch();
            $docProfile = $doc ? parse_json_field($doc['value_json'], []) : [];
            $selected = is_array($docProfile['selectedInterests'] ?? null) ? $docProfile['selectedInterests'] : [];
            if (isset($topicWeights[$topic]) || in_array($topic, $selected, true)) $matches[] = $user;
        }
        return $matches;
    }
    return $pdo->query("SELECT id, email, name FROM users WHERE status = 'active'")->fetchAll();
}

function newsletter_token(string $newsletterId, string $userId, string $email): string
{
    return hash_hmac('sha256', $newsletterId . '|' . $userId . '|' . strtolower($email), credential_crypto_key());
}

function newsletter_user_suppressed(array $user): bool
{
    $email = strtolower(trim((string) ($user['email'] ?? '')));
    if ($email === '') return true;
    $stmt = Database::pdo()->prepare('SELECT email FROM newsletter_suppressions WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return (bool) $stmt->fetch();
}

function tracked_newsletter_html(array $letter, array $user): string
{
    $content = (string) ($letter['content_html'] ?? '');
    $newsletterId = rawurlencode((string) $letter['id']);
    $userId = rawurlencode((string) $user['id']);
    $base = app_url('/');
    $unsubscribeToken = newsletter_token((string) $letter['id'], (string) $user['id'], (string) $user['email']);
    $content = preg_replace_callback('/<a\s+([^>]*href=["\'])([^"\']+)(["\'][^>]*)>/i', function ($match) use ($newsletterId, $userId, $base) {
        $target = $base . 'api/newsletters/' . $newsletterId . '/click?u=' . $userId . '&url=' . rawurlencode($match[2]);
        return '<a ' . $match[1] . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . $match[3] . '>';
    }, $content) ?? $content;
    $pixel = '<img src="' . htmlspecialchars($base . 'api/newsletters/' . $newsletterId . '/open?u=' . $userId, ENT_QUOTES, 'UTF-8') . '" width="1" height="1" alt="" style="display:none" />';
    $unsubscribe = '<p style="font-size:12px;color:#667085;margin-top:24px">You are receiving this because you joined InkRiver. <a href="' . htmlspecialchars($base . 'api/newsletters/unsubscribe?n=' . $newsletterId . '&u=' . $userId . '&t=' . $unsubscribeToken, ENT_QUOTES, 'UTF-8') . '">Unsubscribe</a></p>';
    return $content . $pixel . $unsubscribe;
}

function send_due_newsletters(): array
{
    $pdo = Database::pdo();
    $now = now_iso();
    $stmt = $pdo->prepare("SELECT * FROM newsletters WHERE status = 'scheduled' AND scheduled_at <= ? ORDER BY scheduled_at ASC LIMIT 20");
    $stmt->execute([$now]);
    $letters = $stmt->fetchAll();
    $sent = 0;
    foreach ($letters as $letter) {
        $pdo->prepare("UPDATE newsletters SET status = 'sending', updated_at = ? WHERE id = ?")->execute([$now, $letter['id']]);
        $users = newsletter_audience_users((string) ($letter['audience'] ?? 'all'));
        foreach ($users as $user) {
            if (newsletter_user_suppressed($user)) continue;
            $ok = send_email_message($user['email'], $letter['subject'], ['templateKey' => 'newsletter', 'name' => $user['name'], 'content' => tracked_newsletter_html($letter, $user), 'newsletterTitle' => $letter['title'], 'audience' => $letter['audience']]);
            $pdo->prepare('INSERT INTO newsletter_events (id, newsletter_id, user_id, event_type, metadata_json, created_at) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([uuid_value('NLE-'), $letter['id'], $user['id'], $ok ? 'sent' : 'failed', '{}', now_iso()]);
        }
        $pdo->prepare("UPDATE newsletters SET status = 'sent', sent_at = ?, updated_at = ? WHERE id = ?")->execute([now_iso(), now_iso(), $letter['id']]);
        $sent++;
    }
    return ['sent' => $sent];
}

function queue_abandoned_checkout_recovery(): array
{
    $pdo = Database::pdo();
    $cutoff = gmdate('Y-m-d\TH:i:s.v\Z', time() - 3600);
    $stmt = $pdo->prepare("SELECT payments.*, users.email, users.name FROM payments JOIN users ON users.id = payments.user_id WHERE payments.status IN ('created', 'pending') AND payments.created_at < ? ORDER BY payments.created_at DESC LIMIT 100");
    $stmt->execute([$cutoff]);
    $rows = $stmt->fetchAll();
    $queued = 0;
    foreach ($rows as $row) {
        $exists = $pdo->prepare("SELECT id FROM checkout_recovery_events WHERE payment_id = ? AND event_type = 'reminder' LIMIT 1");
        $exists->execute([$row['id']]);
        if ($exists->fetch()) continue;
        send_email_message($row['email'], 'Complete your InkRiver membership', ['name' => $row['name'], 'paymentId' => $row['id'], 'amount' => $row['amount']]);
        $pdo->prepare("INSERT INTO checkout_recovery_events (id, payment_id, user_id, event_type, created_at) VALUES (?, ?, ?, 'reminder', ?)")
            ->execute([uuid_value('REC-'), $row['id'], $row['user_id'], now_iso()]);
        $queued++;
    }
    return ['queued' => $queued];
}

function seo_audit_rows(): array
{
    $rows = [];
    $seenTitles = [];
    foreach (document_value('stories', []) as $story) {
        if (!is_array($story)) continue;
        $slug = (string) ($story['slug'] ?? '');
        $title = trim((string) ($story['title'] ?? ''));
        $seo = is_array($story['seo'] ?? null) ? $story['seo'] : [];
        $bodyText = trim(strip_tags((string) ($story['contentHtml'] ?? '') . ' ' . implode(' ', is_array($story['body'] ?? null) ? $story['body'] : [])));
        $issues = [];
        if (strlen(trim((string) ($seo['metaDescription'] ?? $story['dek'] ?? ''))) < 80) $issues[] = 'Meta description is short or missing';
        if (strlen($bodyText) < 600) $issues[] = 'Thin content';
        if (empty($story['imageUrl'])) $issues[] = 'Missing featured image';
        if ($title && isset($seenTitles[strtolower($title)])) $issues[] = 'Duplicate title';
        $seenTitles[strtolower($title)] = true;
        if ($issues) $rows[] = ['slug' => $slug, 'title' => $title ?: $slug, 'issues' => $issues, 'score' => max(0, 100 - count($issues) * 20)];
    }
    return $rows;
}

function csv_response(string $filename, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '-', $filename) . '"');
    $out = fopen('php://output', 'w');
    if ($rows) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) fputcsv($out, array_map(fn($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : $value, $row));
    }
    fclose($out);
    exit;
}

function support_is_staff(array $session): bool
{
    return in_array((string) ($session['user']['role'] ?? ''), ['admin', 'moderator'], true);
}

function public_support_ticket(array $row): array
{
    return [
        'id' => $row['id'],
        'userId' => $row['user_id'],
        'subject' => $row['subject'],
        'category' => $row['category'],
        'priority' => $row['priority'],
        'status' => $row['status'],
        'owner' => $row['owner'],
        'details' => $row['details'],
        'createdAt' => $row['created_at'],
        'updatedAt' => $row['updated_at'],
        'requesterName' => $row['requester_name'] ?? '',
        'requesterEmail' => $row['requester_email'] ?? '',
    ];
}

function support_ticket_for_session(PDO $pdo, string $ticketId, array $session): ?array
{
    $stmt = $pdo->prepare('SELECT support_tickets.*, users.name AS requester_name, users.email AS requester_email FROM support_tickets LEFT JOIN users ON users.id = support_tickets.user_id WHERE support_tickets.id = ?');
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch();
    if (!$ticket) return null;
    if (!support_is_staff($session) && $ticket['user_id'] !== $session['user']['id']) return null;
    return $ticket;
}

function support_upload_attachments(PDO $pdo, string $ticketId, ?string $messageId, string $userId): array
{
    $uploaded = [];
    $files = $_FILES['attachments'] ?? null;
    if (!$files) return [];
    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
    $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
    $types = is_array($files['type']) ? $files['type'] : [$files['type']];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf', 'text/plain', 'text/csv', 'application/zip'];
    $relativeDir = 'uploads/support/' . gmdate('Y/m');
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0775, true);
    foreach ($names as $index => $name) {
        if (($errors[$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        if (($errors[$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) json_response(['error' => 'UPLOAD_FAILED', 'message' => 'One attachment could not be uploaded.'], 400);
        $size = (int) ($sizes[$index] ?? 0);
        if ($size > 10 * 1024 * 1024) json_response(['error' => 'FILE_TOO_LARGE', 'message' => 'Support attachments must be 10 MB or smaller.'], 413);
        $tmp = (string) ($tmpNames[$index] ?? '');
        if (!is_uploaded_file($tmp)) continue;
        $mime = mime_content_type($tmp) ?: (string) ($types[$index] ?? 'application/octet-stream');
        if (!in_array($mime, $allowed, true)) json_response(['error' => 'UNSUPPORTED_ATTACHMENT', 'message' => 'Use JPG, PNG, WebP, GIF, PDF, TXT, CSV, or ZIP attachments.'], 400);
        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        $filename = uuid_value('SUP-') . '.' . $safeExtension;
        $target = $absoluteDir . '/' . $filename;
        if (!move_uploaded_file($tmp, $target)) json_response(['error' => 'UPLOAD_STORE_FAILED', 'message' => 'Could not save a support attachment.'], 500);
        $id = uuid_value('SFA-');
        $url = '/' . $relativeDir . '/' . $filename;
        $pdo->prepare('INSERT INTO support_ticket_attachments (id, ticket_id, message_id, user_id, original_name, url, mime_type, size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $ticketId, $messageId, $userId, substr((string) $name, 0, 180), $url, $mime, $size, now_iso()]);
        $uploaded[] = ['id' => $id, 'name' => (string) $name, 'url' => $url, 'mimeType' => $mime, 'size' => $size];
    }
    return $uploaded;
}

function production_readiness(array $installer, array $providers): array
{
    return [
        ['key' => 'sqlite', 'label' => 'SQLite extension', 'ready' => (bool) $installer['sqlite'], 'detail' => 'Required for data storage.'],
        ['key' => 'uploads', 'label' => 'Uploads writable', 'ready' => (bool) $installer['uploadsWritable'], 'detail' => 'Required for media manager and imports.'],
        ['key' => 'storage', 'label' => 'Storage writable', 'ready' => (bool) $installer['storageWritable'], 'detail' => 'Required for SQLite and generated assets.'],
        ['key' => 'cron', 'label' => 'Cron secret', 'ready' => (bool) $installer['cronConfigured'], 'detail' => 'Required for scheduled jobs.'],
        ['key' => 'admin', 'label' => 'Admin account', 'ready' => (int) $installer['adminUsers'] > 0, 'detail' => 'At least one administrator must exist.'],
        ['key' => 'payment', 'label' => 'Payment gateway', 'ready' => in_array(true, $providers['payments'] ?? [], true), 'detail' => 'At least one live gateway should be configured.'],
        ['key' => 'email', 'label' => 'Email provider', 'ready' => (bool) ($providers['email'] ?? false), 'detail' => 'Required for verification, reset, newsletters, and alerts.'],
        ['key' => 'ai', 'label' => 'AI provider', 'ready' => (bool) ($providers['ai'] ?? false), 'detail' => 'Required for AI tools and translations.'],
        ['key' => 'geoip', 'label' => 'IP intelligence', 'ready' => (bool) ($providers['geoip'] ?? false), 'detail' => 'Strengthens PayPal India restriction.'],
    ];
}

function deployment_shell_available(): bool
{
    $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));
    return function_exists('proc_open') && !in_array('proc_open', $disabled, true);
}

function deployment_repo_root(): string
{
    return dirname(__DIR__);
}

function deployment_git_binary(): string
{
    return provider_config_value('GIT_BINARY', 'deployment', 'git_binary', 'git') ?: 'git';
}

function deployment_php_binary(): string
{
    return provider_config_value('PHP_BINARY', 'deployment', 'php_binary', PHP_BINARY) ?: PHP_BINARY;
}

function deployment_command(array $command, int $timeoutSeconds = 120): array
{
    if (!deployment_shell_available()) {
        return ['ok' => false, 'code' => 127, 'stdout' => '', 'stderr' => 'proc_open is disabled on this server.'];
    }
    $cmd = implode(' ', array_map('escapeshellarg', $command));
    $descriptor = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptor, $pipes, deployment_repo_root());
    if (!is_resource($process)) return ['ok' => false, 'code' => 127, 'stdout' => '', 'stderr' => 'Unable to start process.'];
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $stdout = '';
    $stderr = '';
    $started = time();
    $timedOut = false;
    while (true) {
        $stdout .= stream_get_contents($pipes[1]) ?: '';
        $stderr .= stream_get_contents($pipes[2]) ?: '';
        $status = proc_get_status($process);
        if (!$status['running']) break;
        if (time() - $started > $timeoutSeconds) {
            proc_terminate($process);
            $timedOut = true;
            break;
        }
        usleep(100000);
    }
    $stdout .= stream_get_contents($pipes[1]) ?: '';
    $stderr .= stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    if ($timedOut) {
        $code = 124;
        $stderr = trim($stderr . "\nCommand timed out after {$timeoutSeconds}s.");
    }
    return ['ok' => $code === 0, 'code' => $code, 'stdout' => trim($stdout), 'stderr' => trim($stderr)];
}

function deployment_git(array $args, int $timeoutSeconds = 120): array
{
    return deployment_command(array_merge([deployment_git_binary()], $args), $timeoutSeconds);
}

function deployment_parse_changed_files(string $raw): array
{
    $files = [];
    foreach (preg_split('/\r?\n/', trim($raw)) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = preg_split('/\s+/', $line, 2);
        $files[] = ['status' => $parts[0] ?? '', 'path' => $parts[1] ?? $line];
    }
    return $files;
}

function deployment_recent_updates(int $limit = 10): array
{
    try {
        $stmt = Database::pdo()->prepare('SELECT * FROM deployment_updates ORDER BY created_at DESC LIMIT ?');
        $stmt->execute([max(1, min(50, $limit))]);
        return array_map(fn($row) => [
            'id' => $row['id'],
            'action' => $row['action'],
            'status' => $row['status'],
            'branch' => $row['branch'],
            'beforeCommit' => $row['before_commit'],
            'afterCommit' => $row['after_commit'],
            'changedFiles' => parse_json_field($row['changed_files_json'] ?? '[]', []),
            'log' => $row['log'],
            'error' => $row['error'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ], $stmt->fetchAll());
    } catch (Throwable) {
        return [];
    }
}

function deployment_current_status(bool $fetchRemote = false, string $branch = ''): array
{
    $status = [
        'enabled' => is_dir(deployment_repo_root() . '/.git') && deployment_shell_available(),
        'shellAvailable' => deployment_shell_available(),
        'repoPath' => deployment_repo_root(),
        'branch' => '',
        'remote' => '',
        'currentCommit' => '',
        'remoteCommit' => '',
        'dirty' => false,
        'dirtyFiles' => [],
        'changedFiles' => [],
        'recent' => deployment_recent_updates(),
    ];
    if (!$status['enabled']) return $status;
    $inside = deployment_git(['rev-parse', '--is-inside-work-tree'], 15);
    if (!$inside['ok'] || trim($inside['stdout']) !== 'true') {
        $status['enabled'] = false;
        $status['error'] = $inside['stderr'] ?: 'This folder is not a Git work tree.';
        return $status;
    }
    $currentBranch = deployment_git(['rev-parse', '--abbrev-ref', 'HEAD'], 15);
    $status['branch'] = $branch !== '' ? $branch : (($currentBranch['ok'] && $currentBranch['stdout'] !== 'HEAD') ? $currentBranch['stdout'] : 'main');
    $remote = deployment_git(['config', '--get', 'remote.origin.url'], 15);
    $commit = deployment_git(['rev-parse', 'HEAD'], 15);
    $dirty = deployment_git(['status', '--porcelain'], 20);
    $status['remote'] = $remote['ok'] ? $remote['stdout'] : '';
    $status['currentCommit'] = $commit['ok'] ? $commit['stdout'] : '';
    $status['dirtyFiles'] = $dirty['ok'] && $dirty['stdout'] !== '' ? preg_split('/\r?\n/', trim($dirty['stdout'])) : [];
    $status['dirty'] = count($status['dirtyFiles']) > 0;
    if ($fetchRemote) {
        $fetch = deployment_git(['fetch', 'origin', $status['branch']], 180);
        $status['fetchLog'] = trim(($fetch['stdout'] ? $fetch['stdout'] . "\n" : '') . $fetch['stderr']);
        if (!$fetch['ok']) {
            $status['error'] = $fetch['stderr'] ?: 'Git fetch failed.';
            return $status;
        }
        $remoteCommit = deployment_git(['rev-parse', 'origin/' . $status['branch']], 30);
        $diff = deployment_git(['diff', '--name-status', 'HEAD', 'origin/' . $status['branch']], 60);
        $status['remoteCommit'] = $remoteCommit['ok'] ? $remoteCommit['stdout'] : '';
        $status['changedFiles'] = $diff['ok'] ? deployment_parse_changed_files($diff['stdout']) : [];
    }
    return $status;
}

function deployment_write_update(string $id, array $fields): void
{
    $map = [
        'status' => 'status',
        'afterCommit' => 'after_commit',
        'changedFiles' => 'changed_files_json',
        'log' => 'log',
        'error' => 'error',
        'updatedAt' => 'updated_at',
    ];
    $sets = [];
    $values = [];
    foreach ($map as $input => $column) {
        if (!array_key_exists($input, $fields)) continue;
        $sets[] = $column . ' = ?';
        $values[] = $input === 'changedFiles' ? json_encode($fields[$input], JSON_UNESCAPED_SLASHES) : $fields[$input];
    }
    if (!$sets) return;
    $values[] = $id;
    Database::pdo()->prepare('UPDATE deployment_updates SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($values);
}

function deployment_database_backup_path(string $id): string
{
    $dir = deployment_repo_root() . '/storage/backups';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    return $dir . '/pre-deploy-' . preg_replace('/[^A-Za-z0-9_.-]/', '', $id) . '.sqlite';
}

function deployment_run_update(array $session, array $body): array
{
    $status = deployment_current_status(false, substr((string) ($body['branch'] ?? ''), 0, 80));
    if (!$status['enabled']) throw new RuntimeException($status['error'] ?? 'Git deployment is unavailable on this server.');
    if ($status['dirty']) {
        throw new RuntimeException('Update stopped because the server working tree has local changes. Commit, stash, or remove them before pulling from GitHub.');
    }
    $branch = preg_replace('/[^A-Za-z0-9._\/-]/', '', (string) ($status['branch'] ?: 'main')) ?: 'main';
    $before = (string) ($status['currentCommit'] ?? '');
    $id = uuid_value('DEP-');
    $now = now_iso();
    Database::pdo()->prepare("INSERT INTO deployment_updates (id, action, status, branch, before_commit, after_commit, changed_files_json, log, error, triggered_by, created_at, updated_at) VALUES (?, 'pull', 'running', ?, ?, '', '[]', '', '', ?, ?, ?)")
        ->execute([$id, $branch, $before, $session['user']['id'], $now, $now]);

    $log = [];
    $changedFiles = [];
    $dbBackup = '';
    try {
        $fetch = deployment_git(['fetch', 'origin', $branch], 180);
        $log[] = '$ git fetch origin ' . $branch;
        $log[] = trim(($fetch['stdout'] ? $fetch['stdout'] . "\n" : '') . $fetch['stderr']);
        if (!$fetch['ok']) throw new RuntimeException($fetch['stderr'] ?: 'Git fetch failed.');

        $diff = deployment_git(['diff', '--name-status', 'HEAD', 'origin/' . $branch], 60);
        $changedFiles = $diff['ok'] ? deployment_parse_changed_files($diff['stdout']) : [];
        deployment_write_update($id, ['changedFiles' => $changedFiles, 'log' => trim(implode("\n", array_filter($log))), 'updatedAt' => now_iso()]);
        if (!$changedFiles) {
            $after = (deployment_git(['rev-parse', 'HEAD'], 15)['stdout'] ?? $before) ?: $before;
            deployment_write_update($id, ['status' => 'success', 'afterCommit' => $after, 'changedFiles' => [], 'log' => trim(implode("\n", array_filter($log))) . "\nNo remote changes found.", 'updatedAt' => now_iso()]);
            return ['ok' => true, 'deployment' => deployment_current_status(false, $branch), 'update' => deployment_recent_updates(1)[0] ?? null];
        }

        if (is_file(database_path())) {
            $dbBackup = deployment_database_backup_path($id);
            if (!copy(database_path(), $dbBackup)) throw new RuntimeException('Unable to create SQLite backup before update.');
            $log[] = 'Database backup created: storage/backups/' . basename($dbBackup);
        }

        $pull = deployment_git(['pull', '--ff-only', 'origin', $branch], 240);
        $log[] = '$ git pull --ff-only origin ' . $branch;
        $log[] = trim(($pull['stdout'] ? $pull['stdout'] . "\n" : '') . $pull['stderr']);
        if (!$pull['ok']) throw new RuntimeException($pull['stderr'] ?: 'Git pull failed.');

        $migration = deployment_command([deployment_php_binary(), '-r', "require 'app/Database.php'; Database::pdo(); echo \"Migrations complete\\n\";"], 120);
        $log[] = '$ php -r migrations';
        $log[] = trim(($migration['stdout'] ? $migration['stdout'] . "\n" : '') . $migration['stderr']);
        if (!$migration['ok']) throw new RuntimeException($migration['stderr'] ?: 'Database migrations failed.');

        $after = deployment_git(['rev-parse', 'HEAD'], 15);
        $afterCommit = $after['ok'] ? $after['stdout'] : '';
        deployment_write_update($id, ['status' => 'success', 'afterCommit' => $afterCommit, 'changedFiles' => $changedFiles, 'log' => trim(implode("\n", array_filter($log))), 'updatedAt' => now_iso()]);
        audit_log($session['user']['id'], 'admin.deployment_update', 'deployment_update', $id, ['branch' => $branch, 'before' => $before, 'after' => $afterCommit]);
        return ['ok' => true, 'deployment' => deployment_current_status(false, $branch), 'update' => deployment_recent_updates(1)[0] ?? null];
    } catch (Throwable $error) {
        $rollbackLog = '';
        if ($before !== '') {
            $rollback = deployment_git(['reset', '--hard', $before], 120);
            $rollbackLog = trim(($rollback['stdout'] ? $rollback['stdout'] . "\n" : '') . $rollback['stderr']);
            if ($rollback['ok'] && $dbBackup && is_file($dbBackup)) @copy($dbBackup, database_path());
        }
        $log[] = 'Update failed: ' . $error->getMessage();
        if ($rollbackLog !== '') $log[] = "Rollback:\n" . $rollbackLog;
        deployment_write_update($id, ['status' => 'rolled_back', 'changedFiles' => $changedFiles, 'log' => trim(implode("\n", array_filter($log))), 'error' => $error->getMessage(), 'updatedAt' => now_iso()]);
        audit_log($session['user']['id'], 'admin.deployment_update_failed', 'deployment_update', $id, ['branch' => $branch, 'error' => $error->getMessage()]);
        throw new RuntimeException('Update failed and rollback was attempted: ' . $error->getMessage());
    }
}

function imported_story_items(string $sourceType, string $raw): array
{
    if ($sourceType === 'csv') {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);
        $headers = fgetcsv($handle);
        if (!$headers) throw new RuntimeException('CSV import needs a header row.');
        $headers = array_map(fn($header) => trim((string) $header), $headers);
        $items = [];
        while (($row = fgetcsv($handle)) !== false) {
            $item = [];
            foreach ($headers as $index => $header) $item[$header] = $row[$index] ?? '';
            $items[] = $item;
        }
        fclose($handle);
        return $items;
    }
    if (in_array($sourceType, ['wordpress', 'medium'], true)) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw);
        if (!$xml) throw new RuntimeException('Import content must be valid XML for WordPress or Medium exports.');
        $items = [];
        foreach ($xml->xpath('//item') ?: [] as $item) {
            $namespaces = $item->getNamespaces(true);
            $content = isset($namespaces['content']) ? (string) $item->children($namespaces['content'])->encoded : '';
            $dc = isset($namespaces['dc']) ? $item->children($namespaces['dc']) : null;
            $items[] = [
                'title' => (string) $item->title,
                'slug' => strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) ($item->title ?: uuid_value('story'))), '-')),
                'author' => $dc ? (string) $dc->creator : '',
                'contentHtml' => $content ?: (string) $item->description,
                'dek' => trim(strip_tags((string) $item->description)),
                'status' => 'draft',
                'publishedAt' => (string) $item->pubDate,
            ];
        }
        return $items;
    }
    $items = json_decode($raw, true);
    if (!is_array($items)) throw new RuntimeException('Import content must be a JSON array of stories.');
    return $items;
}

function run_background_job(array $job): array
{
    $payload = parse_json_field($job['payload_json'] ?? '{}', []);
    return match ($job['type']) {
        'recommendations.rebuild' => (function () {
            $users = Database::pdo()->query("SELECT id FROM users WHERE status = 'active'")->fetchAll();
            foreach ($users as $user) train_recommendations_for_user($user['id']);
            return ['profiles' => count($users)];
        })(),
        'recommendations.train_user' => (function () use ($payload) {
            $userId = (string) ($payload['userId'] ?? '');
            if ($userId === '') throw new RuntimeException('Missing userId.');
            return train_recommendations_for_user($userId);
        })(),
        'translations.backfill' => (function () {
            if (!provider_status()['ai']) throw new RuntimeException('AI provider is not configured.');
            $stories = array_values(array_filter(document_value('stories', []), fn($story) => ($story['status'] ?? '') === 'published'));
            $results = [];
            foreach ($stories as $story) $results[] = ensure_story_translations($story);
            return ['processed' => count($stories), 'created' => array_sum(array_column($results, 'created')), 'failed' => array_sum(array_column($results, 'failed'))];
        })(),
        'publishing.scheduled' => publish_scheduled_stories(),
        'newsletters.send' => send_due_newsletters(),
        'checkouts.recover' => queue_abandoned_checkout_recovery(),
        'subscriptions.sync' => sync_subscription_lifecycle(),
        'payouts.execute' => execute_due_payouts((int) ($payload['limit'] ?? 20)),
        'media.variants' => (function () use ($payload) {
            $stmt = Database::pdo()->prepare('SELECT * FROM media_assets WHERE id = ?');
            $stmt->execute([(string) ($payload['mediaAssetId'] ?? '')]);
            $asset = $stmt->fetch();
            if (!$asset) throw new RuntimeException('Media asset not found.');
            return create_media_variants_for_asset($asset);
        })(),
        'moderation.scan' => scan_moderation_rules(),
        'pwa.sync' => process_pwa_sync_queue((int) ($payload['limit'] ?? 100)),
        default => throw new RuntimeException('Unknown job type: ' . $job['type']),
    };
}

function run_due_background_jobs(int $limit = 10): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT * FROM background_jobs WHERE status = 'queued' AND available_at <= ? ORDER BY available_at ASC LIMIT ?");
    $stmt->execute([now_iso(), max(1, min(50, $limit))]);
    $jobs = $stmt->fetchAll();
    $summary = ['completed' => 0, 'failed' => 0, 'jobs' => []];
    foreach ($jobs as $job) {
        $now = now_iso();
        $pdo->prepare("UPDATE background_jobs SET status = 'running', attempts = attempts + 1, locked_at = ?, updated_at = ? WHERE id = ?")->execute([$now, $now, $job['id']]);
        try {
            $result = run_background_job($job);
            $pdo->prepare("UPDATE background_jobs SET status = 'completed', result_json = ?, updated_at = ? WHERE id = ?")->execute([json_encode($result, JSON_UNESCAPED_SLASHES), now_iso(), $job['id']]);
            $summary['completed']++;
            $summary['jobs'][] = ['id' => $job['id'], 'type' => $job['type'], 'status' => 'completed', 'result' => $result];
        } catch (Throwable $error) {
            $pdo->prepare("UPDATE background_jobs SET status = 'failed', last_error = ?, updated_at = ? WHERE id = ?")->execute([$error->getMessage(), now_iso(), $job['id']]);
            $summary['failed']++;
            $summary['jobs'][] = ['id' => $job['id'], 'type' => $job['type'], 'status' => 'failed', 'error' => $error->getMessage()];
        }
    }
    return $summary;
}

function process_pwa_sync_queue(int $limit = 100): array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("SELECT * FROM pwa_sync_queue WHERE status = 'queued' ORDER BY created_at ASC LIMIT ?");
    $stmt->execute([max(1, min(500, $limit))]);
    $items = $stmt->fetchAll();
    $processed = 0;
    $failed = 0;
    foreach ($items as $item) {
        $payload = parse_json_field($item['payload_json'] ?? '{}', []);
        try {
            $type = (string) $item['type'];
            if (in_array($type, ['event', 'open', 'complete', 'read_complete', 'save', 'share', 'clap'], true)) {
                $eventType = $type === 'event' ? (string) ($payload['type'] ?? 'open') : $type;
                $pdo->prepare('INSERT INTO engagement_events (id, user_id, anonymous_id, story_slug, event_type, value, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([uuid_value(), $item['user_id'] ?: null, $item['client_id'] ?: null, substr((string) ($payload['storySlug'] ?? ''), 0, 200), $eventType, $payload['value'] ?? null, json_encode($payload['metadata'] ?? [], JSON_UNESCAPED_SLASHES), now_iso()]);
            } elseif ($type === 'user-document' && $item['user_id']) {
                $key = (string) ($payload['key'] ?? '');
                if (in_array($key, USER_DOCUMENTS, true)) {
                    $pdo->prepare('INSERT INTO user_documents (user_id, key, value_json, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id, key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
                        ->execute([$item['user_id'], $key, json_encode($payload['value'] ?? null, JSON_UNESCAPED_SLASHES), now_iso()]);
                }
            }
            $pdo->prepare("UPDATE pwa_sync_queue SET status = 'processed', processed_at = ? WHERE id = ?")->execute([now_iso(), $item['id']]);
            $processed++;
        } catch (Throwable) {
            $pdo->prepare("UPDATE pwa_sync_queue SET status = 'failed', processed_at = ? WHERE id = ?")->execute([now_iso(), $item['id']]);
            $failed++;
        }
    }
    return ['processed' => $processed, 'failed' => $failed, 'total' => count($items)];
}

function scan_moderation_rules(): array
{
    $pdo = Database::pdo();
    $rules = $pdo->query('SELECT * FROM moderation_rules WHERE enabled = 1')->fetchAll();
    foreach ($pdo->query('SELECT * FROM moderation_dictionary')->fetchAll() as $term) {
        $rules[] = [
            'id' => $term['id'],
            'kind' => $term['kind'],
            'pattern' => $term['term'],
            'action' => $term['action'],
        ];
    }
    $comments = $pdo->query("SELECT comments.*, users.id AS reporter FROM comments JOIN users ON users.id = comments.user_id WHERE comments.status = 'published' ORDER BY comments.created_at DESC LIMIT 500")->fetchAll();
    $created = 0;
    $seenCommentHashes = [];
    foreach ($comments as $comment) {
        $text = strtolower(trim((string) $comment['text']));
        $linkCount = preg_match_all('#https?://#i', $comment['text']);
        $wordCount = max(1, str_word_count(strip_tags((string) $comment['text'])));
        $hash = hash('sha256', preg_replace('/\s+/', ' ', $text));
        $builtInHit = '';
        if ($linkCount >= 3 || ($linkCount >= 1 && $wordCount < 8)) $builtInHit = 'bot_score';
        if (isset($seenCommentHashes[$hash])) $builtInHit = 'duplicate_check';
        $seenCommentHashes[$hash] = true;
        if ($builtInHit !== '') {
            $exists = $pdo->prepare("SELECT id FROM moderation_cases WHERE target_type = 'comment' AND target_id = ? AND kind = ? AND status NOT IN ('Resolved', 'Dismissed') LIMIT 1");
            $exists->execute([$comment['id'], $builtInHit]);
            if ($exists->fetch()) continue;
            $id = uuid_value('MOD-');
            $pdo->prepare("INSERT INTO moderation_cases (id, reporter_user_id, target_type, target_id, kind, subject, risk, status, evidence_json, created_at, updated_at) VALUES (?, ?, 'comment', ?, ?, ?, 'Medium', 'Open', ?, ?, ?)")
                ->execute([$id, $comment['reporter'], $comment['id'], $builtInHit, 'Automated abuse protection matched', json_encode([['ruleId' => 'built-in', 'reason' => $builtInHit, 'links' => $linkCount, 'words' => $wordCount]], JSON_UNESCAPED_SLASHES), now_iso(), now_iso()]);
            $created++;
            continue;
        }
        foreach ($rules as $rule) {
            $pattern = strtolower($rule['pattern']);
            $hit = $rule['kind'] === 'blocked_word' ? str_contains($text, $pattern) : ($rule['kind'] === 'link_filter' && preg_match('#https?://#i', $comment['text']));
            if (!$hit) continue;
            $id = uuid_value('MOD-');
            $pdo->prepare("INSERT INTO moderation_cases (id, reporter_user_id, target_type, target_id, kind, subject, risk, status, evidence_json, created_at, updated_at) VALUES (?, ?, 'comment', ?, ?, ?, 'Medium', 'Open', ?, ?, ?)")
                ->execute([$id, $comment['reporter'], $comment['id'], $rule['kind'], 'Automated moderation rule matched', json_encode([['ruleId' => $rule['id'], 'pattern' => $rule['pattern']]], JSON_UNESCAPED_SLASHES), now_iso(), now_iso()]);
            if (in_array($rule['action'], ['hide', 'block'], true)) $pdo->prepare("UPDATE comments SET status = 'hidden', updated_at = ? WHERE id = ?")->execute([now_iso(), $comment['id']]);
            $created++;
            break;
        }
    }
    $stories = array_values(array_filter(document_value('stories', []), fn($story) => is_array($story)));
    $seenStoryHashes = [];
    foreach ($stories as $story) {
        $body = strtolower(trim(strip_tags((string) ($story['contentHtml'] ?? '') . ' ' . implode(' ', is_array($story['body'] ?? null) ? $story['body'] : []))));
        if ($body === '') continue;
        $fingerprint = hash('sha256', preg_replace('/\s+/', ' ', $body));
        if (isset($seenStoryHashes[$fingerprint])) {
            $targetId = (string) ($story['slug'] ?? $story['id'] ?? '');
            $exists = $pdo->prepare("SELECT id FROM moderation_cases WHERE target_type = 'post' AND target_id = ? AND kind = 'duplicate_check' AND status NOT IN ('Resolved', 'Dismissed') LIMIT 1");
            $exists->execute([$targetId]);
            if ($exists->fetch()) continue;
            $id = uuid_value('MOD-');
            $pdo->prepare("INSERT INTO moderation_cases (id, reporter_user_id, target_type, target_id, kind, subject, risk, status, evidence_json, created_at, updated_at) VALUES (?, NULL, 'post', ?, 'duplicate_check', ?, 'Medium', 'Open', ?, ?, ?)")
                ->execute([$id, $targetId, 'Possible duplicate story content', json_encode([['ruleId' => 'built-in', 'duplicateOf' => $seenStoryHashes[$fingerprint]]], JSON_UNESCAPED_SLASHES), now_iso(), now_iso()]);
            $created++;
        } else {
            $seenStoryHashes[$fingerprint] = (string) ($story['slug'] ?? $story['id'] ?? '');
        }
    }
    return ['scanned' => count($comments) + count($stories), 'created' => $created];
}

function test_provider_connection(string $provider): array
{
    try {
        if ($provider === 'paypal') {
            $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
            $token = http_request_json($base . '/v1/oauth2/token', [
                'method' => 'POST',
                'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => ['grant_type' => 'client_credentials'],
                'timeout' => 15,
            ]);
            return ['provider' => $provider, 'ready' => !empty($token['access_token']), 'mode' => 'live_token'];
        }
        if ($provider === 'razorpay') {
            http_request_json('https://api.razorpay.com/v1/payments?count=1', [
                'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('RAZORPAY_KEY_ID', 'razorpay', 'key_id') . ':' . provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret'))],
                'timeout' => 15,
            ]);
            return ['provider' => $provider, 'ready' => true, 'mode' => 'live_api'];
        }
        if ($provider === 'cashfree') {
            $base = provider_config_value('CASHFREE_ENVIRONMENT', 'cashfree', 'environment', 'sandbox') === 'production' ? 'https://api.cashfree.com/pg/orders/non-existent-health-check' : 'https://sandbox.cashfree.com/pg/orders/non-existent-health-check';
            try {
                http_request_json($base, ['headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret')], 'timeout' => 15]);
            } catch (Throwable $error) {
                if (!str_contains($error->getMessage(), '401') && !str_contains($error->getMessage(), '403')) return ['provider' => $provider, 'ready' => true, 'mode' => 'authenticated_probe'];
                throw $error;
            }
            return ['provider' => $provider, 'ready' => true, 'mode' => 'authenticated_probe'];
        }
        if ($provider === 'openai') {
            openai_text_response('Reply with OK.', 'Health check.');
            return ['provider' => $provider, 'ready' => true, 'mode' => 'live_completion'];
        }
        if ($provider === 'payu') {
            return ['provider' => $provider, 'ready' => provider_status()['payments']['payu'], 'mode' => 'hash_credentials'];
        }
        if ($provider === 'geoip') {
            $url = provider_config_value('IP_INTELLIGENCE_API_URL', 'geoip', 'api_url');
            $key = provider_config_value('IP_INTELLIGENCE_API_KEY', 'geoip', 'api_key');
            if (!$url || !$key) return ['provider' => $provider, 'ready' => false, 'error' => 'IP intelligence URL and API key are required.', 'mode' => 'configured'];
            $probeIp = request_ip_address() ?: '8.8.8.8';
            $result = http_request_json(str_replace('{ip}', rawurlencode($probeIp), $url), [
                'headers' => ['Authorization' => 'Bearer ' . $key],
                'timeout' => 8,
            ]);
            $country = $result['country'] ?? $result['country_code'] ?? $result['countryCode'] ?? null;
            return ['provider' => $provider, 'ready' => (bool) $country, 'mode' => 'live_geo_probe', 'country' => $country];
        }
        $status = provider_status();
        return ['provider' => $provider, 'ready' => (bool) ($status['payments'][$provider] ?? $status['social'][$provider] ?? $status[$provider] ?? false), 'mode' => 'configured'];
    } catch (Throwable $error) {
        return ['provider' => $provider, 'ready' => false, 'error' => $error->getMessage(), 'mode' => 'live_api'];
    }
}

function finish_oauth_login(string $provider, array $profile): never
{
    $pdo = Database::pdo();
    $providerUserId = (string) ($profile['id'] ?? $profile['sub'] ?? '');
    $email = strtolower(trim((string) ($profile['email'] ?? '')));
    $name = trim((string) ($profile['name'] ?? $profile['given_name'] ?? 'Reader'));
    if ($providerUserId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) redirect_response('/?oauth=profile_error');
    $stmt = $pdo->prepare('SELECT users.* FROM social_accounts JOIN users ON users.id = social_accounts.user_id WHERE social_accounts.provider = ? AND social_accounts.provider_user_id = ? AND users.status != ?');
    $stmt->execute([$provider, $providerUserId, 'deleted']);
    $user = $stmt->fetch();
    if (!$user) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status != 'deleted'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $now = now_iso();
            $id = uuid_value('USR-');
            $pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, subscription, status, email_verified, created_at, updated_at) VALUES (?, ?, ?, ?, 'reader', 'Free', 'active', 1, ?, ?)")
                ->execute([$id, $name, $email, hash_password_value(base64url_encode_value(random_bytes(32))), $now, $now]);
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
        }
        $pdo->prepare('INSERT OR REPLACE INTO social_accounts (provider, provider_user_id, user_id, email, created_at, updated_at) VALUES (?, ?, ?, ?, COALESCE((SELECT created_at FROM social_accounts WHERE provider = ? AND provider_user_id = ?), ?), ?)')
            ->execute([$provider, $providerUserId, $user['id'], $email, $provider, $providerUserId, now_iso(), now_iso()]);
    }
    $token = create_session_for_user($user['id']);
    set_session_cookie($token);
    audit_log($user['id'], 'auth.oauth_login', 'user', $user['id'], ['provider' => $provider]);
    redirect_response('/dashboard?oauth=success');
}

function handle_api(string $path, string $method): void
{
    $pdo = Database::pdo();

    if ($method === 'POST' && $path === '/api/cron/run') {
        $secret = env_value('CRON_SECRET') ?: provider_config_value('CRON_SECRET', 'cron', 'secret');
        $provided = (string) ($_SERVER['HTTP_X_CRON_SECRET'] ?? ($_GET['token'] ?? ''));
        if (!$secret || !hash_equals((string) $secret, $provided)) {
            json_response(['error' => 'FORBIDDEN', 'message' => 'A valid cron secret is required.'], 403);
        }
        $body = read_json();
        if (!empty($body['enqueueDefaults'])) {
            enqueue_background_job('publishing.scheduled');
            enqueue_background_job('newsletters.send');
            enqueue_background_job('checkouts.recover');
            enqueue_background_job('subscriptions.sync');
            enqueue_background_job('payouts.execute');
            enqueue_background_job('pwa.sync');
            enqueue_background_job('moderation.scan');
        }
        json_response(run_due_background_jobs((int) ($body['limit'] ?? 20)));
    }

    if ($method === 'GET' && $path === '/api/auth/session') {
        $session = current_session();
        json_response(['user' => $session['user'] ?? null]);
    }

    if ($method === 'PATCH' && $path === '/api/me/account') {
        $session = require_auth();
        $body = read_json();
        $name = trim((string) ($body['name'] ?? $session['user']['name']));
        $email = strtolower(trim((string) ($body['email'] ?? $session['user']['email'])));
        $username = clean_username_value((string) ($body['username'] ?? ($session['user']['username'] ?? '')));
        $currentPassword = (string) ($body['currentPassword'] ?? '');
        if (strlen($name) < 2) json_response(['error' => 'INVALID_NAME', 'message' => 'Enter your full display name.'], 400);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'INVALID_EMAIL', 'message' => 'Enter a valid email address.'], 400);
        if ($error = username_error($username)) json_response(['error' => 'INVALID_USERNAME', 'message' => $error], 400);
        if ($username !== '') {
            $usernameExists = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ? AND status != 'deleted'");
            $usernameExists->execute([$username, $session['user']['id']]);
            if ($usernameExists->fetch()) json_response(['error' => 'USERNAME_EXISTS', 'message' => 'That username is already taken.'], 409);
        }
        $emailChanged = strtolower((string) $session['user']['email']) !== $email;
        if ($emailChanged) {
            if ($currentPassword === '' || !verify_password_value($currentPassword, (string) $session['raw']['password_hash'])) {
                json_response(['error' => 'PASSWORD_REQUIRED', 'message' => 'Enter your current password to change your email address.'], 403);
            }
            $exists = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND status != 'deleted'");
            $exists->execute([$email, $session['user']['id']]);
            if ($exists->fetch()) json_response(['error' => 'EMAIL_EXISTS', 'message' => 'Another account already uses that email address.'], 409);
        }
        $now = now_iso();
        $pdo->prepare('UPDATE users SET name = ?, email = ?, username = ?, email_verified = CASE WHEN ? THEN 0 ELSE email_verified END, updated_at = ? WHERE id = ?')
            ->execute([$name, $email, $username !== '' ? $username : null, $emailChanged ? 1 : 0, $now, $session['user']['id']]);
        if ($emailChanged) {
            $pdo->prepare("DELETE FROM security_tokens WHERE user_id = ? AND type = 'email_verification' AND consumed_at IS NULL")->execute([$session['user']['id']]);
            audit_log($session['user']['id'], 'account.email_changed', 'user', $session['user']['id'], ['oldEmail' => $session['user']['email'], 'newEmail' => $email]);
        } else {
            audit_log($session['user']['id'], 'account.profile_updated', 'user', $session['user']['id']);
        }
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$session['user']['id']]);
        json_response(['user' => public_user($stmt->fetch()), 'emailVerificationRequired' => $emailChanged]);
    }

    if ($method === 'POST' && $path === '/api/me/password') {
        $session = require_auth();
        $body = read_json();
        $currentPassword = (string) ($body['currentPassword'] ?? '');
        $newPassword = (string) ($body['newPassword'] ?? '');
        if ($currentPassword === '' || !verify_password_value($currentPassword, (string) $session['raw']['password_hash'])) {
            json_response(['error' => 'INVALID_CURRENT_PASSWORD', 'message' => 'Your current password is incorrect.'], 403);
        }
        $passwordError = password_policy_error($newPassword);
        if ($passwordError) json_response(['error' => 'WEAK_PASSWORD', 'message' => $passwordError], 400);
        $now = now_iso();
        $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?')->execute([hash_password_value($newPassword), $now, $session['user']['id']]);
        $currentToken = $_COOKIE['inkriver_session'] ?? '';
        if ($currentToken) $pdo->prepare('DELETE FROM sessions WHERE user_id = ? AND token_hash != ?')->execute([$session['user']['id'], session_token_hash($currentToken)]);
        audit_log($session['user']['id'], 'account.password_changed', 'user', $session['user']['id']);
        json_response(['ok' => true, 'otherSessionsRevoked' => true]);
    }

    if ($method === 'POST' && $path === '/api/me/avatar') {
        $session = require_auth();
        if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            json_response(['error' => 'NO_FILE', 'message' => 'Choose a profile photo to upload.'], 400);
        }
        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) json_response(['error' => 'UPLOAD_FAILED', 'message' => 'The upload failed.'], 400);
        if ((int) $file['size'] > 4 * 1024 * 1024) json_response(['error' => 'FILE_TOO_LARGE', 'message' => 'Profile photos must be 4 MB or smaller.'], 413);
        $info = getimagesize($file['tmp_name']);
        if (!$info) json_response(['error' => 'INVALID_IMAGE', 'message' => 'Only valid image files can be uploaded.'], 400);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = $info['mime'] ?? '';
        if (!isset($allowed[$mime])) json_response(['error' => 'UNSUPPORTED_IMAGE', 'message' => 'Use JPG, PNG, WebP, or GIF.'], 400);
        $relativeDir = 'uploads/avatars/' . gmdate('Y/m');
        $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
        if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0775, true);
        $storedName = uuid_value('avatar-') . '.' . $allowed[$mime];
        $target = $absoluteDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $target)) json_response(['error' => 'UPLOAD_STORE_FAILED', 'message' => 'Could not save the profile photo.'], 500);
        $url = '/' . $relativeDir . '/' . $storedName;
        $now = now_iso();
        $pdo->prepare('UPDATE users SET avatar_url = ?, updated_at = ? WHERE id = ?')->execute([$url, $now, $session['user']['id']]);
        audit_log($session['user']['id'], 'account.avatar_updated', 'user', $session['user']['id']);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$session['user']['id']]);
        json_response(['user' => public_user($stmt->fetch()), 'avatarUrl' => $url], 201);
    }

    if ($method === 'DELETE' && $path === '/api/me/avatar') {
        $session = require_auth();
        $pdo->prepare("UPDATE users SET avatar_url = '', updated_at = ? WHERE id = ?")->execute([now_iso(), $session['user']['id']]);
        audit_log($session['user']['id'], 'account.avatar_removed', 'user', $session['user']['id']);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$session['user']['id']]);
        json_response(['user' => public_user($stmt->fetch())]);
    }

    if ($method === 'POST' && $path === '/api/auth/register') {
        $body = read_json();
        $name = trim((string) ($body['name'] ?? ''));
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $password = (string) ($body['password'] ?? '');
        $passwordError = password_policy_error($password);
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $passwordError) {
            json_response(['error' => 'INVALID_REGISTRATION', 'message' => 'Enter a valid name, email, and password.'], 400);
        }
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status != 'deleted'");
        $exists->execute([$email]);
        if ($exists->fetch()) json_response(['error' => 'EMAIL_EXISTS', 'message' => 'An account already exists for this email address.'], 409);
        $id = uuid_value('USR-');
        $now = now_iso();
        $pdo->prepare("INSERT INTO users (id, name, email, password_hash, role, subscription, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'reader', 'Free', 'active', ?, ?)")
            ->execute([$id, $name, $email, hash_password_value($password), $now, $now]);
        $token = create_session_for_user($id);
        set_session_cookie($token);
        audit_log($id, 'auth.register', 'user', $id);
        json_response(['user' => public_user(['id' => $id, 'name' => $name, 'email' => $email, 'role' => 'reader', 'subscription' => 'Free', 'status' => 'active', 'email_verified' => 0, 'created_at' => $now, 'updated_at' => $now])], 201);
    }

    if ($method === 'POST' && $path === '/api/auth/login') {
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $password = (string) ($body['password'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status != 'deleted'");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row || !verify_password_value($password, $row['password_hash'])) {
            json_response(['error' => 'INVALID_LOGIN', 'message' => 'Email or password is incorrect.'], 401);
        }
        if ($row['status'] !== 'active') json_response(['error' => 'ACCOUNT_DISABLED', 'message' => 'This account is not active.'], 403);
        $security = $pdo->prepare('SELECT * FROM user_security_settings WHERE user_id = ?');
        $security->execute([$row['id']]);
        $securityRow = $security->fetch();
        if (!empty($securityRow['two_factor_enabled'])) {
            $twoFactorCode = trim((string) ($body['twoFactorCode'] ?? $body['code'] ?? ''));
            $validTotp = $twoFactorCode !== '' && verify_totp_value((string) $securityRow['two_factor_secret'], $twoFactorCode);
            $codes = parse_json_field($securityRow['recovery_codes_json'] ?? '[]', []);
            $validRecovery = false;
            if (!$validTotp && $twoFactorCode !== '') {
                $hash = hash('sha256', strtoupper($twoFactorCode));
                foreach ($codes as $index => $codeHash) {
                    if (hash_equals((string) $codeHash, $hash)) {
                        unset($codes[$index]);
                        $pdo->prepare('UPDATE user_security_settings SET recovery_codes_json = ?, updated_at = ? WHERE user_id = ?')
                            ->execute([json_encode(array_values($codes)), now_iso(), $row['id']]);
                        $validRecovery = true;
                        break;
                    }
                }
            }
            if (!$validTotp && !$validRecovery) {
                json_response(['error' => 'TWO_FACTOR_REQUIRED', 'message' => 'Enter a valid authenticator or recovery code.'], 401);
            }
        }
        $now = now_iso();
        $pdo->prepare('UPDATE users SET last_login_at = ?, updated_at = ? WHERE id = ?')->execute([$now, $now, $row['id']]);
        $row['last_login_at'] = $now;
        $token = create_session_for_user($row['id']);
        set_session_cookie($token);
        audit_log($row['id'], 'auth.login', 'user', $row['id']);
        if (!empty($securityRow['login_alerts_enabled'])) {
            send_email_message($row['email'], 'New InkRiver sign-in', [
                'name' => $row['name'],
                'ip' => request_ip_address(),
                'userAgent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                'time' => $now,
            ]);
        }
        json_response(['user' => public_user($row)]);
    }

    if ($method === 'POST' && $path === '/api/auth/passkeys/challenge') {
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['error' => 'EMAIL_REQUIRED', 'message' => 'Enter your email before using a passkey.'], 400);
        }
        $stmt = $pdo->prepare("SELECT id, email, status FROM users WHERE email = ? AND status != 'deleted'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || $user['status'] !== 'active') json_response(['error' => 'PASSKEY_UNAVAILABLE', 'message' => 'No active passkeys are available for this account.'], 404);
        $credentials = $pdo->prepare('SELECT credential_id, transports FROM passkey_credentials WHERE user_id = ? ORDER BY created_at DESC');
        $credentials->execute([$user['id']]);
        $rows = $credentials->fetchAll();
        if (!$rows) json_response(['error' => 'PASSKEY_UNAVAILABLE', 'message' => 'No active passkeys are available for this account.'], 404);
        $challenge = base64url_encode_value(random_bytes(32));
        $now = now_iso();
        $pdo->prepare('INSERT INTO user_documents (user_id, key, value_json, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id, key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
            ->execute([$user['id'], 'passkey-login-challenge', json_encode(['challenge' => $challenge, 'createdAt' => $now], JSON_UNESCAPED_SLASHES), $now]);
        json_response([
            'challenge' => $challenge,
            'rpId' => webauthn_rp_id(),
            'allowCredentials' => array_map(fn($row) => [
                'type' => 'public-key',
                'id' => $row['credential_id'],
                'transports' => parse_json_field($row['transports'] ?? '[]', []),
            ], $rows),
            'timeout' => 60000,
            'userVerification' => 'preferred',
        ]);
    }

    if ($method === 'POST' && $path === '/api/auth/passkeys/login') {
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $credentialId = trim((string) ($body['credentialId'] ?? ''));
        $clientDataJson = base64url_decode_value((string) ($body['clientDataJSON'] ?? ''));
        $authenticatorData = base64url_decode_value((string) ($body['authenticatorData'] ?? ''));
        $signature = base64url_decode_value((string) ($body['signature'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $credentialId === '' || $clientDataJson === '' || $authenticatorData === '' || $signature === '') {
            json_response(['error' => 'INVALID_PASSKEY_ASSERTION', 'message' => 'A complete passkey assertion is required.'], 400);
        }
        $stmt = $pdo->prepare("SELECT users.*, passkey_credentials.id AS passkey_id, passkey_credentials.public_key, passkey_credentials.sign_count FROM users JOIN passkey_credentials ON passkey_credentials.user_id = users.id WHERE users.email = ? AND passkey_credentials.credential_id = ? AND users.status != 'deleted'");
        $stmt->execute([$email, $credentialId]);
        $row = $stmt->fetch();
        if (!$row || $row['status'] !== 'active') json_response(['error' => 'PASSKEY_NOT_FOUND', 'message' => 'Passkey was not found for this account.'], 404);
        $challengeStmt = $pdo->prepare("SELECT value_json FROM user_documents WHERE user_id = ? AND key = 'passkey-login-challenge'");
        $challengeStmt->execute([$row['id']]);
        $challengeRow = $challengeStmt->fetch();
        $challenge = $challengeRow ? parse_json_field($challengeRow['value_json'], []) : [];
        $clientData = json_decode($clientDataJson, true);
        if (!is_array($clientData)
            || ($clientData['type'] ?? '') !== 'webauthn.get'
            || !hash_equals((string) ($challenge['challenge'] ?? ''), (string) ($clientData['challenge'] ?? ''))
            || !hash_equals(webauthn_expected_origin(), (string) ($clientData['origin'] ?? ''))
            || strtotime((string) ($challenge['createdAt'] ?? '1970-01-01')) < time() - 300) {
            json_response(['error' => 'INVALID_PASSKEY_CHALLENGE', 'message' => 'The passkey challenge expired or did not match.'], 400);
        }
        if (strlen($authenticatorData) < 37 || !hash_equals(hash('sha256', webauthn_rp_id(), true), substr($authenticatorData, 0, 32))) {
            json_response(['error' => 'INVALID_PASSKEY_ASSERTION', 'message' => 'Passkey authenticator data is invalid for this site.'], 400);
        }
        $flags = ord($authenticatorData[32]);
        if (($flags & 0x01) === 0) json_response(['error' => 'USER_PRESENCE_REQUIRED', 'message' => 'Passkey user presence was not verified.'], 400);
        $counter = unpack('N', substr($authenticatorData, 33, 4))[1];
        $publicKey = parse_json_field((string) $row['public_key'], []);
        $verified = openssl_verify($authenticatorData . hash('sha256', $clientDataJson, true), $signature, webauthn_public_key_pem($publicKey), OPENSSL_ALGO_SHA256);
        if ($verified !== 1) json_response(['error' => 'INVALID_PASSKEY_SIGNATURE', 'message' => 'Passkey signature verification failed.'], 401);
        if ($counter > 0 && (int) $row['sign_count'] > 0 && $counter <= (int) $row['sign_count']) {
            audit_log($row['id'], 'security.passkey_counter_replay', 'passkey', $row['passkey_id']);
            json_response(['error' => 'PASSKEY_REPLAY_DETECTED', 'message' => 'This passkey assertion was rejected for security reasons.'], 401);
        }
        $now = now_iso();
        $pdo->prepare('UPDATE passkey_credentials SET sign_count = ?, last_used_at = ? WHERE id = ?')->execute([$counter, $now, $row['passkey_id']]);
        $pdo->prepare("DELETE FROM user_documents WHERE user_id = ? AND key = 'passkey-login-challenge'")->execute([$row['id']]);
        $pdo->prepare('UPDATE users SET last_login_at = ?, updated_at = ? WHERE id = ?')->execute([$now, $now, $row['id']]);
        $row['last_login_at'] = $now;
        $token = create_session_for_user($row['id']);
        set_session_cookie($token);
        audit_log($row['id'], 'auth.passkey_login', 'user', $row['id']);
        json_response(['user' => public_user($row)]);
    }

    if ($method === 'POST' && $path === '/api/auth/logout') {
        $token = $_COOKIE['inkriver_session'] ?? '';
        if ($token) $pdo->prepare('DELETE FROM sessions WHERE token_hash = ?')->execute([session_token_hash($token)]);
        clear_session_cookie();
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/auth/password/forgot') {
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $fallbackUrl = null;
        if ($user) {
            $reset = create_password_reset_token($user);
            $sent = send_email_message($user['email'], 'Reset your InkRiver password', ['name' => $user['name'], 'resetUrl' => $reset['url'], 'expiresAt' => $reset['expiresAt']]);
            if (!$sent) $fallbackUrl = $reset['url'];
            audit_log($user['id'], 'auth.password_reset_requested', 'user', $user['id']);
        }
        json_response(['ok' => true, 'sent' => $user ? provider_status()['email'] : false, 'resetUrl' => is_production() ? null : $fallbackUrl]);
    }

    if ($method === 'POST' && $path === '/api/auth/password/reset') {
        $body = read_json();
        $token = (string) ($body['token'] ?? '');
        $password = (string) ($body['password'] ?? '');
        $passwordError = password_policy_error($password);
        if ($passwordError) json_response(['error' => 'WEAK_PASSWORD', 'message' => $passwordError], 400);
        $stmt = $pdo->prepare("SELECT security_tokens.*, users.status FROM security_tokens JOIN users ON users.id = security_tokens.user_id WHERE token_hash = ? AND type = 'password_recovery' AND consumed_at IS NULL AND expires_at > ?");
        $stmt->execute([hash('sha256', $token), now_iso()]);
        $row = $stmt->fetch();
        if (!$row || $row['status'] !== 'active') json_response(['error' => 'INVALID_RESET_TOKEN', 'message' => 'This reset link is invalid or expired.'], 400);
        $now = now_iso();
        $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?')->execute([hash_password_value($password), $now, $row['user_id']]);
        $pdo->prepare('UPDATE security_tokens SET consumed_at = ? WHERE id = ?')->execute([$now, $row['id']]);
        $pdo->prepare('DELETE FROM sessions WHERE user_id = ?')->execute([$row['user_id']]);
        audit_log($row['user_id'], 'auth.password_reset_completed', 'user', $row['user_id']);
        json_response(['ok' => true]);
    }

    if ($method === 'GET' && $path === '/api/platform/bootstrap') {
        $session = current_session();
        $keys = $session && ($session['user']['role'] ?? '') === 'admin'
            ? array_values(array_unique(array_merge(PUBLIC_DOCUMENTS, ADMIN_DOCUMENTS)))
            : PUBLIC_DOCUMENTS;
        $documents = [];
        foreach ($keys as $key) {
            $stmt = $pdo->prepare('SELECT value_json FROM platform_documents WHERE key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) $documents[$key] = parse_json_field($row['value_json'], null);
        }
        $userDocuments = [];
        if ($session) {
            $stmt = $pdo->prepare('SELECT key, value_json FROM user_documents WHERE user_id = ?');
            $stmt->execute([$session['user']['id']]);
            foreach ($stmt->fetchAll() as $row) {
                if (str_starts_with((string) $row['key'], 'passkey-')) continue;
                $userDocuments[$row['key']] = parse_json_field($row['value_json'], null);
            }
        }
        $storyStats = [];
        foreach ($pdo->query("SELECT story_slug, event_type, COUNT(*) AS count FROM engagement_events WHERE story_slug IS NOT NULL GROUP BY story_slug, event_type")->fetchAll() as $row) {
            $storyStats[$row['story_slug']] ??= ['reads' => 0, 'claps' => 0, 'comments' => 0];
            if ($row['event_type'] === 'open') $storyStats[$row['story_slug']]['reads'] = (int) $row['count'];
            if ($row['event_type'] === 'clap') $storyStats[$row['story_slug']]['claps'] = (int) $row['count'];
        }
        json_response([
            'documents' => $documents,
            'userDocuments' => $userDocuments,
            'storyStats' => $storyStats,
            'translations' => stored_translations(),
            'translationLanguages' => configured_translation_languages(),
            'ads' => active_ads(),
            'featureFlags' => public_feature_flags($session),
            'providers' => provider_status(),
            'paymentPolicy' => ['paypalIndiaRestriction' => paypal_restricted_for_india($session)],
            'pushPublicKey' => provider_config_value('VAPID_PUBLIC_KEY', 'push', 'vapid_public_key', '') ?: '',
            'socialAccounts' => [],
        ]);
    }

    if ($method === 'GET' && $path === '/api/search') {
        json_response(['results' => search_stories($_GET), 'query' => (string) ($_GET['q'] ?? '')]);
    }

    if ($method === 'GET' && $path === '/api/search/suggest') {
        $results = search_stories(['q' => $_GET['q'] ?? '', 'limit' => 8, 'sort' => 'relevance']);
        $authors = [];
        foreach (recommendation_story_catalog() as $story) {
            $name = (string) ($story['author'] ?? '');
            if ($name && stripos($name, (string) ($_GET['q'] ?? '')) !== false) $authors[$name] = ['type' => 'Writer', 'label' => $name, 'meta' => (string) ($story['topic'] ?? ''), 'route' => '/@' . strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'))];
        }
        $suggestions = array_map(fn($story) => ['type' => 'Story', 'label' => $story['title'], 'meta' => $story['author'] . ' · ' . $story['topic'], 'route' => '/stories/' . $story['slug']], $results);
        json_response(['suggestions' => array_slice(array_merge($suggestions, array_values($authors)), 0, 8)]);
    }

    if ($method === 'GET' && preg_match('#^/api/newsletters/([^/]+)/(open|click)$#', $path, $m)) {
        $newsletterId = urldecode($m[1]);
        $eventType = $m[2];
        $userId = (string) ($_GET['u'] ?? '');
        $metadata = $eventType === 'click' ? ['url' => (string) ($_GET['url'] ?? '')] : [];
        try {
            $pdo->prepare('INSERT INTO newsletter_events (id, newsletter_id, user_id, event_type, metadata_json, created_at) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([uuid_value('NLE-'), $newsletterId, $userId ?: null, $eventType, json_encode($metadata, JSON_UNESCAPED_SLASHES), now_iso()]);
        } catch (Throwable) {
        }
        if ($eventType === 'click') {
            $url = (string) ($_GET['url'] ?? '/');
            if (!preg_match('#^(https?://|/)#i', $url)) $url = '/';
            redirect_response($url);
        }
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');
        exit;
    }

    if ($method === 'GET' && $path === '/api/newsletters/unsubscribe') {
        $newsletterId = (string) ($_GET['n'] ?? '');
        $userId = (string) ($_GET['u'] ?? '');
        $token = (string) ($_GET['t'] ?? '');
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user || !hash_equals(newsletter_token($newsletterId, $userId, (string) $user['email']), $token)) {
            http_response_code(403);
            echo 'Invalid unsubscribe link.';
            exit;
        }
        $pdo->prepare("INSERT INTO newsletter_suppressions (email, user_id, reason, source_newsletter_id, created_at) VALUES (?, ?, 'unsubscribe', ?, ?) ON CONFLICT(email) DO UPDATE SET reason = excluded.reason, source_newsletter_id = excluded.source_newsletter_id, created_at = excluded.created_at")
            ->execute([strtolower((string) $user['email']), $userId, $newsletterId ?: null, now_iso()]);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><meta name="viewport" content="width=device-width,initial-scale=1"><title>Unsubscribed</title><body style="font-family:system-ui;margin:40px;line-height:1.5"><h1>You are unsubscribed</h1><p>You will no longer receive InkRiver newsletters at this email address.</p></body>';
        exit;
    }

    if ($method === 'GET' && $path === '/api/publications') {
        $publications = array_map(fn($row) => [
            'id' => $row['id'], 'slug' => $row['slug'], 'name' => $row['name'], 'description' => $row['description'], 'logoUrl' => $row['logo_url'], 'status' => $row['status'],
        ], current_publication_rows());
        json_response(['publications' => $publications]);
    }

    if ($method === 'POST' && $path === '/api/admin/publications') {
        $session = require_auth(['admin']);
        $body = read_json();
        $name = trim((string) ($body['name'] ?? ''));
        if (strlen($name) < 2) json_response(['error' => 'INVALID_PUBLICATION', 'message' => 'Publication name is required.'], 400);
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($body['slug'] ?? $name))), '-');
        $now = now_iso();
        $id = uuid_value('PUB-');
        $pdo->prepare("INSERT INTO publications (id, slug, name, description, logo_url, status, owner_user_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', ?, ?, ?) ON CONFLICT(slug) DO UPDATE SET name = excluded.name, description = excluded.description, logo_url = excluded.logo_url, status = 'active', updated_at = excluded.updated_at")
            ->execute([$id, $slug, $name, substr((string) ($body['description'] ?? ''), 0, 1000), substr((string) ($body['logoUrl'] ?? ''), 0, 1000), $session['user']['id'], $now, $now]);
        audit_log($session['user']['id'], 'admin.publication_upsert', 'publication', $slug);
        json_response(['publications' => current_publication_rows()], 201);
    }

    if (preg_match('#^/api/admin/publications/([^/]+)$#', $path, $m) && in_array($method, ['PATCH', 'DELETE'], true)) {
        $session = require_auth(['admin']);
        $idOrSlug = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT * FROM publications WHERE id = ? OR slug = ?');
        $stmt->execute([$idOrSlug, $idOrSlug]);
        $publication = $stmt->fetch();
        if (!$publication) json_response(['error' => 'PUBLICATION_NOT_FOUND', 'message' => 'Publication was not found.'], 404);
        if ($method === 'DELETE') {
            $pdo->prepare("UPDATE publications SET status = 'archived', updated_at = ? WHERE id = ?")->execute([now_iso(), $publication['id']]);
            audit_log($session['user']['id'], 'admin.publication_archive', 'publication', $publication['id']);
            json_response(['publications' => current_publication_rows()]);
        }
        $body = read_json();
        $name = trim((string) ($body['name'] ?? $publication['name']));
        if (strlen($name) < 2) json_response(['error' => 'INVALID_PUBLICATION', 'message' => 'Publication name is required.'], 400);
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($body['slug'] ?? $publication['slug']))), '-');
        if ($slug === '') json_response(['error' => 'INVALID_SLUG', 'message' => 'Publication slug is required.'], 400);
        $status = in_array(($body['status'] ?? $publication['status']), ['active', 'paused', 'archived'], true) ? (string) ($body['status'] ?? $publication['status']) : 'active';
        $pdo->prepare('UPDATE publications SET slug = ?, name = ?, description = ?, logo_url = ?, status = ?, updated_at = ? WHERE id = ?')
            ->execute([$slug, $name, substr((string) ($body['description'] ?? $publication['description']), 0, 1000), substr((string) ($body['logoUrl'] ?? $publication['logo_url']), 0, 1000), $status, now_iso(), $publication['id']]);
        audit_log($session['user']['id'], 'admin.publication_update', 'publication', $publication['id']);
        json_response(['publications' => current_publication_rows()]);
    }

    if (preg_match('#^/api/admin/publications/([^/]+)/members(?:/([^/]+))?$#', $path, $m)) {
        $session = require_auth(['admin']);
        $idOrSlug = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT * FROM publications WHERE id = ? OR slug = ?');
        $stmt->execute([$idOrSlug, $idOrSlug]);
        $publication = $stmt->fetch();
        if (!$publication) json_response(['error' => 'PUBLICATION_NOT_FOUND', 'message' => 'Publication was not found.'], 404);

        if ($method === 'GET') {
            $members = $pdo->prepare('SELECT publication_members.*, users.name, users.email, users.role AS platform_role FROM publication_members JOIN users ON users.id = publication_members.user_id WHERE publication_members.publication_id = ? ORDER BY publication_members.role, users.name');
            $members->execute([$publication['id']]);
            json_response(['members' => array_map(fn($row) => [
                'userId' => $row['user_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'role' => $row['role'],
                'platformRole' => $row['platform_role'],
                'createdAt' => $row['created_at'],
            ], $members->fetchAll())]);
        }

        if ($method === 'POST') {
            $body = read_json();
            $identifier = trim((string) ($body['userId'] ?? $body['email'] ?? ''));
            $role = in_array(($body['role'] ?? 'writer'), ['owner', 'editor', 'writer'], true) ? (string) ($body['role'] ?? 'writer') : 'writer';
            if ($identifier === '') json_response(['error' => 'USER_REQUIRED', 'message' => 'User ID or email is required.'], 400);
            $userStmt = $pdo->prepare('SELECT id, status FROM users WHERE id = ? OR email = ?');
            $userStmt->execute([$identifier, strtolower($identifier)]);
            $user = $userStmt->fetch();
            if (!$user || $user['status'] !== 'active') json_response(['error' => 'USER_NOT_FOUND', 'message' => 'Active user was not found.'], 404);
            $pdo->prepare('INSERT INTO publication_members (publication_id, user_id, role, created_at) VALUES (?, ?, ?, ?) ON CONFLICT(publication_id, user_id) DO UPDATE SET role = excluded.role')
                ->execute([$publication['id'], $user['id'], $role, now_iso()]);
            audit_log($session['user']['id'], 'admin.publication_member_upsert', 'publication', $publication['id']);
            json_response(['ok' => true]);
        }

        if ($method === 'DELETE' && !empty($m[2])) {
            $userId = urldecode($m[2]);
            $pdo->prepare('DELETE FROM publication_members WHERE publication_id = ? AND user_id = ?')->execute([$publication['id'], $userId]);
            audit_log($session['user']['id'], 'admin.publication_member_remove', 'publication', $publication['id']);
            json_response(['removed' => true]);
        }
    }

    if ($method === 'POST' && preg_match('#^/api/admin/publications/([^/]+)/invites$#', $path, $m)) {
        $session = require_auth(['admin']);
        $idOrSlug = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT * FROM publications WHERE id = ? OR slug = ?');
        $stmt->execute([$idOrSlug, $idOrSlug]);
        $publication = $stmt->fetch();
        if (!$publication) json_response(['error' => 'PUBLICATION_NOT_FOUND', 'message' => 'Publication was not found.'], 404);
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $role = in_array(($body['role'] ?? 'writer'), ['owner', 'editor', 'writer'], true) ? (string) ($body['role'] ?? 'writer') : 'writer';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'INVALID_EMAIL', 'message' => 'Enter a valid invite email.'], 400);
        $token = bin2hex(random_bytes(24));
        $now = now_iso();
        $expiresAt = gmdate('c', time() + 60 * 60 * 24 * 14);
        $id = uuid_value('PIN-');
        $pdo->prepare("UPDATE publication_invites SET status = 'revoked', updated_at = ? WHERE publication_id = ? AND email = ? AND status = 'pending'")
            ->execute([$now, $publication['id'], $email]);
        $pdo->prepare('INSERT INTO publication_invites (id, publication_id, email, role, token_hash, status, invited_by, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $publication['id'], $email, $role, hash('sha256', $token), 'pending', $session['user']['id'], $expiresAt, $now, $now]);
        $inviteUrl = app_url('/publication-invites/' . $token);
        send_email_message($email, 'Publication invite: ' . $publication['name'], [
            'type' => 'publication_invite',
            'publication' => $publication['name'],
            'role' => $role,
            'inviteUrl' => $inviteUrl,
            'html' => '<p>You have been invited to join ' . htmlspecialchars($publication['name'], ENT_QUOTES, 'UTF-8') . ' as ' . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . '.</p><p><a href="' . htmlspecialchars($inviteUrl, ENT_QUOTES, 'UTF-8') . '">Accept invite</a></p>',
        ]);
        audit_log($session['user']['id'], 'admin.publication_invite_create', 'publication', $publication['id']);
        json_response(['inviteId' => $id, 'inviteUrl' => $inviteUrl], 201);
    }

    if ($method === 'POST' && $path === '/api/publication-invites/accept') {
        $session = require_auth();
        $body = read_json();
        $token = (string) ($body['token'] ?? '');
        if ($token === '') json_response(['error' => 'TOKEN_REQUIRED', 'message' => 'Invite token is required.'], 400);
        $stmt = $pdo->prepare("SELECT publication_invites.*, publications.name AS publication_name FROM publication_invites JOIN publications ON publications.id = publication_invites.publication_id WHERE token_hash = ? AND publication_invites.status = 'pending' LIMIT 1");
        $stmt->execute([hash('sha256', $token)]);
        $invite = $stmt->fetch();
        if (!$invite || strtotime($invite['expires_at']) < time()) json_response(['error' => 'INVITE_EXPIRED', 'message' => 'This publication invite is no longer valid.'], 410);
        if (strtolower((string) $session['user']['email']) !== strtolower((string) $invite['email']) && ($session['user']['role'] ?? '') !== 'admin') {
            json_response(['error' => 'INVITE_EMAIL_MISMATCH', 'message' => 'Sign in with the invited email address to accept this invite.'], 403);
        }
        $now = now_iso();
        $pdo->prepare('INSERT INTO publication_members (publication_id, user_id, role, created_at) VALUES (?, ?, ?, ?) ON CONFLICT(publication_id, user_id) DO UPDATE SET role = excluded.role')
            ->execute([$invite['publication_id'], $session['user']['id'], $invite['role'], $now]);
        $pdo->prepare("UPDATE publication_invites SET status = 'accepted', updated_at = ? WHERE id = ?")->execute([$now, $invite['id']]);
        audit_log($session['user']['id'], 'publication_invite_accept', 'publication', $invite['publication_id']);
        json_response(['accepted' => true, 'publication' => $invite['publication_name']]);
    }

    if (preg_match('#^/api/stories/([^/]+)/collaboration-notes(?:/([^/]+))?$#', $path, $m)) {
        $session = require_auth(['admin', 'moderator', 'writer']);
        $slug = substr(urldecode($m[1]), 0, 200);
        if ($method === 'GET') {
            $stmt = $pdo->prepare('SELECT draft_collaboration_notes.*, users.name AS user_name FROM draft_collaboration_notes LEFT JOIN users ON users.id = draft_collaboration_notes.user_id WHERE story_slug = ? ORDER BY created_at DESC');
            $stmt->execute([$slug]);
            json_response(['notes' => $stmt->fetchAll()]);
        }
        if ($method === 'POST') {
            $body = read_json();
            $bodyText = trim((string) ($body['body'] ?? ''));
            if ($bodyText === '') json_response(['error' => 'NOTE_REQUIRED', 'message' => 'Note body is required.'], 400);
            $id = uuid_value('DCN-');
            $now = now_iso();
            $type = in_array(($body['noteType'] ?? 'comment'), ['comment', 'suggestion', 'approval', 'blocker'], true) ? (string) ($body['noteType'] ?? 'comment') : 'comment';
            $pdo->prepare('INSERT INTO draft_collaboration_notes (id, story_slug, user_id, note_type, body, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$id, $slug, $session['user']['id'], $type, substr($bodyText, 0, 4000), 'open', $now, $now]);
            json_response(['noteId' => $id], 201);
        }
        if ($method === 'PATCH' && !empty($m[2])) {
            $body = read_json();
            $status = in_array(($body['status'] ?? 'open'), ['open', 'resolved', 'dismissed'], true) ? (string) ($body['status'] ?? 'open') : 'open';
            $pdo->prepare('UPDATE draft_collaboration_notes SET status = ?, updated_at = ? WHERE id = ? AND story_slug = ?')
                ->execute([$status, now_iso(), urldecode($m[2]), $slug]);
            json_response(['status' => $status]);
        }
    }

    if (preg_match('#^/api/admin/documents/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin']);
        $key = urldecode($m[1]);
        if (!in_array($key, ADMIN_DOCUMENTS, true)) json_response(['error' => 'INVALID_DOCUMENT', 'message' => 'This platform document cannot be managed.'], 400);
        if ($method === 'GET') {
            $stmt = $pdo->prepare('SELECT value_json, updated_at FROM platform_documents WHERE key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            json_response(['value' => $row ? parse_json_field($row['value_json'], null) : null, 'updatedAt' => $row['updated_at'] ?? null]);
        }
        if ($method === 'PUT') {
            $body = read_json();
            $updatedAt = now_iso();
            $value = $body['value'] ?? null;
            $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
                ->execute([$key, json_encode($value, JSON_UNESCAPED_SLASHES), $session['user']['id'], $updatedAt]);
            if ($key === 'site-seo') {
                $pdo->prepare("INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES ('site-seo-public', ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at")
                    ->execute([json_encode($value, JSON_UNESCAPED_SLASHES), $session['user']['id'], $updatedAt]);
            }
            if ($key === 'stories') rebuild_story_search_index(is_array($value) ? $value : null);
            if ($key === 'stories' && is_array($value)) {
                foreach ($value as $story) if (is_array($story)) record_story_revision($story, $session['user']['id'], 'Admin stories document update');
            }
            audit_log($session['user']['id'], 'admin.document_update', 'platform_document', $key);
            json_response(['value' => $value, 'updatedAt' => $updatedAt]);
        }
    }

    if ($path === '/api/admin/provider-credentials') {
        $session = require_auth(['admin']);
        if ($method === 'GET') json_response(['credentials' => provider_credential_summary(), 'providers' => provider_status()]);
        if ($method === 'PUT') {
            $body = read_json();
            $provider = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($body['provider'] ?? '')));
            $key = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($body['key'] ?? '')));
            $value = (string) ($body['value'] ?? '');
            if (!$provider || !$key || $value === '') json_response(['error' => 'INVALID_CREDENTIAL', 'message' => 'Provider, key, and value are required.'], 400);
            $pdo->prepare('INSERT INTO provider_credentials (provider, key, value_encrypted, environment, enabled, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?) ON CONFLICT(provider, key) DO UPDATE SET value_encrypted = excluded.value_encrypted, environment = excluded.environment, enabled = excluded.enabled, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
                ->execute([$provider, $key, encrypt_secret_value($value), substr((string) ($body['environment'] ?? 'production'), 0, 40), empty($body['enabled']) ? 0 : 1, $session['user']['id'], now_iso()]);
            audit_log($session['user']['id'], 'admin.provider_credential_update', 'provider', $provider . ':' . $key);
            json_response(['credentials' => provider_credential_summary(), 'providers' => provider_status()]);
        }
    }

    if ($method === 'POST' && $path === '/api/admin/providers/test') {
        require_auth(['admin']);
        $body = read_json();
        json_response(test_provider_connection((string) ($body['provider'] ?? '')) + ['checkedAt' => now_iso()]);
    }

    if ($path === '/api/admin/jobs') {
        require_auth(['admin']);
        if ($method === 'GET') {
            $rows = $pdo->query('SELECT * FROM background_jobs ORDER BY created_at DESC LIMIT 100')->fetchAll();
            json_response(['jobs' => $rows]);
        }
        if ($method === 'POST') {
            $body = read_json();
            $type = (string) ($body['type'] ?? '');
            if (!in_array($type, ['recommendations.rebuild', 'recommendations.train_user', 'translations.backfill', 'publishing.scheduled', 'newsletters.send', 'checkouts.recover', 'subscriptions.sync', 'payouts.execute', 'moderation.scan', 'media.variants', 'pwa.sync'], true)) {
                json_response(['error' => 'INVALID_JOB_TYPE', 'message' => 'Unsupported background job type.'], 400);
            }
            json_response(['jobId' => enqueue_background_job($type, is_array($body['payload'] ?? null) ? $body['payload'] : [])], 201);
        }
    }

    if ($method === 'POST' && $path === '/api/admin/jobs/run') {
        require_auth(['admin']);
        $body = read_json();
        json_response(run_due_background_jobs((int) ($body['limit'] ?? 10)));
    }

    if ($method === 'GET' && $path === '/api/admin/deployment/status') {
        require_auth(['admin']);
        $branch = substr((string) ($_GET['branch'] ?? ''), 0, 80);
        json_response(['deployment' => deployment_current_status(false, $branch)]);
    }

    if ($method === 'POST' && $path === '/api/admin/deployment/check') {
        require_auth(['admin']);
        $body = read_json();
        $branch = substr((string) ($body['branch'] ?? ''), 0, 80);
        json_response(['deployment' => deployment_current_status(true, $branch)]);
    }

    if ($method === 'POST' && $path === '/api/admin/deployment/update') {
        $session = require_auth(['admin']);
        $body = read_json();
        json_response(deployment_run_update($session, $body));
    }

    if ($method === 'GET' && $path === '/api/admin/production-suite') {
        require_auth(['admin']);
        $tax = document_value('tax-settings', ['gstin' => '', 'taxRate' => 18, 'invoicePrefix' => 'INV']);
        $flags = $pdo->query('SELECT * FROM feature_flags ORDER BY key')->fetchAll();
        $templates = $pdo->query('SELECT key, subject, enabled, updated_at FROM email_templates ORDER BY key')->fetchAll();
        $newsletters = $pdo->query("SELECT newsletters.*, publications.name AS publication_name,
            COALESCE(SUM(CASE WHEN newsletter_events.event_type = 'sent' THEN 1 ELSE 0 END), 0) AS sent_count,
            COALESCE(SUM(CASE WHEN newsletter_events.event_type = 'failed' THEN 1 ELSE 0 END), 0) AS failed_count,
            COALESCE(SUM(CASE WHEN newsletter_events.event_type = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
            COALESCE(SUM(CASE WHEN newsletter_events.event_type = 'click' THEN 1 ELSE 0 END), 0) AS click_count
            FROM newsletters
            LEFT JOIN publications ON publications.id = newsletters.publication_id
            LEFT JOIN newsletter_events ON newsletter_events.newsletter_id = newsletters.id
            GROUP BY newsletters.id
            ORDER BY newsletters.created_at DESC LIMIT 100")->fetchAll();
        $assignments = $pdo->query('SELECT editorial_assignments.*, users.name AS assignee_name FROM editorial_assignments LEFT JOIN users ON users.id = editorial_assignments.assignee_user_id ORDER BY editorial_assignments.created_at DESC LIMIT 100')->fetchAll();
        $imports = $pdo->query('SELECT * FROM content_imports ORDER BY created_at DESC LIMIT 50')->fetchAll();
        $newsletterLinks = [];
        foreach ($pdo->query("SELECT newsletter_id, json_extract(metadata_json, '$.url') AS url, COUNT(*) AS clicks FROM newsletter_events WHERE event_type = 'click' GROUP BY newsletter_id, url ORDER BY clicks DESC LIMIT 50")->fetchAll() as $row) {
            if ($row['url']) $newsletterLinks[$row['newsletter_id']][] = ['url' => $row['url'], 'clicks' => (int) $row['clicks']];
        }
        $newsletterSuppressions = (int) $pdo->query('SELECT COUNT(*) AS count FROM newsletter_suppressions')->fetch()['count'];
        $checkoutRows = $pdo->query("SELECT payments.id AS payment_id, payments.user_id, payments.provider, payments.purpose, payments.amount, payments.currency, payments.status, payments.created_at, COALESCE(checkout_recovery_events.event_type, 'pending') AS event_type, users.name, users.email FROM payments LEFT JOIN users ON users.id = payments.user_id LEFT JOIN checkout_recovery_events ON checkout_recovery_events.payment_id = payments.id WHERE payments.status IN ('created', 'pending') ORDER BY payments.created_at DESC LIMIT 100")->fetchAll();
        $jobs = $pdo->query('SELECT * FROM background_jobs ORDER BY created_at DESC LIMIT 30')->fetchAll();
        $providers = provider_status();
        $installer = [
            'phpVersion' => PHP_VERSION,
            'sqlite' => extension_loaded('pdo_sqlite'),
            'uploadsWritable' => is_writable(dirname(__DIR__) . '/uploads') || is_writable(dirname(__DIR__)),
            'storageWritable' => is_writable(dirname(__DIR__) . '/storage') || is_writable(dirname(__DIR__)),
            'cronConfigured' => (bool) (env_value('CRON_SECRET') ?: provider_config_value('CRON_SECRET', 'cron', 'secret')),
            'adminUsers' => (int) $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch()['count'],
        ];
        json_response([
            'emailTemplates' => $templates,
            'newsletters' => $newsletters,
            'newsletterLinks' => $newsletterLinks,
            'newsletterSuppressions' => $newsletterSuppressions,
            'featureFlags' => $flags,
            'taxSettings' => $tax,
            'seoAudit' => seo_audit_rows(),
            'editorialAssignments' => $assignments,
            'imports' => $imports,
            'abandonedCheckouts' => $checkoutRows,
            'jobs' => $jobs,
            'deployment' => deployment_current_status(false),
            'installer' => $installer + ['readiness' => production_readiness($installer, $providers)],
        ]);
    }

    if ($method === 'PUT' && $path === '/api/admin/feature-flags') {
        $session = require_auth(['admin']);
        $body = read_json();
        $key = preg_replace('/[^a-z0-9_.-]/', '', strtolower((string) ($body['key'] ?? '')));
        if ($key === '') json_response(['error' => 'INVALID_FLAG', 'message' => 'Feature flag key is required.'], 400);
        $enabled = empty($body['enabled']) ? 0 : 1;
        $rollout = max(0, min(100, (int) ($body['rolloutPercent'] ?? $body['rollout_percent'] ?? 100)));
        $roles = is_array($body['roles'] ?? null) ? $body['roles'] : array_values(array_filter(array_map('trim', explode(',', (string) ($body['rolesText'] ?? '')))));
        $roles = array_values(array_intersect($roles, ['guest', 'reader', 'subscriber', 'writer', 'moderator', 'admin']));
        $startsAt = ($body['startsAt'] ?? '') ?: null;
        $endsAt = ($body['endsAt'] ?? '') ?: null;
        $description = substr((string) ($body['description'] ?? ''), 0, 500);
        $now = now_iso();
        $environment = preg_replace('/[^a-z0-9_.-]/', '', strtolower((string) ($body['environment'] ?? 'all'))) ?: 'all';
        if (!in_array($environment, ['all', 'production', 'staging', 'development', 'local'], true)) $environment = 'all';
        $pdo->prepare('INSERT INTO feature_flags (key, enabled, rollout_percent, roles_json, starts_at, ends_at, environment, description, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET enabled = excluded.enabled, rollout_percent = excluded.rollout_percent, roles_json = excluded.roles_json, starts_at = excluded.starts_at, ends_at = excluded.ends_at, environment = excluded.environment, description = excluded.description, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute([$key, $enabled, $rollout, json_encode($roles, JSON_UNESCAPED_SLASHES), $startsAt, $endsAt, $environment, $description, $session['user']['id'], $now]);
        $pdo->prepare('INSERT INTO feature_flag_history (id, key, enabled, rollout_percent, roles_json, starts_at, ends_at, environment, description, updated_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('FFH-'), $key, $enabled, $rollout, json_encode($roles, JSON_UNESCAPED_SLASHES), $startsAt, $endsAt, $environment, $description, $session['user']['id'], $now]);
        json_response(['ok' => true]);
    }

    if ($method === 'PUT' && $path === '/api/admin/tax-settings') {
        $session = require_auth(['admin']);
        $body = read_json();
        $value = ['gstin' => substr((string) ($body['gstin'] ?? ''), 0, 30), 'taxRate' => max(0, min(100, (float) ($body['taxRate'] ?? 18))), 'invoicePrefix' => substr((string) ($body['invoicePrefix'] ?? 'INV'), 0, 12)];
        $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute(['tax-settings', json_encode($value, JSON_UNESCAPED_SLASHES), $session['user']['id'], now_iso()]);
        json_response(['taxSettings' => $value]);
    }

    if ($method === 'PUT' && $path === '/api/admin/email-templates') {
        $session = require_auth(['admin']);
        $body = read_json();
        $key = preg_replace('/[^a-z0-9_.-]/', '', strtolower((string) ($body['key'] ?? '')));
        if ($key === '') json_response(['error' => 'INVALID_TEMPLATE', 'message' => 'Template key is required.'], 400);
        $pdo->prepare('INSERT INTO email_templates (key, subject, body, enabled, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET subject = excluded.subject, body = excluded.body, enabled = excluded.enabled, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute([$key, substr((string) ($body['subject'] ?? ''), 0, 240), (string) ($body['body'] ?? ''), empty($body['enabled']) ? 0 : 1, $session['user']['id'], now_iso()]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/admin/newsletters') {
        if (!feature_flag_enabled('newsletters')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Newsletters are disabled.'], 403);
        $session = require_auth(['admin']);
        $body = read_json();
        $id = uuid_value('NWL-');
        $now = now_iso();
        $status = !empty($body['scheduledAt']) ? 'scheduled' : 'draft';
        $publicationId = ($body['publicationId'] ?? '') ?: null;
        $scheduledAt = ($body['scheduledAt'] ?? '') ?: null;
        $pdo->prepare('INSERT INTO newsletters (id, title, publication_id, subject, content_html, audience, status, scheduled_at, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, substr((string) ($body['title'] ?? 'Newsletter'), 0, 180), $publicationId, substr((string) ($body['subject'] ?? 'InkRiver newsletter'), 0, 240), (string) ($body['contentHtml'] ?? ''), substr((string) ($body['audience'] ?? 'all'), 0, 120), $status, $scheduledAt, $session['user']['id'], $now, $now]);
        if ($status === 'scheduled') enqueue_background_job('newsletters.send', [], (string) $scheduledAt);
        json_response(['newsletterId' => $id], 201);
    }

    if ($method === 'POST' && preg_match('#^/api/admin/newsletters/([^/]+)/send$#', $path, $m)) {
        if (!feature_flag_enabled('newsletters')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Newsletters are disabled.'], 403);
        require_auth(['admin']);
        $id = urldecode($m[1]);
        $pdo->prepare("UPDATE newsletters SET status = 'scheduled', scheduled_at = ?, updated_at = ? WHERE id = ?")->execute([now_iso(), now_iso(), $id]);
        json_response(['jobId' => enqueue_background_job('newsletters.send')], 202);
    }

    if ($method === 'POST' && $path === '/api/admin/newsletters/suppressions') {
        require_auth(['admin']);
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'INVALID_EMAIL', 'message' => 'A valid email address is required.'], 400);
        $reason = substr((string) ($body['reason'] ?? 'admin'), 0, 80);
        $pdo->prepare('INSERT INTO newsletter_suppressions (email, user_id, reason, source_newsletter_id, created_at) VALUES (?, NULL, ?, NULL, ?) ON CONFLICT(email) DO UPDATE SET reason = excluded.reason, created_at = excluded.created_at')
            ->execute([$email, $reason, now_iso()]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/admin/newsletters/bounce') {
        require_auth(['admin']);
        $body = read_json();
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'INVALID_EMAIL', 'message' => 'A valid email address is required.'], 400);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE lower(email) = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $pdo->prepare("INSERT INTO newsletter_suppressions (email, user_id, reason, source_newsletter_id, created_at) VALUES (?, ?, 'bounce', ?, ?) ON CONFLICT(email) DO UPDATE SET reason = excluded.reason, source_newsletter_id = excluded.source_newsletter_id, created_at = excluded.created_at")
            ->execute([$email, $user['id'] ?? null, ($body['newsletterId'] ?? '') ?: null, now_iso()]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/admin/editorial-assignments') {
        $session = require_auth(['admin']);
        $body = read_json();
        $id = uuid_value('EDA-');
        $now = now_iso();
        $assigneeUserId = ($body['assigneeUserId'] ?? '') ?: null;
        $dueAt = ($body['dueAt'] ?? '') ?: null;
        $pdo->prepare('INSERT INTO editorial_assignments (id, story_slug, assignee_user_id, role, due_at, priority, status, notes, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, substr((string) ($body['storySlug'] ?? ''), 0, 200), $assigneeUserId, substr((string) ($body['role'] ?? 'editor'), 0, 80), $dueAt, substr((string) ($body['priority'] ?? 'Normal'), 0, 40), 'assigned', substr((string) ($body['notes'] ?? ''), 0, 2000), $session['user']['id'], $now, $now]);
        json_response(['assignmentId' => $id], 201);
    }

    if ($method === 'POST' && $path === '/api/admin/imports') {
        $session = require_auth(['admin']);
        $body = read_json();
        $sourceType = substr((string) ($body['sourceType'] ?? 'json'), 0, 40);
        $raw = (string) ($body['content'] ?? '');
        $id = uuid_value('IMP-');
        $now = now_iso();
        $imported = 0;
        $skipped = 0;
        $error = '';
        $duplicateMode = in_array(($body['duplicateMode'] ?? 'skip'), ['skip', 'replace', 'unique'], true) ? (string) $body['duplicateMode'] : 'skip';
        $defaultStatus = in_array(($body['defaultStatus'] ?? 'draft'), ['draft', 'published', 'scheduled'], true) ? (string) $body['defaultStatus'] : 'draft';
        $defaultAuthor = substr((string) ($body['defaultAuthor'] ?? ''), 0, 120);
        $defaultTopic = substr((string) ($body['defaultTopic'] ?? ''), 0, 120);
        try {
            $items = imported_story_items($sourceType, $raw);
            $stories = document_value('stories', []);
            $snapshot = $stories;
            $existingSlugs = [];
            foreach ($stories as $index => $existingStory) if (is_array($existingStory) && !empty($existingStory['slug'])) $existingSlugs[(string) $existingStory['slug']] = $index;
            foreach ($items as $item) {
                if (!is_array($item) || empty($item['title'])) continue;
                $item['id'] = $item['id'] ?? 'blog-' . uuid_value();
                $item['slug'] = $item['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) $item['title']), '-'));
                $slug = (string) $item['slug'];
                if (isset($existingSlugs[$slug])) {
                    if ($duplicateMode === 'skip') {
                        $skipped++;
                        continue;
                    }
                    if ($duplicateMode === 'unique') {
                        $baseSlug = $slug;
                        $suffix = 2;
                        while (isset($existingSlugs[$slug])) $slug = $baseSlug . '-' . $suffix++;
                        $item['slug'] = $slug;
                    }
                }
                $item['status'] = $item['status'] ?? $defaultStatus;
                $item['author'] = $item['author'] ?? ($defaultAuthor ?: 'InkRiver Team');
                $item['topic'] = $item['topic'] ?? ($defaultTopic ?: 'Imported');
                $item['contentHtml'] = $item['contentHtml'] ?? ($item['content_html'] ?? '');
                $item['dek'] = $item['dek'] ?? substr(trim(strip_tags((string) $item['contentHtml'])), 0, 180);
                $item['body'] = $item['body'] ?? array_values(array_filter(preg_split('/\n{2,}/', trim(strip_tags((string) $item['contentHtml'])) ?: (string) ($item['content'] ?? ''))));
                $item['createdAt'] = $item['createdAt'] ?? $now;
                $item['updatedAt'] = $now;
                if ($duplicateMode === 'replace' && isset($existingSlugs[$slug])) {
                    $targetIndex = (int) $existingSlugs[$slug];
                    $stories[$targetIndex] = $item;
                } else {
                    $stories[] = $item;
                    $targetIndex = count($stories) - 1;
                }
                $existingSlugs[(string) $item['slug']] = $targetIndex;
                $imported++;
            }
            $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
                ->execute(['stories', json_encode($stories, JSON_UNESCAPED_SLASHES), $session['user']['id'], $now]);
            rebuild_story_search_index($stories);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
        $status = $error ? 'failed' : 'processed';
        $pdo->prepare('INSERT INTO content_imports (id, source_type, status, imported_count, error, metadata_json, snapshot_json, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $sourceType, $status, $imported, $error, json_encode(['preview' => array_slice($items ?? [], 0, 5), 'skippedDuplicates' => $skipped, 'duplicateMode' => $duplicateMode, 'defaults' => ['status' => $defaultStatus, 'author' => $defaultAuthor, 'topic' => $defaultTopic]], JSON_UNESCAPED_SLASHES), json_encode($snapshot ?? [], JSON_UNESCAPED_SLASHES), $session['user']['id'], $now, $now]);
        json_response(['importId' => $id, 'status' => $status, 'imported' => $imported, 'skippedDuplicates' => $skipped, 'error' => $error], $error ? 400 : 201);
    }

    if ($method === 'POST' && $path === '/api/admin/imports/preview') {
        require_auth(['admin']);
        $body = read_json();
        $items = imported_story_items(substr((string) ($body['sourceType'] ?? 'json'), 0, 40), (string) ($body['content'] ?? ''));
        $duplicateMode = in_array(($body['duplicateMode'] ?? 'skip'), ['skip', 'replace', 'unique'], true) ? (string) $body['duplicateMode'] : 'skip';
        $existing = [];
        foreach (document_value('stories', []) as $story) if (is_array($story) && !empty($story['slug'])) $existing[(string) $story['slug']] = true;
        $duplicates = 0;
        foreach ($items as $item) {
            $slug = (string) ($item['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) ($item['title'] ?? '')), '-')));
            if ($slug && isset($existing[$slug])) $duplicates++;
        }
        json_response(['count' => count($items), 'duplicates' => $duplicates, 'duplicateMode' => $duplicateMode, 'sample' => array_slice(array_map(fn($item) => [
            'title' => $item['title'] ?? '',
            'slug' => $item['slug'] ?? '',
            'author' => $item['author'] ?? '',
            'status' => $item['status'] ?? 'draft',
        ], $items), 0, 10)]);
    }

    if ($method === 'POST' && preg_match('#^/api/admin/imports/([^/]+)/rollback$#', $path, $m)) {
        $session = require_auth(['admin']);
        $id = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT * FROM content_imports WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || $row['status'] !== 'processed') json_response(['error' => 'IMPORT_NOT_ROLLBACKABLE', 'message' => 'Only processed imports can be rolled back.'], 400);
        $snapshot = parse_json_field($row['snapshot_json'] ?? '[]', []);
        $now = now_iso();
        $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute(['stories', json_encode($snapshot, JSON_UNESCAPED_SLASHES), $session['user']['id'], $now]);
        rebuild_story_search_index(is_array($snapshot) ? $snapshot : []);
        $metadata = parse_json_field($row['metadata_json'] ?? '{}', []);
        $metadata['rolledBackAt'] = $now;
        $pdo->prepare("UPDATE content_imports SET metadata_json = ?, updated_at = ? WHERE id = ?")->execute([json_encode($metadata, JSON_UNESCAPED_SLASHES), $now, $id]);
        json_response(['rolledBack' => true, 'stories' => count($snapshot)]);
    }

    if ($method === 'GET' && preg_match('#^/api/admin/exports/([a-z_-]+)$#', $path, $m)) {
        require_auth(['admin']);
        $type = $m[1];
        $allowed = [
            'users' => 'SELECT id, name, email, role, subscription, status, created_at, last_login_at FROM users ORDER BY created_at DESC',
            'payments' => 'SELECT id, user_id, provider, purpose, amount, currency, status, created_at FROM payments ORDER BY created_at DESC',
            'subscriptions' => 'SELECT id, user_id, plan_id, provider, provider_subscription_id, amount, currency, status, starts_at, ends_at FROM subscriptions ORDER BY created_at DESC',
            'stories' => null,
            'comments' => 'SELECT id, story_slug, user_id, status, pinned, created_at FROM comments ORDER BY created_at DESC',
            'ads' => 'SELECT id, name, placement, sponsor, status, budget, cpm, created_at FROM ad_campaigns ORDER BY created_at DESC',
            'payouts' => 'SELECT id, writer_user_id, amount, currency, status, created_at, paid_at FROM writer_payouts ORDER BY created_at DESC',
        ];
        if (!array_key_exists($type, $allowed)) json_response(['error' => 'INVALID_EXPORT', 'message' => 'Unsupported export type.'], 400);
        $rows = $type === 'stories' ? array_map(fn($story) => ['id' => $story['id'] ?? '', 'slug' => $story['slug'] ?? '', 'title' => $story['title'] ?? '', 'status' => $story['status'] ?? '', 'author' => $story['author'] ?? '', 'publication' => $story['publication'] ?? ''], document_value('stories', [])) : $pdo->query($allowed[$type])->fetchAll();
        csv_response('inkriver-' . $type . '.csv', $rows);
    }

    if ($method === 'GET' && $path === '/api/admin/installer/report') {
        require_auth(['admin']);
        $providers = provider_status();
        $installer = [
            'phpVersion' => PHP_VERSION,
            'sqlite' => extension_loaded('pdo_sqlite'),
            'uploadsWritable' => is_writable(dirname(__DIR__) . '/uploads') || is_writable(dirname(__DIR__)),
            'storageWritable' => is_writable(dirname(__DIR__) . '/storage') || is_writable(dirname(__DIR__)),
            'cronConfigured' => (bool) (env_value('CRON_SECRET') ?: provider_config_value('CRON_SECRET', 'cron', 'secret')),
            'adminUsers' => (int) $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch()['count'],
        ];
        json_response([
            'generatedAt' => now_iso(),
            'environment' => env_value('APP_ENV', 'production'),
            'installer' => $installer,
            'providers' => $providers,
            'readiness' => production_readiness($installer, $providers),
            'databasePath' => database_path(),
        ]);
    }

    if ($method === 'GET' && $path === '/api/admin/media') {
        require_auth(['admin']);
        $rows = $pdo->query('SELECT * FROM media_assets ORDER BY created_at DESC LIMIT 200')->fetchAll();
        json_response(['assets' => array_map('public_media_asset', $rows)]);
    }

    if ($method === 'POST' && $path === '/api/admin/media') {
        $session = require_auth(['admin']);
        if (empty($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            json_response(['error' => 'NO_FILE', 'message' => 'Choose an image file to upload.'], 400);
        }
        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) json_response(['error' => 'UPLOAD_FAILED', 'message' => 'The upload failed.'], 400);
        if ((int) $file['size'] > 8 * 1024 * 1024) json_response(['error' => 'FILE_TOO_LARGE', 'message' => 'Images must be 8 MB or smaller.'], 413);
        $info = getimagesize($file['tmp_name']);
        if (!$info) json_response(['error' => 'INVALID_IMAGE', 'message' => 'Only valid image files can be uploaded.'], 400);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = $info['mime'] ?? '';
        if (!isset($allowed[$mime])) json_response(['error' => 'UNSUPPORTED_IMAGE', 'message' => 'Use JPG, PNG, WebP, or GIF.'], 400);
        $relativeDir = 'uploads/' . gmdate('Y/m');
        $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
        if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0775, true);
        $storedName = uuid_value('media-') . '.' . $allowed[$mime];
        $target = $absoluteDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $target)) json_response(['error' => 'UPLOAD_STORE_FAILED', 'message' => 'Could not save the image.'], 500);
        $url = '/' . $relativeDir . '/' . $storedName;
        $id = uuid_value('MED-');
        $pdo->prepare('INSERT INTO media_assets (id, uploader_user_id, original_name, stored_name, url, mime_type, size_bytes, width, height, alt_text, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$id, $session['user']['id'], substr((string) $file['name'], 0, 240), $storedName, $url, $mime, (int) $file['size'], (int) $info[0], (int) $info[1], substr((string) ($_POST['altText'] ?? ''), 0, 500), now_iso()]);
        $stmt = $pdo->prepare('SELECT * FROM media_assets WHERE id = ?');
        $stmt->execute([$id]);
        enqueue_background_job('media.variants', ['mediaAssetId' => $id]);
        audit_log($session['user']['id'], 'admin.media_upload', 'media_asset', $id);
        json_response(['asset' => public_media_asset($stmt->fetch())], 201);
    }

    if (preg_match('#^/api/me/documents/([^/]+)$#', $path, $m)) {
        $session = require_auth();
        $key = urldecode($m[1]);
        if (!in_array($key, USER_DOCUMENTS, true)) json_response(['error' => 'INVALID_DOCUMENT', 'message' => 'This account document cannot be managed.'], 400);
        if ($method === 'PUT') {
            $body = read_json();
            $updatedAt = now_iso();
            $pdo->prepare('INSERT INTO user_documents (user_id, key, value_json, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id, key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
                ->execute([$session['user']['id'], $key, json_encode($body['value'] ?? null, JSON_UNESCAPED_SLASHES), $updatedAt]);
            json_response(['value' => $body['value'] ?? null, 'updatedAt' => $updatedAt]);
        }
    }

    if ($method === 'POST' && $path === '/api/events') {
        $session = current_session();
        $body = read_json();
        $pdo->prepare('INSERT INTO engagement_events (id, user_id, anonymous_id, story_slug, event_type, value, metadata, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value(), $session['user']['id'] ?? null, null, substr((string) ($body['storySlug'] ?? ''), 0, 200), (string) ($body['type'] ?? 'open'), $body['value'] ?? null, json_encode($body['metadata'] ?? []), now_iso()]);
        json_response(['ok' => true], 201);
    }

    if ($method === 'GET' && $path === '/api/recommendations/feed') {
        $session = require_auth();
        $feed = recommendation_feed_for_user($session['user']['id'], max(1, min(80, (int) ($_GET['limit'] ?? 24))));
        $profile = $pdo->prepare('SELECT signals_count, last_trained_at, model_version FROM recommendation_profiles WHERE user_id = ?');
        $profile->execute([$session['user']['id']]);
        $row = $profile->fetch();
        json_response(['feed' => $feed, 'profile' => ['signalsCount' => (int) ($row['signals_count'] ?? 0), 'lastTrainedAt' => $row['last_trained_at'] ?? null, 'modelVersion' => (int) ($row['model_version'] ?? 2)]]);
    }

    if ($method === 'POST' && $path === '/api/recommendations/feedback') {
        $session = require_auth();
        $body = read_json();
        $type = (string) ($body['type'] ?? 'feedback');
        if ($type === 'reset_recommendations') {
            $pdo->prepare('DELETE FROM recommendation_profiles WHERE user_id = ?')->execute([$session['user']['id']]);
            $pdo->prepare('DELETE FROM recommendation_story_scores WHERE user_id = ?')->execute([$session['user']['id']]);
            $pdo->prepare("DELETE FROM engagement_events WHERE user_id = ? AND event_type IN ('open','read_midpoint','read_complete','complete','long_read','clap','save','unsave','share','not_interested','less_like_this','more_like_this','hide_topic','interest_added','interest_removed')")->execute([$session['user']['id']]);
            json_response(['ok' => true, 'training' => ['signals' => 0, 'stories' => 0, 'trainedAt' => now_iso(), 'modelVersion' => 2], 'feed' => []]);
        }
        $storySlug = substr((string) ($body['storySlug'] ?? ''), 0, 200);
        $metadata = is_array($body['metadata'] ?? null) ? $body['metadata'] : [];
        $pdo->prepare('INSERT INTO engagement_events (id, user_id, anonymous_id, story_slug, event_type, value, metadata, created_at) VALUES (?, ?, NULL, ?, ?, ?, ?, ?)')
            ->execute([uuid_value(), $session['user']['id'], $storySlug, $type, $body['value'] ?? null, json_encode($metadata, JSON_UNESCAPED_SLASHES), now_iso()]);
        $jobId = enqueue_background_job('recommendations.train_user', ['userId' => $session['user']['id']]);
        json_response(['ok' => true, 'training' => ['status' => 'queued', 'jobId' => $jobId], 'feed' => recommendation_feed_for_user($session['user']['id'], 24)]);
    }

    if ($method === 'POST' && $path === '/api/admin/recommendations/rebuild') {
        require_auth(['admin']);
        json_response(['jobId' => enqueue_background_job('recommendations.rebuild'), 'status' => 'queued'], 202);
    }

    if ($method === 'POST' && $path === '/api/admin/search/rebuild') {
        require_auth(['admin']);
        json_response(['indexed' => rebuild_story_search_index(), 'rebuiltAt' => now_iso()]);
    }

    if ($method === 'GET' && $path === '/api/admin/seo/artifacts') {
        require_auth(['admin']);
        $rows = $pdo->query('SELECT key, mime_type, updated_at FROM seo_artifacts ORDER BY key')->fetchAll();
        json_response(['artifacts' => $rows, 'sitemapUrl' => '/sitemap.xml', 'robotsUrl' => '/robots.txt']);
    }

    if ($method === 'PUT' && $path === '/api/admin/seo/artifacts') {
        $session = require_auth(['admin']);
        $body = read_json();
        $key = preg_replace('/[^a-z0-9_.-]/i', '', (string) ($body['key'] ?? 'custom'));
        $content = substr((string) ($body['content'] ?? ''), 0, 200000);
        $mime = substr((string) ($body['mimeType'] ?? 'text/plain; charset=utf-8'), 0, 120);
        $pdo->prepare('INSERT INTO seo_artifacts (key, content, mime_type, updated_by, updated_at) VALUES (?, ?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET content = excluded.content, mime_type = excluded.mime_type, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute([$key, $content, $mime, $session['user']['id'], now_iso()]);
        json_response(['artifacts' => $pdo->query('SELECT key, mime_type, updated_at FROM seo_artifacts ORDER BY key')->fetchAll()]);
    }

    if ($method === 'GET' && $path === '/api/admin/permissions') {
        require_auth(['admin']);
        json_response(['permissions' => $pdo->query('SELECT * FROM role_permissions ORDER BY role, permission')->fetchAll()]);
    }

    if ($method === 'GET' && $path === '/api/admin/recommendations/status') {
        require_auth(['admin']);
        $row = $pdo->query('SELECT COUNT(*) AS profiles, COALESCE(SUM(signals_count), 0) AS signals, MAX(last_trained_at) AS last_trained_at FROM recommendation_profiles')->fetch();
        $scores = $pdo->query('SELECT COUNT(*) AS scores FROM recommendation_story_scores')->fetch();
        json_response(['profiles' => (int) ($row['profiles'] ?? 0), 'signals' => (int) ($row['signals'] ?? 0), 'scores' => (int) ($scores['scores'] ?? 0), 'lastTrainedAt' => $row['last_trained_at'] ?? null]);
    }

    if ($method === 'GET' && $path === '/api/admin/users') {
        require_auth(['admin']);
        $query = strtolower(trim((string) ($_GET['q'] ?? '')));
        if ($query !== '') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE status != 'deleted' AND (lower(name) LIKE ? OR lower(email) LIKE ? OR lower(id) LIKE ?) ORDER BY created_at DESC LIMIT 200");
            $like = "%{$query}%";
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT * FROM users WHERE status != 'deleted' ORDER BY created_at DESC LIMIT 200");
        }
        json_response(['users' => array_map('public_user', $stmt->fetchAll())]);
    }

    if ($method === 'POST' && $path === '/api/admin/users') {
        $session = require_auth(['admin']);
        $body = read_json();
        $name = trim((string) ($body['name'] ?? ''));
        $email = strtolower(trim((string) ($body['email'] ?? '')));
        $password = (string) ($body['password'] ?? '');
        $roles = ['reader', 'subscriber', 'writer', 'moderator', 'admin'];
        $statuses = ['active', 'suspended'];
        $role = in_array($body['role'] ?? 'reader', $roles, true) ? (string) ($body['role'] ?? 'reader') : 'reader';
        $status = in_array($body['status'] ?? 'active', $statuses, true) ? (string) ($body['status'] ?? 'active') : 'active';
        $subscription = substr((string) ($body['subscription'] ?? 'Free'), 0, 120) ?: 'Free';
        if ($name === '' || strlen($name) > 80) json_response(['error' => 'INVALID_NAME', 'message' => 'Enter a name up to 80 characters.'], 400);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'INVALID_EMAIL', 'message' => 'Enter a valid email address.'], 400);
        $passwordError = password_strength_error($password);
        if ($passwordError) json_response(['error' => 'WEAK_PASSWORD', 'message' => $passwordError], 400);
        $exists = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status != 'deleted'");
        $exists->execute([$email]);
        if ($exists->fetch()) json_response(['error' => 'EMAIL_EXISTS', 'message' => 'A user with this email already exists.'], 409);
        $id = uuid_value('USR-');
        $now = now_iso();
        $pdo->prepare('INSERT INTO users (id, name, email, password_hash, role, subscription, status, email_verified, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)')
            ->execute([$id, $name, $email, hash_password_value($password), $role, $subscription, $status, $now, $now]);
        audit_log($session['user']['id'], 'admin.user_create', 'user', $id, compact('role', 'status', 'subscription'));
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        json_response(['user' => public_user($stmt->fetch())], 201);
    }

    if (preg_match('#^/api/admin/users/([^/]+)$#', $path, $m) && $method === 'PATCH') {
        $session = require_auth(['admin']);
        $body = read_json();
        $targetId = urldecode($m[1]);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status != 'deleted'");
        $stmt->execute([$targetId]);
        $target = $stmt->fetch();
        if (!$target) json_response(['error' => 'NOT_FOUND', 'message' => 'User not found.'], 404);
        $roles = ['reader', 'subscriber', 'writer', 'moderator', 'admin'];
        $statuses = ['active', 'suspended'];
        $role = in_array($body['role'] ?? $target['role'], $roles, true) ? $body['role'] ?? $target['role'] : $target['role'];
        $status = in_array($body['status'] ?? $target['status'], $statuses, true) ? $body['status'] ?? $target['status'] : $target['status'];
        $subscription = substr((string) ($body['subscription'] ?? $target['subscription']), 0, 120);
        $pdo->prepare('UPDATE users SET role = ?, status = ?, subscription = ?, updated_at = ? WHERE id = ?')
            ->execute([$role, $status, $subscription, now_iso(), $targetId]);
        if ($status !== 'active') $pdo->prepare('DELETE FROM sessions WHERE user_id = ?')->execute([$targetId]);
        audit_log($session['user']['id'], 'admin.user_update', 'user', $targetId, compact('role', 'status', 'subscription'));
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$targetId]);
        json_response(['user' => public_user($stmt->fetch())]);
    }

    if (preg_match('#^/api/admin/users/([^/]+)$#', $path, $m) && $method === 'DELETE') {
        $session = require_auth(['admin']);
        $targetId = urldecode($m[1]);
        if ($targetId === $session['user']['id']) json_response(['error' => 'SELF_DELETE', 'message' => 'You cannot delete your own account.'], 400);
        $pdo->prepare("UPDATE users SET status = 'deleted', email = email || '.deleted.' || id, updated_at = ? WHERE id = ? AND status != 'deleted'")
            ->execute([now_iso(), $targetId]);
        $pdo->prepare('DELETE FROM sessions WHERE user_id = ?')->execute([$targetId]);
        audit_log($session['user']['id'], 'admin.user_delete', 'user', $targetId);
        json_response(['ok' => true]);
    }

    if ($method === 'GET' && $path === '/api/me/sessions') {
        $session = require_auth();
        $stmt = $pdo->prepare('SELECT id, created_at, last_seen_at, ip_address, user_agent FROM sessions WHERE user_id = ? ORDER BY last_seen_at DESC');
        $stmt->execute([$session['user']['id']]);
        json_response(['currentSessionId' => $session['sessionId'], 'sessions' => $stmt->fetchAll()]);
    }

    if ($method === 'DELETE' && $path === '/api/me/sessions/others') {
        $session = require_auth();
        $pdo->prepare('DELETE FROM sessions WHERE user_id = ? AND id != ?')->execute([$session['user']['id'], $session['sessionId']]);
        json_response(['revoked' => true]);
    }

    if ($method === 'DELETE' && preg_match('#^/api/me/sessions/([^/]+)$#', $path, $m)) {
        $session = require_auth();
        $sessionId = urldecode($m[1]);
        if ($sessionId === $session['sessionId']) {
            json_response(['error' => 'CURRENT_SESSION', 'message' => 'Use sign out to end the current session.'], 400);
        }
        $stmt = $pdo->prepare('DELETE FROM sessions WHERE id = ? AND user_id = ?');
        $stmt->execute([$sessionId, $session['user']['id']]);
        audit_log($session['user']['id'], 'security.session_revoked', 'session', $sessionId);
        json_response(['revoked' => $stmt->rowCount() > 0]);
    }

    if ($method === 'GET' && $path === '/api/me/security') {
        $session = require_auth();
        $stmt = $pdo->prepare('SELECT * FROM user_security_settings WHERE user_id = ?');
        $stmt->execute([$session['user']['id']]);
        $row = $stmt->fetch();
        $passkeys = $pdo->prepare('SELECT id, credential_id, public_key, transports, created_at, last_used_at FROM passkey_credentials WHERE user_id = ? ORDER BY created_at DESC');
        $passkeys->execute([$session['user']['id']]);
        json_response([
            'emailVerified' => (bool) ($session['raw']['email_verified'] ?? false),
            'twoFactorEnabled' => (bool) ($row['two_factor_enabled'] ?? false),
            'loginAlertsEnabled' => (bool) ($row['login_alerts_enabled'] ?? false),
            'passkeys' => array_map(fn($item) => [
                'id' => $item['id'],
                'credentialId' => substr($item['credential_id'], 0, 8) . '...' . substr($item['credential_id'], -6),
                'transports' => parse_json_field($item['transports'] ?? '[]', []),
                'needsReRegistration' => !isset(parse_json_field((string) ($item['public_key'] ?? '{}'), [])['x']),
                'createdAt' => $item['created_at'],
                'lastUsedAt' => $item['last_used_at'],
            ], $passkeys->fetchAll()),
        ]);
    }

    if ($method === 'GET' && $path === '/api/me/notifications') {
        $session = require_auth();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->execute([$session['user']['id']]);
        $unread = $pdo->prepare('SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND read_at IS NULL');
        $unread->execute([$session['user']['id']]);
        json_response(['notifications' => $stmt->fetchAll(), 'unread' => (int) ($unread->fetch()['count'] ?? 0)]);
    }

    if ($method === 'PATCH' && $path === '/api/me/notifications/read') {
        $session = require_auth();
        $body = read_json();
        if (!empty($body['id'])) {
            $stmt = $pdo->prepare('UPDATE notifications SET read_at = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([now_iso(), (string) $body['id'], $session['user']['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE notifications SET read_at = COALESCE(read_at, ?) WHERE user_id = ?');
            $stmt->execute([now_iso(), $session['user']['id']]);
        }
        json_response(['updated' => $stmt->rowCount()]);
    }

    if ($method === 'GET' && $path === '/api/me/invoices') {
        $session = require_auth();
        $stmt = $pdo->prepare('SELECT * FROM billing_invoices WHERE user_id = ? ORDER BY issued_at DESC LIMIT 100');
        $stmt->execute([$session['user']['id']]);
        json_response(['invoices' => $stmt->fetchAll()]);
    }

    if ($method === 'POST' && $path === '/api/me/subscription/cancel') {
        $session = require_auth();
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$session['user']['id']]);
        $subscription = $stmt->fetch();
        if (!$subscription) json_response(['error' => 'NO_ACTIVE_SUBSCRIPTION', 'message' => 'No active subscription was found.'], 404);
        try {
            $providerCancellation = cancel_provider_subscription($subscription);
        } catch (Throwable $error) {
            $pdo->prepare('INSERT INTO subscription_events (id, subscription_id, user_id, provider, event_type, status_before, status_after, payload_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([uuid_value('SEV-'), $subscription['id'], $session['user']['id'], $subscription['provider'], 'cancel_provider_failed', $subscription['status'], $subscription['status'], json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_SLASHES), now_iso()]);
            json_response(['error' => 'PROVIDER_CANCELLATION_FAILED', 'message' => $error->getMessage()], 502);
        }
        $now = now_iso();
        $pdo->prepare("UPDATE subscriptions SET status = 'cancelled', updated_at = ? WHERE id = ?")->execute([$now, $subscription['id']]);
        $pdo->prepare('INSERT INTO subscription_events (id, subscription_id, user_id, provider, event_type, status_before, status_after, payload_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value('SEV-'), $subscription['id'], $session['user']['id'], $subscription['provider'], 'cancelled_by_user', $subscription['status'], 'cancelled', json_encode($providerCancellation, JSON_UNESCAPED_SLASHES), $now]);
        $pdo->prepare("UPDATE users SET subscription = 'Free', updated_at = ? WHERE id = ?")->execute([$now, $session['user']['id']]);
        create_notification($session['user']['id'], 'subscription_cancelled', 'Membership cancelled', !empty($providerCancellation['remote']) ? 'Your provider subscription cancellation has been submitted.' : 'Your local subscription has been cancelled.', '/dashboard');
        audit_log($session['user']['id'], 'subscription.cancel', 'subscription', $subscription['id']);
        $userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $userStmt->execute([$session['user']['id']]);
        json_response(['status' => 'cancelled', 'provider' => $providerCancellation, 'user' => public_user($userStmt->fetch())]);
    }

    if ($path === '/api/me/pwa/settings') {
        $session = require_auth();
        if ($method === 'GET') {
            $stmt = $pdo->prepare("SELECT value_json FROM user_documents WHERE user_id = ? AND key = 'pwa-settings'");
            $stmt->execute([$session['user']['id']]);
            $row = $stmt->fetch();
            json_response(['settings' => $row ? parse_json_field($row['value_json'], []) : ['offlineSavedArticles' => true, 'backgroundSync' => true, 'pushDigest' => true]]);
        }
        if ($method === 'PUT') {
            $body = read_json();
            $settings = is_array($body['settings'] ?? null) ? $body['settings'] : [];
            $pdo->prepare('INSERT INTO user_documents (user_id, key, value_json, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id, key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
                ->execute([$session['user']['id'], 'pwa-settings', json_encode($settings, JSON_UNESCAPED_SLASHES), now_iso()]);
            json_response(['settings' => $settings]);
        }
    }

    if ($method === 'POST' && $path === '/api/me/security/passkeys/challenge') {
        $session = require_auth();
        if (empty($session['raw']['email_verified'])) {
            json_response(['error' => 'EMAIL_NOT_VERIFIED', 'message' => 'Verify your email before adding a passkey.'], 403);
        }
        $challenge = base64url_encode_value(random_bytes(32));
        $now = now_iso();
        $pdo->prepare('INSERT INTO user_documents (user_id, key, value_json, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id, key) DO UPDATE SET value_json = excluded.value_json, updated_at = excluded.updated_at')
            ->execute([$session['user']['id'], 'passkey-registration-challenge', json_encode(['challenge' => $challenge, 'createdAt' => $now], JSON_UNESCAPED_SLASHES), $now]);
        json_response([
            'challenge' => $challenge,
            'rp' => ['name' => 'InkRiver', 'id' => webauthn_rp_id()],
            'user' => [
                'id' => $session['user']['id'],
                'idEncoded' => base64url_encode_value(hash('sha256', $session['user']['id'], true)),
                'name' => $session['user']['email'],
                'displayName' => $session['user']['name'],
            ],
        ]);
    }

    if ($method === 'POST' && $path === '/api/me/security/passkeys') {
        $session = require_auth();
        $body = read_json();
        $credentialId = trim((string) ($body['credentialId'] ?? ''));
        $clientDataJson = (string) ($body['clientDataJSON'] ?? '');
        $attestationObject = (string) ($body['attestationObject'] ?? '');
        if ($credentialId === '' || $clientDataJson === '' || $attestationObject === '') {
            json_response(['error' => 'INVALID_PASSKEY', 'message' => 'A complete WebAuthn credential is required.'], 400);
        }
        $challengeStmt = $pdo->prepare("SELECT value_json FROM user_documents WHERE user_id = ? AND key = 'passkey-registration-challenge'");
        $challengeStmt->execute([$session['user']['id']]);
        $challengeRow = $challengeStmt->fetch();
        $challenge = parse_json_field($challengeRow['value_json'] ?? '{}', []);
        $clientData = json_decode(base64url_decode_value($clientDataJson), true);
        if (!$challengeRow || !is_array($clientData) || ($clientData['type'] ?? '') !== 'webauthn.create' || !hash_equals((string) ($challenge['challenge'] ?? ''), (string) ($clientData['challenge'] ?? '')) || !hash_equals(webauthn_expected_origin(), (string) ($clientData['origin'] ?? '')) || strtotime((string) ($challenge['createdAt'] ?? '1970-01-01')) < time() - 300) {
            json_response(['error' => 'INVALID_PASSKEY_CHALLENGE', 'message' => 'The passkey challenge expired or did not match.'], 400);
        }
        try {
            $publicKey = extract_webauthn_public_key(base64url_decode_value($attestationObject));
        } catch (Throwable $error) {
            json_response(['error' => 'INVALID_PASSKEY_PUBLIC_KEY', 'message' => $error->getMessage()], 400);
        }
        if (!hash_equals($credentialId, $publicKey['credentialId'])) {
            json_response(['error' => 'INVALID_PASSKEY_CREDENTIAL', 'message' => 'Passkey credential ID did not match authenticator data.'], 400);
        }
        $id = uuid_value('PKY-');
        $pdo->prepare('INSERT INTO passkey_credentials (id, user_id, credential_id, public_key, sign_count, transports, created_at) VALUES (?, ?, ?, ?, 0, ?, ?)')
            ->execute([$id, $session['user']['id'], $credentialId, json_encode($publicKey, JSON_UNESCAPED_SLASHES), json_encode($body['transports'] ?? [], JSON_UNESCAPED_SLASHES), now_iso()]);
        $pdo->prepare("DELETE FROM user_documents WHERE user_id = ? AND key = 'passkey-registration-challenge'")->execute([$session['user']['id']]);
        audit_log($session['user']['id'], 'security.passkey_added', 'passkey', $id);
        json_response(['passkey' => ['id' => $id]], 201);
    }

    if (preg_match('#^/api/me/security/passkeys/([^/]+)$#', $path, $m) && $method === 'DELETE') {
        $session = require_auth();
        $id = urldecode($m[1]);
        $pdo->prepare('DELETE FROM passkey_credentials WHERE id = ? AND user_id = ?')->execute([$id, $session['user']['id']]);
        audit_log($session['user']['id'], 'security.passkey_deleted', 'passkey', $id);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/me/security/email-verification') {
        $session = require_auth();
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $url = app_origin() . '/api/security/verify-email?token=' . rawurlencode($token);
        $pdo->prepare("INSERT INTO security_tokens (id, user_id, token_hash, type, expires_at, created_at) VALUES (?, ?, ?, 'email_verification', ?, ?)")
            ->execute([uuid_value(), $session['user']['id'], hash('sha256', $token), gmdate('Y-m-d\TH:i:s.v\Z', time() + 86400), now_iso()]);
        $sent = send_email_message($session['user']['email'], 'Verify your InkRiver email', ['name' => $session['user']['name'], 'verificationUrl' => $url]);
        json_response(['sent' => $sent, 'verificationUrl' => $sent ? null : $url], 201);
    }

    if ($method === 'GET' && $path === '/api/security/verify-email') {
        $hash = hash('sha256', (string) ($_GET['token'] ?? ''));
        $stmt = $pdo->prepare("SELECT * FROM security_tokens WHERE token_hash = ? AND type = 'email_verification' AND consumed_at IS NULL AND expires_at > ?");
        $stmt->execute([$hash, now_iso()]);
        $row = $stmt->fetch();
        if (!$row) redirect_response('/security?verified=invalid');
        $now = now_iso();
        $pdo->prepare('UPDATE users SET email_verified = 1, updated_at = ? WHERE id = ?')->execute([$now, $row['user_id']]);
        $pdo->prepare('UPDATE security_tokens SET consumed_at = ? WHERE id = ?')->execute([$now, $row['id']]);
        redirect_response('/security?verified=success');
    }

    if (preg_match('#^/api/stories/([^/]+)/comments$#', $path, $m)) {
        if (!feature_flag_enabled('comments')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Comments are disabled.'], 403);
        $slug = urldecode($m[1]);
        if ($method === 'GET') {
            $stmt = $pdo->prepare("SELECT comments.*, users.name AS author_name, users.role AS author_role FROM comments JOIN users ON users.id = comments.user_id WHERE comments.story_slug = ? AND comments.status != 'deleted' ORDER BY comments.pinned DESC, comments.created_at DESC");
            $stmt->execute([$slug]);
            $session = current_session();
            $likeRows = $pdo->prepare('SELECT comment_id, COUNT(*) AS likes FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE story_slug = ?) GROUP BY comment_id');
            $likeRows->execute([$slug]);
            $likesByComment = [];
            foreach ($likeRows->fetchAll() as $likeRow) $likesByComment[$likeRow['comment_id']] = (int) $likeRow['likes'];
            $likedByUser = [];
            if ($session) {
                $likedStmt = $pdo->prepare('SELECT comment_id FROM comment_likes WHERE user_id = ? AND comment_id IN (SELECT id FROM comments WHERE story_slug = ?)');
                $likedStmt->execute([$session['user']['id'], $slug]);
                $likedByUser = array_flip(array_column($likedStmt->fetchAll(), 'comment_id'));
            }
            $comments = array_map(fn($row) => [
                'id' => $row['id'], 'parentId' => $row['parent_id'] ?: '', 'author' => $row['author_name'], 'role' => $row['author_role'],
                'text' => $row['text'], 'likes' => $likesByComment[$row['id']] ?? 0, 'liked' => isset($likedByUser[$row['id']]), 'pinned' => (bool) $row['pinned'], 'reported' => $row['status'] === 'reported',
                'createdAt' => $row['created_at'], 'updatedAt' => $row['updated_at'],
            ], $stmt->fetchAll());
            json_response(['comments' => $comments]);
        }
        if ($method === 'POST') {
            $session = require_auth();
            $body = read_json();
            $text = trim((string) ($body['text'] ?? ''));
            if ($text === '') json_response(['error' => 'INVALID_COMMENT', 'message' => 'Comment text is required.'], 400);
            $id = uuid_value('CMT-');
            $now = now_iso();
            $pdo->prepare("INSERT INTO comments (id, story_slug, parent_id, user_id, text, status, pinned, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'published', 0, ?, ?)")
                ->execute([$id, $slug, $body['parentId'] ?? null, $session['user']['id'], substr($text, 0, 4000), $now, $now]);
            enqueue_background_job('moderation.scan');
            json_response(['comment' => ['id' => $id, 'parentId' => $body['parentId'] ?? '', 'author' => $session['user']['name'], 'role' => $session['user']['role'], 'text' => $text, 'likes' => 0, 'liked' => false, 'pinned' => false, 'reported' => false, 'createdAt' => $now, 'updatedAt' => $now]], 201);
        }
    }

    if (preg_match('#^/api/comments/([^/]+)$#', $path, $m)) {
        if (!feature_flag_enabled('comments')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Comments are disabled.'], 403);
        $session = require_auth();
        $id = urldecode($m[1]);
        if ($method === 'PATCH') {
            $body = read_json();
            $text = trim((string) ($body['text'] ?? ''));
            $status = in_array($body['status'] ?? 'published', ['published', 'reported', 'hidden'], true) ? $body['status'] : 'published';
            $pinned = !empty($body['pinned']) ? 1 : 0;
            $pdo->prepare('UPDATE comments SET text = COALESCE(NULLIF(?, \'\'), text), pinned = ?, status = ?, updated_at = ? WHERE id = ?')
                ->execute([$text, $pinned, $status, now_iso(), $id]);
            json_response(['ok' => true]);
        }
        if ($method === 'DELETE') {
            $pdo->prepare("UPDATE comments SET status = 'deleted', updated_at = ? WHERE id = ?")->execute([now_iso(), $id]);
            json_response(['ok' => true]);
        }
    }

    if ($method === 'POST' && preg_match('#^/api/comments/([^/]+)/like$#', $path, $m)) {
        if (!feature_flag_enabled('comments')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Comments are disabled.'], 403);
        $session = require_auth();
        $commentId = urldecode($m[1]);
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ? AND status != 'deleted'");
        $stmt->execute([$commentId]);
        if (!$stmt->fetch()) json_response(['error' => 'NOT_FOUND', 'message' => 'Comment not found.'], 404);
        $existing = $pdo->prepare('SELECT comment_id FROM comment_likes WHERE comment_id = ? AND user_id = ?');
        $existing->execute([$commentId, $session['user']['id']]);
        if ($existing->fetch()) {
            $pdo->prepare('DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?')->execute([$commentId, $session['user']['id']]);
            $liked = false;
        } else {
            $pdo->prepare('INSERT INTO comment_likes (comment_id, user_id, created_at) VALUES (?, ?, ?)')->execute([$commentId, $session['user']['id'], now_iso()]);
            $liked = true;
        }
        $count = $pdo->prepare('SELECT COUNT(*) AS likes FROM comment_likes WHERE comment_id = ?');
        $count->execute([$commentId]);
        json_response(['liked' => $liked, 'likes' => (int) $count->fetch()['likes']]);
    }

    if ($path === '/api/support/tickets') {
        $session = require_auth();
        if ($method === 'GET') {
            $stmt = support_is_staff($session)
                ? $pdo->query('SELECT support_tickets.*, users.name AS requester_name, users.email AS requester_email FROM support_tickets LEFT JOIN users ON users.id = support_tickets.user_id ORDER BY support_tickets.updated_at DESC')
                : (function () use ($pdo, $session) { $s = $pdo->prepare('SELECT support_tickets.*, users.name AS requester_name, users.email AS requester_email FROM support_tickets LEFT JOIN users ON users.id = support_tickets.user_id WHERE support_tickets.user_id = ? ORDER BY support_tickets.updated_at DESC'); $s->execute([$session['user']['id']]); return $s; })();
            json_response(['tickets' => array_map('public_support_ticket', $stmt->fetchAll())]);
        }
        if ($method === 'POST') {
            $body = str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data') ? $_POST : read_json();
            $subject = trim((string) ($body['subject'] ?? ''));
            $details = trim((string) ($body['details'] ?? ''));
            if ($subject === '' || $details === '') json_response(['error' => 'INVALID_TICKET', 'message' => 'Subject and problem details are required.'], 400);
            $id = uuid_value('TKT-');
            $now = now_iso();
            $pdo->prepare("INSERT INTO support_tickets (id, user_id, subject, category, priority, status, owner, details, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'Open', 'Support', ?, ?, ?)")
                ->execute([$id, $session['user']['id'], substr($subject, 0, 200), substr((string) ($body['category'] ?? 'General'), 0, 80), substr((string) ($body['priority'] ?? 'Normal'), 0, 40), substr($details, 0, 4000), $now, $now]);
            $messageId = uuid_value('STM-');
            $pdo->prepare("INSERT INTO support_ticket_messages (id, ticket_id, user_id, visibility, body, created_at) VALUES (?, ?, ?, 'public', ?, ?)")
                ->execute([$messageId, $id, $session['user']['id'], substr($details, 0, 4000), $now]);
            support_upload_attachments($pdo, $id, $messageId, $session['user']['id']);
            $ticket = support_ticket_for_session($pdo, $id, $session);
            json_response(['ticket' => public_support_ticket($ticket)], 201);
        }
    }

    if (preg_match('#^/api/support/tickets/([^/]+)$#', $path, $m)) {
        $session = require_auth();
        $ticketId = urldecode($m[1]);
        $ticket = support_ticket_for_session($pdo, $ticketId, $session);
        if (!$ticket) json_response(['error' => 'NOT_FOUND', 'message' => 'Support ticket not found.'], 404);
        if ($method === 'GET') {
            $messages = $pdo->prepare('SELECT support_ticket_messages.*, users.name AS user_name, users.role AS user_role FROM support_ticket_messages LEFT JOIN users ON users.id = support_ticket_messages.user_id WHERE ticket_id = ? AND (visibility = ? OR ? = 1) ORDER BY created_at ASC');
            $staff = support_is_staff($session) ? 1 : 0;
            $messages->execute([$ticketId, 'public', $staff]);
            $messageRows = $messages->fetchAll();
            $attachments = $pdo->prepare('SELECT * FROM support_ticket_attachments WHERE ticket_id = ? ORDER BY created_at ASC');
            $attachments->execute([$ticketId]);
            $byMessage = [];
            foreach ($attachments->fetchAll() as $attachment) {
                $byMessage[$attachment['message_id'] ?: 'ticket'][] = [
                    'id' => $attachment['id'],
                    'name' => $attachment['original_name'],
                    'url' => $attachment['url'],
                    'mimeType' => $attachment['mime_type'],
                    'size' => (int) $attachment['size'],
                    'createdAt' => $attachment['created_at'],
                ];
            }
            json_response([
                'ticket' => public_support_ticket($ticket),
                'messages' => array_map(fn($row) => [
                    'id' => $row['id'],
                    'ticketId' => $row['ticket_id'],
                    'userId' => $row['user_id'],
                    'userName' => $row['user_name'] ?? 'Support',
                    'userRole' => $row['user_role'] ?? '',
                    'visibility' => $row['visibility'],
                    'body' => $row['body'],
                    'createdAt' => $row['created_at'],
                    'attachments' => $byMessage[$row['id']] ?? [],
                ], $messageRows),
            ]);
        }
        if ($method === 'PATCH') {
            if (!support_is_staff($session)) json_response(['error' => 'FORBIDDEN', 'message' => 'Only support staff can update ticket status.'], 403);
            $body = read_json();
            $statuses = ['Open', 'Waiting on customer', 'In progress', 'Resolved', 'Closed'];
            $priorities = ['Low', 'Normal', 'High', 'Urgent'];
            $status = in_array($body['status'] ?? $ticket['status'], $statuses, true) ? (string) ($body['status'] ?? $ticket['status']) : $ticket['status'];
            $priority = in_array($body['priority'] ?? $ticket['priority'], $priorities, true) ? (string) ($body['priority'] ?? $ticket['priority']) : $ticket['priority'];
            $owner = substr((string) ($body['owner'] ?? $ticket['owner']), 0, 120) ?: 'Support';
            $pdo->prepare('UPDATE support_tickets SET status = ?, priority = ?, owner = ?, updated_at = ? WHERE id = ?')
                ->execute([$status, $priority, $owner, now_iso(), $ticketId]);
            $updated = support_ticket_for_session($pdo, $ticketId, $session);
            json_response(['ticket' => public_support_ticket($updated)]);
        }
    }

    if ($method === 'POST' && preg_match('#^/api/support/tickets/([^/]+)/messages$#', $path, $m)) {
        $session = require_auth();
        $ticketId = urldecode($m[1]);
        $ticket = support_ticket_for_session($pdo, $ticketId, $session);
        if (!$ticket) json_response(['error' => 'NOT_FOUND', 'message' => 'Support ticket not found.'], 404);
        $body = str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data') ? $_POST : read_json();
        $text = trim((string) ($body['body'] ?? ''));
        if ($text === '' && empty($_FILES['attachments'])) json_response(['error' => 'EMPTY_REPLY', 'message' => 'Write a reply or attach a file.'], 400);
        $visibility = support_is_staff($session) && ($body['visibility'] ?? '') === 'internal' ? 'internal' : 'public';
        $messageId = uuid_value('STM-');
        $now = now_iso();
        $pdo->prepare('INSERT INTO support_ticket_messages (id, ticket_id, user_id, visibility, body, created_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$messageId, $ticketId, $session['user']['id'], $visibility, substr($text, 0, 4000), $now]);
        support_upload_attachments($pdo, $ticketId, $messageId, $session['user']['id']);
        $nextStatus = support_is_staff($session) ? 'Waiting on customer' : 'Open';
        $pdo->prepare('UPDATE support_tickets SET status = CASE WHEN status = ? THEN status ELSE ? END, owner = ?, updated_at = ? WHERE id = ?')
            ->execute(['Closed', $nextStatus, support_is_staff($session) ? ($session['user']['name'] ?? 'Support') : $ticket['owner'], $now, $ticketId]);
        json_response(['ok' => true], 201);
    }

    if ($method === 'GET' && $path === '/api/admin/analytics') {
        require_auth(['admin']);
        json_response(admin_analytics_summary());
    }

    if ($method === 'POST' && $path === '/api/admin/subscriptions/sync') {
        require_auth(['admin']);
        json_response(sync_subscription_lifecycle());
    }

    if ($path === '/api/admin/security/policies') {
        $session = require_auth(['admin']);
        if ($method === 'GET') {
            $policies = [];
            foreach ($pdo->query('SELECT * FROM security_policies ORDER BY key')->fetchAll() as $row) $policies[$row['key']] = parse_json_field($row['value_json'], null);
            $permissions = $pdo->query('SELECT * FROM role_permissions ORDER BY role, permission')->fetchAll();
            json_response(['policies' => $policies, 'permissions' => $permissions]);
        }
        if ($method === 'PUT') {
            $body = read_json();
            $now = now_iso();
            foreach (($body['policies'] ?? []) as $key => $value) {
                $pdo->prepare('INSERT INTO security_policies (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
                    ->execute([preg_replace('/[^a-z0-9_.-]/i', '', (string) $key), json_encode($value, JSON_UNESCAPED_SLASHES), $session['user']['id'], $now]);
            }
            foreach (($body['permissions'] ?? []) as $permission) {
                if (!is_array($permission)) continue;
                $role = (string) ($permission['role'] ?? '');
                $perm = (string) ($permission['permission'] ?? '');
                if (!$role || !$perm) continue;
                $pdo->prepare('INSERT INTO role_permissions (role, permission, allowed, updated_by, updated_at) VALUES (?, ?, ?, ?, ?) ON CONFLICT(role, permission) DO UPDATE SET allowed = excluded.allowed, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
                    ->execute([$role, $perm, empty($permission['allowed']) ? 0 : 1, $session['user']['id'], $now]);
            }
            json_response(['ok' => true, 'updatedAt' => $now]);
        }
    }

    if ($method === 'POST' && $path === '/api/admin/security/backups/verify') {
        require_auth(['admin']);
        $source = database_path();
        $backupDir = dirname(__DIR__) . '/storage/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0775, true);
        $target = $backupDir . '/inkriver-' . gmdate('Ymd-His') . '.sqlite';
        $ok = is_file($source) && copy($source, $target);
        $details = [
            'source' => basename($source),
            'backup' => $ok ? $target : null,
            'sizeBytes' => $ok ? filesize($target) : 0,
            'verified' => false,
        ];
        if ($ok) {
            try {
                $check = new PDO('sqlite:' . $target);
                $row = $check->query('SELECT COUNT(*) AS count FROM users')->fetch(PDO::FETCH_ASSOC);
                $details['verified'] = is_array($row);
            } catch (Throwable $error) {
                $details['error'] = $error->getMessage();
            }
        }
        $status = !empty($details['verified']) ? 'healthy' : 'failed';
        $pdo->prepare('INSERT INTO system_health_checks (key, status, details_json, checked_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET status = excluded.status, details_json = excluded.details_json, checked_at = excluded.checked_at')
            ->execute(['backup.verification', $status, json_encode($details, JSON_UNESCAPED_SLASHES), now_iso()]);
        json_response(['status' => $status, 'details' => $details], $status === 'healthy' ? 200 : 500);
    }

    if ($path === '/api/admin/moderation') {
        $session = require_auth(['admin', 'moderator']);
        if ($method === 'GET') {
            $rows = $pdo->query('SELECT moderation_cases.*, users.name AS assignee_name FROM moderation_cases LEFT JOIN users ON users.id = moderation_cases.assignee_user_id ORDER BY created_at DESC')->fetchAll();
            json_response(['cases' => $rows]);
        }
        if ($method === 'POST') {
            $body = read_json();
            $id = 'MOD-' . random_int(1000, 9999);
            $now = now_iso();
            $pdo->prepare("INSERT INTO moderation_cases (id, reporter_user_id, target_type, target_id, kind, subject, risk, status, evidence_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open', '[]', ?, ?)")
                ->execute([$id, $session['user']['id'], $body['targetType'] ?? 'post', $body['targetId'] ?? '', $body['kind'] ?? 'Report', $body['subject'] ?? 'Moderation case', $body['risk'] ?? 'Medium', $now, $now]);
            json_response(['case' => ['id' => $id]], 201);
        }
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/moderation/([^/]+)$#', $path, $m)) {
        $session = require_auth(['admin', 'moderator']);
        $caseId = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT * FROM moderation_cases WHERE id = ?');
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        if (!$case) json_response(['error' => 'NOT_FOUND', 'message' => 'Moderation case not found.'], 404);
        $body = read_json();
        $allowedStatuses = ['Open', 'In review', 'Escalated', 'Resolved', 'Dismissed'];
        $status = in_array($body['status'] ?? $case['status'], $allowedStatuses, true) ? $body['status'] ?? $case['status'] : $case['status'];
        $evidence = parse_json_field($case['evidence_json'] ?? '[]', []);
        if (is_array($body['evidence'] ?? null)) {
            $evidence[] = [
                'type' => substr((string) ($body['evidence']['type'] ?? 'note'), 0, 80),
                'value' => substr((string) ($body['evidence']['value'] ?? ''), 0, 2000),
                'addedBy' => $session['user']['id'],
                'addedAt' => now_iso(),
            ];
        }
        $assignee = !empty($body['assignToSelf']) ? $session['user']['id'] : ($body['assigneeUserId'] ?? $case['assignee_user_id']);
        $pdo->prepare('UPDATE moderation_cases SET status = ?, evidence_json = ?, assignee_user_id = ?, updated_at = ? WHERE id = ?')
            ->execute([$status, json_encode($evidence, JSON_UNESCAPED_SLASHES), $assignee ?: null, now_iso(), $caseId]);
        audit_log($session['user']['id'], 'moderation.case_update', 'moderation_case', $caseId, ['status' => $status]);
        $stmt = $pdo->prepare('SELECT moderation_cases.*, users.name AS assignee_name FROM moderation_cases LEFT JOIN users ON users.id = moderation_cases.assignee_user_id WHERE moderation_cases.id = ?');
        $stmt->execute([$caseId]);
        json_response(['case' => $stmt->fetch()]);
    }

    if ($path === '/api/admin/moderation/rules') {
        $session = require_auth(['admin', 'moderator']);
        if ($method === 'GET') json_response(['rules' => $pdo->query('SELECT * FROM moderation_rules ORDER BY created_at DESC')->fetchAll()]);
        if ($method === 'POST') {
            $body = read_json();
            $kind = in_array($body['kind'] ?? '', ['blocked_word', 'link_filter', 'bot_score', 'duplicate_check', 'plagiarism'], true) ? $body['kind'] : 'blocked_word';
            $action = in_array($body['action'] ?? '', ['allow', 'queue', 'hide', 'block'], true) ? $body['action'] : 'queue';
            $pattern = trim((string) ($body['pattern'] ?? ''));
            if ($pattern === '') json_response(['error' => 'INVALID_RULE', 'message' => 'A moderation pattern is required.'], 400);
            $id = uuid_value('MRL-');
            $now = now_iso();
            $pdo->prepare('INSERT INTO moderation_rules (id, kind, pattern, action, enabled, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$id, $kind, $pattern, $action, empty($body['enabled']) ? 0 : 1, $session['user']['id'], $now, $now]);
            json_response(['rule' => ['id' => $id]], 201);
        }
    }

    if ($path === '/api/admin/moderation/dictionary') {
        $session = require_auth(['admin', 'moderator']);
        if ($method === 'GET') json_response(['terms' => $pdo->query('SELECT * FROM moderation_dictionary ORDER BY updated_at DESC')->fetchAll()]);
        if ($method === 'POST') {
            $body = read_json();
            $term = trim((string) ($body['term'] ?? ''));
            if ($term === '') json_response(['error' => 'INVALID_TERM', 'message' => 'A moderation term is required.'], 400);
            $now = now_iso();
            $pdo->prepare('INSERT INTO moderation_dictionary (id, term, kind, severity, action, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT(term) DO UPDATE SET kind = excluded.kind, severity = excluded.severity, action = excluded.action, updated_at = excluded.updated_at')
                ->execute([uuid_value('MDT-'), substr($term, 0, 180), substr((string) ($body['kind'] ?? 'blocked_word'), 0, 80), substr((string) ($body['severity'] ?? 'Medium'), 0, 40), substr((string) ($body['action'] ?? 'queue'), 0, 40), $session['user']['id'], $now, $now]);
            json_response(['terms' => $pdo->query('SELECT * FROM moderation_dictionary ORDER BY updated_at DESC')->fetchAll()], 201);
        }
    }

    if ($method === 'GET' && $path === '/api/admin/ads') {
        require_auth(['admin']);
        $rows = $pdo->query("SELECT ad_campaigns.*, SUM(CASE WHEN ad_events.event_type = 'impression' THEN 1 ELSE 0 END) AS impressions, SUM(CASE WHEN ad_events.event_type = 'click' THEN 1 ELSE 0 END) AS clicks FROM ad_campaigns LEFT JOIN ad_events ON ad_events.campaign_id = ad_campaigns.id GROUP BY ad_campaigns.id ORDER BY ad_campaigns.created_at DESC")->fetchAll();
        json_response(['campaigns' => array_map(fn($r) => ['id'=>$r['id'],'name'=>$r['name'],'placement'=>$r['placement'],'sponsor'=>$r['sponsor'],'headline'=>$r['headline'],'targetUrl'=>$r['target_url'],'creativeUrl'=>$r['creative_url'],'targetTopics'=>parse_json_field($r['target_topics'],[]),'status'=>$r['status'],'budget'=>(int)$r['budget'],'cpm'=>(int)$r['cpm'],'impressions'=>(int)($r['impressions'] ?? 0),'clicks'=>(int)($r['clicks'] ?? 0)], $rows)]);
    }

    if ($method === 'POST' && $path === '/api/admin/ads') {
        if (!feature_flag_enabled('ads')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Ads are disabled.'], 403);
        $session = require_auth(['admin']);
        $body = read_json();
        $id = 'AD-' . uuid_value();
        $now = now_iso();
        $placement = in_array($body['placement'] ?? 'leaderboard', ['leaderboard', 'square', 'native'], true) ? $body['placement'] : 'leaderboard';
        $pdo->prepare("INSERT INTO ad_campaigns (id, name, placement, sponsor, headline, target_url, creative_url, target_topics, status, budget, cpm, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, '[]', 'active', 0, 0, ?, ?, ?)")
            ->execute([$id, $body['name'] ?? 'Campaign', $placement, $body['sponsor'] ?? 'Sponsor', $body['headline'] ?? 'Sponsored message', $body['targetUrl'] ?? '#', $body['creativeUrl'] ?? '', $session['user']['id'], $now, $now]);
        json_response(['campaign' => ['id' => $id]], 201);
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/ads/([^/]+)$#', $path, $m)) {
        if (!feature_flag_enabled('ads')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Ads are disabled.'], 403);
        require_auth(['admin']);
        $body = read_json();
        $id = urldecode($m[1]);
        $status = in_array($body['status'] ?? 'active', ['draft', 'active', 'paused', 'ended'], true) ? $body['status'] : 'active';
        $pdo->prepare('UPDATE ad_campaigns SET status = ?, updated_at = ? WHERE id = ?')->execute([$status, now_iso(), $id]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/ads/events') {
        if (!feature_flag_enabled('ads')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Ads are disabled.'], 403);
        $session = current_session();
        $body = read_json();
        $stmt = $pdo->prepare("SELECT * FROM ad_campaigns WHERE id = ? AND status = 'active'");
        $stmt->execute([$body['campaignId'] ?? '']);
        $campaign = $stmt->fetch();
        if (!$campaign) json_response(['error' => 'NOT_FOUND', 'message' => 'Active ad campaign not found.'], 404);
        $pdo->prepare('INSERT INTO ad_events (id, campaign_id, user_id, event_type, placement, story_slug, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([uuid_value(), $campaign['id'], $session['user']['id'] ?? null, $body['eventType'] ?? 'impression', $campaign['placement'], $body['storySlug'] ?? null, now_iso()]);
        json_response(['ok' => true], 201);
    }

    if ($method === 'GET' && $path === '/api/admin/discounts') {
        require_auth(['admin']);
        $rows = $pdo->query('SELECT * FROM discount_codes ORDER BY created_at DESC')->fetchAll();
        json_response(['discounts' => array_map(fn($r) => ['id'=>$r['id'],'code'=>$r['code'],'description'=>$r['description'],'discountType'=>$r['discount_type'],'discountValue'=>(int)$r['discount_value'],'appliesToPlanId'=>$r['applies_to_plan_id'] ?: '','audience'=>$r['audience'],'maxRedemptions'=>$r['max_redemptions'],'redeemedCount'=>(int)$r['redeemed_count'],'startsAt'=>$r['starts_at'] ?: '','endsAt'=>$r['ends_at'] ?: '','active'=>(bool)$r['active']], $rows)]);
    }

    if ($method === 'POST' && $path === '/api/admin/discounts') {
        if (!feature_flag_enabled('discounts')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Discounts are disabled.'], 403);
        $session = require_auth(['admin']);
        $body = read_json();
        $code = strtoupper(preg_replace('/[^A-Z0-9_-]/', '', (string) ($body['code'] ?? '')));
        if (strlen($code) < 3) json_response(['error' => 'INVALID_CODE', 'message' => 'Discount code is invalid.'], 400);
        $id = 'DISC-' . uuid_value();
        $now = now_iso();
        $pdo->prepare("INSERT INTO discount_codes (id, code, description, discount_type, discount_value, applies_to_plan_id, audience, max_redemptions, active, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)")
            ->execute([$id, $code, $body['description'] ?? '', ($body['discountType'] ?? '') === 'amount' ? 'amount' : 'percent', max(1, (int) ($body['discountValue'] ?? 10)), $body['appliesToPlanId'] ?? null, $body['audience'] ?? 'All readers', $body['maxRedemptions'] ?? null, $session['user']['id'], $now, $now]);
        json_response(['discount' => ['id' => $id, 'code' => $code]], 201);
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/discounts/([^/]+)$#', $path, $m)) {
        if (!feature_flag_enabled('discounts')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Discounts are disabled.'], 403);
        require_auth(['admin']);
        $body = read_json();
        $pdo->prepare('UPDATE discount_codes SET active = ?, updated_at = ? WHERE id = ?')->execute([!empty($body['active']) ? 1 : 0, now_iso(), urldecode($m[1])]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/discounts/validate') {
        if (!feature_flag_enabled('discounts')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Discounts are disabled.'], 403);
        $body = read_json();
        $code = strtoupper(trim((string) ($body['code'] ?? '')));
        $amount = max(0, (int) ($body['amount'] ?? 0));
        $stmt = $pdo->prepare('SELECT * FROM discount_codes WHERE code = ? COLLATE NOCASE AND active = 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) json_response(['error' => 'INVALID_DISCOUNT', 'message' => 'This discount code is not active.'], 400);
        $discountAmount = $row['discount_type'] === 'amount' ? min($amount, (int) $row['discount_value']) : (int) round($amount * (int) $row['discount_value'] / 100);
        json_response(['valid' => true, 'discount' => ['id' => $row['id'], 'code' => $row['code']], 'discountAmount' => $discountAmount, 'finalAmount' => max(0, $amount - $discountAmount)]);
    }

    if ($method === 'GET' && $path === '/api/me/creator-analytics') {
        $session = require_auth(['writer', 'admin']);
        $stories = array_values(array_filter(document_value('stories', []), fn($story) =>
            ($story['authorUserId'] ?? '') === $session['user']['id'] || strtolower((string) ($story['author'] ?? '')) === strtolower($session['user']['name'])
        ));
        $slugs = array_map(fn($story) => $story['slug'] ?? '', $stories);
        $eventRows = $pdo->query('SELECT story_slug, event_type, COUNT(*) AS count, COALESCE(AVG(value), 0) AS average_value FROM engagement_events WHERE story_slug IS NOT NULL GROUP BY story_slug, event_type')->fetchAll();
        $commentRows = $pdo->query("SELECT story_slug, COUNT(*) AS count FROM comments WHERE status != 'deleted' GROUP BY story_slug")->fetchAll();
        $stmt = $pdo->prepare("SELECT story_slug, COALESCE(SUM(writer_amount), 0) AS amount FROM writer_tips WHERE status = 'paid' AND writer_name = ? COLLATE NOCASE GROUP BY story_slug");
        $stmt->execute([$session['user']['name']]);
        json_response([
            'stories' => array_map(fn($story) => ['slug' => $story['slug'] ?? '', 'title' => $story['title'] ?? '', 'status' => $story['status'] ?? 'draft'], $stories),
            'eventRows' => array_values(array_filter($eventRows, fn($row) => in_array($row['story_slug'], $slugs, true))),
            'commentRows' => array_values(array_filter($commentRows, fn($row) => in_array($row['story_slug'], $slugs, true))),
            'tipRows' => $stmt->fetchAll(),
        ]);
    }

    if ($method === 'POST' && $path === '/api/stories') {
        $session = require_auth(['writer', 'admin']);
        $body = read_json();
        $title = trim((string) ($body['title'] ?? ''));
        if (strlen($title) < 3) json_response(['error' => 'INVALID_STORY', 'message' => 'Enter a valid story title.'], 400);
        $stories = document_value('stories', []);
        $baseSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) ($body['slug'] ?? $title))), '-');
        if ($baseSlug === '') $baseSlug = 'story-' . time();
        $slug = $baseSlug;
        $i = 2;
        while (array_filter($stories, fn($story) => ($story['slug'] ?? '') === $slug)) $slug = $baseSlug . '-' . $i++;
        $now = now_iso();
        $publication = substr((string) ($body['publication'] ?? 'InkRiver'), 0, 120);
        $requestedStatus = in_array(($body['status'] ?? 'draft'), ['draft', 'review', 'approved', 'scheduled', 'published'], true) ? (string) $body['status'] : 'draft';
        if (in_array($requestedStatus, ['approved', 'scheduled', 'published'], true) && !can_manage_publication_content($session, $publication)) {
            $requestedStatus = 'review';
        }
        $story = [
            'id' => 'blog-' . uuid_value(),
            'slug' => $slug,
            'title' => $title,
            'dek' => substr((string) ($body['dek'] ?? ''), 0, 500),
            'author' => $session['user']['name'],
            'authorUserId' => $session['user']['id'],
            'role' => $session['user']['role'] === 'admin' ? 'Editorial desk' : 'Writer',
            'publication' => $publication,
            'topic' => substr((string) ($body['topic'] ?? 'Marketing'), 0, 80),
            'readTime' => substr((string) ($body['readTime'] ?? '5 min read'), 0, 40),
            'premium' => (bool) ($body['premium'] ?? false),
            'ads' => ($body['ads'] ?? true) !== false,
            'earning' => ($body['earning'] ?? true) !== false,
            'color' => substr((string) ($body['color'] ?? 'mint'), 0, 30),
            'imageUrl' => substr((string) ($body['imageUrl'] ?? ''), 0, 2000),
            'tags' => is_array($body['tags'] ?? null) ? array_slice($body['tags'], 0, 30) : [],
            'status' => $requestedStatus,
            'scheduledAt' => $requestedStatus === 'scheduled' ? substr((string) ($body['scheduledAt'] ?? ''), 0, 80) : '',
            'body' => is_array($body['body'] ?? null) ? array_slice($body['body'], 0, 200) : [(string) ($body['content'] ?? '')],
            'contentHtml' => substr((string) ($body['contentHtml'] ?? ''), 0, 200000),
            'interactiveBlocks' => is_array($body['interactiveBlocks'] ?? null) ? array_slice($body['interactiveBlocks'], 0, 30) : [],
            'seo' => is_array($body['seo'] ?? null) ? $body['seo'] : [],
            'claps' => 0, 'comments' => 0, 'reads' => 0, 'revenue' => 0,
            'publishedAt' => $requestedStatus === 'published' ? $now : '',
            'createdAt' => $now, 'updatedAt' => $now,
        ];
        array_unshift($stories, $story);
        $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute(['stories', json_encode($stories, JSON_UNESCAPED_SLASHES), $session['user']['id'], $now]);
        record_story_revision($story, $session['user']['id'], 'Story created as ' . $story['status']);
        rebuild_story_search_index($stories);
        $translationJobId = null;
        if ($story['status'] === 'published' && provider_status()['ai']) $translationJobId = enqueue_background_job('translations.backfill');
        if ($story['status'] === 'published') create_notification($session['user']['id'], 'story_published', 'Story published', $story['title'] . ' is now live.', '/stories/' . $story['slug']);
        json_response(['story' => $story, 'translationJobId' => $translationJobId], 201);
    }

    if ($method === 'GET' && preg_match('#^/api/admin/stories/([^/]+)/revisions$#', $path, $m)) {
        require_auth(['admin']);
        $slug = urldecode($m[1]);
        $stmt = $pdo->prepare('SELECT id, story_slug, revision_number, author_user_id, note, created_at FROM post_revisions WHERE story_slug = ? ORDER BY revision_number DESC LIMIT 100');
        $stmt->execute([$slug]);
        json_response(['revisions' => $stmt->fetchAll()]);
    }

    if ($method === 'POST' && preg_match('#^/api/admin/stories/([^/]+)/revisions/([^/]+)/restore$#', $path, $m)) {
        $session = require_auth(['admin']);
        $slug = urldecode($m[1]);
        $revisionId = urldecode($m[2]);
        $stmt = $pdo->prepare('SELECT * FROM post_revisions WHERE id = ? AND story_slug = ?');
        $stmt->execute([$revisionId, $slug]);
        $revision = $stmt->fetch();
        if (!$revision) json_response(['error' => 'NOT_FOUND', 'message' => 'Revision not found.'], 404);
        $snapshot = parse_json_field($revision['snapshot_json'], null);
        if (!is_array($snapshot)) json_response(['error' => 'INVALID_REVISION', 'message' => 'Revision snapshot is invalid.'], 400);
        $stories = document_value('stories', []);
        $restored = false;
        foreach ($stories as &$story) {
            if (($story['slug'] ?? '') === $slug) {
                $snapshot['updatedAt'] = now_iso();
                $story = $snapshot;
                $restored = true;
                break;
            }
        }
        unset($story);
        if (!$restored) array_unshift($stories, $snapshot);
        $pdo->prepare('INSERT INTO platform_documents (key, value_json, updated_by, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(key) DO UPDATE SET value_json = excluded.value_json, updated_by = excluded.updated_by, updated_at = excluded.updated_at')
            ->execute(['stories', json_encode($stories, JSON_UNESCAPED_SLASHES), $session['user']['id'], now_iso()]);
        record_story_revision($snapshot, $session['user']['id'], 'Restored revision ' . $revision['revision_number']);
        rebuild_story_search_index($stories);
        json_response(['story' => $snapshot]);
    }

    if ($method === 'GET' && $path === '/api/me/payouts') {
        $session = require_auth(['writer', 'admin']);
        $stmt = $pdo->prepare("SELECT * FROM writer_tips WHERE writer_name = ? COLLATE NOCASE AND status = 'paid' ORDER BY created_at DESC");
        $stmt->execute([$session['user']['name']]);
        $tips = $stmt->fetchAll();
        $paidIds = array_column($pdo->query('SELECT tip_id FROM writer_payout_items')->fetchAll(), 'tip_id');
        $availableTips = array_values(array_filter($tips, fn($tip) => !in_array($tip['id'], $paidIds, true)));
        $payoutStmt = $pdo->prepare('SELECT * FROM writer_payouts WHERE writer_user_id = ? ORDER BY created_at DESC');
        $payoutStmt->execute([$session['user']['id']]);
        $accountStmt = $pdo->prepare('SELECT * FROM payout_accounts WHERE user_id = ?');
        $accountStmt->execute([$session['user']['id']]);
        json_response(['account' => $accountStmt->fetch() ?: null, 'availableAmount' => array_sum(array_map(fn($tip) => (int) $tip['writer_amount'], $availableTips)), 'availableTips' => $availableTips, 'payouts' => $payoutStmt->fetchAll()]);
    }

    if ($method === 'PUT' && $path === '/api/me/payout-account') {
        $session = require_auth(['writer', 'admin']);
        $body = read_json();
        $now = now_iso();
        $pdo->prepare("INSERT INTO payout_accounts (user_id, account_holder, bank_name, payout_method, account_reference, tax_id_reference, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'pending_review', ?, ?) ON CONFLICT(user_id) DO UPDATE SET account_holder = excluded.account_holder, bank_name = excluded.bank_name, payout_method = excluded.payout_method, account_reference = excluded.account_reference, tax_id_reference = excluded.tax_id_reference, status = 'pending_review', updated_at = excluded.updated_at")
            ->execute([$session['user']['id'], $body['accountHolder'] ?? $session['user']['name'], $body['bankName'] ?? '', $body['payoutMethod'] ?? 'bank', hash('sha256', (string) ($body['accountReference'] ?? '')), hash('sha256', (string) ($body['taxIdReference'] ?? '')), $now, $now]);
        json_response(['ok' => true]);
    }

    if ($method === 'GET' && $path === '/api/admin/payouts') {
        require_auth(['admin']);
        json_response(['payouts' => $pdo->query('SELECT writer_payouts.*, users.name AS writer_name, users.email AS writer_email FROM writer_payouts LEFT JOIN users ON users.id = writer_payouts.writer_user_id ORDER BY writer_payouts.created_at DESC')->fetchAll(), 'accounts' => $pdo->query('SELECT payout_accounts.*, users.name, users.email FROM payout_accounts JOIN users ON users.id = payout_accounts.user_id ORDER BY payout_accounts.updated_at DESC')->fetchAll()]);
    }

    if ($method === 'POST' && $path === '/api/admin/payouts') {
        $session = require_auth(['admin']);
        $body = read_json();
        $writerId = (string) ($body['writerUserId'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role IN ('writer', 'admin')");
        $stmt->execute([$writerId]);
        $writer = $stmt->fetch();
        if (!$writer) json_response(['error' => 'NOT_FOUND', 'message' => 'Writer not found.'], 404);
        $accountStmt = $pdo->prepare("SELECT * FROM payout_accounts WHERE user_id = ? AND status = 'verified'");
        $accountStmt->execute([$writerId]);
        $account = $accountStmt->fetch();
        if (!$account) json_response(['error' => 'PAYOUT_ACCOUNT_NOT_VERIFIED', 'message' => 'The writer payout account is not verified.'], 400);
        $tipStmt = $pdo->prepare("SELECT * FROM writer_tips WHERE writer_name = ? COLLATE NOCASE AND status = 'paid'");
        $tipStmt->execute([$writer['name']]);
        $tips = $tipStmt->fetchAll();
        $amount = array_sum(array_map(fn($tip) => (int) $tip['writer_amount'], $tips));
        if (!$amount) json_response(['error' => 'NO_BALANCE', 'message' => 'No unpaid writer balance is available.'], 400);
        $id = 'PO-' . uuid_value();
        $now = now_iso();
        $pdo->prepare("INSERT INTO writer_payouts (id, writer_user_id, amount, currency, status, payout_account_snapshot, created_at, updated_at) VALUES (?, ?, ?, 'INR', 'pending', ?, ?, ?)")
            ->execute([$id, $writerId, $amount, json_encode($account), $now, $now]);
        json_response(['payout' => ['id' => $id]], 201);
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/payouts/([^/]+)$#', $path, $m)) {
        require_auth(['admin']);
        $body = read_json();
        $status = in_array($body['status'] ?? '', ['pending', 'processing', 'paid', 'failed', 'cancelled'], true) ? $body['status'] : 'pending';
        $pdo->prepare('UPDATE writer_payouts SET status = ?, paid_at = CASE WHEN ? = ? THEN ? ELSE paid_at END, updated_at = ? WHERE id = ?')
            ->execute([$status, $status, 'paid', now_iso(), now_iso(), urldecode($m[1])]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && preg_match('#^/api/admin/payouts/([^/]+)/execute$#', $path, $m)) {
        require_auth(['admin']);
        $payoutId = urldecode($m[1]);
        try {
            json_response(execute_payout_by_id($payoutId));
        } catch (Throwable $error) {
            json_response(['error' => 'PAYOUT_TRANSFER_FAILED', 'message' => $error->getMessage()], 502);
        }
    }

    if ($method === 'PATCH' && preg_match('#^/api/admin/payout-accounts/([^/]+)$#', $path, $m)) {
        require_auth(['admin']);
        $body = read_json();
        $status = in_array($body['status'] ?? '', ['pending_review', 'verified', 'rejected'], true) ? $body['status'] : 'pending_review';
        $pdo->prepare('UPDATE payout_accounts SET status = ?, updated_at = ? WHERE user_id = ?')->execute([$status, now_iso(), urldecode($m[1])]);
        json_response(['ok' => true]);
    }

    if ($method === 'POST' && $path === '/api/admin/segments/preview') {
        require_auth(['admin']);
        $body = read_json();
        json_response(preview_audience_segment($body['rules'] ?? ''));
    }

    if ($method === 'POST' && $path === '/api/payments/orders') {
        $session = require_auth();
        $body = read_json();
        $provider = (string) ($body['provider'] ?? '');
        if ($provider === 'paypal') {
            $restriction = paypal_restricted_for_india($session, is_array($body['clientHints'] ?? null) ? $body['clientHints'] : []);
            if ($restriction['restricted']) {
                audit_log($session['user']['id'], 'payment.paypal_blocked_india', 'payment_provider', 'paypal', ['signals' => $restriction['signals']]);
                json_response(['error' => 'PAYPAL_NOT_AVAILABLE_IN_INDIA', 'message' => 'PayPal is not available for users located in India. Please choose Razorpay, PayU, or Cashfree.', 'restriction' => $restriction], 403);
            }
        }
        if (empty(provider_status()['payments'][$provider])) json_response(['error' => 'PROVIDER_NOT_CONFIGURED', 'message' => 'Payment provider is not configured.'], 503);
        $id = 'PAY-' . uuid_value();
        $now = now_iso();
        $metadata = is_array($body['metadata'] ?? null) ? $body['metadata'] : [];
        if (($metadata['kind'] ?? '') === 'tip' && !feature_flag_enabled('tips')) {
            json_response(['error' => 'FEATURE_DISABLED', 'message' => 'Writer tipping is disabled.'], 403);
        }
        if (!empty($body['discountCode'])) $metadata['discountCode'] = strtoupper(trim((string) $body['discountCode']));
        $metadata['originalAmount'] = (int) ($body['amount'] ?? 0);
        $paymentPreview = ['id' => $id, 'amount' => (int) ($body['amount'] ?? 0), 'currency' => strtoupper((string) ($body['currency'] ?? 'INR')), 'purpose' => $body['purpose'] ?? 'payment'];
        $amount = payment_amount_for_checkout($paymentPreview, $metadata);
        $isRecurringMembership = !in_array(($metadata['kind'] ?? 'membership'), ['tip', 'gift'], true);
        $pdo->prepare("INSERT INTO payments (id, user_id, provider, purpose, amount, currency, status, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'created', ?, ?, ?)")
            ->execute([$id, $session['user']['id'], $provider, $body['purpose'] ?? 'payment', $amount, strtoupper((string) ($body['currency'] ?? 'INR')), json_encode($metadata, JSON_UNESCAPED_SLASHES), $now, $now]);
        $payment = ['id' => $id, 'amount' => $amount, 'currency' => strtoupper((string) ($body['currency'] ?? 'INR')), 'purpose' => $body['purpose'] ?? 'payment'];
        try {
            $order = $isRecurringMembership
                ? create_provider_subscription_checkout($provider, $payment, $session['user'], $metadata)
                : create_provider_order($provider, $payment, $session['user']);
        } catch (Throwable $error) {
            $pdo->prepare("UPDATE payments SET status = 'failed', updated_at = ? WHERE id = ?")->execute([now_iso(), $id]);
            json_response(['error' => 'CHECKOUT_FAILED', 'message' => $error->getMessage()], 502);
        }
        if (!empty($order['providerSubscriptionId'])) {
            $metadata['providerSubscriptionId'] = (string) $order['providerSubscriptionId'];
            $metadata['recurring'] = true;
        }
        $pdo->prepare("UPDATE payments SET status = 'pending', provider_order_id = ?, metadata = ?, updated_at = ? WHERE id = ?")
            ->execute([$order['providerOrderId'] ?? '', json_encode($metadata, JSON_UNESCAPED_SLASHES), now_iso(), $id]);
        json_response(['paymentId' => $id, 'provider' => $provider, 'amount' => $amount, 'currency' => $payment['currency'], 'checkout' => $order['checkout'] ?? []], 201);
    }

    if ($method === 'POST' && $path === '/api/payments/razorpay/verify') {
        $session = require_auth();
        $body = read_json();
        $paymentId = (string) ($body['paymentId'] ?? '');
        $orderId = (string) ($body['razorpay_order_id'] ?? '');
        $subscriptionId = (string) ($body['razorpay_subscription_id'] ?? '');
        $razorpayPaymentId = (string) ($body['razorpay_payment_id'] ?? '');
        $signature = (string) ($body['razorpay_signature'] ?? '');
        $signedValue = $subscriptionId !== '' ? $razorpayPaymentId . '|' . $subscriptionId : $orderId . '|' . $razorpayPaymentId;
        $expected = hash_hmac('sha256', $signedValue, (string) provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret'));
        if (!$paymentId || (!$orderId && !$subscriptionId) || !$razorpayPaymentId || !hash_equals($expected, $signature)) {
            json_response(['error' => 'INVALID_PAYMENT_SIGNATURE', 'message' => 'Payment verification failed.'], 400);
        }
        $user = activate_paid_payment($paymentId, $razorpayPaymentId);
        audit_log($session['user']['id'], 'payment.razorpay_verified', 'payment', $paymentId);
        json_response(['ok' => true, 'user' => $user]);
    }

    if ($method === 'GET' && $path === '/api/payments/paypal/return') {
        $paymentId = (string) ($_GET['paymentId'] ?? '');
        $tokenId = (string) ($_GET['token'] ?? '');
        $subscriptionId = (string) ($_GET['subscription_id'] ?? $_GET['ba_token'] ?? '');
        if ($paymentId === '' || ($tokenId === '' && $subscriptionId === '')) redirect_response('/pricing?payment=invalid');
        $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $token = http_request_json($base . '/v1/oauth2/token', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => ['grant_type' => 'client_credentials'],
        ]);
        if (($_GET['mode'] ?? '') === 'subscription' || $subscriptionId !== '') {
            $local = $pdo->prepare('SELECT provider_order_id FROM payments WHERE id = ?');
            $local->execute([$paymentId]);
            $storedSubscriptionId = (string) (($local->fetch()['provider_order_id'] ?? '') ?: $subscriptionId);
            $remote = http_request_json($base . '/v1/billing/subscriptions/' . rawurlencode($storedSubscriptionId), [
                'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            ]);
            if (!in_array(($remote['status'] ?? ''), ['ACTIVE', 'APPROVAL_PENDING'], true)) redirect_response('/pricing?payment=failed');
            activate_paid_payment($paymentId, $storedSubscriptionId);
            redirect_response('/dashboard?payment=success');
        }
        $capture = http_request_json($base . '/v2/checkout/orders/' . rawurlencode($tokenId) . '/capture', [
            'method' => 'POST',
            'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
            'body' => '{}',
        ]);
        if (($capture['status'] ?? '') !== 'COMPLETED') redirect_response('/pricing?payment=failed');
        activate_paid_payment($paymentId, $capture['id'] ?? $tokenId);
        redirect_response('/dashboard?payment=success');
    }

    if ($method === 'GET' && $path === '/api/payments/cashfree/return') {
        $paymentId = (string) ($_GET['paymentId'] ?? $_GET['order_id'] ?? '');
        if ($paymentId === '') redirect_response('/pricing?payment=invalid');
        $paymentStmt = $pdo->prepare('SELECT * FROM payments WHERE id = ?');
        $paymentStmt->execute([$paymentId]);
        $localPayment = $paymentStmt->fetch();
        $localMetadata = $localPayment ? parse_json_field($localPayment['metadata'] ?? '{}', []) : [];
        if (!empty($localMetadata['recurring'])) {
            $subscriptionBase = provider_config_value('CASHFREE_SUBSCRIPTIONS_API_URL', 'cashfree', 'subscriptions_api_url', '');
            if ($subscriptionBase === '') redirect_response('/pricing?payment=failed');
            $subscription = http_request_json(rtrim($subscriptionBase, '/') . '/' . rawurlencode((string) ($localPayment['provider_order_id'] ?? $paymentId)), [
                'headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret')],
            ]);
            if (!in_array(strtoupper((string) ($subscription['status'] ?? $subscription['subscription_status'] ?? '')), ['ACTIVE', 'APPROVED', 'BANK_APPROVAL_PENDING'], true)) redirect_response('/pricing?payment=failed');
            activate_paid_payment($paymentId, (string) ($subscription['subscription_id'] ?? $localPayment['provider_order_id'] ?? $paymentId));
            redirect_response('/dashboard?payment=success');
        }
        $base = provider_config_value('CASHFREE_ENVIRONMENT', 'cashfree', 'environment', 'sandbox') === 'production' ? 'https://api.cashfree.com/pg/orders/' : 'https://sandbox.cashfree.com/pg/orders/';
        $order = http_request_json($base . rawurlencode($paymentId), [
            'headers' => ['x-api-version' => '2023-08-01', 'x-client-id' => provider_config_value('CASHFREE_CLIENT_ID', 'cashfree', 'client_id'), 'x-client-secret' => provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret')],
        ]);
        if (($order['order_status'] ?? '') !== 'PAID') redirect_response('/pricing?payment=failed');
        activate_paid_payment($paymentId, $order['cf_order_id'] ?? $paymentId);
        redirect_response('/dashboard?payment=success');
    }

    if ($method === 'POST' && $path === '/api/payments/payu/return') {
        $body = $_POST;
        $paymentId = (string) ($body['udf1'] ?? $body['txnid'] ?? '');
        if (($body['status'] ?? '') !== 'success' || $paymentId === '' || !payu_response_hash_valid($body)) redirect_response('/pricing?payment=failed');
        activate_paid_payment($paymentId, (string) ($body['subscription_id'] ?? $body['mihpayid'] ?? $body['txnid'] ?? ''));
        redirect_response('/dashboard?payment=success');
    }

    if ($method === 'POST' && preg_match('#^/api/webhooks/(razorpay|paypal|cashfree|payu)$#', $path, $m)) {
        $provider = $m[1];
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) $payload = $_POST ?: [];
        $valid = false;
        $eventType = (string) ($payload['event'] ?? $payload['event_type'] ?? $payload['type'] ?? $payload['status'] ?? 'webhook');
        $paymentId = '';
        $providerPaymentId = '';
        $providerSubscriptionId = '';

        if ($provider === 'razorpay') {
            $signature = (string) ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
            $secret = provider_config_value('RAZORPAY_WEBHOOK_SECRET', 'razorpay', 'webhook_secret') ?: provider_config_value('RAZORPAY_KEY_SECRET', 'razorpay', 'key_secret');
            $valid = $secret && $signature && hash_equals(hash_hmac('sha256', $raw, (string) $secret), $signature);
            $entity = $payload['payload']['payment']['entity'] ?? $payload['payload']['subscription']['entity'] ?? $payload['payload']['order']['entity'] ?? [];
            $providerPaymentId = (string) ($entity['id'] ?? '');
            $providerSubscriptionId = (string) ($entity['subscription_id'] ?? ($payload['payload']['subscription']['entity']['id'] ?? ''));
            $paymentId = (string) ($entity['notes']['paymentId'] ?? $entity['receipt'] ?? '');
            if ($paymentId === '' && !empty($entity['order_id'])) {
                $stmt = $pdo->prepare('SELECT id FROM payments WHERE provider = ? AND provider_order_id = ?');
                $stmt->execute(['razorpay', $entity['order_id']]);
                $paymentId = (string) (($stmt->fetch()['id'] ?? ''));
            }
            if ($paymentId === '' && $providerSubscriptionId !== '') {
                $stmt = $pdo->prepare('SELECT id FROM payments WHERE provider = ? AND provider_order_id = ?');
                $stmt->execute(['razorpay', $providerSubscriptionId]);
                $paymentId = (string) (($stmt->fetch()['id'] ?? ''));
            }
        } elseif ($provider === 'cashfree') {
            $signature = (string) ($_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? $_SERVER['HTTP_X_CASHFREE_SIGNATURE'] ?? '');
            $timestamp = (string) ($_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '');
            $secret = (string) (provider_config_value('CASHFREE_WEBHOOK_SECRET', 'cashfree', 'webhook_secret') ?: provider_config_value('CASHFREE_CLIENT_SECRET', 'cashfree', 'client_secret'));
            $valid = $secret && $signature ? hash_equals(base64_encode(hash_hmac('sha256', $timestamp . $raw, $secret, true)), $signature) : false;
            $data = $payload['data'] ?? $payload;
            $paymentId = (string) ($data['order']['order_id'] ?? $data['order_id'] ?? '');
            $providerPaymentId = (string) ($data['payment']['cf_payment_id'] ?? $data['cf_payment_id'] ?? '');
            $providerSubscriptionId = (string) ($data['subscription']['subscription_id'] ?? $data['subscription_id'] ?? '');
        } elseif ($provider === 'paypal') {
            $eventType = (string) ($payload['event_type'] ?? 'paypal.webhook');
            $valid = false;
            $paypalWebhookId = provider_config_value('PAYPAL_WEBHOOK_ID', 'paypal', 'webhook_id');
            if ($paypalWebhookId) {
                try {
                    $base = provider_config_value('PAYPAL_ENVIRONMENT', 'paypal', 'environment', 'sandbox') === 'production' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
                    $token = http_request_json($base . '/v1/oauth2/token', [
                        'method' => 'POST',
                        'headers' => ['Authorization' => 'Basic ' . base64_encode(provider_config_value('PAYPAL_CLIENT_ID', 'paypal', 'client_id') . ':' . provider_config_value('PAYPAL_CLIENT_SECRET', 'paypal', 'client_secret')), 'Content-Type' => 'application/x-www-form-urlencoded'],
                        'body' => ['grant_type' => 'client_credentials'],
                    ]);
                    $verify = http_request_json($base . '/v1/notifications/verify-webhook-signature', [
                        'method' => 'POST',
                        'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? ''), 'Content-Type' => 'application/json'],
                        'body' => json_encode([
                            'auth_algo' => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
                            'cert_url' => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
                            'transmission_id' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
                            'transmission_sig' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
                            'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
                            'webhook_id' => $paypalWebhookId,
                            'webhook_event' => $payload,
                        ], JSON_UNESCAPED_SLASHES),
                    ]);
                    $valid = ($verify['verification_status'] ?? '') === 'SUCCESS';
                } catch (Throwable) {
                    $valid = false;
                }
            }
            $resource = $payload['resource'] ?? [];
            $providerPaymentId = (string) ($resource['id'] ?? '');
            $providerSubscriptionId = (string) ($resource['billing_agreement_id'] ?? $resource['subscription_id'] ?? (str_contains($eventType, 'SUBSCRIPTION') ? ($resource['id'] ?? '') : ''));
            if (!empty($resource['custom_id'])) $paymentId = (string) $resource['custom_id'];
            foreach (($resource['purchase_units'] ?? []) as $unit) {
                if (!empty($unit['reference_id'])) $paymentId = (string) $unit['reference_id'];
            }
        } else {
            $paymentId = (string) ($payload['udf1'] ?? $payload['txnid'] ?? '');
            $providerPaymentId = (string) ($payload['mihpayid'] ?? $payload['payuMoneyId'] ?? '');
            $providerSubscriptionId = (string) ($payload['subscription_id'] ?? $payload['subscriptionId'] ?? '');
            $valid = $paymentId !== '' && payu_response_hash_valid($payload);
        }

        $paidEvent = preg_match('/paid|captured|completed|success/i', $eventType . ' ' . json_encode($payload));
        $subscriptionProcessed = $valid && $providerSubscriptionId !== '' && process_subscription_provider_event($provider, $eventType, $providerSubscriptionId, $payload);
        $processed = $subscriptionProcessed || ($valid && $paidEvent && process_paid_provider_event($provider, $paymentId, $providerPaymentId));
        store_payment_webhook($provider, $eventType, $paymentId, $providerPaymentId, $valid, $payload, $processed ? 'processed' : ($valid ? 'ignored' : 'failed'));
        json_response(['ok' => true, 'processed' => $processed, 'signatureValid' => $valid]);
    }

    if ($method === 'POST' && $path === '/api/ai/assist') {
        if (!feature_flag_enabled('ai')) json_response(['error' => 'FEATURE_DISABLED', 'message' => 'AI tools are disabled.'], 403);
        $session = require_auth(['writer', 'moderator', 'admin']);
        if (!provider_status()['ai']) json_response(['error' => 'AI_NOT_CONFIGURED', 'message' => 'The AI provider is not configured on the server.'], 503);
        $body = read_json();
        $task = trim((string) ($body['task'] ?? 'improve'));
        $prompt = "Task: {$task}\nTitle: " . (string) ($body['title'] ?? '') . "\nExcerpt: " . (string) ($body['excerpt'] ?? '') . "\nContent HTML:\n" . (string) ($body['content'] ?? '');
        try {
            $result = openai_text_response($prompt, 'You are InkRiver AI. Give concise, actionable editorial help. Keep all changes optional and visible.');
            $result = $result ?: 'The AI provider returned an empty response.';
            $review = null;
            if (str_starts_with($task, 'review-')) {
                $review = [
                    'id' => uuid_value('AIR-'),
                    'task' => $task,
                    'label' => ucwords(str_replace('-', ' ', substr($task, 7))),
                    'status' => stripos($result, 'no issue') !== false || stripos($result, 'looks good') !== false ? 'passed' : 'needs_review',
                    'result' => $result,
                    'createdAt' => now_iso(),
                ];
                $slug = trim((string) ($body['slug'] ?? ''));
                $pdo->prepare("INSERT INTO ai_review_results (id, user_id, story_slug, task, status, result_text, checks_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$review['id'], $session['user']['id'], $slug ?: null, $task, $review['status'], $result, json_encode([$review], JSON_UNESCAPED_SLASHES), $review['createdAt']]);
            }
            json_response(['result' => $result, 'review' => $review]);
        } catch (Throwable $error) {
            json_response(['error' => 'AI_REQUEST_FAILED', 'message' => $error->getMessage()], 502);
        }
    }

    if ($method === 'GET' && $path === '/api/me/export') {
        $session = require_auth();
        $docs = [];
        $stmt = $pdo->prepare('SELECT key, value_json, updated_at FROM user_documents WHERE user_id = ?');
        $stmt->execute([$session['user']['id']]);
        foreach ($stmt->fetchAll() as $row) {
            if (str_starts_with((string) $row['key'], 'passkey-')) continue;
            $docs[$row['key']] = parse_json_field($row['value_json'], null);
        }
        json_response(['user' => $session['user'], 'documents' => $docs, 'exportedAt' => now_iso()]);
    }

    if ($method === 'POST' && $path === '/api/me/deletion-request') {
        $session = require_auth();
        $requestedAt = now_iso();
        $scheduledFor = gmdate('Y-m-d\TH:i:s.v\Z', time() + 30 * 86400);
        $pdo->prepare('DELETE FROM account_deletion_requests WHERE user_id = ? AND cancelled_at IS NULL')->execute([$session['user']['id']]);
        $pdo->prepare("INSERT INTO account_deletion_requests (id, user_id, status, requested_at, scheduled_for) VALUES (?, ?, 'pending_verification', ?, ?)")
            ->execute([uuid_value(), $session['user']['id'], $requestedAt, $scheduledFor]);
        json_response(['status' => 'pending_verification', 'requestedAt' => $requestedAt, 'scheduledFor' => $scheduledFor], 201);
    }

    if ($method === 'POST' && $path === '/api/me/push-subscriptions') {
        $session = require_auth();
        $body = read_json();
        $endpoint = (string) ($body['endpoint'] ?? '');
        if ($endpoint === '') json_response(['error' => 'INVALID_SUBSCRIPTION', 'message' => 'Push endpoint is required.'], 400);
        $now = now_iso();
        $pdo->prepare('INSERT INTO push_subscriptions (id, user_id, endpoint, subscription_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) ON CONFLICT(endpoint) DO UPDATE SET subscription_json = excluded.subscription_json, updated_at = excluded.updated_at')
            ->execute([uuid_value(), $session['user']['id'], $endpoint, json_encode($body, JSON_UNESCAPED_SLASHES), $now, $now]);
        json_response(['ok' => true], 201);
    }

    if ($method === 'POST' && $path === '/api/pwa/sync') {
        $session = current_session();
        $body = read_json();
        $items = is_array($body['items'] ?? null) ? $body['items'] : [$body];
        $saved = 0;
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $pdo->prepare("INSERT INTO pwa_sync_queue (id, user_id, client_id, type, payload_json, status, created_at) VALUES (?, ?, ?, ?, ?, 'queued', ?)")
                ->execute([uuid_value('SYNC-'), $session['user']['id'] ?? null, substr((string) ($item['clientId'] ?? ''), 0, 120), substr((string) ($item['type'] ?? 'event'), 0, 80), json_encode($item['payload'] ?? $item, JSON_UNESCAPED_SLASHES), now_iso()]);
            $saved++;
        }
        json_response(['queued' => $saved], 202);
    }

    if ($method === 'POST' && $path === '/api/admin/push/send') {
        require_auth(['admin']);
        $body = read_json();
        $payload = [
            'title' => substr((string) ($body['title'] ?? 'InkRiver'), 0, 80),
            'body' => substr((string) ($body['body'] ?? 'A new update is available.'), 0, 180),
            'url' => substr((string) ($body['url'] ?? '/'), 0, 500),
        ];
        $rows = $pdo->query('SELECT * FROM push_subscriptions ORDER BY updated_at DESC LIMIT 1000')->fetchAll();
        $sent = 0;
        $failed = 0;
        $notifiedUsers = [];
        foreach ($rows as $row) {
            try {
                send_push_message($row, $payload) ? $sent++ : $failed++;
            } catch (Throwable) {
                $failed++;
            }
            if (!isset($notifiedUsers[$row['user_id']])) {
                create_notification($row['user_id'], 'admin_push', $payload['title'], $payload['body'], $payload['url']);
                $notifiedUsers[$row['user_id']] = true;
            }
        }
        json_response(['sent' => $sent, 'failed' => $failed, 'total' => count($rows)]);
    }

    if ($method === 'DELETE' && preg_match('#^/api/me/social-accounts/(google|facebook)$#', $path, $m)) {
        $session = require_auth();
        $stmt = $pdo->prepare('DELETE FROM social_accounts WHERE user_id = ? AND provider = ?');
        $stmt->execute([$session['user']['id'], $m[1]]);
        json_response(['ok' => true]);
    }

    if ($method === 'GET' && preg_match('#^/api/auth/oauth/(google|facebook)/start$#', $path, $m)) {
        if (!provider_status()['social'][$m[1]]) redirect_response('/?oauth=provider_unavailable');
        $state = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $redirectUri = app_origin() . '/api/auth/oauth/' . $m[1] . '/callback';
        $pdo->prepare('INSERT INTO oauth_states (state, provider, redirect_uri, expires_at, created_at) VALUES (?, ?, ?, ?, ?)')
            ->execute([$state, $m[1], $redirectUri, gmdate('Y-m-d\TH:i:s.v\Z', time() + 600), now_iso()]);
        if ($m[1] === 'google') {
            redirect_response('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query(['client_id' => provider_config_value('GOOGLE_CLIENT_ID', 'google', 'client_id'), 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state]));
        }
        redirect_response('https://www.facebook.com/v23.0/dialog/oauth?' . http_build_query(['client_id' => provider_config_value('FACEBOOK_APP_ID', 'facebook', 'app_id'), 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'scope' => 'email,public_profile', 'state' => $state]));
    }

    if ($method === 'GET' && preg_match('#^/api/auth/oauth/(google|facebook)/callback$#', $path, $m)) {
        $provider = $m[1];
        $state = (string) ($_GET['state'] ?? '');
        $code = (string) ($_GET['code'] ?? '');
        $stmt = $pdo->prepare('SELECT * FROM oauth_states WHERE state = ? AND provider = ? AND expires_at > ?');
        $stmt->execute([$state, $provider, now_iso()]);
        $oauthState = $stmt->fetch();
        if (!$oauthState || $code === '') redirect_response('/?oauth=invalid_state');
        $pdo->prepare('DELETE FROM oauth_states WHERE state = ?')->execute([$state]);
        try {
            if ($provider === 'google') {
                $token = http_request_json('https://oauth2.googleapis.com/token', [
                    'method' => 'POST',
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'body' => [
                        'client_id' => provider_config_value('GOOGLE_CLIENT_ID', 'google', 'client_id'),
                        'client_secret' => provider_config_value('GOOGLE_CLIENT_SECRET', 'google', 'client_secret'),
                        'code' => $code,
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => $oauthState['redirect_uri'],
                    ],
                ]);
                $profile = http_request_json('https://www.googleapis.com/oauth2/v3/userinfo', [
                    'headers' => ['Authorization' => 'Bearer ' . ($token['access_token'] ?? '')],
                ]);
                finish_oauth_login('google', $profile);
            }
            $token = http_request_json('https://graph.facebook.com/v23.0/oauth/access_token?' . http_build_query([
                'client_id' => provider_config_value('FACEBOOK_APP_ID', 'facebook', 'app_id'),
                'client_secret' => provider_config_value('FACEBOOK_APP_SECRET', 'facebook', 'app_secret'),
                'redirect_uri' => $oauthState['redirect_uri'],
                'code' => $code,
            ]));
            $profile = http_request_json('https://graph.facebook.com/me?' . http_build_query(['fields' => 'id,name,email', 'access_token' => $token['access_token'] ?? '']));
            finish_oauth_login('facebook', $profile);
        } catch (Throwable $error) {
            redirect_response('/?oauth=provider_error');
        }
    }

    if ($method === 'POST' && $path === '/api/me/security/totp/setup') {
        $session = require_auth();
        $secret = base32_encode_value(random_bytes(20));
        $now = now_iso();
        $pdo->prepare("INSERT INTO user_security_settings (user_id, two_factor_secret, two_factor_enabled, updated_at) VALUES (?, ?, 0, ?) ON CONFLICT(user_id) DO UPDATE SET two_factor_secret = excluded.two_factor_secret, two_factor_enabled = 0, updated_at = excluded.updated_at")
            ->execute([$session['user']['id'], $secret, $now]);
        json_response(['secret' => $secret, 'otpauthUrl' => 'otpauth://totp/InkRiver:' . rawurlencode($session['user']['email']) . '?secret=' . $secret . '&issuer=InkRiver']);
    }

    if ($method === 'POST' && $path === '/api/me/security/totp/enable') {
        $session = require_auth();
        $body = read_json();
        $stmt = $pdo->prepare('SELECT two_factor_secret FROM user_security_settings WHERE user_id = ?');
        $stmt->execute([$session['user']['id']]);
        $security = $stmt->fetch();
        if (!$security || !verify_totp_value((string) $security['two_factor_secret'], (string) ($body['code'] ?? ''))) {
            json_response(['error' => 'INVALID_TOTP_CODE', 'message' => 'Enter the current 6-digit code from your authenticator app.'], 400);
        }
        $codes = array_map(fn() => strtoupper(bin2hex(random_bytes(5))), range(1, 8));
        $pdo->prepare('UPDATE user_security_settings SET two_factor_enabled = 1, recovery_codes_json = ?, updated_at = ? WHERE user_id = ?')
            ->execute([json_encode(array_map(fn($code) => hash('sha256', $code), $codes)), now_iso(), $session['user']['id']]);
        json_response(['twoFactorEnabled' => true, 'recoveryCodes' => $codes]);
    }

    if ($method === 'DELETE' && $path === '/api/me/security/totp') {
        $session = require_auth();
        $pdo->prepare("UPDATE user_security_settings SET two_factor_enabled = 0, two_factor_secret = NULL, recovery_codes_json = '[]', updated_at = ? WHERE user_id = ?")
            ->execute([now_iso(), $session['user']['id']]);
        json_response(['twoFactorEnabled' => false]);
    }

    if ($method === 'POST' && $path === '/api/admin/translations/run') {
        require_auth(['admin']);
        if (!provider_status()['ai']) json_response(['error' => 'AI_NOT_CONFIGURED', 'message' => 'Add OPENAI_API_KEY to generate article translations.'], 503);
        json_response(['jobId' => enqueue_background_job('translations.backfill'), 'status' => 'queued', 'locales' => array_column(configured_translation_languages(), 'locale')], 202);
    }

    if ($method === 'GET' && $path === '/api/me/payout-account') {
        $session = require_auth(['writer', 'admin']);
        $stmt = $pdo->prepare('SELECT * FROM payout_accounts WHERE user_id = ?');
        $stmt->execute([$session['user']['id']]);
        json_response(['account' => $stmt->fetch() ?: null]);
    }

    json_response(['error' => 'NOT_FOUND', 'message' => 'API route not found in PHP port yet.'], 404);
}
