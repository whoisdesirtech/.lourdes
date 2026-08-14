<?php
/**
 * Amazon Product Provider
 *
 * Implements AA_Product_Provider on top of the existing Amazon Creators API
 * stack (OAuth client, dual SDK/HTTP transports, response normalizer) so the
 * plugin can participate in the multi-provider registry introduced by the
 * 2026 product audit.
 *
 * The facade previously lived in AA_Amazon_API; that class is retained as a
 * thin compatibility alias (see class-amazon-api.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Amazon_Provider implements AA_Product_Provider {

	/**
	 * Single instance.
	 *
	 * @var AA_Amazon_Provider
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
	 *
	 * @return AA_Amazon_Provider
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
	 * Provider id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'amazon';
	}

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'Amazon';
	}

	/**
	 * Provider is configured when a partner tag is set and Creators API
	 * credentials (or a manual token) are present.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return '' !== trim( (string) get_option( 'aa_partner_tag', '' ) )
			&& $this->oauth_client->has_credentials();
	}

	/**
	 * Amazon's Creators API is ASIN-only; it does not support keyword search.
	 *
	 * @return bool
	 */
	public function supports_search() {
		return false;
	}

	/**
	 * Fetch a single normalized product by reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return array|null
	 */
	public function get_product( AA_Product_Reference $reference ) {
		return $this->get_item( $reference->get_product_id(), $reference->get_marketplace() );
	}

	/**
	 * Fetch many products by reference, returning a collection.
	 *
	 * @param AA_Product_Reference[] $references Product references.
	 * @return AA_Product_Collection
	 */
	public function get_products( array $references ) {
		$collection = new AA_Product_Collection( $this->get_id() );

		foreach ( $references as $reference ) {
			$ref = $reference instanceof AA_Product_Reference
				? $reference
				: AA_Product_Reference::parse( (string) $reference );

			if ( null === $ref ) {
				continue;
			}

			if ( 'amazon' !== $ref->get_provider() ) {
				$collection->add_error(
					sprintf(
						/* translators: %s is a provider id. */
						__( 'Reference "%s" does not belong to the Amazon provider.', 'amazon-associates-snippets' ),
						$ref->get_provider()
					)
				);
				continue;
			}

			$item = $this->get_item( $ref->get_product_id(), $ref->get_marketplace() );
			if ( $item ) {
				$collection->add( $item );
			}
		}

		return $collection;
	}

	/**
	 * Amazon does not support keyword search; return an empty collection that
	 * explains why.
	 *
	 * @param AA_Product_Query $query Search query.
	 * @return AA_Product_Collection
	 */
	public function search_products( AA_Product_Query $query ) {
		$collection = new AA_Product_Collection( $this->get_id() );
		$collection->add_error(
			__( 'Amazon returns products by ASIN only. Use a lookup (e.g. amazon:B0GWHKHZRL) instead of a keyword search.', 'amazon-associates-snippets' )
		);
		return $collection;
	}

	/**
	 * Build a tracked affiliate URL for a reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return string
	 */
	public function build_affiliate_url( AA_Product_Reference $reference ) {
		return $this->get_affiliate_url( $reference->get_product_id(), $reference->get_marketplace() );
	}

	/**
	 * Get the marketplace map.
	 *
	 * @return array
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
	 *
	 * @return AA_Creators_API_Transport
	 */
	public function get_transport() {
		return $this->transport;
	}

	/**
	 * Build Affiliate URL for ASIN or URL, optionally for a specific marketplace.
	 *
	 * @param string $asin_or_url ASIN or full URL.
	 * @param string $marketplace Optional marketplace code override.
	 * @return string
	 */
	public function get_affiliate_url( $asin_or_url, $marketplace = '' ) {
		$partner_tag   = trim( (string) get_option( 'aa_partner_tag', '' ) );
		$market_code   = '' !== $marketplace ? strtoupper( $marketplace ) : get_option( 'aa_marketplace', 'US' );
		$markets       = self::get_marketplaces();
		$domain        = isset( $markets[ $market_code ] ) ? $markets[ $market_code ]['domain'] : 'amazon.com';

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
	 *
	 * @param string $asin        ASIN.
	 * @param string $marketplace Optional marketplace code override.
	 * @return array|false
	 */
	public function get_item( $asin, $marketplace = '' ) {
		$asin = strtoupper( trim( $asin ) );
		if ( empty( $asin ) ) {
			return false;
		}

		$partner_tag   = trim( (string) get_option( 'aa_partner_tag', '' ) );
		$transient_key = 'aa_item_' . md5( $asin . '_' . $partner_tag );
		$cached_data   = get_transient( $transient_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		if ( empty( $partner_tag ) || ! $this->oauth_client->has_credentials() ) {
			return $this->get_fallback_product( $asin, __( 'Creators API credentials missing. Showing fallback link.', 'amazon-associates-snippets' ) );
		}

		$marketplace_domain = $this->get_marketplace_domain( $marketplace );

		$response = $this->transport->get_items( array( $asin ), $marketplace_domain, $partner_tag );

		if ( is_wp_error( $response ) ) {
			return $this->get_fallback_product( $asin, $response->get_error_message() );
		}

		$data = $this->normalizer->normalize( $response, $asin );

		if ( null === $data ) {
			return $this->get_fallback_product( $asin, __( 'Item not found in Amazon Creators API response.', 'amazon-associates-snippets' ) );
		}

		$data['url'] = ! empty( $data['url'] ) ? $data['url'] : $this->get_affiliate_url( $asin, $marketplace );

		if ( empty( $data['image'] ) ) {
			$data['image'] = AA_SNIPPETS_URL . 'assets/img/placeholder.png';
		}

		$data['provider']    = $this->get_id();
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
	 * Resolve the marketplace domain (e.g. www.amazon.com) for the configured
	 * or supplied locale.
	 *
	 * @param string $marketplace Optional marketplace code override.
	 * @return string
	 */
	private function get_marketplace_domain( $marketplace = '' ) {
		$market_code = '' !== $marketplace ? strtoupper( $marketplace ) : get_option( 'aa_marketplace', 'US' );
		$markets     = self::get_marketplaces();
		return isset( $markets[ $market_code ] ) ? $markets[ $market_code ]['marketplace_domain'] : 'www.amazon.com';
	}

	/**
	 * Generate Fallback Product Data.
	 *
	 * @param string $asin           ASIN.
	 * @param string $error_message  Optional error context.
	 * @return array
	 */
	public function get_fallback_product( $asin, $error_message = '' ) {
		return array(
			'asin'         => $asin,
			'provider'     => $this->get_id(),
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
