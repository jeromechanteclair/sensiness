<?php

if(have_rows('diagnostic' )): ?>
<section class="diagnostic">


	<?php
	while ( have_rows('diagnostic' ) ) : 
	the_row(); 
	$title = get_sub_field('title');
	$image = get_sub_field('image');

	$link = get_sub_field('link');
	$content = get_sub_field('content');

	?>
	<picture>
			<img src="<?=$image['url'];?>" alt="<?=$title;?>">
		</picture>
	<div class="diagnostic-container container">
		
		<div class="diagnostic-container__left">
			<h2><?=$title;?></h2>
			<?=$content;?>
		</div>
		<div class="diagnostic-container__right">
		<a class="diagnostic-button" href="<?=$link['url'];?>"><?=$link['title'];?></a>
		</div>
		
	</div>
	<span></span>
	<?php  endwhile;?>

	</div>
</section>
<?php endif;?>

