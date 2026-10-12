<?php
/**
 * WooCommerce Product Carousel Integration.
 *
 * @package WPCalibrate\SliderCarousel\Integrations
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WC_Product;
use WPCalibrate\SliderCarousel\Slider\SliderValidator;

/**
 * WooCommerce class.
 */
class WooCommerce {

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		// HPOS compatibility declaration is placed in main plugin file before_woocommerce_init.
	}

	/**
	 * Check if WooCommerce is active and ready.
	 *
	 * @return bool
	 */
	public static function is_active(): bool {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' );
	}

	/**
	 * Fetch products based on slider query configuration.
	 *
	 * @param array $config WooCommerce slider configuration.
	 * @return WC_Product[] Array of WC_Product objects.
	 */
	public static function get_products( array $config ): array {
		if ( ! self::is_active() ) {
			return [];
		}

		$limit = min( 50, max( 1, absint( $config['limit'] ?? 10 ) ) );

		$orderby = in_array( $config['orderby'] ?? 'date', SliderValidator::ALLOWED_ORDERBY, true )
			? $config['orderby']
			: 'date';

		$order = strtoupper( (string) ( $config['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC';

		$query_args = [
			'status'     => 'publish',
			'limit'      => $limit,
			'orderby'    => $orderby,
			'order'      => $order,
			'visibility' => 'catalog', // Do not expose hidden/private products to unauthorized visitors
		];

		// Stock status.
		if ( 'instock' === ( $config['stock_status'] ?? 'instock' ) ) {
			$query_args['stock_status'] = 'instock';
		}

		// Filter types.
		$filter_type = $config['filter_type'] ?? 'all';
		if ( 'featured' === $filter_type ) {
			$query_args['featured'] = true;
		} elseif ( 'on_sale' === $filter_type ) {
			$query_args['on_sale'] = true;
		}

		// Category filter.
		if ( ! empty( $config['category_ids'] ) ) {
			$query_args['category'] = array_map( 'absint', (array) $config['category_ids'] );
		}

		// Specific include IDs.
		if ( ! empty( $config['include_ids'] ) ) {
			$query_args['include'] = array_map( 'absint', (array) $config['include_ids'] );
		}

		// Specific exclude IDs.
		if ( ! empty( $config['exclude_ids'] ) ) {
			$query_args['exclude'] = array_map( 'absint', (array) $config['exclude_ids'] );
		}

		/**
		 * Filter WooCommerce carousel query args.
		 *
		 * @param array $query_args
		 * @param array $config
		 */
		$query_args = (array) apply_filters( 'wpcalibrate_slider_carousel_wc_query_args', $query_args, $config );

		return wc_get_products( $query_args );
	}
}
