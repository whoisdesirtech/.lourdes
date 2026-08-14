<?php
/**
 * Global PHP Snippet Helper Functions
 * Use these functions directly inside theme template files, functions.php, or the Code Snippets plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch raw Amazon Product Data Array
 *
 * @param string $asin Amazon Product ASIN (e.g. B08N5WRWNW)
 * @return array|false Product details array or false on failure
 */
function aa_get_product_data( $asin ) {
	$api = AA_Amazon_API::get_instance();
	return $api->get_item( $asin );
}

/**
 * Generate Affiliate Tag URL
 *
 * @param string $asin_or_url Product ASIN or full Amazon URL
 * @return string Tagged Amazon URL
 */
function aa_get_affiliate_url( $asin_or_url ) {
	$api = AA_Amazon_API::get_instance();
	return $api->get_affiliate_url( $asin_or_url );
}

/**
 * Resolve the affiliate/tracking URL for a product.
 *
 * When click tracking is enabled, this returns the local /aa-go/{id}
 * redirect URL (which records the click and forwards to the affiliate URL);
 * otherwise it returns the direct affiliate URL from the product data.
 *
 * @param array|string $product Product data array or a raw URL string.
 * @return string
 */
function aa_get_tracked_product_url( $product ) {
	$enabled = (bool) get_option( 'aa_click_tracking', 0 );

	if ( ! $enabled ) {
		if ( is_array( $product ) ) {
			return isset( $product['url'] ) ? $product['url'] : '';
		}
		return is_string( $product ) ? $product : '';
	}

	if ( is_array( $product ) ) {
		return AA_Click_Tracker::get_go_url( $product );
	}

	// Scalar string: no product metadata to attach, so keep it as-is.
	return is_string( $product ) ? $product : '';
}

/**
 * Render Complete Product Showcase Box Card HTML
 *
 * @param string $asin Product ASIN
 * @param array  $args Custom overrides (title, button_text, class)
 * @return string HTML Output
 */
function aa_render_product_box( $asin, $args = array() ) {
	$product = aa_get_product_data( $asin );
	if ( ! $product ) {
		return '';
	}
	return aa_render_product_data( $product, $args );
}

/**
 * Render a product card from a normalized product data array.
 *
 * Shared by aa_render_product_box (which fetches by ASIN) and the comparison
 * block/shortcode (which may render items fetched from any provider).
 *
 * @param array $product Normalized product array.
 * @param array $args    Custom overrides (title, button_text, class).
 * @return string HTML Output
 */
function aa_render_product_data( $product, $args = array() ) {
	if ( ! is_array( $product ) ) {
		return '';
	}

	$title       = ! empty( $args['title'] ) ? $args['title'] : $product['title'];
	$button_text = ! empty( $args['button_text'] ) ? $args['button_text'] : get_option( 'aa_button_text', 'Buy on Amazon' );
	$disclosure  = get_option( 'aa_disclosure_text', 'As an Amazon Associate I earn from qualifying purchases.' );
	$extra_class = ! empty( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';

	$link_url = aa_get_tracked_product_url( $product );

	ob_start();
	?>
	<div class="aa-product-card<?php echo $extra_class; ?>" data-asin="<?php echo esc_attr( $product['asin'] ); ?>" data-provider="<?php echo esc_attr( isset( $product['provider'] ) ? $product['provider'] : 'amazon' ); ?>">

		<?php if ( ! empty( $product['is_fallback'] ) ) : ?>
			<div class="aa-fallback-badge" title="<?php echo esc_attr( ! empty( $product['error'] ) ? $product['error'] : __( 'Fallback Mode: Using placeholder image until Amazon API credentials are configured.', 'amazon-associates-snippets' ) ); ?>">
				<span>Fallback Mode</span>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $product['is_prime'] ) ) : ?>
			<div class="aa-prime-badge" title="Eligible for Amazon Prime">
				<span>Prime</span>
			</div>
		<?php endif; ?>

		<div class="aa-card-inner">
			<div class="aa-card-media">
				<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="nofollow sponsored noopener" class="aa-image-link">
					<?php if ( ! empty( $product['image'] ) ) : ?>
						<img src="<?php echo esc_url( $product['image'] ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
					<?php else : ?>
						<div class="aa-no-image"><span>No Image Available</span></div>
					<?php endif; ?>
				</a>
			</div>

			<div class="aa-card-content">
				<?php if ( ! empty( $product['brand'] ) ) : ?>
					<span class="aa-brand-label"><?php echo esc_html( $product['brand'] ); ?></span>
				<?php endif; ?>

				<h3 class="aa-product-title">
					<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="nofollow sponsored noopener">
						<?php echo esc_html( $title ); ?>
					</a>
				</h3>

				<?php if ( ! empty( $product['price'] ) || ! empty( $product['saving_basis'] ) ) : ?>
					<div class="aa-price-wrapper">
						<?php if ( ! empty( $product['price'] ) ) : ?>
							<span class="aa-current-price"><?php echo esc_html( $product['price'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $product['saving_basis'] ) ) : ?>
							<span class="aa-list-price"><del><?php echo esc_html( $product['saving_basis'] ); ?></del></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $product['features'] ) && is_array( $product['features'] ) ) : ?>
					<ul class="aa-product-features">
						<?php foreach ( $product['features'] as $feature ) : ?>
							<li><span class="aa-feature-icon">✓</span> <?php echo esc_html( $feature ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="aa-card-actions">
					<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="nofollow sponsored noopener" class="aa-btn aa-btn-amazon">
						<svg class="aa-amazon-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
							<path d="M15.32 13.5c-.88 0-1.84.34-2.58.82l-.46-.77c-.43-.72-1.22-1.15-2.06-1.15-1.39 0-2.52 1.13-2.52 2.52s1.13 2.52 2.52 2.52c.84 0 1.63-.43 2.06-1.15l.46-.77c.74.48 1.7.82 2.58.82 1.48 0 2.68-1.2 2.68-2.68s-1.2-2.68-2.68-2.68zm-5.1 2.44c-.66 0-1.2-.54-1.2-1.2s.54-1.2 1.2-1.2 1.2.54 1.2 1.2-.54 1.2-1.2 1.2zM21.5 12c0 5.25-4.25 9.5-9.5 9.5S2.5 17.25 2.5 12 6.75 2.5 12 2.5s9.5 4.25 9.5 9.5z"/>
						</svg>
						<span><?php echo esc_html( $button_text ); ?></span>
					</a>
				</div>

				<?php if ( ! empty( $disclosure ) ) : ?>
					<p class="aa-compliance-disclosure"><?php echo esc_html( $disclosure ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render Call To Action Button HTML
 *
 * @param string $asin        Product ASIN
 * @param string $text        Custom button text
 * @param string $custom_class Custom CSS class
 * @return string HTML Output
 */
function aa_render_button( $asin, $text = '', $custom_class = '' ) {
	$url         = aa_get_affiliate_url( $asin );
	$button_text = ! empty( $text ) ? $text : get_option( 'aa_button_text', 'Buy on Amazon' );
	$class       = 'aa-btn aa-btn-amazon' . ( ! empty( $custom_class ) ? ' ' . esc_attr( $custom_class ) : '' );

	$product   = array( 'url' => $url, 'provider' => 'amazon', 'asin' => $asin );
	$link_url  = aa_get_tracked_product_url( $product );

	return sprintf(
		'<a href="%1$s" target="_blank" rel="nofollow sponsored noopener" class="%2$s">
			<svg class="aa-amazon-icon" viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
				<path d="M15.32 13.5c-.88 0-1.84.34-2.58.82l-.46-.77c-.43-.72-1.22-1.15-2.06-1.15-1.39 0-2.52 1.13-2.52 2.52s1.13 2.52 2.52 2.52c.84 0 1.63-.43 2.06-1.15l.46-.77c.74.48 1.7.82 2.58.82 1.48 0 2.68-1.2 2.68-2.68s-1.2-2.68-2.68-2.68zm-5.1 2.44c-.66 0-1.2-.54-1.2-1.2s.54-1.2 1.2-1.2 1.2.54 1.2 1.2-.54 1.2-1.2 1.2zM21.5 12c0 5.25-4.25 9.5-9.5 9.5S2.5 17.25 2.5 12 6.75 2.5 12 2.5s9.5 4.25 9.5 9.5z"/>
			</svg>
			<span>%3$s</span>
		</a>',
		esc_url( $link_url ),
		$class,
		esc_html( $button_text )
	);
}

/**
 * Render Inline Affiliate Text Link HTML
 *
 * @param string $asin        Product ASIN
 * @param string $text        Link anchor text
 * @param string $custom_class Custom CSS class
 * @return string HTML Output
 */
function aa_render_link( $asin, $text = '', $custom_class = '' ) {
	$url   = aa_get_affiliate_url( $asin );
	$title = ! empty( $text ) ? $text : sprintf( __( 'Amazon Product (ASIN: %s)', 'amazon-associates-snippets' ), $asin );
	$class = 'aa-affiliate-link' . ( ! empty( $custom_class ) ? ' ' . esc_attr( $custom_class ) : '' );

	$product  = array( 'url' => $url, 'provider' => 'amazon', 'asin' => $asin );
	$link_url = aa_get_tracked_product_url( $product );

	return sprintf(
		'<a href="%1$s" target="_blank" rel="nofollow sponsored noopener" class="%2$s">%3$s <span class="aa-ext-icon">↗</span></a>',
		esc_url( $link_url ),
		$class,
		esc_html( $title )
	);
}
