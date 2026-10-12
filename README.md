# WPCalibrate Slider & Carousel

[![WordPress Version](https://img.shields.io/badge/WordPress-6.8+-21759b.svg?logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777bb4.svg?logo=php&logoColor=white)](https://php.net)
[![WooCommerce Ready](https://img.shields.io/badge/WooCommerce-11.2+-96588a.svg?logo=woocommerce&logoColor=white)](https://woocommerce.com)
[![Swiper Engine](https://img.shields.io/badge/Swiper.js-14.3.0-6332f6.svg?logo=swiper&logoColor=white)](https://swiperjs.com)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Accessibility](https://img.shields.io/badge/A11y-WCAG%202.1%20AA-success.svg)](https://www.w3.org/WAI/WCAG21/quickref/)

A modern, high-performance visual slider and carousel builder for WordPress and WooCommerce. Built on top of **Swiper.js 14.3.0**, WPCalibrate Slider & Carousel delivers hardware-accelerated transitions, complete WCAG 2.1 AA accessibility, native Gutenberg blocks, Elementor widgets, and zero frontend bloat.

---

## 📌 Table of Contents
- [About the Plugin](#-about-the-plugin)
- [Key Features](#-key-features)
- [How to Use (Quick Start)](#-how-to-use-quick-start)
- [Embedding & Integration Methods](#-embedding--integration-methods)
  - [1. Gutenberg Block](#1-gutenberg-block)
  - [2. Elementor Widget](#2-elementor-widget)
  - [3. Shortcode](#3-shortcode)
  - [4. PHP Template API](#4-php-template-api)
- [Developer Extensibility & Hooks](#-developer-extensibility--hooks)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Changelog](#-changelog)
- [License & Third-Party Credits](#-license--third-party-credits)

---

## 🚀 About the Plugin

**WPCalibrate Slider & Carousel** empowers site creators, designers, and developers to craft stunning, responsive touch carousels and hero banners directly within WordPress without touching JavaScript.

Unlike traditional sliders burdened with monolithic scripts, WPCalibrate adheres to strict performance best practices:
- **Zero Frontend Bloat**: CSS and JavaScript assets (including the bundled Swiper.js engine) are conditionally enqueued **only** on pages where an active slider is rendered.
- **Local Engine**: Swiper.js 14.3.0 is bundled locally under the MIT License — no external CDNs, third-party cookies, or privacy concerns.
- **WooCommerce Certified**: Built-in High-Performance Order Storage (HPOS) compatibility and fluid WooCommerce product carousels with real-time pricing and Add to Cart buttons.
- **Full Accessibility**: Built to satisfy WCAG 2.1 AA standards with `role="region"`, `aria-roledescription="carousel"`, keyboard navigation, and automatic `prefers-reduced-motion` detection.

---

## ✨ Key Features

- **Visual Drag-and-Drop Builder**: Intuitive slide management with sortable ordering, image/video pickers, and real-time heading binding.
- **5 Flexible Layout Presets**:
  - *Standard Single Slider*
  - *Multi-Item Carousel*
  - *Centered Focus Carousel*
  - *Full-Width Hero Slider*
  - *Thumbnail Navigation Slider*
- **Dynamic Content Feeds**:
  - WordPress Posts, Pages, and Custom Post Types with strict parameter allowlisting.
  - WooCommerce Products (Recent, Featured, On Sale, Best Selling, or filtered by Product Category).
- **Manual Slide Types**:
  - High-res Image slides with responsive `srcset` support.
  - Self-hosted MP4/WebM video slides.
  - Rich HTML Content slides with secure `wp_kses_post` sanitization.
- **Granular Motion & Controls**: Autoplay with pause-on-hover, speed control, loop, coverflow/fade/slide effects, navigation arrows, pagination dots, and scrollbars.
- **Responsive Breakpoints**: Independent slides-per-view and gap spacing across Desktop, Tablet, and Mobile.
- **Import & Export Engine**: 1-click JSON schema export and safe import with 1MB security payload cap.
- **Data Preservation**: Clean uninstall handler preserves all sliders by default with optional opt-in data purge.

---

## 🛠 How to Use (Quick Start)

1. **Create a Slider Preset**:
   Navigate to `WPCalibrate` > `Slider & Carousel` > `Add New Slider`. Enter a title and select your layout preset (e.g., Standard Slider, Multi-Item Carousel).
2. **Select Content Source**:
   Choose between **Manual Slides** (add images, videos, or HTML banners), **Dynamic Posts**, or **WooCommerce Products**.
3. **Calibrate Controls & Motion**:
   Set autoplay timing, transition speeds, navigation arrows, pagination dots, and device breakpoints.
4. **Publish & Embed**:
   Set status to **Published**, click **Save Slider**, and embed using the Gutenberg block, Elementor widget, shortcode, or PHP API.

---

## 🔌 Embedding & Integration Methods

### 1. Gutenberg Block
Search for **WPCalibrate Slider & Carousel** in the Gutenberg block inserter. Choose your slider from the inspector dropdown to enjoy live server-side editor preview.
```text
Block identifier: wpcalibrate/slider-carousel
```

### 2. Elementor Widget
In Elementor, open the widget drawer and find the **WPCalibrate Slider** widget under the *WPCalibrate* category. Drag it into any section or column and select your slider.

### 3. Shortcode
Insert the shortcode into any post, page, widget, or custom template:
```html
[wpcalibrate_slider id="123"]
```

### 4. PHP Template API
For custom theme development, render sliders directly within PHP template files:
```php
<?php
if ( function_exists( 'wpcalibrate_render_slider' ) ) {
    echo wpcalibrate_render_slider( 123 );
}
?>
```

---

## 🪝 Developer Extensibility & Hooks

WPCalibrate provides clean filters and actions for developer customization:

| Filter / Hook | Parameters | Description |
|---|---|---|
| `wpcalibrate_slider_query_args` | `$args, $slider_id` | Filter the `WP_Query` arguments for dynamic post feeds before execution. |
| `wpcalibrate_slider_render_html` | `$html, $slider_id, $config` | Filter the compiled HTML markup of any slider before sending to the output buffer. |
| `wpcalibrate_slider_swiper_config` | `$swiper_options, $slider_id` | Modify the JSON configuration array delivered to Swiper.js on the frontend. |
| `wpcalibrate_slider_capabilities` | `$capability` | Customize required capability to manage sliders (defaults to `manage_options`). |

---

## 📋 System Requirements

- **WordPress:** 6.8 or higher (Tested up to 7.1.3)
- **PHP:** 8.2 or higher (Fully tested on PHP 8.3 with strict typing)
- **WooCommerce (Optional):** 10.2+ / 11.2+ (HPOS custom order tables compatible)
- **Modern Browsers:** Chrome, Firefox, Safari, Edge, and iOS/Android mobile browsers

---

## 📦 Installation

### From WordPress Dashboard:
1. Download `wpcalibrate-slider-carousel-1.0.2.zip`.
2. Go to `Plugins` > `Add New` > `Upload Plugin`.
3. Select the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Manual FTP Installation:
1. Extract `wpcalibrate-slider-carousel-1.0.2.zip`.
2. Upload the `wpcalibrate-slider-carousel/` directory to `/wp-content/plugins/`.
3. Activate the plugin through the `Plugins` screen in WordPress.

---

## 📝 Changelog

### [1.0.2] - 2026-10-12
- **Fix:** Resolved Settings tabs navigation link blocking in `admin.js` where `e.preventDefault()` intercepted URL navigation tabs.
- **Fix:** Corrected transient cache clear nonce argument (`wpcalsc_clear_cache_nonce`) in Overview hero action.
- **Feature:** Added direct "Plugin Settings" quick action button in Overview hero banner.
- **Tests:** Added comprehensive automated test coverage for Settings tab routing (20/20 tests passing).

### [1.0.1] - 2026-10-12
- **Fix:** Completely eliminated raw Underscore template logic (`<# if ... #>`) from Slide Editor UI.
- **Feature:** Added dedicated Overview tab in plugin Settings and in-editor guide panel.
- **Feature:** Added support for rich HTML content slides with `wp_kses_post` sanitization.
- **Enhancement:** Redesigned slide card layout with sectioned media previews and dynamic heading labels.

### [1.0.0] - 2026-10-12
- Initial production release.
- Bundled Swiper.js 14.3.0 local engine with zero CDN dependency.
- Gutenberg block and Elementor widget integrations.
- WooCommerce dynamic product carousel integration (HPOS certified).
- Full WCAG 2.1 AA accessibility and motion reduction support.
- JSON Import/Export engine with schema versioning.

---

## 📄 License & Third-Party Credits

- **Plugin License:** [GNU General Public License v2.0 or later (GPL-2.0-or-later)](LICENSE)
- **Swiper.js Engine:** Version 14.3.0 by Vladimir Kharlampidi, licensed under the [MIT License](assets/frontend/vendor/swiper/LICENSE).
- **Author:** [WPCalibrate](https://wpcalibrate.com)
