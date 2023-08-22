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
] );
$besoins_menu = wp_nav_menu( [
	'theme_location' => 'menu-besoins',
	'fallback_cb' => false,
	'echo' => false,
] );
$guidecbd_menu = wp_nav_menu( [
	'theme_location' => 'menu-guidecbd',
	'fallback_cb' => false,
	'echo' => false,
] );
$marque_menu = wp_nav_menu( [
	'theme_location' => 'menu-marque',
	'fallback_cb' => false,
	'echo' => false,
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
		<a href="/">
			Notre blog
		</a>
	</div>
	</div>
</header>
