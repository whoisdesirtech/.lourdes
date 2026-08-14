<?php
/**
 * Normalized Product Query
 *
 * A keyword search request against a single provider (used by providers that
 * support catalog search, e.g. Walmart). Amazon's Creators API is ASIN-only
 * and does not support keyword search, so the Amazon provider reports
 * supports_search() === false.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Product_Query {

	/**
	 * Provider id the query targets.
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Free-text search keywords.
	 *
	 * @var string
	 */
	private $keywords;

	/**
	 * Marketplace/region code.
	 *
	 * @var string
	 */
	private $marketplace;

	/**
	 * Maximum number of results to return.
	 *
	 * @var int
	 */
	private $limit;

	/**
	 * Result page (1-based).
	 *
	 * @var int
	 */
	private $page;

	/**
	 * Constructor.
	 *
	 * @param string $provider    Provider id.
	 * @param string $keywords    Search keywords.
	 * @param string $marketplace Marketplace/region code.
	 * @param int    $limit       Maximum results.
	 * @param int    $page        Result page.
	 */
	public function __construct( $provider, $keywords = '', $marketplace = '', $limit = 10, $page = 1 ) {
		$this->provider    = strtolower( trim( (string) $provider ) );
		$this->keywords    = trim( (string) $keywords );
		$this->marketplace = strtoupper( trim( (string) $marketplace ) );
		$this->limit       = max( 1, (int) $limit );
		$this->page        = max( 1, (int) $page );
	}

	/**
	 * Build a query from an associative array (REST/shortcode friendly).
	 *
	 * @param array $args Associative array with provider/q/keywords/marketplace/limit/page.
	 * @return AA_Product_Query
	 */
	public static function from_array( array $args ) {
		return new self(
			isset( $args['provider'] ) ? $args['provider'] : 'amazon',
			isset( $args['keywords'] ) ? $args['keywords'] : ( isset( $args['q'] ) ? $args['q'] : '' ),
			isset( $args['marketplace'] ) ? $args['marketplace'] : '',
			isset( $args['limit'] ) ? $args['limit'] : 10,
			isset( $args['page'] ) ? $args['page'] : 1
		);
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
	 * Get the search keywords.
	 *
	 * @return string
	 */
	public function get_keywords() {
		return $this->keywords;
	}

	/**
	 * Get the marketplace/region code.
	 *
	 * @return string
	 */
	public function get_marketplace() {
		return $this->marketplace;
	}

	/**
	 * Get the maximum result count.
	 *
	 * @return int
	 */
	public function get_limit() {
		return $this->limit;
	}

	/**
	 * Get the result page.
	 *
	 * @return int
	 */
	public function get_page() {
		return $this->page;
	}
}
