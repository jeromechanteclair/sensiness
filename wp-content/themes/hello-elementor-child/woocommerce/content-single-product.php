<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-wrapper container', $product ); ?>>

	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked woocommerce_show_product_sale_flash - 10
	 * @hooked woocommerce_show_product_images - 20
	 */
	do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary">
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>
		<?php
			/**
			 * Hook: woocommerce_after_single_product_summary.
			 *
			 * @hooked woocommerce_output_product_data_tabs - 10
			 * @hooked woocommerce_upsell_display - 15
			 * @hooked woocommerce_output_related_products - 20
			 */
			do_action('woocommerce_after_single_product_summary');
		?>



				
	</div>

			
</div>

<?php wc_get_template('single-product/bandeau-reassurance.php');?>

<?php if(get_field('video')):?>
		
		<div class="video-container">
			<video  id='video-player' playsinline preload='metadata' width='100%' height='100%' loop>
			<source src="<?=get_field('video')['url'];?>" type=
			"video/mp4">

		</video>
	</div>
<?php endif;?>

<?php wc_get_template('single-product/ingredients.php');?>
<?php wc_get_template('single-product/bandeau-marquee.php');?>
<?php wc_get_template('single-product/long-description.php');?>
<?php wc_get_template('single-product/calculateur.php');?>

<?php do_action('woocommerce_reviews');?>
<?php wc_get_template('single-product/diagnostic.php');?>
<?php woocommerce_output_related_products();?>
<?php wc_get_template('single-product/refer.php');?>

<?php wc_get_template('single-product/bandeau-marquee.php');?>
<?php wc_get_template('single-product/images.php');
?>

<div class="faq">
	<header>
		<h2>Foire aux questions</h2>
		<p>Si vous avez la moindre question, contactez-nous !</p>
		<a class="faq-button" href="/contact">nous contacter</a>
	</header>
	<?= do_shortcode( '[FAQ]');?>
	
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>
