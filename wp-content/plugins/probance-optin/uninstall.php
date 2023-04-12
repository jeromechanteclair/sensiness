<?php 

/**
 * @package probance-optin 
 */

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}


if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_SETTINGS_GROUP', 'probance_admin_optin_settings' );
define('NEWSLETTER_ATTR', array('name' => 'probance_newsletter', 'shortcode' => 'probance_newsletter'));
define( 'PROB_DEBUG', get_option('probance-optin_api-cbdebug'));

if ( class_exists( 'Inc\\Uninstall' ) ) {
	Inc\Uninstall::delete_options();
} else 
{
	die('Uninstall class is missing.');
}

?>