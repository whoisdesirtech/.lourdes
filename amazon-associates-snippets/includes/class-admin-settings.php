<?php
/**
 * Admin Settings Page & Interactive Snippet Generator with OAuth 2.0 Support
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
		add_action( 'wp_ajax_aa_refresh_oauth_token', array( $this, 'ajax_refresh_oauth_token' ) );
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
		register_setting( 'aa_snippets_options', 'aa_auth_mode', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_access_key', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_secret_key', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_oauth_client_id', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_oauth_client_secret', 'sanitize_text_field' );
		register_setting( 'aa_snippets_options', 'aa_oauth_access_token', 'sanitize_textarea_field' );
		register_setting( 'aa_snippets_options', 'aa_oauth_refresh_token', 'sanitize_textarea_field' );
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
			<p style="margin-top: 6px; font-size: 13px; color: #475569;">
				<strong>Developed by:</strong> WhoIsDésir® Media Agency &bull; 
				<strong>Developer Contact:</strong> <a href="mailto:digitalvurv@gmail.com" style="color: #0284c7; text-decoration: none;">digitalvurv@gmail.com</a>
			</p>

			<h2 class="nav-tab-wrapper">
				<a href="?page=amazon-snippets&tab=credentials" class="nav-tab <?php echo 'credentials' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'API & OAuth 2.0 Credentials', 'amazon-associates-snippets' ); ?>
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
		$auth_mode          = get_option( 'aa_auth_mode', 'sigv4' );
		$access_key         = get_option( 'aa_access_key', '' );
		$secret_key         = get_option( 'aa_secret_key', '' );
		$oauth_client_id    = get_option( 'aa_oauth_client_id', '' );
		$oauth_client_secret= get_option( 'aa_oauth_client_secret', '' );
		$oauth_access_token = get_option( 'aa_oauth_access_token', '' );
		$oauth_refresh_token= get_option( 'aa_oauth_refresh_token', '' );
		$partner_tag        = get_option( 'aa_partner_tag', '' );
		$marketplace        = get_option( 'aa_marketplace', 'US' );
		$cache_expiry       = get_option( 'aa_cache_expiry', 24 );

		$marketplaces = AA_Amazon_API::get_marketplaces();
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'aa_snippets_options' );
			do_settings_sections( 'aa_snippets_options' );
			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Authentication Method', 'amazon-associates-snippets' ); ?></th>
					<td>
						<fieldset>
							<label style="margin-right: 20px;">
								<input type="radio" name="aa_auth_mode" value="sigv4" <?php checked( $auth_mode, 'sigv4' ); ?> class="aa-auth-toggle" />
								<strong>AWS Signature V4</strong> (Access Key ID + Secret Key)
							</label>
							<label>
								<input type="radio" name="aa_auth_mode" value="oauth2" <?php checked( $auth_mode, 'oauth2' ); ?> class="aa-auth-toggle" />
								<strong>OAuth 2.0 Access Token</strong> (Bearer Token / Client Credentials)
							</label>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Choose your preferred Amazon API authentication standard.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="aa_partner_tag"><?php esc_html_e( 'Amazon Partner Tag / Associate ID', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="text" name="aa_partner_tag" id="aa_partner_tag" value="<?php echo esc_attr( $partner_tag ); ?>" class="regular-text" placeholder="e.g. yourstore-20" required />
						<p class="description"><?php esc_html_e( 'Your Amazon Associates Tracking ID.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
			</table>

			<!-- SigV4 Fields -->
			<div id="aa-sigv4-section" class="aa-auth-section" style="<?php echo 'sigv4' !== $auth_mode ? 'display:none;' : ''; ?>">
				<h3><?php esc_html_e( 'AWS Signature Version 4 Credentials', 'amazon-associates-snippets' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="aa_access_key"><?php esc_html_e( 'Access Key ID', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<input type="text" name="aa_access_key" id="aa_access_key" value="<?php echo esc_attr( $access_key ); ?>" class="regular-text" placeholder="e.g. AKIAIOSFODNN7EXAMPLE" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aa_secret_key"><?php esc_html_e( 'Secret Key', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<input type="password" name="aa_secret_key" id="aa_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text" placeholder="••••••••••••••••••••••••" />
						</td>
					</tr>
				</table>
			</div>

			<!-- OAuth 2.0 Fields -->
			<div id="aa-oauth2-section" class="aa-auth-section" style="<?php echo 'oauth2' !== $auth_mode ? 'display:none;' : ''; ?>">
				<h3><?php esc_html_e( 'OAuth 2.0 Bearer Access Token Credentials', 'amazon-associates-snippets' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="aa_oauth_client_id"><?php esc_html_e( 'OAuth 2.0 Client ID / App ID', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<input type="text" name="aa_oauth_client_id" id="aa_oauth_client_id" value="<?php echo esc_attr( $oauth_client_id ); ?>" class="regular-text" placeholder="amzn1.application-oa2-client.xxxxxxxx" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aa_oauth_client_secret"><?php esc_html_e( 'OAuth 2.0 Client Secret', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<input type="password" name="aa_oauth_client_secret" id="aa_oauth_client_secret" value="<?php echo esc_attr( $oauth_client_secret ); ?>" class="regular-text" placeholder="••••••••••••••••••••••••" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aa_oauth_access_token"><?php esc_html_e( 'OAuth 2.0 Access Token (Bearer)', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<textarea name="aa_oauth_access_token" id="aa_oauth_access_token" rows="3" class="large-text" placeholder="Atza|..."><?php echo esc_textarea( $oauth_access_token ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Paste your OAuth 2.0 Access Token, or leave empty to automatically fetch/refresh tokens via Client Credentials.', 'amazon-associates-snippets' ); ?></p>
							<button type="button" id="aa-fetch-token-btn" class="button button-secondary" style="margin-top: 6px;">
								<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Fetch Fresh OAuth Access Token Now
							</button>
							<span id="aa-token-status" style="margin-left: 10px; font-weight: 600;"></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aa_oauth_refresh_token"><?php esc_html_e( 'OAuth 2.0 Refresh Token (Optional)', 'amazon-associates-snippets' ); ?></label></th>
						<td>
							<textarea name="aa_oauth_refresh_token" id="aa_oauth_refresh_token" rows="2" class="large-text" placeholder="Atr|..."><?php echo esc_textarea( $oauth_refresh_token ); ?></textarea>
						</td>
					</tr>
				</table>
			</div>

			<table class="form-table">
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
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_cache_expiry"><?php esc_html_e( 'API Cache Lifetime (Hours)', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<input type="number" name="aa_cache_expiry" id="aa_cache_expiry" value="<?php echo esc_attr( $cache_expiry ); ?>" min="1" max="168" class="small-text" />
						<p class="description"><?php esc_html_e( 'Price and item cache duration in hours.', 'amazon-associates-snippets' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Credentials & Settings', 'amazon-associates-snippets' ) ); ?>
		</form>

		<hr>

		<h3><?php esc_html_e( 'Cache Management', 'amazon-associates-snippets' ); ?></h3>
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
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aa_disclosure_text"><?php esc_html_e( 'FTC / Amazon Affiliate Disclosure', 'amazon-associates-snippets' ); ?></label></th>
					<td>
						<textarea name="aa_disclosure_text" id="aa_disclosure_text" rows="3" class="large-text"><?php echo esc_textarea( $disclosure_text ); ?></textarea>
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
			<p><?php esc_html_e( 'Enter an Amazon Product ASIN below to generate shortcodes and PHP functions ready to copy.', 'amazon-associates-snippets' ); ?></p>

			<div class="aa-generator-input-group">
				<label for="aa-gen-asin"><strong><?php esc_html_e( 'Amazon Product ASIN:', 'amazon-associates-snippets' ); ?></strong></label>
				<input type="text" id="aa-gen-asin" placeholder="e.g. B08N5WRWNW" class="regular-text" value="B08N5WRWNW" />
				<button type="button" id="aa-generate-btn" class="button button-primary"><?php esc_html_e( 'Generate Snippets', 'amazon-associates-snippets' ); ?></button>
			</div>

			<div class="aa-snippet-box-results" style="margin-top: 25px;">
				<h3>1. Product Showcase Box Card</h3>
				<div class="aa-code-preview-block">
					<label>Shortcode:</label>
					<div class="aa-code-copy-container">
						<code id="sc-box">[amazon_box asin="B08N5WRWNW"]</code>
						<button type="button" class="button aa-copy-btn" data-target="sc-box">Copy Shortcode</button>
					</div>
					<label>PHP Snippet:</label>
					<div class="aa-code-copy-container">
						<code id="php-box">&lt;?php echo aa_render_product_box( 'B08N5WRWNW' ); ?&gt;</code>
						<button type="button" class="button aa-copy-btn" data-target="php-box">Copy PHP</button>
					</div>
				</div>

				<h3 style="margin-top: 20px;">2. Standalone Amazon Buy Button</h3>
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

				<h3 style="margin-top: 20px;">3. Product Comparison Grid</h3>
				<div class="aa-code-preview-block">
					<label>Shortcode:</label>
					<div class="aa-code-copy-container">
						<code id="sc-comp">[amazon_comparison products="B08N5WRWNW,B09B2W5X1S"]</code>
						<button type="button" class="button aa-copy-btn" data-target="sc-comp">Copy Shortcode</button>
					</div>
				</div>

				<h3 style="margin-top: 20px;">4. Direct PHP Data Fetch</h3>
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
			<h2><span class="dashicons dashicons-testimonial"></span> <?php esc_html_e( 'Amazon API & OAuth 2.0 Connection Diagnostics', 'amazon-associates-snippets' ); ?></h2>
			<p><?php esc_html_e( 'Test your credentials and authentication mode (AWS SigV4 or OAuth 2.0 Bearer Token) in real-time.', 'amazon-associates-snippets' ); ?></p>

			<div style="margin-bottom: 15px;">
				<label for="aa-test-asin"><strong>ASIN to Test:</strong></label>
				<input type="text" id="aa-test-asin" value="B08N5WRWNW" class="regular-text" />
				<button type="button" id="aa-run-test-btn" class="button button-primary"><?php esc_html_e( 'Run Live API Test', 'amazon-associates-snippets' ); ?></button>
			</div>

			<div id="aa-test-log-output" class="aa-log-terminal">
				<em>Click "Run Live API Test" above to verify connection...</em>
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
			'message' => 'Connection Successful! Received valid product data.',
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
		delete_transient( 'aa_oauth_active_access_token' );

		wp_send_json_success( array( 'message' => 'All Amazon item transients & OAuth token caches cleared!' ) );
	}

	public function ajax_refresh_oauth_token() {
		check_ajax_referer( 'aa_snippets_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized user permissions.' ) );
		}

		delete_transient( 'aa_oauth_active_access_token' );
		$api   = AA_Amazon_API::get_instance();
		$token = $api->request_fresh_oauth_token();

		if ( is_wp_error( $token ) ) {
			wp_send_json_error( array( 'message' => $token->get_error_message() ) );
		}

		if ( ! empty( $token['access_token'] ) ) {
			update_option( 'aa_oauth_access_token', $token['access_token'] );
			wp_send_json_success( array(
				'message'      => 'OAuth 2.0 Access Token retrieved successfully!',
				'access_token' => $token['access_token'],
			) );
		}

		wp_send_json_error( array( 'message' => 'Unexpected token response structure.' ) );
	}
}

new AA_Admin_Settings();
