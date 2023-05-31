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
		add_action( 'init',	array($this,'cleanup'));
		add_action('woocommerce_variation_options_pricing', array($this,'variation_fields'), 10, 3);
		add_action('woocommerce_save_product_variation',  array($this,'save_variation_fields'), 10, 2);
		add_action('woocommerce_product_options_general_product_data', array( $this,'product_fields'),10);
		add_action('woocommerce_process_product_meta', array( $this,'save_product_fields'), 10, 2);
		add_action('woocommerce_single_product_summary', array( $this,'display_subtitle'), 7);
		remove_action('woocommerce_single_product_summary', 'ntav_netreviews_product_rating', 31);

		add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
		add_action('woocommerce_after_single_product_summary', array( $this,'display_reassurance'), 10);

	}
	public function display_reassurance(){
		wc_get_template('single-product/reassurance-slider.php');
	}


	public function display_subtitle(){
		$subtitle = get_post_meta(get_the_ID(), 'subtitle', true);
		if(!empty($subtitle)){
			echo'<p class="subtitle">'.$subtitle.'</p>';
		}
	}
	public function product_fields(){
		
		echo '<div class="options_group">';
		woocommerce_wp_text_input(array(
			'id'      => 'subtitle',
			'value'   => get_post_meta(get_the_ID(), 'subtitle', true),
			'label'   => 'Sous titre',
			'desc_tip' => true,
			'type' => 'text',
			'description' => 'Sous titre du produit',
		));


		echo '</div>';

	}
	public function save_product_fields($id, $post)	{
		if (!empty($_POST['subtitle'])) {
			update_post_meta($id, 'subtitle', $_POST['subtitle']);
		} else {
			delete_post_meta($id, 'subtitle');
		}

	}
	public function variation_fields($loop, $variation_data, $variation){

			woocommerce_wp_text_input(
				array(
						'id'            => 'variation_description[' . $loop . ']',
						'label'         => 'Description de la variation',
						'wrapper_class' => 'form-row',
						'placeholder'   => '',
						'desc_tip'      => true,
						'description'   => 'Affiche une indication sous la variation',
						'value'         => get_post_meta($variation->ID, 'variation_description', true)
					)
			);

	}

	public function save_variation_fields($variation_id, $loop){

		// Text Field
		$text_field = ! empty($_POST[ 'variation_description' ][ $loop ]) ? $_POST[ 'variation_description' ][ $loop ] : '';
		update_post_meta($variation_id, 'variation_description', sanitize_text_field($text_field));

	}

	/**
	 * Clean up product from unwanted action & filters 
	 *
	 * @return void
	 */
	public function cleanup(){
	
		$this->remove_filters_with_method_and_class_name('woocommerce_before_add_to_cart_button', 'WC_Points_Rewards_Product', 'add_variation_message_to_product_summary', 25);
		$this->remove_filters_with_method_and_class_name('woocommerce_before_add_to_cart_button', 'WC_Points_Rewards_Product', 'render_product_message', 15);
		remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt',20);
		remove_action('woocommerce_before_main_content','woocommerce_breadcrumb',20);
		remove_action('woocommerce_before_single_product_summary','woocommerce_show_product_sale_flash',10);
		remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		add_action('woocommerce_single_product_summary','woocommerce_breadcrumb',0);
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