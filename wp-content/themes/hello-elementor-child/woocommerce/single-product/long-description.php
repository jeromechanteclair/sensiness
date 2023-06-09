<?php

if(have_rows('long_description')):
    while (have_rows('long_description')) : the_row();
        $content = get_sub_field('content');
        $title = get_sub_field('title');
		if($title):
        ?>
	<section class="long_description">
		<div class="container">

		
			<div class="long_description__left">
				<h2 class="h2"><?=$title;?></h2>
			</div>
			<div class="long_description__right">
				<?=$content;?>
			</div>
		</div>
	</section>
<?php endif; endwhile; endif;?>
