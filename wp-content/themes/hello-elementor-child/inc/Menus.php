<?php

namespace sensiness\app;

class Menus
{
    public function __construct(){
    

		add_action('after_setup_theme', array($this,'sensiness_menus'), 0);
		// add_filter('wp_nav_menu_objects',  array($this,'add_banner_to_menu'), 10, 2);


    }
	
	/**
	 * Menus
	 */
	public function sensiness_menus(){
		register_nav_menus([ 'menu-produits' => esc_html__('Produits', 'hello-elementor') ]);
		register_nav_menus([ 'menu-besoins' => esc_html__('Besoins', 'hello-elementor') ]);
		register_nav_menus([ 'menu-guidecbd' => esc_html__('Guide CBD', 'hello-elementor') ]);
		register_nav_menus([ 'menu-marque' => esc_html__('La Marque', 'hello-elementor') ]);
		register_nav_menus([ 'menu-account' => esc_html__('Votre compte', 'hello-elementor') ]);
		register_nav_menus([ 'menu-support' => esc_html__('Support', 'hello-elementor') ]);

	}
	public function add_banner_to_menu($sorted_menu_items, $args) {


	
    
		// // get menu
		// $menu = wp_get_nav_menu_object($args->menu);
		// // add banner to DOM
		// $banners = get_field('banner', $menu);
		// $htmlbanner = '';
		// foreach($banners as $banner){
		// 	$attachment_id = get_post_thumbnail_id($banner->ID);
			
		// 	$desktop = wp_get_attachment_image_src($attachment_id, 'product_slide');
		// 	$desktop_double = wp_get_attachment_image_src($attachment_id, 'product_slide_double');
		// 	$mobile = wp_get_attachment_image_src($attachment_id, 'product_slide_mobile');
		// 	$mobile_double = wp_get_attachment_image_src($attachment_id, 'product_slide_mobile_double');
		// 	$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

		// 	$src = '  
		// 		<picture>
		// 		<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)"/>
		// 		<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)"/>
		// 			<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy"/>
		// 		</picture>';

		// 	$title = get_the_title($banner->ID);
		// 	$subtitle = get_field('subtitle',$banner->ID);
		// 	$link = get_field('link',$banner->ID);
			
		// 	$htmlbanner.=$src;


		// }
	


		// 	$dom = new \DOMDocument();
		// 	$dom->loadHTML($items, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		// 	// Trouver le premier sous-menu
		// 	$sub_menus = $dom->getElementsByTagName('ul');
		// 	foreach ($sub_menus as $sub_menu) {
		// 		if ($sub_menu->getAttribute('class') == 'sub-menu') {
		// 			// Ajouter l'élément HTML juste avant la fermeture du sous-menu
		// 			$element_html = '<li>'.$htmlbanner.'</li>';
					
		// 			$fragment = $dom->createDocumentFragment();
		// 			$fragment->appendXML($element_html);
		// 			$sub_menu->appendChild($fragment);
		// 			break;
		// 		}
		// 	}

		// 	// Convertir l'objet DOM modifié en chaîne HTML
		// 	$items = $dom->saveHTML();







		return $items;
		
	
	}


}

new Menus();

namespace sensiness\app;

class Custom_Submenu_Walker extends \Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
	
        $output .= '<ul class="sub-menu">';
		$current_menu = $args->menu;
		$banners = get_field('banner', $current_menu);
		$htmlbanner = '';
		foreach($banners as $banner){
			$attachment_id = get_post_thumbnail_id($banner->ID);

			$desktop = wp_get_attachment_image_src($attachment_id, 'banner_header');
			$desktop_double = wp_get_attachment_image_src($attachment_id, 'banner_header');
			$mobile = wp_get_attachment_image_src($attachment_id, 'banner_header');
			$mobile_double = wp_get_attachment_image_src($attachment_id, 'banner_header');
			$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

			$src = '
				<picture>
				<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)"/>
				<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)"/>
					<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy"/>
				</picture>';

			$title = get_the_title($banner->ID);
			$subtitle = get_field('subtitle',$banner->ID);
			$link = get_field('link',$banner->ID);
			$htmlbanner .='<div class="banner-item">';
				$htmlbanner.=$src;
				$htmlbanner .= '<div class="banner-item__container">';
					$htmlbanner .= '<div class="banner-item__container__left">';
						$htmlbanner .= '<p class="h3">'.$title.'</p>';
						$htmlbanner .= '<p>'.$subtitle.'</p>';
					$htmlbanner .='</div>';
					$htmlbanner .= '<div class="banner-item__container__right">';
						$htmlbanner .='<a class="button" href="'.$link['url'].'">'.$link['title'].'</a>';
					$htmlbanner .='</div>';
				$htmlbanner .= '</div>';
			$htmlbanner .='</div>';
		}
		$output .= '<li class="banner">';
		$output .= $htmlbanner;
		$output .= '</li>';
		$output .= '<li class="menu-list"><ul>';
	



    }
	

    function end_lvl(&$output, $depth = 0, $args = null) {
		$output .= '</ul></li>';

        $output .= '</ul>';
    }

    function start_el(&$output, $item, $depth = null, $args = null, $id = 0) {
		
		if($depth==0){
			$output .= '<li id="menu-item-' . $item->ID . '" class="has-child">';
			$output .=  '<a href="' . $item->url . '">' . $item->title . '</a>';
			$output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<mask id="mask0_155_3613" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
				<rect width="24" height="24" fill="#D9D9D9"/>
				</mask>
				<g mask="url(#mask0_155_3613)">
				<path d="M11.9991 14.7019L6.69141 9.39422L7.39911 8.68652L11.9991 13.2865L16.5991 8.68652L17.3068 9.39422L11.9991 14.7019Z" fill="#131313"/>
				</g>
				</svg>
				';
		}
		else{

			$output .= '<li id="menu-item-' . $item->ID . '">';
			$image = get_field('image', $item->ID);
			if($image) {
				$output .=  '<a href="' . $item->url . '"><span class="menu-icon"><img src="'.$image['url'].'"alt="'.$item->title.'"></span>' . $item->title . '</a>';

			} else {

				$output .=  '<a href="' . $item->url . '">' . $item->title . '</a>';
			}
		}

    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</li>';
    }
}



