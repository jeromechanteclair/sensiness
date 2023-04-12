<?php
/**
 * Condition Product class.
 *
 * @since 3.0.0
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_BOGOF_Condition_Product Class
 */
class WC_BOGOF_Condition_Product extends WC_BOGOF_Abstract_Condition {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'product';
		$this->title = __( 'Product', 'wc-buy-one-get-one-free' );
	}

	/**
	 * For product variations, check also if parent meet the condition.
	 *
	 * @return bool
	 */
	protected function check_parent() {
		return true;
	}

	/**
	 * Checks if a value exists in an array.
	 *
	 * @param int   $product_id Product ID to check.
	 * @param array $haystack The array.
	 * @return bool
	 */
	protected function in_array( $product_id, $haystack ) {
		return in_array( $product_id, $haystack ); // phpcs:ignore WordPress.PHP.StrictInArray
	}

	/**
	 * Check if the product is in the array, is not check the parent.
	 *
	 * @param int   $product_id Product ID to check.
	 * @param array $haystack The array.
	 */
	protected function product_in( $product_id, $haystack ) {
		$product_in = $this->in_array( $product_id, $haystack );
		if ( ! $product_in && $this->check_parent() && 'product_variation' === get_post_type( $product_id ) ) {
			$product_in = $this->product_in( wp_get_post_parent_id( $product_id ), $haystack );
		}
		return $product_in;
	}

	/**
	 * Evaluate if a cart item meets the condition.
	 *
	 * @param array $cart_item Cart item to check.
	 * @param array $data Condition field data.
	 * @return boolean
	 */
	protected function check_cart_item( $cart_item, $data ) {
		$is_matching = false;
		if ( isset( $cart_item['data'] ) && is_callable( array( $cart_item['data'], 'get_id' ) ) ) {
			$is_matching = $this->product_in( $cart_item['data']->get_id(), $data );
		}
		return $is_matching;
	}

	/**
	 * Evaluate a condition field.
	 *
	 * @param array $data   Condition field data.
	 * @param mixed $value  Value to check.
	 * @return boolean
	 */
	public function check_condition( $data, $value = null ) {
		// Empty conditions always return false (not evaluated).
		if ( empty( $data['value'] ) || ! is_array( $data['value'] ) ) {
			return false;
		}

		if ( is_numeric( $value ) ) {
			$is_matching = $this->product_in( $value, $data['value'] );
		} else {
			$is_matching = $this->check_cart_item( $value, $data['value'] );
		}

		if ( $this->modifier_is( $data, 'not-in' ) ) {
			$is_matching = ! $is_matching;
		}

		return $is_matching;
	}

	/**
	 * Return the WHERE clause that returns the products that meet the condition.
	 *
	 * @param array $data Condition field data.
	 * @return string
	 */
	public function get_where_clause( $data ) {
		global $wpdb;
		// Empty conditions always return ''.
		if ( empty( $data['value'] ) || ! is_array( $data['value'] ) ) {
			return false;
		}
		$product_ids = array_map( 'absint', $data['value'] );
		$parents     = array();
		foreach ( $product_ids as $product_id ) {
			if ( 'product_variation' === get_post_type( $product_id ) ) {
				$parents[] = wp_get_post_parent_id( $product_id );
			}
		}
		$product_ids = array_merge( $product_ids, $parents );
		$operator    = $this->modifier_is( $data, 'not-in' ) ? 'NOT IN' : 'IN';

		return $wpdb->posts . '.ID ' . $operator . ' (' . implode( ',', $product_ids ) . ')';
	}

	/**
	 * Return the condition as string.
	 *
	 * @param array $data Condition field data.
	 * @return string
	 */
	public function to_string( $data ) {
		if ( empty( $data['value'] ) || ! is_array( $data['value'] ) ) {
			return '';
		}

		$formated_values = $this->get_formatted_values( $data['value'] );
		$modifier        = $this->modifier_is( $data, 'not-in' ) ? 'not-in' : 'in';
		$modifiers       = $this->get_modifiers();

		return $this->title . ' ' . $modifiers[ $modifier ] . ' ( ' . implode( ' | ', $formated_values ) . ' )';
	}

	/**
	 * Get formatted values.
	 *
	 * @param array $values Values to formatted.
	 * @return array
	 */
	protected function get_formatted_values( $values ) {
		$product_ids   = array_map( 'absint', $values );
		$product_names = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product_names[] = wp_strip_all_tags( $product->get_formatted_name() );
			}
		}
		return $product_names;
	}

	/**
	 * Returns a key => title array of modifiers.
	 *
	 * @return array
	 */
	public function get_modifiers() {
		return array(
			'in'     => __( 'In list', 'wc-buy-one-get-one-free' ),
			'not-in' => __( 'Not in list', 'wc-buy-one-get-one-free' ),
		);
	}

	/**
	 * Returns an array with the proprerties of the metabox field.
	 *
	 * @return array
	 */
	public function get_value_metabox_field() {
		return array(
			'type' => 'search-product',
		);
	}
}
