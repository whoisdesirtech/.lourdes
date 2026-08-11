# Amazon Associates PHP Snippets (v1.5.0)

**Amazon Associates PHP Snippets** is a high-performance WordPress plugin for affiliate marketers, bloggers, and web developers integrating the Amazon Creators API.

Developed by **WhoIsDésir® Media Agency** (Developer Contact: `digitalvurv@gmail.com`).

---

## What's New in Version 1.5.0

* **Creators API Migration**: Moved from PA-API 5.0 (SigV4) to the Amazon Creators API with OAuth 2.0 client-credentials authentication.
* **v2 + v3 Credential Support**: Works with both Creators API v3.x (LwA token endpoint) and legacy v2.x (Cognito token endpoint) credentials.
* **Hybrid Transport**: Uses the official Creators API PHP SDK when installed via Composer, with an automatic fallback to a built-in WordPress HTTP client — no hard dependencies.
* **lowerCamelCase Requests + Normalizer**: GetItems calls now use Creators API payloads, and a response normalization layer gives shortcodes a stable product structure.
* **Token Caching**: OAuth access tokens are cached in transients with a 60-second pre-expiry refresh buffer.
* **46 automated PHPUnit tests** covering token caching, request construction, response normalization, and fallback behavior.

---

## Features

- **OAuth 2.0 Authentication**: Credential ID + Secret with automatic cached token refresh.
- **Shortcodes**:
  - `[amazon_box asin="ASIN"]` - Product Showcase Card
  - `[amazon_button asin="ASIN" text="Buy Now"]` - CTA Button
  - `[amazon_link asin="ASIN" text="View Product"]` - Inline Affiliate Link
  - `[amazon_comparison products="ASIN1,ASIN2"]` - Side-by-side product comparison grid
  - `[amazon_grid asins="ASIN1,ASIN2,ASIN3"]` - Responsive product grid
- **PHP Snippet Helpers**: `aa_get_product_data()`, `aa_render_product_box()`, `aa_render_button()`, `aa_render_link()`.
- **Transient Caching**: Product data and OAuth tokens are transient-cached (default 24h) to stay within API rate limits.

---

## Running Tests

From the project root:

```bash
composer install
npm install
npm run test
```

---

## Installation & Setup

1. Upload the `amazon-associates-snippets` directory to `/wp-content/plugins/`.
2. Activate via **Plugins** in WordPress admin.
3. Configure settings under **Settings > Amazon Snippets**.
