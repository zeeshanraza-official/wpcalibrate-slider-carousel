<?php
/**
 * Slider Duplication Service.
 *
 * @package WPCalibrate\SliderCarousel\Slider
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Slider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\Logger;

/**
 * SliderDuplicator class.
 */
class SliderDuplicator {

	/**
	 * Duplicate a slider by ID.
	 *
	 * @param int $slider_id ID of slider to duplicate.
	 * @return int|WP_Error New slider ID or WP_Error.
	 */
	public static function duplicate( int $slider_id ): int|WP_Error {
		if ( ! Capabilities::current_user_can_manage() ) {
			return new WP_Error( 'forbidden', __( 'You do not have permission to duplicate sliders.', 'wpcalibrate-slider-carousel' ) );
		}

		$original = SliderRepository::get( $slider_id );
		if ( ! $original ) {
			return new WP_Error( 'not_found', __( 'Original slider not found.', 'wpcalibrate-slider-carousel' ) );
		}

		$new_title  = sprintf(
			/* translators: %s: Original slider title */
			__( '%s - Copy', 'wpcalibrate-slider-carousel' ),
			$original['title']
		);
		$new_config = $original['config'];

		$new_id = SliderRepository::save( 0, $new_title, $new_config, 'draft' );

		if ( ! is_wp_error( $new_id ) ) {
			Logger::log( sprintf( 'Duplicated slider ID %d to new ID %d', $slider_id, $new_id ), 'info' );
		}

		return $new_id;
	}
}
