<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.javmah.tk
 * @since             5.1.0
 * @package           Woo_Customers_Order_History
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Customers Order History
 * Plugin URI:        https://wordpress.org/plugins/woo-customers-order-history
 * Description:       The most intuitive way to see a user's order history. Nothing will skip your eye.
 * Version:           5.2.1
 * Author:            javmah
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-customers-order-history
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * Including  freemius for monetization and  licencing 
 * @since    5.1.0
 * freemius SDK Version Changed in 2.4.1
 */
# freemius

if ( function_exists( 'freemius_wcoh' ) ) {
    freemius_wcoh()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'freemius_wcoh' ) ) {
        // Create a helper function for easy SDK access.
        function freemius_wcoh()
        {
            global  $freemius_wcoh ;
            
            if ( !isset( $freemius_wcoh ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                $freemius_wcoh = fs_dynamic_init( array(
                    'id'             => '3819',
                    'slug'           => 'woo-customers-order-history',
                    'premium_slug'   => 'woo-customers-order-history-professional',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_34a75ace5ae6bad92b244358c2417',
                    'is_premium'     => false,
                    'premium_suffix' => 'Professional',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'woo-customers-order-history',
                    'support' => false,
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $freemius_wcoh;
        }
        
        // Init Freemius.
        freemius_wcoh();
        // Signal that SDK was initiated.
        do_action( 'freemius_wcoh_loaded' );
    }
    
    // ...  plugin's main file logic ...
    /**
     * Currently plugin version.
     * Start at version 5.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'WOO_CUSTOMERS_ORDER_HISTORY_VERSION', '5.2.1' );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-woo-customers-order-history-activator.php
     */
    function activate_woo_customers_order_history()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-customers-order-history-activator.php';
        Woo_Customers_Order_History_Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-woo-customers-order-history-deactivator.php
     */
    function deactivate_woo_customers_order_history()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-customers-order-history-deactivator.php';
        Woo_Customers_Order_History_Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'activate_woo_customers_order_history' );
    register_deactivation_hook( __FILE__, 'deactivate_woo_customers_order_history' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-woo-customers-order-history.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    5.0.0
     */
    function run_woo_customers_order_history()
    {
        $plugin = new Woo_Customers_Order_History();
        $plugin->run();
    }
    
    /**
     * if WooCommerce is Installed Plugin Will run || if not holt execution;
     * @since    5.0.0
     */
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        run_woo_customers_order_history();
    }
}
