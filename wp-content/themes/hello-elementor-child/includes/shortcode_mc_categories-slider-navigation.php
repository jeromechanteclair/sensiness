<?php
/**
 * Displays an automatic navigation of categories
 * 
 * @return string $html : The categories slider navigation HTML
 */
function mc_categories_slider_navigation() {
  $taxonomy     = 'product_cat';
  // $orderby      = 'default';
  $show_count   = 0;      // 1 for yes, 0 for no
  $pad_counts   = 0;      // 1 for yes, 0 for no
  $hierarchical = 0;      // 1 for yes, 0 for no  
  $title        = '';  
  $empty        = 0;

  $args = array(
    'taxonomy'     => $taxonomy,
    // 'orderby'      => $orderby,
    'show_count'   => $show_count,
    'pad_counts'   => $pad_counts,
    'hierarchical' => $hierarchical,
    'title_li'     => $title,
    'hide_empty'   => $empty
  );
  $categories = get_categories( $args );

  $html = '<nav class="mc_categories-slider-navigation"><ul>';

  global $wp_query;
  $queried_object = $wp_query->get_queried_object();

  foreach ($categories as $category) {
    $category_activated = get_field('navigation_slider', $category->taxonomy . '_' . $category->term_id);

    if ( $queried_object && is_a($queried_object, 'WP_Term') ) {
      $category_current = $queried_object->term_id == $category->term_id;
    } else {
      $category_current = false;
    }

    if ( $category_activated ) {
      $category_link = get_term_link( (int)$category->term_id, 'product_cat' );
      if ($category_current) {
        $html .= "<li class='is-current-category'>";
      } else {
        $html .= "<li>";
      }
      $html .= "<a class='mc_category-link' href='{$category_link}'>";

      // $category_thumb_id = get_term_meta( $category->term_id, 'thumbnail_id', true );
      $html .= "<div class='mc_category'>";
      $short_name = str_replace('de CBD', '', str_replace('au CBD', '', $category->name));
      // if ($category_thumb_id) {
      //   $image = wp_get_attachment_url( $category_thumb_id );
      //   $html .= "<img src='{$image}' alt='{$category->name}' width='20' height='20' />";
      //   $html .= "<span class='has-image'>" . $short_name . "</span>";
      // } else {
      //   $html .= "<span>" . $short_name . "</span>";
      // }
      $html .= "<span>" . $short_name . "</span>";
      $html .= "</div>";
      $html .= "</a></li>";
    }
  }
  $html .= '</ul></nav>';

  return $html;
}
// Register shortcode
add_shortcode('mc_categories-slider-navigation', 'mc_categories_slider_navigation');