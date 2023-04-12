<?php
/**
 * Change the way prices are displayed for variable products
 *
 * @return string $html The span of the product
 */
function mc_custom_variation_price( $price, $product ) {
	// Get prices from variations
	if ( has_term( array( 'fleurs-de-cbd' ), 'product_cat', $product->get_id() ) ) {
		// Check minimum price per gram
		foreach ( $product->get_visible_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			$variation_price_per_gram_regular = intval( $variation->get_regular_price() );
			$variation_price_per_gram_sale = intval( $variation->get_sale_price() );
			if ($variation_price_per_gram_sale == 0) {
				$variation_price_per_gram_sale = $variation_price_per_gram_regular;
			}
			$variation_weight = intval( $variation->get_weight() );
			
			if ( $variation->has_weight() ) {
				$variation_price_per_gram_regular = $variation_price_per_gram_regular / $variation_weight;
				$variation_price_per_gram_sale = $variation_price_per_gram_sale / $variation_weight;
			}
			$variation_price_per_gram = $variation_price_per_gram_sale;
			
			if ( empty($min_price_regular) || $min_price_regular > $variation_price_per_gram_regular ) {
				$min_price_regular = $variation_price_per_gram_regular;
			}
			if ( empty($min_price_sale) || $min_price_sale > $variation_price_per_gram_sale ) {
				$min_price_sale = $variation_price_per_gram_sale;
			}
			if ( empty($max_price) || $max_price < $variation_price_per_gram ) {
				$max_price = $variation_price_per_gram;
			}
			if ( empty($min_price) || $min_price > $variation_price_per_gram ) {
				$min_price = $variation_price_per_gram;
			}
		}
	} else {
		$min_price_regular = $product->get_variation_regular_price( 'min', true );
		$min_price_sale    = $product->get_variation_sale_price( 'min', true );
		$max_price = $product->get_variation_price( 'max', true );
		$min_price = $product->get_variation_price( 'min', true );
		
	}

	// Check if on sale
	if ( $min_price_sale == $min_price_regular ) {
		$price = wc_price($min_price_regular);
	} else {
		$price = sprintf( '<span class="mc_variable-sale">%s</span> <del class="mc_variable-regular">%s</del>', wc_price( $min_price_sale ), wc_price( $min_price_regular) );
	}
	
	// Check if all prices are the same and add prefix if not
	if ( $min_price != $max_price ) {
		$prefix = sprintf( '<span class="mc_variable-from">%s</span> ', _x( 'From:', 'min_price', 'woocommerce' ) );
		$price = $prefix . $price;
	}
	if ( has_term( array( 'fleurs-de-cbd' ), 'product_cat', $product->get_id() ) ) {
		$suffix = sprintf( ' <span class="mc_variable-suffix">%s</span>', '/gr.' );
		$price = $price . $suffix;
	}

	// Return
	return $price;
}
add_filter( 'woocommerce_variable_sale_price_html', 'mc_custom_variation_price', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'mc_custom_variation_price', 10, 2 );
