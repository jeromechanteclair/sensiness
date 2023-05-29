<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 * @since 1.0.0
 */


 /***
  * Load Inc dependancies
  */
require_once(get_stylesheet_directory() . '/inc/Woocommerce.php');
require_once(get_stylesheet_directory() . '/inc/Images.php');

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );
// Child theme and scripts loader
function hello_elementor_child_enqueue_scripts() {
	// Theme
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
	wp_enqueue_style(
		'jc-style',
		get_stylesheet_directory_uri() . '/dist/css/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
	// Scripts
	$main_script_uri = get_stylesheet_directory_uri() . '/assets/js/main.js';
	wp_enqueue_script( 'index', $main_script_uri, array ( 'jquery' ), '1.0', false);
}

/**
 *	Include custom functions & modules
 */
// Add default product to cart for variable products quick ajax add to cart
// include get_stylesheet_directory() . '/includes/add_default_to_cart.php';

// Change the way prices are displayed for variable products
include get_stylesheet_directory() . '/includes/custom_variation_prices.php';

// Change the add to cart text
include get_stylesheet_directory() . '/includes/change_add_to_cart_text.php';

// Change the product images sizes
// include get_stylesheet_directory() . '/includes/product_images_sizes.php';

// Add secondary product image that swap on hover
include get_stylesheet_directory() . '/includes/product_loops_image_swap.php';

// Change the global product price according to the current variation price on the product page
include get_stylesheet_directory() . '/includes/product_variation_price.php';

// Displays an automatic navigation of categories
include get_stylesheet_directory() . '/includes/shortcode_mc_categories-slider-navigation.php';

// Displays an automatic navigation of effects
include get_stylesheet_directory() . '/includes/shortcode_mc_effects-slider-navigation.php';
