<?php
/**
 * Individual Slide Content Renderer.
 *
 * @package WPCalibrate\SliderCarousel\Rendering
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Rendering;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Post;
use WC_Product;
use WPCalibrate\SliderCarousel\Support\Sanitizer;

/**
 * SlideRenderer class.
 */
class SlideRenderer {

	/**
	 * Render a manual slide.
	 *
	 * @param array $slide  Slide data.
	 * @param int   $index  Slide index.
	 * @param array $config Entire slider configuration.
	 * @return string Slide HTML.
	 */
	public static function render_manual_slide( array $slide, int $index, array $config ): string {
		$type             = $slide['type'] ?? 'image';
		$heading          = Sanitizer::sanitize_text( $slide['heading'] ?? '' );
		$description      = Sanitizer::sanitize_slide_html( $slide['description'] ?? '' );
		$button_text      = Sanitizer::sanitize_text( $slide['button_text'] ?? '' );
		$button_url       = Sanitizer::sanitize_url( $slide['button_url'] ?? '' );
		$link_target      = ( $slide['link_target'] ?? '_self' ) === '_blank' ? '_blank' : '_self';
		$rel_attr         = '_blank' === $link_target ? 'rel="noopener noreferrer"' : '';
		$content_position = sanitize_html_class( $slide['content_position'] ?? 'center' );
		$text_align       = sanitize_html_class( $slide['text_align'] ?? 'left' );

		ob_start();
		?>
		<div class="swiper-slide wpcsc-slide wpcsc-slide-<?php echo esc_attr( $type ); ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d of %d', 'wpcalibrate-slider-carousel' ), $index + 1, count( $config['slides'] ?? [] ) ) ); ?>">
			<div class="wpcsc-slide-inner">
				<?php if ( 'image' === $type ) : ?>
					<div class="wpcsc-slide-media">
						<?php
						$image_id = absint( $slide['image_id'] ?? 0 );
						if ( $image_id > 0 ) {
							echo wp_get_attachment_image(
								$image_id,
								'full',
								false,
								[
									'class'   => 'wpcsc-slide-img',
									'alt'     => esc_attr( $slide['alt'] ?? $heading ),
									'loading' => ! empty( $config['style']['lazy_load'] ) && $index > 0 ? 'lazy' : 'eager',
								]
							);
						} elseif ( ! empty( $slide['image_url'] ) ) {
							?>
							<img src="<?php echo esc_url( $slide['image_url'] ); ?>" class="wpcsc-slide-img" alt="<?php echo esc_attr( $slide['alt'] ?? $heading ); ?>" />
							<?php
						}
						?>
						<div class="wpcsc-slide-overlay"></div>
					</div>
				<?php elseif ( 'video' === $type ) : ?>
					<div class="wpcsc-slide-media wpcsc-video-wrap">
						<?php if ( ! empty( $slide['video_url'] ) ) : ?>
							<video class="wpcsc-slide-video"
								<?php echo ! empty( $slide['video_loop'] ) ? 'loop' : ''; ?>
								<?php echo ! empty( $slide['video_muted'] ) ? 'muted' : ''; ?>
								<?php echo ! empty( $slide['video_controls'] ) ? 'controls' : ''; ?>
								playsinline autoplay poster="<?php echo esc_url( $slide['poster_url'] ?? '' ); ?>">
								<source src="<?php echo esc_url( $slide['video_url'] ); ?>" type="video/mp4">
								<?php esc_html_e( 'Your browser does not support the video tag.', 'wpcalibrate-slider-carousel' ); ?>
							</video>
						<?php endif; ?>
						<div class="wpcsc-slide-overlay"></div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $heading ) || ! empty( $description ) || ! empty( $button_text ) ) : ?>
					<div class="wpcsc-slide-content wpcsc-pos-<?php echo esc_attr( $content_position ); ?> wpcsc-align-<?php echo esc_attr( $text_align ); ?>">
						<div class="wpcsc-content-box">
							<?php if ( ! empty( $heading ) ) : ?>
								<h2 class="wpcsc-slide-heading"><?php echo esc_html( $heading ); ?></h2>
							<?php endif; ?>

							<?php if ( ! empty( $description ) ) : ?>
								<div class="wpcsc-slide-desc"><?php echo wp_kses( $description, Sanitizer::get_allowed_html() ); ?></div>
							<?php endif; ?>

							<?php if ( ! empty( $button_text ) && ! empty( $button_url ) ) : ?>
								<div class="wpcsc-slide-btn-wrap">
									<a href="<?php echo esc_url( $button_url ); ?>" class="wpcsc-btn" target="<?php echo esc_attr( $link_target ); ?>" <?php echo $rel_attr; ?>>
										<?php echo esc_html( $button_text ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a dynamic WordPress post slide.
	 *
	 * @param WP_Post $post   WordPress post object.
	 * @param array   $config Entire slider configuration.
	 * @return string Slide HTML.
	 */
	public static function render_post_slide( WP_Post $post, array $config ): string {
		$query_opts = $config['query'] ?? [];
		$post_link  = esc_url( get_permalink( $post->ID ) );
		$post_title = esc_html( get_the_title( $post->ID ) );

		ob_start();
		?>
		<div class="swiper-slide wpcsc-slide wpcsc-slide-post" role="group" aria-roledescription="slide">
			<div class="wpcsc-slide-inner wpcsc-post-card">
				<?php if ( ! empty( $query_opts['show_image'] ) && has_post_thumbnail( $post->ID ) ) : ?>
					<div class="wpcsc-post-media">
						<a href="<?php echo $post_link; ?>" tabindex="-1" aria-hidden="true">
							<?php echo get_the_post_thumbnail( $post->ID, 'large', [ 'class' => 'wpcsc-post-img' ] ); ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="wpcsc-post-body">
					<?php if ( ! empty( $query_opts['show_date'] ) || ! empty( $query_opts['show_author'] ) ) : ?>
						<div class="wpcsc-post-meta">
							<?php if ( ! empty( $query_opts['show_date'] ) ) : ?>
								<span class="wpcsc-post-date"><?php echo esc_html( get_the_date( '', $post->ID ) ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $query_opts['show_author'] ) ) : ?>
								<span class="wpcsc-post-author"><?php echo esc_html( get_the_author_meta( 'display_name', (int) $post->post_author ) ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $query_opts['show_title'] ) ) : ?>
						<h3 class="wpcsc-post-title">
							<a href="<?php echo $post_link; ?>"><?php echo $post_title; ?></a>
						</h3>
					<?php endif; ?>

					<?php if ( ! empty( $query_opts['show_excerpt'] ) ) : ?>
						<div class="wpcsc-post-excerpt">
							<?php echo esc_html( wp_trim_words( get_the_excerpt( $post->ID ), 20 ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $query_opts['show_cta'] ) ) : ?>
						<div class="wpcsc-post-cta">
							<a href="<?php echo $post_link; ?>" class="wpcsc-btn wpcsc-btn-secondary">
								<?php echo esc_html( $query_opts['cta_text'] ?? __( 'Read More', 'wpcalibrate-slider-carousel' ) ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render a WooCommerce product slide.
	 *
	 * @param WC_Product $product WooCommerce product object.
	 * @param array      $config  Entire slider configuration.
	 * @return string Slide HTML.
	 */
	public static function render_product_slide( WC_Product $product, array $config ): string {
		$wc_opts      = $config['woocommerce'] ?? [];
		$product_link = esc_url( $product->get_permalink() );
		$product_id   = $product->get_id();

		ob_start();
		?>
		<div class="swiper-slide wpcsc-slide wpcsc-slide-product" role="group" aria-roledescription="slide">
			<div class="wpcsc-slide-inner wpcsc-product-card">
				<?php if ( ! empty( $wc_opts['show_sale_badge'] ) && $product->is_on_sale() ) : ?>
					<span class="wpcsc-sale-badge"><?php esc_html_e( 'Sale!', 'wpcalibrate-slider-carousel' ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $wc_opts['show_image'] ) ) : ?>
					<div class="wpcsc-product-media">
						<a href="<?php echo $product_link; ?>" tabindex="-1" aria-hidden="true">
							<?php
							if ( has_post_thumbnail( $product_id ) ) {
								echo get_the_post_thumbnail( $product_id, 'woocommerce_thumbnail', [ 'class' => 'wpcsc-product-img' ] );
							} elseif ( function_exists( 'wc_placeholder_img' ) ) {
								echo wc_placeholder_img( 'woocommerce_thumbnail', [ 'class' => 'wpcsc-product-img' ] );
							}
							?>
						</a>
					</div>
				<?php endif; ?>

				<div class="wpcsc-product-body">
					<?php if ( ! empty( $wc_opts['show_title'] ) ) : ?>
						<h3 class="wpcsc-product-title">
							<a href="<?php echo $product_link; ?>"><?php echo esc_html( $product->get_name() ); ?></a>
						</h3>
					<?php endif; ?>

					<?php if ( ! empty( $wc_opts['show_price'] ) ) : ?>
						<div class="wpcsc-product-price">
							<?php
							// Use native WooCommerce price HTML.
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $product->get_price_html();
							?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $wc_opts['show_add_to_cart'] ) ) : ?>
						<div class="wpcsc-product-cta">
							<?php
							if ( function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
								woocommerce_template_loop_add_to_cart();
							} else {
								?>
								<a href="<?php echo $product_link; ?>" class="wpcsc-btn wpcsc-btn-secondary">
									<?php esc_html_e( 'View Product', 'wpcalibrate-slider-carousel' ); ?>
								</a>
								<?php
							}
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
