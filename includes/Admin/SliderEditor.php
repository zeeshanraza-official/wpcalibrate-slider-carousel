<?php
/**
 * Admin Visual Slider Editor Screen.
 *
 * @package WPCalibrate\SliderCarousel\Admin
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Slider\SliderRepository;
use WPCalibrate\SliderCarousel\Slider\SliderValidator;
use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\BrandingHelper;
use WPCalibrate\SliderCarousel\Rendering\Renderer;

/**
 * SliderEditor class.
 */
class SliderEditor {

	/**
	 * Register AJAX/form actions.
	 */
	public static function init(): void {
		add_action( 'admin_post_wpcalsc_save_slider', [ self::class, 'handle_save' ] );
		add_action( 'wp_ajax_wpcalsc_save_slider_ajax', [ self::class, 'handle_save_ajax' ] );
	}

	/**
	 * Render add/edit screen.
	 */
	public static function render_new(): void {
		self::render();
	}

	/**
	 * Render the full visual editor.
	 */
	public static function render(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpcalibrate-slider-carousel' ) );
		}

		$slider_id = isset( $_GET['slider_id'] ) ? absint( $_GET['slider_id'] ) : 0;
		$slider    = $slider_id > 0 ? SliderRepository::get( $slider_id ) : null;

		$title  = $slider['title'] ?? __( 'New Slider', 'wpcalibrate-slider-carousel' );
		$status = $slider['status'] ?? 'publish';
		$config = $slider['config'] ?? SliderValidator::get_default_config();

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'slides';

		// Get all public post types for dynamic query selector.
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $post_types['attachment'] );

		// WooCommerce active check.
		$wc_active = class_exists( 'WooCommerce' );
		?>
		<div class="wrap wpcalsc-admin-wrap">
			<form id="wpcalsc-slider-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="wpcalsc_save_slider" />
				<input type="hidden" name="slider_id" id="wpcalsc_slider_id" value="<?php echo esc_attr( (string) $slider_id ); ?>" />
				<?php wp_nonce_field( 'wpcalsc_save_slider_action', 'wpcalsc_slider_nonce' ); ?>

				<div class="wpcalsc-editor-header">
					<div class="wpcalsc-editor-title-wrap">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SLIDERS ) ); ?>" class="button wpcalsc-back-btn" title="<?php esc_attr_e( 'Back to Sliders', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-arrow-left-alt2"></span>
						</a>
						<div class="wpcalsc-title-input-group">
							<input type="text" name="slider_title" id="wpcalsc_slider_title" class="wpcalsc-title-input" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Enter slider title...', 'wpcalibrate-slider-carousel' ); ?>" required />
						</div>
					</div>
					<div class="wpcalsc-editor-actions">
						<select name="slider_status" class="wpcalsc-select-status">
							<option value="publish" <?php selected( $status, 'publish' ); ?>><?php esc_html_e( 'Published', 'wpcalibrate-slider-carousel' ); ?></option>
							<option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'wpcalibrate-slider-carousel' ); ?></option>
						</select>
						<?php if ( $slider_id > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_ADD_NEW . '&slider_id=' . $slider_id . '&tab=preview' ) ); ?>" class="button wpcalsc-preview-toggle-btn">
								<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Live Preview', 'wpcalibrate-slider-carousel' ); ?>
							</a>
						<?php endif; ?>
						<button type="submit" class="button button-primary button-large wpcalsc-save-btn">
							<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Slider', 'wpcalibrate-slider-carousel' ); ?>
						</button>
					</div>
				</div>

				<?php if ( isset( $_GET['saved'] ) ) : ?>
					<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Slider saved successfully!', 'wpcalibrate-slider-carousel' ); ?></p></div>
				<?php endif; ?>

				<!-- Editor Navigation Tabs -->
				<nav class="nav-tab-wrapper wpcalsc-nav-tabs" aria-label="<?php esc_attr_e( 'Slider Settings Navigation', 'wpcalibrate-slider-carousel' ); ?>">
					<a href="#tab-general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-general">
						<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General & Source', 'wpcalibrate-slider-carousel' ); ?>
					</a>
					<a href="#tab-slides" class="nav-tab <?php echo 'slides' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-slides">
						<span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'Slides / Content', 'wpcalibrate-slider-carousel' ); ?>
					</a>
					<a href="#tab-layout" class="nav-tab <?php echo 'layout' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-layout">
						<span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'Layout & Responsive', 'wpcalibrate-slider-carousel' ); ?>
					</a>
					<a href="#tab-controls" class="nav-tab <?php echo 'controls' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-controls">
						<span class="dashicons dashicons-controls-play"></span> <?php esc_html_e( 'Navigation & Autoplay', 'wpcalibrate-slider-carousel' ); ?>
					</a>
					<a href="#tab-style" class="nav-tab <?php echo 'style' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-style">
						<span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e( 'Style & Design', 'wpcalibrate-slider-carousel' ); ?>
					</a>
					<?php if ( $slider_id > 0 ) : ?>
						<a href="#tab-preview" class="nav-tab <?php echo 'preview' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-preview">
							<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Preview', 'wpcalibrate-slider-carousel' ); ?>
						</a>
					<?php endif; ?>
					<a href="#tab-overview" class="nav-tab <?php echo 'overview' === $active_tab ? 'nav-tab-active' : ''; ?>" data-tab="tab-overview">
						<span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Overview & Guide', 'wpcalibrate-slider-carousel' ); ?>
					</a>
				</nav>

				<div class="wpcalsc-tab-panels">
					<!-- TAB 0: Overview & Guide -->
					<div id="tab-overview" class="wpcalsc-tab-panel <?php echo 'overview' === $active_tab ? 'is-active' : ''; ?>">
						<?php OverviewSection::render_editor_guide(); ?>
					</div>

					<!-- TAB 1: General & Source -->
					<div id="tab-general" class="wpcalsc-tab-panel <?php echo 'general' === $active_tab ? 'is-active' : ''; ?>">
						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'Preset & Data Source', 'wpcalibrate-slider-carousel' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><label for="config_type"><?php esc_html_e( 'Slider Preset / Type', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<select name="config[type]" id="config_type" class="regular-text">
											<option value="standard" <?php selected( $config['type'], 'standard' ); ?>><?php esc_html_e( 'Standard Slider (Single Slide View)', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="multi_item" <?php selected( $config['type'], 'multi_item' ); ?>><?php esc_html_e( 'Multi-Item Carousel', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="centered" <?php selected( $config['type'], 'centered' ); ?>><?php esc_html_e( 'Centered Carousel', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="hero" <?php selected( $config['type'], 'hero' ); ?>><?php esc_html_e( 'Hero / Full-Width Slider', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="thumbnail" <?php selected( $config['type'], 'thumbnail' ); ?>><?php esc_html_e( 'Thumbnail Navigation Slider', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="post_carousel" <?php selected( $config['type'], 'post_carousel' ); ?>><?php esc_html_e( 'Post / Dynamic Content Carousel', 'wpcalibrate-slider-carousel' ); ?></option>
											<?php if ( $wc_active ) : ?>
												<option value="product_carousel" <?php selected( $config['type'], 'product_carousel' ); ?>><?php esc_html_e( 'WooCommerce Product Carousel', 'wpcalibrate-slider-carousel' ); ?></option>
											<?php endif; ?>
										</select>
										<p class="description"><?php esc_html_e( 'Select the visual layout preset for this slider.', 'wpcalibrate-slider-carousel' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="config_source"><?php esc_html_e( 'Content Source', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<select name="config[source]" id="config_source" class="regular-text">
											<option value="manual" <?php selected( $config['source'], 'manual' ); ?>><?php esc_html_e( 'Manual Slides (Images, Videos, Content)', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="posts" <?php selected( $config['source'], 'posts' ); ?>><?php esc_html_e( 'Dynamic WordPress Posts & Pages', 'wpcalibrate-slider-carousel' ); ?></option>
											<?php if ( $wc_active ) : ?>
												<option value="products" <?php selected( $config['source'], 'products' ); ?>><?php esc_html_e( 'WooCommerce Products', 'wpcalibrate-slider-carousel' ); ?></option>
											<?php else : ?>
												<option value="products" disabled><?php esc_html_e( 'WooCommerce Products (WooCommerce inactive)', 'wpcalibrate-slider-carousel' ); ?></option>
											<?php endif; ?>
										</select>
										<p class="description"><?php esc_html_e( 'Choose whether slides are created manually or populated dynamically from WordPress/WooCommerce.', 'wpcalibrate-slider-carousel' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- TAB 2: Slides / Content -->
					<div id="tab-slides" class="wpcalsc-tab-panel <?php echo 'slides' === $active_tab ? 'is-active' : ''; ?>">
						<!-- Sub-section: Manual Slides -->
						<div id="wpcalsc-source-manual" class="wpcalsc-source-section <?php echo 'manual' === $config['source'] ? '' : 'wpcalsc-hidden'; ?>">
							<div class="wpcalsc-slides-toolbar">
								<h2><?php esc_html_e( 'Slide Management', 'wpcalibrate-slider-carousel' ); ?></h2>
								<div class="wpcalsc-add-slide-buttons">
									<button type="button" class="button button-secondary wpcalsc-add-slide-btn" data-type="image">
										<span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Add Image Slide', 'wpcalibrate-slider-carousel' ); ?>
									</button>
									<button type="button" class="button button-secondary wpcalsc-add-slide-btn" data-type="video">
										<span class="dashicons dashicons-format-video"></span> <?php esc_html_e( 'Add Video Slide', 'wpcalibrate-slider-carousel' ); ?>
									</button>
									<button type="button" class="button button-secondary wpcalsc-add-slide-btn" data-type="content">
										<span class="dashicons dashicons-editor-alignleft"></span> <?php esc_html_e( 'Add Content Slide', 'wpcalibrate-slider-carousel' ); ?>
									</button>
								</div>
							</div>

							<div id="wpcalsc-slides-container" class="wpcalsc-slides-list">
								<?php
								$slides = $config['slides'] ?? [];
								if ( empty( $slides ) ) {
									echo '<div class="wpcalsc-no-slides-msg"><p>' . esc_html__( 'No slides added yet. Click one of the buttons above to add your first slide.', 'wpcalibrate-slider-carousel' ) . '</p></div>';
								} else {
									foreach ( $slides as $index => $slide ) {
										self::render_slide_item_form( (int) $index, (array) $slide );
									}
								}
								?>
							</div>
						</div>

						<!-- Sub-section: Dynamic Posts Query -->
						<div id="wpcalsc-source-posts" class="wpcalsc-source-section <?php echo 'posts' === $config['source'] ? '' : 'wpcalsc-hidden'; ?>">
							<div class="wpcalsc-card">
								<h2><?php esc_html_e( 'WordPress Dynamic Query Configuration', 'wpcalibrate-slider-carousel' ); ?></h2>
								<table class="form-table">
									<tr>
										<th scope="row"><label for="query_post_type"><?php esc_html_e( 'Post Type', 'wpcalibrate-slider-carousel' ); ?></label></th>
										<td>
											<select name="config[query][post_type]" id="query_post_type">
												<?php foreach ( $post_types as $pt_name => $pt_obj ) : ?>
													<option value="<?php echo esc_attr( $pt_name ); ?>" <?php selected( $config['query']['post_type'], $pt_name ); ?>><?php echo esc_html( $pt_obj->label ); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="query_limit"><?php esc_html_e( 'Number of Items', 'wpcalibrate-slider-carousel' ); ?></label></th>
										<td>
											<input type="number" name="config[query][limit]" id="query_limit" value="<?php echo esc_attr( (string) $config['query']['limit'] ); ?>" min="1" max="50" class="small-text" />
											<p class="description"><?php esc_html_e( 'Maximum number of posts to fetch (safe limit: 1-50).', 'wpcalibrate-slider-carousel' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="query_orderby"><?php esc_html_e( 'Order By & Order', 'wpcalibrate-slider-carousel' ); ?></label></th>
										<td>
											<select name="config[query][orderby]" id="query_orderby">
												<option value="date" <?php selected( $config['query']['orderby'], 'date' ); ?>><?php esc_html_e( 'Date', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="title" <?php selected( $config['query']['orderby'], 'title' ); ?>><?php esc_html_e( 'Title', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="modified" <?php selected( $config['query']['orderby'], 'modified' ); ?>><?php esc_html_e( 'Last Modified', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="rand" <?php selected( $config['query']['orderby'], 'rand' ); ?>><?php esc_html_e( 'Random', 'wpcalibrate-slider-carousel' ); ?></option>
											</select>
											<select name="config[query][order]">
												<option value="DESC" <?php selected( $config['query']['order'], 'DESC' ); ?>><?php esc_html_e( 'Descending (DESC)', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="ASC" <?php selected( $config['query']['order'], 'ASC' ); ?>><?php esc_html_e( 'Ascending (ASC)', 'wpcalibrate-slider-carousel' ); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Display Elements', 'wpcalibrate-slider-carousel' ); ?></th>
										<td>
											<fieldset>
												<label><input type="checkbox" name="config[query][show_image]" value="1" <?php checked( $config['query']['show_image'] ); ?> /> <?php esc_html_e( 'Featured Image', 'wpcalibrate-slider-carousel' ); ?></label><br />
												<label><input type="checkbox" name="config[query][show_title]" value="1" <?php checked( $config['query']['show_title'] ); ?> /> <?php esc_html_e( 'Post Title', 'wpcalibrate-slider-carousel' ); ?></label><br />
												<label><input type="checkbox" name="config[query][show_excerpt]" value="1" <?php checked( $config['query']['show_excerpt'] ); ?> /> <?php esc_html_e( 'Excerpt', 'wpcalibrate-slider-carousel' ); ?></label><br />
												<label><input type="checkbox" name="config[query][show_cta]" value="1" <?php checked( $config['query']['show_cta'] ); ?> /> <?php esc_html_e( 'Read More Link / Button', 'wpcalibrate-slider-carousel' ); ?></label><br />
												<label><input type="checkbox" name="config[query][show_date]" value="1" <?php checked( $config['query']['show_date'] ); ?> /> <?php esc_html_e( 'Date', 'wpcalibrate-slider-carousel' ); ?></label><br />
												<label><input type="checkbox" name="config[query][show_author]" value="1" <?php checked( $config['query']['show_author'] ); ?> /> <?php esc_html_e( 'Author', 'wpcalibrate-slider-carousel' ); ?></label>
											</fieldset>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<!-- Sub-section: WooCommerce Products -->
						<div id="wpcalsc-source-products" class="wpcalsc-source-section <?php echo 'products' === $config['source'] ? '' : 'wpcalsc-hidden'; ?>">
							<div class="wpcalsc-card">
								<h2><?php esc_html_e( 'WooCommerce Products Configuration', 'wpcalibrate-slider-carousel' ); ?></h2>
								<?php if ( ! $wc_active ) : ?>
									<div class="notice notice-warning inline"><p><?php esc_html_e( 'WooCommerce is not currently active on this site. Please install and activate WooCommerce to query products.', 'wpcalibrate-slider-carousel' ); ?></p></div>
								<?php else : ?>
									<table class="form-table">
										<tr>
											<th scope="row"><label for="wc_filter_type"><?php esc_html_e( 'Product Filter', 'wpcalibrate-slider-carousel' ); ?></label></th>
											<td>
												<select name="config[woocommerce][filter_type]" id="wc_filter_type">
													<option value="all" <?php selected( $config['woocommerce']['filter_type'], 'all' ); ?>><?php esc_html_e( 'All Recent Products', 'wpcalibrate-slider-carousel' ); ?></option>
													<option value="featured" <?php selected( $config['woocommerce']['filter_type'], 'featured' ); ?>><?php esc_html_e( 'Featured Products Only', 'wpcalibrate-slider-carousel' ); ?></option>
													<option value="on_sale" <?php selected( $config['woocommerce']['filter_type'], 'on_sale' ); ?>><?php esc_html_e( 'On Sale Products Only', 'wpcalibrate-slider-carousel' ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th scope="row"><label for="wc_limit"><?php esc_html_e( 'Number of Products', 'wpcalibrate-slider-carousel' ); ?></label></th>
											<td>
												<input type="number" name="config[woocommerce][limit]" id="wc_limit" value="<?php echo esc_attr( (string) $config['woocommerce']['limit'] ); ?>" min="1" max="50" class="small-text" />
											</td>
										</tr>
										<tr>
											<th scope="row"><label for="wc_stock_status"><?php esc_html_e( 'Stock Status', 'wpcalibrate-slider-carousel' ); ?></label></th>
											<td>
												<select name="config[woocommerce][stock_status]" id="wc_stock_status">
													<option value="instock" <?php selected( $config['woocommerce']['stock_status'], 'instock' ); ?>><?php esc_html_e( 'In Stock Only', 'wpcalibrate-slider-carousel' ); ?></option>
													<option value="any" <?php selected( $config['woocommerce']['stock_status'], 'any' ); ?>><?php esc_html_e( 'Any Stock Status', 'wpcalibrate-slider-carousel' ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Product Display Options', 'wpcalibrate-slider-carousel' ); ?></th>
											<td>
												<fieldset>
													<label><input type="checkbox" name="config[woocommerce][show_image]" value="1" <?php checked( $config['woocommerce']['show_image'] ); ?> /> <?php esc_html_e( 'Product Image', 'wpcalibrate-slider-carousel' ); ?></label><br />
													<label><input type="checkbox" name="config[woocommerce][show_title]" value="1" <?php checked( $config['woocommerce']['show_title'] ); ?> /> <?php esc_html_e( 'Product Title', 'wpcalibrate-slider-carousel' ); ?></label><br />
													<label><input type="checkbox" name="config[woocommerce][show_price]" value="1" <?php checked( $config['woocommerce']['show_price'] ); ?> /> <?php esc_html_e( 'Product Price (using WC price format)', 'wpcalibrate-slider-carousel' ); ?></label><br />
													<label><input type="checkbox" name="config[woocommerce][show_sale_badge]" value="1" <?php checked( $config['woocommerce']['show_sale_badge'] ); ?> /> <?php esc_html_e( 'Sale Badge', 'wpcalibrate-slider-carousel' ); ?></label><br />
													<label><input type="checkbox" name="config[woocommerce][show_add_to_cart]" value="1" <?php checked( $config['woocommerce']['show_add_to_cart'] ); ?> /> <?php esc_html_e( 'Add to Cart Button', 'wpcalibrate-slider-carousel' ); ?></label>
												</fieldset>
											</td>
										</tr>
									</table>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<!-- TAB 3: Layout & Responsive -->
					<div id="tab-layout" class="wpcalsc-tab-panel <?php echo 'layout' === $active_tab ? 'is-active' : ''; ?>">
						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'Responsive Breakpoints & Grid', 'wpcalibrate-slider-carousel' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Slides Per View', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<div class="wpcalsc-responsive-grid">
											<div class="wpcalsc-device-col">
												<span class="dashicons dashicons-desktop"></span> <strong><?php esc_html_e( 'Desktop (>1024px)', 'wpcalibrate-slider-carousel' ); ?></strong>
												<input type="number" step="0.5" min="1" max="12" name="config[layout][slides_per_view_desktop]" value="<?php echo esc_attr( (string) $config['layout']['slides_per_view_desktop'] ); ?>" class="small-text" />
											</div>
											<div class="wpcalsc-device-col">
												<span class="dashicons dashicons-tablet"></span> <strong><?php esc_html_e( 'Tablet (641-1024px)', 'wpcalibrate-slider-carousel' ); ?></strong>
												<input type="number" step="0.5" min="1" max="8" name="config[layout][slides_per_view_tablet]" value="<?php echo esc_attr( (string) $config['layout']['slides_per_view_tablet'] ); ?>" class="small-text" />
											</div>
											<div class="wpcalsc-device-col">
												<span class="dashicons dashicons-smartphone"></span> <strong><?php esc_html_e( 'Mobile (<=640px)', 'wpcalibrate-slider-carousel' ); ?></strong>
												<input type="number" step="0.5" min="1" max="4" name="config[layout][slides_per_view_mobile]" value="<?php echo esc_attr( (string) $config['layout']['slides_per_view_mobile'] ); ?>" class="small-text" />
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Space Between / Gap (px)', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<div class="wpcalsc-responsive-grid">
											<div class="wpcalsc-device-col">
												<span><?php esc_html_e( 'Desktop Gap', 'wpcalibrate-slider-carousel' ); ?></span>
												<input type="number" min="0" max="100" name="config[layout][gap_desktop]" value="<?php echo esc_attr( (string) $config['layout']['gap_desktop'] ); ?>" class="small-text" /> px
											</div>
											<div class="wpcalsc-device-col">
												<span><?php esc_html_e( 'Tablet Gap', 'wpcalibrate-slider-carousel' ); ?></span>
												<input type="number" min="0" max="80" name="config[layout][gap_tablet]" value="<?php echo esc_attr( (string) $config['layout']['gap_tablet'] ); ?>" class="small-text" /> px
											</div>
											<div class="wpcalsc-device-col">
												<span><?php esc_html_e( 'Mobile Gap', 'wpcalibrate-slider-carousel' ); ?></span>
												<input type="number" min="0" max="50" name="config[layout][gap_mobile]" value="<?php echo esc_attr( (string) $config['layout']['gap_mobile'] ); ?>" class="small-text" /> px
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Slide Alignment & Size', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<fieldset>
											<label><input type="checkbox" name="config[layout][centered_slides]" value="1" <?php checked( $config['layout']['centered_slides'] ); ?> /> <?php esc_html_e( 'Centered Slides (Active slide centered with adjacent slides partially visible)', 'wpcalibrate-slider-carousel' ); ?></label><br />
											<label><input type="checkbox" name="config[layout][auto_height]" value="1" <?php checked( $config['layout']['auto_height'] ); ?> /> <?php esc_html_e( 'Auto Height (Adapt slider height to current active slide)', 'wpcalibrate-slider-carousel' ); ?></label><br />
											<label><input type="checkbox" name="config[layout][full_width]" value="1" <?php checked( $config['layout']['full_width'] ); ?> /> <?php esc_html_e( 'Full Width (Break out of container to viewport edge)', 'wpcalibrate-slider-carousel' ); ?></label>
										</fieldset>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="layout_aspect_ratio"><?php esc_html_e( 'Aspect Ratio', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<select name="config[layout][aspect_ratio]" id="layout_aspect_ratio">
											<option value="16/9" <?php selected( $config['layout']['aspect_ratio'], '16/9' ); ?>>16:9 (Standard Widescreen)</option>
											<option value="21/9" <?php selected( $config['layout']['aspect_ratio'], '21/9' ); ?>>21:9 (Ultrawide Hero)</option>
											<option value="4/3" <?php selected( $config['layout']['aspect_ratio'], '4/3' ); ?>>4:3 (Classic Box)</option>
											<option value="1/1" <?php selected( $config['layout']['aspect_ratio'], '1/1' ); ?>>1:1 (Square Product)</option>
											<option value="auto" <?php selected( $config['layout']['aspect_ratio'], 'auto' ); ?>><?php esc_html_e( 'Auto / Natural Image Height', 'wpcalibrate-slider-carousel' ); ?></option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- TAB 4: Controls & Autoplay -->
					<div id="tab-controls" class="wpcalsc-tab-panel <?php echo 'controls' === $active_tab ? 'is-active' : ''; ?>">
						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'Navigation Controls', 'wpcalibrate-slider-carousel' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Navigation Arrows', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<label><input type="checkbox" name="config[controls][arrows]" value="1" <?php checked( $config['controls']['arrows'] ); ?> /> <?php esc_html_e( 'Show Next/Previous Arrows', 'wpcalibrate-slider-carousel' ); ?></label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Pagination', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<label><input type="checkbox" name="config[controls][pagination]" value="1" <?php checked( $config['controls']['pagination'] ); ?> /> <?php esc_html_e( 'Show Pagination Indicator', 'wpcalibrate-slider-carousel' ); ?></label>
										<div style="margin-top: 8px;">
											<label for="pagination_type"><?php esc_html_e( 'Type: ', 'wpcalibrate-slider-carousel' ); ?></label>
											<select name="config[controls][pagination_type]" id="pagination_type">
												<option value="bullets" <?php selected( $config['controls']['pagination_type'], 'bullets' ); ?>><?php esc_html_e( 'Dots / Bullets', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="fraction" <?php selected( $config['controls']['pagination_type'], 'fraction' ); ?>><?php esc_html_e( 'Fraction (1 / 5)', 'wpcalibrate-slider-carousel' ); ?></option>
												<option value="progressbar" <?php selected( $config['controls']['pagination_type'], 'progressbar' ); ?>><?php esc_html_e( 'Progress Bar', 'wpcalibrate-slider-carousel' ); ?></option>
											</select>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Thumbnails Navigation', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<label><input type="checkbox" name="config[controls][thumbnail_nav]" value="1" <?php checked( $config['controls']['thumbnail_nav'] ); ?> /> <?php esc_html_e( 'Enable Synchronized Thumbnail Slider Below Main Slider', 'wpcalibrate-slider-carousel' ); ?></label>
									</td>
								</tr>
							</table>
						</div>

						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'Autoplay & Motion Settings', 'wpcalibrate-slider-carousel' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Autoplay', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<label><input type="checkbox" name="config[autoplay][enabled]" value="1" <?php checked( $config['autoplay']['enabled'] ); ?> /> <?php esc_html_e( 'Enable Autoplay', 'wpcalibrate-slider-carousel' ); ?></label>
										<div style="margin-top: 8px;">
											<label><?php esc_html_e( 'Delay (ms): ', 'wpcalibrate-slider-carousel' ); ?><input type="number" name="config[autoplay][delay]" value="<?php echo esc_attr( (string) $config['autoplay']['delay'] ); ?>" step="500" min="1000" max="60000" class="small-text" /></label>
											<label style="margin-left: 15px;"><input type="checkbox" name="config[autoplay][pause_on_hover]" value="1" <?php checked( $config['autoplay']['pause_on_hover'] ); ?> /> <?php esc_html_e( 'Pause on Hover', 'wpcalibrate-slider-carousel' ); ?></label>
											<label style="margin-left: 15px;"><input type="checkbox" name="config[autoplay][pause_on_interaction]" value="1" <?php checked( $config['autoplay']['pause_on_interaction'] ); ?> /> <?php esc_html_e( 'Pause after Touch/Interaction', 'wpcalibrate-slider-carousel' ); ?></label>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="motion_effect"><?php esc_html_e( 'Transition Effect', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<select name="config[motion][effect]" id="motion_effect">
											<option value="slide" <?php selected( $config['motion']['effect'], 'slide' ); ?>><?php esc_html_e( 'Slide (Standard)', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="fade" <?php selected( $config['motion']['effect'], 'fade' ); ?>><?php esc_html_e( 'Fade', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="coverflow" <?php selected( $config['motion']['effect'], 'coverflow' ); ?>><?php esc_html_e( 'Coverflow (3D)', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="flip" <?php selected( $config['motion']['effect'], 'flip' ); ?>><?php esc_html_e( 'Flip', 'wpcalibrate-slider-carousel' ); ?></option>
											<option value="cards" <?php selected( $config['motion']['effect'], 'cards' ); ?>><?php esc_html_e( 'Cards', 'wpcalibrate-slider-carousel' ); ?></option>
										</select>
										<label style="margin-left: 15px;"><?php esc_html_e( 'Speed (ms): ', 'wpcalibrate-slider-carousel' ); ?><input type="number" name="config[motion][speed]" value="<?php echo esc_attr( (string) $config['motion']['speed'] ); ?>" step="50" min="100" max="3000" class="small-text" /></label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Behavior & Interaction', 'wpcalibrate-slider-carousel' ); ?></th>
									<td>
										<fieldset>
											<label><input type="checkbox" name="config[motion][loop]" value="1" <?php checked( $config['motion']['loop'] ); ?> /> <?php esc_html_e( 'Infinite Loop', 'wpcalibrate-slider-carousel' ); ?></label><br />
											<label><input type="checkbox" name="config[motion][touch_swipe]" value="1" <?php checked( $config['motion']['touch_swipe'] ); ?> /> <?php esc_html_e( 'Touch / Swipe Gestures', 'wpcalibrate-slider-carousel' ); ?></label><br />
											<label><input type="checkbox" name="config[motion][keyboard]" value="1" <?php checked( $config['motion']['keyboard'] ); ?> /> <?php esc_html_e( 'Keyboard Navigation (Arrow Keys)', 'wpcalibrate-slider-carousel' ); ?></label><br />
											<label><input type="checkbox" name="config[motion][reduced_motion]" value="1" <?php checked( $config['motion']['reduced_motion'] ); ?> /> <?php esc_html_e( 'Respect prefers-reduced-motion (Disable autoplay & intense animations)', 'wpcalibrate-slider-carousel' ); ?></label>
										</fieldset>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- TAB 5: Style & Design -->
					<div id="tab-style" class="wpcalsc-tab-panel <?php echo 'style' === $active_tab ? 'is-active' : ''; ?>">
						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'Visual Design & Theming', 'wpcalibrate-slider-carousel' ); ?></h2>
							<table class="form-table">
								<tr>
									<th scope="row"><label for="style_border_radius"><?php esc_html_e( 'Corner Radius (px)', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<input type="number" name="config[style][border_radius]" id="style_border_radius" value="<?php echo esc_attr( (string) $config['style']['border_radius'] ); ?>" min="0" max="50" class="small-text" /> px
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="style_overlay_opacity"><?php esc_html_e( 'Hero Overlay Opacity', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<input type="number" step="0.05" min="0" max="1" name="config[style][overlay_opacity]" id="style_overlay_opacity" value="<?php echo esc_attr( (string) $config['style']['overlay_opacity'] ); ?>" class="small-text" />
										<p class="description"><?php esc_html_e( 'Dark scrim overlay opacity behind slide content (0.00 = transparent, 1.00 = solid black).', 'wpcalibrate-slider-carousel' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="style_arrow_color"><?php esc_html_e( 'Arrow Colors', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<label><?php esc_html_e( 'Icon: ', 'wpcalibrate-slider-carousel' ); ?><input type="text" name="config[style][arrow_color]" id="style_arrow_color" value="<?php echo esc_attr( $config['style']['arrow_color'] ); ?>" class="wpcalsc-color-input" /></label>
										<label style="margin-left: 15px;"><?php esc_html_e( 'Background: ', 'wpcalibrate-slider-carousel' ); ?><input type="text" name="config[style][arrow_bg]" value="<?php echo esc_attr( $config['style']['arrow_bg'] ); ?>" class="wpcalsc-color-input" /></label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="style_custom_class"><?php esc_html_e( 'Custom CSS Class', 'wpcalibrate-slider-carousel' ); ?></label></th>
									<td>
										<input type="text" name="config[style][custom_class]" id="style_custom_class" value="<?php echo esc_attr( $config['style']['custom_class'] ); ?>" class="regular-text" placeholder="my-custom-slider" />
										<p class="description"><?php esc_html_e( 'Additional class name applied to slider container for custom styling.', 'wpcalibrate-slider-carousel' ); ?></p>
									</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- TAB 6: Preview -->
					<?php if ( $slider_id > 0 ) : ?>
						<div id="tab-preview" class="wpcalsc-tab-panel <?php echo 'preview' === $active_tab ? 'is-active' : ''; ?>">
							<div class="wpcalsc-card">
								<h2><?php esc_html_e( 'Interactive Preview', 'wpcalibrate-slider-carousel' ); ?></h2>
								<p class="description"><?php esc_html_e( 'This live preview reflects the saved configuration in a real responsive Swiper container.', 'wpcalibrate-slider-carousel' ); ?></p>
								<div class="wpcalsc-preview-stage">
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo Renderer::render( $slider_id, [ 'echo' => false ] );
									?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</form>
		</div>

		<!-- Clean JS Templates for Dynamic Slide Creation (No Raw Template Conditionals) -->
		<!-- 1. Image Slide Template -->
		<script type="text/html" id="tmpl-wpcalsc-slide-image">
			<div class="wpcalsc-slide-card" data-index="{{data.index}}">
				<div class="wpcalsc-slide-card-header">
					<div class="wpcalsc-slide-card-header-left">
						<span class="wpcalsc-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-slider-carousel' ); ?>"></span>
						<span class="wpcalsc-slide-type-badge wpcalsc-badge-image"><?php esc_html_e( 'Image', 'wpcalibrate-slider-carousel' ); ?></span>
						<strong class="wpcalsc-slide-label"><?php esc_html_e( 'Slide #', 'wpcalibrate-slider-carousel' ); ?>{{data.displayIndex}}</strong>
					</div>
					<div class="wpcalsc-slide-card-actions">
						<button type="button" class="button-link wpcalsc-toggle-slide" title="<?php esc_attr_e( 'Collapse / Expand', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-arrow-up-alt2"></span>
						</button>
						<button type="button" class="button-link wpcalsc-dup-slide" title="<?php esc_attr_e( 'Duplicate Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-admin-page"></span>
						</button>
						<button type="button" class="button-link wpcalsc-delete-slide submitdelete" title="<?php esc_attr_e( 'Delete Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				</div>
				<div class="wpcalsc-slide-card-body">
					<input type="hidden" name="config[slides][{{data.index}}][type]" value="image" />

					<!-- Media Selector Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-format-image"></span>
							<strong><?php esc_html_e( 'Slide Image', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-media-row">
							<div class="wpcalsc-media-preview-box">
								<img src="" class="wpcalsc-preview-thumb wpcalsc-hidden" alt="" />
								<div class="wpcalsc-no-media-placeholder">
									<span class="dashicons dashicons-format-image"></span>
									<span><?php esc_html_e( 'No image selected', 'wpcalibrate-slider-carousel' ); ?></span>
								</div>
							</div>
							<div class="wpcalsc-media-controls">
								<input type="hidden" name="config[slides][{{data.index}}][image_id]" class="wpcalsc-media-id" value="0" />
								<input type="hidden" name="config[slides][{{data.index}}][image_url]" class="wpcalsc-media-url" value="" />
								<div class="wpcalsc-media-btn-group">
									<button type="button" class="button button-secondary wpcalsc-upload-media-btn">
										<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Choose Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
									<button type="button" class="button-link wpcalsc-remove-media-btn wpcalsc-hidden submitdelete">
										<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Remove Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
								</div>
								<p class="description"><?php esc_html_e( 'Choose high-resolution photos, illustrations, or graphics from your WordPress media library.', 'wpcalibrate-slider-carousel' ); ?></p>
							</div>
						</div>
					</div>

					<!-- Content Fields Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-content-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<strong><?php esc_html_e( 'Slide Content & Typography', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_heading"><?php esc_html_e( 'Slide Heading', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_heading" name="config[slides][{{data.index}}][heading]" class="widefat wpcalsc-slide-heading-input" value="" placeholder="<?php esc_attr_e( 'e.g. Elevate Your Workflow', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_pos"><?php esc_html_e( 'Content Position', 'wpcalibrate-slider-carousel' ); ?></label>
								<select id="slide_{{data.index}}_pos" name="config[slides][{{data.index}}][content_position]" class="widefat">
									<option value="center"><?php esc_html_e( 'Center', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-left"><?php esc_html_e( 'Center Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-right"><?php esc_html_e( 'Center Right', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-left"><?php esc_html_e( 'Bottom Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-center"><?php esc_html_e( 'Bottom Center', 'wpcalibrate-slider-carousel' ); ?></option>
								</select>
							</div>
						</div>

						<div class="wpcalsc-slide-field">
							<label for="slide_{{data.index}}_desc"><?php esc_html_e( 'Description / Text', 'wpcalibrate-slider-carousel' ); ?></label>
							<textarea id="slide_{{data.index}}_desc" name="config[slides][{{data.index}}][description]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Add an optional subtitle, description, or highlighted text for this slide...', 'wpcalibrate-slider-carousel' ); ?>"></textarea>
						</div>
					</div>

					<!-- Call to Action Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-cta-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-external"></span>
							<strong><?php esc_html_e( 'Call to Action Button', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_btn_text"><?php esc_html_e( 'Button Text (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_btn_text" name="config[slides][{{data.index}}][button_text]" class="widefat" value="" placeholder="<?php esc_attr_e( 'e.g. Learn More / Shop Now', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_btn_url"><?php esc_html_e( 'Button URL (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="url" id="slide_{{data.index}}_btn_url" name="config[slides][{{data.index}}][button_url]" class="widefat" value="" placeholder="https://..." />
							</div>
						</div>
					</div>
				</div>
			</div>
		</script>

		<!-- 2. Video Slide Template -->
		<script type="text/html" id="tmpl-wpcalsc-slide-video">
			<div class="wpcalsc-slide-card" data-index="{{data.index}}">
				<div class="wpcalsc-slide-card-header">
					<div class="wpcalsc-slide-card-header-left">
						<span class="wpcalsc-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-slider-carousel' ); ?>"></span>
						<span class="wpcalsc-slide-type-badge wpcalsc-badge-video"><?php esc_html_e( 'Video', 'wpcalibrate-slider-carousel' ); ?></span>
						<strong class="wpcalsc-slide-label"><?php esc_html_e( 'Slide #', 'wpcalibrate-slider-carousel' ); ?>{{data.displayIndex}}</strong>
					</div>
					<div class="wpcalsc-slide-card-actions">
						<button type="button" class="button-link wpcalsc-toggle-slide" title="<?php esc_attr_e( 'Collapse / Expand', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-arrow-up-alt2"></span>
						</button>
						<button type="button" class="button-link wpcalsc-dup-slide" title="<?php esc_attr_e( 'Duplicate Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-admin-page"></span>
						</button>
						<button type="button" class="button-link wpcalsc-delete-slide submitdelete" title="<?php esc_attr_e( 'Delete Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				</div>
				<div class="wpcalsc-slide-card-body">
					<input type="hidden" name="config[slides][{{data.index}}][type]" value="video" />

					<!-- Video Selector Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-video-alt3"></span>
							<strong><?php esc_html_e( 'Video Source', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_{{data.index}}_video_url"><?php esc_html_e( 'Video URL (Self-hosted MP4, WebM, or Media Library)', 'wpcalibrate-slider-carousel' ); ?></label>
							<div class="wpcalsc-input-button-group">
								<input type="url" id="slide_{{data.index}}_video_url" name="config[slides][{{data.index}}][video_url]" class="regular-text wpcalsc-video-url" value="" placeholder="https://example.com/video.mp4" />
								<button type="button" class="button button-secondary wpcalsc-upload-video-btn">
									<span class="dashicons dashicons-media-video"></span> <?php esc_html_e( 'Choose Video', 'wpcalibrate-slider-carousel' ); ?>
								</button>
							</div>
							<input type="hidden" name="config[slides][{{data.index}}][video_id]" class="wpcalsc-video-id" value="0" />
							<p class="description"><?php esc_html_e( 'Provide a direct link to an MP4 video or select an uploaded video from your WordPress Media Library.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

					<!-- Content Fields Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-content-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<strong><?php esc_html_e( 'Overlay Content & Typography', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_vheading"><?php esc_html_e( 'Slide Heading', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_vheading" name="config[slides][{{data.index}}][heading]" class="widefat wpcalsc-slide-heading-input" value="" placeholder="<?php esc_attr_e( 'e.g. Featured Video Showcase', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_vpos"><?php esc_html_e( 'Content Position', 'wpcalibrate-slider-carousel' ); ?></label>
								<select id="slide_{{data.index}}_vpos" name="config[slides][{{data.index}}][content_position]" class="widefat">
									<option value="center"><?php esc_html_e( 'Center', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-left"><?php esc_html_e( 'Center Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-right"><?php esc_html_e( 'Center Right', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-left"><?php esc_html_e( 'Bottom Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-center"><?php esc_html_e( 'Bottom Center', 'wpcalibrate-slider-carousel' ); ?></option>
								</select>
							</div>
						</div>

						<div class="wpcalsc-slide-field">
							<label for="slide_{{data.index}}_vdesc"><?php esc_html_e( 'Description / Text', 'wpcalibrate-slider-carousel' ); ?></label>
							<textarea id="slide_{{data.index}}_vdesc" name="config[slides][{{data.index}}][description]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Add optional video caption or descriptive text...', 'wpcalibrate-slider-carousel' ); ?>"></textarea>
						</div>
					</div>

					<!-- Call to Action Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-cta-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-external"></span>
							<strong><?php esc_html_e( 'Call to Action Button', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_vbtn_text"><?php esc_html_e( 'Button Text (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_vbtn_text" name="config[slides][{{data.index}}][button_text]" class="widefat" value="" placeholder="<?php esc_attr_e( 'e.g. Watch Full Story', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_vbtn_url"><?php esc_html_e( 'Button URL (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="url" id="slide_{{data.index}}_vbtn_url" name="config[slides][{{data.index}}][button_url]" class="widefat" value="" placeholder="https://..." />
							</div>
						</div>
					</div>
				</div>
			</div>
		</script>

		<!-- 3. Content / HTML Slide Template -->
		<script type="text/html" id="tmpl-wpcalsc-slide-content">
			<div class="wpcalsc-slide-card" data-index="{{data.index}}">
				<div class="wpcalsc-slide-card-header">
					<div class="wpcalsc-slide-card-header-left">
						<span class="wpcalsc-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-slider-carousel' ); ?>"></span>
						<span class="wpcalsc-slide-type-badge wpcalsc-badge-content"><?php esc_html_e( 'Content', 'wpcalibrate-slider-carousel' ); ?></span>
						<strong class="wpcalsc-slide-label"><?php esc_html_e( 'Slide #', 'wpcalibrate-slider-carousel' ); ?>{{data.displayIndex}}</strong>
					</div>
					<div class="wpcalsc-slide-card-actions">
						<button type="button" class="button-link wpcalsc-toggle-slide" title="<?php esc_attr_e( 'Collapse / Expand', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-arrow-up-alt2"></span>
						</button>
						<button type="button" class="button-link wpcalsc-dup-slide" title="<?php esc_attr_e( 'Duplicate Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-admin-page"></span>
						</button>
						<button type="button" class="button-link wpcalsc-delete-slide submitdelete" title="<?php esc_attr_e( 'Delete Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				</div>
				<div class="wpcalsc-slide-card-body">
					<input type="hidden" name="config[slides][{{data.index}}][type]" value="content" />

					<!-- Custom HTML Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-html"></span>
							<strong><?php esc_html_e( 'Custom HTML / Rich Layout', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_{{data.index}}_content_html"><?php esc_html_e( 'Custom HTML or Content', 'wpcalibrate-slider-carousel' ); ?></label>
							<textarea id="slide_{{data.index}}_content_html" name="config[slides][{{data.index}}][content_html]" rows="5" class="widefat" placeholder="<?php esc_attr_e( 'Enter custom HTML markup, shortcodes, or styled content...', 'wpcalibrate-slider-carousel' ); ?>"></textarea>
							<p class="description"><?php esc_html_e( 'Full freedom to embed custom layouts, banners, testimonial quotes, or inline badges.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

					<!-- Content Fields Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-content-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<strong><?php esc_html_e( 'Header & Alignment', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_cheading"><?php esc_html_e( 'Slide Heading (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_cheading" name="config[slides][{{data.index}}][heading]" class="widefat wpcalsc-slide-heading-input" value="" placeholder="<?php esc_attr_e( 'e.g. Client Testimonial', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_cpos"><?php esc_html_e( 'Content Position', 'wpcalibrate-slider-carousel' ); ?></label>
								<select id="slide_{{data.index}}_cpos" name="config[slides][{{data.index}}][content_position]" class="widefat">
									<option value="center"><?php esc_html_e( 'Center', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-left"><?php esc_html_e( 'Center Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-right"><?php esc_html_e( 'Center Right', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-left"><?php esc_html_e( 'Bottom Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-center"><?php esc_html_e( 'Bottom Center', 'wpcalibrate-slider-carousel' ); ?></option>
								</select>
							</div>
						</div>
					</div>

					<!-- Call to Action Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-cta-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-external"></span>
							<strong><?php esc_html_e( 'Call to Action Button', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_cbtn_text"><?php esc_html_e( 'Button Text (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_cbtn_text" name="config[slides][{{data.index}}][button_text]" class="widefat" value="" placeholder="<?php esc_attr_e( 'e.g. Read Case Study', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_cbtn_url"><?php esc_html_e( 'Button URL (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="url" id="slide_{{data.index}}_cbtn_url" name="config[slides][{{data.index}}][button_url]" class="widefat" value="" placeholder="https://..." />
							</div>
						</div>
					</div>
				</div>
			</div>
		</script>

		<!-- 4. Default Fallback Template (Aliased to Image Slide) -->
		<script type="text/html" id="tmpl-wpcalsc-slide-item">
			<div class="wpcalsc-slide-card" data-index="{{data.index}}">
				<div class="wpcalsc-slide-card-header">
					<div class="wpcalsc-slide-card-header-left">
						<span class="wpcalsc-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-slider-carousel' ); ?>"></span>
						<span class="wpcalsc-slide-type-badge wpcalsc-badge-image"><?php esc_html_e( 'Image', 'wpcalibrate-slider-carousel' ); ?></span>
						<strong class="wpcalsc-slide-label"><?php esc_html_e( 'Slide #', 'wpcalibrate-slider-carousel' ); ?>{{data.displayIndex}}</strong>
					</div>
					<div class="wpcalsc-slide-card-actions">
						<button type="button" class="button-link wpcalsc-toggle-slide" title="<?php esc_attr_e( 'Collapse / Expand', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-arrow-up-alt2"></span>
						</button>
						<button type="button" class="button-link wpcalsc-dup-slide" title="<?php esc_attr_e( 'Duplicate Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-admin-page"></span>
						</button>
						<button type="button" class="button-link wpcalsc-delete-slide submitdelete" title="<?php esc_attr_e( 'Delete Slide', 'wpcalibrate-slider-carousel' ); ?>">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				</div>
				<div class="wpcalsc-slide-card-body">
					<input type="hidden" name="config[slides][{{data.index}}][type]" value="image" />

					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-format-image"></span>
							<strong><?php esc_html_e( 'Slide Image', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-media-row">
							<div class="wpcalsc-media-preview-box">
								<img src="" class="wpcalsc-preview-thumb wpcalsc-hidden" alt="" />
								<div class="wpcalsc-no-media-placeholder">
									<span class="dashicons dashicons-format-image"></span>
									<span><?php esc_html_e( 'No image selected', 'wpcalibrate-slider-carousel' ); ?></span>
								</div>
							</div>
							<div class="wpcalsc-media-controls">
								<input type="hidden" name="config[slides][{{data.index}}][image_id]" class="wpcalsc-media-id" value="0" />
								<input type="hidden" name="config[slides][{{data.index}}][image_url]" class="wpcalsc-media-url" value="" />
								<div class="wpcalsc-media-btn-group">
									<button type="button" class="button button-secondary wpcalsc-upload-media-btn">
										<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Choose Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
									<button type="button" class="button-link wpcalsc-remove-media-btn wpcalsc-hidden submitdelete">
										<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Remove Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
								</div>
								<p class="description"><?php esc_html_e( 'Choose high-resolution photos, illustrations, or graphics from your WordPress media library.', 'wpcalibrate-slider-carousel' ); ?></p>
							</div>
						</div>
					</div>

					<div class="wpcalsc-slide-section wpcalsc-slide-content-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<strong><?php esc_html_e( 'Slide Content & Typography', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_fheading"><?php esc_html_e( 'Slide Heading', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_fheading" name="config[slides][{{data.index}}][heading]" class="widefat wpcalsc-slide-heading-input" value="" placeholder="<?php esc_attr_e( 'e.g. Elevate Your Workflow', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_fpos"><?php esc_html_e( 'Content Position', 'wpcalibrate-slider-carousel' ); ?></label>
								<select id="slide_{{data.index}}_fpos" name="config[slides][{{data.index}}][content_position]" class="widefat">
									<option value="center"><?php esc_html_e( 'Center', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-left"><?php esc_html_e( 'Center Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="center-right"><?php esc_html_e( 'Center Right', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-left"><?php esc_html_e( 'Bottom Left', 'wpcalibrate-slider-carousel' ); ?></option>
									<option value="bottom-center"><?php esc_html_e( 'Bottom Center', 'wpcalibrate-slider-carousel' ); ?></option>
								</select>
							</div>
						</div>

						<div class="wpcalsc-slide-field">
							<label for="slide_{{data.index}}_fdesc"><?php esc_html_e( 'Description / Text', 'wpcalibrate-slider-carousel' ); ?></label>
							<textarea id="slide_{{data.index}}_fdesc" name="config[slides][{{data.index}}][description]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Add an optional subtitle, description, or highlighted text for this slide...', 'wpcalibrate-slider-carousel' ); ?>"></textarea>
						</div>
					</div>

					<div class="wpcalsc-slide-section wpcalsc-slide-cta-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-external"></span>
							<strong><?php esc_html_e( 'Call to Action Button', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-grid-2">
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_fbtn_text"><?php esc_html_e( 'Button Text (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="text" id="slide_{{data.index}}_fbtn_text" name="config[slides][{{data.index}}][button_text]" class="widefat" value="" placeholder="<?php esc_attr_e( 'e.g. Learn More / Shop Now', 'wpcalibrate-slider-carousel' ); ?>" />
							</div>
							<div class="wpcalsc-slide-field">
								<label for="slide_{{data.index}}_fbtn_url"><?php esc_html_e( 'Button URL (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
								<input type="url" id="slide_{{data.index}}_fbtn_url" name="config[slides][{{data.index}}][button_url]" class="widefat" value="" placeholder="https://..." />
							</div>
						</div>
					</div>
				</div>
			</div>
		</script>
		<?php
	}

	/**
	 * Helper to render an existing slide item inside the editor.
	 *
	 * @param int   $index Slide index.
	 * @param array $slide Slide data.
	 */
	private static function render_slide_item_form( int $index, array $slide ): void {
		$type             = $slide['type'] ?? 'image';
		$image_id         = (int) ( $slide['image_id'] ?? 0 );
		$image_url        = (string) ( $slide['image_url'] ?? '' );
		$heading          = (string) ( $slide['heading'] ?? '' );
		$description      = (string) ( $slide['description'] ?? '' );
		$button_text      = (string) ( $slide['button_text'] ?? '' );
		$button_url       = (string) ( $slide['button_url'] ?? '' );
		$content_position = (string) ( $slide['content_position'] ?? 'center' );
		$content_html     = (string) ( $slide['content_html'] ?? '' );
		$badge_class      = match ( $type ) {
			'video'   => 'wpcalsc-badge-video',
			'content' => 'wpcalsc-badge-content',
			default   => 'wpcalsc-badge-image',
		};
		?>
		<div class="wpcalsc-slide-card" data-index="<?php echo esc_attr( (string) $index ); ?>">
			<div class="wpcalsc-slide-card-header">
				<div class="wpcalsc-slide-card-header-left">
					<span class="wpcalsc-drag-handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wpcalibrate-slider-carousel' ); ?>"></span>
					<span class="wpcalsc-slide-type-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( ucfirst( $type ) ); ?></span>
					<strong class="wpcalsc-slide-label"><?php echo esc_html( ! empty( $heading ) ? sprintf( 'Slide #%d: %s', $index + 1, $heading ) : sprintf( __( 'Slide #%d', 'wpcalibrate-slider-carousel' ), $index + 1 ) ); ?></strong>
				</div>
				<div class="wpcalsc-slide-card-actions">
					<button type="button" class="button-link wpcalsc-toggle-slide" title="<?php esc_attr_e( 'Collapse / Expand', 'wpcalibrate-slider-carousel' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
					<button type="button" class="button-link wpcalsc-dup-slide" title="<?php esc_attr_e( 'Duplicate Slide', 'wpcalibrate-slider-carousel' ); ?>">
						<span class="dashicons dashicons-admin-page"></span>
					</button>
					<button type="button" class="button-link wpcalsc-delete-slide submitdelete" title="<?php esc_attr_e( 'Delete Slide', 'wpcalibrate-slider-carousel' ); ?>">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>
			</div>
			<div class="wpcalsc-slide-card-body">
				<input type="hidden" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][type]" value="<?php echo esc_attr( $type ); ?>" />

				<?php if ( 'image' === $type ) : ?>
					<!-- Image Media Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-format-image"></span>
							<strong><?php esc_html_e( 'Slide Image', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-media-row">
							<div class="wpcalsc-media-preview-box">
								<?php if ( ! empty( $image_url ) ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" class="wpcalsc-preview-thumb" alt="" />
								<?php else : ?>
									<div class="wpcalsc-no-media-placeholder">
										<span class="dashicons dashicons-format-image"></span>
										<span><?php esc_html_e( 'No image selected', 'wpcalibrate-slider-carousel' ); ?></span>
									</div>
								<?php endif; ?>
							</div>
							<div class="wpcalsc-media-controls">
								<input type="hidden" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][image_id]" class="wpcalsc-media-id" value="<?php echo esc_attr( (string) $image_id ); ?>" />
								<input type="hidden" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][image_url]" class="wpcalsc-media-url" value="<?php echo esc_url( $image_url ); ?>" />
								<div class="wpcalsc-media-btn-group">
									<button type="button" class="button button-secondary wpcalsc-upload-media-btn">
										<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Choose Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
									<button type="button" class="button-link wpcalsc-remove-media-btn <?php echo empty( $image_url ) ? 'wpcalsc-hidden' : ''; ?> submitdelete">
										<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Remove Image', 'wpcalibrate-slider-carousel' ); ?>
									</button>
								</div>
								<p class="description"><?php esc_html_e( 'Choose high-resolution photos, illustrations, or graphics from your WordPress media library.', 'wpcalibrate-slider-carousel' ); ?></p>
							</div>
						</div>
					</div>

				<?php elseif ( 'video' === $type ) : ?>
					<!-- Video Source Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-video-alt3"></span>
							<strong><?php esc_html_e( 'Video Source', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_video_url"><?php esc_html_e( 'Video URL (Self-hosted MP4, WebM, or Media Library)', 'wpcalibrate-slider-carousel' ); ?></label>
							<div class="wpcalsc-input-button-group">
								<input type="url" id="slide_<?php echo esc_attr( (string) $index ); ?>_video_url" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][video_url]" class="regular-text wpcalsc-video-url" value="<?php echo esc_url( (string) ( $slide['video_url'] ?? '' ) ); ?>" placeholder="https://example.com/video.mp4" />
								<button type="button" class="button button-secondary wpcalsc-upload-video-btn">
									<span class="dashicons dashicons-media-video"></span> <?php esc_html_e( 'Choose Video', 'wpcalibrate-slider-carousel' ); ?>
								</button>
							</div>
							<input type="hidden" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][video_id]" class="wpcalsc-video-id" value="<?php echo esc_attr( (string) ( $slide['video_id'] ?? 0 ) ); ?>" />
							<p class="description"><?php esc_html_e( 'Provide a direct link to an MP4 video or select an uploaded video from your WordPress Media Library.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>

				<?php elseif ( 'content' === $type ) : ?>
					<!-- Content / HTML Section -->
					<div class="wpcalsc-slide-section wpcalsc-slide-media-section">
						<div class="wpcalsc-slide-section-title">
							<span class="dashicons dashicons-html"></span>
							<strong><?php esc_html_e( 'Custom HTML / Rich Layout', 'wpcalibrate-slider-carousel' ); ?></strong>
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_content_html"><?php esc_html_e( 'Custom HTML or Content', 'wpcalibrate-slider-carousel' ); ?></label>
							<textarea id="slide_<?php echo esc_attr( (string) $index ); ?>_content_html" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][content_html]" rows="5" class="widefat" placeholder="<?php esc_attr_e( 'Enter custom HTML markup, shortcodes, or styled content...', 'wpcalibrate-slider-carousel' ); ?>"><?php echo esc_textarea( $content_html ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Full freedom to embed custom layouts, banners, testimonial quotes, or inline badges.', 'wpcalibrate-slider-carousel' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<!-- Content Fields Section -->
				<div class="wpcalsc-slide-section wpcalsc-slide-content-section">
					<div class="wpcalsc-slide-section-title">
						<span class="dashicons dashicons-editor-textcolor"></span>
						<strong><?php esc_html_e( 'Slide Content & Typography', 'wpcalibrate-slider-carousel' ); ?></strong>
					</div>
					<div class="wpcalsc-slide-grid-2">
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_heading"><?php esc_html_e( 'Slide Heading', 'wpcalibrate-slider-carousel' ); ?></label>
							<input type="text" id="slide_<?php echo esc_attr( (string) $index ); ?>_heading" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][heading]" class="widefat wpcalsc-slide-heading-input" value="<?php echo esc_attr( $heading ); ?>" placeholder="<?php esc_attr_e( 'e.g. Elevate Your Workflow', 'wpcalibrate-slider-carousel' ); ?>" />
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_pos"><?php esc_html_e( 'Content Position', 'wpcalibrate-slider-carousel' ); ?></label>
							<select id="slide_<?php echo esc_attr( (string) $index ); ?>_pos" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][content_position]" class="widefat">
								<option value="center" <?php selected( $content_position, 'center' ); ?>><?php esc_html_e( 'Center', 'wpcalibrate-slider-carousel' ); ?></option>
								<option value="center-left" <?php selected( $content_position, 'center-left' ); ?>><?php esc_html_e( 'Center Left', 'wpcalibrate-slider-carousel' ); ?></option>
								<option value="center-right" <?php selected( $content_position, 'center-right' ); ?>><?php esc_html_e( 'Center Right', 'wpcalibrate-slider-carousel' ); ?></option>
								<option value="bottom-left" <?php selected( $content_position, 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'wpcalibrate-slider-carousel' ); ?></option>
								<option value="bottom-center" <?php selected( $content_position, 'bottom-center' ); ?>><?php esc_html_e( 'Bottom Center', 'wpcalibrate-slider-carousel' ); ?></option>
							</select>
						</div>
					</div>

					<div class="wpcalsc-slide-field">
						<label for="slide_<?php echo esc_attr( (string) $index ); ?>_desc"><?php esc_html_e( 'Description / Text', 'wpcalibrate-slider-carousel' ); ?></label>
						<textarea id="slide_<?php echo esc_attr( (string) $index ); ?>_desc" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][description]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Add an optional subtitle, description, or highlighted text for this slide...', 'wpcalibrate-slider-carousel' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
					</div>
				</div>

				<!-- Call to Action Section -->
				<div class="wpcalsc-slide-section wpcalsc-slide-cta-section">
					<div class="wpcalsc-slide-section-title">
						<span class="dashicons dashicons-external"></span>
						<strong><?php esc_html_e( 'Call to Action Button', 'wpcalibrate-slider-carousel' ); ?></strong>
					</div>
					<div class="wpcalsc-slide-grid-2">
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_btn_text"><?php esc_html_e( 'Button Text (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
							<input type="text" id="slide_<?php echo esc_attr( (string) $index ); ?>_btn_text" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][button_text]" class="widefat" value="<?php echo esc_attr( $button_text ); ?>" placeholder="<?php esc_attr_e( 'e.g. Learn More / Shop Now', 'wpcalibrate-slider-carousel' ); ?>" />
						</div>
						<div class="wpcalsc-slide-field">
							<label for="slide_<?php echo esc_attr( (string) $index ); ?>_btn_url"><?php esc_html_e( 'Button URL (Optional)', 'wpcalibrate-slider-carousel' ); ?></label>
							<input type="url" id="slide_<?php echo esc_attr( (string) $index ); ?>_btn_url" name="config[slides][<?php echo esc_attr( (string) $index ); ?>][button_url]" class="widefat" value="<?php echo esc_url( $button_url ); ?>" placeholder="https://..." />
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle standard POST save.
	 */
	public static function handle_save(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'Unauthorized user.', 'wpcalibrate-slider-carousel' ) );
		}

		check_admin_referer( 'wpcalsc_save_slider_action', 'wpcalsc_slider_nonce' );

		$slider_id = isset( $_POST['slider_id'] ) ? absint( $_POST['slider_id'] ) : 0;
		$title     = isset( $_POST['slider_title'] ) ? sanitize_text_field( wp_unslash( $_POST['slider_title'] ) ) : '';
		$status    = isset( $_POST['slider_status'] ) ? sanitize_key( wp_unslash( $_POST['slider_status'] ) ) : 'publish';
		$config    = isset( $_POST['config'] ) && is_array( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : [];

		$result = SliderRepository::save( $slider_id, $title, $config, $status );

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		$redirect_url = add_query_arg(
			[
				'page'      => Menu::PAGE_ADD_NEW,
				'slider_id' => $result,
				'saved'     => '1',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle AJAX save.
	 */
	public static function handle_save_ajax(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'wpcalibrate-slider-carousel' ) ], 403 );
		}

		check_ajax_referer( 'wpcalsc_admin_nonce', 'nonce' );

		$slider_id = isset( $_POST['slider_id'] ) ? absint( $_POST['slider_id'] ) : 0;
		$title     = isset( $_POST['slider_title'] ) ? sanitize_text_field( wp_unslash( $_POST['slider_title'] ) ) : '';
		$status    = isset( $_POST['slider_status'] ) ? sanitize_key( wp_unslash( $_POST['slider_status'] ) ) : 'publish';
		$config    = isset( $_POST['config'] ) && is_array( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : [];

		$result = SliderRepository::save( $slider_id, $title, $config, $status );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ] );
		}

		wp_send_json_success(
			[
				'slider_id' => $result,
				'message'   => __( 'Slider saved successfully.', 'wpcalibrate-slider-carousel' ),
			]
		);
	}
}
