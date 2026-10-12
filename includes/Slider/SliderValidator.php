<?php
/**
 * Slider Configuration Validator and Normalizer.
 *
 * @package WPCalibrate\SliderCarousel\Slider
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Slider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Support\Sanitizer;

/**
 * SliderValidator class.
 */
class SliderValidator {

	/**
	 * Allowed slider presets/types.
	 */
	public const ALLOWED_TYPES = [
		'standard',
		'multi_item',
		'centered',
		'hero',
		'thumbnail',
		'post_carousel',
		'product_carousel',
	];

	/**
	 * Allowed data sources.
	 */
	public const ALLOWED_SOURCES = [
		'manual',
		'posts',
		'products',
	];

	/**
	 * Allowed transition effects.
	 */
	public const ALLOWED_EFFECTS = [
		'slide',
		'fade',
		'coverflow',
		'flip',
		'cards',
		'cube',
	];

	/**
	 * Allowed orderby values for dynamic queries.
	 */
	public const ALLOWED_ORDERBY = [
		'date',
		'title',
		'ID',
		'menu_order',
		'rand',
		'modified',
		'comment_count',
		'price',
		'popularity',
		'rating',
	];

	/**
	 * Get default configuration schema.
	 *
	 * @return array
	 */
	public static function get_default_config(): array {
		return [
			'schema_version'   => SliderPostType::CURRENT_SCHEMA_VERSION,
			'type'             => 'standard',
			'source'           => 'manual',
			'slides'           => [],
			'query'            => [
				'post_type'      => 'post',
				'taxonomy'       => 'category',
				'terms'          => [],
				'include_ids'    => [],
				'exclude_ids'    => [],
				'limit'          => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'show_image'     => true,
				'show_title'     => true,
				'show_excerpt'   => true,
				'show_cta'       => true,
				'cta_text'       => __( 'Read More', 'wpcalibrate-slider-carousel' ),
				'show_date'      => false,
				'show_author'    => false,
			],
			'woocommerce'      => [
				'filter_type'    => 'all', // all, featured, on_sale, category, tag
				'category_ids'   => [],
				'tag_ids'        => [],
				'include_ids'    => [],
				'exclude_ids'    => [],
				'limit'          => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'stock_status'   => 'instock', // any, instock
				'show_image'     => true,
				'show_title'     => true,
				'show_price'     => true,
				'show_rating'    => false,
				'show_sale_badge'=> true,
				'show_add_to_cart'=> true,
				'button_text'    => '',
			],
			'layout'           => [
				'slides_per_view_desktop' => 1,
				'slides_per_view_tablet'  => 1,
				'slides_per_view_mobile'  => 1,
				'slides_per_group_desktop'=> 1,
				'slides_per_group_tablet' => 1,
				'slides_per_group_mobile' => 1,
				'gap_desktop'             => 20,
				'gap_tablet'              => 15,
				'gap_mobile'              => 10,
				'centered_slides'         => false,
				'auto_height'             => false,
				'height'                  => 'auto', // auto, custom, fixed px
				'aspect_ratio'            => '16/9',
				'full_width'              => false,
				'max_width'               => '1200px',
			],
			'controls'         => [
				'arrows'                  => true,
				'pagination'              => true,
				'pagination_type'         => 'bullets', // bullets, fraction, progressbar
				'scrollbar'               => false,
				'thumbnail_nav'           => false,
				'thumbnail_slides_per_view' => 5,
				'thumbnail_gap'           => 10,
			],
			'autoplay'         => [
				'enabled'                 => false,
				'delay'                   => 4000,
				'pause_on_hover'          => true,
				'pause_on_interaction'    => true,
				'stop_on_last'            => false,
			],
			'motion'           => [
				'speed'                   => 500,
				'loop'                    => true,
				'rewind'                  => false,
				'effect'                  => 'slide',
				'touch_swipe'             => true,
				'keyboard'                => true,
				'mousewheel'              => false,
				'reduced_motion'          => true,
			],
			'style'            => [
				'border_radius'           => 8,
				'overlay_opacity'         => 0.35,
				'arrow_color'             => '#ffffff',
				'arrow_bg'                => 'rgba(0, 0, 0, 0.45)',
				'dot_color'               => 'rgba(0, 0, 0, 0.25)',
				'dot_active_color'        => '#1d2327',
				'custom_class'            => '',
			],
			'accessibility'    => [
				'aria_label'              => __( 'Slider Carousel', 'wpcalibrate-slider-carousel' ),
			],
		];
	}

	/**
	 * Validate and sanitize slider configuration.
	 *
	 * @param array $config Raw input configuration.
	 * @return array Validated and sanitized array.
	 */
	public static function validate( array $config ): array {
		$defaults = self::get_default_config();

		// Schema version.
		$clean['schema_version'] = SliderPostType::CURRENT_SCHEMA_VERSION;

		// Type & Source.
		$clean['type']   = Sanitizer::sanitize_enum( $config['type'] ?? '', self::ALLOWED_TYPES, $defaults['type'] );
		$clean['source'] = Sanitizer::sanitize_enum( $config['source'] ?? '', self::ALLOWED_SOURCES, $defaults['source'] );

		// Manual slides.
		$clean['slides'] = [];
		if ( isset( $config['slides'] ) && is_array( $config['slides'] ) ) {
			foreach ( $config['slides'] as $slide ) {
				if ( ! is_array( $slide ) ) {
					continue;
				}
				$clean['slides'][] = self::validate_slide( $slide );
			}
		}

		// Dynamic Query.
		$query = $config['query'] ?? [];
		$clean['query'] = [
			'post_type'    => Sanitizer::sanitize_text( $query['post_type'] ?? 'post' ),
			'taxonomy'     => Sanitizer::sanitize_text( $query['taxonomy'] ?? 'category' ),
			'terms'        => array_map( 'absint', (array) ( $query['terms'] ?? [] ) ),
			'include_ids'  => array_map( 'absint', (array) ( $query['include_ids'] ?? [] ) ),
			'exclude_ids'  => array_map( 'absint', (array) ( $query['exclude_ids'] ?? [] ) ),
			'limit'        => min( 50, max( 1, Sanitizer::sanitize_int( $query['limit'] ?? 10, 10 ) ) ),
			'orderby'      => Sanitizer::sanitize_enum( $query['orderby'] ?? 'date', self::ALLOWED_ORDERBY, 'date' ),
			'order'        => strtoupper( (string) ( $query['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC',
			'show_image'   => Sanitizer::sanitize_bool( $query['show_image'] ?? true ),
			'show_title'   => Sanitizer::sanitize_bool( $query['show_title'] ?? true ),
			'show_excerpt' => Sanitizer::sanitize_bool( $query['show_excerpt'] ?? true ),
			'show_cta'     => Sanitizer::sanitize_bool( $query['show_cta'] ?? true ),
			'cta_text'     => Sanitizer::sanitize_text( $query['cta_text'] ?? __( 'Read More', 'wpcalibrate-slider-carousel' ) ),
			'show_date'    => Sanitizer::sanitize_bool( $query['show_date'] ?? false ),
			'show_author'  => Sanitizer::sanitize_bool( $query['show_author'] ?? false ),
		];

		// WooCommerce Products.
		$wc = $config['woocommerce'] ?? [];
		$clean['woocommerce'] = [
			'filter_type'     => Sanitizer::sanitize_enum( $wc['filter_type'] ?? 'all', [ 'all', 'featured', 'on_sale', 'category', 'tag' ], 'all' ),
			'category_ids'    => array_map( 'absint', (array) ( $wc['category_ids'] ?? [] ) ),
			'tag_ids'         => array_map( 'absint', (array) ( $wc['tag_ids'] ?? [] ) ),
			'include_ids'     => array_map( 'absint', (array) ( $wc['include_ids'] ?? [] ) ),
			'exclude_ids'     => array_map( 'absint', (array) ( $wc['exclude_ids'] ?? [] ) ),
			'limit'           => min( 50, max( 1, Sanitizer::sanitize_int( $wc['limit'] ?? 10, 10 ) ) ),
			'orderby'         => Sanitizer::sanitize_enum( $wc['orderby'] ?? 'date', self::ALLOWED_ORDERBY, 'date' ),
			'order'           => strtoupper( (string) ( $wc['order'] ?? 'DESC' ) ) === 'ASC' ? 'ASC' : 'DESC',
			'stock_status'    => Sanitizer::sanitize_enum( $wc['stock_status'] ?? 'instock', [ 'any', 'instock' ], 'instock' ),
			'show_image'      => Sanitizer::sanitize_bool( $wc['show_image'] ?? true ),
			'show_title'      => Sanitizer::sanitize_bool( $wc['show_title'] ?? true ),
			'show_price'      => Sanitizer::sanitize_bool( $wc['show_price'] ?? true ),
			'show_rating'     => Sanitizer::sanitize_bool( $wc['show_rating'] ?? false ),
			'show_sale_badge' => Sanitizer::sanitize_bool( $wc['show_sale_badge'] ?? true ),
			'show_add_to_cart'=> Sanitizer::sanitize_bool( $wc['show_add_to_cart'] ?? true ),
			'button_text'     => Sanitizer::sanitize_text( $wc['button_text'] ?? '' ),
		];

		// Layout.
		$layout = $config['layout'] ?? [];
		$clean['layout'] = [
			'slides_per_view_desktop' => max( 1, min( 12, (float) ( $layout['slides_per_view_desktop'] ?? 1 ) ) ),
			'slides_per_view_tablet'  => max( 1, min( 8, (float) ( $layout['slides_per_view_tablet'] ?? 1 ) ) ),
			'slides_per_view_mobile'  => max( 1, min( 4, (float) ( $layout['slides_per_view_mobile'] ?? 1 ) ) ),
			'slides_per_group_desktop'=> max( 1, min( 12, absint( $layout['slides_per_group_desktop'] ?? 1 ) ) ),
			'slides_per_group_tablet' => max( 1, min( 8, absint( $layout['slides_per_group_tablet'] ?? 1 ) ) ),
			'slides_per_group_mobile' => max( 1, min( 4, absint( $layout['slides_per_group_mobile'] ?? 1 ) ) ),
			'gap_desktop'             => max( 0, min( 100, (int) ( $layout['gap_desktop'] ?? 20 ) ) ),
			'gap_tablet'              => max( 0, min( 80, (int) ( $layout['gap_tablet'] ?? 15 ) ) ),
			'gap_mobile'              => max( 0, min( 50, (int) ( $layout['gap_mobile'] ?? 10 ) ) ),
			'centered_slides'         => Sanitizer::sanitize_bool( $layout['centered_slides'] ?? false ),
			'auto_height'             => Sanitizer::sanitize_bool( $layout['auto_height'] ?? false ),
			'height'                  => Sanitizer::sanitize_text( $layout['height'] ?? 'auto' ),
			'aspect_ratio'            => Sanitizer::sanitize_text( $layout['aspect_ratio'] ?? '16/9' ),
			'full_width'              => Sanitizer::sanitize_bool( $layout['full_width'] ?? false ),
			'max_width'               => Sanitizer::sanitize_text( $layout['max_width'] ?? '1200px' ),
		];

		// Controls.
		$controls = $config['controls'] ?? [];
		$clean['controls'] = [
			'arrows'                    => Sanitizer::sanitize_bool( $controls['arrows'] ?? true ),
			'pagination'                => Sanitizer::sanitize_bool( $controls['pagination'] ?? true ),
			'pagination_type'           => Sanitizer::sanitize_enum( $controls['pagination_type'] ?? 'bullets', [ 'bullets', 'fraction', 'progressbar' ], 'bullets' ),
			'scrollbar'                 => Sanitizer::sanitize_bool( $controls['scrollbar'] ?? false ),
			'thumbnail_nav'             => Sanitizer::sanitize_bool( $controls['thumbnail_nav'] ?? false ),
			'thumbnail_slides_per_view'   => max( 2, min( 10, absint( $controls['thumbnail_slides_per_view'] ?? 5 ) ) ),
			'thumbnail_gap'             => max( 0, min( 30, absint( $controls['thumbnail_gap'] ?? 10 ) ) ),
		];

		// Autoplay.
		$autoplay = $config['autoplay'] ?? [];
		$clean['autoplay'] = [
			'enabled'              => Sanitizer::sanitize_bool( $autoplay['enabled'] ?? false ),
			'delay'                => max( 1000, min( 60000, absint( $autoplay['delay'] ?? 4000 ) ) ),
			'pause_on_hover'       => Sanitizer::sanitize_bool( $autoplay['pause_on_hover'] ?? true ),
			'pause_on_interaction' => Sanitizer::sanitize_bool( $autoplay['pause_on_interaction'] ?? true ),
			'stop_on_last'         => Sanitizer::sanitize_bool( $autoplay['stop_on_last'] ?? false ),
		];

		// Motion.
		$motion = $config['motion'] ?? [];
		$clean['motion'] = [
			'speed'          => max( 100, min( 5000, absint( $motion['speed'] ?? 500 ) ) ),
			'loop'           => Sanitizer::sanitize_bool( $motion['loop'] ?? true ),
			'rewind'         => Sanitizer::sanitize_bool( $motion['rewind'] ?? false ),
			'effect'         => Sanitizer::sanitize_enum( $motion['effect'] ?? 'slide', self::ALLOWED_EFFECTS, 'slide' ),
			'touch_swipe'    => Sanitizer::sanitize_bool( $motion['touch_swipe'] ?? true ),
			'keyboard'       => Sanitizer::sanitize_bool( $motion['keyboard'] ?? true ),
			'mousewheel'     => Sanitizer::sanitize_bool( $motion['mousewheel'] ?? false ),
			'reduced_motion' => Sanitizer::sanitize_bool( $motion['reduced_motion'] ?? true ),
		];

		// Style.
		$style = $config['style'] ?? [];
		$clean['style'] = [
			'border_radius'    => max( 0, min( 50, absint( $style['border_radius'] ?? 8 ) ) ),
			'overlay_opacity'  => max( 0, min( 1, (float) ( $style['overlay_opacity'] ?? 0.35 ) ) ),
			'arrow_color'      => Sanitizer::sanitize_text( $style['arrow_color'] ?? '#ffffff' ),
			'arrow_bg'         => Sanitizer::sanitize_text( $style['arrow_bg'] ?? 'rgba(0, 0, 0, 0.45)' ),
			'dot_color'        => Sanitizer::sanitize_text( $style['dot_color'] ?? 'rgba(0, 0, 0, 0.25)' ),
			'dot_active_color' => Sanitizer::sanitize_text( $style['dot_active_color'] ?? '#1d2327' ),
			'custom_class'     => preg_replace( '/[^a-zA-Z0-9_\-\s]/', '', (string) ( $style['custom_class'] ?? '' ) ),
		];

		// Accessibility.
		$a11y = $config['accessibility'] ?? [];
		$clean['accessibility'] = [
			'aria_label' => Sanitizer::sanitize_text( $a11y['aria_label'] ?? __( 'Slider Carousel', 'wpcalibrate-slider-carousel' ) ),
		];

		return $clean;
	}

	/**
	 * Validate a single manual slide definition.
	 *
	 * @param array $slide Raw slide data.
	 * @return array
	 */
	public static function validate_slide( array $slide ): array {
		$type = Sanitizer::sanitize_enum( $slide['type'] ?? 'image', [ 'image', 'video', 'content' ], 'image' );

		return [
			'type'             => $type,
			'image_id'         => absint( $slide['image_id'] ?? 0 ),
			'image_url'        => Sanitizer::sanitize_url( $slide['image_url'] ?? '' ),
			'mobile_image_id'  => absint( $slide['mobile_image_id'] ?? 0 ),
			'mobile_image_url' => Sanitizer::sanitize_url( $slide['mobile_image_url'] ?? '' ),
			'alt'              => Sanitizer::sanitize_text( $slide['alt'] ?? '' ),
			'video_id'         => absint( $slide['video_id'] ?? 0 ),
			'video_url'        => Sanitizer::sanitize_url( $slide['video_url'] ?? '' ),
			'poster_id'        => absint( $slide['poster_id'] ?? 0 ),
			'poster_url'       => Sanitizer::sanitize_url( $slide['poster_url'] ?? '' ),
			'video_muted'      => Sanitizer::sanitize_bool( $slide['video_muted'] ?? true ),
			'video_loop'       => Sanitizer::sanitize_bool( $slide['video_loop'] ?? true ),
			'video_controls'   => Sanitizer::sanitize_bool( $slide['video_controls'] ?? false ),
			'heading'          => Sanitizer::sanitize_text( $slide['heading'] ?? '' ),
			'description'      => Sanitizer::sanitize_slide_html( $slide['description'] ?? '' ),
			'content_html'     => Sanitizer::sanitize_slide_html( $slide['content_html'] ?? '' ),
			'button_text'      => Sanitizer::sanitize_text( $slide['button_text'] ?? '' ),
			'button_url'       => Sanitizer::sanitize_url( $slide['button_url'] ?? '' ),
			'link_target'      => Sanitizer::sanitize_enum( $slide['link_target'] ?? '_self', [ '_self', '_blank' ], '_self' ),
			'text_align'       => Sanitizer::sanitize_enum( $slide['text_align'] ?? 'left', [ 'left', 'center', 'right' ], 'left' ),
			'content_position' => Sanitizer::sanitize_enum( $slide['content_position'] ?? 'center', [ 'top-left', 'top-center', 'top-right', 'center-left', 'center', 'center-right', 'bottom-left', 'bottom-center', 'bottom-right' ], 'center' ),
			'bg_color'         => Sanitizer::sanitize_text( $slide['bg_color'] ?? '' ),
			'text_color'       => Sanitizer::sanitize_text( $slide['text_color'] ?? '' ),
		];
	}
}
