<?php
/**
 * Unconfigured License Provider Implementation.
 *
 * @package WPCalibrate\SliderCarousel\Licensing
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Licensing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UnconfiguredProvider class.
 */
class UnconfiguredProvider implements LicenseProviderInterface {

	/**
	 * Check if configured.
	 *
	 * @return bool
	 */
	public function is_configured(): bool {
		return false;
	}

	/**
	 * Activate license key.
	 *
	 * @param string $license_key Key.
	 * @return array
	 */
	public function activate( string $license_key ): array {
		return [
			'success' => false,
			'status'  => 'unconfigured',
			'message' => __( 'Licensing provider is not configured. Contact WPCalibrate support.', 'wpcalibrate-slider-carousel' ),
		];
	}

	/**
	 * Deactivate license key.
	 *
	 * @param string $license_key Key.
	 * @return array
	 */
	public function deactivate( string $license_key ): array {
		return [
			'success' => false,
			'status'  => 'unconfigured',
			'message' => __( 'Licensing provider is not configured.', 'wpcalibrate-slider-carousel' ),
		];
	}

	/**
	 * Check license status.
	 *
	 * @param string $license_key Key.
	 * @return array
	 */
	public function check_status( string $license_key ): array {
		return [
			'success'     => false,
			'status'      => 'unconfigured',
			'message'     => __( 'Licensing provider not configured.', 'wpcalibrate-slider-carousel' ),
			'expiry'      => '',
			'renewal_url' => '',
		];
	}
}
