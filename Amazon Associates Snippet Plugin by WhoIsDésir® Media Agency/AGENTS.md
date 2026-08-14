# Amazon Associates Snippet Plugin

WordPress plugin that integrates the Amazon Creators API into WordPress. Provides PHP snippet helpers, shortcodes, and product cards with OAuth 2.0 authentication, transient token caching, and FTC compliance. Developed by WhoIsDésir® Media Agency.

## Repo Context

This folder is a sub-project tracked inside the parent git repo at `~/.lourdes` (branch `main`). It is NOT its own git repository. Run git commands from `/Users/jeanfils/Desktop/.lourdes`, never from inside this folder.

## Project Structure

- `amazon-associates-snippets/` — the WordPress plugin itself (what gets zipped and installed)
  - `amazon-associates-snippets.php` — main plugin file (class `AA_Snippets_Plugin`, version constants)
  - `includes/class-creators-oauth-client.php` — OAuth 2.0 Credential ID + Secret token flow
  - `includes/class-creators-api-transport.php`, `class-creators-api-sdk-transport.php`, `class-creators-api-http-transport.php` — Creators API transports
  - `includes/class-amazon-response-normalizer.php` — normalizes API responses
  - `includes/class-amazon-api.php` — product data API layer
  - `includes/class-admin-settings.php` — settings page
  - `includes/class-shortcodes.php` — shortcode handlers
  - `includes/class-snippet-helpers.php` — PHP helper functions
  - `assets/` — CSS/JS
  - `readme.txt` — WordPress.org readme
- `public/` — marketing/landing pages (`amazon-plugin-landing.html`, `amazon-snippets.html`, `amazon-affiliate-program.html`, `audit.html`, `developer.html`)
- `api/` — Node.js service for the marketing site (`audit.js`, `login.js`, `logout.js`, `session.js`) using `private/auth.js`
- `private/` — internal-only: `auth.js` and `audit/` PDFs. Do not commit secrets or distribute.
- `tests/` — PHPUnit tests (bootstrap.php, TestDoubles.php)
- `scripts/bump-version.sh` — version bump + zip build
- `zips/` — release zips (e.g. `amazon-associates-snippets-v1.5.2.zip`)
- `vendor/` — Composer dependencies (PHP SDK, PHPUnit)
- `composer.json` / `package.json` — dependency + release tooling

## Shortcodes

- `[amazon_box asin="ASIN"]` — product showcase card
- `[amazon_button asin="ASIN" text="Buy Now"]` — CTA button
- `[amazon_link asin="ASIN" text="View Product"]` — inline affiliate link
- `[amazon_comparison products="ASIN1,ASIN2"]` — comparison grid
- `[amazon_grid asins="ASIN1,ASIN2,ASIN3"]` — responsive product grid

## Commands

- Install deps: `composer install` && `npm install`
- Run tests: `npm run test` (runs `./vendor/bin/phpunit`)
- Bump version + build zip: `bash scripts/bump-version.sh <new-version>`

## Conventions

- PHP code follows WordPress coding standards (tabs, `AA_SNIPPETS_` prefix for constants, guarded by `ABSPATH`).
- When bumping a version, update in ALL of: main plugin header + `AA_SNIPPETS_VERSION` constant, `readme.txt`, `package.json`, and rebuild the zip in `zips/`.
- OAuth token handling: transient-cached, auto-recover from expired tokens (v1.5.2 behavior). Preserve manual-token guidance messages.
- Never log, print, or commit OAuth credentials, service accounts, or API keys. Keep them in `.env` / `private/` only.
- Auth-gated routes in `api/` must call `auth.isAuthenticated(req)` before returning data.
- Landing page edits go in `public/`; the plugin code stays in `amazon-associates-snippets/`.

## Handoff

Check `SESSION_HANDOFF.md` in this folder for the current work-in-progress state before starting. Update it when you finish a session.
