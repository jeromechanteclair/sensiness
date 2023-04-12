<?php
/**
 * Buy One Get One Free Cart Rule Buy A Get A. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule_Buy_A_Get_A Class
 */
class WC_BOGOF_Cart_Rule_Buy_A_Get_A extends WC_BOGOF_Cart_Rule {

	/**
	 * Unique ID to handle the add to cart action.
	 *
	 * @var string
	 */
	protected $uniqid;

	/**
	 * Does the Cart Rule support choose your gift?
	 */
	public function support_choose_your_gift() {
		return false;
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 */
	protected function add_free_product_to_cart( $qty ) {
		$cart_item_data = false;
		$cart_item_key  = false;

		foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
			if ( $this->cart_item_match( $cart_item ) ) {
				$cart_item_data = $cart_item;
				break;
			}
		}

		if ( false !== $cart_item_data ) {

			$this->uniqid = uniqid( $this->get_id() );

			$cart_item_data['product_id'] = isset( $cart_item_data['product_id'] ) ? $cart_item_data['product_id'] : $this->product_id;

			$cart_item_key = WC_BOGOF_Cart::add_to_cart_from_item(
				$cart_item_data,
				$qty,
				array(
					'_bogof_free_item' => $this->get_id(),
				)
			);
		}
		return $cart_item_key;
	}
}
