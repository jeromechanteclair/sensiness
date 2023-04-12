<?php


/**
 * Vendeur class File : ajoute le post type Vendeur 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class Vendeur extends MetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'vendeur';

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
                    'zipcode' => array(
                        'label'   => 'Code postal',
                        'type'    => 'number',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ),
                    'address' => array(
                        'label'   => 'Adresse',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'website_url' => array(
                        'label'   => 'Url du site web',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                        // 'value' => '1'
                        
                    ), 
                    'phone_number' => array(
                        'label'   => 'Numéro de téléphone',
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
        add_shortcode('get_vendeurs', array( $this, 'get_vendeurs' ));


    }

    /**
     * @return void enregistre le custom post_type 
     */
    public function create() {

        
        register_post_type(
            $this->title,
            array(
            'labels'          => array(
            'edit_item'     => __('Editer le vendeur', 'vendeur_domain'),
            'add_new'       => __('Ajouter un vendeur', 'vendeur_domain'),
            'add_new_item'  => __('Ajouter un vendeur', 'vendeur_domain'),
            'name'          => __('Vendeurs', 'vendeur_domain'),
            'singular_name' => __('vendeur', 'vendeur_domain'),
            'view_items'    => __('Voir les vendeurs', 'vendeur_domain'),

            ),
            'public'          => false,
            'hierarchical'    => false,
            'has_archive'     => false,
            'show_in_rest'    => true,
            'show_ui' => true, 
            'rest_base'          => 'vendeurs',
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'rewrite'            => array( 'slug' => 'points-de-vente' ),
            'capability_type' => 'post',
            'menu_icon'       => 'dashicons-store',
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
     * @return void retourne la liste des vendeurs
     */

    private function getList(){
        global $wpdb;
        $post_entity = $this->title;
        $query =
            "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt
            INNER JOIN  {$wpdb->prefix}term_relationships t_rel ON  gt.id = t_rel.object_id
            INNER JOIN  {$wpdb->prefix}terms tax ON  t_rel.term_taxonomy_id = tax.term_id
            WHERE post_type='" . $post_entity . "'
            AND (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
                ORDER BY tax.name ASC, gt.post_title ASC
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
     * @return void retourne template part liste des vendeurs
     */
    public function get_vendeurs() {
        ob_start();
        $args=['query'=>$this->getList()];
        get_template_part( 'template-parts/list', 'vendeur',$args );
        return ob_get_clean();
    }
}

