<?php

namespace sensiness\app;

class Menus
{
    public function __construct(){
    

		add_action('after_setup_theme', array($this,'sensiness_menus'), 0);
		add_filter('wp_nav_menu',  array($this,'add_banner_to_menu'), 10, 2);


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
	public function add_banner_to_menu($items, $args ) {
    
		// get menu
		$menu = wp_get_nav_menu_object($args->menu);
		// add banner to DOM
		$banners = get_field('banner', $menu);
		$htmlbanner = '';
		foreach($banners as $banner){
			$attachment_id = get_post_thumbnail_id($banner->ID);
			
			$desktop = wp_get_attachment_image_src($attachment_id, 'product_slide');
			$desktop_double = wp_get_attachment_image_src($attachment_id, 'product_slide_double');
			$mobile = wp_get_attachment_image_src($attachment_id, 'product_slide_mobile');
			$mobile_double = wp_get_attachment_image_src($attachment_id, 'product_slide_mobile_double');
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
			
			$htmlbanner.=$src;


		}
	


			$dom = new \DOMDocument();
			$dom->loadHTML($items, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

			// Trouver le premier sous-menu
			$sub_menus = $dom->getElementsByTagName('ul');
			foreach ($sub_menus as $sub_menu) {
				if ($sub_menu->getAttribute('class') == 'sub-menu') {
					// Ajouter l'élément HTML juste avant la fermeture du sous-menu
					$element_html = '<li>'.$htmlbanner.'</li>';
					
					$fragment = $dom->createDocumentFragment();
					$fragment->appendXML($element_html);
					$sub_menu->appendChild($fragment);
					break;
				}
			}

			// Convertir l'objet DOM modifié en chaîne HTML
			$items = $dom->saveHTML();







		return $items;
		
		// return $items= $items.$htmlbanner;
			
		
			
			
			
			
		
		
		
		// return
		return $items;
		
	}


}

new Menus();
