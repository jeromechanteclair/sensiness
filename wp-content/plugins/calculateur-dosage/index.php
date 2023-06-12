<?php // phpcs:ignore
/**
 * Plugin Name: Calculateur dosage CBD
 * Plugin URI: 
 * Description: Calculateur de dosage CBD personnalisé
 * Author: Jerome chanteclair
 * Author URI: https://jerome-chanteclair.com/
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// // include Classes files.
require_once plugin_dir_path( __FILE__ ) . 'inc/CalculateurSettings.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/ExtraFieldsTaxonomy.php';




$poids_max = array(40,50,60,70,90,110,200);
foreach ($poids_max as $poid) {
	$modifiedTaxonomy = new ExtraFieldsTaxonomy('pa_traitement');

	$modifiedTaxonomy->add_field('term_meta[poids]['.$poid.']', 'number', 'Posologie pour le poids max '.$poid.'kg (en mg/jour)', null);
	$modifiedTaxonomy->init();
}




/**
 * Admin Styles
 */
function calculateur_admin_css() {
	if ( is_admin() ) {
		wp_enqueue_style(
			'calculateur_admin-stylesheet',
			plugins_url( 'dist/admin.min.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . 'dist/admin.min.css' )
		);
	}

}
/**
 * Admin Scripts
 *
 * @param string $hook_suffix page suffix.
 */
function calculateur_admin_js( $hook_suffix ) {

	wp_print_script_tag(
		array(
			'id'    => 'calculateur_admin-script',
			'src'   => plugins_url( 'dist/adminwp.js', __FILE__ ),
			'defer' => true,
		)
	);
	// if ( 'toplevel_page_breaking-news-settings' === $hook_suffix ) {
		calculateur_admin_css();
	// }

}
// add_action( 'admin_print_styles-post-new.php', 'calculateur_admin_css', 11 );
// add_action( 'admin_print_styles-post.php', 'calculateur_admin_css', 11 );
add_action( 'admin_enqueue_scripts', 'calculateur_admin_js', 11 );

// /**
//  * Frontend style
//  */
function calculateur_frondend_css() {
	

    wp_print_script_tag(
    array(
            'id'    => 'calculateur_frontend-script',
            'src'   => plugins_url('dist/main.js', __FILE__),
            'defer' => true,
        )
	);
	
    wp_enqueue_style('calculateur-style',plugins_url( 'dist/frontend.min.css', __FILE__ ));


}
add_action( 'wp_enqueue_scripts', 'calculateur_frondend_css' );

