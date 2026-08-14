<?php
/**
 * Normalized Product Reference
 *
 * Identifies a product across any provider using a "provider:product_id"
 * notation (e.g. "amazon:B0GWHKHZRL", "walmart:1234567", optionally with a
 * ":marketplace" suffix). This is the normalized unit the provider layer
 * consumes so shortcodes and blocks never need to know the provider format.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Product_Reference {

	/**
	 * Provider id (e.g. 'amazon', 'walmart').
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Provider product id (e.g. ASIN, Walmart itemId).
	 *
	 * @var string
	 */
	private $product_id;

	/**
	 * Optional marketplace/region code (e.g. 'US', 'CA').
	 *
	 * @var string
	 */
	private $marketplace;

	/**
	 * Constructor.
	 *
	 * @param string $provider    Provider id.
	 * @param string $product_id  Product id on the provider.
	 * @param string $marketplace Optional marketplace/region code.
	 */
	public function __construct( $provider, $product_id, $marketplace = '' ) {
		$this->provider    = strtolower( trim( (string) $provider ) );
		$this->product_id  = trim( (string) $product_id );
		$this->marketplace = strtoupper( trim( (string) $marketplace ) );
	}

	/**
	 * Parse a reference string into a reference object.
	 *
	 * Supported formats:
	 *   - 'amazon:B0GWHKHZRL'
	 *   - 'amazon:B0GWHKHZRL:US'
	 *   - 'walmart:1234567'
	 *   - 'B0GWHKHZRL' (bare value defaults to amazon)
	 *
	 * @param string $input Reference string.
	 * @return AA_Product_Reference|null Null when the input is empty or malformed.
	 */
	public static function parse( $input ) {
		$input = trim( (string) $input );

		if ( '' === $input ) {
			return null;
		}

		if ( false !== strpos( $input, ':' ) ) {
			$parts       = array_map( 'trim', explode( ':', $input ) );
			$provider    = array_shift( $parts );
			$product_id  = array_shift( $parts );
			$marketplace = ! empty( $parts ) ? array_shift( $parts ) : '';

			if ( '' === (string) $provider || '' === (string) $product_id ) {
				return null;
			}

			return new self( $provider, $product_id, $marketplace );
		}

		return new self( 'amazon', $input );
	}

	/**
	 * Get the provider id.
	 *
	 * @return string
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * Get the provider product id.
	 *
	 * @return string
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get the marketplace/region code (may be empty).
	 *
	 * @return string
	 */
	public function get_marketplace() {
		return $this->marketplace;
	}

	/**
	 * Serialize back to "provider:product_id[:marketplace]".
	 *
	 * @return string
	 */
	public function __toString() {
		$string = $this->provider . ':' . $this->product_id;
		if ( '' !== $this->marketplace ) {
			$string .= ':' . $this->marketplace;
		}
		return $string;
	}
}
