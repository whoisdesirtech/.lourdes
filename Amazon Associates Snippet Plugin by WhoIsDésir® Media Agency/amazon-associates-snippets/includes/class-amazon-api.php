<?php
/**
 * Amazon Creators API Facade
 *
 * Thin facade that ties together the Creators API OAuth client, the transport
 * layer and the response normalizer, while preserving the plugin's existing
 * transient product caching, affiliate URL generation and fallback behavior.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Amazon_API {

	/**
	 * Single instance.
	 */
	private static $instance = null;

	/**
	 * OAuth client.
	 *
	 * @var AA_Creators_OAuth_Client
	 */
	private $oauth_client;

	/**
	 * Active transport.
	 *
	 * @var AA_Creators_API_Transport
	 */
	private $transport;

	/**
	 * Response normalizer.
	 *
	 * @var AA_Amazon_Response_Normalizer
	 */
	private $normalizer;

	/**
	 * Get single instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor with optional dependency injection for testing.
	 *
	 * @param AA_Creators_OAuth_Client|null      $oauth_client OAuth client.
	 * @param AA_Creators_API_Transport|null     $transport    Transport.
	 * @param AA_Amazon_Response_Normalizer|null $normalizer   Normalizer.
	 */
	public function __construct( $oauth_client = null, $transport = null, $normalizer = null ) {
		$this->oauth_client = $oauth_client ? $oauth_client : AA_Creators_OAuth_Client::get_instance();
		$this->transport    = $transport ? $transport : AA_Creators_API_Transport::create( $this->oauth_client );
		$this->normalizer   = $normalizer ? $normalizer : new AA_Amazon_Response_Normalizer();
	}

	/**
	 * Get the marketplace map.
	 */
	public static function get_marketplaces() {
		return array(
			'US' => array(
				'name'              => 'United States (amazon.com)',
				'domain'            => 'amazon.com',
				'marketplace_domain' => 'www.amazon.com',
			),
			'UK' => array(
				'name'              => 'United Kingdom (amazon.co.uk)',
				'domain'            => 'amazon.co.uk',
				'marketplace_domain' => 'www.amazon.co.uk',
			),
			'CA' => array(
				'name'              => 'Canada (amazon.ca)',
				'domain'            => 'amazon.ca',
				'marketplace_domain' => 'www.amazon.ca',
			),
			'DE' => array(
				'name'              => 'Germany (amazon.de)',
				'domain'            => 'amazon.de',
				'marketplace_domain' => 'www.amazon.de',
			),
			'FR' => array(
				'name'              => 'France (amazon.fr)',
				'domain'            => 'amazon.fr',
				'marketplace_domain' => 'www.amazon.fr',
			),
			'ES' => array(
				'name'              => 'Spain (amazon.es)',
				'domain'            => 'amazon.es',
				'marketplace_domain' => 'www.amazon.es',
			),
			'IT' => array(
				'name'              => 'Italy (amazon.it)',
				'domain'            => 'amazon.it',
				'marketplace_domain' => 'www.amazon.it',
			),
			'JP' => array(
				'name'              => 'Japan (amazon.co.jp)',
				'domain'            => 'amazon.co.jp',
				'marketplace_domain' => 'www.amazon.co.jp',
			),
			'AU' => array(
				'name'              => 'Australia (amazon.com.au)',
				'domain'            => 'amazon.com.au',
				'marketplace_domain' => 'www.amazon.com.au',
			),
		);
	}

	/**
	 * Get the active transport (useful for diagnostics/tests).
	 */
	public function get_transport() {
		return $this->transport;
	}

	/**
	 * Build Affiliate URL for ASIN or URL.
	 */
	public function get_affiliate_url( $asin_or_url ) {
		$partner_tag = trim( get_option( 'aa_partner_tag', '' ) );
		$marketplace = get_option( 'aa_marketplace', 'US' );
		$markets     = self::get_marketplaces();
		$domain      = isset( $markets[ $marketplace ] ) ? $markets[ $marketplace ]['domain'] : 'amazon.com';

		if ( filter_var( $asin_or_url, FILTER_VALIDATE_URL ) ) {
			if ( ! empty( $partner_tag ) ) {
				return add_query_arg( 'tag', $partner_tag, $asin_or_url );
			}
			return $asin_or_url;
		}

		$asin = strtoupper( trim( $asin_or_url ) );
		$url  = "https://www.{$domain}/dp/{$asin}";

		if ( ! empty( $partner_tag ) ) {
			$url = add_query_arg( 'tag', $partner_tag, $url );
		}

		return $url;
	}

	/**
	 * Fetch Item Details by ASIN with Transient Caching.
	 */
	public function get_item( $asin ) {
		$asin = strtoupper( trim( $asin ) );
		if ( empty( $asin ) ) {
			return false;
		}

		$partner_tag   = trim( get_option( 'aa_partner_tag', '' ) );
		$transient_key = 'aa_item_' . md5( $asin . '_' . $partner_tag );
		$cached_data   = get_transient( $transient_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		if ( empty( $partner_tag ) || ! $this->oauth_client->has_credentials() ) {
			return $this->get_fallback_product( $asin, __( 'Creators API credentials missing. Showing fallback link.', 'amazon-associates-snippets' ) );
		}

		$marketplace_domain = $this->get_marketplace_domain();

		$response = $this->transport->get_items( array( $asin ), $marketplace_domain, $partner_tag );

		if ( is_wp_error( $response ) ) {
			return $this->get_fallback_product( $asin, $response->get_error_message() );
		}

		$data = $this->normalizer->normalize( $response, $asin );

		if ( null === $data ) {
			return $this->get_fallback_product( $asin, __( 'Item not found in Amazon Creators API response.', 'amazon-associates-snippets' ) );
		}

		$data['url'] = ! empty( $data['url'] ) ? $data['url'] : $this->get_affiliate_url( $asin );

		if ( empty( $data['image'] ) ) {
			$data['image'] = AA_SNIPPETS_URL . 'assets/img/placeholder.png';
		}

		$data['is_fallback'] = false;
		$data['updated_at']  = current_time( 'mysql' );

		$cache_hours = intval( get_option( 'aa_cache_expiry', 24 ) );
		if ( $cache_hours <= 0 ) {
			$cache_hours = 24;
		}

		set_transient( $transient_key, $data, $cache_hours * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Resolve the marketplace domain (e.g. www.amazon.com) for the configured locale.
	 */
	private function get_marketplace_domain() {
		$marketplace = get_option( 'aa_marketplace', 'US' );
		$markets     = self::get_marketplaces();
		return isset( $markets[ $marketplace ] ) ? $markets[ $marketplace ]['marketplace_domain'] : 'www.amazon.com';
	}

	/**
	 * Generate Fallback Product Data.
	 */
	public function get_fallback_product( $asin, $error_message = '' ) {
		return array(
			'asin'         => $asin,
			'title'        => sprintf( __( 'Amazon Product (ASIN: %s)', 'amazon-associates-snippets' ), $asin ),
			'url'          => $this->get_affiliate_url( $asin ),
			'image'        => AA_SNIPPETS_URL . 'assets/img/placeholder.png',
			'price'        => '',
			'saving_basis' => '',
			'is_prime'     => false,
			'features'     => array(),
			'brand'        => 'Amazon',
			'is_fallback'  => true,
			'error'        => $error_message,
			'updated_at'   => current_time( 'mysql' ),
		);
	}
}
