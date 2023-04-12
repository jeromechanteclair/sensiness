<?php


/**
 * FAQ class File : ajoute le post type FAQ 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class FAQ extends MetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'FAQ';
    /**
     * @var string
     */

    /**
     * @var string
     */
    protected $apiroute = '/wp/v2/product';
    /**
     * @var array
     * define meta fields 
     */
    protected $fields = array( 

        
    );


        

    /**
     * Constructor
     * @return void 
     */
    public function __construct() {

        add_action('init', array( $this, 'create' ));
        add_action('init', array( $this, 'rest_meta' ));
        add_action('admin_init', array( $this, 'searchApiMetabox' ));
        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
        add_action('save_post', array( $this, 'saveMetaBox' ), 10, 2);
        add_shortcode('FAQ', array( $this, 'FAQ' ),10,1);
        add_filter( 'wpseo_schema_graph',  array( $this,'custom_schema'), 10, 2 );
    }

    /**
     * @return void enregistre le custom post_type 
     */
    public function create() {

        
        register_post_type(
            $this->title,
            array(
                'labels'          => array(
                'edit_item'     => __('Editer la question', 'FAQ_domain'),
                'add_new'       => __('Ajouter une question', 'FAQ_domain'),
                'add_new_item'  => __('Ajouter une question', 'FAQ_domain'),
                'name'          => __('FAQ', 'FAQ_domain'),
                'singular_name' => __('question', 'FAQ_domain'),
                'view_items'    => __('Voir les questions', 'FAQ_domain'),

            ),
            'public'          => false,
            'hierarchical'    => false,
            'has_archive'     => false,
            'show_in_rest'    => false,
            'show_ui' => true, 
            'rest_base'          => 'FAQs',
            'exclude_from_search' => false,
            'show_in_nav_menus' => false,
            'publicly_queryable'  => true,
            'rewrite'            => false,
            'menu_icon'       => 'dashicons-format-chat',
            
            'taxonomies'      => array( 'thematique_tags'),
            // 'supports'        => array(
            //         'page-attributes',
            //         'title',
            //         'editor',
            //         'excerpt',
            //         'thumbnail',
            //         'author'
            // ),
        
            )
        );
    }


     /**
     * @return void retourne la liste des FAQs
     */

    public function getList($product_id = null){
        global $wpdb;
        $post_entity = $this->title;
        if(empty($product_id)){
        
            $query =
            "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt
            INNER JOIN  {$wpdb->prefix}term_relationships t_rel ON  gt.id = t_rel.object_id
            INNER JOIN  {$wpdb->prefix}terms tax ON  t_rel.term_taxonomy_id = tax.term_id
            LEFT JOIN {$wpdb->prefix}termmeta termmeta ON tax.term_id=termmeta.term_id

            WHERE post_type='" . $post_entity . "'
            AND (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
            AND(termmeta.meta_key LIKE '%custom_order%')
                ORDER BY termmeta.meta_value ASC, gt.post_title ASC
            " ;
        }
        else{
            $product_id = '"'.$product_id.'"';
            $query =
                "SELECT DISTINCT(gt.id) FROM {$wpdb->prefix}posts gt
                INNER JOIN  {$wpdb->prefix}term_relationships t_rel ON  gt.id = t_rel.object_id
                INNER JOIN  {$wpdb->prefix}terms tax ON  t_rel.term_taxonomy_id = tax.term_id
                LEFT JOIN {$wpdb->prefix}postmeta metaproduct ON gt.id=metaproduct.post_id AND metaproduct.meta_key = 'product'
                WHERE post_type='" . $post_entity . "'
                AND (gt.post_status  NOT LIKE '%draft%' AND gt.post_status  NOT LIKE  '%trash%')
                AND metaproduct.meta_value LIKE '%{$product_id}%'
                    ORDER BY tax.name ASC, gt.post_title ASC
                " ;
        }

  
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
     * @return void retourne template part liste des FAQs
     */
    public function FAQ( $atts = []) {

        ob_start();
        $args=['query'=>$this->getList()];
        if(!empty($atts)){
         
            if(array_key_exists('product_id',$atts)){
             $args=['query'=>$this->getList( $atts['product_id'])];
            }
            if(array_key_exists('category',$atts)){
                $args['category']=$atts['category'];
            }
        }
        get_template_part( 'template-parts/list', 'faq',$args );
        return ob_get_clean();
    }

    public function custom_schema ($data, $context){

        global $post;

        if ( has_shortcode( $post->post_content, 'FAQ' )) {
        foreach ( $data as $key => $value ) {


      
              



                $faqQuery = $this->getList();
                $schema = array();
                while ( $faqQuery->have_posts() ) : $faqQuery->the_post();
                    $faq_id = get_the_ID();
                    $faq_title = get_the_title();
                    $faq_content = get_the_content();

                    $temp = array(
                    '@type'=>'Question',
                            'name'=>$faq_title,
                            'acceptedAnswer'=>array(
                                '@type'=>'Answer',
                                'text'=>$faq_content
                            )
                        );
                    array_push($schema, $temp);
                endwhile;
                


                $data[$key]['mainEntity'] = $schema ;
                    

                // $data[$key]['contentUrl'] = str_replace( 'http://basic.wordpress.test/', 'https://cdn.domain.tld/', $value['contentUrl'] );
            }
        
        array_push($data,array(
            '@type' => 'FAQPage',
            "mainEntity"=> $schema
        ));
        }
    
// var_dump($data);
        
//         die();
        return $data;

        // var_dump($context);
    }


}

