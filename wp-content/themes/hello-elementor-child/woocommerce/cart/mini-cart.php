<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 *
 * @package WooCommerce\Templates
 *
 * @version 5.2.0
 */
defined('ABSPATH') || exit;
global $woocommerce;

$counter =$woocommerce->cart->cart_contents_count;
$notices = wc_get_notices();

wc_clear_notices();


do_action('woocommerce_before_mini_cart');
$classes = ''; 
$hide ='hide';

if(isset($args['loaded']) && $args['loaded']=='true' && $counter> 0){
	$hide ='';
}

if(!is_cart() && $counter> 0){
	$classes = ' toggle-cart';
}
if(is_cart()){
	
$classes = ' toggle-cart no-touch';

}
?>

<div class="woocart-icon <?=$classes;?>">

	
		<?php if($counter > 0) :?>
			<span class="count"><?php echo $counter;?></span>
		<?php endif;?>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<mask id="mask0_155_3600" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
				<rect width="24" height="24" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_155_3600)">
				<path d="M5.61538 21C5.16794 21 4.78685 20.8426 4.4721 20.5279C4.15737 20.2131 4 19.8321 4 19.3846V8.61538C4 8.16794 4.15737 7.78685 4.4721 7.4721C4.78685 7.15737 5.16794 7 5.61538 7H8C8 5.8859 8.38814 4.94071 9.16442 4.16443C9.94071 3.38814 10.8859 3 12 3C13.1141 3 14.0593 3.38814 14.8356 4.16443C15.6119 4.94071 16 5.8859 16 7H18.3846C18.8321 7 19.2132 7.15737 19.5279 7.4721C19.8426 7.78685 20 8.16794 20 8.61538V19.3846C20 19.8321 19.8426 20.2131 19.5279 20.5279C19.2132 20.8426 18.8321 21 18.3846 21H5.61538ZM5.61538 20H18.3846C18.5385 20 18.6795 19.9359 18.8077 19.8077C18.9359 19.6795 19 19.5385 19 19.3846V8.61538C19 8.46154 18.9359 8.32052 18.8077 8.1923C18.6795 8.0641 18.5385 8 18.3846 8H5.61538C5.46154 8 5.32052 8.0641 5.1923 8.1923C5.0641 8.32052 5 8.46154 5 8.61538V19.3846C5 19.5385 5.0641 19.6795 5.1923 19.8077C5.32052 19.9359 5.46154 20 5.61538 20ZM12 13C13.1141 13 14.0593 12.6119 14.8356 11.8356C15.6119 11.0593 16 10.1141 16 9H15C15 9.83333 14.7083 10.5417 14.125 11.125C13.5417 11.7083 12.8333 12 12 12C11.1667 12 10.4583 11.7083 9.875 11.125C9.29167 10.5417 9 9.83333 9 9H8C8 10.1141 8.38814 11.0593 9.16442 11.8356C9.94071 12.6119 10.8859 13 12 13ZM9 7H15C15 6.16667 14.7083 5.45833 14.125 4.875C13.5417 4.29167 12.8333 4 12 4C11.1667 4 10.4583 4.29167 9.875 4.875C9.29167 5.45833 9 6.16667 9 7Z" fill="#131313"/>
				</g>
				</svg>




	
</div>
<div class="minicart-aside <?=$hide;?>">


<?php if ( ! WC()->cart->is_empty() ) : ?>
	<div class="minicart-aside__header">
		<p class="minicart-title">Panier : <span><?php echo $counter;?> article<?php if($counter > 1) :?>s<?php endif;?></span></p>
		<span class="toggle-close">
	<svg class="" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M1.39911 13.3078L0.691406 12.6001L6.29141 7.00008L0.691406 1.40008L1.39911 0.692383L6.99911 6.29238L12.5991 0.692383L13.3068 1.40008L7.70681 7.00008L13.3068 12.6001L12.5991 13.3078L6.99911 7.70778L1.39911 13.3078Z" fill="#1C1B1F"/>
			</svg>
</span>
			<?php echo  do_shortcode('[woo_free_shipping_bar ]');?>
	</div>
		

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );


		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				// if($_product->get_type()=='gift-card' && !empty($cart_item['ywgc_amount'])){
				// 	$pp     =round(intval($cart_item['ywgc_amount']*$cart_item['quantity']),2);
				// 	$product_price  =$pp ;
				// }
				// var_dump($cart_item);
			
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					<!-- thumbnail -->
					<div class="woocommerce-mini-cart-item__thumbnail">
						<?php if ( empty( $product_permalink ) ) : ?>
							<?php echo $thumbnail . wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>" aria-label="<?=$product_name;?>" class="product-info">
								<figure><?php echo $thumbnail ?></figure>
							</a>
						<?php endif; ?>
					</div>
					<!-- metas -->
					<div class="woocommerce-mini-cart-item__datas">
						<p><?=$product_name;?> </p>
					
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					
						<p class="qty">	<?=$cart_item['quantity'];?> X <?=$product_price;?> </p>
					</div>
					<!-- qty +rpice	 -->
					<div class="woocommerce-mini-cart-item__aside">
						<span class="remove_from_cart_button" data-product_id="<?=$product_id;?>" data-cart_item_key="<?=$cart_item_key;?>" data-product_sku="<?=$_product->get_sku();?>">Supprimer</span>

					</div>	
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>
	<div class="minicart-aside__footer">

		<p class="woocommerce-mini-cart__total total">

			<?php
			/**
			 * Hook: woocommerce_widget_shopping_cart_total.
			 *
			 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
			 */
			do_action( 'woocommerce_widget_shopping_cart_total' );
			?>
		</p>


		<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

		<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

		<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
	</div>
<?php endif ; ?>
</div>
<?php if(!empty($notices)):?>
	<div class="global-notices">
		<?php if(! empty($notices['success'])):?>
			<?php foreach($notices['success'] as $success):?>
				<div class="notice success"><?=$success['notice'];?></div>
			<?php endforeach;?>
		<?php endif;?>
		<?php if(! empty($notices['errors'])):?>
			<?php foreach($notices['errors'] as $errors):?>
				<div class="notice errors"><?=$errors['notice'];?></div>
			<?php endforeach;?>
		<?php endif;?>
		<?php if(! empty($notices['error'])):?>
		
				<div class="notice errors"><?=$notices['error'][0]['notice'];?></div>
			
		<?php endif;?>
	</div>
<?php endif;?>
