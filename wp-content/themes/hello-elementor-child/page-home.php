<?php
/* Template Name:Page home */ 

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();


while (have_posts()) :
    the_post();
    ?>

<main id="content" >

	<div class="page-content">
		<?php the_content(); ?>
	
	</div>


</main>

	<?php
endwhile;
get_footer();

