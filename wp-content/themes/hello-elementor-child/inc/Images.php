<?php

namespace sensiness\app;

class Images
{
    public function __construct()
    {

		add_image_size('medium_large', '768', '768', true);
		add_image_size('1536x1536', '1536', '1536', false);
		add_image_size('2048x2048', '2048', '2048', false);
		add_image_size('woocommerce_gallery_thumbnail', '300', '300', true);
		add_image_size('woocommerce_thumbnail', '600', '600', true);
		add_image_size('single_thumbnail', '600', '336', true);
		add_image_size('single_thumbnail_mobile', '358', '200', true);
		add_image_size('single_hero', '500', '350', true);
		// add_image_size('single_hero_mobile', '300', '168', true);
		add_image_size('woocommerce_single', '1024', '0', false);
		add_image_size('product_slide_thumbnail', 62, 62);
		add_image_size('product_slide', 500, 961, true);
		add_image_size('product_slide_double', 1000, 1922, true);
		add_image_size('banner_header', 684, 188, true);
		add_image_size('product_slide_mobile', 428, 740, true);
		add_image_size('product_slide_mobile_double', 856, 1480, true);

    }
}

new Images();
