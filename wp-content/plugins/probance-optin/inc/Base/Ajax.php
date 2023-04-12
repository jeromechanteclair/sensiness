<?php

/**
 * @package probance-optin
 */

namespace Inc\Base;

use Inc\Common\Utils;

class Ajax 
{
    public function register()
    {
        add_action( 'wp_ajax_admin_load_option', array( $this, 'probance_options') );
        add_action( 'wp_ajax_nopriv_admin_load_option', array( $this, 'probance_options') );

        add_action( 'wp_ajax_admin_add_language', array( $this, 'probance_add_language') );
        add_action( 'wp_ajax_nopriv_admin_add_language', array( $this, 'probance_add_language') );
    }

    /**
     * Ajax code to register properties options 
     */
    public function probance_options() 
    {
        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            $debug=0;
        }

        if($debug == 1) Utils::write_log($_POST);

        if (isset($_POST['values']) && isset($_POST['options'])) {
            $nb_options=count($_POST['options']);
            $options=$_POST['options'];
            $values=$_POST['values'];

            if($debug == 1) Utils::write_log('NB OPTIONS : '.$nb_options);

            for ($i = 0; $i < $nb_options; $i++) {
                $o=$options[$i];
                $v=$values[$i];
                update_option($o,$v);
                if($debug == 1) Utils::write_log('[PROBANCE - optin] option after update : '. get_option($o));
            }
            
        }
    }

    /**
     * Ajax code to register languages
     */
    public function probance_add_language() 
    {   
        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            $debug=0;
        }

        if($debug == 1) Utils::write_log($_POST);

        if (isset($_POST['value']) && isset($_POST['option'])) {
            $option=$_POST['option'];
            $value=$_POST['value'];

            if (!Utils::option_exists($option))
            {
                if($debug == 1) Utils::write_log('Creating option language : ' . $option . ' with value : ' . $value);
                add_option( $option, strtoupper($value));
            }
            else
            {
                $langs_str = get_option($option);

                $lang_arr = explode(';', $langs_str);

                array_push($lang_arr, strtoupper($value));

                $lang_arr = array_unique($lang_arr);

                $langs_str = implode($lang_arr, ';');

                if($debug == 1) Utils::write_log('Updating option language : ' . $option . ' with value : ' . $langs_str);

                update_option($option, $langs_str);
            }

            return true;
            
        } else
        {
            return false;
        }
    }
}

?>