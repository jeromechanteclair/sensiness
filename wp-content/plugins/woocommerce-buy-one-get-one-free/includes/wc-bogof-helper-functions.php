<?php
/**
 * Buy One Get One Free Helper Functions
 *
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main function for returning WC_BOGOF_Rule.
 *
 * This function should only be called after 'init' action is finished, as there might be post types that are getting
 * registered during the init action.
 *
 * @since 2.1.5
 *
 * @param mixed $rule_id Rule ID of the BOGOF rule.
 * @return WC_BOGOF_Rule|false
 */
function wc_bogof_get_rule( $rule_id ) {
	if ( ! did_action( 'woocommerce_init' ) || ! did_action( 'wc_bogof_after_register_post_type' ) ) {
		/* translators: 1: wc_bogof_get_rule 2: woocommerce_init 3: wc_bogof_after_register_post_type 4: woocommerce_after_register_post_type */
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called before the %2$s, %3$s and %4$s action have finished.', 'wc-buy-one-get-one-free' ), 'wc_bogof_get_rule', 'woocommerce_init', 'wc_bogof_after_register_post_type' ), '2.1.5' );
		return false;
	}

	$rule_id = absint( $rule_id );
	try {
		return new WC_BOGOF_Rule( $rule_id );
	} catch ( Exception $e ) {
		return false;
	}
}


/**
 * Main function for returning WC_BOGOF_Cart_Rules.
 *
 * This function should only be called after 'woocommerce_cart_loaded_from_session' action is finished.
 *
 * @since 2.2.0
 *
 * @return WC_BOGOF_Cart_Rules|false
 */
function wc_bogof_cart_rules() {
	if ( ! did_action( 'woocommerce_cart_loaded_from_session' ) ) {
		/* translators: 1: wc_bogof_cart_rules 2: woocommerce_cart_loaded_from_session */
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called before the %2$s action have finished.', 'wc-buy-one-get-one-free' ), 'wc_bogof_cart_rules', 'woocommerce_init', 'wc_bogof_after_register_post_type' ), '2.2.0' );
		return false;
	}

	return WC_BOGOF_Cart::$cart_rules;
}

/**
 * Is choose your gift request?
 * Only the products added to the cart from the choose your gift page should be added as a free item.
 *
 * @since 2.2.0
 * @see WC_BOGOF_Cart::add_cart_item_data
 * @return bool
 */
function wc_bogof_is_choose_your_gift_request() {
	return apply_filters(
		'wc_bogof_is_choose_your_gift_request',
		false !== WC_BOGOF_Choose_Gift::get_refer() || isset( $_REQUEST['wc_bogof_data'] ) // phpcs:ignore WordPress.Security.NonceVerification
	);
}

/**
 * Duplicate a BOGOF rule.
 *
 * @param int $post_id BOGOF Rule ID.
 * @return WC_BOGOF_Rule
 */
function wc_bogof_duplicate_rule( $post_id ) {
	$rule    = wc_bogof_get_rule( $post_id );
	$newrule = false;
	if ( $rule ) {
		$newrule = new WC_BOGOF_Rule();
		$data    = $rule->get_data();
		unset( $data['id'], $data['enabled'], $data['title'] );

		$newrule->set_props( $data );

		$newrule->set_enabled( false );
		$newrule->set_title( $rule->get_title() . ' (' . __( 'copy', 'wc-buy-one-get-one-free' ) . ')' );
		$newrule->save();

		do_action( 'wc_bogof_after_duplicate_rule', $newrule->get_id() );
	}
	return $newrule;
}

/*
|--------------------------------------------------------------------------
| Utils Functions
|--------------------------------------------------------------------------
*/

/**
 * Checks if the array insterset of two arrays is no empty.
 *
 * @param array $array1 The array with master values to check.
 * @param array $array2 An array to compare values against.
 * @return bool
 */
function wc_bogof_in_array_intersect( $array1, $array2 ) {
	$intersect = array_intersect( $array1, $array2 );
	return ! empty( $intersect );
}

/**
 * Return product categories IDs.
 *
 * @param int $product_id Product ID.
 * @return array
 */
function wc_bogof_get_product_cats( $product_id ) {
	$cache_key    = WC_Cache_Helper::get_cache_prefix( 'product_' . $product_id ) . '_bogof_product_cat_' . $product_id;
	$product_cats = wp_cache_get( $cache_key, 'products' );
	if ( ! is_array( $product_cats ) ) {
		$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		wp_cache_set( $cache_key, $product_cats, 'products' );
	}
	return $product_cats;
}

/**
 * Check if the product belong to category.
 *
 * @param int   $product_id Product ID.
 * @param array $term_ids Array of product categories IDs to check.
 * @return bool.
 */
function wc_bogof_product_in_category( $product_id, $term_ids ) {
	$in_category = in_array( 'all', $term_ids, true );
	if ( ! $in_category ) {
		$product_cats = wc_bogof_get_product_cats( $product_id );
		$in_category  = wc_bogof_in_array_intersect( $term_ids, $product_cats );
	}
	return $in_category;
}

/**
 * Check if the current user has one or more roles.
 *
 * @param array $roles Array roles to check.
 * @return bool.
 */
function wc_bogof_current_user_has_role( $roles ) {
	$has_role   = false;
	$user       = wp_get_current_user();
	$user_roles = empty( $user->ID ) ? array( 'not-logged-in' ) : array_merge( $user->roles, array( 'logged-in' ) );
	$has_role   = wc_bogof_in_array_intersect( $user_roles, $roles );

	return $has_role;
}

/**
 * Check if the page content has the wc_choose_your_gift shortcode.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function wc_bogof_has_choose_your_gift_shortcode( $post_id ) {
	$post          = get_post( $post_id );
	$has_shortcode = false;

	if ( $post && isset( $post->post_content ) ) {
		$has_shortcode = has_shortcode( $post->post_content, 'wc_choose_your_gift' );
	}
	return $has_shortcode;
}

/**
 * Returns an array of variable product types.
 *
 * @return array
 */
function wc_bogof_variable_types() {
	return array( 'variable', 'variable-subscription' );
}

/**
 * Returns incompatible product types.
 *
 * @return array
 */
function wc_bogof_incompatible_product_types() {
	return array( 'mix-and-match', 'composite' );
}

/**
 * Returns an array with the WC Customer emails and user ID.
 *
 * @return array
 */
function wc_bogof_user_ids() {
	$ids = array();
	if ( WC()->customer ) {
		foreach ( array( 'get_email', 'get_billing_email', 'get_id' ) as $getter ) {
			if ( is_callable( array( WC()->customer, $getter ) ) ) {
				$ids[] = WC()->customer->{$getter}();
			}
		}
	}

	return array_unique( array_filter( array_map( 'strtolower', $ids ) ) );
}

/**
 * Get the product row price per item.
 *
 * @param WC_Product $product Product object.
 * @param array      $args Optional arguments to pass product quantity and price.
 * @return float
 */
function wc_bogof_get_cart_product_price( $product, $args = array() ) {
	if ( WC()->cart->display_prices_including_tax() ) {
		$product_price = wc_get_price_including_tax( $product, $args );
	} else {
		$product_price = wc_get_price_excluding_tax( $product, $args );
	}
	return ( $product_price ? $product_price : 0 );
}

/**
 * Returns the rules that have a coupon.
 *
 * @param WC_Coupon $coupon Coupon object.
 * @return bool
 */
function wc_bogof_get_coupon_rule_ids( $coupon ) {

	$cache_key    = 'wc_bogof_coupon_rules_' . $coupon->get_id() . WC_Cache_Helper::get_transient_version( 'bogof_rules' );
	$coupon_rules = wp_cache_get( $cache_key, 'wc_bogof' );

	if ( ! is_array( $coupon_rules ) ) {

		$data_store   = WC_Data_Store::load( 'bogof-rule' );
		$rules        = $data_store->get_rules();
		$coupon_rules = array();

		foreach ( $rules as $rule ) {
			if ( in_array( $coupon->get_id(), $rule->get_coupon_ids() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
				$coupon_rules[] = $rule->get_id();
			}
		}

		wp_cache_set( $cache_key, $coupon_rules, 'wc_bogof' );
	}

	return $coupon_rules;
}

/*
|--------------------------------------------------------------------------
| Transients Functions
|--------------------------------------------------------------------------
*/

/**
 * Delete product transients.
 *
 * @param int $post_id (default: 0) The product ID.
 */
function wc_bogof_delete_product_transients( $post_id ) {
	if ( $post_id ) {
		delete_transient( 'wc_bogof_rules_' . $post_id );
	}
}
add_action( 'woocommerce_delete_product_transients', 'wc_bogof_delete_product_transients' );

/**
 * Delete used by transient on order status change.
 *
 * @param int $post_id The product ID.
 */
function wc_bogof_delete_used_by_transient( $post_id ) {
	$rule_ids = get_post_meta( $post_id, '_wc_bogof_rule_id' );

	if ( is_array( $rule_ids ) && ! empty( $rule_ids ) ) {
		foreach ( array_unique( $rule_ids ) as $rule_id ) {
			delete_transient( 'wc_bogof_uses_' . $rule_id );
		}
	}
}
add_action( 'woocommerce_order_status_changed', 'wc_bogof_delete_used_by_transient' );

/**
 * Run after clear transients action of the WooCommerce System Status Tool.
 *
 * @param array $tool Details about the tool that has been executed.
 */
function wc_bogof_clear_transients( $tool ) {
	global $wpdb;

	$id = isset( $tool['id'] ) ? $tool['id'] : false;
	if ( 'clear_transients' === $id && ! empty( $tool['success'] ) ) {

		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
				'_transient_timeout_wc_bogof_uses_%',
				'_transient_wc_bogof_uses_%',
				'_transient_timeout_wc_bogof_cyg_%',
				'_transient_wc_bogof_cyg_%'
			)
		);
	}
}
add_action( 'woocommerce_system_status_tool_executed', 'wc_bogof_clear_transients' );
add_action( 'woocommerce_rest_insert_system_status_tool', 'wc_bogof_clear_transients' );

/**
 * Delete transients on BOGO rule delete.
 *
 * @param mixed $id ID of post being deleted.
 */
function wc_bogof_delete_post( $id ) {
	if ( ! $id ) {
		return;
	}

	$post_type = get_post_type( $id );

	if ( 'shop_bogof_rule' === $post_type ) {
		WC_Cache_Helper::get_transient_version( 'bogof_rules', true );
	}
}
add_action( 'delete_post', 'wc_bogof_delete_post' );
add_action( 'wp_trash_post', 'wc_bogof_delete_post' );
add_action( 'untrashed_post', 'wc_bogof_delete_post' );

/*
|--------------------------------------------------------------------------
| Compatibility Functions
|--------------------------------------------------------------------------
*/

/**
 * Skips cart item to not count to the rule.
 *
 * @param WC_BOGOF_Rule $cart_rule Cart rule.
 * @param array         $cart_item Cart item.
 * @return bool
 */
function wc_bogof_cart_item_match_skip( $cart_rule, $cart_item ) {
	// Skip bundle child items.
	return apply_filters(
		'wc_bogof_cart_item_match_skip',
		( class_exists( 'WC_Product_Woosb' ) && ! empty( $cart_item['woosb_parent_id'] ) ),
		$cart_rule,
		$cart_item
	);
}

/*
|--------------------------------------------------------------------------
| Meta Box Functions
|--------------------------------------------------------------------------
*/

/**
 * Returns the display key for the _wc_bogof_rule_id meta data.
 *
 * @param string $display_key Display key.
 * @param object $meta Meta data.
 */
function wc_bogof_order_item_display_meta_key( $display_key, $meta ) {
	if ( '_wc_bogof_rule_id' === $meta->key ) {
		$display_key = __( 'BOGO Promotion', 'wc-buy-one-get-one-free' );
	}
	return $display_key;
}
add_filter( 'woocommerce_order_item_display_meta_key', 'wc_bogof_order_item_display_meta_key', 10, 2 );

/**
 * Returns the display value for the _wc_bogof_rule_id meta data.
 *
 * @param string                $display_value Display value.
 * @param object                $meta Meta data.
 * @param WC_Order_Item_Product $item Order Item.
 */
function wc_bogof_order_item_display_meta_value( $display_value, $meta, $item ) {
	if ( '_wc_bogof_rule_id' === $meta->key ) {

		$title = is_callable( array( $item, 'get_meta' ) ) ? $item->get_meta( '_wc_bogof_rule_name' ) : false;
		$title = is_array( $title ) && isset( $title[ $meta->value ] ) ? $title[ $meta->value ] : $title;
		$rule  = wc_bogof_get_rule( $meta->value );

		$title = ! $title && $rule ? $rule->get_title() : $title;

		if ( $rule ) {
			$display_value = sprintf( '<a href="%s">%s</a>', admin_url( '/post.php?action=edit&post=' . $rule->get_id() ), $title );
		} elseif ( $title ) {
			$display_value = $title;
		}
	}
	return $display_value;
}
add_filter( 'woocommerce_order_item_display_meta_value', 'wc_bogof_order_item_display_meta_value', 10, 3 );

/**
 * Hide the _wc_bogof_rule_name order item meta from the Order items metabox
 *
 * @param array $hidden_order_itemmeta Hidden order ite mmetas.
 */
function wc_bogof_hidden_order_itemmeta( $hidden_order_itemmeta ) {
	$hidden_order_itemmeta   = is_array( $hidden_order_itemmeta ) ? $hidden_order_itemmeta : array();
	$hidden_order_itemmeta[] = '_wc_bogof_rule_name';
	$hidden_order_itemmeta[] = '_wc_bogof_free_item';
	return $hidden_order_itemmeta;
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'wc_bogof_hidden_order_itemmeta' );

/**
 * Returns an array with the BOGO rule type in type desc pair.
 *
 * @return array
 */
function wc_bogof_rule_type_options() {
	return array(
		'cheapest_free' => __( 'Get the cheapest product for free (Special offer)', 'wc-buy-one-get-one-free' ),
		'buy_a_get_b'   => __( 'Product as a gift', 'wc-buy-one-get-one-free' ),
		'buy_a_get_a'   => __( 'Get the same product you buy for free', 'wc-buy-one-get-one-free' ),
	);
}
