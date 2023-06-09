<?php
$group=get_field('satisfaction_group');
if($group):
// Check rows existexists.
$subtitle =$group['subtitle_satisfaction'];
if(have_rows('satisfaction_group')): while ( have_rows('satisfaction_group') ) : the_row(); ?>
<?php if(have_rows('satisfaction')):?>
<section class="satisfaction">

	<div class="satisfaction-container">
	<?php

        // Loop through rows.
        while(have_rows('satisfaction')) : the_row();

            // Load sub field value.
            $satisfaction_item = get_sub_field('satisfaction_item');
            $number = $satisfaction_item['number'];
            $title = $satisfaction_item['title'];
			$degree = (intval($number)/100) *180;
			// $degree =(180-$degree) ;
			$degree .='deg';
		

		
			
            ?>
		<div class="satisfaction__item">

		    <div class="circle-wrap">
				<div class="circle">
					
					<div class="mask full" style="
					   transform: rotate(<?=$degree;?>);">
						<div class="fill" ></div>
					</div>
				
					<div class="mask half">
						<div class="fill" style="
						transform: rotate(<?=$degree;?>);" ></div>
						</div>
					
						<div class="inside-circle">
						<?=$number;?>%
						</div>
					
					</div>

				
				</div>
				<p class="satisfaction__item--title"><?=$title;?></p>
			</div>
	
		<?php endwhile;?>
	
	</div>
	<p><?=$subtitle;?></p>
</section>
<?php
endif;
?>
<?php
endwhile;
endif;
endif;
?>

