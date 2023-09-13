<?php
if(!is_product() && !is_checkout() && !is_cart() && !is_account_page()) {
    $args = array(
        'post_type' => 'popup', // Type de publication "popup"
        'posts_per_page' => 1, // Récupérer un seul post
        'meta_key' => 'is_active', // Clé de méta-champ ACF
        'meta_value' => true, // La valeur que vous souhaitez rechercher (true)
    );

    $popup_query = new WP_Query($args);

    if ($popup_query->have_posts()) {
        while ($popup_query->have_posts()) {
            $popup_query->the_post();
            $popup_id = get_the_ID();
            $popup_time = get_option('popup_time_'. $popup_id);
            $thumbnail = get_the_post_thumbnail($popup_id);
            $title = get_the_title($popup_id);
            $content = get_the_content($popup_id);
            $link = get_field('link');

            $desktop = get_the_post_thumbnail_url($popup_id, 'single_hero');
            $mobile = get_the_post_thumbnail_url($popup_id, 'single_hero');
            $show_popup = true;
            if (isset($_COOKIE['popup_time_'. $popup_id])) {
                $timevalue = $_COOKIE['popup_time_'. $popup_id];
                if($timevalue == $popup_time) {
                    $show_popup = false;
                } else {
                    $show_popup = true;



                }
            }

            if($show_popup):
                ?>
	<div id="popup" class="popup <?php if (is_product()):?> popup--product<?php endif;?>" data-id="<?= $popup_id;?>" data-cookie="<?=$popup_time;?>">
	<div class="close-popup">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
		<mask id="mask0_155_4097" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="16" height="16">
		<rect width="16" height="16" fill="#D9D9D9"/>
		</mask>
		<g mask="url(#mask0_155_4097)">
		<path d="M4.26672 12.2052L3.79492 11.7334L7.52825 8.00005L3.79492 4.26672L4.26672 3.79492L8.00005 7.52825L11.7334 3.79492L12.2052 4.26672L8.47186 8.00005L12.2052 11.7334L11.7334 12.2052L8.00005 8.47185L4.26672 12.2052Z" fill="#131313"/>
		</g>
		</svg>

	</div>
		<picture>
			<source srcset="<?=$desktop;?>" media="(min-width: 999px)">
				<source srcset="<?=$mobile;?>" media="(max-width: 999px)">
			<img src="<?=$desktop;?>" alt="<?= wp_strip_all_tags($title);?>">
		</picture>
		<div class="popup__content">
			<p class="title"><?=$title;?></p>
			<p><?=$content;?></p>
			<a class="button"href="<?= $link['url'];?>">
				<?= $link['title'];?>
			</a>
		</div>
	</div>


   <?php endif;
        }
        wp_reset_postdata(); // Réinitialiser la requête
    } else {
    }
}