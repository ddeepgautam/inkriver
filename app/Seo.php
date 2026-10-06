<?php
declare(strict_types=1);

/**
 * Server-rendered discovery layer for public Nitross pages.
 *
 * The application remains interactive after hydration, but crawlers and link
 * unfurlers receive the page's real title, copy, links, canonical and schema in
 * the first response. This file intentionally depends only on the public data
 * accessors already loaded by Api.php.
 */

function seo_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function seo_text(mixed $value, int $limit = 0): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    if ($limit > 0 && strlen($text) > $limit) return rtrim(substr($text, 0, $limit - 1)) . '…';
    return $text;
}

function seo_absolute_url(string $value): string
{
    $value = trim($value);
    if ($value === '') return '';
    if (preg_match('#^https?://#i', $value)) return $value;
    return rtrim(app_origin(), '/') . '/' . ltrim($value, '/');
}

function seo_site_settings(): array
{
    try {
        return public_site_seo();
    } catch (Throwable) {
        return [];
    }
}

function seo_site_name(array $settings = []): string
{
    return trim((string) ($settings['siteTitle'] ?? '')) ?: 'Nitross';
}

function seo_default_description(): string
{
    return 'Learn entrepreneurship, discover startup insights, explore founder and company profiles, and use practical resources to build and grow your business.';
}

function seo_published_stories(): array
{
    try {
        return array_values(array_filter(document_value('stories', []), fn($story) => is_array($story) && ($story['status'] ?? '') === 'published' && !empty($story['slug'])));
    } catch (Throwable) {
        return [];
    }
}

function seo_categories(): array
{
    try {
        return array_values(array_filter(public_categories(), fn($category) => is_array($category) && !empty($category['slug'])));
    } catch (Throwable) {
        return [];
    }
}

function seo_story_by_slug(string $slug): ?array
{
    foreach (seo_published_stories() as $story) {
        if (hash_equals((string) $story['slug'], $slug)) return $story;
    }
    return null;
}

function seo_category_by_slug(string $slug): ?array
{
    foreach (seo_categories() as $category) {
        if (hash_equals((string) $category['slug'], $slug)) return $category;
    }
    return null;
}

function seo_category_slug_for_topic(string $topic): string
{
    $topicSlug = business_slug($topic);
    foreach (seo_categories() as $category) {
        if (strcasecmp((string) ($category['name'] ?? ''), $topic) === 0 || (string) ($category['slug'] ?? '') === $topicSlug) return (string) $category['slug'];
        if ($topicSlug === 'ai-automation' && (string) ($category['slug'] ?? '') === 'ai') return 'ai';
    }
    return $topicSlug;
}

function seo_resource_by_slug(string $slug): ?array
{
    try {
        $row = resource_find_by_slug_or_id($slug);
        return $row ? public_resource($row) : null;
    } catch (Throwable) {
        return null;
    }
}

function seo_business_profile(string $type, string $slug): ?array
{
    try {
        return business_get_profile($type, $slug);
    } catch (Throwable) {
        return null;
    }
}

function seo_publication_by_slug(string $slug): ?array
{
    try {
        foreach (current_publication_rows() as $publication) {
            if (($publication['status'] ?? '') === 'active' && hash_equals((string) ($publication['slug'] ?? ''), $slug)) return $publication;
        }
    } catch (Throwable) {
    }
    return null;
}

function seo_public_user_by_slug(string $slug): ?array
{
    if (!preg_match('/^[a-z0-9_]{3,30}$/', $slug)) return null;
    try {
        $stmt = Database::pdo()->prepare("SELECT name, username, avatar_url, headline, bio, website, location, expertise_json, updated_at FROM users WHERE username = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    } catch (Throwable) {
        return null;
    }
}

function seo_breadcrumbs(array $items): array
{
    $position = 0;
    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(function (array $item) use (&$position) {
            return ['@type' => 'ListItem', 'position' => ++$position, 'name' => $item[0], 'item' => rtrim(app_origin(), '/') . $item[1]];
        }, $items),
    ];
}

function seo_page_defaults(string $path): array
{
    $settings = seo_site_settings();
    $siteName = seo_site_name($settings);
    return [
        'status' => 200,
        'title' => trim((string) ($settings['homepageSeoTitle'] ?? '')) ?: $siteName . ' | Entrepreneurship, Startups, AI and Business Growth',
        'description' => trim((string) ($settings['homepageMetaDescription'] ?? '')) ?: seo_default_description(),
        'canonical' => rtrim(app_origin(), '/') . ($path === '/' ? '/' : $path),
        'robots' => 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1',
        'type' => 'website',
        'image' => seo_absolute_url((string) ($settings['defaultSocialImage'] ?? '')),
        'socialTitle' => '',
        'socialDescription' => '',
        'body' => '',
        'schema' => [],
        'siteName' => $siteName,
    ];
}

function seo_internal_links(array $stories, int $limit = 12): string
{
    if (!$stories) return '';
    $links = '';
    foreach (array_slice($stories, 0, $limit) as $story) {
        $links .= '<li><a href="/stories/' . rawurlencode((string) $story['slug']) . '">' . seo_escape((string) $story['title']) . '</a></li>';
    }
    return '<nav aria-label="Related articles"><h2>Explore more insights</h2><ul>' . $links . '</ul></nav>';
}

function seo_matches_terms(string $haystack, array $terms): bool
{
    foreach ($terms as $term) {
        $term = trim((string) $term);
        if (strlen($term) >= 3 && stripos($haystack, $term) !== false) return true;
    }
    return false;
}

function seo_related_stories(array $stories, array $terms, string $excludeSlug = '', int $limit = 8): array
{
    return array_slice(array_values(array_filter($stories, function ($story) use ($terms, $excludeSlug) {
        if (($story['slug'] ?? '') === $excludeSlug) return false;
        $haystack = implode(' ', [(string) ($story['title'] ?? ''), (string) ($story['dek'] ?? ''), (string) ($story['topic'] ?? ''), implode(' ', (array) ($story['tags'] ?? []))]);
        return seo_matches_terms($haystack, $terms);
    })), 0, $limit);
}

function seo_story_entity_links(array $story): string
{
    $terms = array_values(array_filter(array_merge([(string) ($story['topic'] ?? '')], (array) ($story['tags'] ?? []))));
    $links = [];
    try {
        foreach (business_list_profiles('company', ['status' => 'published']) as $profile) {
            $haystack = implode(' ', [(string) ($profile['name'] ?? ''), (string) ($profile['industry'] ?? ''), (string) ($profile['description'] ?? ''), implode(' ', (array) ($profile['keywords'] ?? [])), implode(' ', (array) ($profile['technologies'] ?? []))]);
            if (seo_matches_terms($haystack, $terms)) $links[] = ['/companies/' . rawurlencode((string) $profile['slug']), (string) $profile['name']];
            if (count($links) >= 4) break;
        }
        foreach (business_list_profiles('person', ['status' => 'published']) as $profile) {
            $haystack = implode(' ', [(string) ($profile['full_name'] ?? ''), (string) ($profile['headline'] ?? ''), (string) ($profile['biography'] ?? ''), implode(' ', (array) ($profile['expertise'] ?? []))]);
            if (seo_matches_terms($haystack, $terms)) $links[] = ['/founders/' . rawurlencode((string) $profile['slug']), (string) $profile['full_name']];
            if (count($links) >= 7) break;
        }
        $rows = Database::pdo()->query("SELECT * FROM resources WHERE status = 'published' ORDER BY updated_at DESC LIMIT 100")->fetchAll();
        foreach ($rows as $row) {
            $resource = public_resource($row);
            $haystack = implode(' ', [(string) ($resource['name'] ?? ''), (string) ($resource['category'] ?? ''), (string) ($resource['shortDescription'] ?? ''), implode(' ', (array) ($resource['tags'] ?? []))]);
            if (seo_matches_terms($haystack, $terms)) $links[] = ['/resources/' . rawurlencode((string) $resource['slug']), (string) $resource['name']];
            if (count($links) >= 10) break;
        }
    } catch (Throwable) {
    }
    if (!$links) return '';
    return '<nav aria-label="Related founders, companies, and resources"><h2>Related business profiles and resources</h2><ul>'
        . implode('', array_map(fn($link) => '<li><a href="' . $link[0] . '">' . seo_escape($link[1]) . '</a></li>', $links)) . '</ul></nav>';
}

function seo_resolve_page(string $path): array
{
    $path = '/' . ltrim(rawurldecode($path), '/');
    if ($path !== '/') $path = rtrim($path, '/');
    $page = seo_page_defaults($path);
    $siteName = $page['siteName'];
    $stories = seo_published_stories();
    $categories = seo_categories();
    $origin = rtrim(app_origin(), '/');

    if ($path === '/') {
        $categoryLinks = implode('', array_map(fn($category) => '<li><a href="/topics/' . rawurlencode((string) $category['slug']) . '">' . seo_escape((string) $category['name']) . '</a></li>', $categories));
        $page['body'] = '<main><header><h1>Learn entrepreneurship. Build and grow your business.</h1><p>' . seo_escape($page['description']) . '</p></header>'
            . ($categoryLinks ? '<nav aria-label="Topics"><h2>Business topics</h2><ul>' . $categoryLinks . '</ul></nav>' : '')
            . seo_internal_links($stories, 20) . '<p><a href="/business-network">Explore founders and companies</a> · <a href="/resources">Browse business resources</a></p></main>';
        $page['schema'] = [
            ['@type' => 'Organization', 'name' => $siteName, 'url' => $origin . '/', 'logo' => seo_absolute_url((string) (seo_site_settings()['organizationLogo'] ?? '')) ?: null],
            ['@type' => 'WebSite', 'name' => $siteName, 'url' => $origin . '/', 'potentialAction' => ['@type' => 'SearchAction', 'target' => $origin . '/search?q={search_term_string}', 'query-input' => 'required name=search_term_string']],
        ];
        return $page;
    }

    if (preg_match('#^/stories/([^/]+)$#', $path, $match)) {
        $story = seo_story_by_slug($match[1]);
        if (!$story) return seo_not_found_page($path, $siteName);
        $seo = is_array($story['seo'] ?? null) ? $story['seo'] : [];
        $topic = trim((string) ($story['topic'] ?? 'Entrepreneurship'));
        $topicSlug = seo_category_slug_for_topic($topic);
        $page['title'] = trim((string) ($seo['seoTitle'] ?? '')) ?: (string) $story['title'] . ' | ' . $siteName;
        $page['description'] = trim((string) ($seo['metaDescription'] ?? '')) ?: seo_text($story['dek'] ?? '', 160);
        $customCanonical = trim((string) ($seo['canonicalUrl'] ?? ''));
        if ($customCanonical !== '' && preg_match('#^https?://#i', $customCanonical)) $page['canonical'] = $customCanonical;
        $page['robots'] = ($seo['robotsIndex'] ?? true) === false ? 'noindex,' : 'index,';
        $page['robots'] .= ($seo['robotsFollow'] ?? true) === false ? 'nofollow' : 'follow';
        $page['robots'] .= ',max-snippet:' . (int) ($seo['maxSnippet'] ?? -1) . ',max-image-preview:' . (string) ($seo['maxImagePreview'] ?? 'large') . ',max-video-preview:' . (int) ($seo['maxVideoPreview'] ?? -1);
        $page['type'] = 'article';
        $page['image'] = seo_absolute_url((string) ($seo['socialImage'] ?? $story['imageUrl'] ?? ''));
        $page['socialTitle'] = trim((string) ($seo['socialTitle'] ?? ''));
        $page['socialDescription'] = trim((string) ($seo['socialDescription'] ?? ''));
        $bodyText = seo_text(($story['contentHtml'] ?? '') ?: implode("\n\n", (array) ($story['body'] ?? [])));
        if (!empty($story['premium'])) $bodyText = seo_text($story['dek'] ?? $bodyText, 500);
        $related = seo_related_stories($stories, array_merge([$topic], (array) ($story['tags'] ?? [])), (string) $story['slug']);
        $page['body'] = '<main><article><nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/topics/' . rawurlencode($topicSlug) . '">' . seo_escape($topic) . '</a></nav><h1>' . seo_escape((string) $story['title']) . '</h1><p>' . seo_escape((string) ($story['dek'] ?? '')) . '</p><p>By ' . seo_escape((string) ($story['author'] ?? $siteName)) . '</p>' . ($bodyText ? '<div><p>' . nl2br(seo_escape($bodyText)) . '</p></div>' : '') . '</article>' . seo_internal_links($related) . seo_story_entity_links($story) . '</main>';
        $authorId = $origin . '/#author-' . rawurlencode(business_slug((string) ($story['author'] ?? $siteName)));
        $publisherId = $origin . '/#organization';
        $page['schema'] = [
            ['@type' => (string) ($seo['schemaArticleType'] ?? 'BlogPosting'), 'headline' => (string) $story['title'], 'description' => $page['description'], 'image' => $page['image'] ?: null, 'datePublished' => $story['publishedAt'] ?? null, 'dateModified' => $story['updatedAt'] ?? $story['publishedAt'] ?? null, 'author' => ['@id' => $authorId], 'publisher' => ['@id' => $publisherId], 'mainEntityOfPage' => $page['canonical'], 'articleSection' => $topic, 'keywords' => implode(', ', (array) ($story['tags'] ?? []))],
            ['@type' => 'Person', '@id' => $authorId, 'name' => (string) ($story['author'] ?? $siteName)],
            ['@type' => 'Organization', '@id' => $publisherId, 'name' => $siteName, 'url' => $origin . '/', 'logo' => seo_absolute_url((string) (seo_site_settings()['organizationLogo'] ?? '')) ?: null],
            seo_breadcrumbs([['Home', '/'], [$topic, '/topics/' . $topicSlug], [(string) $story['title'], '/stories/' . $story['slug']]]),
        ];
        return $page;
    }

    if (preg_match('#^/topics/([^/]+)$#', $path, $match)) {
        $category = seo_category_by_slug($match[1]);
        if (!$category) return seo_not_found_page($path, $siteName);
        $page['canonical'] = $origin . '/topics/' . rawurlencode((string) $category['slug']);
        $page['title'] = trim((string) ($category['seoTitle'] ?? '')) ?: (string) $category['name'] . ' Insights | ' . $siteName;
        $page['description'] = trim((string) ($category['metaDescription'] ?? '')) ?: (string) ($category['description'] ?? '');
        $matching = array_values(array_filter($stories, function ($story) use ($category) {
            $topicSlug = business_slug((string) ($story['topic'] ?? ''));
            return strcasecmp((string) ($story['topic'] ?? ''), (string) $category['name']) === 0
                || $topicSlug === (string) $category['slug']
                || ($topicSlug === 'ai-automation' && (string) $category['slug'] === 'ai');
        }));
        $intro = (string) ($category['longDescription'] ?? $category['description'] ?? '');
        $page['body'] = '<main><nav aria-label="Breadcrumb"><a href="/">Home</a> / Topics</nav><h1>' . seo_escape((string) $category['name']) . '</h1><div><p>' . seo_escape($intro) . '</p></div>' . seo_internal_links($matching, 50) . seo_story_entity_links(['topic' => (string) $category['name'], 'tags' => [(string) $category['slug']]]) . '</main>';
        $page['schema'] = [['@type' => 'CollectionPage', 'name' => (string) $category['name'], 'description' => $page['description'], 'url' => $page['canonical'], 'about' => ['@type' => 'Thing', 'name' => (string) $category['name']], 'mainEntity' => ['@type' => 'ItemList', 'itemListElement' => array_map(fn($index, $story) => ['@type' => 'ListItem', 'position' => $index + 1, 'url' => $origin . '/stories/' . rawurlencode((string) $story['slug']), 'name' => (string) $story['title']], array_keys($matching), $matching)]], seo_breadcrumbs([['Home', '/'], [(string) $category['name'], '/topics/' . $category['slug']]])];
        return $page;
    }

    if (preg_match('#^/(companies|founders)/([^/]+)$#', $path, $match)) {
        $isCompany = $match[1] === 'companies';
        $profile = seo_business_profile($isCompany ? 'company' : 'person', $match[2]);
        if (!$profile) return seo_not_found_page($path, $siteName);
        $name = (string) ($profile[$isCompany ? 'name' : 'full_name'] ?? '');
        $defaultTitle = $isCompany ? $name . ': Founders, Company Profile, Products & Business Overview | ' . $siteName : $name . ': Founder Profile, Companies & Biography | ' . $siteName;
        $defaultDescription = $isCompany ? ($profile['description'] ?: $profile['tagline']) : ($profile['biography'] ?: $profile['headline']);
        $page['title'] = trim((string) ($profile['seo_title'] ?? '')) ?: $defaultTitle;
        $page['description'] = trim((string) ($profile['meta_description'] ?? '')) ?: seo_text($defaultDescription, 160);
        if (!empty($profile['canonical_url'])) $page['canonical'] = (string) $profile['canonical_url'];
        if (isset($profile['robots_index']) && !(bool) $profile['robots_index']) $page['robots'] = 'noindex,follow';
        $page['image'] = seo_absolute_url((string) ($profile[$isCompany ? 'logo_url' : 'image_url'] ?? ''));
        $page['socialTitle'] = trim((string) ($profile['social_title'] ?? ''));
        $page['socialDescription'] = trim((string) ($profile['social_description'] ?? ''));
        if (!empty($profile['social_image_url'])) $page['image'] = seo_absolute_url((string) $profile['social_image_url']);
        $linked = (array) ($profile[$isCompany ? 'people' : 'companies'] ?? []);
        $linkedHtml = implode('', array_map(function ($item) use ($isCompany) {
            $label = (string) ($item[$isCompany ? 'full_name' : 'name'] ?? '');
            return '<li><a href="/' . ($isCompany ? 'founders' : 'companies') . '/' . rawurlencode((string) ($item['slug'] ?? '')) . '">' . seo_escape($label) . '</a></li>';
        }, $linked));
        $copy = $isCompany ? (($profile['description'] ?? '') . ' ' . ($profile['mission'] ?? '')) : (($profile['biography'] ?? '') . ' ' . ($profile['founder_story'] ?? ''));
        $profileTerms = $isCompany ? array_merge([(string) ($profile['industry'] ?? '')], (array) ($profile['industries'] ?? []), (array) ($profile['keywords'] ?? []), (array) ($profile['technologies'] ?? [])) : (array) ($profile['expertise'] ?? []);
        $profileStories = seo_related_stories($stories, $profileTerms);
        $page['body'] = '<main><nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/business-network">Business Network</a></nav><article><h1>' . seo_escape($name) . '</h1><p>' . seo_escape(seo_text($copy)) . '</p>' . ($linkedHtml ? '<h2>' . ($isCompany ? 'Founders and team' : 'Companies') . '</h2><ul>' . $linkedHtml . '</ul>' : '') . '</article>' . seo_internal_links($profileStories) . '</main>';
        $entityId = $page['canonical'] . '#profile';
        $entity = ['@type' => $isCompany ? 'Organization' : 'Person', '@id' => $entityId, 'name' => $name, 'description' => $page['description'], 'image' => $page['image'] ?: null, 'url' => $page['canonical']];
        $sameAs = $isCompany
            ? array_values(array_filter([$profile['website'] ?? '', $profile['linkedin_url'] ?? '', $profile['x_url'] ?? '', $profile['facebook_url'] ?? '']))
            : array_values(array_filter([$profile['website'] ?? '', $profile['linkedin_url'] ?? '', $profile['x_url'] ?? '']));
        if ($sameAs) $entity['sameAs'] = $sameAs;
        $address = array_filter([
            '@type' => 'PostalAddress',
            'addressLocality' => (string) ($profile['city'] ?? ''),
            'addressRegion' => (string) ($profile['state_region'] ?? ''),
            'addressCountry' => (string) ($profile['country'] ?? ''),
        ]);
        if (count($address) > 1) $entity['address'] = $address;
        if ($isCompany) {
            if (!empty($profile['legal_name'])) $entity['legalName'] = (string) $profile['legal_name'];
            if (!empty($profile['industry'])) $entity['industry'] = (string) $profile['industry'];
            if (!empty($profile['founded_on'])) $entity['foundingDate'] = (string) $profile['founded_on'];
            if ($linked) $entity['founder'] = array_values(array_map(fn($person) => ['@type' => 'Person', 'name' => (string) ($person['full_name'] ?? ''), 'url' => $origin . '/founders/' . rawurlencode((string) ($person['slug'] ?? ''))], array_filter($linked, fn($person) => !empty($person['is_founder']))));
            if (!empty($profile['products'])) $entity['makesOffer'] = array_map(fn($product) => ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Product', 'name' => (string) $product]], (array) $profile['products']);
            $knowledge = array_values(array_filter(array_merge((array) ($profile['technologies'] ?? []), (array) ($profile['markets'] ?? []), (array) ($profile['keywords'] ?? []))));
            if ($knowledge) $entity['knowsAbout'] = $knowledge;
        } else {
            if (!empty($profile['headline'])) $entity['jobTitle'] = (string) $profile['headline'];
            if (!empty($profile['expertise'])) $entity['knowsAbout'] = array_values((array) $profile['expertise']);
            if ($linked) $entity['worksFor'] = array_values(array_map(fn($company) => ['@type' => 'Organization', 'name' => (string) ($company['name'] ?? ''), 'url' => $origin . '/companies/' . rawurlencode((string) ($company['slug'] ?? ''))], $linked));
        }
        $page['schema'] = [['@type' => 'ProfilePage', '@id' => $page['canonical'], 'mainEntity' => ['@id' => $entityId]], $entity, seo_breadcrumbs([['Home', '/'], ['Business Network', '/business-network'], [$name, $path]])];
        return $page;
    }

    if (preg_match('#^/resources/([^/]+)$#', $path, $match)) {
        $resource = seo_resource_by_slug($match[1]);
        if (!$resource) return seo_not_found_page($path, $siteName);
        $page['title'] = trim((string) ($resource['seoTitle'] ?? '')) ?: (string) $resource['name'] . ' | ' . $siteName . ' Resources';
        $page['description'] = trim((string) ($resource['metaDescription'] ?? '')) ?: seo_text($resource['shortDescription'] ?? '', 160);
        if (!empty($resource['canonicalUrl'])) $page['canonical'] = (string) $resource['canonicalUrl'];
        if (isset($resource['robotsIndex']) && !(bool) $resource['robotsIndex']) $page['robots'] = 'noindex,follow';
        $page['socialTitle'] = trim((string) ($resource['socialTitle'] ?? ''));
        $page['socialDescription'] = trim((string) ($resource['socialDescription'] ?? ''));
        $page['image'] = seo_absolute_url((string) (($resource['socialImageUrl'] ?? '') ?: ($resource['thumbnailUrl'] ?? '')));
        $resourceStories = seo_related_stories($stories, array_merge([(string) ($resource['category'] ?? '')], (array) ($resource['tags'] ?? [])));
        $page['body'] = '<main><nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/resources">Resources</a></nav><article><h1>' . seo_escape((string) $resource['name']) . '</h1><p>' . seo_escape((string) $resource['shortDescription']) . '</p><div>' . seo_escape(seo_text($resource['description'] ?? '')) . '</div></article>' . seo_internal_links($resourceStories) . '</main>';
        $page['schema'] = [['@type' => 'Product', 'name' => (string) $resource['name'], 'description' => $page['description'], 'image' => $page['image'] ?: null, 'category' => $resource['category'] ?? null, 'sku' => $resource['id'] ?? null, 'offers' => ['@type' => 'Offer', 'priceCurrency' => $resource['currency'] ?? 'INR', 'price' => ((int) ($resource['price'] ?? 0)) / 100, 'availability' => 'https://schema.org/InStock', 'url' => $page['canonical']]], seo_breadcrumbs([['Home', '/'], ['Resources', '/resources'], [(string) $resource['name'], $path]])];
        return $page;
    }

    if (preg_match('#^/lists/([^/]+)$#', $path, $match)) {
        $lists = [
            'editors-picks' => ["Editor's Picks", 'Strong reporting and practical ideas selected by the Nitross editorial desk.'],
            'build-better-products' => ['Build Better Products', 'Design, AI, operations, and product thinking for teams making useful software.'],
            'independent-growth' => ['Independent Growth', 'A focused collection for founders and teams building sustainable companies.'],
        ];
        if (!isset($lists[$match[1]])) return seo_not_found_page($path, $siteName);
        [$name, $description] = $lists[$match[1]];
        $page['title'] = $name . ' | ' . $siteName;
        $page['description'] = $description;
        $page['body'] = '<main><h1>' . seo_escape($name) . '</h1><p>' . seo_escape($description) . '</p>' . seo_internal_links($stories, 20) . '</main>';
        $page['schema'] = [['@type' => 'CollectionPage', 'name' => $name, 'description' => $description, 'url' => $page['canonical']]];
        return $page;
    }

    if (preg_match('#^/publications/([^/]+)$#', $path, $match)) {
        $publication = seo_publication_by_slug($match[1]);
        if (!$publication || strcasecmp((string) ($publication['name'] ?? ''), 'InkRiver') === 0) return seo_not_found_page($path, $siteName);
        $name = (string) $publication['name'];
        $page['title'] = $name . ' | ' . $siteName;
        $page['description'] = seo_text($publication['description'] ?? '', 160);
        $matching = array_values(array_filter($stories, fn($story) => strcasecmp((string) ($story['publication'] ?? ''), $name) === 0));
        $page['body'] = '<main><h1>' . seo_escape($name) . '</h1><p>' . seo_escape($page['description']) . '</p>' . seo_internal_links($matching, 50) . '</main>';
        $page['schema'] = [['@type' => 'CollectionPage', 'name' => $name, 'description' => $page['description'], 'url' => $page['canonical']]];
        return $page;
    }

    $static = [
        '/resources' => ['Business Resources & Tools | ' . $siteName, 'Discover practical templates, guides, tools, and resources for founders and growing businesses.', 'Business resources'],
        '/business-network' => ['Founder & Company Network | ' . $siteName, 'Discover founder profiles, company information, products, markets, and business insights.', 'Founder and company network'],
        '/about' => ['About ' . $siteName, seo_default_description(), 'About ' . $siteName],
        '/contact' => ['Contact ' . $siteName, 'Contact the Nitross team.', 'Contact ' . $siteName],
        '/pricing' => ['Membership Plans | ' . $siteName, 'Choose a Nitross membership for premium insights, resources, and business network access.', 'Membership plans'],
        '/privacy' => ['Privacy Policy | ' . $siteName, 'Read the Nitross privacy policy.', 'Privacy policy'],
        '/terms' => ['Terms of Service | ' . $siteName, 'Read the Nitross terms of service.', 'Terms of service'],
        '/search' => ['Search | ' . $siteName, 'Search Nitross articles, resources, founders, and companies.', 'Search Nitross'],
    ];
    if (isset($static[$path])) {
        [$page['title'], $page['description'], $heading] = $static[$path];
        $page['body'] = '<main><h1>' . seo_escape($heading) . '</h1><p>' . seo_escape($page['description']) . '</p>' . ($path === '/resources' ? '' : seo_internal_links($stories, 8)) . '</main>';
        $page['schema'] = [['@type' => $path === '/resources' || $path === '/business-network' ? 'CollectionPage' : 'WebPage', 'name' => $heading, 'description' => $page['description'], 'url' => $page['canonical']]];
        return $page;
    }

    $userSlug = trim($path, '/');
    $user = !str_contains($userSlug, '/') ? seo_public_user_by_slug($userSlug) : null;
    if ($user) {
        $name = (string) $user['name'];
        $page['title'] = $name . ': Writer Profile | ' . $siteName;
        $page['description'] = seo_text(($user['bio'] ?? '') ?: ($user['headline'] ?? ''), 160);
        $page['image'] = seo_absolute_url((string) ($user['avatar_url'] ?? ''));
        $page['body'] = '<main><article><h1>' . seo_escape($name) . '</h1><p>' . seo_escape((string) ($user['headline'] ?? '')) . '</p><div>' . seo_escape((string) ($user['bio'] ?? '')) . '</div></article></main>';
        $page['schema'] = [['@type' => 'ProfilePage', 'mainEntity' => ['@type' => 'Person', 'name' => $name, 'description' => $page['description'], 'image' => $page['image'] ?: null, 'url' => $page['canonical']]]];
        return $page;
    }

    $privatePrefixes = ['/admin', '/dashboard', '/me', '/notifications', '/security', '/support', '/write', '/become-author', '/publication-invites', '/business-network/submission-success'];
    foreach ($privatePrefixes as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            $page['title'] = $siteName . ' Account';
            $page['description'] = 'Private account area.';
            $page['robots'] = 'noindex,nofollow';
            $page['body'] = '<main><h1>' . seo_escape($siteName) . ' account</h1><p>Sign in to continue.</p></main>';
            return $page;
        }
    }

    return seo_not_found_page($path, $siteName);
}

function seo_not_found_page(string $path, string $siteName): array
{
    $page = seo_page_defaults($path);
    $page['status'] = 404;
    $page['title'] = 'Page not found | ' . $siteName;
    $page['description'] = 'The requested page could not be found on ' . $siteName . '.';
    $page['robots'] = 'noindex,follow';
    $page['body'] = '<main><h1>Page not found</h1><p>The page may have moved or no longer exists.</p><p><a href="/">Return to ' . seo_escape($siteName) . '</a></p></main>';
    $page['schema'] = [];
    return $page;
}

function seo_render_document(string $shell, array $page): string
{
    $title = seo_escape((string) $page['title']);
    $description = seo_escape((string) $page['description']);
    $socialTitle = seo_escape(trim((string) ($page['socialTitle'] ?? '')) ?: (string) $page['title']);
    $socialDescription = seo_escape(trim((string) ($page['socialDescription'] ?? '')) ?: (string) $page['description']);
    $shell = preg_replace_callback('/<title>.*?<\/title>/s', fn() => '<title>' . $title . '</title>', $shell, 1) ?: $shell;
    $shell = preg_replace_callback('/<meta\s+name="description"\s+content="[^"]*"\s*\/?>/s', fn() => '<meta name="description" content="' . $description . '" />', $shell, 1) ?: $shell;
    $head = '<link rel="canonical" href="' . seo_escape((string) $page['canonical']) . '" />' . "\n"
        . '    <meta name="robots" content="' . seo_escape((string) $page['robots']) . '" />' . "\n"
        . '    <meta property="og:site_name" content="' . seo_escape((string) $page['siteName']) . '" />' . "\n"
        . '    <meta property="og:title" content="' . $socialTitle . '" />' . "\n"
        . '    <meta property="og:description" content="' . $socialDescription . '" />' . "\n"
        . '    <meta property="og:type" content="' . seo_escape((string) $page['type']) . '" />' . "\n"
        . '    <meta property="og:url" content="' . seo_escape((string) $page['canonical']) . '" />' . "\n"
        . '    <meta name="twitter:card" content="' . (!empty($page['image']) ? 'summary_large_image' : 'summary') . '" />' . "\n"
        . '    <meta name="twitter:title" content="' . $socialTitle . '" />' . "\n"
        . '    <meta name="twitter:description" content="' . $socialDescription . '" />';
    if (!empty($page['image'])) {
        $image = seo_escape((string) $page['image']);
        $head .= "\n    " . '<meta property="og:image" content="' . $image . '" />' . "\n    " . '<meta name="twitter:image" content="' . $image . '" />';
    }
    $settings = seo_site_settings();
    foreach (['google-site-verification' => 'googleVerification', 'msvalidate.01' => 'bingVerification', 'p:domain_verify' => 'pinterestVerification', 'yandex-verification' => 'yandexVerification'] as $metaName => $setting) {
        $verification = trim((string) ($settings[$setting] ?? ''));
        if ($verification !== '') $head .= "\n    " . '<meta name="' . $metaName . '" content="' . seo_escape($verification) . '" />';
    }
    if (!empty($page['schema'])) {
        $schema = ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($page['schema']))];
        $head .= "\n    " . '<script id="nitross-server-schema" type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>';
    }
    $shell = str_replace('</head>', '    ' . $head . "\n  </head>", $shell);
    $shell = preg_replace_callback('/<div id="root">.*?<\/div>/s', fn() => '<div id="root">' . $page['body'] . '</div>', $shell, 1) ?: $shell;
    return $shell;
}

function seo_canonical_redirect(string $path): ?string
{
    // The dedicated MCP origin is intentionally distinct from APP_ORIGIN.
    // Let its router serve MCP, OAuth metadata, health, and 404 responses
    // instead of redirecting discovery requests to the website origin.
    if (is_mcp_host_request()) return null;
    $canonical = parse_url(app_origin());
    $canonicalHost = strtolower((string) ($canonical['host'] ?? ''));
    $requestHost = request_host();
    if ($canonicalHost === '' || $requestHost === '' || in_array($canonicalHost, ['localhost', '127.0.0.1'], true)) return null;
    $forwarded = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwarded === 'https';
    $canonicalScheme = strtolower((string) ($canonical['scheme'] ?? 'https'));
    if ($requestHost === $canonicalHost && ($canonicalScheme !== 'https' || $secure)) return null;
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    return rtrim(app_origin(), '/') . $path . ($query !== '' ? '?' . $query : '');
}

function seo_robots_txt(?string $configured = null): string
{
    $robots = trim((string) $configured);
    if ($robots === '') $robots = "User-agent: *\nAllow: /";
    $robots = preg_replace('/^\s*Sitemap:\s*.*$/mi', '', $robots) ?? $robots;
    foreach (['/admin/', '/dashboard/', '/me', '/notifications', '/api/', '/oauth/', '/write', '/publication-invites/'] as $path) {
        if (!preg_match('/^\s*Disallow:\s*' . preg_quote($path, '/') . '\s*$/mi', $robots)) $robots .= "\nDisallow: " . $path;
    }
    return trim($robots) . "\n\nSitemap: " . rtrim(app_origin(), '/') . "/sitemap.xml\n";
}

function seo_sitemap_entries(string $type): array
{
    $origin = rtrim(app_origin(), '/');
    $entries = [];
    $add = function (string $path, ?string $modified = null) use (&$entries, $origin): void {
        $entries[] = ['loc' => $origin . $path, 'lastmod' => substr((string) ($modified ?: gmdate('c')), 0, 10)];
    };
    if ($type === 'pages') {
        foreach (['/', '/about', '/business-network', '/resources', '/pricing'] as $path) $add($path);
    } elseif ($type === 'articles') {
        foreach (seo_published_stories() as $story) {
            $seo = is_array($story['seo'] ?? null) ? $story['seo'] : [];
            if (($seo['robotsIndex'] ?? true) === false) continue;
            $add('/stories/' . rawurlencode((string) $story['slug']), (string) ($story['updatedAt'] ?? $story['publishedAt'] ?? ''));
        }
    } elseif ($type === 'categories') {
        foreach (seo_categories() as $category) $add('/topics/' . rawurlencode((string) $category['slug']), (string) ($category['updatedAt'] ?? ''));
    } elseif ($type === 'companies' || $type === 'founders') {
        try {
            foreach (business_list_profiles($type === 'companies' ? 'company' : 'person', ['status' => 'published']) as $profile) {
                if (isset($profile['robots_index']) && !(bool) $profile['robots_index']) continue;
                $add('/' . $type . '/' . rawurlencode((string) $profile['slug']), (string) ($profile['updated_at'] ?? ''));
            }
        } catch (Throwable) {
        }
    } elseif ($type === 'resources') {
        try {
            $rows = Database::pdo()->query("SELECT * FROM resources WHERE status = 'published' ORDER BY updated_at DESC")->fetchAll();
            foreach (array_map(fn($row) => public_resource($row), $rows) as $resource) {
                if (isset($resource['robotsIndex']) && !(bool) $resource['robotsIndex']) continue;
                $add('/resources/' . rawurlencode((string) $resource['slug']), (string) ($resource['updatedAt'] ?? ''));
            }
        } catch (Throwable) {
        }
    }
    return $entries;
}

function seo_sitemap_urlset(string $type): string
{
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (seo_sitemap_entries($type) as $entry) {
        $xml .= '  <url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1) . '</loc><lastmod>' . htmlspecialchars($entry['lastmod'], ENT_XML1) . "</lastmod></url>\n";
    }
    return $xml . "</urlset>\n";
}

function seo_sitemap_index(): string
{
    $origin = rtrim(app_origin(), '/');
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (['pages', 'articles', 'categories', 'founders', 'companies', 'resources'] as $type) {
        $xml .= '  <sitemap><loc>' . htmlspecialchars($origin . '/sitemaps/' . $type . '.xml', ENT_XML1) . '</loc></sitemap>' . "\n";
    }
    return $xml . "</sitemapindex>\n";
}

function seo_sitemap_all(): string
{
    $seen = [];
    $entries = [];
    foreach (['pages', 'articles', 'categories', 'founders', 'companies', 'resources'] as $type) {
        foreach (seo_sitemap_entries($type) as $entry) {
            if (isset($seen[$entry['loc']])) continue;
            $seen[$entry['loc']] = true;
            $entries[] = $entry;
        }
    }
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($entries as $entry) $xml .= '  <url><loc>' . htmlspecialchars($entry['loc'], ENT_XML1) . '</loc><lastmod>' . htmlspecialchars($entry['lastmod'], ENT_XML1) . "</lastmod></url>\n";
    return $xml . "</urlset>\n";
}
