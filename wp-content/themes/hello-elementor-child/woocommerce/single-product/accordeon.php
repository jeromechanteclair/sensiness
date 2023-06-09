<?php

$group=get_field('accordeon');
if($group):
// Check rows existexists.
$title =$group['title'];

if(have_rows('accordeon')): while ( have_rows('accordeon') ) : the_row(); ?>
<?php if(have_rows('accordeon_group')):?>
<section class="accordeon">

	<p class="summary-heading"><?=$title;?></p>
	<ul class=" accordions accordions--product">
	<?php

        // Loop through rows.
        while(have_rows('accordeon_group')) : the_row();

            // Load sub field value.
            $accordeon_title = get_sub_field('title');
            $accordeon_content = get_sub_field('content');


		
			
            ?>

		<li id="post--<?php echo get_row_index(); ?>" <?php post_class('accordion'); ?>  itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
			<input id="ac--<?php echo get_row_index(); ?>" name="accordion--<?php echo get_row_index(); ?>" type="checkbox" checked>
			<i></i>
			<h3 class="accordion-header" for="ac--<?php echo get_row_index(); ?>" itemprop="name"><?=$accordeon_title;?></h3>

				

			<span itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer" class="accordion-content">
				<div itemprop="text">
					<?=   $accordeon_content ;?>
				</div>
			</span>

		</li>
	
		<?php endwhile;?>
	
	</ul>

</section>
<?php
endif;
?>
<?php
endwhile;
endif;
endif;
?>

