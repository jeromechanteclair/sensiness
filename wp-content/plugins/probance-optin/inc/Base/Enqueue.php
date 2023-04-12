<?php 

/**
 * @package probance-optin
 */

namespace Inc\Base;

use Inc\Data\Translations;
use Inc\Common\Utils;

class Enqueue 
{
    public function register()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueues'));
        add_action('wp_enqueue_scripts', array($this, 'enqueues'));
    }

    /**
     * Admin enqueues scripts and style
     */
    public function admin_enqueues()
    {
        // Retrieve DEBUG option
        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            // write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            die('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
        }
        
        wp_enqueue_script('probance-admin-form-handler', PLUGIN_URL . 'assets/admin.js');

        wp_enqueue_style('probance-newsletter-style',  PLUGIN_URL . 'assets/admin.css');

        wp_localize_script('probance-admin-form-handler', 'admin', array('debug' => $debug,'ajaxurl' => admin_url( 'admin-ajax.php' )));

        // BOOTSTRAP
        // wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css' );
    }

    /**
     * Frontend enqueues scripts and style
     */
    public function enqueues() 
    {   

        $allTranslations = [];
        $langs = Utils::getLanguages();

        foreach ($langs as $lang )
        {
            $allTranslations[strtolower($lang)]=[];

            foreach(['newsletter', 'optin'] as $module)
            {  
                $allTranslations[strtolower($lang)]=array_merge($allTranslations[strtolower($lang)],Translations::getTranslatedFields($module, $lang));

                
            }
        }

        if(!is_admin()) 
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script('probance-lang', PLUGIN_URL . 'assets/probance_lang.js', array('jquery'), null, true);
            wp_enqueue_script('probance-newsletter', PLUGIN_URL . 'assets/probance_newsletter.js', array('jquery'), null, true);
            // Enqueue style
            wp_enqueue_style('probance-newsletter-style',  PLUGIN_URL . 'assets/newsletter-form-css.css');
        }

        $recaptcha_checked=get_option('probance-optin_banner-cbrecaptcha');
        $recaptcha_sitekey=get_option('probance-optin_banner-recaptchasitekey');

        if($recaptcha_checked==1 && $recaptcha_sitekey!='') wp_enqueue_script('probance-newsletter-recaptcha', 'https://www.google.com/recaptcha/api.js?render='.$recaptcha_sitekey);
        
        wp_script_add_data( 'probance-newsletter-recaptcha', 'async/defer' , true );

        // Retrieve DEBUG option
        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            // write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            die('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
        }

        // Retrieve CBNAMES option
        try{
            $cbnames=get_option('probance-optin_banner-cbnames');
        }catch(Exception $e){
            // write_log('[PROBANCE - optin] Erreur récupération "probance-optin_banner-cbnames" : '.$e);
            die('[PROBANCE - optin] Erreur récupération "probance-optin_banner-cbnames" : '.$e);
        }

        wp_localize_script('probance-newsletter', 'probance_newsletter', array('debug'=> $debug,  'cbnames' => $cbnames, 'rc_sitekey' => $recaptcha_sitekey, 'cb_recaptcha' => $recaptcha_checked,'ajaxurl' => admin_url( 'admin-ajax.php' )));
        wp_localize_script('probance-lang', 'probance_lang', array('debug'=> $debug, 'translations' => $allTranslations));

    }
}

?>