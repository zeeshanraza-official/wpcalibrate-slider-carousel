<?php
/**
 * Plugin Capabilities and Permissions Helper.
 *
 * @package WPCalibrate\SliderCarousel\Support
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capabilities class.
 */
class Capabilities {

	/**
	 * Default capability required to manage sliders.
	 */
	public const MANAGE_SLIDERS = 'manage_options';

	/**
	 * Get the capability required to manage sliders.
	 *
	 * @return string
	 */
	public static function get_manage_capability(): string {
		/**
		 * Filter the capability required to manage WPCalibrate sliders.
		 *
		 * @param string $capability Default capability.
		 */
		return (string) apply_filters( 'wpcalibrate_slider_carousel_manage_capability', self::MANAGE_SLIDERS );
	}

	/**
	 * Check if the current user can manage sliders.
	 *
	 * @return bool
	 */
	public static function current_user_can_manage(): bool {
		return current_user_can( self::get_manage_capability() );
	}
}
