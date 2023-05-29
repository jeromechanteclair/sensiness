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
		add_image_size('woocommerce_single', '1024', '0', false);

    }
}

new Images();
