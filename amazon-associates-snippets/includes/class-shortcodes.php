<?php
/**
 * WordPress Shortcodes Implementation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AA_Shortcodes {

	public static function init() {
		add_shortcode( 'amazon_box', array( __CLASS__, 'render_box_shortcode' ) );
		add_shortcode( 'amazon_button', array( __CLASS__, 'render_button_shortcode' ) );
		add_shortcode( 'amazon_link', array( __CLASS__, 'render_link_shortcode' ) );
		add_shortcode( 'amazon_grid', array( __CLASS__, 'render_grid_shortcode' ) );
	}

	/**
	 * Render Product Showcase Card: [amazon_box asin="ASIN"]
	 */
	public static function render_box_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'asin'        => '',
				'title'       => '',
				'button_text' => '',
				'class'       => '',
			),
			$atts,
			'amazon_box'
		);

		if ( empty( $atts['asin'] ) ) {
			return '<p class="aa-error-notice">' . esc_html__( '[amazon_box error: Missing ASIN]', 'amazon-associates-snippets' ) . '</p>';
		}

		return aa_render_product_box( $atts['asin'], $atts );
	}

	/**
	 * Render CTA Button: [amazon_button asin="ASIN" text="Buy Now"]
	 */
	public static function render_button_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'asin'  => '',
				'text'  => '',
				'class' => '',
			),
			$atts,
			'amazon_button'
		);

		if ( empty( $atts['asin'] ) ) {
			return '';
		}

		return aa_render_button( $atts['asin'], $atts['text'], $atts['class'] );
	}

	/**
	 * Render Inline Link: [amazon_link asin="ASIN" text="View on Amazon"]
	 */
	public static function render_link_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'asin'  => '',
				'text'  => '',
				'class' => '',
			),
			$atts,
			'amazon_link'
		);

		if ( empty( $atts['asin'] ) ) {
			return '';
		}

		return aa_render_link( $atts['asin'], $atts['text'], $atts['class'] );
	}

	/**
	 * Render Grid Layout: [amazon_grid asins="ASIN1,ASIN2,ASIN3" columns="3"]
	 */
	public static function render_grid_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'asins'   => '',
				'columns' => 3,
			),
			$atts,
			'amazon_grid'
		);

		if ( empty( $atts['asins'] ) ) {
			return '';
		}

		$asin_list = array_filter( array_map( 'trim', explode( ',', $atts['asins'] ) ) );
		if ( empty( $asin_list ) ) {
			return '';
		}

		$cols  = intval( $atts['columns'] );
		$cols  = ( $cols >= 1 && $cols <= 4 ) ? $cols : 3;

		$output = '<div class="aa-product-grid aa-grid-cols-' . esc_attr( $cols ) . '">';
		foreach ( $asin_list as $asin ) {
			$output .= aa_render_product_box( $asin );
		}
		$output .= '</div>';

		return $output;
	}
}

AA_Shortcodes::init();
