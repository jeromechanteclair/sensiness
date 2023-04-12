<?php

$query = $args['query'];
	$display=true;
if(isset($args['category'])){
	$display=true;
}
if($query !== null) :
	$currentthematique ="";?>
	<div class="container">
	<ul class="accordions">
	<?php
	while ( $query ->have_posts() ) :
		$query ->the_post();
		$thematique =	get_the_terms($post->ID,'thematique_tags')[0]->name;
	
		$index = $query ->current_post;
		if($display){
			
			if($thematique !== $currentthematique){
				$currentthematique = $thematique;
				
				echo'<li class="heading"><h2>'.$currentthematique.'</h2></li>';
			}
		}
		get_template_part( 'template-parts/bloc', 'faq' );

	endwhile; // End of the loop.
	wp_reset_postdata();?>
	</ul>
	</div>
	<?php
endif;
?>