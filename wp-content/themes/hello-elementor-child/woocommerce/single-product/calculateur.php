<?php

if(have_rows('calculateur' )): ?>



<?php
while (have_rows('calculateur')) :
	the_row();
	$image = get_sub_field('image');
	$activate = get_sub_field('activate');
	?>
	<?php if($activate):?>
	<section class="calculateur">

		<div class="calculateur-container ">

			<div class="calculateur-container__left">
			<picture>
					<img src="<?=$image['url'];?>" alt="Calculer votre dosage">
				</picture>
			</div>
			<div class="calculateur-container__right">
				<?= do_shortcode( '[comparateur_cbd product_id='.get_the_ID().']');?>
			</div>

		</div>

	</section>
<?php endif;?>
<?php  endwhile;?>

	
<?php endif;?>

