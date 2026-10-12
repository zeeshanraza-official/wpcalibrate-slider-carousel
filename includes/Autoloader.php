<?php
/**
 * PSR-4 Autoloader for WPCalibrate Slider & Carousel.
 *
 * @package WPCalibrate\SliderCarousel
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader class.
 */
final class Autoloader {

	/**
	 * Namespace prefix.
	 *
	 * @var string
	 */
	private const PREFIX = 'WPCalibrate\\SliderCarousel\\';

	/**
	 * Base directory for class files.
	 *
	 * @var string
	 */
	private static string $base_dir = '';

	/**
	 * Register autoloader callback.
	 *
	 * @param string $base_dir Path to includes/ directory.
	 */
	public static function register( string $base_dir ): void {
		self::$base_dir = rtrim( $base_dir, '/\\' ) . DIRECTORY_SEPARATOR;
		spl_autoload_register( [ self::class, 'load' ] );
	}

	/**
	 * Autoload callback.
	 *
	 * @param string $class Fully qualified class name.
	 */
	public static function load( string $class ): void {
		if ( ! str_starts_with( $class, self::PREFIX ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::PREFIX ) );
		$file           = self::$base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
