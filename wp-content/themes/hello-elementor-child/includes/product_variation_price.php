<?php
// Change the global product price according to the current variation price on the product page
add_action('woocommerce_before_add_to_cart_form', 'selected_variation_price_replace_variable_price_range');

function selected_variation_price_replace_variable_price_range(){
  global $product;

  if( $product->is_type('variable') ): ?>
  <style>
    .woocommerce-variation-price {
      display:none;
    }
  </style>

  <script>
    jQuery(function($) {
      var p = 'p.price';
      q = $(p).html();

      $('form.cart').on('show_variation', function( event, data ) {
        if ( data.price_html ) {
          $(p).html(data.price_html);
        }
      }).on('hide_variation', function( event ) {
        $(p).html(q);
      });
    });
  </script>
  <?php endif;
}