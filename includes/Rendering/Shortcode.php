<?php
/**
 * Shortcode Handler for [wpcalibrate_slider].
 *
 * @package WPCalibrate\SliderCarousel\Rendering
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Rendering;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 */
class Shortcode {

	/**
	 * Shortcode tag.
	 */
	public const TAG = 'wpcalibrate_slider';

	/**
	 * Register shortcode.
	 */
	public static function init(): void {
		add_shortcode( self::TAG, [ self::class, 'render' ] );
	}

	/**
	 * Render shortcode callback.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string Rendered slider HTML.
	 */
	public static function render( array|string $atts ): string {
		$parsed = shortcode_atts(
			[
				'id'    => 0,
				'class' => '',
			],
			$atts,
			self::TAG
		);

		$slider_id = absint( $parsed['id'] );
		if ( $slider_id <= 0 ) {
			return '';
		}

		return Renderer::render( $slider_id, [ 'class' => sanitize_html_class( (string) $parsed['class'] ) ] );
	}
}
