<?php
// Add default product to cart in products loop links
function filter_woocommerce_loop_add_to_cart_link( $args, $product ) {
  // Shop page & product type = variable
  if ( $product->is_type( 'variable' ) ) {
    // Get the default variation
    $variation = get_default_variation($product);
    if ($variation && ! $variation->is_sold_individually()) {
      // Get variation ID, sku & add to cart url
      $variation_id = $variation->get_id();
      $variation_sku = $variation->get_sku();
      $variation_url = $variation->add_to_cart_url();
  
      // Quantity & text
      // $quantity = isset( $args['quantity'] ) ? $args['quantity'] : 1;
      $quantity = 1;
      $text = $variation->add_to_cart_text();
  
      if ($product->is_in_stock() && $variation->is_purchasable()) {
        $args = '<a href="' . $variation_url . '" data-quantity="' . $quantity . '" data-product_id="' . $variation_id . '" data-product_sku="' . $variation_sku . '" class="button product_type_simple add_to_cart_button ajax_add_to_cart add-to-cart" aria-label="Add to cart">' . $text . '</a>';
      } else {
        $text = 'Rupture de stock';
        $variation_url = $variation->get_permalink();
        $args = '<a href="' . $variation_url . '" data-product_id="' . $variation_id . '" data-product_sku="' . $variation_sku . '" class="button product_type_simple" aria-label="View product">' . $text . '</a>';
      }
    }
  } else if ( ! $product->is_in_stock() ) {
    $text = 'Rupture de stock';
    $product_url = $product->get_permalink();
    $product_id = $product->get_id();
    $product_sku = $product->get_sku();
    $args = '<a href="' . $product_url . '" data-product_id="' . $product_id . '" data-product_sku="' . $product_sku . '" class="button product_type_simple" aria-label="View product">' . $text . '</a>';
  }
  
  return $args; 
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'filter_woocommerce_loop_add_to_cart_link', 10, 2 );

// Function to get the default product variation
function get_default_variation($product) {
  // Check if product is variable
  if( $product->is_type('variable') ){
    // Get all default attributes
    $default_attributes = $product->get_default_attributes();
    foreach($product->get_available_variations() as $variation_values ){
      foreach($variation_values['attributes'] as $key => $attribute_value ){
        $attribute_name = str_replace( 'attribute_', '', $key );
        $default_value = $product->get_variation_default_attribute($attribute_name);
        // Check if default attribute is default variation
        if( $default_value == $attribute_value ){
          $is_default_variation = true;
        } else {
          $is_default_variation = false;
          break; // Stop this loop to start next main lopp
        }
      }
      if( $is_default_variation ){
        $variation_id = $variation_values['variation_id'];
        break; // Stop the main loop
      }
    }

    // Get the default variation data
    if( $is_default_variation ){
      // Get the "default" WC_Product_Variation object to use available methods
      return $default_variation = wc_get_product($variation_id);
    }
  }
}
