<?php
/**
 * Keepa Price-History Adapter
 *
 * Enriches Amazon products with price-trend data from the Keepa API
 * (https://api.keepa.com/product). Returns a normalized list of
 * [timestamp_ms, price_usd] points suitable for a sparkline, so publishers can
 * show the price history customers pay for.
 *
 * The HTTP layer is isolated in a protected method so tests can stub it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Keepa_Price_Adapter {

	/**
	 * Keepa product endpoint.
	 */
	const API_BASE_URL = 'https://api.keepa.com/product';

	/**
	 * Fetch the Amazon price history for an ASIN.
	 *
	 * @param string $asin      ASIN.
	 * @param int    $domain_id Keepa domain id (1 = US). Optional.
	 * @return array[] List of [ts => ms, price => USD] points (empty on failure).
	 */
	public function get_price_history( $asin, $domain_id = 0 ) {
		$key = trim( (string) get_option( 'aa_keepa_access_key', '' ) );

		if ( '' === $key ) {
			return array();
		}

		$domain     = $domain_id ? (int) $domain_id : (int) get_option( 'aa_keepa_domain', 1 );
		$asin       = strtoupper( trim( $asin ) );
		$url        = add_query_arg(
			array(
				'key'     => $key,
				'domain'  => $domain,
				'asin'    => $asin,
				'stats'   => 180,
				'history' => 1,
			),
			self::API_BASE_URL
		);

		$response = $this->request( $url );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$status  = wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status || ! is_array( $decoded ) || empty( $decoded['products'][0]['csv'] ) ) {
			return array();
		}

		$csv = $decoded['products'][0]['csv'];

		// csv[0] is the AMAZON price history: [keepaMinute, priceCent, ...].
		if ( ! isset( $csv[0] ) || ! is_array( $csv[0] ) ) {
			return array();
		}

		return $this->parse_history( $csv[0] );
	}

	/**
	 * Convert a Keepa AMAZON history CSV into [ts, price] points.
	 *
	 * Keepa timestamps are minutes since epoch; prices are cents (a negative
	 * value means "out of stock", which we skip).
	 *
	 * @param array $csv Keepa AMAZON price history array.
	 * @return array[]
	 */
	protected function parse_history( array $csv ) {
		$points = array();
		$count  = count( $csv );

		for ( $i = 0; $i + 1 < $count; $i += 2 ) {
			$minutes = (int) $csv[ $i ];
			$price   = (int) $csv[ $i + 1 ];

			if ( $price < 0 ) {
				continue;
			}

			$points[] = array(
				'ts'    => $minutes * 60 * 1000,
				'price' => $price / 100,
			);
		}

		return $points;
	}

	/**
	 * Perform the HTTP GET (override in tests).
	 *
	 * @param string $url Request URL.
	 * @return array|WP_Error
	 */
	protected function request( $url ) {
		return wp_remote_get(
			$url,
			array(
				'timeout' => 15,
			)
		);
	}
}
