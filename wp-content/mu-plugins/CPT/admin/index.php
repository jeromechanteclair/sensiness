<?php

/**
 * Load File : Inclus les différents custom post_types

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Inclusion des custom post_types
 */


require_once 'taxonomies/ExtraFieldsTaxonomy.php';
require_once 'taxonomies/FAQTaxonomy.php';
require_once 'post-types/MetaboxGenerator.php';
require_once 'post-types/FAQ.php';

/**
 * Inclusion des taxonomies
 */

new FAQTaxonomy();
/**
 * On instancie les custom post_types
 */


new FAQ();

$modifiedTaxonomy = new ExtraFieldsTaxonomyTheme('thematique_tags');

$modifiedTaxonomy->add_field('term_meta[custom_order]','number','priorité');
$modifiedTaxonomy->init();




