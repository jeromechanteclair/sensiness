<?php

/**
 * Load File : Inclus les différents custom post_types
 *
 * @category  Load
 * @package   lilet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://bigbump.fr
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Inclusion des custom post_types
 */

// require_once plugin_dir_path( __FILE__ ).'vendor/autoload.php';
// require_once 'taxonomies/ExtraFieldsTaxonomy.php';
// require_once 'taxonomies/VilleTaxonomy.php';
require_once 'taxonomies/FAQTaxonomy.php';
require_once 'post-types/MetaboxGenerator.php';
// require_once 'post-types/CommentMetaboxGenerator.php';
// require_once 'post-types/Vendeur.php';
// require_once 'post-types/Atelier.php';
// require_once 'post-types/Diagnostic.php';
// require_once 'post-types/Prestation.php';
// require_once 'post-types/Product.php';
// require_once 'post-types/Page.php';
require_once 'post-types/FAQ.php';
// require_once 'post-types/Post.php';
// require_once 'post-types/Comment.php';


/**
 * Inclusion des taxonomies
 */
// new VilleTaxonomy();
new FAQTaxonomy();
/**
 * On instancie les custom post_types
 */

// new Vendeur();
// new Atelier();
// new Prestation();
// new Product();
// new Page();
new FAQ();
// new Post();
// new Comment();
// new Diagnostic();
// custom tax
// $modifiedTaxonomy = new ExtraFieldsTaxonomy('category');

// $modifiedTaxonomy->add_field('term_meta[file]','file','priorité');
// $modifiedTaxonomy->init();

// $modifiedTaxonomy = new ExtraFieldsTaxonomy('category');

// $modifiedTaxonomy->add_field('term_meta[custom_order]','number','priorité');
// $modifiedTaxonomy->init();


// function LAC_admin_fcss() {
// 	if(is_admin()){
// 		wp_enqueue_style(
// 			'my-custom-block-frontend-style',
// 			plugins_url( 'css/editor.css', __FILE__ ),
// 			array( ),
// 			filemtime( plugin_dir_path( __FILE__ ) . 'css/editor.css' )
// 		);
// 		   global $post_type;
// 		//    var_dump($post_type);die();
//  			 $screen = get_current_screen(); 
// //   var_dump($screen);die();
// 		if( 'product' == $post_type  ||  'diagnostic' == $post_type  || isset($_GET['action'])&& $_GET['action']=='editcomment' || isset($_GET['taxonomy']) && $_GET['taxonomy']=='category')
// 			// var_dump(plugins_url( 'js\main.js', __FILE__ ));die();
// 		wp_enqueue_script( 'bibliotheque-admin-script', plugins_url( 'js\main.js', __FILE__ )  );
// 	}

// }
// /**
//  * Editor Styles
//  */
// add_action( 'admin_print_styles-post-new.php', 'LAC_admin_fcss', 11 );
// add_action( 'admin_print_styles-post.php', 'LAC_admin_fcss', 11 );
// // add_action( 'admin_print_styles-comments.php', 'LAC_admin_fcss', 11 );
// add_action( 'admin_print_styles-comment.php', 'LAC_admin_fcss', 11 );
// add_action( 'admin_enqueue_scripts', 'LAC_admin_fcss', 11 );



