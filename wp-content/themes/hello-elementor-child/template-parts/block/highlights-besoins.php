<?php
$title = get_field('title');

if(have_rows('highlights-besoins')): ?>
<section class="highlights-besoins">
	<h3 class="subtitle"><?= $title;?></h3>
<div class="container">




		
			<?php
            while (have_rows('highlights-besoins')) :
                the_row();
                $image = get_sub_field('image');
                $link = get_sub_field('link');
                $title = $link['title'];
                $icon = get_sub_field('icon');
                ?>
				<a href="<?= $link['url'];?>" class="highlights-besoins__item">
				<picture class="bg">
					<img src="<?=$image['url'];?>" alt="<?=$title;?>">
				</picture>
				<p><?=$title;?></p>
					<picture class="icon">
					<img src="<?=$icon['url'];?>" alt="<?=$title;?>">
				</picture>
			</a>
			<?php  endwhile;?>
		
	</div>

</section>
<?php endif;?>
