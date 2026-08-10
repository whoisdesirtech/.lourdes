=== Amazon Associates PHP Snippets ===
Contributors: WhoIsDésir® Media Agency
Donate link: mailto:digitalvurv@gmail.com
Tags: amazon, amazon associates, affiliate, pa-api, php snippets, shortcode, amazon box
Requires at least: 5.6
Tested up to: 6.6
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily display Amazon Associates affiliate product cards, buttons, text links, and PHP snippets on your WordPress site with Amazon PA-API 5.0 integration. Developed by **WhoIsDésir® Media Agency** (Developer Contact: digitalvurv@gmail.com).

== Description ==

**Amazon Associates PHP Snippets** is a lightweight, high-performance WordPress plugin designed for affiliate marketers, bloggers, and web developers who want to integrate Amazon Product Advertising API 5.0 (PA-API 5.0) into their site.

### Key Features

* **Dual Authentication Modes**: Supports both standard **AWS Signature Version 4 (SigV4)** credentials and **OAuth 2.0 Bearer Access Tokens** with automated token refresh.
* **PHP Snippet Helpers**: Global PHP functions (`aa_get_product_data()`, `aa_render_product_box()`, `aa_render_button()`) ready for custom theme templates, `functions.php`, or the Code Snippets plugin.
* **WordPress Shortcodes**:
  * `[amazon_box asin="B08N5WRWNW"]` - Product Showcase Card.
  * `[amazon_button asin="B08N5WRWNW" text="Check Price on Amazon"]` - Amazon CTA Button.
  * `[amazon_link asin="B08N5WRWNW" text="View Product"]` - Inline Affiliate Link.
  * `[amazon_comparison products="ASIN1,ASIN2"]` - Side-by-side product comparison grid layout.
  * `[amazon_grid asins="ASIN1,ASIN2,ASIN3"]` - Multi-product responsive grid layout.
* **Transient Caching**: Automatic WordPress transient caching (configurable 24h default) to prevent Amazon API rate limits and deliver lightning-fast page loading.
* **Graceful Fallback**: Works immediately even before API credentials are approved by automatically generating direct Amazon affiliate links with your Partner Tag, bundled fallback placeholder images, and a visual Fallback Mode badge notice.
* **FTC & Amazon TOS Compliance**: Automatically includes required affiliate earnings disclosures and Prime badges.

== Installation ==

1. Upload the `amazon-associates-snippets` directory to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Settings > Amazon Snippets** in your WordPress admin dashboard.
4. Enter your **Amazon Partner Tag**, **Access Key ID**, **Secret Key**, and select your **Marketplace Locale**.
5. Save settings and start adding shortcodes or PHP code snippets to your posts, pages, or theme templates!

== Frequently Asked Questions ==

= Do I need an Amazon PA-API Access Key to use this plugin? =
No! If you haven't received API access yet, the plugin operates in Fallback Mode using your Partner Tag to generate tagged affiliate links and displaying a fallback placeholder image with a Fallback Mode badge notice. Once you add your PA-API keys, it automatically fetches live product titles, images, list prices, and Prime status.

= How do I use the PHP helper snippets? =
Go to **Settings > Amazon Snippets > PHP Snippet Generator**. Enter an ASIN to instantly generate clean PHP code snippets that you can paste into your theme's `single.php` or inside plugins like Code Snippets.

== Changelog ==

= 1.3.0 - August 10, 2026 =
* Fixed image generation issue when API calls fail or credentials are not yet configured.
* Added bundled placeholder image fallback (`assets/img/placeholder.png`) to ensure visual cards and comparison grids always render images.
* Added visual Fallback Mode badge notice on product cards when operating without active API credentials.
* Integrated automated unit testing suite (PHPUnit + Composer + npm test).

= 1.2.0 - August 9, 2026 =
* Fixed OAuth 2.0 "Fetch Fresh Access Token" — corrected token endpoint scope from `amazon_associates:api` to `creatorsapi::default`.
* Added lead capture gate (first name, last name, email, city) to plugin download button on landing page.
* Lead data saved to PostgreSQL database via `/api/plugin-lead` endpoint with email notification support.

= 1.1.0 - August 9, 2026 =
* Added `[amazon_comparison products="ASIN1,ASIN2"]` shortcode and responsive side-by-side comparison grid CSS rules.
* Updated OAuth 2.0 Access Token authentication support.

= 1.0.0 =
* Initial public release with PA-API 5.0 SigV4 client, transient caching, shortcodes, and admin snippet generator.
