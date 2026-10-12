<?php
/**
 * License Provider Interface.
 *
 * @package WPCalibrate\SliderCarousel\Licensing
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LicenseProviderInterface.
 */
interface LicenseProviderInterface {

	/**
	 * Check if a real licensing provider is configured.
	 *
	 * @return bool
	 */
	public function is_configured(): bool;

	/**
	 * Activate a license key.
	 *
	 * @param string $license_key License key.
	 * @return array Result array with status, message, expiry, renewal_url.
	 */
	public function activate( string $license_key ): array;

	/**
	 * Deactivate the stored license key.
	 *
	 * @param string $license_key License key.
	 * @return array Result array.
	 */
	public function deactivate( string $license_key ): array;

	/**
	 * Check current license status.
	 *
	 * @param string $license_key Stored license key.
	 * @return array Status array.
	 */
	public function check_status( string $license_key ): array;
}
