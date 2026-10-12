<?php
/**
 * Plugin Deactivator.
 *
 * @package WPCalibrate\SliderCarousel\Core
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator class.
 */
class Deactivator {

	/**
	 * Run deactivation tasks.
	 */
	public static function deactivate(): void {
		// Clear plugin transients.
		global $wpdb;
		if ( isset( $wpdb->options ) ) {
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpcalsc_%' OR option_name LIKE '_transient_timeout_wpcalsc_%'" );
		}

		// Flush rewrite rules.
		flush_rewrite_rules( false );
	}
}
