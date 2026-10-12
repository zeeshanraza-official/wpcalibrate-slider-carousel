<?php
/**
 * Elementor Slider Widget Definition.
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

if ( class_exists( '\Elementor\Widget_Base' ) ) {

	/**
	 * ElementorWidget class.
	 */
	class ElementorWidget extends \Elementor\Widget_Base {

		/**
		 * Widget slug name.
		 *
		 * @return string
		 */
		public function get_name(): string {
			return 'wpcalibrate_slider_widget';
		}

		/**
		 * Widget display title.
		 *
		 * @return string
		 */
		public function get_title(): string {
			return __( 'WPCalibrate Slider & Carousel', 'wpcalibrate-slider-carousel' );
		}

		/**
		 * Widget icon.
		 *
		 * @return string
		 */
		public function get_icon(): string {
			return 'eicon-slider-album';
		}

		/**
		 * Widget categories.
		 *
		 * @return array
		 */
		public function get_categories(): array {
			return [ 'general' ];
		}

		/**
		 * Register controls.
		 */
		protected function register_controls(): void {
			$this->start_controls_section(
				'section_slider',
				[
					'label' => __( 'Slider Settings', 'wpcalibrate-slider-carousel' ),
				]
			);

			$sliders = SliderRepository::get_all( [ 'posts_per_page' => 100 ] );
			$options = [ '0' => __( '-- Select a Slider --', 'wpcalibrate-slider-carousel' ) ];
			foreach ( $sliders as $s ) {
				$options[ (string) $s['id'] ] = $s['title'] . ' (#' . $s['id'] . ')';
			}

			$this->add_control(
				'slider_id',
				[
					'label'   => __( 'Choose Slider', 'wpcalibrate-slider-carousel' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => '0',
					'options' => $options,
				]
			);

			$this->end_controls_section();
		}

		/**
		 * Render widget on frontend.
		 */
		protected function render(): void {
			$settings  = $this->get_settings_for_display();
			$slider_id = absint( $settings['slider_id'] ?? 0 );

			if ( $slider_id <= 0 ) {
				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					echo '<div style="padding: 20px; background: #f6f7f7; border: 1px dashed #c3c4c7; text-align: center;">' . esc_html__( 'Please select a slider in the widget settings panel.', 'wpcalibrate-slider-carousel' ) . '</div>';
				}
				return;
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo Renderer::render( $slider_id, [ 'class' => 'wpcalsc-elementor-render' ] );
		}
	}
}
