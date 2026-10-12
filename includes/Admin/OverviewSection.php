<?php
/**
 * WPCalibrate Slider & Carousel Overview Section.
 *
 * Details the plugin's purpose, how to use it step-by-step, integration methods, and quick actions.
 *
 * @package WPCalibrate\SliderCarousel\Admin
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Support\BrandingHelper;

/**
 * OverviewSection class.
 */
class OverviewSection {

	/**
	 * Render the full Overview tab content for Settings.
	 */
	public static function render(): void {
		$new_slider_url  = admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW );
		$all_sliders_url = admin_url( 'admin.php?page=' . Menu::PAGE_SLIDERS );
		$settings_url    = admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=general' );
		$cache_clear_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=wpcalsc_clear_cache' ),
			'wpcalsc_clear_cache_action',
			'wpcalsc_clear_cache_nonce'
		);
		$logo_url        = BrandingHelper::get_logo_url();
		?>
		<div class="wpcalsc-overview-wrap">
			<!-- Hero Card -->
			<div class="wpcalsc-overview-hero">
				<div class="wpcalsc-overview-hero-content">
					<div class="wpcalsc-overview-badges">
						<span class="wpcalsc-pill-badge wpcalsc-pill-brand">WPCalibrate Suite</span>
						<span class="wpcalsc-pill-badge wpcalsc-pill-version">v<?php echo esc_html( WPCALSC_VERSION ); ?></span>
						<span class="wpcalsc-pill-badge wpcalsc-pill-engine">Swiper.js 14.3.0</span>
						<span class="wpcalsc-pill-badge wpcalsc-pill-wc">WooCommerce Ready</span>
						<span class="wpcalsc-pill-badge wpcalsc-pill-a11y">WCAG 2.1 AA</span>
					</div>
					<h1 class="wpcalsc-overview-hero-title"><?php esc_html_e( 'WPCalibrate Slider & Carousel', 'wpcalibrate-slider-carousel' ); ?></h1>
					<p class="wpcalsc-overview-hero-subtitle">
						<?php esc_html_e( 'Create high-performance, fully accessible, and responsive sliders and product carousels for WordPress and WooCommerce with modern drag-and-drop management, hardware-accelerated transitions, and zero asset bloat.', 'wpcalibrate-slider-carousel' ); ?>
					</p>
					<div class="wpcalsc-overview-hero-actions">
						<a href="<?php echo esc_url( $new_slider_url ); ?>" class="button button-primary button-hero">
							<span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e( 'Create New Slider', 'wpcalibrate-slider-carousel' ); ?>
						</a>
						<a href="<?php echo esc_url( $all_sliders_url ); ?>" class="button button-secondary button-hero">
							<span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'View All Sliders', 'wpcalibrate-slider-carousel' ); ?>
						</a>
						<a href="<?php echo esc_url( $settings_url ); ?>" class="button button-secondary button-hero">
							<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Plugin Settings', 'wpcalibrate-slider-carousel' ); ?>
						</a>
						<a href="<?php echo esc_url( $cache_clear_url ); ?>" class="button button-secondary button-hero">
							<span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Clear Transients Cache', 'wpcalibrate-slider-carousel' ); ?>
						</a>
					</div>
				</div>
				<?php if ( ! empty( $logo_url ) ) : ?>
					<div class="wpcalsc-overview-hero-icon">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="WPCalibrate" />
					</div>
				<?php endif; ?>
			</div>

			<!-- 3 Feature Highlights Grid -->
			<div class="wpcalsc-overview-grid-3">
				<div class="wpcalsc-overview-card wpcalsc-feature-card">
					<div class="wpcalsc-card-icon-wrap" style="background: #e7f1f9; color: #2271b1;">
						<span class="dashicons dashicons-performance"></span>
					</div>
					<h3><?php esc_html_e( 'Performance & Zero Bloat', 'wpcalibrate-slider-carousel' ); ?></h3>
					<p><?php esc_html_e( 'Assets (CSS, JS, and Swiper 14.3.0 bundle) are conditionally loaded only on pages where a slider actually renders. Transient caching accelerates repeat page loads.', 'wpcalibrate-slider-carousel' ); ?></p>
				</div>
				<div class="wpcalsc-feature-card wpcalsc-overview-card">
					<div class="wpcalsc-card-icon-wrap" style="background: #e7f5ea; color: #1e7e34;">
						<span class="dashicons dashicons-cart"></span>
					</div>
					<h3><?php esc_html_e( 'WooCommerce Native & HPOS', 'wpcalibrate-slider-carousel' ); ?></h3>
					<p><?php esc_html_e( 'Showcase Featured, Recent, On Sale, or Best-Selling products in fluid carousels with formatted pricing, badges, and Add to Cart buttons. 100% HPOS certified.', 'wpcalibrate-slider-carousel' ); ?></p>
				</div>
				<div class="wpcalsc-feature-card wpcalsc-overview-card">
					<div class="wpcalsc-card-icon-wrap" style="background: #fff8e5; color: #b26b00;">
						<span class="dashicons dashicons-universal-access"></span>
					</div>
					<h3><?php esc_html_e( 'WCAG 2.1 AA Accessible', 'wpcalibrate-slider-carousel' ); ?></h3>
					<p><?php esc_html_e( 'Semantic HTML output with role="region", aria-roledescription="carousel", keyboard navigation, and automatic prefers-reduced-motion motion reduction.', 'wpcalibrate-slider-carousel' ); ?></p>
				</div>
			</div>

			<!-- How to Use: Step by Step Workflow -->
			<div class="wpcalsc-overview-card wpcalsc-workflow-card">
				<div class="wpcalsc-overview-card-header">
					<span class="dashicons dashicons-welcome-learn-more"></span>
					<h2><?php esc_html_e( 'How to Use: 4-Step Quick Start Guide', 'wpcalibrate-slider-carousel' ); ?></h2>
				</div>
				<div class="wpcalsc-steps-timeline">
					<div class="wpcalsc-step-item">
						<div class="wpcalsc-step-number">1</div>
						<div class="wpcalsc-step-content">
							<h4><?php esc_html_e( 'Create a Slider & Choose Layout Preset', 'wpcalibrate-slider-carousel' ); ?></h4>
							<p><?php esc_html_e( 'Go to "Add New Slider" and pick your visual preset: Standard Single Slider, Multi-Item Carousel, Centered Focus Carousel, Full-Width Hero Slider, or Thumbnail Navigation Slider.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

					<div class="wpcalsc-step-item">
						<div class="wpcalsc-step-number">2</div>
						<div class="wpcalsc-step-content">
							<h4><?php esc_html_e( 'Select Your Content Source', 'wpcalibrate-slider-carousel' ); ?></h4>
							<p><?php esc_html_e( 'Choose between Manual Slides (Images, Videos, or custom HTML), Dynamic WordPress Posts/Pages, or automated WooCommerce Product feeds.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

					<div class="wpcalsc-step-item">
						<div class="wpcalsc-step-number">3</div>
						<div class="wpcalsc-step-content">
							<h4><?php esc_html_e( 'Configure Controls, Motion & Responsive Breakpoints', 'wpcalibrate-slider-carousel' ); ?></h4>
							<p><?php esc_html_e( 'Tailor autoplay speed, pause-on-hover, navigation arrows, pagination dots, and specify exact slides-per-view and gap spacing across Desktop, Tablet, and Mobile devices.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

					<div class="wpcalsc-step-item">
						<div class="wpcalsc-step-number">4</div>
						<div class="wpcalsc-step-content">
							<h4><?php esc_html_e( 'Publish & Embed Anywhere', 'wpcalibrate-slider-carousel' ); ?></h4>
							<p><?php esc_html_e( 'Save as "Published" and embed your slider effortlessly using the Gutenberg Block, Elementor Widget, Shortcode, or PHP Template API.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<!-- Integration Methods & Code Reference -->
			<div class="wpcalsc-overview-card wpcalsc-methods-card">
				<div class="wpcalsc-overview-card-header">
					<span class="dashicons dashicons-editor-code"></span>
					<h2><?php esc_html_e( 'Methods of Embedding & Integration', 'wpcalibrate-slider-carousel' ); ?></h2>
				</div>
				<p class="wpcalsc-card-desc"><?php esc_html_e( 'WPCalibrate Slider & Carousel integrates smoothly across all modern WordPress editing surfaces:', 'wpcalibrate-slider-carousel' ); ?></p>

				<div class="wpcalsc-methods-grid">
					<!-- Method 1: Gutenberg -->
					<div class="wpcalsc-method-box">
						<div class="wpcalsc-method-badge"><?php esc_html_e( 'Block Editor', 'wpcalibrate-slider-carousel' ); ?></div>
						<h3><span class="dashicons dashicons-block-default"></span> <?php esc_html_e( 'Gutenberg Block', 'wpcalibrate-slider-carousel' ); ?></h3>
						<p><?php esc_html_e( 'Search for "WPCalibrate Slider & Carousel" in the Gutenberg block inserter. Choose your slider from the block inspector dropdown for live server-side editor preview.', 'wpcalibrate-slider-carousel' ); ?></p>
						<code>Block: wpcalibrate/slider-carousel</code>
					</div>

					<!-- Method 2: Elementor -->
					<div class="wpcalsc-method-box">
						<div class="wpcalsc-method-badge"><?php esc_html_e( 'Page Builder', 'wpcalibrate-slider-carousel' ); ?></div>
						<h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Elementor Widget', 'wpcalibrate-slider-carousel' ); ?></h3>
						<p><?php esc_html_e( 'Open Elementor and locate the "WPCalibrate Slider" widget under the WPCalibrate category. Drag it into any section or container and pick your slider.', 'wpcalibrate-slider-carousel' ); ?></p>
						<code>Widget: WPCalibrate Slider</code>
					</div>

					<!-- Method 3: Shortcode -->
					<div class="wpcalsc-method-box">
						<div class="wpcalsc-method-badge"><?php esc_html_e( 'Anywhere', 'wpcalibrate-slider-carousel' ); ?></div>
						<h3><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e( 'WordPress Shortcode', 'wpcalibrate-slider-carousel' ); ?></h3>
						<p><?php esc_html_e( 'Paste the shortcode into any post, page, widget, or template. Copy directly from your sliders list table with 1 click.', 'wpcalibrate-slider-carousel' ); ?></p>
						<pre class="wpcalsc-code-box"><code>[wpcalibrate_slider id="123"]</code></pre>
					</div>

					<!-- Method 4: PHP Template API -->
					<div class="wpcalsc-method-box">
						<div class="wpcalsc-method-badge"><?php esc_html_e( 'Developers', 'wpcalibrate-slider-carousel' ); ?></div>
						<h3><span class="dashicons dashicons-media-code"></span> <?php esc_html_e( 'PHP Template API', 'wpcalibrate-slider-carousel' ); ?></h3>
						<p><?php esc_html_e( 'Render sliders directly within your theme template files (e.g., front-page.php or header.php):', 'wpcalibrate-slider-carousel' ); ?></p>
						<pre class="wpcalsc-code-box"><code>&lt;?php wpcalibrate_render_slider( 123 ); ?&gt;</code></pre>
					</div>
				</div>
			</div>

			<!-- Advanced Developer Hooks & Extensibility -->
			<div class="wpcalsc-overview-card">
				<div class="wpcalsc-overview-card-header">
					<span class="dashicons dashicons-admin-tools"></span>
					<h2><?php esc_html_e( 'Developer Filters & Extensibility', 'wpcalibrate-slider-carousel' ); ?></h2>
				</div>
				<table class="widefat striped wpcalsc-hooks-table">
					<thead>
						<tr>
							<th style="width: 35%;"><?php esc_html_e( 'Hook / Filter', 'wpcalibrate-slider-carousel' ); ?></th>
							<th><?php esc_html_e( 'Purpose & Description', 'wpcalibrate-slider-carousel' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>wpcalibrate_slider_query_args</code></td>
							<td><?php esc_html_e( 'Filter arguments passed to WP_Query for dynamic post queries prior to fetching.', 'wpcalibrate-slider-carousel' ); ?></td>
						</tr>
						<tr>
							<td><code>wpcalibrate_slider_render_html</code></td>
							<td><?php esc_html_e( 'Filter the complete compiled HTML markup of any slider before returning to the output buffer.', 'wpcalibrate-slider-carousel' ); ?></td>
						</tr>
						<tr>
							<td><code>wpcalibrate_slider_capabilities</code></td>
							<td><?php esc_html_e( 'Customize required WordPress capability to create, edit, or delete sliders (defaults to manage_options).', 'wpcalibrate-slider-carousel' ); ?></td>
						</tr>
						<tr>
							<td><code>wpcalibrate_slider_swiper_config</code></td>
							<td><?php esc_html_e( 'Filter the JSON configuration array delivered to Swiper.js on the frontend.', 'wpcalibrate-slider-carousel' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a focused in-editor Guide panel for SliderEditor.php.
	 */
	public static function render_editor_guide(): void {
		?>
		<div class="wpcalsc-editor-guide-wrap">
			<div class="wpcalsc-card">
				<h2><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Slider Editor Guide & Instructions', 'wpcalibrate-slider-carousel' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Quick reference for building responsive, accessible sliders and carousels with WPCalibrate Slider & Carousel.', 'wpcalibrate-slider-carousel' ); ?>
				</p>

				<div class="wpcalsc-overview-grid-3" style="margin-top: 18px;">
					<div class="wpcalsc-guide-box">
						<h4><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( '1. Preset & Source', 'wpcalibrate-slider-carousel' ); ?></h4>
						<p><?php esc_html_e( 'Use the "General & Source" tab to choose between Standard single view, Multi-Item carousels, Centered carousels, or Hero sliders. Select whether slides are added manually or fetched dynamically from Posts or WooCommerce.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
					<div class="wpcalsc-guide-box">
						<h4><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( '2. Slide Management', 'wpcalibrate-slider-carousel' ); ?></h4>
						<p><?php esc_html_e( 'Add image, video, or content slides in the "Slides / Content" tab. Drag the handle on any slide card to reorder them instantly. Add headings, text, and optional call to action buttons.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
					<div class="wpcalsc-guide-box">
						<h4><span class="dashicons dashicons-layout"></span> <?php esc_html_e( '3. Responsive & Motion', 'wpcalibrate-slider-carousel' ); ?></h4>
						<p><?php esc_html_e( 'Customize slides per view and gaps per device in "Layout & Responsive". Enable autoplay, loop, and arrow or dot navigation under "Navigation & Autoplay".', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				</div>

				<div style="margin-top: 20px; padding: 16px; background: #f6f7f7; border-radius: 6px; border: 1px solid #dcdcde;">
					<h4 style="margin: 0 0 8px;"><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e( 'Embedding This Slider', 'wpcalibrate-slider-carousel' ); ?></h4>
					<p style="margin: 0 0 10px; font-size: 13px;"><?php esc_html_e( 'Once saved with "Published" status, embed this slider anywhere:', 'wpcalibrate-slider-carousel' ); ?></p>
					<ul style="margin: 0; padding-left: 20px; list-style: disc;">
						<li><strong><?php esc_html_e( 'Gutenberg Block:', 'wpcalibrate-slider-carousel' ); ?></strong> <?php esc_html_e( 'Add "WPCalibrate Slider & Carousel" block and select this slider title.', 'wpcalibrate-slider-carousel' ); ?></li>
						<li><strong><?php esc_html_e( 'Elementor Widget:', 'wpcalibrate-slider-carousel' ); ?></strong> <?php esc_html_e( 'Insert the "WPCalibrate Slider" widget into any section.', 'wpcalibrate-slider-carousel' ); ?></li>
						<li><strong><?php esc_html_e( 'Shortcode:', 'wpcalibrate-slider-carousel' ); ?></strong> <code>[wpcalibrate_slider id="<?php echo isset( $_GET['slider_id'] ) ? absint( $_GET['slider_id'] ) : 'YOUR_ID'; ?>"]</code></li>
						<li><strong><?php esc_html_e( 'PHP Template:', 'wpcalibrate-slider-carousel' ); ?></strong> <code>&lt;?php wpcalibrate_render_slider( <?php echo isset( $_GET['slider_id'] ) ? absint( $_GET['slider_id'] ) : 'YOUR_ID'; ?> ); ?&gt;</code></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}
}
