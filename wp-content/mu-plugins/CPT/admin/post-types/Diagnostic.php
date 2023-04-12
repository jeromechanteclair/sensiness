<?php


/**
 * Diagnostic class File : ajoute le post type Diagnostic 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */
require_once 'DiagnosticTable.php';
class Diagnostic extends MetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'diagnostic';

    /**
     * @var string
     */
    protected $apiroute = '/wp/v2/product';

    /**
     * @var array
     * define meta fields 
     */
    protected $fields = array( 
        array(
            'slot'   => 'advanced',
            'title'  => 'Informations',
            'priority'=>'default',
            'data'   =>
                array(

                    'sante_percent' => array(
                        'label'   => '% de santé du cheveux',
                        'type'    => 'number',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'mini_intro' => array(
                        'label'   => 'Mini intro',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'precisions_routine' => array(
                        'label'   => 'Précisions routine',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                   
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 1 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_1' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_1_img' => array(
                        'label'   => 'Image Etape 1',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 2 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_2' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_2_img' => array(
                        'label'   => 'Image Etape 2',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 3 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_3' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_3_img' => array(
                        'label'   => 'Image Etape 3',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 4 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_4' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_4_img' => array(
                        'label'   => 'Image Etape 4',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 5 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_5' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_5_img' => array(
                        'label'   => 'Image Etape 5',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Etape 6 ',
            'priority'=>'default',
            'data'   =>
                array(

                    'step_6' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'step_6_img' => array(
                        'label'   => 'Image Etape 6',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Coiffure idéale ',
            'priority'=>'default',
            'data'   =>
                array(

                    'coiffure_ideale' => array(
                        'label'   => 'Contenu',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'coiffure_ideale_img' => array(
                        'label'   => 'Image coiffure url',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'coiffure_ideale_url' => array(
                        'label'   => 'Lien prise de rdv',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'advanced',
            'title'  => 'Technique de la routine',
            'priority'=>'default',
            'data'   =>
                array(

                    'technique' => array(
                        'label'   => 'La technique à maitriser pour cette routine',
                        'type'    => 'textarea',
                        'wrapper' => 'col-md-12',
                        // 'value' => '1'
                        
                    ), 
                    'technique_img' => array(
                        'label'   => 'Image Etape 6',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                ),
            ),
        array(
            'slot'   => 'side',
            'title'  => 'Routine',
            'priority'=>'default',
            'data'   =>
                array(

                    'routine' => array(
                        'label'   => 'Nom de la routine',
                        'type'    => 'select',
                        'wrapper' => 'col-md-12',
                        'choices'=> array(
                            'Choisissez une routine'=>0,
                            'Fortifiante'=>'Fortifiante',
                            'Hydratante'=>'Hydratante',
                            'Croissance'=>'Croissance',
                            'Assainissante'=>'Assainissante',
                            'Volumatrice'=>'Volumatrice',
                            
                        )
                        
                    ), 
           
                ),
            ),

    );


        

    /**
     * Constructor
     * @return void 
     */
    public function __construct() {

        add_action('init', array( $this, 'create' ));
        add_action('admin_init', array( $this, 'create_diagnostic_table' ));
        add_action('init', array( $this, 'rest_meta' ));
        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
        add_action('save_post', array( $this, 'saveMetaBox' ), 10, 2);
        add_shortcode('get_diagnostics', array( $this, 'get_diagnostics' ));
        add_action( 'admin_menu', array( $this, 'add_submenu_page_to_post_type' ) );
        add_action('admin_init', array( $this, 'searchApiMetabox' ));
        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
		add_action( 'wp_ajax_insert_diagnostic', array( $this,'insert_diagnostic' ));
		add_action( 'wp_ajax_nopriv_insert_diagnostic',array( $this, 'insert_diagnostic') );
    }

    /**
     * @return void enregistre le custom post_type 
     */
    public function create() {

        
        register_post_type(
            $this->title,
            array(
                'labels'          => array(
                'edit_item'     => __('Editer le diagnostic', 'diagnostic_domain'),
                'add_new'       => __('Ajouter un diagnostic', 'diagnostic_domain'),
                'add_new_item'  => __('Ajouter un diagnostic', 'diagnostic_domain'),
                'name'          => __('Diagnostics', 'diagnostic_domain'),
                'singular_name' => __('Diagnostic', 'diagnostic_domain'),
                'view_items'    => __('Voir les diagnostics', 'diagnostic_domain'),

            ),
            'public'          => false,
            'hierarchical'    => false,
            'has_archive'     => false,
            'show_in_rest'    => true,
            'show_ui' => true, 
            'rest_base'          => 'diagnostics',
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'rewrite'            => array( 'slug' => 'diagnostics' ),
            'capability_type' => 'post',
            'menu_icon'       => 'dashicons-calendar-alt',
            'supports'        => array(
                    // 'page-attributes',
                    'title',
                    'editor',
                    // 'excerpt',
                    // 'thumbnail',
                    'author'
            ),
            // 'taxonomies'      => array( 'ville_tags'),
            )
        );
    }

	/**
	 * Undocumented function
	 *
	 * @return void
	 */

	public function insert_diagnostic() {
		parse_str($_POST['data'], $datas);

		if ( ! isset( $datas['form_diagnostic_nonce'] ) || ! wp_verify_nonce( $datas['form_diagnostic_nonce'], 'form_diagnostic_nonce' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
			return;
		}
		// save in DB
		Diagnostic::save($datas);


		$routine_a=$routine_b=$routine_c=0;
		$finale_routine='';
        $SIB_list='';
		// verify form_diagnostic nonce	

		if(!empty($datas)){

			foreach($datas as $value){

		// regex start with 'b-'
				if(preg_match('/^a-/', $value)){
					$routine_a++;
				}
				if(preg_match('/^b-/', $value)){
					$routine_b++;
				}
				if(preg_match('/^c-/', $value)){
					$routine_c++;
				}
			}
			// var_dump($_POST);
			// var_dump($routine_a);
			// var_dump($routine_b);
			// var_dump($routine_c);
			// find min number between 3 numbers	
			$min=min($routine_a, $routine_b, $routine_c);
			switch (true) {
				case ($routine_c>$routine_a && $routine_c>$routine_b) || ($routine_a !== $min && $routine_c !== $min ):
					$finale_routine = 'Fortifiante';
                     $SIB_list=22;
					break;
				case ($routine_b !== $min && $routine_c !== $min) :
					$finale_routine = 'Assainissante';
                     $SIB_list=25;
					break;

				case $routine_b !== $min && $routine_a !== $min :
                     $SIB_list=26;
					$finale_routine = 'Volumatrice';
					break;
				case $routine_a>$routine_b && $routine_a>$routine_c:
                     $SIB_list=24;
					$finale_routine = 'Croissance';
					break;
				case $routine_b>$routine_a && $routine_b>$routine_c:
                       $SIB_list=23;
					$finale_routine = 'Hydratante';
					break;
			}
			// var_dump();
		}
        // return function
        $msg='';
        if(!empty($datas['newsletter'])){
            $customSIB =new CustomSIB();
            if(!empty($datas['conseils'])){
                $msg= 	$customSIB->SIB_create_contact( $datas['email'],$SIB_list,false);

            }
            else{
                $msg= 	$customSIB->SIB_create_contact( $datas['email'],'',false);
            }
		 
           
        }
        if(!empty($datas['conseils'])){
            $customSIB =new CustomSIB();
              if(!empty($datas['newsletter'])){
		        $msg= 	$customSIB->SIB_create_contact( $datas['email'],$SIB_list,false);
            }else{
                $msg= 	$customSIB->SIB_create_contact( $datas['email'],$SIB_list,false,true);
            }
            
        }

        $template=  $this->find_routine( $finale_routine);
        wp_send_json_success(array(
        'routine' => $finale_routine,'html'=>$template,'msg'=>$msg
        ));
      
     
	// return json_encode($data);
	wp_die();
	}
    public function find_routine($routine){

        global $wpdb;
        $post_entity = $this->title;
        $query =   
            "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt
            LEFT JOIN {$wpdb->prefix}postmeta routine ON gt.id=routine.post_id AND routine.meta_key = 'routine'
            WHERE gt.post_type='{$post_entity}' 
            AND 
            (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
            AND routine.meta_value='{$routine}'
            ";
            $routine_id =$wpdb->get_col( $query  );
            ob_start();
            $args['routine_id']=$routine_id;
                get_template_part( 'template-parts/content', 'routine',$args );
                $data = ob_get_clean();
        return  $data;
     

    }
	/**
	 * create table diagnostic results
	 *
	 * @return void
	 */
	public function create_diagnostic_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'diagnostic_results';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			gender varchar(255) NOT NULL,
			birthdate date NOT NULL,
			texture varchar(255) NOT NULL,
			etat_cheveux varchar(255) NOT NULL,
			coupe varchar(255) NOT NULL,
			protection varchar(255) NOT NULL,
			hydratation varchar(255) NOT NULL,
			manipulation varchar(255) NOT NULL,
			shampoing varchar(255) NOT NULL,
			objectif varchar(255) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		maybe_create_table($table_name, $sql );
	}
		/**
	 * insert into table diagnostic results
	 *
	 * @return void
	 */
	public static function save($data) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'diagnostic_results';
		$wpdb->insert( $table_name, array(
			'email' => $data['email'],
			'gender' => $data['gender'],
			'birthdate' => $data['birthdate'],
			'etat_cheveux' => $data['etat_cheveux'],
			'coupe' => $data['coupe'],
			'protection' => $data['protection'],
			'hydratation' => $data['hydratation'],
			'manipulation' => $data['manipulation'],
			'texture' => $data['texture'],
			'shampoing' => $data['shampoing'],
			'objectif' => $data['objectif']));
	}

    /**
     * Add sub menu page to the custom post type
     */
    public function add_submenu_page_to_post_type()
    {
        add_submenu_page(
            'edit.php?post_type=diagnostic',
            __('Résultats', 'LAC'),
            __('Résultats', 'LAC'),
            'manage_options',
            'diagnostic_options',
            array($this, 'diagnostic_result_display'));
    }
	/**
     * Options page callback
     */
    public function diagnostic_result_display()  {
        $exampleListTable = new DiagnosticTable();
        $exampleListTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Diagnostics issus du formulaire</h2>

            <?php 
            //filters
            // $exampleListTable->get_bulk_actions(); 
            ?>
            <?php $exampleListTable->display(); ?>
        </div>
        <?php
    }



     /**
     * @return void retourne la liste des diagnostics
     */

    private function getList(){

        global $wpdb;
        $post_entity = $this->title;
        $query =   
            "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt
            LEFT JOIN {$wpdb->prefix}postmeta metastart ON gt.id=metastart.post_id AND metastart.meta_key = 'start_date'
            LEFT JOIN {$wpdb->prefix}postmeta metasend ON gt.id=metasend.post_id AND metasend.meta_key = 'end_date'
            WHERE gt.post_type='".$post_entity."' 
            AND 
            (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
            AND
            metastart.post_id IN (
                SELECT post_id  FROM {$wpdb->prefix}postmeta metastart WHERE metastart.meta_key = 'start_date' AND  (CURRENT_DATE >= metastart.meta_value or  CURRENT_DATE <= metastart.meta_value)
                )
            AND
            metasend.post_id IN ( 
                SELECT post_id FROM {$wpdb->prefix}postmeta metasend WHERE metasend.meta_key = 'end_date' AND (CURRENT_DATE <= metasend.meta_value)
                )
            ORDER BY metastart.meta_value   ASC ";

        $result =  $wpdb->get_col( $query  );
        if(!empty( $result)){
            $args = array(  
                'post_type' => $this->title,
                'post__in' => $result,
                'posts_per_page' => -1, 
                'orderby'=>'post__in',
                'ignore_sticky_posts'=>true
            
            );

            return  new WP_Query( $args ); 
        }
        else{
            return null;
        }
       
        
    }
    
    /**
     * @return void retourne template part liste des diagnostics
     */
    public function get_diagnostics() {
        ob_start();
        $args=['query'=>$this->getList()];
        get_template_part( 'template-parts/list', 'diagnostics',$args );
        return ob_get_clean();
    }



}