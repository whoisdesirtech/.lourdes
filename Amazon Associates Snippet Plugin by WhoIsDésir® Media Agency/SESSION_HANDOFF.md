# Session Handoff — Amazon Associates Snippet Plugin

## Last Updated
2026-08-13

## Version
Code is ahead of the last release (v1.5.2) — multi-provider architecture is implemented but not yet version-bumped/released.

## Git State
- Repo: `~/.lourdes` (this folder is tracked there; NOT its own repo)
- Branch: `main`
- Last commit: `9baddcf` — v1.5.2: Auto-recover from expired Creators API Bearer tokens

## In-Progress Work
- [x] Multi-provider product architecture (provider registry, reference, query, collection, interface)
- [x] `AA_Amazon_Provider` + `AA_Walmart_Provider` + `AA_Keepa_Price_Adapter` (Keepa price history)
- [x] `AA_Click_Tracker` (self-hosted /aa-go/{id} click tracking + table)
- [x] `AA_Freshness_Refresh` (WP-Cron cache refresh)
- [x] `AA_Blocks` (aa/box, aa/grid, aa/comparison blocks + REST search route)
- [x] Refactored `class-snippet-helpers.php`: shared `aa_render_product_data()` + `aa_get_tracked_product_url()` so boxes, buttons, links, and the comparison grid all flow through the provider registry and optional click tracking
- [x] `amazon_comparison_shortcode` now supports multi-provider `items="amazon:B0..,walmart:123"` (provider-prefixed) in addition to legacy `products="ASIN,ASIN"`
- [x] Fixed `AA_Amazon_Provider` to `implements AA_Product_Provider` (was a fatal TypeError in the registry)
- [x] Added WP stubs (`register_activation_hook`/`register_deactivation_hook`) to `tests/bootstrap.php`
- [ ] Version bump + release zip not yet done (keep at v1.5.2 until ready to ship)

## Tests
- `npm run test` (`./vendor/bin/phpunit`) — 56 tests / 173 assertions, all passing.

## Hosting (Firebase Blaze)
- Landing pages (static) + login-gated audit page, served by Firebase Hosting with an `api` Cloud Function (`functions/`) handling `/api/login`, `/api/session`, `/api/logout`, `/api/audit`.
- Handlers live in `api/*.js` and use `private/auth.js` (SHA-256 password gate + HMAC-signed HttpOnly session cookie). `private/` is gitignored; the `predeploy` hook (`scripts/prepare-firebase.js`) stages `api/` + `private/` into `functions/` so `firebase deploy` ships the PDF and auth hash from disk.
- Requires the Blaze (pay-as-you-go) plan for Cloud Functions. Set `AUDIT_ADMIN_PASSWORD_HASH` + `AUDIT_SESSION_SECRET` in Firebase Functions env for production.
- Local test server: `node server.js` → http://localhost:8080/audit.html. Verified full flow (unauth 401 → login cookie → session → 8-page PDF).

## Next Steps
- Create the Firebase project (Blaze) and run: `firebase login` → `firebase use --add` → `firebase deploy --only hosting,functions`.
- Set `AUDIT_ADMIN_PASSWORD_HASH` (SHA-256 of the real admin password) + `AUDIT_SESSION_SECRET` in Firebase console (Functions → env).
- Decide whether to formally bump to v1.6.0 and rebuild the zip (`bash scripts/bump-version.sh 1.6.0`).
- Wire Walmart/Keepa credentials into the settings UI (Providers & Data tab) if those tabs aren't surfaced yet.
- Optional: surface click-tracking stats in admin (currently only the table is written).

## Open Questions
- Should the comparison grid default to provider-prefixed `items` syntax going forward, deprecating bare-ASIN `products`?
- Keepa adapter returns raw points; UI sparkline rendering not yet built.
