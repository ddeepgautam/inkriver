# InkRiver PHP Port

This folder is a PHP/SQLite port of the InkRiver Node.js project.

## Current Port Status

Completed:

- Same frontend layout and assets copied from InkRiver.
- PHP front controller with clean URL fallback.
- SQLite schema creation from the Node.js schema.
- User registration, login, logout, sessions, cookies, and admin creation.
- Public bootstrap API for stories/settings/user documents.
- Admin platform document save API.
- Reader document sync API.
- Engagement event API.
- Admin user management APIs.
- Security/session/email-verification/TOTP endpoints, including login-time 2FA enforcement.
- Comments, support tickets, moderation, analytics, ads, discounts, payouts, story publishing, creator analytics, privacy export, account deletion, push subscription, and translation-backfill endpoints.
- Razorpay, PayPal, PayU, and Cashfree checkout creation/callback routes.
- Google and Facebook OAuth callback token exchange and account linking.
- OpenAI-backed AI assistant and stored article translation generation.
- Discount redemption tracking, paid gift membership records, paid tips, and subscription activation after payment capture.
- Password reset request/reset flow using signed recovery tokens.
- Admin media upload manager for post images.
- Payment webhook endpoints for Razorpay, PayPal, PayU, and Cashfree.
- Push subscription storage and admin push-send endpoint.
- Server-side recommendation training with persisted user profiles, story scores, transparent factors, feedback controls, and admin rebuild/status APIs.
- SQLite FTS5 full-text search with filters, autocomplete, fallback search, and admin index rebuild.
- Business Network discovery for companies and founders with 12-profile server pagination, linked roles, subscriber-only contact details, profile claiming, suggested corrections, and staff review workflows. The admin profile manager also uses 12-item pages with name search, profile type, industry, and status filters.
- Editorial discovery uses 20-story homepage pagination, while the admin blog manager uses 20-entry pagination with title, slug, author, topic, and status search.
- Story likes are unique per signed-in user and work as a reversible like/unlike toggle backed by the database.
- The writer studio includes the rich editor, featured images, post SEO, interactive polls/surveys/quizzes, and safe post settings without staff-only approval, scheduling, or publishing controls.
- Unknown routes render a dedicated creative 404 page, and the configured Site SEO title is used as the platform name in the application shell and generated web manifest.

Remaining production services to connect with live provider credentials:

- Email delivery requires `EMAIL_API_URL` and `EMAIL_API_KEY`.
- Web-push payload delivery can use `WEB_PUSH_API_URL` and `WEB_PUSH_API_KEY`; direct VAPID ping delivery is also attempted when VAPID keys are configured.
- Payment and OAuth callbacks must be configured in each provider dashboard to point at this domain.

The current PHP copy now covers the main application routes and runs locally. Live payments, OAuth, email, and AI translation require valid production keys on the host.

## Environment Variables

Core:

- `APP_ENV=production`
- `APP_ORIGIN=https://your-domain.com`
- `APP_SECRET=` a unique random secret of at least 32 characters; production refuses to start without it
- `PRIVATE_STORAGE_PATH=/absolute/path/outside/public_html/inkriver-private`
- `DATABASE_PATH=/absolute/path/outside/public_html/inkriver-private/database/inkriver.sqlite`
- `SESSION_DAYS=30`
- `PAYMENT_CURRENCY_RATES_JSON={"INR":1,"USD":0.012}` - server-owned checkout conversion rates

Payments:

- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_WEBHOOK_SECRET`
- `PAYPAL_CLIENT_ID`
- `PAYPAL_CLIENT_SECRET`
- `PAYPAL_ENVIRONMENT=sandbox` or `production`
- `PAYPAL_WEBHOOK_ID`
- `PAYU_MERCHANT_KEY`
- `PAYU_SALT`
- `PAYU_ENVIRONMENT=test` or `production`
- `CASHFREE_CLIENT_ID`
- `CASHFREE_CLIENT_SECRET`
- `CASHFREE_ENVIRONMENT=sandbox` or `production`
- `CASHFREE_WEBHOOK_SECRET`
- Configure a distinct recurring provider plan for every public plan ID, for example `RAZORPAY_PLAN_ID_STARTER`, `PAYPAL_PLAN_ID_ANNUAL`, `CASHFREE_PLAN_ID_PATRON`, or the equivalent encrypted provider-vault keys. There is no cross-plan fallback because it could activate a higher tier against a cheaper gateway plan.

Social login:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `FACEBOOK_APP_ID`
- `FACEBOOK_APP_SECRET`

AI and communication:

- `OPENAI_API_KEY`
- `OPENAI_MODEL=gpt-4.1-mini`
- `EMAIL_API_URL`
- `EMAIL_API_KEY`
- `VAPID_PUBLIC_KEY`
- `VAPID_PRIVATE_KEY`
- `WEB_PUSH_API_URL`
- `WEB_PUSH_API_KEY`

MCP publishing automation:

- `MCP_API_TOKEN` - bearer token required by `/mcp` for ChatGPT, Claude, or other MCP clients.
- `MCP_USER_EMAIL` - optional active admin/writer email used as the publishing identity when the request uses the MCP bearer token.
- `MCP_ACCESS_TOKEN_TTL=604800` - optional OAuth access-token lifetime in seconds for MCP clients.
- `JSON_REQUEST_MAX_BYTES=12582912` - optional JSON request size limit; useful for base64 image uploads.

You can also store these in the encrypted admin API-key vault as:

- `mcp.api_token`
- `mcp.user_email`

Webhook URLs:

- `https://your-domain.com/api/webhooks/razorpay`
- `https://your-domain.com/api/webhooks/paypal`
- `https://your-domain.com/api/webhooks/payu`
- `https://your-domain.com/api/webhooks/cashfree`

Recommendation model:

- Signed-in reader events train `recommendation_profiles` and `recommendation_story_scores`.
- The personalized feed reads `/api/recommendations/feed`.
- Admins can rebuild all active user profiles from `/api/admin/recommendations/rebuild`.
- Add a Hostinger cron job that POSTs to `/api/admin/recommendations/rebuild` with an authenticated admin session, or trigger it manually from Platform Health.

Search index:

- Public search uses `/api/search` and `/api/search/suggest`.
- The index is stored in the SQLite FTS5 table `story_search_index`.
- Admins can rebuild it from Platform Health or by POSTing to `/api/admin/search/rebuild`.

Uploads:

- Public editorial images are stored under `public/uploads/YYYY/MM/` and served as `/uploads/YYYY/MM/...`, with server-generated names and image extensions.
- Support attachments are stored in `PRIVATE_STORAGE_PATH` and are only downloaded through an authenticated authorization check.
- Ensure PHP can write to `public/uploads` and the private storage directory. Private storage should use owner-only filesystem permissions.

## MCP Blog Publishing

InkRiver exposes a Streamable HTTP-style MCP JSON-RPC endpoint at:

```text
https://your-domain.com/mcp
```

ChatGPT and Claude should connect directly to `/mcp`. If they arrive without authorization, InkRiver returns a `401` response with a `WWW-Authenticate` header pointing to the OAuth protected-resource metadata. The client can then discover:

- `/.well-known/oauth-protected-resource/mcp`
- `/.well-known/oauth-authorization-server/mcp`
- `/api/oauth/register`
- `/oauth/authorize`
- `/oauth/token`

Dynamic client registration is published at `/api/oauth/register`; `/oauth/register` remains available as a compatibility alias.

Only administrator accounts can complete the MCP OAuth flow. Non-admin users see a clear access-denied message. `MCP_API_TOKEN` is still available as a server-to-server bearer fallback, but OAuth is the preferred connector path.

Supported MCP methods:

- `initialize`
- `tools/list`
- `tools/call`
- `resources/list`
- `resources/read`
- `ping`

Publishing tools:

- `get_blog_editor_schema` - returns every supported new-blog field, SEO key, status, and interactive block shape.
- `list_categories` - returns available blog categories/topics.
- `list_publications` - returns available publications.
- `list_blogs` - returns existing story ids/slugs/statuses for updates.
- `upload_blog_image` - accepts `sourceUrl` or `dataBase64`, stores the image, and returns a URL.
- `create_or_update_blog` - fills the blog editor fields and saves, schedules, or publishes the story.

Business Network tools:

- `get_company_profile_schema` / `get_founder_profile_schema` - return the dedicated field map for each profile type.
- `list_company_profiles` / `list_founder_profiles` - find existing profile ids before creating, updating, or linking.
- `create_or_update_company_profile` / `create_or_update_founder_profile` - create and manage dedicated business profiles independently from article authors.
- `upload_profile_image` - imports a company logo or founder headshot and returns the profile field to populate.
- `link_founder_to_company` - links existing profiles without replacing their other relationships.
- `get_business_profile_schema` - returns every company/founder field, relationship shape, status, and privacy rule.
- `list_business_profiles` - finds existing company/founder ids before linking or updating.
- `upload_business_profile_image` - imports a logo/headshot from a URL or base64 image and returns the correct profile field to populate.
- `create_or_update_company` - fills all company fields and links founders/key people by id.
- `create_or_update_founder` - fills all founder fields and links companies by id.

Every MCP tool declares both `inputSchema` and `outputSchema`. Tool responses include `structuredContent` matching the declared output schema so ChatGPT and Claude can reason about returned story ids, slugs, media URLs, publication/category options, and publishing status in follow-up calls.

`create_or_update_blog` supports title, slug, excerpt/dek, `contentHtml`, author/byline, publication, category/topic, tags, read time, visual tone, featured image URL, inline images, member-only paywall (`premium` or `memberOnly`), ads/earning toggles, detailed `seo`, and `interactiveBlocks` for polls, surveys, and quizzes. Set `status` to `draft`, `review`, `approved`, `scheduled`, or `published`; include `scheduledAt` for scheduled posts.

Example MCP `tools/call` payload:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "create_or_update_blog",
    "arguments": {
      "title": "How AI Search Changes Content Strategy",
      "slug": "ai-search-content-strategy",
      "dek": "A practical guide to planning content for AI-assisted discovery.",
      "contentHtml": "<h2>Why it matters</h2><p>AI search changes how readers discover useful editorial work.</p>",
      "topic": "AI",
      "tags": ["AI search", "SEO", "content strategy"],
      "premium": true,
      "status": "draft",
      "seo": {
        "focusKeyphrase": "AI search content strategy",
        "additionalKeyphrases": "AI SEO, generative search, content planning",
        "seoTitle": "AI Search Content Strategy Guide",
        "metaDescription": "Learn how to plan content for AI search, generative answers, and modern reader discovery."
      },
      "interactiveBlocks": [
        {
          "type": "poll",
          "question": "Where is AI search affecting your work most?",
          "options": ["Research", "SEO", "Editorial planning", "Analytics"]
        }
      ]
    }
  }
}
```

## Local Run

From `InkRiver-PHP`, explicitly use `public/` as the document root:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

Create an admin:

```bash
php scripts/create-admin.php admin@example.com "StrongPasswordHere" "Site Administrator"
```

## Production Web Root

Deploy the complete repository outside the public web directory and configure the domain's document root to the repository's `public/` directory. For example, deploy to `/home/account/inkriver` and set the domain document root to `/home/account/inkriver/public`. Do not configure the repository root as the document root.

Only `public/index.php`, the application shell, frontend assets, the service worker, manifest, and public image uploads are web-accessible. PHP source, `.env` files, SQLite databases, scripts, tests, schemas, documentation, and Git metadata remain above the web boundary. The supplied web-server examples explicitly deny the repository parent and grant access only to `public/`.

Production startup also verifies the server-reported document root and refuses to initialize the database when it does not resolve to `public/`. On managed hosting that cannot change its fixed document root, create a public-only bridge containing an explicit allowlist of links to `public/`, then set `PUBLIC_DOCUMENT_ROOT` to the bridge's absolute path. Never link the repository root, `app/`, `.env`, `data/`, `scripts/`, `tests/`, or private storage into the bridge.

Apache must allow overrides for `public/.htaccess`; an example virtual host is provided at `deploy/apache-vhost.conf.example`. An equivalent Nginx configuration is provided at `deploy/nginx.conf.example`. Prefer changing a managed host's document-root setting to `public/`; use the explicit public-only bridge above when the provider does not support changing it.

Keep `PRIVATE_STORAGE_PATH`, `DATABASE_PATH`, backups, and secrets outside the repository as an additional isolation layer. If upgrading an existing installation, move existing public media from the old repository-level `uploads/` directory into `public/uploads/` while the application is stopped.

Required PHP extensions:

- PDO
- PDO SQLite
- SQLite3
- OpenSSL

Set environment variables through the hosting control panel or a `.env` file at the repository root, never under `public/`. Do not place credentials directly in tracked PHP or JavaScript files. See `SECURITY.md` before enabling production traffic.
