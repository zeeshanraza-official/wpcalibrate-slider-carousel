<?php
/**
 * Admin Slider List Screen.
 *
 * @package WPCalibrate\SliderCarousel\Admin
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Slider\SliderRepository;
use WPCalibrate\SliderCarousel\Slider\SliderDuplicator;
use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\BrandingHelper;

/**
 * SliderList class.
 */
class SliderList {

	/**
	 * Render the sliders list screen.
	 */
	public static function render(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpcalibrate-slider-carousel' ) );
		}

		// Handle actions (duplicate, trash, restore).
		self::handle_actions();

		$sliders = SliderRepository::get_all();
		?>
		<div class="wrap wpcalsc-admin-wrap">
			<div class="wpcalsc-header">
				<div class="wpcalsc-branding">
					<img src="<?php echo esc_url( BrandingHelper::get_icon_dark_url() ); ?>" alt="WPCalibrate" class="wpcalsc-header-logo" />
					<div>
						<h1><?php esc_html_e( 'WPCalibrate Slider & Carousel', 'wpcalibrate-slider-carousel' ); ?></h1>
						<p class="wpcalsc-tagline"><?php esc_html_e( 'Create responsive, high-performance sliders and carousels.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				</div>
				<div class="wpcalsc-header-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add New Slider', 'wpcalibrate-slider-carousel' ); ?>
					</a>
				</div>
			</div>

			<?php if ( empty( $sliders ) ) : ?>
				<div class="wpcalsc-empty-state">
					<div class="wpcalsc-empty-icon dashicons dashicons-images-alt2"></div>
					<h2><?php esc_html_e( 'No Sliders Created Yet', 'wpcalibrate-slider-carousel' ); ?></h2>
					<p><?php esc_html_e( 'Start by creating your first responsive image, video, hero slider, or dynamic post/product carousel.', 'wpcalibrate-slider-carousel' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW ) ); ?>" class="button button-primary button-hero">
						<?php esc_html_e( 'Create Your First Slider', 'wpcalibrate-slider-carousel' ); ?>
					</a>
				</div>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped table-view-list wpcalsc-table">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-title column-primary"><?php esc_html_e( 'Slider Name', 'wpcalibrate-slider-carousel' ); ?></th>
							<th scope="col" class="manage-column column-type"><?php esc_html_e( 'Preset / Type', 'wpcalibrate-slider-carousel' ); ?></th>
							<th scope="col" class="manage-column column-source"><?php esc_html_e( 'Source', 'wpcalibrate-slider-carousel' ); ?></th>
							<th scope="col" class="manage-column column-shortcode"><?php esc_html_e( 'Shortcode', 'wpcalibrate-slider-carousel' ); ?></th>
							<th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'wpcalibrate-slider-carousel' ); ?></th>
							<th scope="col" class="manage-column column-date"><?php esc_html_e( 'Last Modified', 'wpcalibrate-slider-carousel' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sliders as $slider ) :
							$slider_id    = (int) $slider['id'];
							$edit_url     = admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW . '&slider_id=' . $slider_id );
							$preview_url  = admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW . '&slider_id=' . $slider_id . '&tab=preview' );
							$duplicate_url= wp_nonce_url( admin_url( 'admin.php?page=' . Menu::PAGE_SLIDERS . '&action=duplicate&slider_id=' . $slider_id ), 'wpcalsc_duplicate_' . $slider_id );
							$trash_url    = wp_nonce_url( admin_url( 'admin.php?page=' . Menu::PAGE_SLIDERS . '&action=trash&slider_id=' . $slider_id ), 'wpcalsc_trash_' . $slider_id );
							$shortcode    = '[wpcalibrate_slider id="' . $slider_id . '"]';
							?>
							<tr>
								<td class="title column-title has-row-actions column-primary">
									<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $slider['title'] ); ?></a></strong>
									<div class="row-actions">
										<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'wpcalibrate-slider-carousel' ); ?></a> | </span>
										<span class="preview"><a href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview', 'wpcalibrate-slider-carousel' ); ?></a> | </span>
										<span class="duplicate"><a href="<?php echo esc_url( $duplicate_url ); ?>"><?php esc_html_e( 'Duplicate', 'wpcalibrate-slider-carousel' ); ?></a> | </span>
										<span class="trash"><a href="<?php echo esc_url( $trash_url ); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to trash this slider?', 'wpcalibrate-slider-carousel' ) ); ?>');"><?php esc_html_e( 'Trash', 'wpcalibrate-slider-carousel' ); ?></a></span>
									</div>
								</td>
								<td class="column-type">
									<span class="wpcalsc-badge"><?php echo esc_html( ucwords( str_replace( '_', ' ', (string) $slider['type'] ) ) ); ?></span>
								</td>
								<td class="column-source">
									<?php echo esc_html( ucfirst( (string) $slider['source'] ) ); ?>
								</td>
								<td class="column-shortcode">
									<div class="wpcalsc-shortcode-copy-wrap">
										<code><?php echo esc_html( $shortcode ); ?></code>
										<button type="button" class="button button-small wpcalsc-copy-btn" data-clipboard="<?php echo esc_attr( $shortcode ); ?>" title="<?php esc_attr_e( 'Copy shortcode to clipboard', 'wpcalibrate-slider-carousel' ); ?>">
											<span class="dashicons dashicons-admin-page"></span>
										</button>
									</div>
								</td>
								<td class="column-status">
									<?php if ( 'publish' === $slider['status'] ) : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-published"><?php esc_html_e( 'Published', 'wpcalibrate-slider-carousel' ); ?></span>
									<?php else : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-draft"><?php esc_html_e( 'Draft', 'wpcalibrate-slider-carousel' ); ?></span>
									<?php endif; ?>
								</td>
								<td class="column-date">
									<?php echo esc_html( (string) $slider['modified'] ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle admin row actions with nonce and capability verification.
	 */
	private static function handle_actions(): void {
		if ( ! isset( $_GET['action'], $_GET['slider_id'] ) ) {
			return;
		}

		$action    = sanitize_text_field( wp_unslash( $_GET['action'] ) );
		$slider_id = absint( $_GET['slider_id'] );

		if ( 'duplicate' === $action ) {
			check_admin_referer( 'wpcalsc_duplicate_' . $slider_id );
			$new_id = SliderDuplicator::duplicate( $slider_id );
			if ( ! is_wp_error( $new_id ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Slider duplicated successfully.', 'wpcalibrate-slider-carousel' ) . '</p></div>';
			}
		} elseif ( 'trash' === $action ) {
			check_admin_referer( 'wpcalsc_trash_' . $slider_id );
			SliderRepository::delete( $slider_id, false );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Slider moved to trash.', 'wpcalibrate-slider-carousel' ) . '</p></div>';
		}
	}
}
