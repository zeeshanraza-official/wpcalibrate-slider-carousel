<?php
/**
 * System and Environment Requirements Checker.
 *
 * @package WPCalibrate\SliderCarousel\Core
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Requirements class.
 */
class Requirements {

	/**
	 * Minimum PHP version required.
	 */
	public const MIN_PHP_VERSION = '8.2';

	/**
	 * Minimum WordPress version required.
	 */
	public const MIN_WP_VERSION = '6.8';

	/**
	 * Check if the current environment meets all requirements.
	 *
	 * @return bool True if requirements are met, false otherwise.
	 */
	public static function check(): bool {
		return self::is_php_compatible() && self::is_wp_compatible();
	}

	/**
	 * Check PHP version.
	 *
	 * @return bool
	 */
	public static function is_php_compatible(): bool {
		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
	}

	/**
	 * Check WordPress version.
	 *
	 * @return bool
	 */
	public static function is_wp_compatible(): bool {
		global $wp_version;
		return version_compare( (string) $wp_version, self::MIN_WP_VERSION, '>=' );
	}

	/**
	 * Render admin notice if requirements are not met.
	 */
	public static function render_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$errors = [];

		if ( ! self::is_php_compatible() ) {
			$errors[] = sprintf(
				/* translators: 1: Required PHP version, 2: Current PHP version */
				__( 'WPCalibrate Slider & Carousel requires PHP version %1$s or higher. Your server is running PHP %2$s.', 'wpcalibrate-slider-carousel' ),
				self::MIN_PHP_VERSION,
				PHP_VERSION
			);
		}

		if ( ! self::is_wp_compatible() ) {
			global $wp_version;
			$errors[] = sprintf(
				/* translators: 1: Required WordPress version, 2: Current WordPress version */
				__( 'WPCalibrate Slider & Carousel requires WordPress version %1$s or higher. Your site is running WordPress %2$s.', 'wpcalibrate-slider-carousel' ),
				self::MIN_WP_VERSION,
				esc_html( (string) $wp_version )
			);
		}

		if ( empty( $errors ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'WPCalibrate Slider & Carousel cannot run:', 'wpcalibrate-slider-carousel' ) . '</strong></p><ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	}
}
