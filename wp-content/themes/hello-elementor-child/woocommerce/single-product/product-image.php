<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.1
 */

defined('ABSPATH') || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if (! function_exists('wc_get_gallery_image_html')) {
    return;
}

global $product;

$columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
$post_thumbnail_id = $product->get_image_id();
$attachment_ids = $product->get_gallery_image_ids();
$controls =true;
// get post meta gif
$gif = get_post_meta(get_the_id(), 'gif', true);


array_unshift($attachment_ids, $post_thumbnail_id);
if(empty($attachment_ids)) {
    $attachment_ids =[$post_thumbnail_id];
    $controls = false;
}
if(empty($attachment_ids) || count($attachment_ids) == 1) {

    $controls = false;
}

if(!empty($gif)) {

    array_push($attachment_ids, $gif);
}

?>

<!-- swiper gallery -->
<div class="product-gallery">
	<div class="swiper gallery-top">
		<?php if($controls) {?>
			<div class="control-prev">
				<i class="icon icon-arrow-square"></i>
			</div>
			<div class="control-next">
					<i class="icon icon-arrow-square"></i>
			</div>
		<?php };?>
		<div class="swiper-wrapper">
			<?php foreach($attachment_ids as $key => $attachment_id) {
			    // get attachmen_id extension
			    $ext = pathinfo(get_attached_file($attachment_id), PATHINFO_EXTENSION);
			    if($ext == 'mov' || $ext == 'mp4') {
			        // get attachment url
			        $url = wp_get_attachment_url($attachment_id);
			        $class="autoplay";
			        $src = '<video  src="'.$url.'" ></video>';
			    } else {
			        $class='';
			        if($key ==0) {
			            $src= wp_get_attachment_image($attachment_id, 'product_slide', false, array('loading'=>true));

			        } else {

			            $src= wp_get_attachment_image($attachment_id, 'product_slide', false);
			        }
			    }
			    ?>
				<figure class="woocommerce-product-gallery__wrapper swiper-slide <?=$class;?>">
					<?= $src ?>
				</figure>
			<?php };?>
		</div>
	</div>
	<!-- thumbnail images -->
	<?php if(count($attachment_ids)>1):?>
	<div class="gallery-thumbs">
		<?php foreach($attachment_ids as  $key=>$attachment_id) {
		    $ext = pathinfo(get_attached_file($attachment_id), PATHINFO_EXTENSION);
		    if($ext == 'mov' || $ext == 'mp4') {
		        // get attachment url
		        $url = wp_get_attachment_url($attachment_id);

		        $src = '<video  src="'.$url.'" ></video>';
		    } else {
		        $src= wp_get_attachment_image($attachment_id, 'product_slide_thumbnail', array('loading'=>true));
		    }
		    ?>

			<figure class="thumb <?php if ($key==0) {
			    echo('thumb--active');
			}?>">
				<?= $src ?>
			</figure>
		<?php };?>
	</div>
	<?php endif;?>
	<a href="" class="diagnostic-cta">
		Faire le diagnostic
	</a>
	<?php 
	$referlink =get_field('link_refer', 'option');
	
	if($referlink ):
		;?>
	<a class="referlink" href="<?=$referlink['url'];?>" class="diagnostic-cta">

		<svg viewBox="0 0 100 100" class="textpath" width="100" height="100">
			<defs>
				<path id="circle"
				d="
					M 50, 50
					m -37, 0
					a 37,37 0 1,1 74,0
					a 37,37 0 1,1 -74,0"/>
			</defs>
			<text font-size="17">
				<textPath xlink:href="#circle">
				<?=$referlink['title'];?>
				</textPath>
			</text>
			</svg>
			<svg class="icon" width="24" height="37" viewBox="0 0 24 37" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10.3232 0.999999C11.0684 -0.333334 12.9316 -0.333333 13.6768 1L23.7377 19C24.483 20.3333 23.5514 22 22.0609 22H1.93911C0.448604 22 -0.482958 20.3333 0.262293 19L10.3232 0.999999Z" fill="#364321"/>
			<path d="M10.7596 16.2439C11.3141 15.252 12.6859 15.252 13.2404 16.2439L23.3013 34.2439C23.8737 35.2682 23.1422 36.5 22.0609 36.5H1.93911C0.857755 36.5 0.126276 35.2682 0.698744 34.2439L10.7596 16.2439Z" fill="#364321" stroke="#FFD372"/>
			</svg>

	</a>
	<?php endif;?>

</div>


