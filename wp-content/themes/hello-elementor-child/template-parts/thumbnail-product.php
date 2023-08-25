<?php

global $product;
$key = $args['key'];

// Ensure visibility.
if (empty($product) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class('woocommerce', $product); ?>>
	<?php
    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_open - 10
     */
    do_action('woocommerce_before_shop_loop_item');

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	// do_action('woocommerce_before_shop_loop_item_title');
	$image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail');?>
	<picture>
		<img src="<?php  echo $image[0]; ?>" >
		<?php mc_wc_secondary_product_image();?>
	</picture> 

	<p class="title">
		<?php  echo $product->get_title();?>
	</p>
	
		<?php
	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	// do_action('woocommerce_shop_loop_item_title');

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	?>
	<div class="custom-stars">

	<?php
		woocommerce_template_loop_rating();?>
	</div>
	<?php
		woocommerce_template_loop_price();
	

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action('woocommerce_after_shop_loop_item');
?>
</li>
