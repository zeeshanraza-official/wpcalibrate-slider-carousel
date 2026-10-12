<?php
/**
 * WPCalibrate Slider & Carousel Uninstaller.
 *
 * Triggered only when the plugin is deleted via the WordPress admin plugins screen.
 * Respects data retention settings: preserves all user data by default unless
 * the explicit "Delete plugin data on uninstall" setting was enabled by an administrator.
 *
 * @package WPCalibrate\SliderCarousel
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Retrieve plugin settings.
$wpcalsc_settings = get_option( 'wpcalsc_settings', [] );

// If data deletion is NOT enabled, exit immediately to preserve all user sliders and settings.
if ( empty( $wpcalsc_settings['delete_on_uninstall'] ) ) {
	return;
}

global $wpdb;

// 1. Delete all slider posts and their post metadata.
$wpcalsc_slider_ids = get_posts(
	[
		'post_type'      => 'wpcal_slider',
		'post_status'    => [ 'publish', 'draft', 'trash', 'any' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

if ( ! empty( $wpcalsc_slider_ids ) ) {
	foreach ( $wpcalsc_slider_ids as $slider_id ) {
		// Force delete post and all associated meta without moving to trash.
		wp_delete_post( (int) $slider_id, true );
	}
}

// 2. Delete plugin-specific options.
delete_option( 'wpcalsc_version' );
delete_option( 'wpcalsc_db_version' );
delete_option( 'wpcalsc_settings' );
delete_option( 'wpcalsc_license' );

// 3. Clear all plugin transients.
if ( isset( $wpdb->options ) ) {
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpcalsc_%' OR option_name LIKE '_transient_timeout_wpcalsc_%'" );
}

// Clear object cache if supported.
if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
}
