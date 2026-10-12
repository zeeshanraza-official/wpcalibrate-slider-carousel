<?php
/**
 * Custom Post Type Registration for Sliders.
 *
 * @package WPCalibrate\SliderCarousel\Slider
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Slider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SliderPostType class.
 */
class SliderPostType {

	/**
	 * Custom Post Type identifier.
	 */
	public const POST_TYPE = 'wpcal_slider';

	/**
	 * Meta key storing slider JSON configuration.
	 */
	public const META_CONFIG = '_wpcal_slider_config';

	/**
	 * Meta key storing slider schema version.
	 */
	public const META_SCHEMA_VERSION = '_wpcal_slider_schema_version';

	/**
	 * Current slider schema version.
	 */
	public const CURRENT_SCHEMA_VERSION = 1;

	/**
	 * Register the custom post type.
	 */
	public static function register(): void {
		$labels = [
			'name'               => _x( 'Sliders', 'post type general name', 'wpcalibrate-slider-carousel' ),
			'singular_name'      => _x( 'Slider', 'post type singular name', 'wpcalibrate-slider-carousel' ),
			'menu_name'          => _x( 'Sliders', 'admin menu', 'wpcalibrate-slider-carousel' ),
			'name_admin_bar'     => _x( 'Slider', 'add new on admin bar', 'wpcalibrate-slider-carousel' ),
			'add_new'            => _x( 'Add New', 'slider', 'wpcalibrate-slider-carousel' ),
			'add_new_item'       => __( 'Add New Slider', 'wpcalibrate-slider-carousel' ),
			'new_item'           => __( 'New Slider', 'wpcalibrate-slider-carousel' ),
			'edit_item'          => __( 'Edit Slider', 'wpcalibrate-slider-carousel' ),
			'view_item'          => __( 'View Slider', 'wpcalibrate-slider-carousel' ),
			'all_items'          => __( 'All Sliders', 'wpcalibrate-slider-carousel' ),
			'search_items'       => __( 'Search Sliders', 'wpcalibrate-slider-carousel' ),
			'not_found'          => __( 'No sliders found.', 'wpcalibrate-slider-carousel' ),
			'not_found_in_trash' => __( 'No sliders found in Trash.', 'wpcalibrate-slider-carousel' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false, // Managed through our custom admin UI
			'show_in_menu'        => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => [ 'title' ],
			'show_in_rest'        => false,
			'exclude_from_search' => true,
		];

		register_post_type( self::POST_TYPE, $args );
	}
}
