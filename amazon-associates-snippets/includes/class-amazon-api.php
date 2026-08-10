<?php
/**
 * Amazon PA-API 5.0 Client with AWS SigV4 Signing & Transients Caching
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Amazon_API {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get marketplace host and region map
	 */
	public static function get_marketplaces() {
		return array(
			'US' => array(
				'name'   => 'United States (amazon.com)',
				'host'   => 'webservices.amazon.com',
				'region' => 'us-east-1',
				'domain' => 'amazon.com',
			),
			'UK' => array(
				'name'   => 'United Kingdom (amazon.co.uk)',
				'host'   => 'webservices.amazon.co.uk',
				'region' => 'eu-west-1',
				'domain' => 'amazon.co.uk',
			),
			'CA' => array(
				'name'   => 'Canada (amazon.ca)',
				'host'   => 'webservices.amazon.ca',
				'region' => 'us-east-1',
				'domain' => 'amazon.ca',
			),
			'DE' => array(
				'name'   => 'Germany (amazon.de)',
				'host'   => 'webservices.amazon.de',
				'region' => 'eu-west-1',
				'domain' => 'amazon.de',
			),
			'FR' => array(
				'name'   => 'France (amazon.fr)',
				'host'   => 'webservices.amazon.fr',
				'region' => 'eu-west-1',
				'domain' => 'amazon.fr',
			),
			'ES' => array(
				'name'   => 'Spain (amazon.es)',
				'host'   => 'webservices.amazon.es',
				'region' => 'eu-west-1',
				'domain' => 'amazon.es',
			),
			'IT' => array(
				'name'   => 'Italy (amazon.it)',
				'host'   => 'webservices.amazon.it',
				'region' => 'eu-west-1',
				'domain' => 'amazon.it',
			),
			'JP' => array(
				'name'   => 'Japan (amazon.co.jp)',
				'host'   => 'webservices.amazon.co.jp',
				'region' => 'us-west-2',
				'domain' => 'amazon.co.jp',
			),
			'AU' => array(
				'name'   => 'Australia (amazon.com.au)',
				'host'   => 'webservices.amazon.com.au',
				'region' => 'us-west-2',
				'domain' => 'amazon.com.au',
			),
		);
	}

	/**
	 * Build Affiliate URL for ASIN or URL
	 */
	public function get_affiliate_url( $asin_or_url ) {
		$partner_tag  = trim( get_option( 'aa_partner_tag', '' ) );
		$marketplace  = get_option( 'aa_marketplace', 'US' );
		$markets      = self::get_marketplaces();
		$domain       = isset( $markets[ $marketplace ] ) ? $markets[ $marketplace ]['domain'] : 'amazon.com';

		// If full URL was provided
		if ( filter_var( $asin_or_url, FILTER_VALIDATE_URL ) ) {
			if ( ! empty( $partner_tag ) ) {
				return add_query_arg( 'tag', $partner_tag, $asin_or_url );
			}
			return $asin_or_url;
		}

		// ASIN provided
		$asin = strtoupper( trim( $asin_or_url ) );
		$url  = "https://www.{$domain}/dp/{$asin}";

		if ( ! empty( $partner_tag ) ) {
			$url = add_query_arg( 'tag', $partner_tag, $url );
		}

		return $url;
	}

	/**
	 * Fetch Item Details by ASIN from PA-API 5.0 (with Transient Caching)
	 */
	public function get_item( $asin ) {
		$asin = strtoupper( trim( $asin ) );
		if ( empty( $asin ) ) {
			return false;
		}

		$partner_tag  = trim( get_option( 'aa_partner_tag', '' ) );
		$transient_key = 'aa_item_' . md5( $asin . '_' . $partner_tag );
		$cached_data  = get_transient( $transient_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Check credentials
		$access_key = trim( get_option( 'aa_access_key', '' ) );
		$secret_key = trim( get_option( 'aa_secret_key', '' ) );

		if ( empty( $access_key ) || empty( $secret_key ) || empty( $partner_tag ) ) {
			return $this->get_fallback_product( $asin, __( 'Amazon API credentials missing. Showing fallback link.', 'amazon-associates-snippets' ) );
		}

		$response = $this->call_pa_api_get_items( array( $asin ) );

		if ( is_wp_error( $response ) ) {
			return $this->get_fallback_product( $asin, $response->get_error_message() );
		}

		$data = $this->parse_item_response( $response, $asin );

		// Cache valid response
		$cache_hours = intval( get_option( 'aa_cache_expiry', 24 ) );
		if ( $cache_hours <= 0 ) {
			$cache_hours = 24;
		}

		set_transient( $transient_key, $data, $cache_hours * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * PA-API 5.0 Request for GetItems
	 */
	private function call_pa_api_get_items( array $asins ) {
		$access_key  = trim( get_option( 'aa_access_key', '' ) );
		$secret_key  = trim( get_option( 'aa_secret_key', '' ) );
		$partner_tag = trim( get_option( 'aa_partner_tag', '' ) );
		$marketplace = get_option( 'aa_marketplace', 'US' );
		$markets     = self::get_marketplaces();

		$market_info = isset( $markets[ $marketplace ] ) ? $markets[ $marketplace ] : $markets['US'];
		$host        = $market_info['host'];
		$region      = $market_info['region'];

		$payload = array(
			'ItemIds'     => array_values( $asins ),
			'ItemIdType'  => 'ASIN',
			'PartnerTag'  => $partner_tag,
			'PartnerType' => 'Associates',
			'Resources'   => array(
				'Images.Primary.Large',
				'Images.Primary.Medium',
				'ItemInfo.Title',
				'ItemInfo.Features',
				'ItemInfo.ByLineInfo',
				'Offers.Listings.Price',
				'Offers.Listings.SavingBasis',
				'Offers.Listings.DeliveryInfo.IsPrimeEligible',
			),
		);

		$payload_json = wp_json_encode( $payload );
		$uri          = '/paapi5/getitems';
		$target       = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems';

		$headers = $this->generate_aws_sigv4_headers(
			$access_key,
			$secret_key,
			$host,
			$region,
			$uri,
			$target,
			$payload_json
		);

		$request_url = 'https://' . $host . $uri;

		$response = wp_remote_post(
			$request_url,
			array(
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => $payload_json,
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$decoded     = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$error_msg = isset( $decoded['Errors'][0]['Message'] ) ? $decoded['Errors'][0]['Message'] : 'API HTTP Error ' . $status_code;
			return new WP_Error( 'amazon_api_error', $error_msg );
		}

		return $decoded;
	}

	/**
	 * AWS Signature Version 4 Signature Generator
	 */
	private function generate_aws_sigv4_headers( $access_key, $secret_key, $host, $region, $uri, $target, $payload_json ) {
		$service      = 'paapi5';
		$algorithm    = 'AWS4-HMAC-SHA256';
		$time         = time();
		$amz_date     = gmdate( 'Ymd\THis\Z', $time );
		$date_stamp   = gmdate( 'Ymd', $time );

		$canonical_uri = $uri;
		$canonical_querystring = '';

		$headers = array(
			'content-encoding' => 'amz-1.0',
			'content-type'     => 'application/json; charset=UTF-8',
			'host'             => strtolower( $host ),
			'x-amz-date'       => $amz_date,
			'x-amz-target'     => $target,
		);

		ksort( $headers );

		$canonical_headers = '';
		$signed_headers_arr = array();
		foreach ( $headers as $key => $val ) {
			$canonical_headers .= $key . ':' . trim( $val ) . "\n";
			$signed_headers_arr[] = $key;
		}
		$signed_headers = implode( ';', $signed_headers_arr );

		$payload_hash = hash( 'sha256', $payload_json );

		$canonical_request = "POST\n" .
			$canonical_uri . "\n" .
			$canonical_querystring . "\n" .
			$canonical_headers . "\n" .
			$signed_headers . "\n" .
			$payload_hash;

		$credential_scope = $date_stamp . '/' . $region . '/' . $service . '/aws4_request';
		$string_to_sign   = $algorithm . "\n" .
			$amz_date . "\n" .
			$credential_scope . "\n" .
			hash( 'sha256', $canonical_request );

		// Calculate Signature
		$k_date    = hash_hmac( 'sha256', $date_stamp, 'AWS4' + $secret_key ? 'AWS4' . $secret_key : '', true );
		$k_region  = hash_hmac( 'sha256', $region, $k_date, true );
		$k_service = hash_hmac( 'sha256', $service, $k_region, true );
		$k_signing = hash_hmac( 'sha256', 'aws4_request', $k_service, true );
		$signature = hash_hmac( 'sha256', $string_to_sign, $k_signing );

		$authorization = $algorithm . ' ' .
			'Credential=' . $access_key . '/' . $credential_scope . ', ' .
			'SignedHeaders=' . $signed_headers . ', ' .
			'Signature=' . $signature;

		return array(
			'Content-Encoding' => 'amz-1.0',
			'Content-Type'     => 'application/json; charset=UTF-8',
			'Host'             => $host,
			'X-Amz-Date'       => $amz_date,
			'X-Amz-Target'     => $target,
			'Authorization'    => $authorization,
		);
	}

	/**
	 * Parse PA-API Response
	 */
	private function parse_item_response( $response, $asin ) {
		if ( empty( $response['ItemsResult']['Items'][0] ) ) {
			return $this->get_fallback_product( $asin, __( 'Item not found in Amazon API response.', 'amazon-associates-snippets' ) );
		}

		$item = $response['ItemsResult']['Items'][0];

		$title = isset( $item['ItemInfo']['Title']['DisplayValue'] ) ? $item['ItemInfo']['Title']['DisplayValue'] : 'Amazon Product';
		$url   = isset( $item['DetailPageURL'] ) ? $item['DetailPageURL'] : $this->get_affiliate_url( $asin );
		$image = isset( $item['Images']['Primary']['Large']['URL'] ) ? $item['Images']['Primary']['Large']['URL'] : '';

		if ( empty( $image ) && isset( $item['Images']['Primary']['Medium']['URL'] ) ) {
			$image = $item['Images']['Primary']['Medium']['URL'];
		}

		$price = '';
		$saving_basis = '';
		$is_prime = false;

		if ( ! empty( $item['Offers']['Listings'][0] ) ) {
			$listing = $item['Offers']['Listings'][0];
			if ( isset( $listing['Price']['DisplayAmount'] ) ) {
				$price = $listing['Price']['DisplayAmount'];
			}
			if ( isset( $listing['SavingBasis']['DisplayAmount'] ) ) {
				$saving_basis = $listing['SavingBasis']['DisplayAmount'];
			}
			if ( isset( $listing['DeliveryInfo']['IsPrimeEligible'] ) && $listing['DeliveryInfo']['IsPrimeEligible'] ) {
				$is_prime = true;
			}
		}

		$features = array();
		if ( ! empty( $item['ItemInfo']['Features']['DisplayValues'] ) ) {
			$features = array_slice( $item['ItemInfo']['Features']['DisplayValues'], 0, 4 );
		}

		$brand = isset( $item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'] ) ? $item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'] : '';

		return array(
			'asin'         => $asin,
			'title'        => $title,
			'url'          => $url,
			'image'        => $image,
			'price'        => $price,
			'saving_basis' => $saving_basis,
			'is_prime'     => $is_prime,
			'features'     => $features,
			'brand'        => $brand,
			'is_fallback'  => false,
			'updated_at'   => current_time( 'mysql' ),
		);
	}

	/**
	 * Generate Fallback Product Data
	 */
	public function get_fallback_product( $asin, $error_message = '' ) {
		return array(
			'asin'         => $asin,
			'title'        => sprintf( __( 'Amazon Product (ASIN: %s)', 'amazon-associates-snippets' ), $asin ),
			'url'          => $this->get_affiliate_url( $asin ),
			'image'        => 'https://images-na.ssl-images-amazon.com/images/G/01/x-locale/common/pk-badge-prime._CB485934476_.png',
			'price'        => '',
			'saving_basis' => '',
			'is_prime'     => false,
			'features'     => array(),
			'brand'        => 'Amazon',
			'is_fallback'  => true,
			'error'        => $error_message,
			'updated_at'   => current_time( 'mysql' ),
		);
	}
}
