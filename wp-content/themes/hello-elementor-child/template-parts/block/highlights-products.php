<?php


$title = get_field('title');
$link = get_field('link');
$products = get_field('products');
$limit_mobile =get_field('limit_mobile');


?>
<?php

if($products):?>
<section class="highlights-products ">
	<div class="container">
		<h2>
			<?= $title;?>
		</h2>
		<a href="<?= $link['url'];?>" class="button">
			<?= $link['title'];?>

		</a>
		</div>

<?php
	 woocommerce_product_loop_start();
	foreach($products as $key=> $product) {
		$hide_mobile=false;
			if($limit_mobile){
				if($key>=$limit_mobile){
					$hide_mobile=true;
				}
			}


		// Load sub field value.

		$args = ['key' => $key,'product'=>$product,'grid'=>'home','hide_mobile'=>	$hide_mobile];
		$product = wc_get_product($args['product']->ID);

		
		$post_object = get_post($product->get_id());


		

		setup_postdata($GLOBALS['post'] = & $post_object);


		get_template_part('template-parts/thumbnail-product', '', $args);

		// Do something...

		// End loop.
	}
	woocommerce_product_loop_end();
?>
</section>
<?php
    // No value.
else :
    // Do something...
endif;

