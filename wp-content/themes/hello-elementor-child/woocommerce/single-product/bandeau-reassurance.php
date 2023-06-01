<?php

if(have_rows('reassurance_produit', 'option')): ?>
<section class="bandeau-reassurance">


	<?php
	while ( have_rows('reassurance_produit', 'option') ) : 
	the_row(); 

	$icon = get_sub_field('icon');

	$content = get_sub_field('content');

	?>
	<div class="bandeau-reassurance__item">
		<picture>
			<img src="<?=$icon['sizes']['thumbnail'];?>" alt="<?=htmlspecialchars($content);?>">
		</picture>
		<?=$content;?>
	</div>
	<?php  endwhile;?>
</section>
<?php endif;?>
