# Amazon Associates PHP Snippets (v1.5.1)

**Amazon Associates PHP Snippets** is a high-performance WordPress plugin for affiliate marketers, bloggers, and web developers integrating the Amazon Creators API.

Developed by **WhoIsDésir® Media Agency** (Developer Contact: `digitalvurv@gmail.com`).

---

## What's New in Version 1.5.1

* **Manual OAuth 2.0 Bearer Token**: The Access Token (Bearer) field is back on the Credentials tab — paste an existing token to use it directly instead of the client-credentials flow.
* **Token Override Priority**: A manually pasted token now takes priority over the automatic client-credentials flow.
* **Better Error Diagnostics**: API failures now surface Amazon's provided error message (e.g. `message` / `error_description`) instead of a generic HTTP status.

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
