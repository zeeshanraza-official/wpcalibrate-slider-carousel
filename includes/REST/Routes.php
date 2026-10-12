<?php
/**
 * REST API Routes for WPCalibrate Slider & Carousel.
 *
 * @package WPCalibrate\SliderCarousel\REST
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;
use WPCalibrate\SliderCarousel\Slider\SliderRepository;
use WPCalibrate\SliderCarousel\Rendering\Renderer;
use WPCalibrate\SliderCarousel\Support\Capabilities;

/**
 * Routes class.
 */
class Routes {

	/**
	 * REST namespace.
	 */
	public const NAMESPACE = 'wpcalibrate/v1';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes(): void {
		// List sliders.
		register_rest_route(
			self::NAMESPACE,
			'/sliders',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ self::class, 'get_sliders' ],
					'permission_callback' => [ self::class, 'check_manage_permission' ],
				],
			]
		);

		// Get single slider.
		register_rest_route(
			self::NAMESPACE,
			'/sliders/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ self::class, 'get_slider' ],
					'permission_callback' => [ self::class, 'check_manage_permission' ],
					'args'                => [
						'id' => [
							'validate_callback' => static fn( $param ) => is_numeric( $param ),
						],
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ self::class, 'save_slider' ],
					'permission_callback' => [ self::class, 'check_manage_permission' ],
				],
			]
		);

		// Preview slider HTML.
		register_rest_route(
			self::NAMESPACE,
			'/sliders/(?P<id>\d+)/preview',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ self::class, 'preview_slider' ],
					'permission_callback' => [ self::class, 'check_manage_permission' ],
				],
			]
		);
	}

	/**
	 * Permission callback verifying manage capability.
	 *
	 * @return bool
	 */
	public static function check_manage_permission(): bool {
		return Capabilities::current_user_can_manage();
	}

	/**
	 * Retrieve all sliders.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public static function get_sliders( WP_REST_Request $request ): WP_REST_Response {
		$sliders = SliderRepository::get_all();
		return new WP_REST_Response( [ 'sliders' => $sliders ], 200 );
	}

	/**
	 * Retrieve a single slider.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_slider( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$slider = SliderRepository::get( $id );

		if ( ! $slider ) {
			return new WP_Error( 'not_found', __( 'Slider not found.', 'wpcalibrate-slider-carousel' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $slider, 200 );
	}

	/**
	 * Save slider via REST.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function save_slider( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id     = (int) $request->get_param( 'id' );
		$body   = $request->get_json_params() ?: [];
		$title  = (string) ( $body['title'] ?? '' );
		$config = (array) ( $body['config'] ?? [] );
		$status = (string) ( $body['status'] ?? 'publish' );

		$result = SliderRepository::save( $id, $title, $config, $status );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( [ 'id' => $result, 'message' => __( 'Slider saved.', 'wpcalibrate-slider-carousel' ) ], 200 );
	}

	/**
	 * Generate preview HTML for a slider.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function preview_slider( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id   = (int) $request->get_param( 'id' );
		$html = Renderer::render( $id, [ 'echo' => false ] );

		return new WP_REST_Response( [ 'html' => $html ], 200 );
	}
}
