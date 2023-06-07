<?php

if(have_rows('photos_illustration', )): ?>
<section class="photos">
	<div class="container">


	<?php
	while ( have_rows('photos_illustration' ) ) : 
	the_row(); 
	$title= get_the_title();
	$image_left = get_sub_field('image_left');
	$image_right = get_sub_field('image_right');


	?>
	<picture>
		<img src="<?=$image_left['url'];?>" alt="<?=$title;?>">
	</picture>
	<picture>
		<img src="<?=$image_right['url'];?>" alt="<?=$title;?>">
	</picture>
	
	<?php  endwhile;?>

	</div>
</section>
<?php endif;?>

