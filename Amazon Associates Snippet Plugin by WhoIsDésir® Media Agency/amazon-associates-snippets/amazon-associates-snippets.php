<?php
/**
 * Plugin Name:       Amazon Associates PHP Snippets
 * Plugin URI:        https://example.com/amazon-associates-snippets
 * Description:       Integrates the Amazon Creators API into WordPress. Provides PHP snippet helpers, shortcodes, and product cards with OAuth 2.0 authentication, transient token caching, and FTC compliance.
* Version:           1.5.2
 * Release Date:      August 11, 2026
 * Author:            WhoIsDésir® Media Agency
 * Author URI:        mailto:digitalvurv@gmail.com
 * Text Domain:       amazon-associates-snippets
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'AA_SNIPPETS_VERSION', '1.5.2' );
define( 'AA_SNIPPETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AA_SNIPPETS_URL', plugin_dir_url( __FILE__ ) );
define( 'AA_SNIPPETS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class
 */
class AA_Snippets_Plugin {

	/**
	 * Single Instance
	 */
	private static $instance = null;

	/**
	 * Get Instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load Includes
	 */
	private function load_dependencies() {
		// Normalized product data model + provider abstraction.
		require_once AA_SNIPPETS_PATH . 'includes/class-product-reference.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-product-query.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-product-collection.php';
		require_once AA_SNIPPETS_PATH . 'includes/interface-product-provider.php';

		// Amazon provider (refactor of the original AA_Amazon_API facade).
		require_once AA_SNIPPETS_PATH . 'includes/class-creators-oauth-client.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-creators-api-transport.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-creators-api-sdk-transport.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-creators-api-http-transport.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-amazon-response-normalizer.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-amazon-provider.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-amazon-api.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-product-provider-registry.php';

		// Additional providers + data services (graceful if a dependency is missing).
		require_once AA_SNIPPETS_PATH . 'includes/class-walmart-api.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-walmart-provider.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-keepa-price-adapter.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-click-tracker.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-freshness-refresh.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-blocks.php';

		require_once AA_SNIPPETS_PATH . 'includes/class-admin-settings.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-shortcodes.php';
		require_once AA_SNIPPETS_PATH . 'includes/class-snippet-helpers.php';
	}

	/**
	 * Initialize Hooks
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'plugin_action_links_' . AA_SNIPPETS_BASENAME, array( $this, 'add_plugin_action_links' ) );

		// Data services introduced by the 2026 multi-provider audit.
		AA_Click_Tracker::init();
		AA_Freshness_Refresh::init();
		AA_Blocks::init();
	}

	/**
	 * Enqueue Frontend CSS
	 */
	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'aa-snippets-style',
			AA_SNIPPETS_URL . 'assets/css/amazon-snippets.css',
			array(),
			AA_SNIPPETS_VERSION
		);
	}

	/**
	 * Enqueue Admin CSS & JS
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_amazon-snippets' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'aa-snippets-admin-style',
			AA_SNIPPETS_URL . 'assets/css/amazon-snippets.css',
			array(),
			AA_SNIPPETS_VERSION
		);

		wp_enqueue_script(
			'aa-snippets-admin-js',
			AA_SNIPPETS_URL . 'assets/js/admin-scripts.js',
			array( 'jquery' ),
			AA_SNIPPETS_VERSION,
			true
		);

		wp_localize_script(
			'aa-snippets-admin-js',
			'aaSnippetsAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'aa_snippets_admin_nonce' ),
			)
		);
	}

	/**
	 * Add Settings Link on Plugins Page
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=amazon-snippets' ) ) . '">' . __( 'Settings', 'amazon-associates-snippets' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}

/**
 * Initialize Plugin
 */
function aa_snippets_init() {
	return AA_Snippets_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'aa_snippets_init' );

/**
 * Activation: install data tables, schedule cron, flush rewrite rules.
 */
register_activation_hook( __FILE__, function () {
	AA_Click_Tracker::activate();
	AA_Freshness_Refresh::schedule();
	AA_Product_Provider_Registry::boot();
	flush_rewrite_rules();
} );

/**
 * Deactivation: unschedule cron, flush rewrite rules.
 */
register_deactivation_hook( __FILE__, function () {
	AA_Freshness_Refresh::unschedule();
	flush_rewrite_rules();
} );
