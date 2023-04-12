<?php


/**
 * Comment class File : ajoute le post type Comment 
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

class Comment extends CommentMetaboxGenerator {


    /**
     * @var string
     */
    protected $title = 'comment';


    /**
     * @var array
     * define meta fields 
     */
    protected $fields = array( 
        array(
            'slot'   => 'normal',
            'title'  => 'Photos',
            'priority'=>'default',
            'data'   =>
                array(
                    'review_img' => array(
                        'label'   => 'review_img',
                        'type'    => 'file',
                        'wrapper' => 'col-md-6',
                    ),
                ),
            ),
             array(
            'slot'   => 'normal',
            'title'  => 'Titre',
            'priority'=>'default',
            'data'   =>
                array(
                    'comment_title' => array(
                        'label'   => 'TItre',
                        'type'    => 'text',
                        'wrapper' => 'col-md-6',
                    ),
                ),
            ),
    
    );


    /**
     * Constructor
     * @return void 
     */
    public function __construct() {
        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
        add_shortcode('get_comments', array( $this, 'get_comments' ));
        add_filter( 'pre_comment_approved', function( $approved, $data ) {
            return isset($data['comment_type']) && $data['comment_type'] === 'prestation_review'
            ? 0
            : $approved;
        }, 20, 2);
        add_filter( 'preprocess_comment', array( $this,'preprocess_comment_type'), 12, 1 );
        add_filter( 'manage_edit-comments_columns', array( $this,'comment_columns' ));
        add_action( 'edit_comment', array( $this,'saveMetaBox') , 10, 2);
        add_action( 'manage_comments_custom_column',  array( $this,'add_comment_columns_content'), 10, 2 );
        add_action( 'comment_post',  array( $this,'save_comment_review_fields' ),10,1);
        


    }
    /**
     * Undocumented function
     *
     * @param [type] $commentdata
     * @return void
     */
    public function preprocess_comment_type( $commentdata ) {
        
        if( ( isset( $_POST['comment_type'] ) ) && ( $_POST['comment_type'] != '') ) { 
            $commentdata['comment_type'] = wp_filter_nohtml_kses( $_POST['comment_type'] );
        }
        return $commentdata;    
    } 
    /**
     * Save review comment meta
     *
     * @param [type] $comment_id
     * @return void
     */
    public  function save_comment_review_fields( $comment_id ){

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $attachment_ids=[];
        $files = $_FILES["review_img"];
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );
                $_FILES = array("review_img" => $file);
                $attachment_id = media_handle_upload("review_img", 0);
            // 
                if (is_wp_error($attachment_id)) {
                    // There was an error uploading the image.
                    echo "Error adding file";
                } else {
                    array_push( $attachment_ids, $attachment_id);
                }
            }
        }
    
        add_comment_meta( $comment_id, 'review_img', implode(',',$attachment_ids));
        if( isset( $_POST['rating'] ) )
        update_comment_meta( $comment_id, 'rating', esc_attr( $_POST['rating'] ) );
        if( isset( $_POST['comment_title'] ) )
        update_comment_meta( $comment_id, 'comment_title', esc_attr( $_POST['comment_title'] ) );
    }


    /**
     * comment_columns
     *
     * @param [type] $my_cols
     * @return void
     */
    public function comment_columns( $my_cols ){

        // but the above way is not so good - there could be problems when plugins would like to hook the comment columns
        // so, better like this:
        $misha_columns = array(
            'review_img' => 'Photos',
            'rating' => 'Note',
        );
        $my_cols = array_slice( $my_cols, 0, 3, true ) + $misha_columns + array_slice( $my_cols, 3, NULL, true );

        return $my_cols;
    }
    /**
     * Undocumented function
     *
     * @param [type] $column
     * @param [type] $comment_ID
     * @return void
     */
    public function add_comment_columns_content( $column, $comment_ID ) {
        global $comment;
        switch ( $column ) :
            case 'review_img' : {
                $image_size = 'thumbnail';
                $value=get_comment_meta( $comment_ID,'review_img' ,true);
                // var_dump($value);
                 if( strpos($value, ',') !== false ) {
                    $value = explode(',', $value);
                }
                else{
                    $value=[$value];
                }


                    if (!empty($value)) {
                        $image_str='';
                        foreach ($value as $values) {
                            if ($image_attributes = wp_get_attachment_image_src($values, $image_size)) {
                                $image_str .= '<img src="' . $image_attributes[0] . '" />';
                            }
                        }

                        echo $image_str; // or echo $comment->comment_ID;
                    }
                break;
            }
            case 'rating' : {
            
                $value=get_comment_meta( $comment_ID,'rating' ,true);
                if (!empty($value)) {

                    echo $value; // or echo $comment->comment_ID;
                }
                break;
            }

        endswitch;
    }
    /**
     * Undocumented function
     *
     * @param string $review_type
     * @param integer $page_id
     * @return void
     */
    public static function get_review_data($review_type='review',$page_id =57){
        global $wpdb;
        $post_entity = $review_type;
        //          INNER JOIN  {$wpdb->prefix}term_relationships t_rel ON  gt.id = t_rel.object_id
        //  INNER JOIN  {$wpdb->prefix}terms tax ON  t_rel.term_taxonomy_id = tax.term_id
        $query =
            "SELECT DISTINCT COUNT(cm.comment_ID) as total,AVG(cdata.meta_value) as moyenne FROM {$wpdb->prefix}comments cm
            INNER JOIN  {$wpdb->prefix}commentmeta cdata ON  cm.comment_ID = cdata.comment_ID
            WHERE cm.comment_post_ID ={$page_id}
            AND cm.comment_type ='{$review_type}'
            AND cm.comment_approved=1 
            AND cdata.meta_key ='rating' ";
	
        $result =  $wpdb->get_results( $query,ARRAY_A     );

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param string $review_type
     * @return void
     */
    public static function get_review_list($review_type='review',$page_id =57){
        define('DEFAULT_COMMENTS_PER_PAGE',3);
        $page = isset($_GET['review']) ? $_GET['review']  : 1;

        $limit = DEFAULT_COMMENTS_PER_PAGE;
        $offset = ($page * $limit) - $limit;

        $reviews = get_comments(
            array(
                // 'post_type'     => 'post', // Could be your CPT.
                'status'        => 'approve',
                'orderby'       => 'comment_date',
                'order'         => 'DESC',
                'type'          => $review_type ,
                'offset'=>$offset,
                'number'=>$limit,
                'post_id' => $page_id,
        // Your comment type.
            )
        );
        $total_comments = get_comments(
            array(
                'status'=>'approve',
                'type'=> $review_type,
                'post_id'=>$page_id,
            )
                
        );
        $pages = ceil(count($total_comments)/DEFAULT_COMMENTS_PER_PAGE);
      
        if ( $reviews ) :?>
        <section class="reviews" id="reviews">
            <div class="container">
        <?php
            foreach ( $reviews as $review ) :
                $args=['review'=>$review];
                get_template_part( 'template-parts/bloc', 'review',$args );
                // Do whatever you want.
                

                // Grab the meta data and display.
                // echo get_comment_meta( $review->comment_ID, 'my_meta_key', true );
            endforeach;
        
            $args = array(
                'base'         => get_permalink(). '%_%',
                'format'       => '?review=%#%#reviews',
                'total'        => $pages,
                'current'      => $page,
                'show_all'     => false,
                'end_size'     => 1,
                'mid_size'     => 2,
                'prev_next'    => true,
                'prev_text'    => __( '←'),
                'next_text'    => __(  '→'),
                'type'         => 'plain',
            );
            // ECHO THE PAGENATION ?>
            <nav class="navigation pagination" role="navigation">
                <div class="nav-links">
                    <?= paginate_links( $args );?>
                </div>
            </nav>
        </div>   
        </section>
            <?php
        endif;
    }
        
    /**
     * @return void retourne template part liste des comments
     */
    public function get_comments() {
        ob_start();
        $args=['query'=>$this->getList()];
        get_template_part( 'template-parts/list', 'comments',$args );
        return ob_get_clean();
    }
    /**
     * Ajoute la review
     *
     * @return void
     */
    public function insert_review() {
        // var_dump($_FILES);
        if ( ! isset( $_POST['my_review_form'] ) ) {
            return;
        }

        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( $_POST['_wpnonce'], 'my-review-nonce' ) ) {
            return;
        }

        global $current_user;

        // WARNING: Make sure the inputs are properly sanitized.
        $review_id = wp_new_comment(
            array(
                'comment_post_ID'       => absint($_POST['post_id'] ), // The post on which the reviews are being recorded.
                'comment_author'        => wp_strip_all_tags( $current_user->display_name ),
                'comment_author_email'  => sanitize_email( $current_user->user_email ),
                'comment_author_url'    => esc_url( $current_user->user_url ),
                'comment_content'       => wp_strip_all_tags($_POST['review_content']), // Sanitize as per your requirement. You can use wp_kses().
                'comment_type'          => 'prestation_review', // Or, your custom comment type.
                'comment_parent'        => 0,
                'user_id'               => absint( $current_user->ID ),
            )
        );
        // These files need to be included as dependencies when on the front end.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        // $attachment_id = media_handle_upload( 'review_img', $review_id );
        
        // if ( is_wp_error( $attachment_id ) ) {
        //     // There was an error uploading the image.
        
        // } else {

        //     // The image was uploaded successfully!
        // }
        $attachment_ids=[];
        $files = $_FILES["review_img"];
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );
                $_FILES = array("review_img" => $file);
                $attachment_id = media_handle_upload("review_img", 0);
            // 
                if (is_wp_error($attachment_id)) {
                    // There was an error uploading the image.
                    echo "Error adding file";
                } else {
                    array_push( $attachment_ids, $attachment_id);
                }
            }
        }
        // If error, return with the error message.
        if ( is_wp_error( $review_id ) ) {
            return $review_id->get_error_message();
        }

        // You can use add_comment_meta() for additional information.
        add_comment_meta( $review_id, 'rating', wp_strip_all_tags($_POST['rating'] ));
        add_comment_meta( $review_id, 'comment_title', wp_strip_all_tags($_POST['rating'] ));
        add_comment_meta( $comment_id, 'review_img', implode(',',$attachment_ids));

        // Redirect with a success hint.
        wp_redirect( add_query_arg( 'success', 1, get_the_permalink($_POST['post_id'] ) ) );
        exit();
    }
     /**
     * zone édition
     *
     * @return void
     */
    private function edit_comment( $comment_id )
    {
        if( ! isset( $_POST['pmg_comment_update'] ) || ! wp_verify_nonce( $_POST['pmg_comment_update'], 'pmg_comment_update' ) )
            return;
        if( isset( $_POST['rating'] ) )
            update_comment_meta( $comment_id, 'rating', esc_attr( $_POST['rating'] ) );
    }
}

