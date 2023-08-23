<?php
/**
 * The template for displaying header.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description', 'display' );
$product_menu = wp_nav_menu( [
	'theme_location' => 'menu-produits',
	'fallback_cb' => false,
	'echo' => false,
	'walker' => new sensiness\app\Custom_Submenu_Walker(),
] );
$besoins_menu = wp_nav_menu( [
	'theme_location' => 'menu-besoins',
	'fallback_cb' => false,
	'echo' => false,
	'walker' => new sensiness\app\Custom_Submenu_Walker(),
] );
$guidecbd_menu = wp_nav_menu( [
	'theme_location' => 'menu-guidecbd',
	'fallback_cb' => false,
	'echo' => false,
	'walker' => new sensiness\app\Custom_Submenu_Walker(),
] );
$marque_menu = wp_nav_menu( [
	'theme_location' => 'menu-marque',
	'fallback_cb' => false,
	'echo' => false,
	'walker' => new sensiness\app\Custom_Submenu_Walker(),
] );

?>

<header id="site-header" class="site-header" role="banner">
	
	<div class="container">
	<div class="site-branding">
		<a href="/">
			<?php get_template_part('template-parts/svg/logo.svg');?>

		</a>
	</div>

	<nav class="site-navigation">
		<?php if ( $product_menu ) : ?>
		<?php
		// PHPCS - escaped by WordPress with "wp_nav_menu"
		echo $product_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<?php endif; ?>
		<?php if ( $product_menu ) : ?>
		<?php
		// PHPCS - escaped by WordPress with "wp_nav_menu"
		echo $besoins_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<?php endif; ?>
		<?php if ( $besoins_menu ) : ?>
		<?php
		// PHPCS - escaped by WordPress with "wp_nav_menu"
		echo $guidecbd_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<?php endif; ?>
		<?php if ( $marque_menu ) : ?>
		<?php
		// PHPCS - escaped by WordPress with "wp_nav_menu"
		echo $marque_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<?php endif; ?>
	</nav>
	<div class="end-navigation">
		<a href="<?php get_permalink(get_option('page_for_posts'));?>">
			<span>Notre blog</span>
		</a>
		<a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id'));?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id="mask0_155_3603" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
			<rect width="24" height="24" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask0_155_3603)">
			<path d="M6.19615 17.4846C7.04615 16.8731 7.94808 16.3894 8.90193 16.0337C9.85577 15.6779 10.8885 15.5 12 15.5C13.1115 15.5 14.1442 15.6779 15.0981 16.0337C16.0519 16.3894 16.9538 16.8731 17.8039 17.4846C18.4641 16.8013 18.9952 15.9942 19.3971 15.0634C19.799 14.1327 20 13.1115 20 12C20 9.78333 19.2208 7.89583 17.6625 6.3375C16.1042 4.77917 14.2167 4 12 4C9.78333 4 7.89583 4.77917 6.3375 6.3375C4.77917 7.89583 4 9.78333 4 12C4 13.1115 4.20096 14.1327 4.60288 15.0634C5.00481 15.9942 5.5359 16.8013 6.19615 17.4846ZM12.0006 12.5C11.1579 12.5 10.4471 12.2108 9.86827 11.6323C9.28942 11.0539 9 10.3433 9 9.50058C9 8.65788 9.28923 7.94711 9.86768 7.36828C10.4461 6.78943 11.1567 6.5 11.9994 6.5C12.8421 6.5 13.5529 6.78923 14.1317 7.3677C14.7106 7.94615 15 8.65672 15 9.49942C15 10.3421 14.7108 11.0529 14.1323 11.6317C13.5539 12.2106 12.8433 12.5 12.0006 12.5ZM12 21C10.7449 21 9.56987 20.7664 8.475 20.2991C7.38013 19.8317 6.42757 19.1929 5.6173 18.3827C4.80705 17.5724 4.16827 16.6199 3.70095 15.525C3.23365 14.4301 3 13.2551 3 12C3 10.7449 3.23365 9.56987 3.70095 8.475C4.16827 7.38013 4.80705 6.42757 5.6173 5.6173C6.42757 4.80705 7.38013 4.16827 8.475 3.70095C9.56987 3.23365 10.7449 3 12 3C13.2551 3 14.4301 3.23365 15.525 3.70095C16.6199 4.16827 17.5724 4.80705 18.3827 5.6173C19.1929 6.42757 19.8317 7.38013 20.299 8.475C20.7663 9.56987 21 10.7449 21 12C21 13.2551 20.7663 14.4301 20.299 15.525C19.8317 16.6199 19.1929 17.5724 18.3827 18.3827C17.5724 19.1929 16.6199 19.8317 15.525 20.2991C14.4301 20.7664 13.2551 21 12 21ZM12 20C12.9218 20 13.8289 19.8388 14.7212 19.5163C15.6135 19.1939 16.3846 18.7526 17.0346 18.1923C16.3846 17.6705 15.6327 17.258 14.7788 16.9548C13.925 16.6516 12.9987 16.5 12 16.5C11.0013 16.5 10.0718 16.6484 9.21155 16.9452C8.35128 17.242 7.60256 17.6577 6.96537 18.1923C7.61537 18.7526 8.38653 19.1939 9.27885 19.5163C10.1712 19.8388 11.0782 20 12 20ZM12 11.5C12.5615 11.5 13.0353 11.307 13.4212 10.9212C13.807 10.5353 14 10.0615 14 9.5C14 8.93847 13.807 8.46475 13.4212 8.07885C13.0353 7.69295 12.5615 7.5 12 7.5C11.4385 7.5 10.9647 7.69295 10.5788 8.07885C10.1929 8.46475 10 8.93847 10 9.5C10 10.0615 10.1929 10.5353 10.5788 10.9212C10.9647 11.307 11.4385 11.5 12 11.5Z" fill="#131313"/>
			</g>
			</svg>
		</a>
		<div class="toggle-cart mini-cart">
			<?php woocommerce_mini_cart();?>
		

		</div>
		<div class="cart-overlay"></div>
	</div>
	</div>
</header>
