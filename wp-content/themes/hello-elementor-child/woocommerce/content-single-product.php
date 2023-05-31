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
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-wrapper', $product ); ?>>

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
		?><p>

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam interdum condimentum imperdiet. Vestibulum efficitur placerat velit. Quisque blandit, risus ut finibus eleifend, felis enim malesuada leo, non dictum urna nulla nec turpis. Nunc fermentum urna quis lacus porta convallis. Aenean sagittis dignissim ex eu commodo. Nulla suscipit mi accumsan metus laoreet sagittis. Mauris at sollicitudin augue. In egestas sodales tellus ut scelerisque. Nulla facilisi. Aenean eget aliquet tortor, at posuere elit. Duis accumsan mi vel risus auctor, nec laoreet libero condimentum. Proin leo enim, commodo vitae est at, imperdiet pellentesque libero. Sed nunc nibh, convallis non quam sit amet, ornare porta eros. Nullam semper orci non hendrerit auctor. Integer hendrerit, elit ut eleifend interdum, mi tortor cursus velit, sed auctor nunc mauris ac magna.

Quisque ut neque vitae diam scelerisque suscipit ut in dolor. Aenean sagittis ullamcorper volutpat. Sed leo justo, congue ac ipsum sit amet, porta maximus sem. Phasellus vel vulputate tortor, at ultrices turpis. Vestibulum auctor vestibulum nunc. Sed dictum, sapien quis dignissim suscipit, lectus erat varius dolor, sed pellentesque dui lacus sit amet enim. Integer sagittis consequat sapien, eget porta ante dapibus sit amet. </p>
				
	</div>


</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
