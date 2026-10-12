<?php
/**
 * WPCalibrate Branding and Menu Icon Helper.
 *
 * @package WPCalibrate\SliderCarousel\Support
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BrandingHelper class.
 */
class BrandingHelper {

	/**
	 * Get the URL of the white icon for dark backgrounds (WordPress admin sidebar).
	 *
	 * @return string
	 */
	public static function get_icon_white_url(): string {
		$path = WPCALSC_DIR . 'branding/icon-white.png';
		if ( file_exists( $path ) ) {
			return WPCALSC_URL . 'branding/icon-white.png';
		}
		return 'dashicons-images-alt2';
	}

	/**
	 * Get the URL of the dark icon for light backgrounds.
	 *
	 * @return string
	 */
	public static function get_icon_dark_url(): string {
		$path = WPCALSC_DIR . 'branding/icon-dark.png';
		if ( file_exists( $path ) ) {
			return WPCALSC_URL . 'branding/icon-dark.png';
		}
		return 'dashicons-images-alt2';
	}

	/**
	 * Get logo URL (for headers and hero cards).
	 *
	 * @return string
	 */
	public static function get_logo_url(): string {
		return self::get_icon_white_url();
	}

	/**
	 * Get the menu icon URL for add_menu_page.
	 *
	 * @return string
	 */
	public static function get_menu_icon_url(): string {
		return self::get_icon_white_url();
	}

	/**
	 * Output inline CSS in admin_head to strictly constrain admin menu branding icon.
	 */
	public static function render_admin_menu_css(): void {
		?>
		<style id="wpcal-branding-menu-css">
			#adminmenu .toplevel_page_wpcalibrate .wp-menu-image img,
			#adminmenu .toplevel_page_wpcalibrate-slider-carousel .wp-menu-image img {
				max-width: 20px;
				max-height: 20px;
				padding-top: 7px;
				object-fit: contain;
			}
		</style>
		<?php
	}
}
