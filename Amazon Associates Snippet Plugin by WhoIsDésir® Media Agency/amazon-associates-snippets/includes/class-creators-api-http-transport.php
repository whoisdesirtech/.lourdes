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
		$payload = array(
			'itemIds'     => array_values( $asins ),
			'itemIdType'  => 'ASIN',
			'marketplace' => $marketplace_domain,
			'partnerTag'  => $partner_tag,
			'resources'   => self::get_resources(),
		);

		$response = $this->post_payload( $payload, $marketplace_domain );

		if ( is_wp_error( $response ) && $this->is_token_error( $response ) ) {
			$response = $this->handle_token_error( $payload, $marketplace_domain );
		}

		return $response;
	}

	/**
	 * Send a GetItems request with a Bearer token.
	 *
	 * @param array  $payload           JSON payload body.
	 * @param string $marketplace_domain e.g. www.amazon.com.
	 * @param bool   $prefer_fresh      Bypass the manual/cached token.
	 * @return array|WP_Error
	 */
	private function post_payload( array $payload, $marketplace_domain, $prefer_fresh = false ) {
		$token = $this->oauth_client->get_token( $prefer_fresh );

		if ( empty( $token ) ) {
			return new WP_Error(
				'creators_api_token_missing',
				__( 'Could not obtain a Creators API access token.', 'amazon-associates-snippets' )
			);
		}

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
	 * Recover from an expired or rejected Bearer token.
	 *
	 * A cached client-credentials token is invalidated and one retry is made
	 * with a fresh token. A manually pasted Bearer token cannot be refreshed
	 * by the plugin, so when it is rejected and no client credentials are
	 * configured a clear error is returned instead.
	 *
	 * @param array  $payload           JSON payload body.
	 * @param string $marketplace_domain e.g. www.amazon.com.
	 * @return array|WP_Error
	 */
	private function handle_token_error( array $payload, $marketplace_domain ) {
		$manual    = $this->oauth_client->get_manual_token();
		$can_fetch = $this->oauth_client->has_client_credentials();

		if ( ! empty( $manual ) && ! $can_fetch ) {
			return new WP_Error(
				'creators_api_token_expired',
				__( 'The manually pasted Creators API Bearer access token has expired or is invalid (Amazon says "Token has expired"). Please paste a fresh access token on the Credentials tab, or configure your Credential ID / Secret so the plugin can request tokens automatically.', 'amazon-associates-snippets' )
			);
		}

		$this->oauth_client->clear_cached_token();

		$retry = $this->post_payload( $payload, $marketplace_domain, true );

		if ( is_wp_error( $retry ) && $this->is_token_error( $retry ) ) {
			if ( ! empty( $manual ) ) {
				return new WP_Error(
					'creators_api_token_expired',
					__( 'The manually pasted Bearer access token is expired or invalid, and the automatic token refresh also failed. Please update the Access Token field on the Credentials tab or verify your Credential ID / Secret.', 'amazon-associates-snippets' )
				);
			}
			return $retry;
		}

		return $retry;
	}

	/**
	 * Whether a transport error indicates an expired or invalid access token.
	 *
	 * @param WP_Error $error Transport error.
	 * @return bool
	 */
	private function is_token_error( $error ) {
		if ( ! is_wp_error( $error ) ) {
			return false;
		}

		$data   = $error->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 0;

		if ( 401 === $status ) {
			return true;
		}

		$message = strtolower( (string) $error->get_error_message() );

		if ( false !== strpos( $message, 'token' )
			&& ( false !== strpos( $message, 'expired' ) || false !== strpos( $message, 'invalid' ) ) ) {
			return true;
		}

		return false !== strpos( $message, 'unauthorized' );
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
