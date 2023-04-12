<?php

/**
 * Fired during plugin activation
 *
 * @link       www.javmah.tk
 * @since      1.0.0
 *
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Woo_Customers_Order_History_Activator {

	/**
	 * Short Description. (use period)
	 * Long Description.
	 * @since    1.0.0
	*/
	public static function activate() {
		# Stop Duala Installation or aka Error Handler || Hold This Part we will Use Frm
		$active_plugins = get_option( 'active_plugins');

		# Removing Old Premium version to Prevent Bug 
		// if ( in_array('woo-customers-order-history-premium/woo-customers-order-history.php' , $active_plugins )) {
		// 	die('<h3> Please uninstall & remove the Premium version of this plugin before installing the New Professional version !</h3>');
		// }

		// if ( in_array('woo-customers-order-history/woo-customers-order-history.php' , $active_plugins )) {
		// 	die('<h3> Please uninstall & remove the old version of this plugin before installing the New version !</h3>');
		// }

		# Setting the Instal time 
		if ( !get_option( "wcchpo_install_date" ) ) {
		    add_option( "wcchpo_install_date", time() );
		}

		# Default Order page cash should enabled 
		update_option( "wcchpo_orderCacheStatus", TRUE );
	}
}
