<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.javmah.tk
 * @since      1.0.0
 *
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Woo_Customers_Order_History_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-customers-order-history',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
