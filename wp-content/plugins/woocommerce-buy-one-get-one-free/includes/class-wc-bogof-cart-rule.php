<?php
/**
 * Buy One Get One Free Cart Rule. Handles BOGO rule actions.
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Cart_Rule Class
 */
class WC_BOGOF_Cart_Rule {

	/**
	 * Cart rule ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * BOGOF rule.
	 *
	 * @var WC_BOGOF_Rule
	 */
	protected $rule;

	/**
	 * Array of cart totals.
	 *
	 * @var array
	 */
	protected $totals;

	/**
	 * Array of notices.
	 *
	 * @var array
	 */
	protected $notices;

	/**
	 * Product ID - For "individual" rules.
	 *
	 * @var int
	 */
	protected $product_id;


	/**
	 * Constructor.
	 *
	 * @param WC_BOGOF_Rule $rule BOGOF rule.
	 */
	public function __construct( $rule ) {
		$this->rule       = $rule;
		$this->totals     = array();
		$this->notices    = array();
		$this->product_id = 0;
		$this->id         = '';

		add_action( 'wc_bogof_cart_rule_clear_totals', array( $this, 'clear_totals' ) );
		add_action( 'woocommerce_add_to_cart', array( $this, 'clear_totals' ), 0 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'clear_totals' ), 0 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'clear_totals' ), 0 );
	}

	/**
	 * Set the ID
	 *
	 * @param string $id Object ID.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Return the cart rule ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the product ID.
	 *
	 * @param int $product_id Product ID.
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * Return the rule ID.
	 */
	final public function get_rule_id() {
		return $this->rule->get_id();
	}

	/**
	 * Return the rule ID.
	 */
	final public function get_rule() {
		return $this->rule;
	}

	/**
	 * Unset the totals array.
	 */
	public function clear_totals() {
		$this->totals = array();
	}

	/**
	 * Does the Cart Rule support gifts in the cart?
	 */
	public function support_gifts() {
		return true;
	}

	/**
	 * Does the Cart Rule support choose your gift?
	 */
	public function support_choose_your_gift() {
		return true;
	}

	/**
	 * Does the cart item match with the rule?
	 *
	 * @param array $cart_item Cart item.
	 * @return bool
	 */
	public function cart_item_match( $cart_item ) {
		if ( WC_BOGOF_Cart::is_free_item( $cart_item ) || wc_bogof_cart_item_match_skip( $this, $cart_item ) ) {
			return false;
		}

		$match = $this->rule->is_buy_product( $cart_item );

		if ( $match && $this->rule->is_individual() && $this->product_id ) {
			$product_id = isset( $cart_item['data'] ) && is_callable( array( $cart_item['data'], 'get_id' ) ) ? $cart_item['data']->get_id() : false;
			$match      = $match && ( $product_id === $this->product_id );
		}

		return $match;
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 */
	protected function add_to_cart( $qty = 1 ) {
		$items = WC_BOGOF_Cart::get_free_items( $this->get_id() );

		if ( count( $items ) ) {
			// Set the qty.
			$cart_item_keys = array_keys( $items );
			$cart_item_key  = $cart_item_keys[0];

			$cart_item = WC()->cart->get_cart_item( $cart_item_key );
			if ( ! empty( $cart_item ) && isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) && isset( $cart_item['data'] ) ) {

				$product_data = $cart_item['data'];

				// Force quantity to 1 if sold individually and check for existing item in cart.
				if ( $product_data->is_sold_individually() ) {
					$qty = apply_filters( 'woocommerce_add_to_cart_sold_individually_quantity', 1, $qty, $cart_item['product_id'], 0, $cart_item );
				}

				$qty_added = $qty - $cart_item['quantity'];
				if ( $qty_added > 0 ) {
					// Set the quantity.
					WC()->cart->set_quantity( $cart_item_key, $qty, false );
					// Update the discount.
					WC_BOGOF_Cart::set_cart_item_discount( $cart_item_key, $this->get_id(), $qty );

					// Add the message.
					$this->add_free_product_to_cart_message( $cart_item['product_id'], $qty_added );
				}
			}
		} else {
			// Add the item to the cart.
			$cart_item_key = $this->add_free_product_to_cart( $qty );

			if ( $cart_item_key ) {
				$cart_item = WC()->cart->get_cart_item( $cart_item_key );

				if ( ! empty( $cart_item ) && isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) ) {
					$this->add_free_product_to_cart_message( $cart_item['product_id'], $cart_item['quantity'] );
				}

				/**
				 * Trigger after adding the fee item to the cart automatically.
				 *
				 * @since 3.7.0
				 */
				do_action( 'wc_bogof_auto_add_to_cart', $cart_item, $this );

			} elseif ( current_user_can( 'manage_woocommerce' ) ) {
				// Displays the error to store managers.
				$notices = wc_get_notices();
				if ( ! empty( $notices['error'] ) && is_array( $notices['error'] ) ) {
					$errors = array();
					foreach ( $notices['error'] as $notice ) {
						$errors[] = isset( $notice['notice'] ) ? $notice['notice'] : $notice;
					}
					$error_text = __( 'The Buy One Get One Free plugin was unable to add the product to the cart. Please check the following errors', 'wc-buy-one-get-one-free' ) . ':<br>' . implode( '<br>', $errors );

					wc_clear_notices();
					wc_add_notice( $error_text, 'error' );
				}
			}
		}

		$this->clear_totals();
	}

	/**
	 * Add the free product to the cart.
	 *
	 * @param int $qty The quantity of the item to add.
	 * @return string|bool $cart_item_key
	 */
	protected function add_free_product_to_cart( $qty ) {
		$cart_item_key = false;
		$product_id    = $this->rule->get_free_product_id();
		if ( $product_id ) {
			// Add the gift.
			$cart_item_data = array(
				'_bogof_free_item' => $this->get_id(),
			);
			$cart_item_key  = WC()->cart->add_to_cart( $product_id, $qty, 0, array(), $cart_item_data );

			// Check post status and warning store manager.
			if ( current_user_can( 'manage_woocommerce' ) ) {

				$product_data = false;

				if ( $cart_item_key ) {
					$cart_item    = WC()->cart->get_cart_item( $cart_item_key );
					$product_data = $cart_item['data'];
				} else {
					$product_data = wc_get_product( $product_id );
				}

				if ( ! $product_data ) {
					// Translators: %s: Product ID.
					wc_add_notice( sprintf( __( 'The product "#%s" does not exits.', 'wc-buy-one-get-one-free' ), $product_id ), 'error' );
				} elseif ( 'publish' !== $product_data->get_status() ) {
					// Translators: %s: Product title.
					wc_add_notice( sprintf( __( 'Warning: "%s" must be public for this BOGO promotion to work for customer.', 'wc-buy-one-get-one-free' ), $product_data->get_title() ), 'error' );
				}
			}
		}
		return $cart_item_key;
	}

	/**
	 * Add free product to cart message.
	 *
	 * @param int $product_id Product ID.
	 * @param int $qty Quantity.
	 */
	protected function add_free_product_to_cart_message( $product_id, $qty ) {
		global $wp_query;

		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && 'add_to_cart' === $wp_query->get( 'wc-ajax' ) && 'yes' !== get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			return;
		}

		/* translators: %s: product name */
		$title = apply_filters( 'woocommerce_add_to_cart_qty_html', absint( $qty ) . ' &times; ', $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'wc-buy-one-get-one-free' ), wp_strip_all_tags( get_the_title( $product_id ) ) ), $product_id );
		if ( 100 === $this->get_rule()->get_discount() ) {
			/* translators: %s: product name */
			$message = sprintf( _n( '%s has been added to your cart for free!', '%s have been added to your cart for free!', $qty, 'wc-buy-one-get-one-free' ), $title );
		} else {
			/* translators: 1: product name, 2: percentage discount */
			$message = sprintf( _n( '%1$s has been added to your cart with %2$s off!', '%1$s has been added to your cart with %2$s off!', $qty, 'wc-buy-one-get-one-free' ), $title, $this->get_rule()->get_discount() . '%' );
		}

		// Add the notices to the array.
		$this->notices[] = apply_filters( 'wc_bogof_add_free_product_to_cart_message_html', $message, $product_id, $qty );

		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$this->add_messages();
		}
	}

	/**
	 * Add the free product messages to the session.
	 */
	public function add_messages() {
		foreach ( $this->notices as $notice ) {
			wc_add_notice( $notice, apply_filters( 'woocommerce_add_to_cart_notice_type', 'success' ) );
		}
		$this->notices = array();
	}

	/**
	 * Check the cart
	 *
	 * @return bool
	 */
	protected function check_cart() {
		return $this->check_cart_amount() && $this->check_cart_coupons();
	}

	/**
	 * Check the cart amount.
	 *
	 * @return bool
	 */
	protected function check_cart_amount() {
		$minimum_amount = $this->rule->get_minimum_amount();
		$minimum_amount = empty( $minimum_amount ) ? 0 : $minimum_amount;

		return WC_BOGOF_Cart::cart_subtotal() > $minimum_amount;
	}


	/**
	 * Check the cart coupons.
	 *
	 * @return bool
	 */
	protected function check_cart_coupons() {
		$coupons = $this->rule->get_coupon_codes();
		$valid   = empty( $coupons );
		if ( ! $valid ) {
			$valid = wc_bogof_in_array_intersect( $coupons, WC()->cart->get_applied_coupons() );
		}
		return $valid;
	}

	/**
	 * Returns the quantity from a cart item.
	 *
	 * @param array $cart_item Cart item data.
	 * @param bool  $raw Get raw value? Do not remove discounts qty if raw is true.
	 * @return int
	 */
	protected function get_cart_item_quantity( $cart_item, $raw = false ) {
		$quantity = isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 0;
		if ( ! $raw && WC_BOGOF_Cart::is_valid_discount( $cart_item ) ) {
			$quantity -= $cart_item['data']->_bogof_discount->get_free_quantity();
		}
		return 0 > $quantity ? 0 : $quantity;
	}

	/**
	 * Return the number of items in the cart that match the rule.
	 *
	 * @since 2.2.0
	 * @param bool $raw Get raw value? Do not remove discounts qty if raw is true.
	 * @return int
	 */
	protected function count_cart_quantity( $raw = false ) {
		$cart_quantity = 0;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents as $key => $cart_item ) {
			if ( $this->cart_item_match( $cart_item ) ) {
				$cart_quantity += $this->get_cart_item_quantity( $cart_item, $raw );
			}
		}

		return $cart_quantity;
	}

	/**
	 * Count numbers of products that match the rule.
	 *
	 * @return int
	 */
	public function get_cart_quantity() {
		if ( ! isset( $this->totals['cart_quantity'] ) ) {
			$this->totals['cart_quantity'] = $this->count_cart_quantity();
		}
		return $this->totals['cart_quantity'];
	}

	/**
	 * Calculate the available number of free items.
	 *
	 * @since 2.2.0
	 * @param int $cart_qty Number of items that match the rule.
	 * @return int
	 */
	protected function calculate_free_items( $cart_qty ) {
		$free_qty = 0;
		if ( $cart_qty >= $this->rule->get_min_quantity() &&
			0 < $this->rule->get_min_quantity() &&
			0 < $this->rule->get_free_quantity() &&
			$this->check_cart()
		) {

			$free_qty = absint( ( floor( $cart_qty / $this->rule->get_min_quantity() ) * $this->rule->get_free_quantity() ) );

			if ( $this->rule->get_cart_limit() && $free_qty > $this->rule->get_cart_limit() ) {
				$free_qty = $this->rule->get_cart_limit();
			}
		}
		return $free_qty;
	}

	/**
	 * Get the quantity of the free items based on rule and on the product quantity in the cart.
	 *
	 * @return int
	 */
	public function get_max_free_quantity() {
		if ( ! isset( $this->totals['free_quantity'] ) ) {
			$this->totals['free_quantity'] = $this->calculate_free_items(
				$this->get_cart_quantity()
			);
		}

		return apply_filters( 'wc_bogof_free_item_quantity', $this->totals['free_quantity'], $this->get_cart_quantity(), $this->rule, $this );
	}

	/**
	 * Returns the number of items available for free in the shop.
	 *
	 * @return int
	 */
	public function get_shop_free_quantity() {
		if ( ! isset( $this->totals['shop_free_quantity'] ) ) {
			$this->totals['shop_free_quantity'] = $this->support_choose_your_gift() ? $this->get_max_free_quantity() - WC_BOGOF_Cart::get_free_quantity( $this->get_id() ) : 0;
		}
		return $this->totals['shop_free_quantity'];
	}

	/**
	 * Is the product avilable for free in the shop.
	 *
	 * @param int|WC_Product $product Product ID or Product object.
	 * @return bool
	 */
	public function is_shop_avilable_free_product( $product ) {
		$is_free = false;
		if ( $this->get_shop_free_quantity() > 0 ) {
			if ( is_numeric( $product ) ) {
				$is_free = $this->rule->is_free_product( $product );
			} elseif ( is_a( $product, 'WC_Product' ) ) {
				$is_free = $this->rule->is_free_product( $product->get_id() );
				if ( ! $is_free && 'variable' === $product->get_type() ) {
					foreach ( $product->get_children() as $child_id ) {
						$is_free = $this->rule->is_free_product( $child_id );
						if ( $is_free ) {
							break;
						}
					}
				}
			}
		}
		return $is_free;
	}

	/**
	 * Calculate the discount amount.
	 *
	 * @param float $amount The base amount to calculate the discount.
	 * @param int   $quantity Quantity to which to apply the discount.
	 * @return float
	 */
	public function calculate_discount( $amount, $quantity = 1 ) {
		return $amount * $quantity * $this->get_rule()->get_discount() / 100;
	}

	/**
	 * Update the quantity of free items in the cart.
	 *
	 * @param bool $add_to_cart Add free items to cart?.
	 */
	public function update_free_items_qty( $add_to_cart = true ) {

		$max_qty        = $this->get_max_free_quantity();
		$free_items_qty = WC_BOGOF_Cart::get_free_quantity( $this->get_id() );

		if ( $free_items_qty > $max_qty ) {

			$items    = WC_BOGOF_Cart::get_free_items( $this->get_id() );
			$over_qty = $free_items_qty - $max_qty;

			foreach ( $items as $key => $item ) {
				if ( 0 === $over_qty ) {
					break;
				}

				if ( $item['quantity'] > $over_qty ) {
					// Set the item quantity.
					WC()->cart->set_quantity( $key, $item['quantity'] - $over_qty, false );
					// Update the discount.
					WC_BOGOF_Cart::set_cart_item_discount( $key, $this->get_id(), $item['quantity'] - $over_qty );
					// Exit.
					$over_qty = 0;
				} else {
					WC()->cart->set_quantity( $key, 0, false );
					$over_qty -= $item['quantity'];
				}
			}
		} elseif ( $add_to_cart && $this->rule->is_action( 'add_to_cart' ) && ( $max_qty - $free_items_qty ) > 0 ) {
			$this->add_to_cart( $max_qty );
		}
	}

	/**
	 * Returns SQL string of the free avilable products to be use in a SELECT.
	 *
	 * @see WC_BOGOF_Choose_Gift::posts_where
	 * @return string
	 */
	public function get_free_products_in() {
		if ( $this->get_shop_free_quantity() < 1 ) {
			return false;
		}

		return WC_BOGOF_Conditions::get_where_clause(
			$this->get_rule()->get_gift_products()
		);
	}

	/**
	 * Does the rule match?.
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	public function match() {
		$match_items = $this->count_cart_quantity( true );
		$free_items  = $this->calculate_free_items( $match_items );
		return $free_items > 0;
	}
}
