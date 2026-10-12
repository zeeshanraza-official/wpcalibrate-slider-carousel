<?php
/**
 * Plugin Name:       WPCalibrate Slider & Carousel
 * Plugin URI:        https://wpcalibrate.com
 * Description:       Create responsive, accessible visual sliders and carousels for WordPress and WooCommerce with Swiper.js, Gutenberg blocks, Elementor widgets, and shortcodes.
 * Version:           1.0.2
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            WPCalibrate
 * Author URI:        https://wpcalibrate.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpcalibrate-slider-carousel
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/zeeshanraza-official/wpcalibrate-slider-carousel
 * Primary Branch:    main
 * Release Asset:     true
 *
 * @package WPCalibrate\SliderCarousel
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WPCALSC_VERSION', '1.0.2' );
define( 'WPCALSC_FILE', __FILE__ );
define( 'WPCALSC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPCALSC_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCALSC_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPCALSC_MIN_PHP', '8.2' );
define( 'WPCALSC_MIN_WP', '6.8' );

// Autoloader.
require_once WPCALSC_DIR . 'includes/Autoloader.php';
Autoloader::register( WPCALSC_DIR . 'includes' );

// Declare WooCommerce HPOS compatibility safely.
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// Activation & Deactivation hooks.
register_activation_hook( __FILE__, [ Core\Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Core\Deactivator::class, 'deactivate' ] );

// Initialize plugin after plugins loaded.
add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! Core\Requirements::check() ) {
			add_action( 'admin_notices', [ Core\Requirements::class, 'render_notice' ] );
			return;
		}

		Core\Plugin::instance()->init();
	}
);

// Load template functions unconditionally so theme developers can safely call them.
require_once WPCALSC_DIR . 'includes/Rendering/TemplateApi.php';
