<?php


/**
 * Post class File : ajoute le post type Post 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class Post  {


    /**
     * @var string
     */
    protected $title = 'post';



        

    /**
     * Constructor
     * @return void 
     */
    public function __construct() {


        add_shortcode('get_posts', array( $this, 'get_posts' ),10,1);
        add_filter( 'the_posts', function( $posts, \WP_Query $q )
        {
            if( 
                wp_validate_boolean( $q->get( '_exact_posts_per_page' ) )
                && ! empty( $posts ) 
                && is_int( $q->get( 'posts_per_page' ) ) 
            )
                $posts = array_slice( $posts, 0, absint( $q->get( 'posts_per_page' ) ) );

            return $posts;

        }, 999, 2 );
        add_action( 'pre_get_posts', array( $this, 'filter_query' ));
        add_filter('excerpt_length',array( $this, 'new_excerpt_length'));
        add_filter('excerpt_more',array( $this, 'new_excerpt_more'));

        add_filter( 'get_the_archive_title',array( $this,'prefix_category_title') );
    }

    /**
     * Undocumented function
     *
     * @param [type] $title
     * @return void
     */
    public function prefix_category_title( $title ) {
        if ( is_category() ) {
            $title = single_cat_title( '', false );
        }
        return $title;
    }
    /**
     * Undocumented function
     *
     * @param [type] $more
     * @return void
     */
    public function new_excerpt_more( $more ) {
        return '...';
    }

    /**
     * Custom excertp length
     */
    public  function new_excerpt_length($length) {
        return 16;
    }
       

    /**
     * 
     */
    public function filter_query($query){
    if( !is_admin() && $query->is_home() && $query->is_main_query() ){
            $query->set( '_exact_posts_per_page', true );
        }
    }

     /**
     * @return void retourne la liste des posts
     */

    private function getList($posts_per_page=3,$ignore_sticky_posts=false){

   
            $args = array(  
                'post_type' => $this->title,
                'posts_per_page' =>$posts_per_page, 
                'ignore_sticky_postss'=>$ignore_sticky_posts,
                '_exact_posts_per_page' => true   
            
            );

            return  new WP_Query( $args ); 
       

    }

      /**
     * @return void retourne template part liste des posts
     */
    public function get_posts($atts=[]) {
        ob_start();
        $args=['query'=>$this->getList()];
        if(!empty($atts)){
            $args=['query'=>$this->getList( $atts['posts_per_page'], $atts['ignore_sticky_posts'])];
        }
        get_template_part( 'template-parts/list', 'post',$args );
        return ob_get_clean();
    }


}

