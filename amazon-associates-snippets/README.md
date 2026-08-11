# Amazon Associates PHP Snippets (v1.4.0)

**Amazon Associates PHP Snippets** is a high-performance WordPress plugin for affiliate marketers, bloggers, and web developers integrating Amazon Product Advertising API 5.0 (PA-API 5.0).

Developed by **WhoIsDésir® Media Agency** (Developer Contact: `digitalvurv@gmail.com`).

---

## What's New in Version 1.4.0

* **AWS Secret Key Base64 Fix**: Resolved sanitization bug where `sanitize_text_field` mutated base64 AWS Secret Access Key characters (`+`, `/`, `=`).
* **Settings Option Group Separation**: Split credentials and display settings into distinct WordPress option groups to prevent cross-tab option erasure.
* **SigV4 Header Refactor**: Updated HTTP request header generator for complete cURL and PA-API 5.0 signature verification compatibility.
* **Automatic Cache Invalidation**: Added automatic transient purging on option updates.

---

## Features

- **Dual Authentication**: AWS SigV4 signatures & OAuth 2.0 Access Token auth.
- **Shortcodes**:
  - `[amazon_box asin="ASIN"]` - Product Showcase Card
  - `[amazon_button asin="ASIN" text="Buy Now"]` - CTA Button
  - `[amazon_link asin="ASIN" text="View Product"]` - Inline Affiliate Link
  - `[amazon_comparison products="ASIN1,ASIN2"]` - Side-by-side product comparison grid
  - `[amazon_grid asins="ASIN1,ASIN2,ASIN3"]` - Responsive product grid
- **PHP Snippet Helpers**: `aa_get_product_data()`, `aa_render_product_box()`, `aa_render_button()`, `aa_render_link()`.
- **Transient Caching**: Configurable transient caching (default 24h) to stay within PA-API rate limits.

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
