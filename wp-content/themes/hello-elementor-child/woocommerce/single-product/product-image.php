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
			            // $src= wp_get_attachment_image($attachment_id, 'product_slide', false, array('loading'=>true));
					$desktop= wp_get_attachment_image_src($attachment_id, 'product_slide' );
					$desktop_double= wp_get_attachment_image_src($attachment_id, 'product_slide_double' );
					$mobile= wp_get_attachment_image_src($attachment_id, 'product_slide_mobile' );
					$mobile_double= wp_get_attachment_image_src($attachment_id, 'product_slide_mobile_double' );
					$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

					$src='  
					<picture>
						<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)">
						<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)">
							<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy">
						</picture>';
			        } else {


					$desktop= wp_get_attachment_image_src($attachment_id, 'product_slide');
					$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
					$mobile= wp_get_attachment_image_src($attachment_id, 'product_slide_mobile');
					$desktop_double= wp_get_attachment_image_src($attachment_id, 'product_slide_double');
					$mobile_double= wp_get_attachment_image_src($attachment_id, 'product_slide_mobile_double');

					$src='  
					<picture>
						<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)">
						<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)">
						<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy">
					</picture>';
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

</div>


