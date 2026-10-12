<?php
/**
 * Main Plugin Coordinator / Service Container.
 *
 * @package WPCalibrate\SliderCarousel\Core
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Slider\SliderPostType;
use WPCalibrate\SliderCarousel\Admin\Menu;
use WPCalibrate\SliderCarousel\Admin\SliderEditor;
use WPCalibrate\SliderCarousel\Admin\Settings;
use WPCalibrate\SliderCarousel\Rendering\Shortcode;
use WPCalibrate\SliderCarousel\Blocks\SliderBlock;
use WPCalibrate\SliderCarousel\Integrations\WooCommerce;
use WPCalibrate\SliderCarousel\Integrations\Elementor;
use WPCalibrate\SliderCarousel\REST\Routes;

/**
 * Plugin class (Singleton).
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Get instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Initialize plugin components.
	 */
	public function init(): void {
		// Load textdomain.
		load_plugin_textdomain(
			'wpcalibrate-slider-carousel',
			false,
			dirname( WPCALSC_BASENAME ) . '/languages'
		);

		// Register Slider CPT.
		add_action( 'init', [ SliderPostType::class, 'register' ] );

		// Register Assets manager.
		Assets::init();

		// Register Admin Menu.
		Menu::init();

		// Register Admin AJAX / Editor actions.
		SliderEditor::init();

		// Register Settings.
		Settings::init();

		// Register Shortcode.
		Shortcode::init();

		// Register Gutenberg Block.
		SliderBlock::init();

		// Register REST routes.
		Routes::init();

		// Integrations.
		WooCommerce::init();
		Elementor::init();
	}
}
