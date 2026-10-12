<?php
/**
 * Internal Logger Abstraction.
 *
 * @package WPCalibrate\SliderCarousel\Support
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class.
 */
class Logger {

	/**
	 * Log a message if debug logging is enabled.
	 *
	 * @param string $message The message to log.
	 * @param string $level   Log level ('info', 'warning', 'error', 'debug').
	 * @param array  $context Optional context.
	 */
	public static function log( string $message, string $level = 'info', array $context = [] ): void {
		$settings = get_option( 'wpcalsc_settings', [] );
		$enabled  = ! empty( $settings['debug_logging'] );

		if ( ! $enabled && 'error' !== $level ) {
			return;
		}

		// Mask any sensitive values in context (license keys, passwords, tokens).
		$masked_context = self::mask_sensitive( $context );
		$context_str    = ! empty( $masked_context ) ? ' | ' . wp_json_encode( $masked_context ) : '';

		$entry = sprintf( '[WPCalibrate Slider][%s] %s%s', strtoupper( $level ), $message, $context_str );

		// WooCommerce logger integration if available.
		if ( function_exists( 'wc_get_logger' ) ) {
			$wc_logger = wc_get_logger();
			$wc_logger->log( $level, $entry, [ 'source' => 'wpcalibrate-slider-carousel' ] );
			return;
		}

		// WordPress standard debug logging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $entry );
		}
	}

	/**
	 * Mask sensitive keys in data.
	 *
	 * @param array $data Raw data array.
	 * @return array Sanitized array.
	 */
	public static function mask_sensitive( array $data ): array {
		$sensitive_keys = [ 'license_key', 'key', 'password', 'token', 'secret', 'auth' ];
		$cleaned        = [];

		foreach ( $data as $k => $v ) {
			if ( is_array( $v ) ) {
				$cleaned[ $k ] = self::mask_sensitive( $v );
			} elseif ( in_array( strtolower( (string) $k ), $sensitive_keys, true ) && is_string( $v ) ) {
				$len = strlen( $v );
				$cleaned[ $k ] = $len > 6 ? substr( $v, 0, 3 ) . '...' . substr( $v, -3 ) : '***';
			} else {
				$cleaned[ $k ] = $v;
			}
		}

		return $cleaned;
	}
}
