<?php
function mc_images_theme_support(){
  add_theme_support( 'woocommerce', array(
    'thumbnail_image_width' => 400,
    'gallery_thumbnail_image_width' => 256,
    'single_image_width' => 1024,
  ) );
}
add_action( 'after_setup_theme', 'mc_images_theme_support' );