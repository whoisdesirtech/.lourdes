<?php
/**
 * Normalized Product Collection
 *
 * A provider-agnostic list of product arrays plus any errors encountered while
 * assembling them. Implements IteratorAggregate so callers can loop over the
 * items directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Product_Collection implements IteratorAggregate {

	/**
	 * Provider id this collection belongs to.
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Normalized product arrays.
	 *
	 * @var array[]
	 */
	private $items = array();

	/**
	 * Human-readable error messages encountered while building the collection.
	 *
	 * @var string[]
	 */
	private $errors = array();

	/**
	 * Constructor.
	 *
	 * @param string  $provider Provider id.
	 * @param array[] $items    Optional pre-populated product arrays.
	 */
	public function __construct( $provider = '', array $items = array() ) {
		$this->provider = strtolower( trim( (string) $provider ) );
		foreach ( $items as $item ) {
			$this->add( $item );
		}
	}

	/**
	 * Add a product to the collection.
	 *
	 * @param array|null $item Normalized product array.
	 */
	public function add( $item ) {
		if ( is_array( $item ) && ! empty( $item['title'] ) ) {
			$this->items[] = $item;
		}
	}

	/**
	 * Record an error message for this collection.
	 *
	 * @param string|WP_Error $error Error message or WP_Error object.
	 */
	public function add_error( $error ) {
		if ( is_wp_error( $error ) ) {
			$this->errors[] = $error->get_error_message();
		} elseif ( is_string( $error ) && '' !== trim( (string) $error ) ) {
			$this->errors[] = $error;
		}
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
	 * Get the number of products in the collection.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Get the first product, or null when empty.
	 *
	 * @return array|null
	 */
	public function first() {
		return ! empty( $this->items ) ? $this->items[0] : null;
	}

	/**
	 * Get the product arrays.
	 *
	 * @return array[]
	 */
	public function items() {
		return $this->items;
	}

	/**
	 * Alias of items() for idiomatic access.
	 *
	 * @return array[]
	 */
	public function to_array() {
		return $this->items;
	}

	/**
	 * Get recorded error messages.
	 *
	 * @return string[]
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Whether the collection has errors.
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Iterator over the product items.
	 *
	 * @return ArrayIterator
	 */
	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new ArrayIterator( $this->items );
	}
}
