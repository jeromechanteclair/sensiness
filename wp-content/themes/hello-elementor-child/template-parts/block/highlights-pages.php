<?php
$title = get_field('title');

if(have_rows('highlights-pages')): ?>
<section class="highlights-pages">
	<?php if($title):?>
	<h3 class="subtitle"><?= $title;?></h3>
	<?php endif;?>
	<div class="container">
	<?php
	while (have_rows('highlights-pages')) :
		the_row();
		$image = get_sub_field('image');
		$title = get_sub_field('title');
		$content = get_sub_field('content');
		$link = get_sub_field('link');
		?>
		<div class="highlights-pages__item">
			<picture class="bg">
				<img src="<?=$image['url'];?>" alt="<?=
				wp_strip_all_tags($title );?>">
			</picture>
			<p class="title"><?=$title;?></p>
			<?=$content;?>
			<a href="<?= $link['url'];?>" class="button">
				<?= $link['title'];?>
			</a>
		</div>
	<?php  endwhile;?>
	</div>
</section>
<?php endif;?>
