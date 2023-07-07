<?php

if(have_rows('refer','options')): ?>



	<?php
    while (have_rows('refer','options')) :
        the_row();
        $title = get_sub_field('title');
        $image = get_sub_field('image');
        $link = get_sub_field('link_refer');
        $content = get_sub_field('subtitle');
        if($title):
            ?>
	<section class="diagnostic">
	<?php if($image):?>
		<picture>
			<img src="<?=$image['url'];?>" alt="<?=$title;?>">
		</picture>
	<?php endif;?>
	<div class="diagnostic-container container">
		
		<div class="diagnostic-container__left">
			<h2><?=$title;?></h2>
			<?=$content;?>
		</div>
		<div class="diagnostic-container__right">
		<?php if($link):?>
			<a class="diagnostic-button" href="<?=$link['url'];?>"><?=wp_strip_all_tags($link['title']);?></a>
		<?php endif;?>	
	</div>
		
	</div>
	
	</section>
	<?php endif; endwhile;?>


<?php endif;?>

