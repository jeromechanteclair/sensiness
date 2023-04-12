<?php
/**
 * Change the add to cart text
 * 
 * @param string $label : The initial label
 * @param array $product : The product
 * 
 * @return string : the label
 */
 
add_filter( 'woocommerce_product_add_to_cart_text', 'mc_change_add_to_cart_button_text', 9999, 2 );
 
function mc_change_add_to_cart_button_text( $label, $product ) {
   // if ( $product->is_type( 'variable' ) ) {
   //    return __('Add to cart', 'woocommerce');
   // }
   // return $label;
   return __('Add to cart', 'woocommerce');
}