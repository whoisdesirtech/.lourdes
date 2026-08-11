<?php
/**
 * Built-in Creators API HTTP Transport
 *
 * Dependency-free fallback that talks to the Creators API directly using the
 * WordPress HTTP API and a Bearer access token obtained from
 * AA_Creators_OAuth_Client.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Creators_API_Http_Transport extends AA_Creators_API_Transport {

	/**
	 * Creators API base URL.
	 */
	const API_BASE_URL = 'https://creatorsapi.amazon/catalog/v1/getItems';

	/**
	 * {@inheritdoc}
	 */
	public function get_items( array $asins, $marketplace_domain, $partner_tag ) {
		$token = $this->oauth_client->get_token();

		if ( empty( $token ) ) {
			return new WP_Error(
				'creators_api_token_missing',
				__( 'Could not obtain a Creators API access token.', 'amazon-associates-snippets' )
			);
		}

		$payload = array(
			'itemIds'     => array_values( $asins ),
			'itemIdType'  => 'ASIN',
			'marketplace' => $marketplace_domain,
			'partnerTag'  => $partner_tag,
			'resources'   => self::get_resources(),
		);

		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $token,
			'x-marketplace' => $marketplace_domain,
		);

		$response = $this->post_json( self::API_BASE_URL, $headers, wp_json_encode( $payload ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status  = wp_remote_retrieve_response_code( $response );
		$raw     = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $raw, true );

		if ( 200 !== $status || ! is_array( $decoded ) ) {
			return new WP_Error(
				'creators_api_error',
				$this->api_error_message( $decoded, $status ),
				array( 'status' => $status )
			);
		}

		return $decoded;
	}

	/**
	 * Perform the Creators API HTTP request.
	 *
	 * Override in subclasses/tests to stub the transport.
	 *
	 * @param string $url     Request URL.
	 * @param array  $headers Request headers.
	 * @param string $body    JSON request body.
	 * @return array|WP_Error
	 */
	protected function post_json( $url, array $headers, $body ) {
		return wp_remote_post(
			$url,
			array(
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => $body,
				'timeout' => 15,
			)
		);
	}
}
