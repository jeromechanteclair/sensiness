<?php

/**
 * @package probance-optin 
 */

/**
* Plugin Name: Probance-optin
* Plugin URI: https://www.probance.com/
* Description: Plugin displaying a checkbox and a Newsletter banner to manage the optin on Probance side 
* Version: 1.0
* Author: Probance
* Author URI: https://www.probance.com/
**/


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
Copyright 2005-2015 Automattic, Inc.
*/

defined( 'ABSPATH' ) or die( 'Access denied.' );

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_SETTINGS_GROUP', 'probance_admin_optin_settings' );
define('NEWSLETTER_ATTR', array('name' => 'probance_newsletter', 'shortcode' => 'probance_newsletter'));
define( 'PROB_DEBUG', get_option('probance-optin_api-cbdebug'));

if ( class_exists( 'Inc\\Init' ) ) {
	Inc\Init::register_services();
} else 
{
	die('Init class is missing.');
}

?>