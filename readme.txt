=== WPCalibrate Slider & Carousel ===
Contributors: wpcalibrate
Donate link: https://wpcalibrate.com
Tags: slider, carousel, swiper, responsive slider, woocommerce carousel
Requires at least: 6.8
Tested up to: 7.1.3
Requires PHP: 8.2
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create responsive visual sliders and carousels for WordPress and WooCommerce with Swiper.js, Gutenberg blocks, Elementor, and shortcodes.

== Description ==

WPCalibrate Slider & Carousel is a visual slider and carousel builder that allows WordPress site owners and designers to build responsive, accessible sliders without writing JavaScript code.

The plugin bundles the modern Swiper.js engine (v14.3.0) locally without relying on external CDN dependencies.

### Features
* **Visual Slider Builder:** Clean, WordPress-native management interface with drag-and-drop slide ordering.
* **Multiple Presets:** Standard sliders, multi-item carousels, centered carousels, full-width hero sliders, and thumbnail-synchronized sliders.
* **Manual Slides:** High-resolution image slides with responsive srcset, self-hosted HTML5 video slides, and rich content slides with call-to-action buttons.
* **Dynamic Content Carousels:** Query published posts, pages, or public custom post types with safe WP_Query execution.
* **WooCommerce Product Carousels:** Filter products by category, featured status, sale status, or stock status with native WooCommerce price formatting and Add to Cart buttons (active only when WooCommerce is present).
* **Native Gutenberg Block:** Select any slider with live editor preview and server-side rendering.
* **Elementor Widget:** Integrated slider selector widget when Elementor is active.
* **Shortcode & PHP API:** Insert via `[wpcalibrate_slider id="123"]` or `wpcalibrate_render_slider( 123 )`.
* **Responsive Breakpoints:** Configurable desktop, tablet, and mobile views, slides per group, and spacing.
* **Accessibility & RTL:** Native keyboard navigation, semantic ARIA attributes, RTL layout support, and automatic respect for `prefers-reduced-motion`.
* **Lightweight Asset Loading:** Swiper and plugin CSS/JS load only on pages where a slider is actually displayed.

== Installation ==

1. Upload the `wpcalibrate-slider-carousel` folder to your `/wp-content/plugins/` directory, or install the ZIP file via Plugins > Add New > Upload Plugin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to WPCalibrate > Slider & Carousel to create your first slider.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =
No. WooCommerce is completely optional. If WooCommerce is installed and active, the Product Carousel source option is enabled automatically.

= Does this plugin require Elementor? =
No. Elementor is completely optional. You can use Gutenberg blocks, the standard shortcode, or the PHP template function.

= Are third-party CDN scripts loaded? =
No. Swiper.js 14.3.0 is bundled directly within the plugin release, ensuring fast loading and privacy compliance.

= What happens to my data upon uninstallation? =
By default, all your sliders and configurations are preserved. You can optionally enable "Delete plugin data on uninstall" in WPCalibrate > Slider & Carousel > Settings if you wish to wipe plugin data upon deletion.

== Screenshots ==

1. Slider list view with shortcode copy, status indicators, and actions.
2. Visual slide editor with drag-and-drop slide ordering and media selector.
3. Layout and responsive breakpoint controls for desktop, tablet, and mobile.
4. Live interactive preview screen.
5. Gutenberg block selector in the block editor.

== Changelog ==

= 1.0.2 =
* Fix: Resolved Settings tabs navigation link blocking in admin controller script.
* Fix: Corrected transient cache clear nonce argument in Overview hero action.
* Feature: Added direct "Plugin Settings" quick action button in Overview hero.
* Tests: Added comprehensive automated test coverage for Settings tab routing.

= 1.0.1 =
* Fix: Completely eliminated raw Underscore template logic (<# if ... #>) from Slide Editor UI.
* Feature: Added dedicated Overview and quick start guide tab in plugin Settings and Editor.
* Feature: Added support for rich HTML content slides with wp_kses_post sanitization.
* Enhancement: Redesigned slide card layout with sectioned media previews and dynamic heading labels.

= 1.0.0 =
* Initial production release.
* Swiper.js 14.3.0 local engine integration.
* Gutenberg block and Elementor widget integrations.
* WooCommerce product carousel support.
* Import and Export configuration support.
* WPCalibrate shared dashboard menu integration.

== Privacy Statement ==

WPCalibrate Slider & Carousel does not collect personal data, track user browsing behavior, or send telemetry to external servers. It does not set visitor cookies.

== Third-Party Libraries ==

This plugin bundles the following open-source third-party library:
* **Swiper.js** (v14.3.0)
  * Author: Vladimir Kharlampidi
  * Source: https://swiperjs.com
  * License: MIT License (https://opensource.org/licenses/MIT)
  * Bundled location: `assets/frontend/vendor/swiper/`
