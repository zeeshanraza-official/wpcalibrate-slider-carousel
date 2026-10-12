<?php
/**
 * Gutenberg Block Registration and Server-Side Rendering.
 *
 * @package WPCalibrate\SliderCarousel\Blocks
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Rendering\Renderer;
use WPCalibrate\SliderCarousel\Slider\SliderRepository;

/**
 * SliderBlock class.
 */
class SliderBlock {

	/**
	 * Register block.
	 */
	public static function init(): void {
		add_action( 'init', [ self::class, 'register_block' ] );
	}

	/**
	 * Register the block type.
	 */
	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block editor script.
		wp_register_script(
			'wpcalsc-block-editor',
			WPCALSC_URL . 'assets/blocks/index.js',
			[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ],
			WPCALSC_VERSION,
			true
		);

		// Register block editor styles.
		wp_register_style(
			'wpcalsc-block-editor-style',
			WPCALSC_URL . 'assets/blocks/editor.css',
			[],
			WPCALSC_VERSION
		);

		// Localize sliders list for the editor dropdown.
		$all_sliders = SliderRepository::get_all( [ 'posts_per_page' => 100 ] );
		$options     = [];
		foreach ( $all_sliders as $s ) {
			$options[] = [
				'id'    => (int) $s['id'],
				'title' => (string) $s['title'],
			];
		}

		wp_localize_script(
			'wpcalsc-block-editor',
			'wpcalscBlockData',
			[
				'sliders' => $options,
			]
		);

		register_block_type(
			WPCALSC_DIR . 'assets/blocks/block.json',
			[
				'render_callback' => [ self::class, 'render' ],
			]
		);
	}

	/**
	 * Render callback for the dynamic block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered slider HTML.
	 */
	public static function render( array $attributes ): string {
		$slider_id = absint( $attributes['sliderId'] ?? 0 );
		if ( $slider_id <= 0 ) {
			return '';
		}

		return Renderer::render( $slider_id, [ 'class' => 'wpcalsc-block-render' ] );
	}
}
