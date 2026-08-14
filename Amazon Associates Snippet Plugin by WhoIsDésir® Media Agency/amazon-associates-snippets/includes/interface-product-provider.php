<?php
/**
 * Product Provider Interface
 *
 * Every affiliate product source (Amazon, Walmart, ...) implements this
 * interface so the rest of the plugin can treat them uniformly through the
 * AA_Product_Provider_Registry.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface AA_Product_Provider {

	/**
	 * Stable provider id (e.g. 'amazon').
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Human-readable provider label.
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Whether the provider is configured with the credentials it needs.
	 *
	 * @return bool
	 */
	public function is_configured();

	/**
	 * Whether the provider supports keyword search (vs. id-only lookup).
	 *
	 * @return bool
	 */
	public function supports_search();

	/**
	 * Fetch a single normalized product by reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return array|null Normalized product array or null when not found.
	 */
	public function get_product( AA_Product_Reference $reference );

	/**
	 * Fetch many products by reference.
	 *
	 * @param AA_Product_Reference[] $references Product references.
	 * @return AA_Product_Collection
	 */
	public function get_products( array $references );

	/**
	 * Keyword search the provider catalog.
	 *
	 * @param AA_Product_Query $query Search query.
	 * @return AA_Product_Collection
	 */
	public function search_products( AA_Product_Query $query );

	/**
	 * Build a tracked affiliate URL for a reference.
	 *
	 * @param AA_Product_Reference $reference Product reference.
	 * @return string
	 */
	public function build_affiliate_url( AA_Product_Reference $reference );
}
