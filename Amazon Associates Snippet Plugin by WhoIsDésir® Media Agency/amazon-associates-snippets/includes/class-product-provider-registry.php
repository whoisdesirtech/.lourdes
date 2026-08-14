<?php
/**
 * Product Provider Registry
 *
 * Holds the active product providers (Amazon, Walmart, ...) and provides
 * lookup + resolution helpers. Booted lazily the first time it is queried.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Product_Provider_Registry {

	/**
	 * Registered providers keyed by provider id.
	 *
	 * @var AA_Product_Provider[]
	 */
	private static $providers = array();

	/**
	 * Whether the registry has booted.
	 *
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Lazily register the built-in providers.
	 */
	public static function boot() {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		self::register( AA_Amazon_Provider::get_instance() );

		if ( class_exists( 'AA_Walmart_Provider' ) ) {
			self::register( AA_Walmart_Provider::get_instance() );
		}
	}

	/**
	 * Register a provider.
	 *
	 * @param AA_Product_Provider $provider Provider instance.
	 */
	public static function register( AA_Product_Provider $provider ) {
		$id = $provider->get_id();
		if ( '' !== $id ) {
			self::$providers[ $id ] = $provider;
		}
	}

	/**
	 * Get a provider by id.
	 *
	 * @param string $id Provider id.
	 * @return AA_Product_Provider|null
	 */
	public static function get( $id ) {
		self::boot();
		$id = strtolower( trim( (string) $id ) );
		return isset( self::$providers[ $id ] ) ? self::$providers[ $id ] : null;
	}

	/**
	 * Whether a provider is registered.
	 *
	 * @param string $id Provider id.
	 * @return bool
	 */
	public static function has( $id ) {
		return null !== self::get( $id );
	}

	/**
	 * Get all registered providers.
	 *
	 * @return AA_Product_Provider[]
	 */
	public static function all() {
		self::boot();
		return self::$providers;
	}

	/**
	 * Get only the configured providers.
	 *
	 * @return AA_Product_Provider[]
	 */
	public static function configured() {
		$configured = array();
		foreach ( self::all() as $id => $provider ) {
			if ( $provider->is_configured() ) {
				$configured[ $id ] = $provider;
			}
		}
		return $configured;
	}

	/**
	 * Resolve a reference from a string, array, or object.
	 *
	 * @param string|array|AA_Product_Reference $reference Source value.
	 * @return AA_Product_Reference|null
	 */
	public static function resolve( $reference ) {
		if ( $reference instanceof AA_Product_Reference ) {
			return $reference;
		}
		if ( is_string( $reference ) ) {
			return AA_Product_Reference::parse( $reference );
		}
		if ( is_array( $reference ) && ! empty( $reference['provider'] ) && ! empty( $reference['product_id'] ) ) {
			return new AA_Product_Reference(
				$reference['provider'],
				$reference['product_id'],
				isset( $reference['marketplace'] ) ? $reference['marketplace'] : ''
			);
		}
		return null;
	}

	/**
	 * Fetch a product through the registry using any reference format.
	 *
	 * @param string|array|AA_Product_Reference $reference Source value.
	 * @return array|null
	 */
	public static function get_product( $reference ) {
		$ref = self::resolve( $reference );
		if ( null === $ref ) {
			return null;
		}
		$provider = self::get( $ref->get_provider() );
		return $provider ? $provider->get_product( $ref ) : null;
	}
}
