<?php
/**
 * Buy One Get One Free - WooCommerce Composite Products by WooCommerce
 *
 * @see https://woocommerce.com/products/composite-products/
 * @since 3.6.0
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Composite_Products Class
 */
class WC_BOGOF_Composite_Products {

	/**
	 * Retrun the minimun version required.
	 */
	public static function min_version_required() {
		return '8.5.0';
	}

	/**
	 * Returns the extension name.
	 */
	public static function extension_name() {
		return 'Composite Products';
	}

	/**
	 * Checks the minimum version required.
	 */
	public static function check_min_version() {
		return defined( 'WC_CP_VERSION' ) ? version_compare( WC_CP_VERSION, static::min_version_required(), '>=' ) : false;
	}

	/**
	 * Init hooks
	 */
	public static function init() {
		add_action( 'wc_bogof_after_set_cart_item_discount', array( __CLASS__, 'cart_item_discount_init' ), 10, 2 );
		add_action( 'wc_bogof_init_cart_item_discount', array( __CLASS__, 'cart_item_discount_init' ), 10, 2 );
		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'composite_flags' ), 50 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'cart_item_set_quantity' ), 20 );
		add_action( 'woocommerce_remove_cart_item', array( __CLASS__, 'remove_cart_item' ), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 5 );
		add_filter( 'wc_bogof_cart_item_discount_sale_price', array( __CLASS__, 'cart_item_discount_sale_price' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'cart_item_subtotal' ), 9999, 2 );
		add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'cart_item_price' ), 9999, 2 );
		add_filter( 'wc_bogof_should_add_cart_rule', array( __CLASS__, 'should_add_cart_rule' ), 10, 3 );
		add_filter( 'wc_bogof_cart_item_match_skip', array( __CLASS__, 'cart_item_match_skip' ), 10, 3 );
		// Admin.
		add_action( 'wc_bogof_after_metabox_settings_fields', array( __CLASS__, 'metabox_settings_fields' ) );
		add_action( 'wc_bogof_admin_process_rule_object', array( __CLASS__, 'admin_process_rule_object' ), 10, 2 );
	}

	/**
	 * Recalculate the container base price.
	 *
	 * @param array                       $cart_item Cart item data.
	 * @param WC_BOGOF_Cart_Item_Discount $cart_discount The discount object.
	 */
	public static function cart_item_discount_init( $cart_item, $cart_discount ) {
		if ( wc_cp_is_composite_container_cart_item( $cart_item ) && ! $cart_discount->get_extra_data( 'is_composite_container' ) ) {
			// Calculate the price based on components items.
			$container_price = $cart_discount->get_base_price();
			$base_price      = $container_price;
			$items_price     = 0;
			$child_items     = wc_cp_get_composited_cart_items( $cart_item, WC()->cart->cart_contents, false, true );

			foreach ( $child_items as $child_item ) {
				$base_price  += $child_item['data']->get_price() * absint( $child_item['quantity'] ) / absint( $cart_item['quantity'] );
				$items_price += $child_item['data']->get_price() * $child_item['quantity'];
			}

			$cart_discount->set_base_price( $base_price );
			$cart_discount->add_extra_data( 'is_composite_container', true );
			$cart_discount->add_extra_data( 'container_price', $container_price );
			$cart_discount->add_extra_data( 'child_items_price', $items_price );
		}
	}

	/**
	 * Add flags to the composite free composite items.
	 */
	public static function composite_flags() {
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents as $key => $cart_item ) {

			if ( WC_BOGOF_Cart::is_valid_free_item( $cart_item ) && wc_cp_is_composite_container_cart_item( $cart_item ) ) {
				// No editable in the cart.
				WC()->cart->cart_contents[ $key ]['data']->set_editable_in_cart( false );

				// Update the children component.
				foreach ( $cart_item['composite_children'] as $children_key ) {
					if ( ! isset( WC()->cart->cart_contents[ $children_key ]['composite_data'] ) ) {
						continue;
					}

					// No edit quantity of children components.
					foreach ( WC()->cart->cart_contents[ $children_key ]['composite_data'] as $id => $data ) {
						WC()->cart->cart_contents[ $children_key ]['composite_data'][ $id ]['quantity_max'] = $data['quantity'];
						WC()->cart->cart_contents[ $children_key ]['composite_data'][ $id ]['quantity_min'] = $data['quantity'];
					}
				}
			}
		}
	}

	/**
	 * Sync quantity of components child items for the Buy A get A rule.
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function cart_item_set_quantity( $cart_item_key ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		$avoid_recursion = true;
		$cart_item       = isset( WC()->cart->cart_contents[ $cart_item_key ] ) ? WC()->cart->cart_contents[ $cart_item_key ] : array();
		$parent_key      = isset( $cart_item['composite_parent'] ) && isset( $cart_item['composite_item'] ) ? $cart_item['composite_parent'] : false;

		if ( ! $parent_key || self::is_container_discount_cart_item( WC()->cart->cart_contents[ $parent_key ] ) ) {
			return;
		}

		$cart_rules = wc_bogof_cart_rules()->get_by_cart_item_key( $parent_key );

		foreach ( $cart_rules as $cart_rule ) {
			if ( 'buy_a_get_a' !== $cart_rule->get_rule()->get_type() ) {
				continue;
			}

			// Sync the free items.
			$free_items = WC_BOGOF_Cart::get_free_items( $cart_rule->get_id() );
			foreach ( $free_items as $free_item_key => $free_item ) {
				if ( ! isset( $free_item['composite_children'] ) ) {
					continue;
				}

				foreach ( $free_item['composite_children'] as $children_key ) {
					if ( isset( WC()->cart->cart_contents[ $children_key ]['composite_item'] ) && WC()->cart->cart_contents[ $children_key ]['composite_item'] === $cart_item['composite_item'] ) {
						WC()->cart->set_quantity( $children_key, $cart_item['quantity'] );
					}
				}
			}
		}

		$avoid_recursion = false;
	}

	/**
	 * Sync the composite items with the Buy A Get A free items.
	 *
	 * @param string $cart_item_key Cart item key.
	 */
	public static function remove_cart_item( $cart_item_key ) {
		static $avoid_recursion = false;

		if ( $avoid_recursion ) {
			return;
		}

		$cart_item  = WC()->cart->removed_cart_contents[ $cart_item_key ];
		$parent_key = isset( $cart_item['composite_parent'] ) && isset( $cart_item['composite_item'] ) ? $cart_item['composite_parent'] : false;

		if ( ! $parent_key || self::is_container_discount_cart_item( WC()->cart->cart_contents[ $parent_key ] ) ) {
			return;
		}

		$cart_rules = wc_bogof_cart_rules()->get_by_cart_item_key( $parent_key );

		foreach ( $cart_rules as $cart_rule ) {
			if ( 'buy_a_get_a' !== $cart_rule->get_rule()->get_type() ) {
				continue;
			}

			// Sync the free items.
			$free_items = WC_BOGOF_Cart::get_free_items( $cart_rule->get_id() );
			foreach ( $free_items as $free_item_key => $free_item ) {
				if ( ! isset( $free_item['composite_children'] ) ) {
					continue;
				}

				foreach ( $free_item['composite_children'] as $children_key ) {
					if ( isset( WC()->cart->cart_contents[ $children_key ]['composite_item'] ) && WC()->cart->cart_contents[ $children_key ]['composite_item'] === $cart_item['composite_item'] ) {
						WC()->cart->remove_cart_item( $children_key );
					}
				}
			}
		}

		$avoid_recursion = true;
	}

	/**
	 * Unset the cart CP cart data when is a free item.
	 *
	 * @param array $cart_item_data Cart item data.
	 */
	public static function add_cart_item_data( $cart_item_data ) {
		if ( WC_BOGOF_Cart::is_free_item( $cart_item_data ) && isset( $cart_item_data['composite_data'] ) && ! wc_bogof_is_choose_your_gift_request() ) {
			unset( $cart_item_data['composite_data'] );
			unset( $cart_item_data['composite_parent'] );
			unset( $cart_item_data['composite_item'] );
		}
		return $cart_item_data;
	}

	/**
	 * Return the sale price for composite container.
	 *
	 * @param float                       $sale_price Discount sale price.
	 * @param WC_BOGOF_Cart_Item_Discount $cart_discount The discount object.
	 */
	public static function cart_item_discount_sale_price( $sale_price, $cart_discount ) {
		if ( $cart_discount->get_extra_data( 'is_composite_container' ) ) {

			$container_price   = floatval( $cart_discount->get_extra_data( 'container_price' ) ) * $cart_discount->get_cart_quantity();
			$child_items_price = floatval( $cart_discount->get_extra_data( 'child_items_price' ) );

			$final_price = $container_price + $child_items_price - $cart_discount->get_discount();
			$sale_price  = ( $final_price - $child_items_price ) / $cart_discount->get_cart_quantity();

		}
		return $sale_price;
	}

	/**
	 * Cart item subtotal. Recalculate the subtotal for Composite containers.
	 *
	 * @param string $cart_subtotal Subtotal to display.
	 * @param array  $cart_item Cart item.
	 */
	public static function cart_item_subtotal( $cart_subtotal, $cart_item ) {
		if ( self::is_container_discount_cart_item( $cart_item ) ) {
			$container_price = wc_bogof_get_cart_product_price( $cart_item['data'], array( 'qty' => $cart_item['quantity'] ) );
			$child_items     = wc_cp_get_composited_cart_items( $cart_item, WC()->cart->cart_contents );

			foreach ( $child_items as $child_item ) {
				$container_price += wc_bogof_get_cart_product_price( $child_item['data'], array( 'qty' => $child_item['quantity'] ) );
			}
			$cart_subtotal = WC_CP()->display->format_subtotal( $cart_item['data'], $container_price );

		} elseif ( self::is_component_free_cart_item( $cart_item ) ) {
			$cart_subtotal = '';
		}
		return $cart_subtotal;
	}

	/**
	 * Cart item price. Display empty price of the components whose parent is a free item..
	 *
	 * @param string $cart_item_price Price to display.
	 * @param array  $cart_item Cart item.
	 */
	public static function cart_item_price( $cart_item_price, $cart_item ) {
		if ( self::is_component_free_cart_item( $cart_item ) ) {
			return '';
		}
		return $cart_item_price;
	}

	/**
	 * Check if the cart item is a container product with a discount.
	 *
	 * @param array $cart_item Cart item.
	 * @return true
	 */
	private static function is_container_discount_cart_item( $cart_item ) {
		return isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) && isset( $cart_item['data']->_bogof_discount ) && $cart_item['data']->_bogof_discount->get_extra_data( 'is_composite_container' );
	}

	/**
	 * Check if the cart item is a component child product and its parent is a free item.
	 *
	 * @param array $cart_item Cart item.
	 * @return true
	 */
	private static function is_component_free_cart_item( $cart_item ) {
		$is_component_free_cart_item = false;
		return isset( $cart_item['composite_parent'] ) &&
			isset( WC()->cart->cart_contents[ $cart_item['composite_parent'] ] ) &&
			self::is_container_discount_cart_item( WC()->cart->cart_contents[ $cart_item['composite_parent'] ] ) &&
			WC_BOGOF_Cart::is_valid_free_item( WC()->cart->cart_contents[ $cart_item['composite_parent'] ] );
	}

	/**
	 * Does the rule applies to the cart item?.
	 *
	 * @param bool          $applies_to True or false.
	 * @param array         $cart_item Cart item.
	 * @param WC_BOGOF_Rule $rule Rule object.
	 * @return bool
	 */
	public static function should_add_cart_rule( $applies_to, $cart_item, $rule ) {
		$is_container_cart_item  = wc_cp_is_composite_container_cart_item( $cart_item );
		$is_composited_cart_item = wc_cp_is_composited_cart_item( $cart_item );

		return $applies_to && (
			! ( $is_container_cart_item || $is_composited_cart_item ) ||
			( 'child' === $rule->get_meta( '_cp_applies_to' ) && $is_composited_cart_item ) ||
			( 'child' !== $rule->get_meta( '_cp_applies_to' ) && $is_container_cart_item )
		);
	}

	/**
	 * Skip bundle contaniner or bundle items on the rule match function.
	 *
	 * @param bool               $skip True or false.
	 * @param WC_BOGOF_Cart_Rule $cart_rule Cart rule object.
	 * @param array              $cart_item Cart item.
	 * @return bool
	 */
	public static function cart_item_match_skip( $skip, $cart_rule, $cart_item ) {

		$is_container_cart_item  = wc_cp_is_composite_container_cart_item( $cart_item );
		$is_composited_cart_item = wc_cp_is_composited_cart_item( $cart_item );
		$is_cp_cart_item         = $is_composited_cart_item || $is_container_cart_item;

		if ( $skip || ! $is_cp_cart_item ) {
			// No need to check.
			return $skip;
		}

		$rule = $cart_rule->get_rule();

		return ( 'child' === $rule->get_meta( '_cp_applies_to' ) && $is_container_cart_item ) ||
			( 'child' !== $rule->get_meta( '_cp_applies_to' ) && $is_composited_cart_item );
	}

	/**
	 * Output the extra fields.
	 *
	 * @param WC_BOGOF_Rule $rule BOGO rule instance.
	 */
	public static function metabox_settings_fields( $rule ) {

		$field = array(
			'id'          => '_cp_applies_to',
			'label'       => __( 'Composite Products compatiblity', 'wc-buy-one-get-one-free' ),
			'description' => __( 'Controls to which element of the composite product the promotion applies', 'wc-buy-one-get-one-free' ),
			'type'        => 'select',
			'value'       => $rule->get_meta( '_cp_applies_to' ),
			'options'     => array(
				''      => __( 'Applies to parent/container product (default)', 'wc-buy-one-get-one-free' ),
				'child' => __( 'Applies to component/children products', 'wc-buy-one-get-one-free' ),
			),
		);
		WC_BOGOF_Admin_Meta_Boxes::output_metabox_field( $field );
	}

	/**
	 * Set metadata before save.
	 *
	 * @param WC_BOGOF_Rule $rule rule object.
	 * @param array         $postdata Data of the _POST array sanitized.
	 */
	public static function admin_process_rule_object( $rule, $postdata ) {
		$applies_to = empty( $postdata['_cp_applies_to'] ) ? '' : $postdata['_cp_applies_to'];
		$rule->update_meta_data( '_cp_applies_to', $applies_to );
	}
}
