<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://revenuehunt.com/
 * @since      1.0.0
 *
 * @package    Product_Recommendation_Quiz_For_Woocommerce
 * @subpackage Product_Recommendation_Quiz_For_Woocommerce/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Product_Recommendation_Quiz_For_Woocommerce
 * @subpackage Product_Recommendation_Quiz_For_Woocommerce/includes
 */
class Product_Recommendation_Quiz_For_Woocommerce_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
				'product-recommendation-quiz-for-woocommerce', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}

}
