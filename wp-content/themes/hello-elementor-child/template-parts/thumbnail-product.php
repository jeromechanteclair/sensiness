<?php

global $product;
$key = $args['key'];
$grid = $args['grid'];

$classes='';
if($grid=='home'){
	if($key==0){
		$classes.=' prems';
	}
	if($key==1){
		$classes.=' second';
	}

}
if(isset($args['hide_mobile']) && $args['hide_mobile']){
$classes .= ' hide_mobile';

}

// Ensure visibility.
if (empty($product) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class('woocommerce'.$classes, $product); ?>>
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
	$attachment_id= get_post_thumbnail_id($product->get_id());
	$image_alt = get_field('image_alt',$product->get_id());
	if($image_alt && $grid=='home'){
		$attachment_id = $image_alt['id'];
	}

	if($grid=='home' && $key ==0){
		$desktop_double = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_big_desktop');
		$desktop = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_big_desktop');
	}
	elseif($grid=='home' && $key ==1){
		$desktop_double = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_medium_desktop');
		$desktop = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_medium_desktop');
	}
	else{
		$desktop_double = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_normal_desktop');
		$desktop = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_normal_desktop');
			
	}
	$mobile_double = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_normal_mobile');
	$mobile = wp_get_attachment_image_src($attachment_id, 'product_thumbnail_normal_mobilex2');

	// $image = wp_get_attachment_image_src(, 'single-post-thumbnail');
	?>
	<div class="pictures">

	
	<picture class="main">
		<source srcset="<?=$desktop_double[0];?> 2x,<?=$desktop[0];?>" media="(min-width: 999px)">
		<source srcset="<?=$mobile_double[0];?> 2x,<?=$mobile[0];?>" media="(min-width: 999px)">

		<img src="<?php  echo $desktop[0]; ?>"  alt="<?=wp_strip_all_tags($product->get_title());?>" >
	</picture> 
	<?php

		$attachment_ids = $product->get_gallery_image_ids();
		if ( $attachment_ids && has_post_thumbnail() ) {
			
			$secondary_image_id = $attachment_ids['0'];

		}
				
		if($image_alt && $grid=='home') {
			$secondary_image_id =get_post_thumbnail_id($product->get_id());
		}

				
		if($grid == 'home' && $key == 0) {
			$desktop_double = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_big_desktop');
			$desktop = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_big_desktop');
		} elseif($grid == 'home' && $key == 1) {
			$desktop_double = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_medium_desktop');
			$desktop = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_medium_desktop');
		} else {
			$desktop_double = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_normal_desktop');
			$desktop = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_normal_desktop');

		}
		$mobile_double = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_normal_mobile');
		$mobile = wp_get_attachment_image_src($secondary_image_id, 'product_thumbnail_normal_mobilex2');


	?>
	<picture class="secondary">
		<source srcset="<?=$desktop_double[0];?> 2x,<?=$desktop[0];?>" media="(min-width: 999px)">
		<source srcset="<?=$mobile_double[0];?> 2x,<?=$mobile[0];?>" media="(min-width: 999px)">

		<img src="<?=$desktop[0];?>" alt="<?=wp_strip_all_tags($product->get_title());?>" >
	</picture> 
</div>
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
