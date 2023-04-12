<?php
if( !function_exists('wpce_get_attachment_alt') ){
	function wpce_get_attachment_alt( $attachment_id ){
		if ( ! $attachment_id ) {
			return '';
		}

		$attachment = get_post( $attachment_id );
		if ( ! $attachment ) {
			return '';
		}

		$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( ! $alt ) {
			$alt = $attachment->post_excerpt;
			if ( ! $alt ) {
				$alt = $attachment->post_title;
			}
		}
		return trim( strip_tags( $alt ) );
	}
}

if( !function_exists('wpce_get_product_types') ){
	function wpce_get_product_types(){
		$product_types_lists = wc_get_product_types();
		return $product_types_lists;
	}
}

if( !function_exists('wpce_get_post_status') ){
	function wpce_get_post_status(){
		$post_statuses = array();
		$post_statuses['any'] = esc_html__('Any', 'wpce');
		$post_statuses = get_post_statuses();
		return $post_statuses;
	}
}

if( !function_exists('wpce_get_product_lists') ){
	function wpce_get_product_lists(){
		$product_lists = array();
		
		$args = array(
			'numberposts'=>-1,
			'return' => 'ids',
		);
		$products_lists = wc_get_products($args);
		if( is_array($products_lists) && !empty($products_lists) ){
			foreach( $products_lists as $index=>$id ){
				$product_lists[$id] = get_the_title($id);
			}
		}
		 return $product_lists;
	}
}

if( !function_exists('wpce_get_product_cats') ){
	function wpce_get_product_cats( $category='product_cat' ){
		$product_categories_list = array();
		$args = array(
		    'taxonomy'   => $category,
		);
		$args = apply_filters( 'wpce_get_product_cat_args', $args );
		$product_categories = get_terms($args);
		
		if( !empty($product_categories) ){
			foreach ($product_categories as $cat) {
				$product_categories_list[$cat->slug] = $cat->name;
			}
		}
		return $product_categories_list;
	}
}

if( !function_exists('wpce_get_excerpt') ){

	function wpce_get_excerpt( $args = array() ) {

		// Defaults
		$defaults = array(
			'post'            => '',
			'length'          => 40,
			'readmore'        => false,
			'readmore_text'   => esc_html__( 'read more', 'text-domain' ),
			'readmore_after'  => '',
			'custom_excerpts' => true,
			'disable_more'    => false,
		);

		// Apply filters
		$defaults = apply_filters( 'wpce_get_excerpt_defaults', $defaults );

		// Parse args
		$args = wp_parse_args( $args, $defaults );

		// Apply filters to args
		$args = apply_filters( 'wpce_get_excerpt_args', $defaults );

		// Extract
		extract( $args );

		// Get global post data
		if ( ! $post ) {
			global $post;
		}

		// Get post ID
		$post_id = $post->ID;

		// Check for custom excerpt
		if ( $custom_excerpts && has_excerpt( $post_id ) ) {
			$output = $post->post_excerpt;
		}

		// No custom excerpt...so lets generate one
		else {
			// Readmore link
			$readmore_link = '<a href="' . get_permalink( $post_id ) . '" class="readmore">' . $readmore_text . $readmore_after . '</a>';
			// Check for more tag and return content if it exists
			if ( ! $disable_more && strpos( $post->post_content, '<!--more-->' ) ) {
				$output = apply_filters( 'the_content', get_the_content( $readmore_text . $readmore_after ) );
			}
			// No more tag defined so generate excerpt using wp_trim_words
			else {
				// Generate excerpt
				$output = wp_trim_words( strip_shortcodes( $post->post_content ), $length );

				// Add readmore to excerpt if enabled
				if ( $readmore ) {
					$output .= apply_filters( 'wb_readmore_link', $readmore_link );
				}
			}
		}
		// Apply filters and echo
		return apply_filters( 'wpce_get_excerpt', $output );
	}
}

function wpce_display_product_rating( $average, $rating_count, $id ) {
    if ( 0 == $average ) {
		$html  = '<div class="star-rating">';
		$html .= wc_get_star_rating_html( $average, $rating_count );
		$html .= '</div>';
		return $html;
	}else{
		return wc_get_rating_html( $average, $rating_count );
	}
}