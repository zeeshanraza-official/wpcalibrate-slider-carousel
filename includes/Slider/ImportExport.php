<?php
/**
 * Slider Import and Export Service.
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
 * ImportExport class.
 */
class ImportExport {

	/**
	 * Export format identifier.
	 */
	public const FORMAT_ID = 'wpcalibrate_slider_export';

	/**
	 * Export a single slider configuration to array/JSON.
	 *
	 * @param int $slider_id Post ID.
	 * @return array|WP_Error Export payload or WP_Error.
	 */
	public static function export( int $slider_id ): array|WP_Error {
		if ( ! Capabilities::current_user_can_manage() ) {
			return new WP_Error( 'forbidden', __( 'Permission denied to export slider.', 'wpcalibrate-slider-carousel' ) );
		}

		$slider = SliderRepository::get( $slider_id );
		if ( ! $slider ) {
			return new WP_Error( 'not_found', __( 'Slider not found for export.', 'wpcalibrate-slider-carousel' ) );
		}

		return [
			'format'         => self::FORMAT_ID,
			'plugin_version' => WPCALSC_VERSION,
			'schema_version' => SliderPostType::CURRENT_SCHEMA_VERSION,
			'exported_at'    => gmdate( 'Y-m-d H:i:s' ),
			'slider'         => [
				'title'  => $slider['title'],
				'config' => $slider['config'],
			],
		];
	}

	/**
	 * Import a slider configuration from a JSON string.
	 *
	 * @param string $json_data Raw JSON string.
	 * @return int|WP_Error Created slider ID or WP_Error.
	 */
	public static function import( string $json_data ): int|WP_Error {
		if ( ! Capabilities::current_user_can_manage() ) {
			return new WP_Error( 'forbidden', __( 'Permission denied to import slider.', 'wpcalibrate-slider-carousel' ) );
		}

		// Reject oversized payloads (max 1MB).
		if ( strlen( $json_data ) > 1048576 ) {
			return new WP_Error( 'payload_too_large', __( 'Import file is too large. Maximum size is 1MB.', 'wpcalibrate-slider-carousel' ) );
		}

		$data = json_decode( $json_data, true );
		if ( ! is_array( $data ) || ( $data['format'] ?? '' ) !== self::FORMAT_ID ) {
			return new WP_Error( 'invalid_format', __( 'Invalid slider import file format.', 'wpcalibrate-slider-carousel' ) );
		}

		$schema_version = (int) ( $data['schema_version'] ?? 0 );
		if ( $schema_version > SliderPostType::CURRENT_SCHEMA_VERSION ) {
			return new WP_Error( 'unsupported_version', __( 'This import file was created with a newer version of the plugin and is not supported.', 'wpcalibrate-slider-carousel' ) );
		}

		$slider_payload = $data['slider'] ?? [];
		if ( ! is_array( $slider_payload ) ) {
			return new WP_Error( 'malformed_payload', __( 'Malformed slider payload inside import file.', 'wpcalibrate-slider-carousel' ) );
		}

		$title = sanitize_text_field( (string) ( $slider_payload['title'] ?? __( 'Imported Slider', 'wpcalibrate-slider-carousel' ) ) );
		$title = sprintf( '%s (Imported)', $title );

		$raw_config = is_array( $slider_payload['config'] ?? null ) ? $slider_payload['config'] : [];
		$clean_config = SliderValidator::validate( $raw_config );

		$new_id = SliderRepository::save( 0, $title, $clean_config, 'draft' );

		if ( ! is_wp_error( $new_id ) ) {
			Logger::log( 'Slider imported successfully with ID ' . $new_id, 'info' );
		}

		return $new_id;
	}
}
