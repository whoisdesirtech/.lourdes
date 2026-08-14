<?php
/**
 * Gutenberg Blocks
 *
 * Registers three dynamic blocks (aa/box, aa/grid, aa/comparison) that render
 * the existing shortcodes server-side, plus a REST route the block editor's
 * "Insert Product" search modal uses to look products up by ASIN or keyword.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Blocks {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
	}

	/**
	 * Register the dynamic blocks.
	 */
	public static function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'aa/box',
			array(
				'api_version'     => 2,
				'attributes'      => array(
					'asin'       => array( 'type' => 'string', 'default' => '' ),
					'title'      => array( 'type' => 'string', 'default' => '' ),
					'buttonText' => array( 'type' => 'string', 'default' => '' ),
					'className'  => array( 'type' => 'string', 'default' => '' ),
				),
				'render_callback' => array( __CLASS__, 'render_box' ),
			)
		);

		register_block_type(
			'aa/grid',
			array(
				'api_version'     => 2,
				'attributes'      => array(
					'asins'     => array( 'type' => 'string', 'default' => '' ),
					'columns'   => array( 'type' => 'number', 'default' => 3 ),
					'className' => array( 'type' => 'string', 'default' => '' ),
				),
				'render_callback' => array( __CLASS__, 'render_grid' ),
			)
		);

		register_block_type(
			'aa/comparison',
			array(
				'api_version'     => 2,
				'attributes'      => array(
					'items'     => array( 'type' => 'string', 'default' => '' ),
					'columns'   => array( 'type' => 'number', 'default' => 2 ),
					'className' => array( 'type' => 'string', 'default' => '' ),
				),
				'render_callback' => array( __CLASS__, 'render_comparison' ),
			)
		);
	}

	/**
	 * Render callback for aa/box.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_box( $attributes ) {
		$asin = isset( $attributes['asin'] ) ? $attributes['asin'] : '';
		if ( '' === $asin ) {
			return '';
		}

		$args = array( 'asin' => $asin );
		if ( ! empty( $attributes['title'] ) ) {
			$args['title'] = $attributes['title'];
		}
		if ( ! empty( $attributes['buttonText'] ) ) {
			$args['button_text'] = $attributes['buttonText'];
		}
		if ( ! empty( $attributes['className'] ) ) {
			$args['class'] = $attributes['className'];
		}

		return aa_render_product_box( $asin, $args );
	}

	/**
	 * Render callback for aa/grid.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_grid( $attributes ) {
		$atts = array(
			'asins'   => isset( $attributes['asins'] ) ? $attributes['asins'] : '',
			'columns' => isset( $attributes['columns'] ) ? $attributes['columns'] : 3,
		);
		return AA_Shortcodes::render_grid_shortcode( $atts );
	}

	/**
	 * Render callback for aa/comparison.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_comparison( $attributes ) {
		$atts = array(
			'items'   => isset( $attributes['items'] ) ? $attributes['items'] : '',
			'columns' => isset( $attributes['columns'] ) ? $attributes['columns'] : 2,
		);
		return AA_Shortcodes::amazon_comparison_shortcode( $atts );
	}

	/**
	 * Enqueue the editor script that registers the block types in JS.
	 */
	public static function enqueue_editor_assets() {
		wp_register_script(
			'aa-blocks',
			AA_SNIPPETS_URL . 'assets/js/aa-blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-api-fetch' ),
			AA_SNIPPETS_VERSION,
			true
		);
		wp_enqueue_script( 'aa-blocks' );
	}

	/**
	 * Register the product search REST route used by the Insert Product modal.
	 */
	public static function register_rest_routes() {
		register_rest_route(
			'aa/v1',
			'/products/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_search_products' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'        => array( 'type' => 'string', 'required' => false, 'default' => '' ),
					'provider' => array( 'type' => 'string', 'default' => 'amazon' ),
					'limit'    => array( 'type' => 'integer', 'default' => 10 ),
				),
			)
		);
	}

	/**
	 * REST handler: lookup by ASIN (amazon) or keyword (walmart/search).
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public static function rest_search_products( $request ) {
		$provider = strtolower( trim( (string) $request['provider'] ) );
		$query    = trim( (string) $request['q'] );
		$limit    = (int) $request['limit'];
		$provider_obj = AA_Product_Provider_Registry::get( $provider );

		if ( null === $provider_obj ) {
			return rest_ensure_response( array( 'items' => array(), 'errors' => array( 'Unknown provider.' ) ) );
		}

		// Amazon is ASIN-based: treat the query as one or more id references.
		if ( ! $provider_obj->supports_search() ) {
			$refs = array();
			foreach ( array_filter( array_map( 'trim', explode( ',', $query ) ) ) as $part ) {
				$ref = AA_Product_Reference::parse( $part );
				if ( $ref ) {
					$refs[] = $ref;
				}
			}
			$collection = $provider_obj->get_products( $refs );
			return rest_ensure_response(
				array(
					'items'  => $collection->items(),
					'errors' => $collection->get_errors(),
				)
			);
		}

		$collection = $provider_obj->search_products( new AA_Product_Query( $provider, $query, '', $limit ) );
		return rest_ensure_response(
			array(
				'items'  => $collection->items(),
				'errors' => $collection->get_errors(),
			)
		);
	}
}
