<?php
/**
 * Safe Dynamic WordPress Content Query Builder.
 *
 * @package WPCalibrate\SliderCarousel\Rendering
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Rendering;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Query;
use WPCalibrate\SliderCarousel\Slider\SliderValidator;

/**
 * DynamicQuery class.
 */
class DynamicQuery {

	/**
	 * Execute a safe query for WordPress posts/pages/CPTs.
	 *
	 * @param array $query_config Validated query configuration.
	 * @return array Array of WP_Post objects.
	 */
	public static function get_posts( array $query_config ): array {
		$post_type = sanitize_key( $query_config['post_type'] ?? 'post' );

		// Validate post type is public.
		$public_types = get_post_types( [ 'public' => true ] );
		if ( ! in_array( $post_type, $public_types, true ) ) {
			$post_type = 'post';
		}

		$limit = min( 50, max( 1, absint( $query_config['limit'] ?? 10 ) ) );

		$orderby = in_array( $query_config['orderby'] ?? 'date', SliderValidator::ALLOWED_ORDERBY, true )
			? $query_config['orderby']
			: 'date';

		$order = strtoupper( (string) ( $query_config['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

		$args = [
			'post_type'              => $post_type,
			'post_status'            => 'publish', // Strictly published posts for public visitors
			'posts_per_page'         => $limit,
			'orderby'                => $orderby,
			'order'                  => $order,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true, // Performance optimization: bypass SQL_CALC_FOUND_ROWS
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		];

		// Include IDs.
		if ( ! empty( $query_config['include_ids'] ) ) {
			$args['post__in'] = array_map( 'absint', (array) $query_config['include_ids'] );
		}

		// Exclude IDs.
		if ( ! empty( $query_config['exclude_ids'] ) ) {
			$args['post__not_in'] = array_map( 'absint', (array) $query_config['exclude_ids'] );
		}

		// Taxonomy filter.
		if ( ! empty( $query_config['terms'] ) && ! empty( $query_config['taxonomy'] ) ) {
			$tax = sanitize_key( $query_config['taxonomy'] );
			if ( taxonomy_exists( $tax ) ) {
				$args['tax_query'] = [
					[
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => array_map( 'absint', (array) $query_config['terms'] ),
					],
				];
			}
		}

		/**
		 * Filter the dynamic query args before WP_Query.
		 *
		 * @param array $args         WP_Query args.
		 * @param array $query_config Raw query configuration.
		 */
		$args = (array) apply_filters( 'wpcalibrate_slider_carousel_dynamic_query_args', $args, $query_config );

		$query = new WP_Query( $args );

		return $query->posts;
	}
}
