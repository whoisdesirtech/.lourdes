<?php
/**
 * Official Creators API PHP SDK Transport
 *
 * Used only when the Amazon\CreatorsAPI\v1 SDK namespace is available
 * (installed via composer). The SDK owns OAuth 2.0 authentication and token
 * renewal, so no custom signing or token code is duplicated here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Creators_API_Sdk_Transport extends AA_Creators_API_Transport {

	/**
	 * {@inheritdoc}
	 */
	public function get_items( array $asins, $marketplace_domain, $partner_tag ) {
		$config = new \Amazon\CreatorsAPI\v1\Configuration();
		$config->setCredentialId( trim( (string) get_option( 'aa_credential_id', '' ) ) );
		$config->setCredentialSecret( trim( (string) get_option( 'aa_credential_secret', '' ) ) );
		$config->setVersion( trim( (string) get_option( 'aa_credential_version', '' ) ) );

		$api = new \Amazon\CreatorsAPI\v1\com\amazon\creators\api\DefaultApi( null, $config );

		$request = new \Amazon\CreatorsAPI\v1\com\amazon\creators\model\GetItemsRequestContent();
		$request->setPartnerTag( $partner_tag );
		$request->setItemIds( array_values( $asins ) );
		$request->setResources( self::get_resources() );

		try {
			$response = $api->getItems( $marketplace_domain, $request );
		} catch ( \Amazon\CreatorsAPI\v1\ApiException $e ) {
			return new WP_Error(
				'creators_sdk_api_error',
				$this->sdk_error_message( $e ),
				array( 'status' => $e->getCode() )
			);
		} catch ( \Throwable $e ) {
			return new WP_Error( 'creators_sdk_error', $e->getMessage() );
		}

		$decoded = json_decode( wp_json_encode( $response ), true );

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'creators_sdk_invalid_response',
				__( 'The Creators API SDK returned an unexpected response.', 'amazon-associates-snippets' )
			);
		}

		return $decoded;
	}

	/**
	 * Build a safe error message from an SDK ApiException.
	 *
	 * Only the API-provided error message is surfaced; full response bodies
	 * and Authorization headers are never exposed.
	 */
	private function sdk_error_message( \Amazon\CreatorsAPI\v1\ApiException $e ) {
		$body = $e->getResponseBody();

		if ( is_object( $body ) ) {
			if ( isset( $body->message ) && is_scalar( $body->message ) ) {
				return (string) $body->message;
			}
			if ( isset( $body->errors ) && is_array( $body->errors ) && isset( $body->errors[0]->message ) ) {
				return (string) $body->errors[0]->message;
			}
		}

		return $e->getMessage();
	}
}
