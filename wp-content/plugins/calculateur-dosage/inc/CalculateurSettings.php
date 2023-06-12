<?php  // phpcs:ignore
/**
 * @category    Manage Calculateur settings
 * @package     Calculateur
 * @author      Jerome chanteclair
 * @license     GPL-2.0
 * @link        https://git.toptal.com/screening/jerome-chanteclair-2
 */

class CalculateurSettings {


	/**
	 * Holds the fields to be used in the fields callbacks
	 *
	 *  @var array
	 */
	private $options;

	/**
	 * Defines Calculateur fields fields
	 *
	 * @var array
	 */
	private $fields = array(
		array(
			'label'       => 'Titre du calculateur',
			'ID'          => 'calculateur-settings-title',
			'type'        => 'textarea',
			'placeholder' => 'Calculateur : ',
		),
		array(
			'label'       => 'Texte d\'introduction',
			'ID'          => 'calculateur-settings-description',
			'type'        => 'textarea',
			'placeholder' => 'Description : ',
		),
		array(
			'label'       => 'Posologie',
			'ID'          => 'calculateur-settings-posologie',
			'type'        => 'textarea',
			'placeholder' => 'Posologie : ',
		),



	);

	/**
	 * Init actions and helpers
	 */
	public function __construct() {
		// add_action( 'loop_start', ), 1 );
		
		add_shortcode('comparateur_cbd',  array( $this, 'display_calculateur'));
		add_action( 'admin_menu', array( $this, 'calculateur_menu' ) );
		add_action( 'admin_init', array( $this, 'calculateur_settings' ) );
		// ajout des options dans la fiche produit
		add_action('woocommerce_product_options_general_product_data', array( $this,'calculateur_adv_product_options'));
		// save options fiche produit
		add_action('woocommerce_process_product_meta', array( $this,'calculateur_save_fields'), 10, 2);
		add_action('wp_ajax_caculateur_ajax_results', array($this,'caculateur_ajax_results'),10,2);
		add_action('wp_ajax_nopriv_caculateur_ajax_results', array($this,'caculateur_ajax_results'),10,2);


	}

	/**
	 * Display Calculateur 
	 *
	 * @return html
	 */
	public function display_calculateur($args) {

		$title                    = get_option( 'calculateur-settings-title' );
		$description                    = get_option( 'calculateur-settings-description' );
		$posologie                    = get_option( 'calculateur-settings-posologie' );
		$taux_cbd = get_terms("pa_taux-de-cbd");
		$traitement = get_terms("pa_traitement");
		$ids = wp_list_pluck($traitement, 'term_taxonomy_id');
		$isproduct=false;
		
		if(isset($args['product_id'])){
			
			$isproduct =true;
			$traitement = get_the_terms($args['product_id'],"pa_traitement");
			$products = $this->caculateur_ajax_results(true,$args['product_id']);

		}
		else{

			$products = $this->caculateur_ajax_results(true);
		}
	
		include (plugin_dir_path(__DIR__) . 'template/form.php');

	}
	
	/**
	 * Caculateur_ajax_results
	 *
	 * @param array $terms
	 * @param integer $human_weight
	 * @return Array
	 */
	public function caculateur_ajax_results($called =false,$product_id=false){

       	global $wpdb;
		$human_weight = 50;
		if(!$product_id) {
			if (!$called) {
				$terms = [];
				if (isset($_POST['taux_cbd_value'])) {
					$terms[] =$_POST['taux_cbd_value'];
				}
				if (isset($_POST['traitement_value'])) {
					$terms[] =$_POST['traitement_value'];
				}


				if (isset($_POST['human_weight_value'])) {
					$human_weight=$_POST['human_weight_value'];
				}


				$countterms = count($terms);
				$terms = implode(',', $terms);

				$sql = "
							SELECT  product.id as product_id
							FROM {$wpdb->prefix}posts as product
							LEFT JOIN {$wpdb->prefix}term_relationships as tr ON product.ID = tr.object_id
							LEFT JOIN {$wpdb->prefix}postmeta as human_weight ON product.ID = human_weight.post_id
							LEFT JOIN {$wpdb->prefix}postmeta as human_weight_max ON product.ID = human_weight_max.post_id
							WHERE product.post_type = 'product'
							AND product.post_status = 'publish'
							AND (human_weight.meta_key like '%human_weight%' AND human_weight.meta_value <= {$human_weight})
							AND (human_weight_max.meta_key like '%human_weight_max%' AND human_weight_max.meta_value >= {$human_weight})
							AND tr.term_taxonomy_id IN ({$terms})
							GROUP BY product.id
							HAVING COUNT(tr.term_taxonomy_id) = {$countterms}
						" ;
			} else {
				$sql = "
						SELECT  product.id as product_id
						FROM {$wpdb->prefix}posts as product
						LEFT JOIN {$wpdb->prefix}postmeta as product_meta ON product.ID = product_meta.post_id
						WHERE product.post_type = 'product'
						AND product.post_status = 'publish'
						AND (product_meta.meta_key like '%human_weight%' AND product_meta.meta_value <= {$human_weight})
						GROUP BY product.id

					" ;

			}
		
	

		$result =  $wpdb->get_results($sql);
		
		if($called){
			return $result;
		}
		else{

            $msg = 'super';
            wp_send_json_success(array(
                'status'=>'success',
                    'msg'=>$this->display_products($result),'result'=>$result
                ));

            die();

		}
		}
		else{
			$product_id =intval($product_id);
			return array($product_id);
		}

	}

	function display_products($result){
		$html='';
		if(!empty($result)){

			$html="<h3>Produits conseillés : </h3>";
			$html.='<div class="cbd-calculator__products">';
				foreach ($result as $product) {
					$permalink=get_permalink($product->product_id);
					$product = wc_get_product($product->product_id);

					$title = $product->get_name();
					$title = $product->get_name();
					$price = $product->get_price();
					$image =$product->get_image();
					$html.='<a  href="'.$permalink.'"class="cbd-calculator__product">';
					$html.=$image;
					$html.='<h4>'.$title.'</h4>';
					$html.='<span>'.$price.'€</span>';
					$html.='<button class="add_to_cart_button">Ajouter au panier</button>';

					$html.='</a>';
				
					# code...
				}
			$html.='</div>';


		}
		else{
			$html="<p>Aucun produit trouvé pour ces critères </p>";
		}
	
		return $html;
	}



	/**
	 * UDisplay product meta field
	 *
	 * @return void
	 */
	public function calculateur_adv_product_options(){
		echo '<div class="options_group">';
			woocommerce_wp_text_input(array(
				'id'      => 'human_weight',
				'value'   => get_post_meta(get_the_ID(), 'human_weight', true),
				'label'   => 'Poids conseillé minimum',
				'desc_tip' => true,
				'type' => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min' => '0'
				),
				'description' => 'Poids minimum conseillé pour le produit (kg)',
			));
			woocommerce_wp_text_input(array(
				'id'      => 'human_weight_max',
				'value'   => get_post_meta(get_the_ID(), 'human_weight_max', true),
				'label'   => 'Poids conseillé maximum',
				'desc_tip' => true,
				'type' => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min' => '0'
				),
				'default'=>150,
				'description' => 'Poids max conseillé pour le produit (kg)',
			));

		echo '</div>';
	}

	/**
	 * Save product meta fields
	 *
	 * @param [type] $id
	 * @param [type] $post
	 * @return void
	 */
	public function calculateur_save_fields($id, $post)	{
		if (!empty($_POST['human_weight'])) {
			update_post_meta($id, 'human_weight', $_POST['human_weight']);
		} else {
			delete_post_meta($id, 'human_weight');
		}
		if (!empty($_POST['human_weight_max'])) {
			update_post_meta($id, 'human_weight_max', $_POST['human_weight_max']);
		} else {
			delete_post_meta($id, 'human_weight_max');
		}
	}


	/**
	 * Add Calculateur setting page to WordPress admin
	 *
	 * @return void
	 */
	public function calculateur_menu() {

		add_menu_page(
			'Calculateur de dosage Options',
			'Calculateur de dosage',
			'administrator',
			'calculateur-settings',
			array( $this, 'calculateur_settings_page' ),
			'dashicons-megaphone'
		);
	}

	/**
	 * Register Calculateur settings
	 *
	 * @return void
	 */
	public function calculateur_settings() {

		foreach ( $this->fields as $field ) {
			register_setting( 'calculateur-settings-group', $field['ID'] );
			add_settings_field(
				$field['ID'],
				$field['label'],
				array( $this, 'display_settings_fields' ),
				'calculateur-settings',
				'calculateur-settings-section',
				$field
			);
		}

	}

	/**
	 * Render Calculateur option page
	 *
	 * @return void
	 */
	public function calculateur_settings_page() {
		// add saved fields to options property.
		foreach ( $this->fields as $field ) {
			$this->options[ $field['ID'] ] = get_option( $field['ID'] );
		}


		?>
		<div class="wrap">
			<h1>Paramètres généraux</h1>
			<form method="post" action="options.php">
				<p>Pour afficher le comparateur utiliser le shortcode suivant : [comparateur_cbd]</p>
				<p>Pour les taux de cbd changeants , utilisez le shortcode suivant dans l'editeur : [comparateur_cbd_value]</p>
				<table class="form-table">
					<?php
					// Prints out all hidden setting fields.
					settings_fields( 'calculateur-settings-group' );
					// Prints out setting fields.
					do_settings_fields( 'calculateur-settings', 'calculateur-settings-section' );

					?>
				</table>
				<?php submit_button(); ?>
			</form>

		</div>
		<?php
	}

	/**
	 * Get the settings option array and print one of its fields
	 *
	 * @param  array $args array of current field values.
	 */
	public function display_settings_fields( $args ) {

		if($args['type'] == 'textarea'){

            printf(
				'<textarea class="editor" id="' . $args['ID'] . '" name="' . $args['ID'] . '" placeholder="' . $args['placeholder'] . '"/> %s</textarea>',
				isset($this->options[ $args['ID'] ]) ? esc_attr($this->options[ $args['ID'] ]) : ''
			);

		}
		else{

			printf(
				'<input class="editor" type="' . $args['type'] . '" id="' . $args['ID'] . '" name="' . $args['ID'] . '" value="%s" placeholder="' . $args['placeholder'] . '"/>',
				isset( $this->options[ $args['ID'] ] ) ? esc_attr( $this->options[ $args['ID'] ] ) : ''
			);
		}
	}
}
new CalculateurSettings();
