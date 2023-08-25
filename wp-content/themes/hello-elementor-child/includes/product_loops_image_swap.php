<?php
/**
 * Add secondary product image that swap on hover.
 *
 * @return void
 */
function mc_wc_secondary_product_image() {
  $post_type = get_post_type( get_the_ID() );
  
	if ( 'product' === $post_type && method_exists( 'WC_Product', 'get_gallery_image_ids' ) ) {
   		$product = new WC_Product( get_the_ID() );
		$attachment_ids = $product->get_gallery_image_ids();
    
		if ( $attachment_ids && has_post_thumbnail() ) {
     		$secondary_image_id = $attachment_ids['0'];
			echo wp_get_attachment_image( $secondary_image_id, 'shop_catalog', '', $attr = array( 'class' => 'secondary-image attachment-shop-catalog' ) );
		}
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'mc_wc_secondary_product_image', 1, 2 );