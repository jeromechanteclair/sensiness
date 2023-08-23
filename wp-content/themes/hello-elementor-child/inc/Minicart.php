<?php

namespace sensiness\app;

class Minicart{
	
    public function __construct(){
    


		add_action('wp_ajax_ajax_add_to_cart', array($this,'ajax_add_to_cart'), 10);
		add_action('wp_ajax_nopriv_ajax_add_to_cart', array($this,'ajax_add_to_cart'), 10);
		add_action('wp_ajax_ajax_remove_from_cart', array($this,'ajax_remove_from_cart'), 10);
		add_action('wp_ajax_nopriv_ajax_remove_from_cart', array($this,'ajax_remove_from_cart'), 10);


    }
	/**
	 * AJAX add to cart.
	 * single item
	 */
	public function ajax_add_to_cart()	{
		$searcharray = $_POST;
		wc_clear_notices();
		ob_start();
		if(isset($searcharray['wc_bookings_field_persons'])){
			$qty =1;
		}
		else{
			$qty =intval($searcharray['quantity']);
		}
		if (! empty($searcharray)) {
			$product_id =  $searcharray['product_id'] ;
			if (isset($searcharray['empty_cart'])) {
				WC()->cart->empty_cart();
			}
			if (isset($searcharray['remove_coupon'])) {
				global $woocommerce;
				WC()->cart->remove_coupons();
			}
			$notices= $success=$errors = [];
			if (! empty($product_id)) {
				$product           = wc_get_product($product_id);
				$quantity =   $qty ;
				$variation_id      = 0;
				$variation         = [];
				$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
				$product_status    = get_post_status($product_id);

				if ('variable' === $product->get_type() && ! $variation_id) {
					$added =false;
					wc_add_notice('Produit indisponible', 'error');
				}
				$added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

				if ($passed_validation && false !== $added && 'publish' === $product_status) {
					do_action('woocommerce_ajax_added_to_cart', $product_id);
					// wc_add_notice($product->name.' ajouté au panier', 'success');
				
				wc_add_notice($product->name.' ajouté au panier', 'success');
					// wc_add_to_cart_message($product_id);
					$success =   wc_get_notices('success');
				} else {
					// If there was an error adding to the cart, redirect to the product page to show any errors
					$errors = wc_get_notices('error');
				}
			}

			$totals = WC()->cart->calculate_totals();
			WC()->cart->maybe_set_cart_cookies();
			woocommerce_mini_cart(['loaded'=>$_POST['loaded']]);
			$mini_cart = ob_get_clean();
			// Fragments and mini cart are returned
			$data = [
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					[
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">'.$mini_cart.'</div>'
					]
				),
				'html'=> $mini_cart,
				'notices'=>['success'=>$success,'errors'=>$errors],
				'cart_hash' => apply_filters('woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5(json_encode(WC()->cart->get_cart_for_session())) : '', WC()->cart->get_cart_for_session())
			];

			wp_send_json($data);

			die();
		}
	}



	/**
	 * AJAX remove from cart.
	 * single item
	 */
	public function ajax_remove_from_cart()
	{
	
		wc_clear_notices();

		// Get mini cart
		ob_start();

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			if ($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key']) {
				WC()->cart->remove_cart_item($cart_item_key);
				$product           = wc_get_product($cart_item['product_id'] );
				
				wc_add_notice($product->name.' retiré du panier', 'success');

			}
		}


			$counter = WC()->cart->cart_contents_count;



	
			woocommerce_mini_cart(['loaded'=>true]);

			$mini_cart = ob_get_clean();
			$success ='ok';
			$errors='';
			// Fragments and mini cart are returned
			$data = [
				'fragments' => apply_filters(
					'woocommerce_add_to_cart_fragments',
					[
						'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">'.$mini_cart.'</div>'
					]
				),
				'html'=> $mini_cart,
				'counter'=>$counter,
				'notices'=>['success'=>$success,'errors'=>$errors],
				'cart_hash' => apply_filters('woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5(json_encode(WC()->cart->get_cart_for_session())) : '', WC()->cart->get_cart_for_session())
			];

			wp_send_json($data);
			WC()->cart->calculate_totals();


	}
}
new Minicart();