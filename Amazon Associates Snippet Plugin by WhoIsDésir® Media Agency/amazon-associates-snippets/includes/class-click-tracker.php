<?php
/**
 * Click Tracker
 *
 * Self-hosted click tracking. Each tracked product link points at a local
 * redirect endpoint (/aa-go/{id}) that records the click and forwards the
 * visitor to the affiliate URL. Replaces third-party link-cloakers with a
 * provider-agnostic table the plugin owns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Click_Tracker {

	/**
	 * Database table name (without prefix).
	 */
	const TABLE = 'aa_click_tracking';

	/**
	 * Query var used by the redirect endpoint.
	 */
	const QUERY_VAR = 'aa_go_id';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ) );
		add_filter( 'query_vars', array( __CLASS__, 'register_query_var' ) );
	}

	/**
	 * Activation: install table + rewrite rules.
	 */
	public static function activate() {
		self::install_table();
		self::add_rewrite_rules();
	}

	/**
	 * Create the click-tracking table.
	 */
	public static function install_table() {
		global $wpdb;

		$table   = $wpdb->prefix . self::TABLE;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			provider VARCHAR(20) NOT NULL DEFAULT 'amazon',
			product_id VARCHAR(40) NOT NULL DEFAULT '',
			destination_url TEXT NOT NULL,
			post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ip VARCHAR(45) NOT NULL DEFAULT '',
			user_agent VARCHAR(255) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			PRIMARY KEY (id),
			KEY provider (provider),
			KEY product_id (product_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Register the redirect rewrite rule.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule(
			'^aa-go/([0-9]+)/?$',
			'index.php?' . self::QUERY_VAR . '=$matches[1]',
			'top'
		);
	}

	/**
	 * Expose the query var to WordPress.
	 *
	 * @param array $vars Registered query vars.
	 * @return array
	 */
	public static function register_query_var( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Whether click tracking is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) get_option( 'aa_click_tracking', 0 );
	}

	/**
	 * Get the tracked "go" URL for a product (records a row, returns local URL).
	 *
	 * When tracking is disabled or the product has no URL, the direct URL is
	 * returned unchanged.
	 *
	 * @param array $product Normalized product array.
	 * @param int   $post_id Optional source post id.
	 * @return string
	 */
	public static function get_go_url( array $product, $post_id = 0 ) {
		if ( ! self::is_enabled() || empty( $product['url'] ) ) {
			return isset( $product['url'] ) ? $product['url'] : '';
		}

		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;
		$wpdb->insert(
			$table,
			array(
				'provider'        => isset( $product['provider'] ) ? $product['provider'] : 'amazon',
				'product_id'      => isset( $product['asin'] ) ? $product['asin'] : ( isset( $product['item_id'] ) ? $product['item_id'] : '' ),
				'destination_url' => $product['url'],
				'post_id'         => (int) $post_id,
				'ip'              => self::get_client_ip(),
				'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( (string) $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at'      => current_time( 'mysql' ),
			)
		);

		if ( empty( $wpdb->insert_id ) ) {
			return $product['url'];
		}

		return home_url( '/aa-go/' . $wpdb->insert_id );
	}

	/**
	 * Resolve the destination URL for a tracked click id (testable).
	 *
	 * @param int $id Click row id.
	 * @return string|null Destination URL or null when not found.
	 */
	public static function get_destination_url( $id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT destination_url FROM {$table} WHERE id = %d", (int) $id ) );

		if ( ! $row || empty( $row->destination_url ) ) {
			return null;
		}

		return $row->destination_url;
	}

	/**
	 * Handle the redirect endpoint.
	 */
	public static function maybe_redirect() {
		$id = get_query_var( self::QUERY_VAR );
		if ( empty( $id ) ) {
			return;
		}

		$url = self::get_destination_url( (int) $id );
		if ( null === $url ) {
			wp_safe_redirect( home_url( '/' ), 302 );
			exit;
		}

		wp_redirect( $url, 302 );
		exit;
	}

	/**
	 * Best-effort client IP (respecting reverse proxies).
	 *
	 * @return string
	 */
	protected static function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'] );
			return trim( $parts[0] );
		}
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return (string) $_SERVER['REMOTE_ADDR'];
		}
		return '';
	}
}
