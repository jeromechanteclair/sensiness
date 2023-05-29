<?php 
namespace sensiness\app;

class Woocommerce  {


	/**
	 * Instance of \WC_Points_Rewards_Product
	 *
	 * @var [type]
	 */
	private $rewards;


	/**
	 * Undocumented function
	 */
	public function __construct(){
		$this->rewards  =new \WC_Points_Rewards_Product();
		add_action( 'init',	array($this,'cleanup'));
		add_action('dk_after_price', array($this->rewards,'add_variation_message_to_product_summary'), 35);
		add_action('dk_after_price', array($this->rewards,'render_product_message'), 20);

	}

	/**
	 * Clean up product from unwanted action & filters 
	 *
	 * @return void
	 */
	public function cleanup(){
	
		$this->remove_filters_with_method_and_class_name('woocommerce_before_add_to_cart_button', 'WC_Points_Rewards_Product', 'add_variation_message_to_product_summary', 25);
		$this->remove_filters_with_method_and_class_name('woocommerce_before_add_to_cart_button', 'WC_Points_Rewards_Product', 'render_product_message', 15);
	}

	/**
	 *  Clean existing actions & filters
	 *
	 * @param [type] $hook_name
	 * @param [type] $class_name
	 * @param [type] $method_name
	 * @param integer $priority
	 * @return void
	 */
	public function remove_filters_with_method_and_class_name( $hook_name, $class_name,$method_name, $priority = 0 ) {
		global $wp_filter;
	
		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}
		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function']) ) {
				// Test if object is a class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] )
					&& get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}
		}
		return false;
	}

}

new Woocommerce();