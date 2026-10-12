<?php
/**
 * WPCalibrate Admin Menu and Submenu Management.
 *
 * @package WPCalibrate\SliderCarousel\Admin
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Support\BrandingHelper;
use WPCalibrate\SliderCarousel\Support\Capabilities;

/**
 * Menu class.
 */
class Menu {

	/**
	 * Shared WPCalibrate parent menu slug.
	 */
	public const PARENT_SLUG = 'wpcalibrate';

	/**
	 * Primary submenu slug (Sliders list).
	 */
	public const PAGE_SLIDERS = 'wpcalibrate-slider-carousel';

	/**
	 * Add new slider page slug.
	 */
	public const PAGE_ADD_NEW = 'wpcalibrate-slider-carousel-add';

	/**
	 * Settings page slug.
	 */
	public const PAGE_SETTINGS = 'wpcalibrate-slider-carousel-settings';

	/**
	 * License page slug.
	 */
	public const PAGE_LICENSE = 'wpcalibrate-slider-carousel-license';

	/**
	 * Support page slug.
	 */
	public const PAGE_SUPPORT = 'wpcalibrate-slider-carousel-support';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ self::class, 'register_menus' ], 11 );
		add_action( 'admin_head', [ BrandingHelper::class, 'render_admin_menu_css' ] );
	}

	/**
	 * Register menu and submenus.
	 */
	public static function register_menus(): void {
		$capability = Capabilities::get_manage_capability();
		global $admin_page_hooks;

		// 1. Shared top-level WPCalibrate menu item.
		if ( ! isset( $admin_page_hooks[ self::PARENT_SLUG ] ) ) {
			add_menu_page(
				__( 'WPCalibrate', 'wpcalibrate-slider-carousel' ),
				__( 'WPCalibrate', 'wpcalibrate-slider-carousel' ),
				$capability,
				self::PARENT_SLUG,
				[ SliderList::class, 'render' ],
				BrandingHelper::get_menu_icon_url(),
				58
			);
		}

		// 2. Submenus specific to this plugin.
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Sliders & Carousels', 'wpcalibrate-slider-carousel' ),
			__( 'Slider & Carousel', 'wpcalibrate-slider-carousel' ),
			$capability,
			self::PAGE_SLIDERS,
			[ SliderList::class, 'render' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Add New Slider', 'wpcalibrate-slider-carousel' ),
			__( 'Add New Slider', 'wpcalibrate-slider-carousel' ),
			$capability,
			self::PAGE_ADD_NEW,
			[ SliderEditor::class, 'render_new' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Slider & Carousel Settings', 'wpcalibrate-slider-carousel' ),
			__( 'Slider Settings', 'wpcalibrate-slider-carousel' ),
			$capability,
			self::PAGE_SETTINGS,
			[ Settings::class, 'render' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Slider & Carousel License', 'wpcalibrate-slider-carousel' ),
			__( 'Slider License', 'wpcalibrate-slider-carousel' ),
			$capability,
			self::PAGE_LICENSE,
			[ Settings::class, 'render_license' ]
		);

		add_submenu_page(
			self::PARENT_SLUG,
			__( 'WPCalibrate Support', 'wpcalibrate-slider-carousel' ),
			__( 'Slider Support', 'wpcalibrate-slider-carousel' ),
			$capability,
			self::PAGE_SUPPORT,
			[ Settings::class, 'render_support' ]
		);
	}
}
