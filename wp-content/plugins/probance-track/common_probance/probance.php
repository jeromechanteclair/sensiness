<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//Check de l'existence de la fonction write_log, sinon création
if (!function_exists('write_log')) {

    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}


/*
 * Gestion des menus
 */

if (!function_exists('addProbanceAdminMenus')) {
    function addProbanceAdminMenus() {
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
            add_menu_page('Probance', 'Probance', 'administrator', 'probance', 'displayProbanceAdminDashboard', plugin_dir_url( __FILE__ ) . 'img/logo-icon.png', 86 );
            
            if ( is_plugin_active( 'probance-track/probance-track.php' ) ) {
                add_submenu_page('probance', 'Tracking', 'Tracking', 'administrator', $slug, 'displayTrackingSettings');
            } 
        } else {
            $slug='probance-track';
            if ( is_plugin_active( 'probance-track/probance-track.php' ) ) {
                add_submenu_page('probance', 'Tracking', 'Tracking', 'administrator', $slug, 'displayTrackingSettings');
            } 
        }
    }
}

//Pas utilisé, sert si on veut une page de garde probance
if (!function_exists('displayProbanceAdminDashboard')) {
    function displayProbanceAdminDashboard() {
        require_once 'dashboard-admin-home.php';
    }
}

//On créé les menus (de tous les plugins probance) une seule fois (chaque pluggin check les autres plugins activés et créé les menus pour tout le monde)
add_action('admin_menu','addProbanceAdminMenus', 9); 


/*
 * Affichage des champs
 */
if (!function_exists('probance_display_field')) {
    function probance_display_field($args) {
        /* EXAMPLE INPUT
                'type'      => 'input',
                'subtype'   => '',
                'id'    => $this->plugin_name.'_example_setting',
                'name'      => $this->plugin_name.'_example_setting',
                'required' => 'required="required"',
                'get_option_list' => "",
                    'value_type' = serialized OR normal,
        'wp_data'=>(option or post_meta),
        'post_id' =>
        */     
        if($args['wp_data'] == 'option'){
            $wp_data_value = get_option($args['name'],$args['default_value']);
        } elseif($args['wp_data'] == 'post_meta'){
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
        }

        $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;

        switch ($args['type']) {

            case 'input':            
                if($args['subtype'] != 'checkbox'){
                    $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
                    $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
                    $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
                    $size = (isset($args['size'])) ? 'size="'.$args['size'].'"' : 'size="40"';
                    if(isset($args['disabled'])){
                        // hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" '.$size.' disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />';
                    } else {
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" '.$size.' value="' . esc_attr($value) . '" />';
                    }
                } else {
                    $checked = ($value) ? 'checked' : '';
                    echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
                }
            break;
            case 'select':
                echo '<select id="'.$args['id'].'" name="'.$args['name'].'">';
                echo '<option value="'.$value.'">'.$args['select_options'][$value].'</option>';
                foreach($args['select_options'] as $optionVal => $optionTitle)
                {
                    if($optionVal!=$value)
                        echo '<option value="'.$optionVal.'">'.$optionTitle.'</option>';
                }
                echo '</select>';
            break;
            default:
            # code...
            break;
        }
    }
}

?>