<?php
add_action('admin_menu', 'wpce_menu_page');
function wpce_menu_page(){
	global $submenu;
	add_menu_page(
		'Woo Elementor Product Carousel',
		'Woo Elementor Product Carousel',
		'manage_options',
		'wpce-slider',
		'wpce_callback',
		'dashicons-image-flip-horizontal',
		'59'
	);

	add_submenu_page(
		'wpce-slider',
		'Custom CSS',
		'Custom CSS',
		'manage_options',
		'wpce-custom-css',
		'wpce_css_callback' 
	);

	add_submenu_page(
		'wpce-slider',
		'Custom JS',
		'Custom JS',
		'manage_options',
		'wpce-custom-js',
		'wpce_js_callback' 
	);

	$link_text = '<span class="wpce-up-pro-link" style="font-weight: bold; color: #FCB214">Upgrade To Pro</span>';
			
	$submenu["wpce-slider"][4] = array( $link_text, 'manage_options' , WPCE_PRO_LINK );
	
	return $submenu;
}

function wpce_callback(){}
function wpce_css_callback(){
	 // The default message that will appear
    $custom_css_default = __( '/*
Welcome to the Custom CSS editor!

Please add all your custom CSS here and avoid modifying the core plugin files. Don\'t use <style> tag
*/');
	    $custom_css = get_option( 'wpce_custom_css', $custom_css_default );
?>
	    <div class="wrap">
	        <div id="icon-themes" class="icon32"></div>
	        <h2><?php _e( 'Custom CSS' ); ?></h2>
	        <?php if ( ! empty( $_GET['settings-updated'] ) ) echo '<div id="message" class="updated"><p><strong>' . __( 'Custom CSS updated.' ) . '</strong></p></div>'; ?>
	 
	        <form id="custom_css_form" method="post" action="options.php" style="margin-top: 15px;">
	 
	            <?php settings_fields( 'wpce_custom_css' ); ?>
	 
	            <div id="custom_css_container">
	                <div name="wpce_custom_css" id="wpce_custom_css" style="border: 1px solid #DFDFDF; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; width: 100%; height: 400px; position: relative;"></div>
	            </div>
	 
	            <textarea id="custom_css_textarea" name="wpce_custom_css" style="display: none;"><?php echo $custom_css; ?></textarea>
	            <p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" /></p>
	        </form>
	    </div>
<?php
}

function wpce_js_callback(){
	// The default message that will appear
    $custom_js_default = __( '/*
Welcome to the Custom JS editor!

Please add all your custom JS here and avoid modifying the core plugin files. Don\'t use <script> tag
*/');
	    $custom_js = get_option( 'wpce_custom_js', $custom_js_default );
?>
	    <div class="wrap">
	        <div id="icon-themes" class="icon32"></div>
	        <h2><?php _e( 'Custom JS' ); ?></h2>
	        <?php if ( ! empty( $_GET['settings-updated'] ) ) echo '<div id="message" class="updated"><p><strong>' . __( 'Custom JS updated.' ) . '</strong></p></div>'; ?>
	 
	        <form id="custom_js_form" method="post" action="options.php" style="margin-top: 15px;">
	 
	            <?php settings_fields( 'wpce_custom_js' ); ?>
	 
	            <div id="custom_css_container">
	                <div name="wpce_custom_js" id="wpce_custom_js" style="border: 1px solid #DFDFDF; -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px; width: 100%; height: 400px; position: relative;"></div>
	            </div>
	 
	            <textarea id="custom_js_textarea" name="wpce_custom_js" style="display: none;"><?php echo $custom_js; ?></textarea>
	            <p>
	            	<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
	            </p>
	        </form>
	    </div>
<?php
}

add_action( 'admin_enqueue_scripts', 'wpce_custom_css_js_scripts' );
function wpce_custom_css_js_scripts( $hook ) {

	wp_enqueue_script( 'wpce_admin_js', WPCE_URL . 'admin/assets/js/admin.js', array( 'jquery' ), '1.0.0', true );

    if ( ('woo-elementor-product-carousel_page_wpce-custom-css' == $hook) || ('woo-elementor-product-carousel_page_wpce-custom-js' == $hook) ) {
        wp_enqueue_script( 'ace_code_highlighter_js', WPCE_URL . 'assets/ace/js/ace.js', '', '1.0.0', true );
        wp_enqueue_script( 'ace_mode_css', WPCE_URL . 'assets/ace/js/mode-css.js', array( 'ace_code_highlighter_js' ), '1.0.0', true );
        wp_enqueue_script( 'ace_mode_js', WPCE_URL . 'assets/ace/js/mode-javascript.js', array( 'ace_code_highlighter_js' ), '1.0.0', true );
        wp_enqueue_script( 'custom_css_js', WPCE_URL . 'assets/ace/ace-include.js', array( 'jquery', 'ace_code_highlighter_js' ), '1.0.0', true );
    }
}

add_action( 'admin_init', 'wpce_register_custom_css_setting' ); 
function wpce_register_custom_css_setting() {
    register_setting( 'wpce_custom_css', 'wpce_custom_css',  'wpce_custom_css_validation');
    register_setting( 'wpce_custom_js', 'wpce_custom_js');
}

function wpce_custom_css_validation( $input ) {
    if ( ! empty( $input['wpce_custom_css'] ) )
        $input['wpce_custom_css'] = trim( $input['wpce_custom_css'] );
    return $input;
}


