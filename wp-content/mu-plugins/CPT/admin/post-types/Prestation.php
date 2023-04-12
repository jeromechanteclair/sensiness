<?php


/**
 * Prestation class File : ajoute le post type Prestation 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class Prestation extends MetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'prestation';

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
                    'price' => array(
                        'label'   => 'Prix',
                        'type'    => 'number',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ),
                    'duration' => array(
                        'label'   => 'Durée',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 

                    'reservation_url' => array(
                        'label'   => 'Lien de réservation Paris',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'reservation_url_lyon' => array(
                        'label'   => 'Lien de réservation Lyon',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'location' => array(
                        'label'   => 'localisation',
                        'type'    => 'text',
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
        add_shortcode('get_prestations', array( $this, 'get_prestations' ));

    }

    /**
     * @return void enregistre le custom post_type 
     */
    public function create() {

        
        register_post_type(
            $this->title,
            array(
            'labels'          => array(
            'edit_item'     => __('Editer la prestation', 'prestation_domain'),
            'add_new'       => __('Ajouter une prestation', 'prestation_domain'),
            'add_new_item'  => __('Ajouter une prestation', 'prestation_domain'),
            'name'          => __('Prestations', 'prestation_domain'),
            'singular_name' => __('prestation', 'prestation_domain'),
            'view_items'    => __('Voir les prestations', 'prestation_domain'),

            ),
            'public'          => false,
            'hierarchical'    => false,
            'has_archive'     => false,
            'show_in_rest'    => true,
            'show_ui' => true, 
            'rest_base'          => 'prestations',
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'            => array( 'slug' => 'nos-prestations' ),
            'capability_type' => 'post',
            'menu_icon'       => 'dashicons-text-page',
            'supports'        => array(
                    'page-attributes',
                    'title',
                    'editor',
                    'excerpt',
                    'thumbnail',
                    'author'
            ),
            'taxonomies'      => array( 'post_tag'),
            )
        );
    }


     /**
     * @return void retourne la liste des prestations
     */

    private function getList(){

        global $wpdb;
        $post_entity = $this->title;
        $query =
            "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt

            WHERE post_type='" . $post_entity . "'
            AND (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
                ORDER BY  gt.post_date DESC
            " ;
        
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
     * @return void retourne template part liste des prestations
     */
    public function get_prestations() {
        ob_start();
        $args=['query'=>$this->getList()];
        get_template_part( 'template-parts/list', 'prestation',$args );
        return ob_get_clean();
    }


}

