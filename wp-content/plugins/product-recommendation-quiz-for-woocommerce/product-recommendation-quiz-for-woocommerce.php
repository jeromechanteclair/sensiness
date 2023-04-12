<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://revenuehunt.com/
 * @since             1.0.0
 * @package           Product_Recommendation_Quiz_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Product Recommendation Quiz for WooCommerce
 * Plugin URI:        https://revenuehunt.com/product-recommendation-quiz-woocommerce/
 * Description:       Advise and delight your customers by engaging them with a personal shopper experience on your store, guiding your customers from start to cart and helping them find the products that best match their needs.
 * Version:           2.0.20
 * Author:            RevenueHunt
 * Author URI:        https://revenuehunt.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-recommendation-quiz-for-woocommerce
 * Domain Path:       /languages
 * Woo: 6046806:70a0dc05e97a64b20be052f171b299ce
 * WC requires at least: 3.5
 * WC tested up to: 7.5.1
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PRQ_PLUGIN_VERSION', '2.0.20');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-product-recommendation-quiz-for-woocommerce-activator.php
 */
function product_recommendation_quiz_for_woocommerce_activate() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-product-recommendation-quiz-for-woocommerce-activator.php';
	Product_Recommendation_Quiz_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-product-recommendation-quiz-for-woocommerce-deactivator.php
 */
function product_recommendation_quiz_for_woocommerce_deactivate() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-product-recommendation-quiz-for-woocommerce-deactivator.php';
	Product_Recommendation_Quiz_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'product_recommendation_quiz_for_woocommerce_activate');
register_deactivation_hook(__FILE__, 'product_recommendation_quiz_for_woocommerce_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-product-recommendation-quiz-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function product_recommendation_quiz_for_woocommerce_run() {

	$plugin = new Product_Recommendation_Quiz_For_Woocommerce();
	$plugin->run();
}

function prq_set_token() {
	$post = $_REQUEST;
	
	if (!$post) {
		return 'die';
	}
	
	$shop_hashid	= get_option('rh_shop_hashid');
	$api_key		= get_option('rh_api_key');
		
	if ( !$shop_hashid && $post['shop_hashid'] ) {
	  update_option('rh_shop_hashid', $post['shop_hashid'], false);
	}
	
	if ( !$api_key && $post['api_key'] ) {
	  update_option('rh_api_key', $post['api_key'], false);		
	}

	return get_option('rh_shop_hashid');
}

function prq_deactivate_plugin() {
	update_option('rh_shop_hashid', false, false);
	update_option('rh_api_key', false, false);
	update_option('rh_domain', false, false);
	update_option('rh_token', false, false);

	$GLOBALS['wp_object_cache']->delete( 'rh_shop_hashid', 'options' );
	$GLOBALS['wp_object_cache']->delete( 'rh_api_key', 'options' );
	$GLOBALS['wp_object_cache']->delete( 'rh_domain', 'options' );
	$GLOBALS['wp_object_cache']->delete( 'rh_token', 'options' );
}

add_action('rest_api_init', function() {
	register_rest_route('prq/v1', 'settoken', array(
		'methods' => 'POST',
		'callback' => 'prq_set_token',
		'permission_callback' => '__return_true',
	));
});

register_deactivation_hook( __FILE__, 'prq_deactivate_plugin' );
register_uninstall_hook(    __FILE__, 'prq_deactivate_plugin' );

product_recommendation_quiz_for_woocommerce_run();
