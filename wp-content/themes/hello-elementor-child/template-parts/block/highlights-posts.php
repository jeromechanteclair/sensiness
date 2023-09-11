<?php 
$display_posts = get_field('display_posts');
$title = get_field('title');
$link = get_field('link');

if($display_posts=='auto'){
		
	$args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 3,
		'orderby' => 'date',
		'order' => 'DESC',
	);

	// Instancier WP_Query avec les arguments
	

}
else{
	
	$posts = get_field('related_posts');
	if($posts){

		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 3,
			'orderby' => 'post__in',
			'order' => 'DESC',
			'post__in'=>$posts 
		);
	}

}
$related_query = new WP_Query($args);

if ($related_query->have_posts()) { ?>
<div class="related-posts">
	<div class="container ">
		<header>
			<h2><?= $title;?></h2>
			<?php if($link):?>
				<a class="button" href="<?=$link['url'];?>">
				<?=$link['title'];?>
				</a>

			<?php endif?>
		</header>
    <div class="related-posts-grid">

        <?php while ($related_query->have_posts()) { ?>

            <?php $related_query->the_post(); ?>
				<?php get_template_part('template-parts/single', 'post');?>
      

        <?php } ?>

    </div>

    <?php wp_reset_postdata(); ?>
</div>
</div>
<?php } ?>