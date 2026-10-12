<?php
/**
 * Slider Repository for CRUD operations and post meta management.
 *
 * @package WPCalibrate\SliderCarousel\Slider
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Slider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WP_Post;
use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\Logger;

/**
 * SliderRepository class.
 */
class SliderRepository {

	/**
	 * Cache group name.
	 */
	public const CACHE_GROUP = 'wpcalsc_sliders';

	/**
	 * Retrieve a slider by ID.
	 *
	 * @param int $id Post ID.
	 * @return array|null Slider data array or null if not found.
	 */
	public static function get( int $id ): ?array {
		if ( $id <= 0 ) {
			return null;
		}

		$post = get_post( $id );
		if ( ! $post instanceof WP_Post || SliderPostType::POST_TYPE !== $post->post_type ) {
			return null;
		}

		$raw_meta = get_post_meta( $id, SliderPostType::META_CONFIG, true );
		$config   = is_array( $raw_meta ) ? $raw_meta : [];

		// Validate & normalize config against current schema.
		$validated_config = SliderValidator::validate( $config );

		return [
			'id'       => $post->ID,
			'title'    => $post->post_title,
			'status'   => $post->post_status,
			'modified' => $post->post_modified,
			'config'   => $validated_config,
		];
	}

	/**
	 * Save or create a slider.
	 *
	 * @param int    $id     Post ID (0 for new slider).
	 * @param string $title  Slider name/title.
	 * @param array  $config Slider configuration array.
	 * @param string $status Post status ('publish' or 'draft').
	 * @return int|WP_Error Saved post ID or WP_Error on failure.
	 */
	public static function save( int $id, string $title, array $config, string $status = 'publish' ): int|WP_Error {
		if ( ! Capabilities::current_user_can_manage() ) {
			return new WP_Error( 'forbidden', __( 'You do not have permission to manage sliders.', 'wpcalibrate-slider-carousel' ) );
		}

		$clean_title = sanitize_text_field( $title );
		if ( empty( $clean_title ) ) {
			$clean_title = __( 'Untitled Slider', 'wpcalibrate-slider-carousel' );
		}

		$clean_status = in_array( $status, [ 'publish', 'draft' ], true ) ? $status : 'draft';
		$clean_config = SliderValidator::validate( $config );

		if ( $id > 0 ) {
			// Update existing slider.
			$existing = get_post( $id );
			if ( ! $existing instanceof WP_Post || SliderPostType::POST_TYPE !== $existing->post_type ) {
				return new WP_Error( 'not_found', __( 'Slider not found.', 'wpcalibrate-slider-carousel' ) );
			}

			$post_id = wp_update_post(
				[
					'ID'          => $id,
					'post_title'  => $clean_title,
					'post_status' => $clean_status,
				],
				true
			);
		} else {
			// Create new slider.
			$post_id = wp_insert_post(
				[
					'post_type'   => SliderPostType::POST_TYPE,
					'post_title'  => $clean_title,
					'post_status' => $clean_status,
				],
				true
			);
		}

		if ( is_wp_error( $post_id ) ) {
			Logger::log( 'Failed to save slider: ' . $post_id->get_error_message(), 'error' );
			return $post_id;
		}

		// Update configuration metadata.
		update_post_meta( (int) $post_id, SliderPostType::META_CONFIG, $clean_config );
		update_post_meta( (int) $post_id, SliderPostType::META_SCHEMA_VERSION, SliderPostType::CURRENT_SCHEMA_VERSION );

		// Invalidate any transient caches associated with this slider.
		delete_transient( 'wpcalsc_slider_html_' . $post_id );

		Logger::log( 'Slider saved successfully: ID ' . $post_id, 'info' );

		return (int) $post_id;
	}

	/**
	 * Retrieve a list of sliders.
	 *
	 * @param array $args Query arguments.
	 * @return array List of sliders.
	 */
	public static function get_all( array $args = [] ): array {
		$defaults = [
			'post_type'      => SliderPostType::POST_TYPE,
			'post_status'    => [ 'publish', 'draft' ],
			'posts_per_page' => 50,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		$query_args = wp_parse_args( $args, $defaults );
		$posts      = get_posts( $query_args );
		$sliders    = [];

		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$raw_meta = get_post_meta( $post->ID, SliderPostType::META_CONFIG, true );
				$config   = is_array( $raw_meta ) ? $raw_meta : [];

				$sliders[] = [
					'id'       => $post->ID,
					'title'    => $post->post_title,
					'status'   => $post->post_status,
					'modified' => $post->post_modified,
					'type'     => $config['type'] ?? 'standard',
					'source'   => $config['source'] ?? 'manual',
					'config'   => $config,
				];
			}
		}

		return $sliders;
	}

	/**
	 * Delete a slider.
	 *
	 * @param int  $id    Post ID.
	 * @param bool $force Bypass trash.
	 * @return bool
	 */
	public static function delete( int $id, bool $force = false ): bool {
		if ( ! Capabilities::current_user_can_manage() ) {
			return false;
		}

		$post = get_post( $id );
		if ( ! $post instanceof WP_Post || SliderPostType::POST_TYPE !== $post->post_type ) {
			return false;
		}

		delete_transient( 'wpcalsc_slider_html_' . $id );

		$result = wp_delete_post( $id, $force );
		return false !== $result && null !== $result;
	}
}
