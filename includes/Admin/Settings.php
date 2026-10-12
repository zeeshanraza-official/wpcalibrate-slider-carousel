<?php
/**
 * Plugin Settings, Licensing, and Support Screens.
 *
 * @package WPCalibrate\SliderCarousel\Admin
 */

declare(strict_types=1);

namespace WPCalibrate\SliderCarousel\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPCalibrate\SliderCarousel\Support\Capabilities;
use WPCalibrate\SliderCarousel\Support\BrandingHelper;
use WPCalibrate\SliderCarousel\Licensing\LicenseManager;
use WPCalibrate\SliderCarousel\Integrations\WooCommerce;

/**
 * Settings class.
 */
class Settings {

	/**
	 * Settings option name.
	 */
	public const OPTION_NAME = 'wpcalsc_settings';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'admin_post_wpcalsc_save_settings', [ self::class, 'handle_save_settings' ] );
		add_action( 'admin_post_wpcalsc_save_license', [ self::class, 'handle_save_license' ] );
		add_action( 'admin_post_wpcalsc_clear_cache', [ self::class, 'handle_clear_cache' ] );
	}

	/**
	 * Get all plugin settings with defaults.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$defaults = [
			'default_speed'        => 500,
			'default_navigation'   => 'both',
			'delete_on_uninstall'  => 0,
			'lazy_load'            => 1,
			'enable_cache'         => 1,
			'cache_duration'       => 3600,
			'debug_logging'        => 0,
		];

		$saved = get_option( self::OPTION_NAME, [] );
		return wp_parse_args( is_array( $saved ) ? $saved : [], $defaults );
	}

	/**
	 * Render Settings Screen.
	 */
	public static function render(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpcalibrate-slider-carousel' ) );
		}

		$settings   = self::get_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
		?>
		<div class="wrap wpcalsc-admin-wrap">
			<div class="wpcalsc-header">
				<div class="wpcalsc-branding">
					<img src="<?php echo esc_url( BrandingHelper::get_icon_dark_url() ); ?>" alt="WPCalibrate" class="wpcalsc-header-logo" />
					<div>
						<h1><?php esc_html_e( 'Slider & Carousel Settings', 'wpcalibrate-slider-carousel' ); ?></h1>
						<p class="wpcalsc-tagline"><?php esc_html_e( 'Configure global defaults, caching, integrations, and lifecycle preferences.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				</div>
			</div>

			<?php if ( isset( $_GET['saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved successfully!', 'wpcalibrate-slider-carousel' ); ?></p></div>
			<?php elseif ( isset( $_GET['cache_cleared'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Slider transient caches cleared successfully!', 'wpcalibrate-slider-carousel' ); ?></p></div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper wpcalsc-nav-tabs">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=overview' ) ); ?>" class="nav-tab <?php echo 'overview' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Overview', 'wpcalibrate-slider-carousel' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=general' ) ); ?>" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General', 'wpcalibrate-slider-carousel' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=performance' ) ); ?>" class="nav-tab <?php echo 'performance' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-performance"></span> <?php esc_html_e( 'Performance', 'wpcalibrate-slider-carousel' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=integrations' ) ); ?>" class="nav-tab <?php echo 'integrations' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e( 'Integrations', 'wpcalibrate-slider-carousel' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Menu::PAGE_SETTINGS . '&tab=advanced' ) ); ?>" class="nav-tab <?php echo 'advanced' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e( 'Advanced', 'wpcalibrate-slider-carousel' ); ?>
				</a>
			</nav>

			<?php if ( 'overview' === $active_tab ) : ?>
				<?php OverviewSection::render(); ?>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wpcalsc_save_settings" />
					<input type="hidden" name="current_tab" value="<?php echo esc_attr( $active_tab ); ?>" />
					<?php wp_nonce_field( 'wpcalsc_save_settings_action', 'wpcalsc_settings_nonce' ); ?>

					<?php if ( 'general' === $active_tab ) : ?>
						<div class="wpcalsc-card">
							<h2><?php esc_html_e( 'General Defaults', 'wpcalibrate-slider-carousel' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><label for="default_speed"><?php esc_html_e( 'Default Transition Speed', 'wpcalibrate-slider-carousel' ); ?></label></th>
								<td>
									<input type="number" name="settings[default_speed]" id="default_speed" value="<?php echo esc_attr( (string) $settings['default_speed'] ); ?>" min="100" max="3000" step="50" class="small-text" /> ms
									<p class="description"><?php esc_html_e( 'Default animation duration applied to newly created sliders.', 'wpcalibrate-slider-carousel' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="default_navigation"><?php esc_html_e( 'Default Navigation', 'wpcalibrate-slider-carousel' ); ?></label></th>
								<td>
									<select name="settings[default_navigation]" id="default_navigation">
										<option value="both" <?php selected( $settings['default_navigation'], 'both' ); ?>><?php esc_html_e( 'Arrows and Dots (Both)', 'wpcalibrate-slider-carousel' ); ?></option>
										<option value="arrows" <?php selected( $settings['default_navigation'], 'arrows' ); ?>><?php esc_html_e( 'Arrows Only', 'wpcalibrate-slider-carousel' ); ?></option>
										<option value="dots" <?php selected( $settings['default_navigation'], 'dots' ); ?>><?php esc_html_e( 'Dots Only', 'wpcalibrate-slider-carousel' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Uninstall Data Retention', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="settings[delete_on_uninstall]" value="1" <?php checked( ! empty( $settings['delete_on_uninstall'] ) ); ?> />
										<strong style="color: #d63638;"><?php esc_html_e( 'Delete plugin data on uninstall', 'wpcalibrate-slider-carousel' ); ?></strong>
									</label>
									<p class="description"><?php esc_html_e( 'By default, uninstall preserves your sliders and settings. Check this option only if you wish to completely wipe all slider post data and settings when uninstalling.', 'wpcalibrate-slider-carousel' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

				<?php elseif ( 'performance' === $active_tab ) : ?>
					<div class="wpcalsc-card">
						<h2><?php esc_html_e( 'Performance & Optimization', 'wpcalibrate-slider-carousel' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Lazy-Load Images', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="settings[lazy_load]" value="1" <?php checked( ! empty( $settings['lazy_load'] ) ); ?> />
										<?php esc_html_e( 'Enable native browser lazy loading for off-screen slides', 'wpcalibrate-slider-carousel' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Slider Caching', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="settings[enable_cache]" value="1" <?php checked( ! empty( $settings['enable_cache'] ) ); ?> />
										<?php esc_html_e( 'Enable transient caching for dynamic query slides', 'wpcalibrate-slider-carousel' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="cache_duration"><?php esc_html_e( 'Cache Duration', 'wpcalibrate-slider-carousel' ); ?></label></th>
								<td>
									<input type="number" name="settings[cache_duration]" id="cache_duration" value="<?php echo esc_attr( (string) $settings['cache_duration'] ); ?>" min="60" max="86400" step="300" class="small-text" /> <?php esc_html_e( 'seconds', 'wpcalibrate-slider-carousel' ); ?>
								</td>
							</tr>
						</table>
					</div>

				<?php elseif ( 'integrations' === $active_tab ) : ?>
					<div class="wpcalsc-card">
						<h2><?php esc_html_e( 'Third-Party Integrations & Environment', 'wpcalibrate-slider-carousel' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'WooCommerce', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<?php if ( WooCommerce::is_active() ) : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-published"><?php esc_html_e( 'Active & Connected', 'wpcalibrate-slider-carousel' ); ?></span>
										<p class="description"><?php esc_html_e( 'WooCommerce product carousels are enabled. High-Performance Order Storage (HPOS) compatibility declared.', 'wpcalibrate-slider-carousel' ); ?></p>
									<?php else : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-draft"><?php esc_html_e( 'Not Detected', 'wpcalibrate-slider-carousel' ); ?></span>
										<p class="description"><?php esc_html_e( 'WooCommerce is not installed or inactive. Product carousel features are gracefully disabled.', 'wpcalibrate-slider-carousel' ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Elementor Page Builder', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<?php if ( did_action( 'elementor/loaded' ) ) : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-published"><?php esc_html_e( 'Active & Connected', 'wpcalibrate-slider-carousel' ); ?></span>
										<p class="description"><?php esc_html_e( 'WPCalibrate Slider widget is available in the Elementor panel.', 'wpcalibrate-slider-carousel' ); ?></p>
									<?php else : ?>
										<span class="wpcalsc-status-pill wpcalsc-status-draft"><?php esc_html_e( 'Not Detected', 'wpcalibrate-slider-carousel' ); ?></span>
										<p class="description"><?php esc_html_e( 'Elementor is not active. The plugin continues running normally using Gutenberg and Shortcodes.', 'wpcalibrate-slider-carousel' ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Swiper.js Engine', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<span class="wpcalsc-status-pill wpcalsc-status-published">Swiper 14.3.0</span>
									<p class="description"><?php esc_html_e( 'Bundled locally under MIT License. No third-party CDN dependency required.', 'wpcalibrate-slider-carousel' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

				<?php elseif ( 'advanced' === $active_tab ) : ?>
					<div class="wpcalsc-card">
						<h2><?php esc_html_e( 'Advanced & Debugging', 'wpcalibrate-slider-carousel' ); ?></h2>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Debug Logging', 'wpcalibrate-slider-carousel' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="settings[debug_logging]" value="1" <?php checked( ! empty( $settings['debug_logging'] ) ); ?> />
										<?php esc_html_e( 'Enable debug logging to WooCommerce log or WordPress debug.log (secrets and keys are never logged)', 'wpcalibrate-slider-carousel' ); ?>
									</label>
								</td>
							</tr>
						</table>
					</div>
				<?php endif; ?>

				<?php if ( 'integrations' !== $active_tab ) : ?>
					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'wpcalibrate-slider-carousel' ); ?></button>
					</p>
				<?php endif; ?>
			</form>
			<?php endif; ?>

			<?php if ( 'advanced' === $active_tab ) : ?>
				<div class="wpcalsc-card" style="margin-top: 20px;">
					<h2><?php esc_html_e( 'Cache Maintenance', 'wpcalibrate-slider-carousel' ); ?></h2>
					<p><?php esc_html_e( 'Clear all cached slider HTML transients generated by dynamic queries.', 'wpcalibrate-slider-carousel' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="wpcalsc_clear_cache" />
						<?php wp_nonce_field( 'wpcalsc_clear_cache_action', 'wpcalsc_clear_cache_nonce' ); ?>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Clear All Slider Caches', 'wpcalibrate-slider-carousel' ); ?></button>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Dedicated License Screen.
	 */
	public static function render_license(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpcalibrate-slider-carousel' ) );
		}

		$provider   = LicenseManager::get_provider();
		$masked_key = LicenseManager::get_masked_key();
		?>
		<div class="wrap wpcalsc-admin-wrap">
			<div class="wpcalsc-header">
				<div class="wpcalsc-branding">
					<img src="<?php echo esc_url( BrandingHelper::get_icon_dark_url() ); ?>" alt="WPCalibrate" class="wpcalsc-header-logo" />
					<div>
						<h1><?php esc_html_e( 'Slider & Carousel License', 'wpcalibrate-slider-carousel' ); ?></h1>
						<p class="wpcalsc-tagline"><?php esc_html_e( 'Manage your WPCalibrate license activation.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				</div>
			</div>

			<?php if ( isset( $_GET['license_saved'] ) ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'License key stored. Licensing provider is not configured yet.', 'wpcalibrate-slider-carousel' ); ?></p></div>
			<?php elseif ( isset( $_GET['license_deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'License key removed.', 'wpcalibrate-slider-carousel' ); ?></p></div>
			<?php endif; ?>

			<div class="wpcalsc-card">
				<h2><?php esc_html_e( 'License Configuration', 'wpcalibrate-slider-carousel' ); ?></h2>

				<?php if ( ! $provider->is_configured() ) : ?>
					<div class="notice notice-warning inline" style="margin-bottom: 20px;">
						<p><strong><?php esc_html_e( 'Licensing provider not configured', 'wpcalibrate-slider-carousel' ); ?></strong></p>
						<p><?php esc_html_e( 'No remote licensing provider API has been linked yet. You can store your key below; verification will activate once a provider is configured.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wpcalsc_save_license" />
					<?php wp_nonce_field( 'wpcalsc_save_license_action', 'wpcalsc_license_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row"><label for="license_key"><?php esc_html_e( 'License Key', 'wpcalibrate-slider-carousel' ); ?></label></th>
							<td>
								<input type="password" name="license_key" id="license_key" value="<?php echo esc_attr( $masked_key ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Enter your license key...', 'wpcalibrate-slider-carousel' ); ?>" autocomplete="off" />
								<p class="description"><?php esc_html_e( 'Your license key is securely masked and accessible only to administrators.', 'wpcalibrate-slider-carousel' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'License Status', 'wpcalibrate-slider-carousel' ); ?></th>
							<td>
								<span class="wpcalsc-status-pill wpcalsc-status-draft"><?php esc_html_e( 'Unconfigured / Standalone', 'wpcalibrate-slider-carousel' ); ?></span>
							</td>
						</tr>
					</table>

					<p class="submit">
						<button type="submit" name="license_action" value="save" class="button button-primary"><?php esc_html_e( 'Save License Key', 'wpcalibrate-slider-carousel' ); ?></button>
						<?php if ( ! empty( $masked_key ) ) : ?>
							<button type="submit" name="license_action" value="delete" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this license key?', 'wpcalibrate-slider-carousel' ) ); ?>');"><?php esc_html_e( 'Deactivate / Remove', 'wpcalibrate-slider-carousel' ); ?></button>
						<?php endif; ?>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Dedicated Support Screen.
	 */
	public static function render_support(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpcalibrate-slider-carousel' ) );
		}
		?>
		<div class="wrap wpcalsc-admin-wrap">
			<div class="wpcalsc-header">
				<div class="wpcalsc-branding">
					<img src="<?php echo esc_url( BrandingHelper::get_icon_dark_url() ); ?>" alt="WPCalibrate" class="wpcalsc-header-logo" />
					<div>
						<h1><?php esc_html_e( 'WPCalibrate Support & Contact', 'wpcalibrate-slider-carousel' ); ?></h1>
						<p class="wpcalsc-tagline"><?php esc_html_e( 'Official technical assistance, resources, and contact information.', 'wpcalibrate-slider-carousel' ); ?></p>
					</div>
				</div>
			</div>

			<div class="wpcalsc-card">
				<h2><?php esc_html_e( 'Official Contact Details', 'wpcalibrate-slider-carousel' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Author / Organization', 'wpcalibrate-slider-carousel' ); ?></th>
						<td><strong>WPCalibrate</strong></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Support Email', 'wpcalibrate-slider-carousel' ); ?></th>
						<td>
							<a href="mailto:support@wpcalibrate.com">support@wpcalibrate.com</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Official Website', 'wpcalibrate-slider-carousel' ); ?></th>
						<td>
							<a href="https://wpcalibrate.com" target="_blank" rel="noopener noreferrer">https://wpcalibrate.com</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Marketplace', 'wpcalibrate-slider-carousel' ); ?></th>
						<td>
							<a href="https://marketplace.wpcalibrate.com/" target="_blank" rel="noopener noreferrer">https://marketplace.wpcalibrate.com/</a>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Phone / WhatsApp', 'wpcalibrate-slider-carousel' ); ?></th>
						<td>
							<a href="https://wa.me/447474795976" target="_blank" rel="noopener noreferrer">+447474795976</a>
						</td>
					</tr>
				</table>
			</div>

			<div class="wpcalsc-card">
				<h2><?php esc_html_e( 'System Environment Information', 'wpcalibrate-slider-carousel' ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Plugin Version', 'wpcalibrate-slider-carousel' ); ?></th>
						<td><code><?php echo esc_html( WPCALSC_VERSION ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'PHP Version', 'wpcalibrate-slider-carousel' ); ?></th>
						<td><code><?php echo esc_html( PHP_VERSION ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'WordPress Version', 'wpcalibrate-slider-carousel' ); ?></th>
						<td><code><?php echo esc_html( (string) $GLOBALS['wp_version'] ); ?></code></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'WooCommerce Status', 'wpcalibrate-slider-carousel' ); ?></th>
						<td><code><?php echo WooCommerce::is_active() ? 'Active (' . esc_html( defined( 'WC_VERSION' ) ? WC_VERSION : 'Installed' ) . ')' : 'Inactive'; ?></code></td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle Settings POST save.
	 */
	public static function handle_save_settings(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'Unauthorized user.', 'wpcalibrate-slider-carousel' ) );
		}

		check_admin_referer( 'wpcalsc_save_settings_action', 'wpcalsc_settings_nonce' );

		$raw         = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
		$current_tab = isset( $_POST['current_tab'] ) ? sanitize_key( $_POST['current_tab'] ) : 'general';

		$existing = self::get_settings();

		$clean = [
			'default_speed'       => min( 5000, max( 100, absint( $raw['default_speed'] ?? $existing['default_speed'] ) ) ),
			'default_navigation'  => in_array( $raw['default_navigation'] ?? '', [ 'both', 'arrows', 'dots' ], true ) ? $raw['default_navigation'] : $existing['default_navigation'],
			'delete_on_uninstall' => ! empty( $raw['delete_on_uninstall'] ) ? 1 : 0,
			'lazy_load'           => ! empty( $raw['lazy_load'] ) ? 1 : 0,
			'enable_cache'        => ! empty( $raw['enable_cache'] ) ? 1 : 0,
			'cache_duration'      => min( 86400, max( 60, absint( $raw['cache_duration'] ?? $existing['cache_duration'] ) ) ),
			'debug_logging'       => ! empty( $raw['debug_logging'] ) ? 1 : 0,
		];

		update_option( self::OPTION_NAME, $clean );

		$redirect = add_query_arg(
			[
				'page'  => Menu::PAGE_SETTINGS,
				'tab'   => $current_tab,
				'saved' => '1',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle License POST save or delete.
	 */
	public static function handle_save_license(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'Unauthorized user.', 'wpcalibrate-slider-carousel' ) );
		}

		check_admin_referer( 'wpcalsc_save_license_action', 'wpcalsc_license_nonce' );

		$action = sanitize_key( $_POST['license_action'] ?? 'save' );

		if ( 'delete' === $action ) {
			LicenseManager::delete_key();
			$redirect = add_query_arg(
				[
					'page'            => Menu::PAGE_LICENSE,
					'license_deleted' => '1',
				],
				admin_url( 'admin.php' )
			);
		} else {
			$raw_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
			// If not matching masked key, save new key
			if ( ! str_contains( $raw_key, '****' ) ) {
				LicenseManager::save_key( $raw_key );
			}
			$redirect = add_query_arg(
				[
					'page'          => Menu::PAGE_LICENSE,
					'license_saved' => '1',
				],
				admin_url( 'admin.php' )
			);
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle cache clear request.
	 */
	public static function handle_clear_cache(): void {
		if ( ! Capabilities::current_user_can_manage() ) {
			wp_die( esc_html__( 'Unauthorized user.', 'wpcalibrate-slider-carousel' ) );
		}

		check_admin_referer( 'wpcalsc_clear_cache_action', 'wpcalsc_clear_cache_nonce' );

		global $wpdb;
		if ( isset( $wpdb->options ) ) {
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpcalsc_%' OR option_name LIKE '_transient_timeout_wpcalsc_%'" );
		}

		$redirect = add_query_arg(
			[
				'page'          => Menu::PAGE_SETTINGS,
				'tab'           => 'advanced',
				'cache_cleared' => '1',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
