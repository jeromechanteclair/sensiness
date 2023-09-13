<?php

$thumbnail = get_field('image');
$video = get_field('video');
$title = get_field('title');


if(have_rows('highlights-reviews')): ?>
<section class="highlights-reviews">
	<video class="lazy" id='video-home'   playsinline preload='metadata' width='100%' height='100%' loop muted preload="none" poster="<?= $thumbnail['url'];?>">
		<source data-src="<?= $video['url'];?>" type="<?= $video['mime_type'];?>" />
	</video>
	<div class="container">

		<p class="h3"><?= $title;?></p>
		<div class="swiper swiper-reviews">

		
			<div class="swiper-wrapper ">



			
				<?php
				while (have_rows('highlights-reviews')) :
					the_row();
					$name = get_sub_field('name');
					$content = get_sub_field('content');

					?>
					<div  class="highlights-reviews__item swiper-slide">

						<?=$content;?>
						<p class="name">
							<?=$name;?>
						</p>
						<p class="subtitle">
							Achat vérifié
						</p>

					</div>
				<?php  endwhile;?>
			</div>
			<div class="swiper-pagination"></div>
		</div>
	</div>

</section>
<?php endif;?>
