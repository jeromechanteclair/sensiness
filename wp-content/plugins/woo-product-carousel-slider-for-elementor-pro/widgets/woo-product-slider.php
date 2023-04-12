<?php
namespace WB_WPCE\PRODUCT_CAROUSEL;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
/**
 * Elementor Post Slider Slider Widget.
 *
 * Main widget that create the Post Slider widget
 *
 * @since 1.0.0
*/
class WB_WPCE_WIDGET extends \Elementor\Widget_Base
{

	/**
	 * Get widget name
	 *
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpce-slider';
	}

	/**
	 * Get widget title
	 *
	 * Retrieve the widget title.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html( 'Product Slider', 'wpce' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	public function get_style_depends()
    {
        return [
            'font-awesome-5-all',
            'font-awesome-4-shim',
        ];
    }

    public function get_script_depends()
    {
        return [
            'font-awesome-4-shim'
        ];
    }

	/**
	 * Retrieve the widget category.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_categories() {
		return [ 'web-builder-element' ];
	}

	/**
	 * Retrieve the widget category.
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'query_configuration',
			[
				'label' => esc_html( 'Query Builder', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


        $this->add_control(
			'post_status',
			[
				'label' => esc_html__( 'Post Status', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Post Status', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'publish',
				'multiple' => true,
				'options' => wpce_get_post_status(),
			]
		);

		$this->add_control(
			'product_types',
			[
				'label' => esc_html__( 'Product Types', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Products to Include', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'default' => '',
				'options' => wpce_get_product_types(),
			]
		);

		$this->add_control(
			'product_cats',
			[
				'label' => esc_html__( 'Categories', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Categories to Include', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'default' => '',
				'options' => wpce_get_product_cats('product_cat'),
			]
		);

		$this->add_control(
			'product_tags',
			[
				'label' => esc_html__( 'Tags', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Tags to Include', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'default' => '',
				'options' => wpce_get_product_cats('product_tag'),
			]
		);

		$product_lists = wpce_get_product_lists();
		$this->add_control(
			'include_products_posts',
			[
				'label' => esc_html__( 'Include Products', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Products to Include', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'default' => '',
				'options' => $product_lists,
			]
		);

		$this->add_control(
			'exclude_products_posts',
			[
				'label' => esc_html__( 'Exclude Products', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Products to Exclude', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'default' => '',
				'options' => $product_lists,
			]
		);

		$this->add_control(
			'exclude_current_product',
			[
				'label' => esc_html__( 'Exclude Current Product', 'post-slider-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'post-slider-for-elementor' ),
				'label_off' => esc_html__( 'No', 'post-slider-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'display_featured_only',
			[
				'label' => esc_html__( 'Display Featured Products Only', 'post-slider-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'post-slider-for-elementor' ),
				'label_off' => esc_html__( 'No', 'post-slider-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'display_on_sale_only',
			[
				'label' => esc_html__( 'Display On Sale Only', 'post-slider-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'post-slider-for-elementor' ),
				'label_off' => esc_html__( 'No', 'post-slider-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

        $this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Limit', 'wpce' ),
				'placeholder' => esc_html__( 'Default is 10', 'wpce' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => -1,
				'default' => 10,
			]
		);

		$this->add_control(
			'order_by_products_posts',
			[
				'label' => esc_html__( 'Order By', 'wpce' ),
				// 'placeholder' => esc_html__( 'Choose Products to Exclude', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => false,
				'multiple' => false,
				'default' => 'date',
				'options' => [
					'date'	=>	'Date',
					'modified'	=>	'Modified Date',
					'id'	=>	'ID',
					'name'	=>	'Name',
					'type'	=>	'Product Type',
					'menu_order'=>	'Menu Order',
					'rand'	=>	'Random',
				],
			]
		);

		$this->add_control(
			'order_products_posts',
			[
				'label' => esc_html__( 'Order', 'wpce' ),
				// 'placeholder' => esc_html__( 'Choose Products to Exclude', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => false,
				'multiple' => false,
				'default' => 'DESC',
				'options' => [
					'DESC'	=>	'Descending',
					'ASC'	=>	'Ascending',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'item_configuration',
			[
				'label' => esc_html( 'Item Configurtion', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'template_style',
			[
				'label' => esc_html__( 'Template Style', 'wpce' ),
				'placeholder' => esc_html__( 'Choose Template from Here', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => esc_html__( 'Default', 'wpce' ),
				],
			]
		);

		$this->add_control(
			'display_image',
			[
				'label' => esc_html__( 'Show Image', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail_size',
				'default' => 'medium_large',
				'condition' => [
					'display_image'	=>	'yes',
				]
			]
		);

		$this->add_control(
			'display_title',
			[
				'label' => esc_html__( 'Display Title', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		/*$this->add_control(
			'display_content',
			[
				'label' => esc_html__( 'Display Content', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);*/

		$this->add_control(
			'display_rating',
			[
				'label' => esc_html__( 'Display Ratings', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'display_price',
			[
				'label' => esc_html__( 'Display Price', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'display_read_more',
			[
				'label' => esc_html__( 'Display Add to Cart Button', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'display_read_more2',
			[
				'label' => esc_html__( 'Display Read More Button', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label' => __( 'Read More:', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Read More', 'post-slider-for-elementor'),
				'description'	=>	'Change Read More Text from Here',
				'condition' => [
					'display_read_more2'	=>	'yes',
				]
			]
		);

		$this->add_control(
			'item_spacing',
			[
				'label' => __( 'Item Spacing', 'wpce' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpce_item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '15',
					'right' => '15',
					'bottom' => '15',
					'left' => '15',
					'unit' => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'slider_configuration',
			[
				'label' => esc_html( 'Slider Configurtion', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'display_navigation_arrows',
			[
				'label' => esc_html__( 'Display Navigation Arrows', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'display_dots',
			[
				'label' => esc_html__( 'Display Dots', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'infinite_loop',
			[
				'label' => esc_html__( 'Infinite Loop', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__( 'AutoPlay', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__( 'AutoPlay Speed', 'wpce' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '3000',
				'condition' => [
					'autoplay' => 'yes'
				]
			]
		);

		/*$this->add_control(
			'pauseOnFocus',
			[
				'label' => esc_html__( 'Pause On Focus', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);*/

		$this->add_control(
			'pauseOnHover',
			[
				'label' => esc_html__( 'Pause On Hover', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'pauseOnDotsHover',
			[
				'label' => esc_html__( 'Pause On Dots Hover', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'wpce' ),
				'label_off' => esc_html__( 'No', 'wpce' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

		$this->add_control(
			'slide_speed',
			[
				'label' => esc_html__( 'Slide Speed', 'wpce' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => '1000',
			]
		);

		$this->add_control(
			'slide_to_show',
			[
				'label' => __( 'Slides to Show', 'wpce' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
			]
		);

		$this->add_control(
			'slides_to_scroll',
			[
				'label' => __( 'Slides to Scroll', 'wpce' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 3,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'responsive_control',
			[
				'label' => esc_html( 'Responsive Configurtion', 'post-slider-for-elementor' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();  //Start Responsive Slider Control Section

		$repeater->add_control(
			'slide_to_show', [
				'label' => __( 'Slides to Show', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => __( 2 , 'plugin-domain' ),
				'label_block' => false,
				'separator'=> 'before',
			]
		);

		$repeater->add_control(
			'slides_to_scroll', [
				'label' => __( 'Slides to Scroll', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => __( 2 , 'plugin-domain' ),
				'label_block' => false,
				'separator'=> 'before',
			]
		);

		$repeater->add_control(
			'breakpoint_size', [
				'label' => __( 'Breakpoint Width', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 769 , 'plugin-domain' ),
				'label_block' => false,
				'separator'=> 'before',
			]
		);

		/*$repeater->add_control(
			'display_navigation_arrows', [
				'label' => esc_html__( 'Display Navigation Arrows', 'post-slider-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'post-slider-for-elementor' ),
				'label_off' => esc_html__( 'No', 'post-slider-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$repeater->add_control(
			'display_dots',
			[
				'label' => esc_html__( 'Display Dots', 'post-slider-for-elementor' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'post-slider-for-elementor' ),
				'label_off' => esc_html__( 'No', 'post-slider-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);*/

		$this->add_control(
			'slider_responsive_control',
			[
				'label' => __( 'Responsive Breakpoints', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'separator' => 'before',
				'default' => [
					[
						'slide_to_show' => __( 2, 'plugin-domain' ),
						'slides_to_scroll' => __( 2, 'plugin-domain' ),
						'breakpoint_size' => __( 768, 'plugin-domain' ),
					],
					[
						'slide_to_show' => __( 1, 'plugin-domain' ),
						'slides_to_scroll' => __( 1, 'plugin-domain' ),
						'breakpoint_size' => __( 480, 'plugin-domain' ),
					],
				],
				'title_field' => '{{{ breakpoint_size }}}',
				'prevent_empty'=>false,
			]
		); 

		$this->end_controls_section(); // End Reponsive Carousel Control Section

		$this->start_controls_section(
			'title_style_section',
			[
				'label' => esc_html( 'Title Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce_title h2,{{WRAPPER}} .wpce_title h2 a',
			]
		);

		$this->add_control(
			'title_margin',
			[
				'label' => __( 'Margin', 'wpce' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpce_title h2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '',
					'right' => '',
					'bottom' => '12',
					'left' => '',
					'unit' => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_control(
			'title_text_align',
			[
				'label' => esc_html__( 'Text Align', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'center',
				'options' => [
					'left'	=> 'Left',
					'center'	=> 'Center',
					'right'	=> 'Right',
					'justify'	=> 'Justify',
				],
			]
		);	

		$this->start_controls_tabs(
			'title_link_tabs'
		);

		$this->start_controls_tab(
			'title_link_normal_tab',
			[
				'label' => __( 'Normal', 'wpce' ),
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_title a' => 'color: {{VALUE}}',
				],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'title_link_hover_tab',
			[
				'label' => __( 'Hover', 'wpce' ),
			]
		);
		$this->add_control(
			'title_hover_color',
			[
				'label' => __( 'Hover Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_title a:hover' => 'color: {{VALUE}}',
				],
			]
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
/*
		$this->start_controls_section(
			'content_style_section',
			[
				'label' => esc_html( 'Content Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_description' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce_description',
			]
		);

		$this->add_control(
			'content_margin',
			[
				'label' => __( 'Margin', 'wpce' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpce_text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '',
					'right' => '',
					'bottom' => '',
					'left' => '',
					'unit' => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_control(
			'content_text_align',
			[
				'label' => esc_html__( 'Text Align', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'center',
				'options' => [
					'left'	=> 'Left',
					'center'	=> 'Center',
					'right'	=> 'Right',
					'justify'	=> 'Justify',
				],
			]
		);	

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce_content',
			]
		);

		$this->end_controls_section();
		*/

		$this->start_controls_section(
			'rating_style_section',
			[
				'label' => esc_html( 'Star Rating Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'rating_default_color',
			[
				'label' => __( 'Default Star Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'separator'=> 'after',
				'selectors' => [
					'{{WRAPPER}} .wpce-rating .star-rating::before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'rating_fill_color',
			[
				'label' => __( 'Positive Star Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'separator'=> 'after',
				'selectors' => [
					'{{WRAPPER}} .wpce-rating .star-rating span::before' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'price_style_section',
			[
				'label' => esc_html( 'Price Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __( 'Price Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'separator'=> 'after',
				'selectors' => [
					'{{WRAPPER}} .wpce_price' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce_price',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'cart_btn_style_section',
			[
				'label' => esc_html( 'Cart Button Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs(
			'cart_btn_style_tabs'
		);

		$this->start_controls_tab(
			'cart_btn_normal_tab',
			[
				'label' => __( 'Normal', 'wpce' ),
			]
		);

		$this->add_control(
			'cart_btn_text_align',
			[
				'label' => esc_html__( 'Button Align', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'center',
				'options' => [
					'left'	=> 'Left',
					'center'	=> 'Center',
					'right'	=> 'Right',
					'justify'	=> 'Justify',
				],
			]
		);	

		$this->add_control(
			'cart_btn_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_add_to_cart_btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'cart_btn_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce_add_to_cart_btn',
			]
		);

		$this->add_control(
			'cart_btn_padding',
			[
				'label' => __( 'Padding', 'wpce' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpce_add_to_cart_btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '10',
					'right' => '15',
					'bottom' => '10',
					'left' => '15',
					'unit' => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'cart_btn_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_add_to_cart_btn',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'cart_btn_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce_add_to_cart_btn',
			]
		);

		$this->add_control(
			'ajax_added_to_cart_btn_color',
			[
				'label' => __( 'Added to Cart Text Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .added_to_cart' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cart_btn_hover_tab',
			[
				'label' => __( 'Hover', 'wpce' ),
			]
		);

		$this->add_control(
			'cart_btn_hover_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_add_to_cart_btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'cart_btn_hover_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_add_to_cart_btn:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'cart_btn_hover_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce_add_to_cart_btn:hover',
			]
		);

		$this->add_control(
			'ajax_added_to_cart_btn_hover_color',
			[
				'label' => __( 'Added to Cart Text Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .added_to_cart:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();


		// Start Read More Style Controle
		$this->start_controls_section(
			'read_more_style_section',
			[
				'label' => esc_html( 'Read More Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs(
			'read_more_style_tabs'
		);

		$this->start_controls_tab(
			'read_more_normal_tab',
			[
				'label' => __( 'Normal', 'wpce' ),
			]
		);

		$this->add_control(
			'read_more_text_align',
			[
				'label' => esc_html__( 'Button Align', 'wpce' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'center',
				'options' => [
					'left'	=> 'Left',
					'center'	=> 'Center',
					'right'	=> 'Right',
					'justify'	=> 'Justify',
				],
			]
		);	

		$this->add_control(
			'read_more_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_more_btn' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'read_more_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce_more_btn',
			]
		);

		$this->add_control(
			'read_more_padding',
			[
				'label' => __( 'Padding', 'wpce' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .wpce_more_btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '10',
					'right' => '15',
					'bottom' => '10',
					'left' => '15',
					'unit' => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'read_more_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_more_btn',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'read_more_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce_more_btn',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'read_more_hover_tab',
			[
				'label' => __( 'Hover', 'wpce' ),
			]
		);

		$this->add_control(
			'read_more_hover_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce_more_btn:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'read_more_hover_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_more_btn:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'read_more_hover_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce_more_btn:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
		// End Read More Style Control


		// Arrow Style
		$this->start_controls_section(
			'nav_arrow_style_section',
			[
				'label' => esc_html( 'Navigation Arrow Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);


		$this->start_controls_tabs(
			'nav_arrow_style_tabs'
		);

		$this->start_controls_tab(
			'nav_arrow_normal_tab',
			[
				'label' => __( 'Normal', 'wpce' ),
			]
		);

		$this->add_control(
			'nav_arrow_width',
			[
				'label' => __( 'Width', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpce-arrow' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nav_arrow_height',
			[
				'label' => __( 'Height', 'plugin-domain' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .wpce-arrow' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nav_arrow_left_icon',
			[
				'label' => __( 'Left Icon', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fa fa-angle-left',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'nav_arrow_right_icon',
			[
				'label' => __( 'Right Icon', 'text-domain' ),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fa fa-angle-right',
					'library' => 'solid',
				],
			]
		);

		$this->add_control(
			'nav_arrow_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce-arrow' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'nav_arrow_typography',
				'label' => __( 'Typography', 'wpce' ),
				'selector' => '{{WRAPPER}} .wpce-arrow',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'nav_arrow_border',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce-arrow',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'nav_arrow_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce-arrow',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'nav_arrow_hover_tab',
			[
				'label' => __( 'Hover', 'wpce' ),
			]
		);

		$this->add_control(
			'nav_arrow_hover_color',
			[
				'label' => __( 'Color', 'wpce' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpce-arrow:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'nav_arrow_border_hover',
				'label' => __( 'Border', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce-arrow:hover',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'nav_arrow_hover_background',
				'label' => __( 'Background', 'plugin-domain' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wpce-arrow:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'others_style_section',
			[
				'label' => esc_html( 'Others Style', 'wpce' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'full_content_box_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_single_item',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'image_box_shadow',
				'label' => __( 'Image Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .wpce_thumbnail img',
			]
		);

		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$element_id = 'wb_wpce_'.$this->get_id();

		$template_style = $settings['template_style'];
		$display_dots = $settings['display_dots'];
		$autoplay = $settings['autoplay'];
		// $pauseOnFocus = $settings['pauseOnFocus'];
		$pauseOnFocus = 'true';
		$pauseOnHover = $settings['pauseOnHover'];
		$pauseOnDotsHover = $settings['pauseOnDotsHover'];
		$slide_to_show = isset($settings['slide_to_show']) && $settings['slide_to_show'] ? $settings['slide_to_show'] : 3;
		$slides_to_scroll = isset($settings['slides_to_scroll']) && $settings['slides_to_scroll'] ? $settings['slides_to_scroll'] : 3;
		$autoplay_speed = isset($settings['autoplay_speed']) && $settings['autoplay_speed'] ? $settings['autoplay_speed'] : 3000;
		$slide_speed = isset($settings['slide_speed']) && $settings['slide_speed'] ? $settings['slide_speed'] : 3000;

		$display_rating = isset($settings['display_rating']) && $settings['display_rating'] ? $settings['display_rating'] : 'no';
		$display_price = isset($settings['display_price']) && $settings['display_price'] ? $settings['display_price'] : 'no';

		$exclude_current_product = isset($settings['exclude_current_product']) ? $settings['exclude_current_product'] : 'no';
		
		$display_featured_only = isset($settings['display_featured_only']) ? $settings['display_featured_only'] : 'no';
		
		$display_on_sale_only = isset($settings['display_on_sale_only']) ? $settings['display_on_sale_only'] : 'no';

		$infinite_loop = isset($settings['infinite_loop']) ? $settings['infinite_loop'] : 'yes';
		$read_more_text = isset($settings['read_more_text']) ? $settings['read_more_text'] : 'Read More';



		$args = array();

		// $args['post_type'] = 'product';
		$args['status'] = 'publish';
		if( isset($settings['post_status']) && is_array($settings['post_status']) && !empty($settings['post_status']) ){
			$args['status'] = $settings['post_status'];
		}

		if( isset($settings['product_types']) && is_array($settings['product_types']) && !empty($settings['product_types']) ){
			$args['type'] = $settings['product_types'];
		}

		if( isset($settings['product_cats']) && is_array($settings['product_cats']) && !empty($settings['product_cats']) ){
			$args['category'] = $settings['product_cats'];
		}

		if( isset($settings['product_tags']) && is_array($settings['product_tags']) && !empty($settings['product_tags']) ){
			$args['tag'] = $settings['product_tags'];
		}

		if( isset($settings['include_products_posts']) && is_array($settings['include_products_posts']) && !empty($settings['include_products_posts']) ){
			$args['include'] = $settings['include_products_posts'];
		}

		if( $display_on_sale_only == 'yes' ){
			$included_products = array();
			if( isset($settings['include_products_posts']) && is_array($settings['include_products_posts']) && !empty($settings['include_products_posts']) ){
				$included_products = $settings['include_products_posts'];
			}
			$products_array = array_merge( $included_products, wc_get_product_ids_on_sale() );
			$args['include'] = $products_array;
		}

		if( isset($settings['exclude_products_posts']) && is_array($settings['exclude_products_posts']) && !empty($settings['exclude_products_posts']) ){
			$args['exclude'] = $settings['exclude_products_posts'];
		}

		if( isset($settings['posts_per_page']) && intval($settings['posts_per_page']) > 0 ){
			$args['limit'] = $settings['posts_per_page'];
		}

		if( isset($settings['posts_per_page']) && intval($settings['posts_per_page']) == -1 ){
			$args['limit'] = $settings['posts_per_page'];
		}

		$args['orderby'] = 'date';
		if( isset($settings['order_by_products_posts']) && $settings['order_by_products_posts'] != '' ){
			$args['orderby'] = $settings['order_by_products_posts'];
		}

		$args['order'] = 'DESC';
		if( isset($settings['order_products_posts']) && $settings['order_products_posts'] != '' ){
			$args['order'] = $settings['order_products_posts'];
		}  

		$current_product_id = 0;
		if( $exclude_current_product == 'yes' ){
			if( is_singular('product') ){
				$args['posts_per_page'] = intval($settings['posts_per_page']) + 1;
				$current_product_id = get_the_ID();
			}
		}

		if( isset($settings['display_featured_only']) && $settings['display_featured_only'] == 'yes' ){
			$args['featured'] = true;
		}
		

        /*if( in_array($settings['content_text_align'], ['left','center','right','justify']) ){
	        $style = '<style>#wpce_slider_wrapper_'.$element_id.' .wpce_text p{text-align: '. $settings['content_text_align'].'}</style>';
	        echo $style;
        }*/

        if( in_array($settings['cart_btn_text_align'], ['left','center','right','justify']) ){
	        $style = '<style>#wpce_slider_wrapper_'.$element_id.' .wpce_cartbtn{text-align: '. $settings['cart_btn_text_align'].'}</style>';
	        echo $style;
        }

        $slick_attr = '{';
        $responsive_slider_control = isset($settings['slider_responsive_control']) && !empty(isset($settings['slider_responsive_control'])) ? $settings['slider_responsive_control'] : array();
        if( !empty($responsive_slider_control) ){
        	$slick_attr .= '"responsive": [';
        	foreach ($responsive_slider_control as $rsc_index => $rsc_item) {
        		$slick_attr .= '{
					"breakpoint": '.$rsc_item['breakpoint_size'].',
			        "settings": {
			        	"slidesToShow": '.$rsc_item['slide_to_show'].',
			        	"slidesToScroll": '.$rsc_item['slides_to_scroll'].'
			        }';
			    if( $rsc_index == (count($responsive_slider_control) - 1) ){
			    	$slick_attr .= '}';	
			    }else{
        			$slick_attr .= '},';
			    }
        	}
        	$slick_attr .= ']';
        }
        $slick_attr .= '}';

        echo '<div
        		class="wpce_slider_wrapper wpce_slider_wrapper_'.$template_style.'"
        		id="wpce_slider_wrapper_'.esc_attr($element_id).'"
        		data-display-dots="'.$display_dots.'"
        		data-slide-to-show="'.$slide_to_show.'"
        		data-slides-to-scroll="'.$slides_to_scroll.'"
        		data-autoplay="'.$autoplay.'"
        		data-autoplay-speed="'.$autoplay_speed.'"
        		data-slide-speed="'.$slide_speed.'"
        		data-pause-on-focus="'.$pauseOnFocus.'"
        		data-pause-on-hover="'.$pauseOnHover.'"
        		data-pause-on-dots-hover="'.$pauseOnDotsHover.'"
        		data-infinite_loop="'.$infinite_loop.'"
        		data-slick="'.esc_attr($slick_attr).'"
        	>';
        	
        $products = wc_get_products($args);
        if( $products ){
        	$count=0;
			foreach( $products as $product ){
				$product_id = $product->get_id();
				if( $exclude_current_product == 'yes' ){
					if( $product_id == $current_product_id ){
						continue;
					}
				}
				$count++;
				$thumbnail_id = $product->get_image_id();
				// $product = wc_get_product(get_the_ID());
				if( $template_style === 'default' ){
					require( WPCE_PATH . 'templates/style-1/template.php' );
				}
			}
		}
		echo "</div>";
		
		if( isset($settings['display_navigation_arrows']) && ($settings['display_navigation_arrows'] == 'yes') ){
		?>
			<div class="wpce-arrow wb-arrow-prev">
				<?php \Elementor\Icons_Manager::render_icon( $settings['nav_arrow_left_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</div>
			<div class="wpce-arrow wb-arrow-next">
				<?php \Elementor\Icons_Manager::render_icon( $settings['nav_arrow_right_icon'], [ 'aria-hidden' => 'true' ] ); ?>
			</div>
		<?php
			//echo apply_filters('wpce_arrow_left_container', $arrow_left_container);
			//echo apply_filters('wpce_arrow_right_container', $arrow_right_container);
		}


	}


}
