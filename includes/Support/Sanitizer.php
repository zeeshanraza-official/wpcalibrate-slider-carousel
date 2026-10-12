<?php
/**
 * Data Sanitizer and Validator Helper.
 *
 * @package WPCalibrate\SliderCarousel\Support
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizer class.
 */
class Sanitizer {

	/**
	 * Documented allowed HTML tags for slide content.
	 *
	 * Administrator-authored content with safe formatting.
	 *
	 * @return array
	 */
	public static function get_allowed_html(): array {
		return [
			'a'      => [
				'href'   => [],
				'title'  => [],
				'target' => [],
				'rel'    => [],
				'class'  => [],
			],
			'br'     => [],
			'em'     => [],
			'strong' => [],
			'span'   => [
				'class' => [],
				'style' => [],
			],
			'p'      => [
				'class' => [],
			],
			'h1'     => [ 'class' => [] ],
			'h2'     => [ 'class' => [] ],
			'h3'     => [ 'class' => [] ],
			'h4'     => [ 'class' => [] ],
			'h5'     => [ 'class' => [] ],
			'h6'     => [ 'class' => [] ],
			'ul'     => [ 'class' => [] ],
			'ol'     => [ 'class' => [] ],
			'li'     => [ 'class' => [] ],
		];
	}

	/**
	 * Sanitize slide rich text content with safe kses.
	 *
	 * @param string $content Raw content.
	 * @return string Sanitized content.
	 */
	public static function sanitize_slide_html( string $content ): string {
		return wp_kses( $content, self::get_allowed_html() );
	}

	/**
	 * Sanitize text field.
	 *
	 * @param mixed $text Raw text.
	 * @return string
	 */
	public static function sanitize_text( mixed $text ): string {
		return sanitize_text_field( (string) $text );
	}

	/**
	 * Sanitize URL.
	 *
	 * @param mixed $url Raw URL.
	 * @return string
	 */
	public static function sanitize_url( mixed $url ): string {
		return esc_url_raw( (string) $url );
	}

	/**
	 * Sanitize positive integer.
	 *
	 * @param mixed $val Raw value.
	 * @param int   $default Fallback.
	 * @return int
	 */
	public static function sanitize_int( mixed $val, int $default = 0 ): int {
		return is_numeric( $val ) ? absint( $val ) : $default;
	}

	/**
	 * Sanitize boolean.
	 *
	 * @param mixed $val Raw value.
	 * @return bool
	 */
	public static function sanitize_bool( mixed $val ): bool {
		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sanitize allowlisted enum.
	 *
	 * @param mixed $val Raw value.
	 * @param array $allowed Allowed values.
	 * @param mixed $default Fallback value.
	 * @return mixed
	 */
	public static function sanitize_enum( mixed $val, array $allowed, mixed $default ): mixed {
		return in_array( $val, $allowed, true ) ? $val : $default;
	}
}
