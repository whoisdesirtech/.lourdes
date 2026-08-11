<?php
/**
 * Creators API Response Normalizer
 *
 * Converts a raw Creators API GetItems response (lowerCamelCase) into the
 * plugin's canonical internal product structure so shortcodes and helpers
 * never have to know about Amazon's response format.
 *
 * Canonical structure:
 *   [
 *     'asin'         => '',
 *     'title'        => '',
 *     'url'          => '',
 *     'image'        => '',
 *     'price'        => '',
 *     'saving_basis' => '',
 *     'is_prime'     => false,
 *     'features'     => [],
 *     'brand'        => '',
 *   ]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Amazon_Response_Normalizer {

	/**
	 * Normalize a raw Creators API GetItems response into the internal structure.
	 *
	 * @param array  $response Decoded Creators API response.
	 * @param string $asin     Requested ASIN.
	 * @return array|null Canonical product array, or null when the item was not found.
	 */
	public function normalize( array $response, $asin ) {
		$item  = $this->find_item( $response, $asin );
		if ( ! is_array( $item ) ) {
			return null;
		}

		$listing = $this->find_offers_listing( $item );

		return array(
			'asin'         => strtoupper( trim( (string) $asin ) ),
			'title'        => $this->extract( $item, array( 'itemInfo', 'title', 'displayValue' ), 'Amazon Product' ),
			'url'          => isset( $item['detailPageURL'] ) ? (string) $item['detailPageURL'] : '',
			'image'        => $this->extract_image( $item ),
			'price'        => $this->extract( $listing, array( 'price', 'money', 'displayAmount' ), '' ),
			'saving_basis' => $this->extract( $listing, array( 'price', 'savingBasis', 'money', 'displayAmount' ), '' ),
			'is_prime'     => false,
			'features'     => $this->extract_features( $item ),
			'brand'        => $this->extract( $item, array( 'itemInfo', 'byLineInfo', 'brand', 'displayValue' ), '' ),
		);
	}

	/**
	 * Locate the requested item within a GetItems response.
	 *
	 * The Creators API can reorder items and reports failed lookups in the
	 * errors container, so items are matched by ASIN rather than position.
	 *
	 * @param array  $response Decoded response.
	 * @param string $asin     Requested ASIN.
	 * @return array|null
	 */
	private function find_item( array $response, $asin ) {
		$container = isset( $response['itemResults']['items'] ) ? $response['itemResults']['items'] : null;
		if ( null === $container && isset( $response['itemsResult']['items'] ) ) {
			$container = $response['itemsResult']['items'];
		}

		if ( ! is_array( $container ) ) {
			return null;
		}

		$needle = strtoupper( trim( (string) $asin ) );

		foreach ( $container as $item ) {
			if ( is_array( $item ) && isset( $item['asin'] ) && strtoupper( (string) $item['asin'] ) === $needle ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Find the best offer listing. Prefers the Buy Box winning listing.
	 *
	 * @param array $item Decoded item.
	 * @return array
	 */
	private function find_offers_listing( array $item ) {
		$listings = isset( $item['offersV2']['listings'] ) ? $item['offersV2']['listings'] : array();

		if ( ! is_array( $listings ) ) {
			return array();
		}

		foreach ( $listings as $listing ) {
			if ( is_array( $listing ) && ! empty( $listing['isBuyBoxWinner'] ) ) {
				return $listing;
			}
		}

		return isset( $listings[0] ) && is_array( $listings[0] ) ? $listings[0] : array();
	}

	/**
	 * Extract the largest available primary image URL.
	 */
	private function extract_image( array $item ) {
		foreach ( array( 'large', 'medium', 'small' ) as $size ) {
			$url = $this->extract( $item, array( 'images', 'primary', $size, 'url' ), '' );
			if ( ! empty( $url ) ) {
				return $url;
			}
		}
		return '';
	}

	/**
	 * Extract up to four feature bullets.
	 */
	private function extract_features( array $item ) {
		$features = $this->extract( $item, array( 'itemInfo', 'features', 'displayValues' ), array() );

		if ( ! is_array( $features ) ) {
			return array();
		}

		return array_slice( $features, 0, 4 );
	}

	/**
	 * Defensively traverse a nested array and return a fallback when missing.
	 */
	private function extract( array $data, array $path, $fallback = '' ) {
		$current = $data;

		foreach ( $path as $key ) {
			if ( ! is_array( $current ) || ! array_key_exists( $key, $current ) ) {
				return $fallback;
			}
			$current = $current[ $key ];
		}

		return $current;
	}
}
