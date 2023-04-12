<?php


/**
 * Atelier class File : ajoute le post type Atelier 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class Atelier extends MetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'atelier';
    /**
     * @var string
     */


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
                    'start_date' => array(
                        'label'   => 'Début de l\'événement',
                        'type'    => 'datetime-local',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ),
                    'end_date' => array(
                        'label'   => 'Fin de l\'événement',
                        'type'    => 'datetime-local',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'address' => array(
                        'label'   => 'Adresse',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'zipcode' => array(
                        'label'   => 'Code postal',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'location' => array(
                        'label'   => 'Lieu de l\'événement',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'reservation_url' => array(
                        'label'   => 'Lien de réservation',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'price' => array(
                        'label'   => 'Prix',
                        'type'    => 'number',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
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
        add_action('init', array( $this, 'rest_meta' ));
        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
        add_action('save_post', array( $this, 'saveMetaBox' ), 10, 2);
        add_shortcode('get_ateliers', array( $this, 'get_ateliers' ));


    }

    /**
     * @return void enregistre le custom post_type 
     */
    public function create() {

        
        register_post_type(
            $this->title,
            array(
                'labels'          => array(
                'edit_item'     => __('Editer l\' atelier', 'atelier_domain'),
                'add_new'       => __('Ajouter un atelier', 'atelier_domain'),
                'add_new_item'  => __('Ajouter un atelier', 'atelier_domain'),
                'name'          => __('Ateliers', 'atelier_domain'),
                'singular_name' => __('atelier', 'atelier_domain'),
                'view_items'    => __('Voir les ateliers', 'atelier_domain'),

            ),
            'public'          => true,
            'hierarchical'    => false,
            'has_archive'     => false,
            'show_in_rest'    => true,
            'show_ui' => true, 
            'rest_base'          => 'ateliers',
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'            => array( 'slug' => 'ateliers' ),
            'capability_type' => 'post',
            'menu_icon'       => 'dashicons-calendar-alt',
            'supports'        => array(
                    'page-attributes',
                    'title',
                    'editor',
                    'excerpt',
                    'thumbnail',
                    'author'
            ),
            'taxonomies'      => array( 'ville_tags'),
            )
        );
    }


     /**
     * @return void retourne la liste des ateliers
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
            AND CURRENT_DATE <= metasend.meta_value
       
            ORDER BY metastart.meta_value   ASC ";
// print_r($query);
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
     * @return void retourne template part liste des ateliers
     */
    public function get_ateliers() {
        ob_start();
        $args=['query'=>$this->getList()];
        get_template_part( 'template-parts/list', 'ateliers',$args );
        return ob_get_clean();
    }



}

