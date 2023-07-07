<?php 
$thumbnail_id = get_post_thumbnail_id(  );

$desktop = get_the_post_thumbnail_url( $post, 'single_thumbnail' );
$mobile = get_the_post_thumbnail_url( $post, 'single_thumbnail_mobile' );

?>


<a href="<?= get_the_permalink() ;?>" class="post-preview">
	<picture class="post-preview__thumbnail">
		<source srcset="<?=$desktop;?>" media="(min-width: 999px)">
		<source srcset="<?=$mobile;?>" media="(max-width: 999px)">

		<img src="<?=$desktop;?>" alt="<?= the_title();?>">
	</picture>
	<div class="post-preview__content">
		<p class="title"><?= the_title();?></p>
		<time><?= the_date();?></time>
		<p class='excerpt'><?= get_the_excerpt();?>...</p>
		<span class="post-preview__content__link">
			Lire la suite
		</span>
	</div>
</a>