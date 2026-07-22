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

Remaining production services to connect with live provider credentials:

- Email delivery requires `EMAIL_API_URL` and `EMAIL_API_KEY`.
- Web-push payload delivery can use `WEB_PUSH_API_URL` and `WEB_PUSH_API_KEY`; direct VAPID ping delivery is also attempted when VAPID keys are configured.
- Payment and OAuth callbacks must be configured in each provider dashboard to point at this domain.

The current PHP copy now covers the main application routes and runs locally. Live payments, OAuth, email, and AI translation require valid production keys on the host.

## Environment Variables

Core:

- `APP_ENV=production`
- `APP_ORIGIN=https://your-domain.com`
- `DATABASE_PATH=/absolute/path/to/inkriver.sqlite`
- `SESSION_DAYS=30`

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

- Uploaded images are stored under `/uploads/YYYY/MM/`.
- Ensure the PHP process can write to the `uploads` directory.

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

From `InkRiver-PHP`:

```bash
php -S 127.0.0.1:8080 index.php
```

Create an admin:

```bash
php scripts/create-admin.php admin@example.com "StrongPasswordHere" "Site Administrator"
```

## Hostinger PHP Upload

Upload this folder's contents to your PHP-enabled hosting directory.

Required PHP extensions:

- PDO
- PDO SQLite
- SQLite3
- OpenSSL

Set environment variables where your Hostinger plan allows it, or edit `app/config.php` carefully for deployment values.
