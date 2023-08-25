<?php
$count = count(get_field('bandeau_marquee'));
$count = 8;
if(have_rows('bandeau_marquee')): ?>
<section class="bandeau-marquee">

<div class="bandeau-marquee__item">
	<?php for ($i = 0; $i < $count; $i++) { ?>
		<span class="marquee">
			<?php
            while (have_rows('bandeau_marquee')) :
                the_row();
                $content = get_sub_field('content');
                ?>
			<span><?=$content;?></span>
			<?php  endwhile;?>
		</span>
	<?php }?>
	</div>
</section>
<?php endif;?>
