<?php

namespace sensiness\app;

class Menus
{
    public function __construct(){
    

		add_action('after_setup_theme', array($this,'sensiness_menus'), 0);


    }
	
	/**
	 * Menus
	 */
	public function sensiness_menus(){
		register_nav_menus([ 'menu-produits' => esc_html__('Produits', 'hello-elementor') ]);
		register_nav_menus([ 'menu-categories' => esc_html__('Catégories de produit', 'hello-elementor') ]);
		register_nav_menus([ 'menu-besoins' => esc_html__('Besoins', 'hello-elementor') ]);
		register_nav_menus([ 'menu-guidecbd' => esc_html__('Guide CBD', 'hello-elementor') ]);
		register_nav_menus([ 'menu-marque' => esc_html__('La Marque', 'hello-elementor') ]);
		register_nav_menus([ 'menu-account' => esc_html__('Votre compte', 'hello-elementor') ]);
		register_nav_menus([ 'menu-support' => esc_html__('Support', 'hello-elementor') ]);
		register_nav_menus([ 'menu-follow' => esc_html__('Nous suivre', 'hello-elementor') ]);
		register_nav_menus([ 'menu-partenaires' => esc_html__('Partenaires', 'hello-elementor') ]);

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
		
			$current_url = esc_url($_SERVER['REQUEST_URI']);

$site_url = esc_url(get_site_url());


$current_url = str_replace($site_url, '', $current_url);


// Retirer la racine du site de l'URL de la page actuelle

// Obtenir l'URL du lien de menu actuel
$item_url = str_replace($site_url, '', $item->url);


// Vérifier si l'URL du lien de menu est contenue dans l'URL de la page actuelle
$is_current = $current_url==$item_url;


$class_names = $is_current ? 'current' : '';


		if($depth==0){
			$output .= '<li id="menu-item-' . $item->ID . '" class="has-child ' . $class_names . '">';
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

			$output .= '<li id="menu-item-' . $item->ID . '" class="' . $class_names . '">';
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
namespace sensiness\app;

class Categories_Walker extends \Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
	
      
	



    }
	

    function end_lvl(&$output, $depth = 0, $args = null) {


    }

    function start_el(&$output, $item, $depth = null, $args = null, $id = 0) {
		if($item->object =='product_cat'){
			$term_id =$item->object_id;
			$term = get_term($term_id);
			$title =$item->title;
			$attachment_id = get_term_meta(	$term_id, 'thumbnail_id', true);
			
			$desktop = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$desktop_double = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$mobile = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$mobile_double = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

			$src = '
				<picture>
				<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)"/>
				<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)"/>
					<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy"/>
				</picture>';

			$icon = get_field('icon',$term);
	
		

		}
		
	
		$output .= '<li id="menu-item-' . $item->ID . '">';
		$output .=  '<a href="' . $item->url . '">';
		$output.= $src;
		if($icon){

			$output.='<img class="icon" src="'.$icon['url'].'"/>';
		}
		$output .= '<span class="title">'.$item->title .'</span></a>';
		$output .= '</li>';


    }

    function end_el(&$output, $item, $depth =  null, $args = null) {
    



    }
}
namespace sensiness\app;

class Footer_Walker extends \Walker_Nav_Menu {
    private $first_element = true;

    function start_lvl(&$output, $depth = 0, $args = null) {


 
    }
	

    function end_lvl(&$output, $depth = 0, $args = null) {


    }

    function start_el(&$output, $item, $depth = null, $args = null, $id = 0) {

			$arrow 				= '';
			if ($this->first_element) {
			$arrow 				= '<svg class="toggle-sublist" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id="mask0_155_5191" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
			<rect width="24" height="24" fill="#D9D9D9"/>
			</mask>
			<g mask="url(#mask0_155_5191)">
			<path d="M12.0015 14.6538L7.59766 10.25H16.4053L12.0015 14.6538Z" fill="#F5F3E6"/>
			<path d="M12.0015 14.6538L7.59766 10.25H16.4053L12.0015 14.6538Z" fill="#F5F3E6"/>
			</g>
			</svg>
			';


				// C'est le premier élément, faites quelque chose avec lui
				$this->first_element = false; // Mettez à jour la variable pour le prochain élément
			}

		$logo = get_field('image',$item->ID);
		$src ='';
		if($logo && $args->logos) {
			$attachment_id=$logo['ID'];

			$desktop = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$desktop_double = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$mobile = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$mobile_double = wp_get_attachment_image_src($attachment_id, 'menu_cat');
			$image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

			$src = '
							<picture>
						<source srcset="'.$desktop_double[0].' 2x,'.$desktop[0].'" media="(min-width: 999px)"/>
						<source srcset="'.$mobile_double[0].' 2x,'.$mobile[0].'" media="(max-width: 999px)"/>
							<img src="'.$desktop[0].'" alt="'. $image_alt.'" loading="lazy"/>
						</picture>';


		}
				
		$show_in_footer = get_field('show_footer', $item->ID);
		if($show_in_footer) {
			$output .= '<li id="menu-item-' . $item->ID . '">';
			if($item->url!='#'){
	if($logo && $args->logos){
		$output .=  '<a  class="logo" href="' . $item->url . '">';

	}
	else{

		$output .=  '<a href="' . $item->url . '">';
	}
			}
			else{
				$output .="<span>";
			}
			if($logo && $args->logos){

				$output .= ''.$src ;

			}
			else{
				
				$output .= ''.$item->title;
			}
			
			if($item->url != '#') {

				$output .=  '</a>';
			} else {
				$output .= "</span>";
			}
				$output .= $arrow;

			$output .= '</li>';

		}
    }

    function end_el(&$output, $item, $depth =  null, $args = null) {
    

    }
}



