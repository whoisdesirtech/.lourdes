# Amazon Associates PHP Snippets (v1.3.0)

**Amazon Associates PHP Snippets** is a high-performance WordPress plugin for affiliate marketers, bloggers, and web developers integrating Amazon Product Advertising API 5.0 (PA-API 5.0).

Developed by **WhoIsDésir® Media Agency** (Developer Contact: `digitalvurv@gmail.com`).

---

## What's New in Version 1.3.0

* **Image Generation Fix**: Resolved issue where missing API credentials or failed API requests caused product cards/grids to render without images.
* **Bundled Fallback Placeholder Image**: Included `assets/img/placeholder.png` so cards and comparison grids always render complete visual cards in Fallback Mode.
* **Visual Fallback Notice**: Product cards operating in Fallback Mode display a subtle `Fallback Mode` badge notice with tooltip explanations.
* **Automated Testing Suite**: Integrated PHPUnit, Composer, and `npm test` configuration for continuous integration testing.

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
