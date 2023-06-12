<?php
/**
 * Plugin Name: WooCommerce Buy One Get One Free
 * Plugin URI: https://woocommerce.com/products/buy-one-get-one-free/
 * Description: Create Buy One, Get One Free deals in your WooCommerce store.
 * Version: 3.9.2
 * Author: Oscar Gare
 * Author URI: https://oscargare.com/
 * Developer: Oscar Gare
 * Developer URI: https://oscargare.com/
 * Text Domain: wc-buy-one-get-one-free
 * Domain Path: /languages/
 *
 * Requires at least: 4.4
 * Tested up to: 6.2
 *
 * WC requires at least: 3.4
 * WC tested up to: 7.6
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Woo: 3820067:2d9a80cf8b3bb593e10c71687f630725
 *
 * @package WC_BOGOF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define WC_BOGOF_PLUGIN_FILE.
if ( ! defined( 'WC_BOGOF_PLUGIN_FILE' ) ) {
	define( 'WC_BOGOF_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'WC_Buy_One_Get_One_Free' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-buy-one-get-one-free.php';
	WC_Buy_One_Get_One_Free::init();
}
