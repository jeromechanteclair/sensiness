<?php


$title = get_field('title');


if(have_rows('highlights-testimonials')): ?>
<section class="highlights-testimonials">

	<div class="container">
		<p class="h3"><?= $title;?></p>
		<div class="swiper swiper-testimonials">

		
			<div class="swiper-wrapper ">



			
				<?php
				while (have_rows('highlights-testimonials')) :
					the_row();
					$image = get_sub_field('image');
					$content = get_sub_field('content');

					?>
					<div  class="highlights-testimonials__item swiper-slide">
						<?php if($image):?>
						<picture>
							<img src="<?=$image['url'];?>" alt="<?=wp_strip_all_tags(
								$content
							);?>">
						</picture>
						<?php endif;?>
						<?=$content;?>
			


					</div>
				<?php  endwhile;?>
			</div>
			<div class="swiper-pagination"></div>
		</div>
	</div>

</section>
<?php endif;?>
