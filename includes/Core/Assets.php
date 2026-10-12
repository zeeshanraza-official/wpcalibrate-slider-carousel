<?php
/**
 * Script and Style Asset Management.
 *
 * @package WPCalibrate\SliderCarousel\Core
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class.
 */
class Assets {

	/**
	 * Whether frontend assets have been enqueued for the current request.
	 *
	 * @var bool
	 */
	private static bool $frontend_enqueued = false;

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', [ self::class, 'register_frontend_assets' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'register_admin_assets' ] );
	}

	/**
	 * Register (but do NOT automatically enqueue) frontend assets.
	 */
	public static function register_frontend_assets(): void {
		// Vendor Swiper CSS.
		wp_register_style(
			'wpcalsc-swiper',
			WPCALSC_URL . 'assets/frontend/vendor/swiper/swiper-bundle.min.css',
			[],
			'14.3.0'
		);

		// Core Plugin Frontend CSS.
		wp_register_style(
			'wpcalsc-frontend',
			WPCALSC_URL . 'assets/frontend/css/frontend.css',
			[ 'wpcalsc-swiper' ],
			WPCALSC_VERSION
		);

		// Vendor Swiper JS.
		wp_register_script(
			'wpcalsc-swiper',
			WPCALSC_URL . 'assets/frontend/vendor/swiper/swiper-bundle.min.js',
			[],
			'14.3.0',
			true
		);

		// Plugin Frontend Controller JS.
		wp_register_script(
			'wpcalsc-frontend',
			WPCALSC_URL . 'assets/frontend/js/frontend.js',
			[ 'wpcalsc-swiper' ],
			WPCALSC_VERSION,
			true
		);

		wp_localize_script(
			'wpcalsc-frontend',
			'wpcalscFrontendData',
			[
				'isRtl' => is_rtl(),
			]
		);
	}

	/**
	 * Conditional enqueue of frontend assets when a slider is rendered.
	 */
	public static function enqueue_frontend(): void {
		if ( self::$frontend_enqueued ) {
			return;
		}

		// Ensure registered if called early or within custom loop.
		if ( ! wp_style_is( 'wpcalsc-frontend', 'registered' ) ) {
			self::register_frontend_assets();
		}

		wp_enqueue_style( 'wpcalsc-swiper' );
		wp_enqueue_style( 'wpcalsc-frontend' );
		wp_enqueue_script( 'wpcalsc-swiper' );
		wp_enqueue_script( 'wpcalsc-frontend' );

		self::$frontend_enqueued = true;
	}

	/**
	 * Register and enqueue admin assets.
	 *
	 * @param string $hook Admin screen hook.
	 */
	public static function register_admin_assets( string $hook ): void {
		// Only enqueue on our plugin pages.
		if ( ! str_contains( $hook, 'wpcalibrate' ) ) {
			return;
		}

		// Media library scripts for slide upload.
		wp_enqueue_media();

		// jQuery UI Sortable for drag-and-drop slide reordering.
		wp_enqueue_script( 'jquery-ui-sortable' );

		// Admin CSS.
		wp_enqueue_style(
			'wpcalsc-admin',
			WPCALSC_URL . 'assets/admin/css/admin.css',
			[],
			WPCALSC_VERSION
		);

		// Vendor Swiper for Admin Preview.
		wp_enqueue_style(
			'wpcalsc-swiper',
			WPCALSC_URL . 'assets/frontend/vendor/swiper/swiper-bundle.min.css',
			[],
			'14.3.0'
		);

		wp_enqueue_style(
			'wpcalsc-frontend',
			WPCALSC_URL . 'assets/frontend/css/frontend.css',
			[ 'wpcalsc-swiper' ],
			WPCALSC_VERSION
		);

		wp_enqueue_script(
			'wpcalsc-swiper',
			WPCALSC_URL . 'assets/frontend/vendor/swiper/swiper-bundle.min.js',
			[],
			'14.3.0',
			true
		);

		wp_enqueue_script(
			'wpcalsc-frontend',
			WPCALSC_URL . 'assets/frontend/js/frontend.js',
			[ 'wpcalsc-swiper' ],
			WPCALSC_VERSION,
			true
		);

		// Admin JS.
		wp_enqueue_script(
			'wpcalsc-admin',
			WPCALSC_URL . 'assets/admin/js/admin.js',
			[ 'jquery', 'jquery-ui-sortable', 'wpcalsc-frontend' ],
			WPCALSC_VERSION,
			true
		);

		wp_localize_script(
			'wpcalsc-admin',
			'wpcalscAdmin',
			[
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'wpcalsc_admin_nonce' ),
				'mediaTitle'   => __( 'Select or Upload Slide Media', 'wpcalibrate-slider-carousel' ),
				'mediaButton'  => __( 'Use this media', 'wpcalibrate-slider-carousel' ),
				'confirmDelete'=> __( 'Are you sure you want to delete this slide?', 'wpcalibrate-slider-carousel' ),
				'confirmSlider'=> __( 'Are you sure you want to delete this slider?', 'wpcalibrate-slider-carousel' ),
			]
		);
	}
}
