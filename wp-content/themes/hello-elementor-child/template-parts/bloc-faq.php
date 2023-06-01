<?php
/**
 * Template part for displaying a single atelier
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ateliercrepus
 */
$metas = get_post_meta($post->ID);

?>

<li id="post-<?php the_ID(); ?>" <?php post_class('accordion'); ?>  itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
	<input id="ac-<?php the_ID(); ?>" name="accordion-<?php the_ID(); ?>" type="checkbox" checked>
	<i></i>
	<h3 class="accordion-header" for="ac-<?php the_ID(); ?>" itemprop="name"><?php the_title( );?></h3>

		

	<span itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer" class="accordion-content">
		<div itemprop="text">
			<?php the_content();?>
		</div>
	</span>

</li><!-- #post-<?php the_ID(); ?> -->
