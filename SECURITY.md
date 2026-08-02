# InkRiver Production Security

## Mandatory deployment boundaries

1. Set `APP_ENV=production` and an HTTPS `APP_ORIGIN`.
2. Generate independent random values for `APP_SECRET`, `CRON_SECRET`, and every provider credential. `APP_SECRET` must be at least 32 characters.
3. Set `PRIVATE_STORAGE_PATH` and `DATABASE_PATH` to absolute locations outside the hosted application directory. Production startup rejects unsafe in-project locations.
4. Give the web/PHP account access only to the application files it needs and to its private storage directory. Private directories and database/backup files should be owner-only (`0700` directories, `0600` files where supported).
5. Keep Apache `.htaccess` overrides enabled. The root rules block dotfiles, application source directories, databases, SQL, logs, backups, keys, and executable uploads.
6. Terminate TLS only at a trusted proxy, preserve HTTPS for `APP_ORIGIN`, and redirect all HTTP traffic to HTTPS at the host or proxy.

## Existing installations

Move the existing `data/inkriver.sqlite` and any `storage/backups` files into private storage while the application is stopped. Set `DATABASE_PATH` to the new database location before restarting. After verifying the moved database, remove all legacy copies from the hosted directory and rotate credentials that may previously have been stored in or derivable from it.

## Payment integrity

Membership and gift prices, names, periods, discounts, and conversion rates are resolved by the server. Gateway callbacks must match the stored provider order, provider, amount, currency, local user where applicable, and pending payment state before access is activated. Treat any mismatch as a security event.

## Operational controls

- Put the application behind a managed WAF/CDN with request-size limits, bot controls, and rate limits in addition to the application throttles.
- Restrict administration by identity-aware access or a VPN when possible.
- Back up private storage to an encrypted destination under a separate credential and regularly test restores.
- Centralize application, web-server, authentication, payment, and audit logs. Alert on repeated 429 responses, payment mismatches, permission-check failures, and unexpected administrative actions.
- Patch PHP, extensions, the web server, and third-party services promptly. Run security regression tests before every release.
- Rotate provider credentials and invalidate active sessions after a suspected data exposure.
