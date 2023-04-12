<?php 

/**
 * @package probance-optin
 */

namespace Inc\Pages;

use Inc\Base\SettingsField;
/*
* 
*/
class Admin 
{
    public $settings_fields;

    public function __construct() 
    {
        $this->settings_fields = new SettingsField();
    }

    public function register()
    {
        add_action('admin_menu', array($this, 'add_admin_pages') );
        add_action('admin_init', array($this->settings_fields, 'add_settings_fields'));
        // add_action('admin_init', SettingsField::add_settings_fields());
    }

    public function add_admin_pages() 
    {

        global $menu;
        $menuExist = false;
        foreach($menu as $item) {
            if(strtolower($item[0]) == strtolower('Probance')) {
                $menuExist = true;
            }
        }
        if(!$menuExist){
            $slug='probance';
            //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
            add_menu_page('Probance', 'Probance', 'administrator', 'probance', array( $this, 'admin_index'), PLUGIN_URL . 'src/images/logo-icon.png', 86 );

            if ( is_plugin_active( 'probance-optin/probance-optin.php' ) ) {
                //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
                add_submenu_page('probance', 'Optin', 'Optin', 'administrator', $slug, array( $this, 'admin_index_optin'));
            } 
        } 
        else
        {
            if ( is_plugin_active( 'probance-optin/probance-optin.php' ) ) {
                $slug='probance-optin';
                //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
                add_submenu_page('probance', 'Optin', 'Optin', 'administrator', $slug, array( $this, 'admin_index_optin'));
            } 
        }
    } 

    public function admin_index_track() 
    {
        // require template
        require_once PLUGIN_PATH . 'templates/admin_track.php';
    }

    public function admin_index_optin() 
    {
        // require template
        require_once PLUGIN_PATH . 'templates/admin_optin.php';

    }
    
    public function admin_index() 
    {
        // require template
        require_once PLUGIN_PATH . 'templates/admin.php';
    }

}

?>