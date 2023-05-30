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
require_once(get_stylesheet_directory() . '/inc/Woocommerce.php');
require_once(get_stylesheet_directory() . '/inc/Images.php');

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );
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
	$main_script_uri = get_stylesheet_directory_uri() . '/dist/js/main.js';
	wp_enqueue_script( 'index', $main_script_uri, array ( 'jquery' ), '1.0', false);
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



function display_variations($attributes, $available_variations)
{
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
    $prices = array();
    $variations_id = array();
    // $availables = array();

    foreach($available_variations as $key1=> $variation) {
        // if(!$variation['is_in_stock']){
        // 	break;
        // }

        // if( != $attribute_value){}
        $price_html = json_encode($variation['price_html']);
$display_price= $variation['display_price'];
$display_regular_price= $variation['display_regular_price'];



        foreach($variation['attributes'] as $key => $value) {
            if(!array_key_exists($key1, $linkedvariations)) {
                $linkedvariations[$key1]= array();

            }
            array_push($linkedvariations[$key1], $value);
            if(!array_key_exists($key, $options)) {
                $options[$key] = array();
                $prices[$key] = array();
                $variations_id[$key] = array();

                // $linkedvariations[$key] = array();
            }

            if(!in_array($value, $options[$key])
                // && $variation['is_in_stock']
            ) {
                array_push($options[$key], $value);
                array_push($prices[$key], [
                    'html'=>$price_html,
                    'display_price'=>$display_price,
                    'display_regular_price'=>$display_regular_price,

                ]);
                array_push($variations_id[$key], $variation['variation_id']);
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
        //


        // remove 'attribute_' from name
        $attribute_name = str_replace('attribute_', '', $name);
        if(count($values)>0) {
            $html .='<label for="'.$id.'">'.wc_attribute_label($attribute_name).' : </label>';

        }
        $html .= '<select style="display:none;"id="' . $id  . '" class="' . $class  . ' attribute-select" name="' .  $name  . '" data-attribute_name="' . $name . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
        $html .= '<option value="">Sélectionner une option</option>';

        foreach($values as  $value) {


            $selected='';
            if($current == $value) {
                $selected='selected';
            }

            if(!empty($value)) {


                $html .= '<option  value="' . esc_attr($value) . '" '.$selected.'>' . esc_html($value) . '</option>';
            }
        }
        $html .='</select>';
        $html.='<ul class="variation-wrapper">';

        foreach($values as $valuekey => $value) {
            $classes='';
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
                // get term_meta
                $color='';
                if(!empty($term)) {
                    $color = get_term_meta($term->term_id, 'color', true);
                }

                if(!empty($color)) {
                    $html .= '<li class="'.$classes.' colored" data-link="'.$linked.'"  data-value="'.$value.'" data-select="'.$id.'"><span class="color"style="background-color:'.$color.'"></span></li>';

                } else {
                    $html .= '<li class="'.$classes.'" data-variation_id="'. esc_attr($variations_id[$key][$valuekey]) .'" data-price="'. esc_attr($prices[$key][$valuekey]['html']) .'" data-link="'.$linked.'"  data-value="'.$value.'" data-select="'.$id.'">';
                    $html .='<span class="attribute_value">'.$value.'</span>';                    
                    $percentage = round(($prices[$key][$valuekey]['display_regular_price'] - $prices[$key][$valuekey]['display_price']) / $prices[$key][$valuekey]['display_regular_price'] * 100);
                   
                     if(intval($percentage)!==0){
                         $html .='<span class="attribute_prices">'. wc_price(esc_attr($prices[$key][$valuekey]['display_price']) ).'</span>';                    
                         $html .='<span class="attribute_prices">'. wc_price(esc_attr($prices[$key][$valuekey]['display_regular_price'])) .'</span>';    
                         $html .='<span class="attribute_prices">'.$percentage .'%</span>';
                     }
                     else{
                        $html .='<span class="attribute_prices">'. wc_price(esc_attr($prices[$key][$valuekey]['display_regular_price'])) .'</span>';
                     }
             
                     $html .='</li>';
                }
            }
        }
        $html.='</ul>';
    }
    echo $html;

}

