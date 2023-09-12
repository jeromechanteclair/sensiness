<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 * @since 1.0.0
 */


 /***
  * Load Inc dependancies
  */
require_once(get_stylesheet_directory() . '/inc/Commands.php');
require_once(get_stylesheet_directory() . '/inc/Woocommerce.php');
require_once(get_stylesheet_directory() . '/inc/Minicart.php');
require_once(get_stylesheet_directory() . '/inc/Menus.php');
require_once(get_stylesheet_directory() . '/inc/Images.php');
require_once(get_stylesheet_directory() . '/inc/Summary.php');
require_once(get_stylesheet_directory() . '/inc/acf.php');
require_once(get_stylesheet_directory() . '/inc/Post.php');

@ini_set('upload_max_size', '80M');
@ini_set('post_max_size', '80M');
@ini_set('max_execution_time', '300');


/***
 * Defer scripts
 */


function add_defer_attribute($tag, $handle)
{
    if ('sensiness-theme' !== $handle) {
        return $tag;
    }
    return str_replace(' src', ' defer="defer" src', $tag);
}

add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);


/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 90 );
// Child theme and scripts loader
function hello_elementor_child_enqueue_scripts() {
	// Theme
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
	wp_enqueue_style(
		'jc-style',
		get_stylesheet_directory_uri() . '/dist/css/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
	// Scripts
	$vendor_script_uri = get_stylesheet_directory_uri() . '/dist/js/vendor.js';
	wp_enqueue_script( 'sensiness-theme-vendor', $vendor_script_uri,[], '1.0', false);
	$manifest_script_url = get_stylesheet_directory_uri() . '/dist/js/manifest.js';
	wp_enqueue_script( 'sensiness-theme-manifest', $manifest_script_url,[], '1.0', false);
	$main_script_uri = get_stylesheet_directory_uri() . '/dist/js/main.js';
	wp_enqueue_script( 'sensiness-theme', $main_script_uri,[], '1.0', false);
}

/**
 *	Include custom functions & modules
 */
// Add default product to cart for variable products quick ajax add to cart
// include get_stylesheet_directory() . '/includes/add_default_to_cart.php';

// Change the way prices are displayed for variable products
include get_stylesheet_directory() . '/includes/custom_variation_prices.php';

// Change the add to cart text
include get_stylesheet_directory() . '/includes/change_add_to_cart_text.php';

// Change the product images sizes
// include get_stylesheet_directory() . '/includes/product_images_sizes.php';

// Add secondary product image that swap on hover
include get_stylesheet_directory() . '/includes/product_loops_image_swap.php';

// Change the global product price according to the current variation price on the product page
include get_stylesheet_directory() . '/includes/product_variation_price.php';

// Displays an automatic navigation of categories
include get_stylesheet_directory() . '/includes/shortcode_mc_categories-slider-navigation.php';

// Displays an automatic navigation of effects
include get_stylesheet_directory() . '/includes/shortcode_mc_effects-slider-navigation.php';


/**
 * Custom variation select output
 */
function display_variations($attributes, $available_variations){

    global $product;
    // get default variation
    if($product->is_type('variable')) {
        $default_attributes = $product->get_default_attributes();

        foreach($default_attributes as $key => $value) {
            $default_attributes['attribute_'.$key] = $value;
        }

    }

    $options = array();
    $linkedvariations = array();
    $datas = array();
    $datas = array();
    $variations_id = array();



    foreach($available_variations as $key1=> $variation) {
        // remove attr if not in stock
        if(!$variation['is_in_stock']){
        	break;
        }


        // if( != $attribute_value){}
        $price_html = json_encode($variation['price_html']);
        $display_price= $variation['display_price'];
        $display_regular_price= $variation['display_regular_price'];
        $description = $variation['variation_description'];

        foreach($variation['attributes'] as $key => $value) {


            if(!array_key_exists($key1, $linkedvariations)) {
                $linkedvariations[$key1]= array();

            }
            array_push($linkedvariations[$key1], $value);
            if(!array_key_exists($key, $options)) {
                $options[$key] = array();
                $datas[$key] = array();
                $variations_id[$key] = array();


                // $linkedvariations[$key] = array();
            }

            if(!in_array($value, $options[$key])
                // && $variation['is_in_stock']
            ) {
                array_push($options[$key], $value);
                array_push($datas[$key], [
                    'html'=>$price_html,
                    'display_price'=>$display_price,
                    'display_regular_price'=>$display_regular_price,
                    'availability'=>$variation['is_in_stock'],
                    'description'=> get_post_meta($variation['variation_id'], 'variation_description', true)?get_post_meta($variation['variation_id'], 'variation_description', true):'',
                    'variation_id'=>$variation['variation_id'],
                    'availability_html'=> json_encode($variation['availability_html']),
                ]);

                array_push($variations_id[$key], );
            }

        }
    }
  
    // var_dump($options );

    $html ='';

    foreach($options as $key =>$values) {

        $show_option_none=true;
        $name = $key ;
        $id = $class= $name ;
        $current='';

        if(!empty($options[$key])) {
            $current =$options[$key][0];
        }

        if(empty($options[$key])) {
            break ;
        }
        $current =$options[$key][0];
        if(!empty($default_attributes) && in_array($default_attributes[$key], $values)) {
            $current = $default_attributes[$key];
        }
        $attribute_name = str_replace('attribute_', '', $name);
        if(count($values)>0) {
            $html .='<label style="display:none;"for="'.$id.'">'.wc_attribute_label($attribute_name).' : </label>';

        }
        $html .= '<select style="display:none;" id="' . $id  . '" class="' . $class  . ' attribute-select custom-select" name="' .  $name  . '" data-attribute_name="' . $name . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';

        foreach($values as $valuekey => $value) {
            $selected='';
            $term = get_term_by('slug', $value, $attribute_name);
            if(!$term){
                break;
            }
            $name = $term->name;
            if($current == $value) {
                $selected='selected';
            }
            if(!empty($value)) {
                $attr='';
            if(isset($datas[$key][$valuekey]['display_regular_price']) && $datas[$key][$valuekey]['display_regular_price'] !=0) {


                $percentage = round(($datas[$key][$valuekey]['display_regular_price'] - $datas[$key][$valuekey]['display_price']) / $datas[$key][$valuekey]['display_regular_price'] * 100);
                if(intval($percentage)!==0) {
                    $attr .=' data-price-promo="'. htmlspecialchars(json_encode(wc_price(esc_attr($datas[$key][$valuekey]['display_price']))), ENT_QUOTES, 'UTF-8').'"';
                    $attr .=' data-price-reg="'. htmlspecialchars(json_encode(wc_price(esc_attr($datas[$key][$valuekey]['display_regular_price']))), ENT_QUOTES, 'UTF-8').'"';
                } else {
                    $attr .=' data-price-promo=""';
                    $attr .=' data-price-reg="'.  htmlspecialchars(json_encode(wc_price(esc_attr($datas[$key][$valuekey]['display_regular_price']))), ENT_QUOTES, 'UTF-8').'"';
                }
            }
                $html .= '<option  value="' . esc_attr($value) . '" '.$selected.' '. $attr.'>';
                $html .= esc_html($name) ;
                
             

                $html .= '</option>';
            }
        }
        $html .='</select>';
        $html.='<ul class="variation-wrapper">';

        foreach($values as $valuekey => $value) {
            $classes='variation-item';
            $linked='';
            if($product->get_type() =='pw-gift-card') {
                $classes='inline';
            }
            if($current == $value) {
                $classes.=' selected';
            }
            if(!empty($value)) {

                foreach($linkedvariations as $linkedvariation) {
                    if(in_array($value, $linkedvariation)) {
                        // var_dump($linkedvariation);
                        $currentstring = implode('', $linkedvariation);
                        // remove value from current string
                        $currentstring = str_replace($value, ' ', $currentstring);
                        // remove last comma from current string
                        $linked.= $currentstring;
                    }
                }
                $term = get_term_by('slug', $value, $attribute_name);
                $name = $term->name;
               

                // get term_meta
                $color='';
                if(!empty($term)) {
                    $color = get_term_meta($term->term_id, 'color', true);
                }

                if(!empty($color)) {
                    $html .= '<li class="'.$classes.' colored" data-link="'.$linked.'"  data-value="'.$value.'" data-select="'.$id.'"><span class="color"style="background-color:'.$color.'"></span></li>';

                } else {
                    $html .= '<li class="'.$classes.'" data-variation_id="'. esc_attr($datas[$key][$valuekey]['variation_id']) .'" data-price="'. esc_attr($datas[$key][$valuekey]['html']) .'" data-link="'.$linked.'"  data-value="'.$value.'" data-select="'.$id.'">';
                        $html .='<span class="checkbox"></span>';                    
                        $html .='<div class="variation-item__title"><p class="h3">'.$name.'</p>';                    
                            $html .='<p class="description">'.$datas[$key][$valuekey]['description'].'</p>';
                        $html .='</div>';   
                        $html .='<div class="variation-item__prices">';  
                        if(isset($datas[$key][$valuekey]['display_regular_price']) && $datas[$key][$valuekey]['display_regular_price'] !=0) {

                            $percentage = round(($datas[$key][$valuekey]['display_regular_price'] - $datas[$key][$valuekey]['display_price']) / $datas[$key][$valuekey]['display_regular_price'] * 100);
                        }
                        else{
                        $percentage=0;
                        } if(intval($percentage)!==0){
                                $html .='<span class="display_regular_price">'. wc_price(esc_attr($datas[$key][$valuekey]['display_regular_price'])) .'</span>';    
                                $html .='<span class="display_price">'. wc_price(esc_attr($datas[$key][$valuekey]['display_price']) ).'</span>';                    
                                $html .='<span class="percent">-'.$percentage .'%</span>';
                            }
                            else{
                                $html .='<span class="display_price">'. wc_price(esc_attr($datas[$key][$valuekey]['display_regular_price'])) .'</span>';
                            }
                        $html .='</div>';

                    $html .='</li>';
                }
            }
        }
        $html.='</ul>';
    }
    echo $html;

}

/***
 * Options page ACF
 */

 
if(function_exists('acf_add_options_page')) {

    acf_add_options_page(array(
        'page_title'    => 'Réglages généraux',
        'menu_title'    => 'Réglages généraux',
        'menu_slug'     => 'acf-general-settings',
        'capability'    => 'edit_posts',
    ));


}


/**
 * Proper ob_end_flush() for all levels
 *
 * This replaces the WordPress `wp_ob_end_flush_all()` function
 * with a replacement that doesn't cause PHP notices.
 */
remove_action('shutdown', 'wp_ob_end_flush_all', 1);
add_action('shutdown', function () {
    while (@ob_end_flush());
});


/**
 * Reading time
 */

function reading_time()
{
    global $post;
    $content = get_post_field('post_content', $post->ID);
    $word_count = str_word_count(strip_tags($content));
    $readingtime = ceil($word_count / 200);

    if ($readingtime == 1) {
        $timer = " minute";
    } else {
        $timer = " minutes";
    }
    $totalreadingtime = $readingtime . $timer.' de lecture';

    return $totalreadingtime;
}

