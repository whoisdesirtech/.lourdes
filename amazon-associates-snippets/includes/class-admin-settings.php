<?php
/**
 * Admin Settings Page & Interactive Snippet Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Admin_Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_aa_test_api_connection', array( $this, 'ajax_test_api_connection' ) );
		add_action( 'wp_ajax_aa_clear_plugin_cache', array( $this, 'ajax_clear_plugin_cache' ) );
	}

	public function add_admin_menu() {
		add_options_page(
			__( 'Amazon Associates Snippets', 'amazon-associates-snippets' ),
			__( 'Amazon Snippets', 'amazon-associates-snippets' ),
			'manage_options',
			'amazon-snippets',
			array( $this, 'render_admin_page' )
		);
	}

	public function register_settings() {
		register_setting( 'aa_snippets_options', 'aa_access_key', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_secret_key', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_partner_tag', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_marketplace', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_cache_expiry', 'absint' );
		register_setting( 'aa_snippets_options', 'aa_button_text', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_disclosure_text', 'sanitize_textarea_field' );
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'credentials';
		?>
		<div class="wrap aa-admin-wrap">
			<h1><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Amazon Associates PHP Snippets', 'amazon-associates-snippets' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Configure your Amazon PA-API 5.0 keys and generate custom PHP code snippets and shortcodes for your WordPress site.', 'amazon-associates-snippets' ); ?>
			</p>

			<h2 class="nav-tab-wrapper">
				<a href="?page=amazon-snippets&tab=credentials" class="nav-tab <?php echo 'credentials' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'API Credentials', 'amazon-associates-snippets' ); ?>
				</a>
				<a href="?page=amazon-snippets&tab=display" class="nav-tab <?php echo 'display' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Display & Compliance', 'amazon-associates-snippets' ); ?>
				</a>
				<a href="?page=amazon-snippets&tab=generator" class="nav-tab <?php echo 'generator' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'PHP Snippet Generator', 'amazon-associates-snippets' ); ?>
				</a>
				<a href="?page=amazon-snippets&tab=tester" class="nav-tab <?php echo 'tester' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'API Connection Tester', 'amazon-associates-snippets' ); ?>
				</a>
			</h2>

			<div class="aa-admin-content">
				<?php
				if ( 'credentials' === $active_tab ) {
					$this->render_credentials_tab();
				} elseif ( 'display' === $active_tab ) {
					$this->render_display_tab();
				} elseif ( 'generator' === $active_tab ) {
					$this->render_generator_tab();
				} elseif ( 'tester' === $active_tab ) {
					$this->render_tester_tab();
				}
				?>
			</div>
		</div>
		<?php
	}

	private function render_credentials_tab() {
		$access_key   = get_option( 'aa_access_key', '' );
		$secret_key   = get_option( 'aa_secret_key', '' );
		$partner_tag  = get_option( 'aa_partner_tag', '' );
		$marketplace  = get_option( 'aa_marketplace', 'US' );
		$cache_expiry = get_option( 'aa_cache_expiry', 24 );

		$marketplaces = AA_Amazon_API::get_marketplaces();
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'aa_snippets_options' );
			do_settings_sections( 'aa_snippets_options' );
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="aa_partner_tag"><?php esc_html_e( 'Amazon Partner Tag / Associate ID', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="text" name="aa_partner_tag" id="aa_partner_tag" value="<?php echo esc_attr( $partner_tag ); ?>" class="regular-text" placeholder="e.g. yourstore-20" required />
						<p class="description"><?php esc_html_e( 'Your Amazon Associates Tracking ID. Appended to all affiliate links.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_access_key"><?php esc_html_e( 'Amazon Access Key ID', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="text" name="aa_access_key" id="aa_access_key" value="<?php echo esc_attr( $access_key ); ?>" class="regular-text" placeholder="e.g. AKIAIOSFODNN7EXAMPLE" />
						<p class="description"><?php esc_html_e( 'Obtained from your Amazon Associates Account under Tools > Product Advertising API.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_secret_key"><?php esc_html_e( 'Amazon Secret Key', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="password" name="aa_secret_key" id="aa_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text" placeholder="••••••••••••••••••••••••" />
						<p class="description"><?php esc_html_e( 'Your PA-API Secret Key. Stored securely in WordPress options.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_marketplace"><?php esc_html_e( 'Default Marketplace / Region', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<select name="aa_marketplace" id="aa_marketplace">
							<?php foreach ( $marketplaces as $code => $info ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $marketplace, $code ); ?>>
									<?php echo esc_html( $info['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Select the Amazon locale for product queries.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_cache_expiry"><?php esc_html_e( 'API Cache Lifetime (Hours)', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="number" name="aa_cache_expiry" id="aa_cache_expiry" value="<?php echo esc_attr( $cache_expiry ); ?>" min="1" max="168" class="small-text" />
						<p class="description"><?php esc_html_e( 'Price and item cache duration in hours (Default: 24h per Amazon TOS).', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save API Settings', 'amazon-associates-snippets' ) ); ?>
		</form>

		<hr>

		<h3><?php esc_html_e( 'Cache Management', 'amazon-associates-snippets' ); ?></h3>
		<p><?php esc_html_e( 'Clear cached product data transients if you updated prices or want fresh API calls.', 'amazon-associates-snippets' ); ?></p>
		<button type="button" id="aa-clear-cache-btn" class="button button-secondary">
			<span class="dashicons dashicons-trash" style="vertical-align: middle;"></span> <?php esc_html_e( 'Purge All Cached Amazon Items', 'amazon-associates-snippets' ); ?>
		</button>
		<span id="aa-cache-status" style="margin-left: 10px; font-weight: 600;"></span>
		<?php
	}

	private function render_display_tab() {
		$button_text     = get_option( 'aa_button_text', 'Buy on Amazon' );
		$disclosure_text = get_option( 'aa_disclosure_text', 'As an Amazon Associate I earn from qualifying purchases.' );
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'aa_snippets_options' );
			do_settings_sections( 'aa_snippets_options' );
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="aa_button_text"><?php esc_html_e( 'Default CTA Button Text', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="text" name="aa_button_text" id="aa_button_text" value="<?php echo esc_attr( $button_text ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Text displayed on affiliate buy buttons.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_disclosure_text"><?php esc_html_e( 'FTC / Amazon Affiliate Disclosure', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<textarea name="aa_disclosure_text" id="aa_disclosure_text" rows="3" class="large-text"><?php echo esc_textarea( $disclosure_text ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Required notice displayed at the bottom of Amazon product showcase cards to satisfy FTC and Amazon Associates guidelines.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Display Settings', 'amazon-associates-snippets' ) ); ?>
		</form>
		<?php
	}

	private function render_generator_tab() {
		?>
		<div class="aa-generator-card">
			<h2><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e( 'Interactive Code Snippet Generator', 'amazon-associates-snippets' ); ?></h2>
			<p><?php esc_html_e( 'Enter an Amazon Product ASIN (e.g. B08N5WRWNW) below to generate shortcodes and PHP functions ready to copy directly into your content, theme, or Code Snippets plugin.', 'amazon-associates-snippets' ); ?></p>

			<div class="aa-generator-input-group">
				<label for="aa-gen-asin"><strong><?php esc_html_e( 'Amazon Product ASIN:', 'amazon-associates-snippets' ); ?></strong></label>
				<input type="text" id="aa-gen-asin" placeholder="e.g. B08N5WRWNW" class="regular-text" value="B08N5WRWNW" />
				<button type="button" id="aa-generate-btn" class="button button-primary"><?php esc_html_e( 'Generate Snippets', 'amazon-associates-snippets' ); ?></button>
			</div>

			<div class="aa-snippet-box-results" style="margin-top: 25px;">
				<h3>1. Product Showcase Box Card</h3>
				<p>Renders a full responsive showcase card with product image, title, price tag, features, Prime badge, CTA button, and disclosure notice.</p>
				<div class="aa-code-preview-block">
					<label>Shortcode:</label>
					<div class="aa-code-copy-container">
						<code id="sc-box">[amazon_box asin="B08N5WRWNW"]</code>
						<button type="button" class="button aa-copy-btn" data-target="sc-box">Copy Shortcode</button>
					</div>
					<label>PHP Snippet (Theme / Code Snippets plugin):</label>
					<div class="aa-code-copy-container">
						<code id="php-box">&lt;?php echo aa_render_product_box( 'B08N5WRWNW' ); ?&gt;</code>
						<button type="button" class="button aa-copy-btn" data-target="php-box">Copy PHP</button>
					</div>
				</div>

				<h3 style="margin-top: 20px;">2. Standalone Amazon Buy Button</h3>
				<p>Renders a styled call-to-action button with Amazon icon.</p>
				<div class="aa-code-preview-block">
					<label>Shortcode:</label>
					<div class="aa-code-copy-container">
						<code id="sc-btn">[amazon_button asin="B08N5WRWNW" text="Check Price on Amazon"]</code>
						<button type="button" class="button aa-copy-btn" data-target="sc-btn">Copy Shortcode</button>
					</div>
					<label>PHP Snippet:</label>
					<div class="aa-code-copy-container">
						<code id="php-btn">&lt;?php echo aa_render_button( 'B08N5WRWNW', 'Check Price on Amazon' ); ?&gt;</code>
						<button type="button" class="button aa-copy-btn" data-target="php-btn">Copy PHP</button>
					</div>
				</div>

				<h3 style="margin-top: 20px;">3. Inline Affiliate Text Link</h3>
				<div class="aa-code-preview-block">
					<label>Shortcode:</label>
					<div class="aa-code-copy-container">
						<code id="sc-link">[amazon_link asin="B08N5WRWNW" text="View Product on Amazon"]</code>
						<button type="button" class="button aa-copy-btn" data-target="sc-link">Copy Shortcode</button>
					</div>
					<label>PHP Snippet:</label>
					<div class="aa-code-copy-container">
						<code id="php-link">&lt;?php echo aa_render_link( 'B08N5WRWNW', 'View Product on Amazon' ); ?&gt;</code>
						<button type="button" class="button aa-copy-btn" data-target="php-link">Copy PHP</button>
					</div>
				</div>

				<h3 style="margin-top: 20px;">4. Direct PHP Data Fetch (For Developers)</h3>
				<p>Returns raw structured array (Title, Image, Price, Prime status, Features) from Amazon PA-API 5.0 with caching.</p>
				<div class="aa-code-preview-block">
					<div class="aa-code-copy-container">
						<code id="php-raw">&lt;?php
$product = aa_get_product_data( 'B08N5WRWNW' );
if ( $product ) {
    echo esc_html( $product['title'] ) . ' - ' . esc_html( $product['price'] );
}
?&gt;</code>
						<button type="button" class="button aa-copy-btn" data-target="php-raw">Copy PHP</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_tester_tab() {
		?>
		<div class="aa-tester-card">
			<h2><span class="dashicons dashicons-testimonial"></span> <?php esc_html_e( 'Amazon PA-API 5.0 Connection Diagnostics', 'amazon-associates-snippets' ); ?></h2>
			<p><?php esc_html_e( 'Test your Amazon credentials in real-time. This will attempt a live API request to fetch item details.', 'amazon-associates-snippets' ); ?></p>

			<div style="margin-bottom: 15px;">
				<label for="aa-test-asin"><strong>ASIN to Test:</strong></label>
				<input type="text" id="aa-test-asin" value="B08N5WRWNW" class="regular-text" />
				<button type="button" id="aa-run-test-btn" class="button button-primary"><?php esc_html_e( 'Run Live API Test', 'amazon-associates-snippets' ); ?></button>
			</div>

			<div id="aa-test-log-output" class="aa-log-terminal">
				<em>Click "Run Live API Test" above to verify PA-API 5.0 connection...</em>
			</div>
		</div>
		<?php
	}

	public function ajax_test_api_connection() {
		check_ajax_referer( 'aa_snippets_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user permissions.' ) );
		}

		$asin = isset( $_POST['asin'] ) ? sanitize_text_field( $_POST['asin'] ) : 'B08N5WRWNW';
		$api  = AA_Amazon_API::get_instance();

		// Force fresh fetch for testing (bypass transient)
		$partner_tag   = trim( get_option( 'aa_partner_tag', '' ) );
		$transient_key = 'aa_item_' . md5( strtoupper( $asin ) . '_' . $partner_tag );
		delete_transient( $transient_key );

		$data = $api->get_item( $asin );

		if ( isset( $data['is_fallback'] ) && $data['is_fallback'] ) {
			wp_send_json_error( array(
				'message' => isset( $data['error'] ) && ! empty( $data['error'] ) ? $data['error'] : 'Fallback mode triggered.',
				'data'    => $data,
			) );
		}

		wp_send_json_success( array(
			'message' => 'Connection Successful! Received valid product data from Amazon PA-API 5.0.',
			'data'    => $data,
		) );
	}

	public function ajax_clear_plugin_cache() {
		check_ajax_referer( 'aa_snippets_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user permissions.' ) );
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aa_item_%' OR option_name LIKE '_transient_timeout_aa_item_%'" );

		wp_send_json_success( array( 'message' => 'All Amazon item transients cleared successfully!' ) );
	}
}

new AA_Admin_Settings();
