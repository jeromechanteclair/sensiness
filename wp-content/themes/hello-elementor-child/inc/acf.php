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
