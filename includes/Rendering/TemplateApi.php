<?php
/**
 * Public PHP Template API functions.
 *
 * @package WPCalibrate\SliderCarousel\Rendering
 */

declare(strict_types=1);

if ( ! function_exists( 'wpcalibrate_render_slider' ) ) {
	/**
	 * Render or return a WPCalibrate slider by ID.
	 *
	 * Safe to call in theme templates, hooks, and builders.
	 *
	 * @param int   $slider_id The slider post ID.
	 * @param array $args      Optional overrides:
	 *                         - 'echo' (bool): Whether to echo (true, default) or return (false) the HTML.
	 *                         - 'class' (string): Extra container CSS classes.
	 * @return string Rendered HTML if 'echo' is false, empty string if echoed or invalid.
	 */
	function wpcalibrate_render_slider( int $slider_id, array $args = [] ): string {
		$echo = $args['echo'] ?? true;

		if ( $slider_id <= 0 ) {
			return '';
		}

		if ( ! class_exists( '\WPCalibrate\SliderCarousel\Rendering\Renderer' ) ) {
			return '';
		}

		$output = \WPCalibrate\SliderCarousel\Rendering\Renderer::render( $slider_id, $args );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is fully escaped inside Renderer.
			echo $output;
			return '';
		}

		return $output;
	}
}
