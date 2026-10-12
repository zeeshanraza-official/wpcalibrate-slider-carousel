<?php
/**
 * License Manager and Storage Abstraction.
 *
 * @package WPCalibrate\SliderCarousel\Licensing
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\Logger;

/**
 * LicenseManager class.
 */
class LicenseManager {

	/**
	 * Option name for license data.
	 */
	public const OPTION_NAME = 'wpcalsc_license';

	/**
	 * Get the active license provider.
	 *
	 * @return LicenseProviderInterface
	 */
	public static function get_provider(): LicenseProviderInterface {
		$provider = new UnconfiguredProvider();

		/**
		 * Filter the license provider.
		 *
		 * @param LicenseProviderInterface $provider
		 */
		return apply_filters( 'wpcalibrate_slider_carousel_license_provider', $provider );
	}

	/**
	 * Get stored license data.
	 *
	 * @return array
	 */
	public static function get_license_data(): array {
		if ( ! Capabilities::current_user_can_manage() ) {
			return [];
		}

		$data = get_option( self::OPTION_NAME, [] );
		return is_array( $data ) ? $data : [];
	}

	/**
	 * Get the masked license key for display in admin UI.
	 *
	 * @return string
	 */
	public static function get_masked_key(): string {
		$data = self::get_license_data();
		$key  = (string) ( $data['key'] ?? '' );

		if ( empty( $key ) ) {
			return '';
		}

		$len = strlen( $key );
		if ( $len <= 8 ) {
			return '********';
		}

		return substr( $key, 0, 4 ) . str_repeat( '*', $len - 8 ) . substr( $key, -4 );
	}

	/**
	 * Save or update license key.
	 *
	 * @param string $key Raw license key.
	 * @return bool
	 */
	public static function save_key( string $key ): bool {
		if ( ! Capabilities::current_user_can_manage() ) {
			return false;
		}

		$clean_key = sanitize_text_field( trim( $key ) );
		$data      = self::get_license_data();

		$data['key']        = $clean_key;
		$data['updated_at'] = time();

		Logger::log( 'Saved license key (masked)', 'info', [ 'key' => $clean_key ] );

		return update_option( self::OPTION_NAME, $data );
	}

	/**
	 * Remove stored license key.
	 *
	 * @return bool
	 */
	public static function delete_key(): bool {
		if ( ! Capabilities::current_user_can_manage() ) {
			return false;
		}

		return delete_option( self::OPTION_NAME );
	}
}
