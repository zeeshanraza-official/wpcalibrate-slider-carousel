<?php
/**
 * Database and Settings Migrator.
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
 * Migrator class.
 */
class Migrator {

	/**
	 * Run migrations idempotently.
	 */
	public static function run(): void {
		$current_version = get_option( 'wpcalsc_version', '0.0.0' );

		if ( version_compare( (string) $current_version, WPCALSC_VERSION, '<' ) ) {
			self::migrate_to_1_0_0();
			update_option( 'wpcalsc_version', WPCALSC_VERSION );
			update_option( 'wpcalsc_db_version', '1.0.0' );
		}
	}

	/**
	 * Migration to 1.0.0.
	 */
	private static function migrate_to_1_0_0(): void {
		// Initialize default settings if not present.
		$existing_settings = get_option( 'wpcalsc_settings', null );
		if ( ! is_array( $existing_settings ) ) {
			$default_settings = [
				'default_speed'        => 500,
				'default_navigation'   => 'both',
				'default_breakpoints'  => [
					'mobile'  => 640,
					'tablet'  => 1024,
					'desktop' => 1280,
				],
				'delete_on_uninstall'  => 0,
				'lazy_load'            => 1,
				'enable_cache'         => 1,
				'cache_duration'       => 3600,
				'debug_logging'        => 0,
			];
			update_option( 'wpcalsc_settings', $default_settings );
		}
	}
}
