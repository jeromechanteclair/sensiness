<?php

class AcfBlocks
{
    public function __construct()
    {
        if (function_exists('acf_register_block_type')) {
            add_action('allowed_block_types_all', array($this,'allowed_block_types'), 10, 2);
            add_action('acf/init', array($this,'register_acf_blocks'), 10);
        }

        add_filter('block_categories_all', [$this, 'createCategoryBlocks'], 10, 2);
    }

    /**
     * register_acf_blocks
     * Declare acf block with associate view
     *
     * @return void
     */
    public function register_acf_blocks()
    {

        acf_register_block_type([
            'name' => 'bandeau',
            'title' => __('bandeau'),
            'description' => __('bandeau'),
            'render_template' => 'block/bandeau',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('bandeau')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'hero-video',
            'title' => __('hero vidéo'),
            'description' => __('hero vidéo'),
            'render_template' => 'block/hero-video',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('hero vidéo')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'bandeau-marquee',
            'title' => __('Bandeau marquee'),
            'description' => __('Bandeau marquee'),
            'render_template' => 'block/bandeau-marquee',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Bandeau marquee')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-products',
            'title' => __('Mise en avant de produits'),
            'description' => __('Mise en avant de produits'),
            'render_template' => 'block/highlights-products',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Mise en avant de produits')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-besoins',
            'title' => __('Mise en avant des besoins'),
            'description' => __('Mise en avant des besoins'),
            'render_template' => 'block/highlights-besoins',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Mise en avant des besoins')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-pages',
            'title' => __('Mise en avant de pages'),
            'description' => __('Mise en avant de pages'),
            'render_template' => 'block/highlights-pages',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Mise en avant de pages ')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-reviews',
            'title' => __('Mise en avant d\'avis'),
            'description' => __('Mise en avant  d\'avis'),
            'render_template' => 'block/highlights-reviews',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Mise en avant  d\'avis ')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-testimonials',
            'title' => __('Témoignages'),
            'description' => __('Témoignages'),
            'render_template' => 'block/highlights-testimonials',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Témoignages')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
        acf_register_block_type([
            'name' => 'highlights-posts',
            'title' => __('Mise en avant  d\'articles '),
            'description' => __('Mise en avant  d\'articles'),
            'render_template' => 'block/highlights-posts',
            'category' => 'blocks_sensiness',
            'icon' => 'format-aside',
            'keyword' => [__('Mise en avant  d\'articles')],
            'mode' => 'edit',
            'render_callback' => array($this,'acf_render'),
        ]);
     
    }

    /**
     * allowed_block_types
     * filter blocks on backend
     *
     * @param [type] $allowedBlocks
     * @param [type] $post
     * @return void
     */
    public function allowed_block_types($allowedBlocks, $post)
    {
		$registered_blocks = array_keys(WP_Block_Type_Registry::get_instance()->get_all_registered());

		$registered_blocks[]='bandeau';
		$registered_blocks[]='bandeau-marquee';
		$registered_blocks[]='hero-video';
		$registered_blocks[]='highlights-products';
		$registered_blocks[]='highlights-pages';
		$registered_blocks[]='highlights-besoins';
		$registered_blocks[]='highlights-testimonials';
		$registered_blocks[]='highlights-reviews';
		$registered_blocks[]='highlights-posts';
           

        return $registered_blocks;

    }

    /**
     * acf_render
     * Render callback function for acf blocks
     *
     *
     * @param [type] $block
     * @param [type] $content
     * @param [type] $is_preview
     * @param [type] $post_id
     * @return void
     */
    public function acf_render($block, $content, $is_preview, $post_id)
    {

        $args=[

                'data' => $block['data'],
                'content' => $content,
                'is_preview' => $is_preview,
                'post_id' => $post_id,

        ];


        get_template_part('template-parts/'.$block['render_template'], '', $args);

    }
    public function createCategoryBlocks($categories)
    {

        $customCategories = [];

        $customCategories[] = [
            'slug' => 'blocks_sensiness',
            'title' => __('Blocs Sensiness', 'sensiness'),
        ];


        return array_merge(
            $categories,
            $customCategories
        );
    }
}
new AcfBlocks();
