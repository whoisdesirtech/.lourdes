<?php
/**
 * Creators API Transport Layer
 *
 * Two transports are provided:
 *
 *  - AA_Creators_API_Sdk_Transport  Uses Amazon's official Creators API PHP
 *                                   SDK when it is available (installed via
 *                                   composer). The SDK handles OAuth 2.0
 *                                   token acquisition and renewal internally.
 *
 *  - AA_Creators_API_Http_Transport Uses WordPress' HTTP API directly with
 *                                   Bearer token authentication. Token
 *                                   caching is handled by
 *                                   AA_Creators_OAuth_Client.
 *
 * AA_Creators_API_Transport::create() picks the SDK transport when the SDK
 * classes are loaded and falls back to the built-in HTTP client otherwise,
 * so the plugin works with or without a composer-installed vendor directory.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AA_Creators_API_Transport {

	/**
	 * OAuth client used by the built-in HTTP transport.
	 *
	 * @var AA_Creators_OAuth_Client|null
	 */
	protected $oauth_client;

	/**
	 * Constructor.
	 *
	 * @param AA_Creators_OAuth_Client|null $oauth_client OAuth client (optional).
	 */
	public function __construct( $oauth_client = null ) {
		$this->oauth_client = $oauth_client ? $oauth_client : AA_Creators_OAuth_Client::get_instance();
	}

	/**
	 * GetItems resource names used by the plugin (lowerCamelCase).
	 */
	public static function get_resources() {
		return array(
			'images.primary.large',
			'images.primary.medium',
			'itemInfo.title',
			'itemInfo.features',
			'itemInfo.byLineInfo',
			'offersV2.listings.price',
			'offersV2.listings.availability',
		);
	}

	/**
	 * Fetch item data for one or more ASINs.
	 *
	 * @param array  $asins             List of ASINs (max 10).
	 * @param string $marketplace_domain e.g. www.amazon.com.
	 * @param string $partner_tag       Partner tracking tag.
	 * @return array|WP_Error Decoded Creators API response or WP_Error.
	 */
	abstract public function get_items( array $asins, $marketplace_domain, $partner_tag );

	/**
	 * Whether the official SDK classes are available.
	 */
	public static function sdk_is_available() {
		return class_exists( 'Amazon\CreatorsAPI\v1\com\amazon\creators\api\DefaultApi' )
			&& class_exists( 'Amazon\CreatorsAPI\v1\com\amazon\creators\model\GetItemsRequestContent' );
	}

	/**
	 * Factory: prefer the official SDK, fall back to the built-in HTTP client.
	 *
	 * @param AA_Creators_OAuth_Client|null $oauth_client OAuth client (optional).
	 * @return AA_Creators_API_Transport
	 */
	public static function create( $oauth_client = null ) {
		if ( self::sdk_is_available() ) {
			return new AA_Creators_API_Sdk_Transport( $oauth_client );
		}
		return new AA_Creators_API_Http_Transport( $oauth_client );
	}

	/**
	 * Extract a human-readable error message from a decoded Creators API error body.
	 *
	 * @param array|null $decoded Decoded error response.
	 * @param int        $status  HTTP status code.
	 * @return string
	 */
	protected function api_error_message( $decoded, $status ) {
		if ( is_array( $decoded ) && ! empty( $decoded['errors'][0]['message'] ) ) {
			return $decoded['errors'][0]['message'];
		}
		return sprintf(
			// translators: %d is the HTTP status code.
			__( 'Amazon Creators API HTTP Error %d.', 'amazon-associates-snippets' ),
			(int) $status
		);
	}
}
