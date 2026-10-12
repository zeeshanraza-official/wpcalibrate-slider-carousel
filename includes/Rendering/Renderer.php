<?php
/**
 * Central Slider Renderer and Orchestrator.
 *
 * @package WPCalibrate\SliderCarousel\Rendering
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Rendering;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Slider\SliderRepository;
use WPCalibrate\SliderCarousel\Core\Assets;
use WPCalibrate\SliderCarousel\Integrations\WooCommerce;
use WPCalibrate\SliderCarousel\Support\Capabilities;

/**
 * Renderer class.
 */
class Renderer {

	/**
	 * Instance counter to ensure unique container IDs across the page.
	 *
	 * @var int
	 */
	private static int $instance_counter = 0;

	/**
	 * Render slider markup.
	 *
	 * @param int   $slider_id Post ID.
	 * @param array $args      Additional render arguments (e.g. 'class').
	 * @return string Rendered HTML.
	 */
	public static function render( int $slider_id, array $args = [] ): string {
		if ( $slider_id <= 0 ) {
			return '';
		}

		$slider = SliderRepository::get( $slider_id );
		if ( ! $slider ) {
			return '';
		}

		// Security: Draft sliders must not be exposed publicly to unauthorized visitors.
		if ( 'publish' !== $slider['status'] && ! Capabilities::current_user_can_manage() ) {
			return '';
		}

		$config = $slider['config'];

		// Enqueue frontend scripts and styles on demand.
		Assets::enqueue_frontend();

		self::$instance_counter++;
		$instance_id  = sprintf( 'wpcsc-slider-%d-%d', $slider_id, self::$instance_counter );
		$extra_class  = ! empty( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';
		$custom_class = ! empty( $config['style']['custom_class'] ) ? ' ' . esc_attr( $config['style']['custom_class'] ) : '';

		// Resolve Slides based on source.
		$slides_html = '';
		$slide_count = 0;
		$source      = $config['source'] ?? 'manual';

		if ( 'manual' === $source ) {
			$slides = $config['slides'] ?? [];
			$slide_count = count( $slides );
			foreach ( $slides as $index => $slide ) {
				$slides_html .= SlideRenderer::render_manual_slide( (array) $slide, (int) $index, $config );
			}
		} elseif ( 'posts' === $source ) {
			$posts = DynamicQuery::get_posts( (array) ( $config['query'] ?? [] ) );
			$slide_count = count( $posts );
			foreach ( $posts as $post ) {
				$slides_html .= SlideRenderer::render_post_slide( $post, $config );
			}
		} elseif ( 'products' === $source && WooCommerce::is_active() ) {
			$products = WooCommerce::get_products( (array) ( $config['woocommerce'] ?? [] ) );
			$slide_count = count( $products );
			foreach ( $products as $product ) {
				$slides_html .= SlideRenderer::render_product_slide( $product, $config );
			}
		}

		if ( empty( $slides_html ) ) {
			return '';
		}

		// Prepare Swiper Client-Side Configuration.
		$swiper_config = self::prepare_swiper_config( $config, $slide_count );

		// Scoped CSS Custom Properties.
		$style_vars = sprintf(
			'--wpcsc-radius:%dpx;--wpcsc-overlay:%s;--wpcsc-arrow-color:%s;--wpcsc-arrow-bg:%s;--wpcsc-dot-color:%s;--wpcsc-dot-active:%s;--wpcsc-gap:%dpx;',
			absint( $config['style']['border_radius'] ?? 8 ),
			esc_attr( (string) ( $config['style']['overlay_opacity'] ?? 0.35 ) ),
			esc_attr( $config['style']['arrow_color'] ?? '#ffffff' ),
			esc_attr( $config['style']['arrow_bg'] ?? 'rgba(0, 0, 0, 0.45)' ),
			esc_attr( $config['style']['dot_color'] ?? 'rgba(0, 0, 0, 0.25)' ),
			esc_attr( $config['style']['dot_active_color'] ?? '#1d2327' ),
			absint( $config['layout']['gap_desktop'] ?? 20 )
		);

		$is_full_width = ! empty( $config['layout']['full_width'] ) ? ' wpcsc-full-width' : '';
		$aria_label    = esc_attr( $config['accessibility']['aria_label'] ?? $slider['title'] );

		/**
		 * Action before slider render.
		 */
		do_action( 'wpcalibrate_slider_carousel_before_render', $slider_id, $config );

		ob_start();
		?>
		<div class="wpcsc-slider-container wpcsc-preset-<?php echo esc_attr( $config['type'] ); ?><?php echo $is_full_width . $extra_class . $custom_class; ?>"
			id="<?php echo esc_attr( $instance_id ); ?>"
			style="<?php echo esc_attr( $style_vars ); ?>"
			data-swiper-config="<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>"
			role="region"
			aria-roledescription="carousel"
			aria-label="<?php echo $aria_label; ?>"
			dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

			<div class="swiper wpcsc-main-swiper">
				<div class="swiper-wrapper">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $slides_html;
					?>
				</div>

				<?php if ( ! empty( $config['controls']['arrows'] ) ) : ?>
					<button type="button" class="swiper-button-prev wpcsc-arrow wpcsc-arrow-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'wpcalibrate-slider-carousel' ); ?>"></button>
					<button type="button" class="swiper-button-next wpcsc-arrow wpcsc-arrow-next" aria-label="<?php esc_attr_e( 'Next slide', 'wpcalibrate-slider-carousel' ); ?>"></button>
				<?php endif; ?>

				<?php if ( ! empty( $config['controls']['pagination'] ) ) : ?>
					<div class="swiper-pagination wpcsc-pagination"></div>
				<?php endif; ?>

				<?php if ( ! empty( $config['controls']['scrollbar'] ) ) : ?>
					<div class="swiper-scrollbar wpcsc-scrollbar"></div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $config['controls']['thumbnail_nav'] ) && 'manual' === $source ) : ?>
				<div class="swiper wpcsc-thumbs-swiper" data-thumbs-gap="<?php echo esc_attr( (string) $config['controls']['thumbnail_gap'] ); ?>" data-thumbs-spv="<?php echo esc_attr( (string) $config['controls']['thumbnail_slides_per_view'] ); ?>">
					<div class="swiper-wrapper">
						<?php
						foreach ( (array) ( $config['slides'] ?? [] ) as $thumb_slide ) {
							$thumb_img = ! empty( $thumb_slide['image_url'] ) ? $thumb_slide['image_url'] : '';
							?>
							<div class="swiper-slide wpcsc-thumb-slide">
								<?php if ( ! empty( $thumb_img ) ) : ?>
									<img src="<?php echo esc_url( $thumb_img ); ?>" alt="" class="wpcsc-thumb-img" />
								<?php else : ?>
									<div class="wpcsc-thumb-placeholder"><?php esc_html_e( 'Slide', 'wpcalibrate-slider-carousel' ); ?></div>
								<?php endif; ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
		$output = (string) ob_get_clean();

		/**
		 * Filter final slider output.
		 *
		 * @param string $output    HTML output.
		 * @param int    $slider_id Slider ID.
		 * @param array  $config    Slider configuration.
		 */
		$output = (string) apply_filters( 'wpcalibrate_slider_carousel_render_output', $output, $slider_id, $config );

		/**
		 * Action after slider render.
		 */
		do_action( 'wpcalibrate_slider_carousel_after_render', $slider_id, $config );

		return $output;
	}

	/**
	 * Prepare JSON Swiper configuration options.
	 *
	 * @param array $config      Slider configuration.
	 * @param int   $slide_count Number of slides rendered.
	 * @return array
	 */
	private static function prepare_swiper_config( array $config, int $slide_count ): array {
		$layout   = $config['layout'] ?? [];
		$motion   = $config['motion'] ?? [];
		$autoplay = $config['autoplay'] ?? [];
		$controls = $config['controls'] ?? [];

		// Loop protection: Cannot loop if slide count is smaller than slidesPerView
		$can_loop = ! empty( $motion['loop'] ) && $slide_count > (float) ( $layout['slides_per_view_desktop'] ?? 1 );

		$swiper = [
			'speed'          => absint( $motion['speed'] ?? 500 ),
			'loop'           => $can_loop,
			'rewind'         => ! empty( $motion['rewind'] ),
			'effect'         => sanitize_key( $motion['effect'] ?? 'slide' ),
			'centeredSlides' => ! empty( $layout['centered_slides'] ),
			'autoHeight'     => ! empty( $layout['auto_height'] ),
			'simulateTouch'  => ! empty( $motion['touch_swipe'] ),
			'keyboard'       => ! empty( $motion['keyboard'] ) ? [ 'enabled' => true ] : false,
			'mousewheel'     => ! empty( $motion['mousewheel'] ) ? [ 'enabled' => true ] : false,
			'slidesPerView'  => (float) ( $layout['slides_per_view_mobile'] ?? 1 ),
			'spaceBetween'   => absint( $layout['gap_mobile'] ?? 10 ),
			'breakpoints'    => [
				640  => [
					'slidesPerView' => (float) ( $layout['slides_per_view_tablet'] ?? 2 ),
					'spaceBetween'  => absint( $layout['gap_tablet'] ?? 15 ),
					'slidesPerGroup' => absint( $layout['slides_per_group_tablet'] ?? 1 ),
				],
				1024 => [
					'slidesPerView' => (float) ( $layout['slides_per_view_desktop'] ?? 3 ),
					'spaceBetween'  => absint( $layout['gap_desktop'] ?? 20 ),
					'slidesPerGroup' => absint( $layout['slides_per_group_desktop'] ?? 1 ),
				],
			],
			'a11y'           => [
				'enabled' => true,
			],
		];

		// Navigation arrows.
		if ( ! empty( $controls['arrows'] ) ) {
			$swiper['navigation'] = [
				'nextEl' => '.swiper-button-next',
				'prevEl' => '.swiper-button-prev',
			];
		}

		// Pagination.
		if ( ! empty( $controls['pagination'] ) ) {
			$swiper['pagination'] = [
				'el'        => '.swiper-pagination',
				'type'      => sanitize_key( $controls['pagination_type'] ?? 'bullets' ),
				'clickable' => true,
			];
		}

		// Scrollbar.
		if ( ! empty( $controls['scrollbar'] ) ) {
			$swiper['scrollbar'] = [
				'el'        => '.swiper-scrollbar',
				'draggable' => true,
			];
		}

		// Autoplay.
		if ( ! empty( $autoplay['enabled'] ) ) {
			$swiper['autoplay'] = [
				'delay'                => absint( $autoplay['delay'] ?? 4000 ),
				'pauseOnMouseEnter'    => ! empty( $autoplay['pause_on_hover'] ),
				'disableOnInteraction' => ! empty( $autoplay['pause_on_interaction'] ),
				'stopOnLastSlide'      => ! empty( $autoplay['stop_on_last'] ),
			];
		}

		// Reduced motion: user accessibility preference.
		$swiper['reducedMotion'] = ! empty( $motion['reduced_motion'] );

		// Thumbnail nav toggle.
		$swiper['hasThumbs'] = ! empty( $controls['thumbnail_nav'] );

		return $swiper;
	}
}
