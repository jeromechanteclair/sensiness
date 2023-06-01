<?php
$group=get_field('ingredients');
// Check rows existexists.
$main_title =$group['title'];
$main_img = $group['image'];
if(have_rows('ingredients')): while (have_rows('ingredients')) : the_row(); ?>
<?php if(have_rows('ingredient_group')):?>
<section class="ingredients">

	<div class="ingredients__left">
	<h2><?=$main_title;?></h2>
	<?php
        while(have_rows('ingredient_group')) : the_row();

            $image = get_sub_field('image');
            $title = get_sub_field('title');
            $subtitle = get_sub_field('subtitle');
            ?>
		<div class="ingredients__left__item">
			<picture>
				<img src="<?=$image['sizes']['thumbnail'];?>" alt="<?=$title;?>">
			</picture>
			<div class="ingredients__left__item__content">
				<p><?=$title;?></p>
				<span><?=$subtitle;?></span>
			</div>
		</div>
	
		<?php endwhile;?>
	
	</div>
	<div class="ingredients__right">
		<picture>
			<img src="<?=$main_img['sizes']['large'];?>" alt="<?=$main_title;?>">
		</picture>
	</div>

</section>
<?php
endif;
    ?>
<?php
endwhile;
endif;
?>

