<?php
/**
 * Creators API OAuth 2.0 Client Credentials Token Manager
 *
 * Requests access tokens from the Login with Amazon (LwA) / Cognito token
 * endpoint and caches them in WordPress transients. Tokens are valid for
 * approximately 3600 seconds and are reused until they are close to
 * expiration, at which point a fresh token is requested.
 *
 * Credential secrets and access tokens are never logged or echoed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Creators_OAuth_Client {

	/**
	 * Transient key used to cache the active access token.
	 */
	const TOKEN_TRANSIENT = 'aa_creators_access_token';

	/**
	 * Buffer (seconds) subtracted from expires_in so tokens refresh before expiry.
	 */
	const EXPIRY_BUFFER = 60;

	/**
	 * Single instance.
	 */
	private static $instance = null;

	/**
	 * Get single instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Whether Creators API credentials have been configured.
	 */
	public function has_credentials() {
		return ! empty( $this->get_credential_id() )
			&& ! empty( $this->get_credential_secret() )
			&& ! empty( $this->get_credential_version() );
	}

	/**
	 * Get the configured Credential ID.
	 */
	public function get_credential_id() {
		return trim( (string) get_option( 'aa_credential_id', '' ) );
	}

	/**
	 * Get the configured Credential Secret.
	 */
	public function get_credential_secret() {
		return trim( (string) get_option( 'aa_credential_secret', '' ) );
	}

	/**
	 * Get the configured Credential Version (e.g. 3.1, 3.2, 3.3, 2.1 ...).
	 */
	public function get_credential_version() {
		return trim( (string) get_option( 'aa_credential_version', '' ) );
	}

	/**
	 * Get a valid OAuth 2.0 access token, reusing the cached transient token
	 * when it is still valid. Returns false when no token can be obtained.
	 *
	 * @return string|false
	 */
	public function get_token() {
		$cached = get_transient( self::TOKEN_TRANSIENT );

		if ( is_array( $cached ) && ! empty( $cached['token'] ) ) {
			$expires_at = isset( $cached['expires_at'] ) ? (int) $cached['expires_at'] : 0;
			if ( 0 === $expires_at || time() < $expires_at ) {
				return $cached['token'];
			}
		}

		$token_data = $this->request_token();

		if ( is_wp_error( $token_data ) ) {
			return false;
		}

		if ( empty( $token_data['access_token'] ) ) {
			return false;
		}

		$expires_in = isset( $token_data['expires_in'] ) ? intval( $token_data['expires_in'] ) : 3600;
		if ( $expires_in <= self::EXPIRY_BUFFER ) {
			$expires_in = 3600;
		}

		$cache = array(
			'token'      => $token_data['access_token'],
			'expires_at' => time() + $expires_in,
		);

		set_transient( self::TOKEN_TRANSIENT, $cache, $expires_in - self::EXPIRY_BUFFER );

		return $token_data['access_token'];
	}

	/**
	 * Request a fresh access token using the client_credentials grant.
	 *
	 * @return array|WP_Error Decoded token response or WP_Error.
	 */
	public function request_token() {
		$client_id     = $this->get_credential_id();
		$client_secret = $this->get_credential_secret();
		$version       = $this->get_credential_version();

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error(
				'creators_oauth_missing_credentials',
				__( 'Creators API Credential ID and Credential Secret are required.', 'amazon-associates-snippets' )
			);
		}

		$endpoint = $this->get_token_endpoint( $version );
		if ( is_wp_error( $endpoint ) ) {
			return $endpoint;
		}

		$is_lwa = $this->is_lwa_version( $version );

		if ( $is_lwa ) {
			$content_type = 'application/json';
			$body         = wp_json_encode(
				array(
					'grant_type'    => 'client_credentials',
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'scope'         => 'creatorsapi::default',
				)
			);
		} else {
			$content_type = 'application/x-www-form-urlencoded';
			$body         = http_build_query(
				array(
					'grant_type'    => 'client_credentials',
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'scope'         => 'creatorsapi/default',
				)
			);
		}

		$response = $this->post_token_request( $endpoint, $body, $content_type );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status  = wp_remote_retrieve_response_code( $response );
		$raw     = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $raw, true );

		if ( 200 !== $status || ! is_array( $decoded ) ) {
			$message = __( 'Creators API token request failed.', 'amazon-associates-snippets' );
			if ( is_array( $decoded ) && ! empty( $decoded['error_description'] ) ) {
				$message = $decoded['error_description'];
			} elseif ( is_array( $decoded ) && ! empty( $decoded['error'] ) ) {
				$message = $decoded['error'];
			} elseif ( ! empty( $raw ) && strlen( $raw ) < 512 ) {
				$message = 'HTTP ' . $status;
			}
			return new WP_Error( 'creators_oauth_error', $message, array( 'status' => $status ) );
		}

		return $decoded;
	}

	/**
	 * Clear the cached access token (forces a fresh request on next use).
	 */
	public function clear_cached_token() {
		delete_transient( self::TOKEN_TRANSIENT );
	}

	/**
	 * Resolve the OAuth 2.0 token endpoint for a given credential version.
	 *
	 * @param string $version Credential version (2.1, 2.2, 2.3, 3.1, 3.2, 3.3).
	 * @return string|WP_Error
	 */
	public function get_token_endpoint( $version ) {
		switch ( $version ) {
			case '2.1':
				return 'https://creatorsapi.auth.us-east-1.amazoncognito.com/oauth2/token';
			case '2.2':
				return 'https://creatorsapi.auth.eu-south-2.amazoncognito.com/oauth2/token';
			case '2.3':
				return 'https://creatorsapi.auth.us-west-2.amazoncognito.com/oauth2/token';
			case '3.1':
				return 'https://api.amazon.com/auth/o2/token';
			case '3.2':
				return 'https://api.amazon.co.uk/auth/o2/token';
			case '3.3':
				return 'https://api.amazon.co.jp/auth/o2/token';
			default:
				return new WP_Error(
					'creators_oauth_unsupported_version',
					sprintf(
						// translators: %s is the configured credential version.
						__( 'Unsupported Creators API credential version: %s. Supported versions are 2.1, 2.2, 2.3, 3.1, 3.2, 3.3.', 'amazon-associates-snippets' ),
						(string) $version
					)
				);
		}
	}

	/**
	 * Whether a credential version uses the LwA (v3.x, JSON) flow.
	 */
	public function is_lwa_version( $version ) {
		return 0 === strpos( (string) $version, '3.' );
	}

	/**
	 * Perform the token endpoint HTTP request.
	 *
	 * Override in subclasses/tests to stub the transport.
	 *
	 * @param string $url          Token endpoint.
	 * @param string $body         Raw request body.
	 * @param string $content_type Request content type.
	 * @return array|WP_Error
	 */
	protected function post_token_request( $url, $body, $content_type ) {
		return wp_remote_post(
			$url,
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => $content_type,
				),
				'body'    => $body,
				'timeout' => 15,
			)
		);
	}
}
