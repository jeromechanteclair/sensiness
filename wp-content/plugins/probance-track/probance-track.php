<?php
/**
* Plugin Name: Probance-track
* Plugin URI: https://www.probance.com/
* Description: Plugin activating tracking on visit and cart-in action to Probance
* Version: 1.0
* Author: Probance
* Author URI: https://www.probance.com/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


require_once 'common_probance/probance.php';

/*
 * Settings page
 */

function displayTrackingSettings(){
    require_once 'dashboard-admin-tracking.php';
}



function descSectionWT() {
    echo "";
}


function registerAndBuildFieldsTrack() {
   
    //On enregistre nos sections (elle seront affichée par la page grâce à la fonction do_sections())
    //ID de la section , Titre de la section, fonction qui affiche une description de la section, id de la page à laquelle on raccorde la section
    add_settings_section('probance-track_section', 'Probance Webtrack configuration', 'descSectionWT', 'dashboard-admin-track');

    //On défini des champs
    unset($argsWTtoken);
    $argsWTtoken = array (
            'type'      => 'input',
            'subtype'   => 'text',
            'id'    => 'probance-track_token',
            'name'      => 'probance-track_token',
            'size'      => '',
            'required' => 'true',
            'value_type'=>'normal',
            'wp_data' => 'option',
            'default_value' => ''
    );
    
    unset($argsWTinfra);
    $argsWTinfra = array (
            'type'      => 'select',
            'subtype'   => '',
            'id'    => 'probance-track_wt-infra',
            'name'      => 'probance-track_wt-infra',
            'size'      => '',
            'required' => '',
            'value_type'=>'normal',
            'wp_data' => 'option',
            'default_value' => 'my-probance.one',
            'select_options' => array("t4.my-probance.one"=>"France","jp2.probance.jp"=>"Japan","wt1.probance.ca"=>"Canada")
    );

    unset($argsPrefixTracking);
    $argsPrefixTracking = array (
            'type'      => 'input',
            'subtype'   => 'text',
            'id'    => 'probance-track_prefix-tracking',
            'name'      => 'probance-track_prefix-tracking',
            'size'      => '',
            'required' => 'true',
            'value_type'=>'normal',
            'wp_data' => 'option',
            'default_value' => ''
    );


    unset($argsCBsku);
    $argsCBsku = array (
            'type'      => 'input',
            'subtype'   => 'checkbox',
            'id'    => 'probance-track_cbsku',
            'name'      => 'probance-track_cbsku',
            'size'      => '',
            'required' => '',
            'value_type'=>'normal',
            'wp_data' => 'option',
            'default_value' => ""
    );

    unset($argsCBdebug);
    $argsCBdebug = array (
            'type'      => 'input',
            'subtype'   => 'checkbox',
            'id'    => 'probance-track_cbdebug',
            'name'      => 'probance-track_cbdebug',
            'size'      => '',
            'required' => '',
            'value_type'=>'normal',
            'wp_data' => 'option',
            'default_value' => ""
    );
    
    //On ajoute les champs à la section
    //Id du champ, titre du champ, fonction qui affiche le html du champ, ID page, ID section, Arguments du champs
    add_settings_field('probance-track_token','Token webtrack', 'probance_display_field','dashboard-admin-track','probance-track_section',$argsWTtoken);
    add_settings_field('probance-track_wt-infra','Infra webtrack', 'probance_display_field','dashboard-admin-track','probance-track_section',$argsWTinfra);
    add_settings_field('probance-track_prefix-tracking','Prefix Tracking', 'probance_display_field','dashboard-admin-track','probance-track_section',$argsPrefixTracking);
    add_settings_field('probance-track_cbsku','Utiliser le SKU pour identifier les produits', 'probance_display_field','dashboard-admin-track','probance-track_section',$argsCBsku);
    add_settings_field('probance-track_cbdebug','Debug mode', 'probance_display_field','dashboard-admin-track','probance-track_section',$argsCBdebug);


    //enregistrement du champs dans un groupe de champs qui sera appellé dans la page
    register_setting('probance-track_settings','probance-track_token');
    register_setting('probance-track_settings','probance-track_wt-infra');
    register_setting('probance-track_settings','probance-track_prefix-tracking');
    register_setting('probance-track_settings','probance-track_cbsku');
    register_setting('probance-track_settings','probance-track_cbdebug');

}

//On enregistre les champs du formulaire
add_action('admin_init', 'registerAndBuildFieldsTrack');







/*
 * TRACKING
 */

//fonction appellée en ajax pour retourner le SKU d'un produit (utile pour la variation dont on a pas l'ID avant le clic sur add-to-cart)
function p_getsku(){
    global $wpdb;
    if(isset($_REQUEST['pid']))
    {
        $pid=$_REQUEST['pid'];
        $product = wc_get_product($pid);
        echo ($product->get_sku());
    }
    wp_die();
}

add_action( "wp_ajax_p_getsku", "p_getsku" );
add_action( "wp_ajax_nopriv_p_getsku", "p_getsku" );




//fonction appellée lors de l'affichage de toute page
function probance_track(){
    //appel de nos deux scripts JS 
    wp_enqueue_script('probance-tracker', plugin_dir_url(__FILE__) . 'js/probance_tracker-min.js');

    wp_enqueue_script('probance-visit', plugin_dir_url(__FILE__) . 'js/probance_visit.js',array('jquery'));

    try{
        $ajax=get_option('woocommerce_enable_ajax_add_to_cart');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "woocommerce_enable_ajax_add_to_cart" : '.$e);
    }
    try{
        $token=get_option('probance-track_token');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "probance-track_token" : '.$e);
    }
    try{
        $infra=get_option('probance-track_wt-infra');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "probance-track_wt-infra" : '.$e);
    }
    try{
        $prefixTrk=get_option('probance-track_prefix-tracking');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "probance-track_prefix-tracking" : '.$e);
    }
    try{
        $sku=get_option('probance-track_cbsku');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "probance-track_cbsku" : '.$e);
    }
    try{
        $debug=get_option('probance-track_cbdebug');
    }catch(Exception $e){

        write_log('[PROBANCE - track] Erreur récupération "probance-track_cbdebug" : '.$e);
    }

    //Check if the user is logged 
    if ( is_user_logged_in() ) {
        global $current_user;
        wp_get_current_user();
        $email = $current_user->user_email;
     }else
        $email='';


    
    if($ajax == 'yes'){
        wp_enqueue_script('probance-ajax-cart', plugin_dir_url(__FILE__) . 'js/probance_ajax_cart.js',array('jquery'));
        //Passe les variables au script ajax-cart
        wp_localize_script('probance-ajax-cart', 'probance_ajax_cart_vars', array('token'=>$token, 'email' => $email, 'infra'=>$infra, 'prefix'=>$prefixTrk, 'sku'=>$sku, 'debug'=>$debug));
    }
    
    //Check if we are in product page
    if ( is_product() ){
        global $post;
        $product = wc_get_product( $post->ID );
        $pid=$product->get_id();
        $ptype=$product->get_type();
        $psku=$product->get_sku();

        wp_enqueue_script('probance-cart', plugin_dir_url(__FILE__) . 'js/probance_cart.js',array('jquery'));
        //Passe les variables au script cart
        wp_localize_script('probance-cart', 'probance_cart_vars', array('token'=>$token, 'pid' => $pid, 'ptype' => $ptype, 'psku'=>$psku, 'email' => $email, 'infra'=>$infra, 'prefix'=>$prefixTrk, 'sku'=>$sku, 'debug'=>$debug, 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    }
    else{
        $pid='';
        $psku='';
    }
    
    
    //Passe les variables au script visit
    wp_localize_script('probance-visit', 'probance_visit_vars', array('token'=>$token, 'pid' => $pid , 'psku'=>$psku, 'email' => $email, 'infra'=>$infra, 'prefix'=>$prefixTrk, 'sku'=>$sku, 'debug'=>$debug));      
        
}

//Ajout de nos scripts de track visit au chargement des JS de WP
add_action('wp_enqueue_scripts','probance_track');

?>