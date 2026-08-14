<?php
/**
 * Walmart Affiliate (Content Provider) API Client
 *
 * Wraps the Walmart Affiliate API v2 (developer.api.walmart.com/api-proxy/
 * service/affil/product/v2). Requests are authenticated with the RSA-signed
 * WM_SEC.AUTH_SIGNATURE headers described by Walmart's affiliate quickstart.
 *
 * Requests are sent through a single protected request() method so tests can
 * subclass and stub the HTTP layer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Walmart_API {

	/**
	 * Affiliate API v2 base URL.
	 */
	const API_BASE_URL = 'https://developer.api.walmart.com/api-proxy/service/affil/product/v2';

	/**
	 * Get a single item by Walmart item id.
	 *
	 * @param string $item_id      Walmart item id.
	 * @param string $marketplace  Marketplace code (US, CA, MX).
	 * @return array|WP_Error
	 */
	public function get_item( $item_id, $marketplace = 'US' ) {
		$path = '/items/' . rawurlencode( $item_id );
		return $this->request( $path, array(), $marketplace );
	}

	/**
	 * Keyword search the Walmart catalog.
	 *
	 * @param string $query        Search keywords.
	 * @param int    $limit        Maximum results.
	 * @param string $marketplace  Marketplace code.
	 * @return array|WP_Error
	 */
	public function search( $query, $limit = 10, $marketplace = 'US' ) {
		$params = array(
			'query' => $query,
			'count' => max( 1, min( 25, (int) $limit ) ),
		);
		return $this->request( '/search', $params, $marketplace );
	}

	/**
	 * Send an authenticated GET to the affiliate API.
	 *
	 * @param string $path        API path (e.g. '/search').
	 * @param array  $params      Query parameters.
	 * @param string $marketplace Marketplace code.
	 * @return array|WP_Error
	 */
	protected function request( $path, array $params = array(), $marketplace = 'US' ) {
		$publisher_id = trim( (string) get_option( 'aa_walmart_publisher_id', '' ) );

		$query = array_merge( $params, array( 'publisherId' => $publisher_id ) );
		$url   = add_query_arg( $query, self::API_BASE_URL . $path );

		$headers = $this->build_headers();

		if ( empty( $headers ) ) {
			return new WP_Error(
				'walmart_not_configured',
				__( 'Walmart affiliate credentials are not configured.', 'amazon-associates-snippets' )
			);
		}

		$response = $this->execute( $url, $headers );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$raw    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $raw, true );

		if ( 200 !== $status || ! is_array( $decoded ) ) {
			$message = __( 'Walmart API request failed.', 'amazon-associates-snippets' );
			if ( is_array( $decoded ) && ! empty( $decoded['errors'][0]['message'] ) ) {
				$message = $decoded['errors'][0]['message'];
			} elseif ( is_array( $decoded ) && ! empty( $decoded['message'] ) ) {
				$message = $decoded['message'];
			}
			return new WP_Error( 'walmart_api_error', $message, array( 'status' => $status ) );
		}

		return $decoded;
	}

	/**
	 * Perform the HTTP GET (override in tests).
	 *
	 * @param string $url     Fully qualified URL.
	 * @param array  $headers Request headers.
	 * @return array|WP_Error
	 */
	protected function execute( $url, array $headers ) {
		return wp_remote_get(
			$url,
			array(
				'headers' => $headers,
				'timeout' => 15,
			)
		);
	}

	/**
	 * Build the signed request headers.
	 *
	 * Returns an empty array when the credentials are missing so callers can
	 * surface a clear "not configured" error.
	 *
	 * @return array
	 */
	protected function build_headers() {
		$consumer_id = trim( (string) get_option( 'aa_walmart_consumer_id', '' ) );
		$key_version = trim( (string) get_option( 'aa_walmart_key_version', '' ) );
		$private_key = trim( (string) get_option( 'aa_walmart_private_key', '' ) );

		if ( '' === $consumer_id || '' === $key_version || '' === $private_key ) {
			return array();
		}

		$timestamp = (string) time();

		$signature = $this->sign( $consumer_id, $timestamp, $key_version, $private_key );
		if ( '' === $signature ) {
			return array();
		}

		return array(
			'WM_CONSUMER.ID'         => $consumer_id,
			'WM_SEC.KEY_VERSION'     => $key_version,
			'WM_CONSUMER.INTIMESTAMP' => $timestamp,
			'WM_SEC.AUTH_SIGNATURE'  => $signature,
			'Accept'                 => 'application/json',
		);
	}

	/**
	 * Produce the RSA-SHA256 base64 signature Walmart requires.
	 *
	 * @param string $consumer_id Consumer id.
	 * @param string $timestamp   Unix timestamp (string).
	 * @param string $key_version Key version.
	 * @param string $private_key PEM private key.
	 * @return string Base64 signature, or '' on failure.
	 */
	protected function sign( $consumer_id, $timestamp, $key_version, $private_key ) {
		$payload = $consumer_id . "\n" . $timestamp . "\n" . $key_version . "\n";

		$signature = '';
		if ( ! openssl_sign( $payload, $signature, $private_key, OPENSSL_ALGO_SHA256 ) ) {
			return '';
		}

		return base64_encode( $signature );
	}
}
