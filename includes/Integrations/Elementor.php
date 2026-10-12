<?php
/**
 * Safe Elementor Widget Integration.
 *
 * @package WPCalibrate\SliderCarousel\Integrations
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Rendering\Renderer;
use WPCalibrate\SliderCarousel\Slider\SliderRepository;

/**
 * Elementor class.
 */
class Elementor {

	/**
	 * Register hooks if Elementor is present.
	 */
	public static function init(): void {
		add_action( 'elementor/widgets/register', [ self::class, 'register_widgets' ] );
	}

	/**
	 * Register WPCalibrate Slider widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public static function register_widgets( $widgets_manager ): void {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		// Define widget class dynamically.
		require_once WPCALSC_DIR . 'includes/Integrations/ElementorWidget.php';
		$widgets_manager->register( new ElementorWidget() );
	}
}
