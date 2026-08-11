# Brand Kit: Amazon Associates PHP Snippets (Elementor × Anthropic Fusion)

This document establishes the strategic foundation and visual design system for the **Amazon Associates PHP Snippets** plugin. It fuses the creative, structured building blocks of **Elementor** with the warm, intellectual, and sophisticated design language of **Anthropic**.

---

## 🧭 The DEFINE Framework

### 1. [ D ] — BRAND NAME
* **Plugin Name**: Amazon Associates PHP Snippets
* **Tagline**: Elegant Amazon Affiliate Integration for WordPress
* **Identity**: Fusing high-tier code snippet efficiency with premium, humanistic styling.

### 2. [ E ] — INDUSTRY
* **Sector**: WordPress Plugin Development & Affiliate Marketing Tools.
* **Industry Standards**: Usually corporate and high-tech. This brand deviates to offer a warmer, scholarly, and publisher-centric aesthetic.

### 3. [ F ] — PRIMARY TARGET AUDIENCE
* **Audience**: Professional bloggers, content publishers, web developers, and affiliate marketers using WordPress.
* **Key Needs**: Seamless layouts, clean code, compliance security (FTC/Amazon TOS), and beautiful, high-converting product showcases.

### 4. [ I ] — MAIN COMPETITORS
* **Direct Competitors**: AAWP (Amazon Affiliate WordPress Plugin), Lassie / Lasso, AmaLinks Pro.
* **Differentiation**: Light-weight PHP helper snippets, oauth Bearer tokens with automatic refreshes, and an organic, Editorial-level visual layout out-of-the-box.

---

## 🔍 Visual Identity Specs

### 5. [ N ] — COLOR PALETTE
We merge Elementor's bold, creative crimson/magenta with Anthropic's sophisticated warm tones:

*   **Anthropic Charcoal** (`#191919`) — Used for primary dark surfaces, text headers, and terminal backgrounds.
*   **Anthropic Warm Sand** (`#F9F6F0`) — Primary light background; soft, organic, high-end editorial feel.
*   **Elementor Magenta** (`#E52C5E`) — Core call-to-action color, hover states, and brand accent.
*   **Anthropic Terracotta** (`#D17B58`) — Secondary accent, decorative borders, and highlight badges.
*   **Slate Surface** (`#334155`) — Soft border lines and secondary button states.

### 6. [ E ] — TYPOGRAPHY FEEL
*   **Headings**: Google Fonts **Playfair Display** (or Newsreader). Elegant, high-character serif that feels literary and professional (Anthropic-inspired).
*   **Body Copy**: Google Fonts **DM Sans** (or Inter). Modern, clean geometric sans-serif for clear readability and structure (Elementor-inspired).
*   **Monospace/Code**: Google Fonts **JetBrains Mono**. High-readability code font.

---

## 🛠️ CSS Styling Guide

Include these design tokens in your global stylesheets:

```css
:root {
  /* Brand Theme: Elementor x Anthropic Fusion */
  --bg-primary: #F9F6F0;       /* Anthropic Warm Sand */
  --bg-dark: #191919;          /* Anthropic Charcoal */
  --bg-surface: #ffffff;
  --bg-card-dark: #222222;
  
  /* Brand Accents */
  --color-magenta: #E52C5E;     /* Elementor Rose */
  --color-terracotta: #D17B58;  /* Anthropic Clay */
  --border-color: rgba(25, 25, 25, 0.08);
  
  /* Text */
  --text-dark: #191919;
  --text-light: #F9F6F0;
  --text-muted: #6b7280;
  
  /* Typography */
  --font-heading: 'Playfair Display', serif;
  --font-body: 'DM Sans', sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  --radius-lg: 16px;
  --radius-md: 8px;
}
```
