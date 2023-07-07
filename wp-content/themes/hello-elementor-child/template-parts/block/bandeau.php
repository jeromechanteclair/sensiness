<?php

$thumbnail = get_field('image');
$title =get_field('title');
$content =get_field('content');
$link =get_field('link');



?>
<section class="bandeau">
		<?php if($thumbnail):?>
		<picture>
			<img src="<?=$thumbnail['url'];?>" alt="<?=$title;?>">
		</picture>
		<?php endif;?>
		<div class="bandeau-container container">
			
			<div class="bandeau-container__left">
				<h2><?=$title;?></h2>
				<?=$content;?>
			</div>
			<div class="bandeau-container__right">
			<?php if($link):?>
			<a class="bandeau-button" href="<?=$link['url'];?>"><?=wp_strip_all_tags($link['title']);?></a>
			<?php endif;?>
		</div>
		
	</div>
	
	</section>