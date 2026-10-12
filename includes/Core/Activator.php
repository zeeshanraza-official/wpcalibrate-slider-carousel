<?php
/**
 * Plugin Activator.
 *
 * @package WPCalibrate\SliderCarousel\Core
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Slider\SliderPostType;

/**
 * Activator class.
 */
class Activator {

	/**
	 * Run activation tasks.
	 */
	public static function activate(): void {
		if ( ! Requirements::check() ) {
			deactivate_plugins( WPCALSC_BASENAME );
			wp_die(
				esc_html__( 'WPCalibrate Slider & Carousel cannot be activated because the server environment does not meet minimum requirements (PHP 8.2+ and WordPress 6.8+).', 'wpcalibrate-slider-carousel' ),
				esc_html__( 'Plugin Activation Error', 'wpcalibrate-slider-carousel' ),
				[ 'back_link' => true ]
			);
		}

		// Register post type during activation.
		SliderPostType::register();

		// Run idempotent migrations.
		Migrator::run();

		// Flush rewrite rules safely.
		flush_rewrite_rules( false );
	}
}
