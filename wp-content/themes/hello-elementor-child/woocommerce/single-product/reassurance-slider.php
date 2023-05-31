<?php

// Check rows existexists.
if(have_rows('reassurance')):?>
<section class="swiper reassurance-slider">
	<div class="swiper-wrapper">

	<?php 

		// Loop through rows.
		while(have_rows('reassurance')) : the_row();

			// Load sub field value.
			$reassurance_item = get_sub_field('reassurance_item');
			$icon = $reassurance_item['icon'];
			$title = $reassurance_item['title'];
			$subtitle = $reassurance_item['subtitle'];
		
		?>
		<div class="swiper-slide">
			<header>

				<picture>
					<img src="<?=$icon['sizes']['thumbnail'];?>" alt="<?=$title;?>">
				</picture>
				<p><?=$title;?></p>
			</header>
			<p class="swiper-slide--subtitle"><?=$subtitle;?></p>

		</div>
	
		<?php endwhile;?>
	
	</div>
	<div class="swiper-pagination"></div>
</section>
<?php
endif;
?>

