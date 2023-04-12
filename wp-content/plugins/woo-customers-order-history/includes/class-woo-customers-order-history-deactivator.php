<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Woo_Customers_Order_History_Deactivator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	# Removing Saved Data After Deactivation
	public static function deactivate() {
		$user_ids = get_users( array(
		    'blog_id' => '',
		    'fields'  => 'ID',
		));

		foreach ( $user_ids as $user_id ){
		    delete_user_meta( $user_id, 'wc_total_number_of_orders' );
		}

		# If Woocommerce is Inactive It Will give an error so i handale it .
		if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			# Delete Colour Options
			$order_statuses = wc_get_order_statuses();
			foreach ( $order_statuses as $key => $value ) {
			    # echo "wcchpo_colour_".substr($key, 3) ."<br>" ;
			    if ( get_option( "wcchpo_colour_" . substr( $key, 3 ) ) ) {
			        delete_option( "wcchpo_colour_" . substr( $key, 3 ) );
			    }
			}
		}

		/* Goodbye! Thank you for your Patient , please feel free to contact me  .! */
	}
}
