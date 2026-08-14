<?php
/**
 * Walmart Product Provider
 *
 * Implements AA_Product_Provider on top of the Walmart Affiliate API v2. Adds
 * keyword search (which Amazon lacks) so the provider registry supports the
 * "amazon:B0...,walmart:..." comparison vision from the 2026 audit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Walmart_Provider implements AA_Product_Provider {

	/**
	 * Single instance.
	 *
	 * @var AA_Walmart_Provider
	 */
	private static $instance = null;

	/**
	 * Walmart API client.
	 *
	 * @var AA_Walmart_API
	 */
	private $api;

	/**
	 * Get single instance.
	 *
	 * @return AA_Walmart_Provider
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
	 * @param AA_Walmart_API|null $api Walmart API client.
	 */
	public function __construct( $api = null ) {
		$this->api = $api ? $api : new AA_Walmart_API();
	}

	/**
	 * Provider id.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'walmart';
	}

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	public function get_label() {
		return 'Walmart';
	}

	/**
	 * Provider is configured when the affiliate credentials are present.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return '' !== trim( (string) get_option( 'aa_walmart_consumer_id', '' ) )
			&& '' !== trim( (string) get_option( 'aa_walmart_private_key', '' ) )
			&& '' !== trim( (string) get_option( 'aa_walmart_publisher_id', '' ) );
	}

	/**
	 * Walmart supports keyword search.
	 *
	 * @return bool
	 */
	public function supports_search() {
		return true;
	}

	/**
	 * Fetch a single normalized product by reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return array|null
	 */
	public function get_product( AA_Product_Reference $reference ) {
		$response = $this->api->get_item( $reference->get_product_id(), $reference->get_marketplace() );

		if ( is_wp_error( $response ) ) {
			return $this->fallback( $reference->get_product_id(), $response->get_error_message() );
		}

		$items = $this->extract_items( $response );
		if ( empty( $items ) ) {
			return $this->fallback( $reference->get_product_id(), __( 'Item not found in Walmart catalog.', 'amazon-associates-snippets' ) );
		}

		return $this->normalize_item( $items[0], $reference );
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

			if ( 'walmart' !== $ref->get_provider() ) {
				$collection->add_error(
					sprintf(
						/* translators: %s is a provider id. */
						__( 'Reference "%s" does not belong to the Walmart provider.', 'amazon-associates-snippets' ),
						$ref->get_provider()
					)
				);
				continue;
			}

			$item = $this->get_product( $ref );
			if ( $item ) {
				$collection->add( $item );
			}
		}

		return $collection;
	}

	/**
	 * Keyword search the Walmart catalog.
	 *
	 * @param AA_Product_Query $query Search query.
	 * @return AA_Product_Collection
	 */
	public function search_products( AA_Product_Query $query ) {
		$collection = new AA_Product_Collection( $this->get_id() );

		if ( '' === trim( $query->get_keywords() ) ) {
			$collection->add_error( __( 'A search keyword is required.', 'amazon-associates-snippets' ) );
			return $collection;
		}

		$response = $this->api->search( $query->get_keywords(), $query->get_limit(), $query->get_marketplace() );

		if ( is_wp_error( $response ) ) {
			$collection->add_error( $response->get_error_message() );
			return $collection;
		}

		$items = $this->extract_items( $response );
		if ( empty( $items ) ) {
			$collection->add_error( __( 'No matching Walmart products found.', 'amazon-associates-snippets' ) );
			return $collection;
		}

		foreach ( $items as $item ) {
			$collection->add( $this->normalize_item( $item ) );
		}

		return $collection;
	}

	/**
	 * Build a tracked affiliate URL for a reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return string
	 */
	public function build_affiliate_url( AA_Product_Reference $reference ) {
		$publisher_id = trim( (string) get_option( 'aa_walmart_publisher_id', '' ) );
		$base         = 'https://www.walmart.com/ip/' . rawurlencode( $reference->get_product_id() );

		if ( '' !== $publisher_id ) {
			$base = add_query_arg( 'publisherId', $publisher_id, $base );
		}

		return $base;
	}

	/**
	 * Extract the items array from a Walmart API response.
	 *
	 * @param array $response Decoded response.
	 * @return array
	 */
	private function extract_items( array $response ) {
		if ( isset( $response['items'] ) && is_array( $response['items'] ) ) {
			return $response['items'];
		}
		if ( isset( $response['item'] ) && is_array( $response['item'] ) ) {
			return array( $response['item'] );
		}
		return array();
	}

	/**
	 * Normalize a raw Walmart item into the canonical product structure.
	 *
	 * @param array                  $item      Raw Walmart item.
	 * @param AA_Product_Reference|null $reference Optional reference (provides marketplace).
	 * @return array
	 */
	private function normalize_item( array $item, $reference = null ) {
		$item_id   = isset( $item['itemId'] ) ? (string) $item['itemId'] : '';
		$title     = isset( $item['name'] ) ? (string) $item['name'] : '';
		$url       = isset( $item['productUrl'] ) ? (string) $item['productUrl'] : $this->build_affiliate_url(
			new AA_Product_Reference( 'walmart', $item_id, $reference ? $reference->get_marketplace() : '' )
		);
		$image     = isset( $item['largeImage'] ) ? (string) $item['largeImage']
			: ( isset( $item['mediumImage'] ) ? (string) $item['mediumImage'] : '' );
		$price     = isset( $item['salePrice'] ) ? '$' . $item['salePrice'] : '';
		$brand     = isset( $item['brandName'] ) ? (string) $item['brandName'] : '';

		return array(
			'asin'         => $item_id,
			'item_id'      => $item_id,
			'provider'     => $this->get_id(),
			'title'        => $title,
			'url'          => $url,
			'image'        => $image,
			'price'        => $price,
			'saving_basis' => '',
			'is_prime'     => false,
			'features'     => array(),
			'brand'        => $brand,
			'is_fallback'  => false,
			'updated_at'   => current_time( 'mysql' ),
		);
	}

	/**
	 * Generate a fallback product array when Walmart data is unavailable.
	 *
	 * @param string $item_id       Walmart item id.
	 * @param string $error_message Error context.
	 * @return array
	 */
	private function fallback( $item_id, $error_message = '' ) {
		return array(
			'asin'         => $item_id,
			'item_id'      => $item_id,
			'provider'     => $this->get_id(),
			'title'        => sprintf( __( 'Walmart Product (Item %s)', 'amazon-associates-snippets' ), $item_id ),
			'url'          => $this->build_affiliate_url( new AA_Product_Reference( 'walmart', $item_id ) ),
			'image'        => AA_SNIPPETS_URL . 'assets/img/placeholder.png',
			'price'        => '',
			'saving_basis' => '',
			'is_prime'     => false,
			'features'     => array(),
			'brand'        => 'Walmart',
			'is_fallback'  => true,
			'error'        => $error_message,
			'updated_at'   => current_time( 'mysql' ),
		);
	}
}
