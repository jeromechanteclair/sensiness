<?php 


/**
 * CommentMetaboxGenerator class File : Permet de générer des metaboxes pour un comment_ID
 *
 * @category  Class
 * @package   150_lillet
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://www.lillet.com/
 */

abstract class CommentMetaboxGenerator
{
   
    /**
     * @param  object $comment          entité en cours de
     *                               consultation
     * @param  array  $callback_args tableau des champs personnalisés
     * @return void 
     */

    public function meta_box( $comment, $callback_args )
    {
       
        $meta = get_comment_meta($comment->comment_ID);

    //  var_dump($comment->comment_ID);die();
        wp_nonce_field(basename(__FILE__), $comment->comment_ID.'_meta_box_nonce');

        $html = '<div class="form form-admin">
            <div class="form-admin__left row">';
           
        foreach ( $callback_args['args'][0] as $key => $field ) {
            $attr = '';
            if (isset($field['attr']) && ! empty($field['attr']) ) {
                foreach ( $field['attr'] as $attrKey => $attribut ) {
                    $attr .= '' . $attrKey . '="' . $attribut . '" ';
                }
            }

            $html .= '<div class="form-group ' . ( isset($field['wrapper']) && ! empty($field['wrapper']) ? $field['wrapper'] : 'col-12' ) . '">';
            if ('textarea' == $field['type'] ) {
                $html .= '
                <label for="' . strtolower($key) . '" class="form-label">' . $field['label'] . ' :</label>';
                if(!empty( preg_match("^(.*?)\[(.*?)\]^",$key,$match))){
                    $meta =  get_comment_meta($comment->comment_ID, $match[1],true);
                    $val ='';
                   
                    if(!empty($meta)){
                       $val = array_key_exists($match[2], $meta) ? $meta[ $match[2]] : '' ;
                    }
                    $html.=' <textarea name="' . strtolower($key) . '" id="' . strtolower($key) . '" class="form-control ' . ( isset($field['class']) ? $field['label'] : '' ) . '" ' . $attr . '>' . $val . '</textarea>';
                }
                else{
                 $html.=' <textarea name="' . strtolower($key) . '" id="' . strtolower($key) . '" class="form-control ' . ( isset($field['class']) ? $field['label'] : '' ) . '" ' . $attr . '>' . ( array_key_exists($key, $meta) ? $meta[ $key ][0] : '' ) . '</textarea>';
                }
            } elseif ('select' == $field['type'] ) {
                $html .= '<label for="' . strtolower($key) . '" class="form-label">' . $field['label'] . ' :</label>';
                if($field['multiple']){
                    $multiple="multiple";
                }
                else{
                    $multiple='';
                }
                $html .= '<select name="' . strtolower($key) . '" id="' . strtolower($key) . '" class="form-control ' . ( isset($field['class']) ? $field['label'] : '' ) . '"'.$multiple.' >';
                  if($field['multiple']){
                //  var_dump($meta);
                    foreach ( $field['choices'] as $choiceKey => $choice ) {
                    
                    $key = str_replace("[]","", $key);
// var_dump( get_comment_meta($comment->comment_ID,$key,true));
                    $selected='';
                    if(!empty(get_comment_meta($comment->comment_ID,$key,true))){
                        $selected = in_array($choice, get_comment_meta($comment->comment_ID,$key,true)) ? 'selected' : '' ;

                    }
                        
                    $html .= '<option value="' . $choice . '" ' .$selected . '>' . $choiceKey . '</option>';
                 }
                }
                else{
                     foreach ( $field['choices'] as $choiceKey => $choice ) {
                
                    $html .= '<option value="' . $choice . '" ' . ( array_key_exists($key, $meta) && $meta[ $key ][0] == $choice ? 'selected' : '' ) . '>' . $choiceKey . '</option>';
                    }
                }
               
                $html .= '</select>';
            } elseif ('file' == $field['type'] ) {

                $banner_img = get_comment_meta($comment->comment_ID,$key,true);
               
                $html.='<table cellspacing="10" cellpadding="10">
                <tr>
                    
                    <td>';
                    echo $this->multi_media_uploader_field( $key, $banner_img );
                    $html.='</td>
                    </tr>
                </table>';
            } elseif ('checkbox' == $field['type'] ) {

                $html .= '
                <div class="form-check">
                    <input type="' . $field['type'] . '" name="' . strtolower($key) . '" id="' . strtolower($key) . '" value="' . $field['value'] . '" ' . ( array_key_exists($key, $meta) && $meta[ $key ][0] == $field['value'] ? 'checked' : '' ) . ' class="form-check-input ' . ( isset($field['class']) ? $field['class'] : '' ) . '" />
                    <label for="' . strtolower($key) . '" class="form-check-label">' . $field['label'] . '</label>
                </div>';
            } else {
              $decimal='';
                $html .= '<label for="' . strtolower($key) . '" class="form-label components-base-control__label ">' . $field['label'] . ' :</label>';
                if($field['type']=='number'){
                  $decimal='  step="any"';
                }
                if(!empty( preg_match("^(.*?)\[(.*?)\]^",$key,$match))){
                    $meta =  get_comment_meta($comment->comment_ID, $match[1],true);
                    $val ='';
                  
                    if(!empty($meta)){
                       $val = array_key_exists($match[2], $meta) ? $meta[$match[2] ] :( isset($field['value'])?$field['value']:'')  ;
                    }
                    $html .= '<input type="' . $field['type'] . '" name="' . strtolower($key) . '" id="' . strtolower($key) . '" value="' .$val. '" class="form-control components-text-control__input' . ( isset($field['class']) ? $field['class'] : '' ) . '" "'. $decimal.'/><br>';
                }
                else{
                    $html .= '<input type="' . $field['type'] . '" name="' . strtolower($key) . '" id="' . strtolower($key) . '" value="' . (  array_key_exists($key, $meta) ? $meta[ $key ][0] :( isset($field['value'])?$field['value']:'')  ) . '" class="form-control components-text-control__input' . ( isset($field['class']) ? $field['class'] : '' ) . '" "'. $decimal.'/><br>';

                }
                
            }

            $html .= '</div>';
        }

        $html .= '</div>
        </div>';
        echo $html;
    }

    /**
     * @return function ajoute les metabox en fonction des champs personnalisés 
     */
    public function addListMetaBox()
    {

        foreach ( $this->fields as $field ) {
       
            add_meta_box(
                $this->title.'_'.uniqid(), // $id
                $field['title'], // $title
                array( $this, 'meta_box' ),  // $callback
                $this->title, // $screen
                $field['slot'], // $context
                $field['priority'], // priority
                array( $field['data'] ), // emplacement
            );
        }
      
    }

    /**
     * @return void ajoute les metabox en fonction des champs personnalisés 
     * @param  object $comment_id le post_id en cours
     * @param  $comment    le post en cours
     */
    public function saveMetaBox( $comment_id, $comment )
    {

        /*
        * Security checks
        */
             
        if (! isset($_POST[$comment_id.'_meta_box_nonce'])
            || ! wp_verify_nonce($_POST[$comment_id.'_meta_box_nonce'], basename(__FILE__)) 
        ) {
            return $comment_id;
        }
        /*
        * Check current user permissions
        */
    
        // $comment_ID = get_comment_ID_object($comment->comment_ID);
         
        // if (! current_user_can($comment_ID->cap->edit_post, $comment_id) ) {
        //     return $comment_id;
        // }
        /*
        * Do not save the data if autosave
        */
      
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $comment_id;
        }
 
        if (isset($_POST) && count($_POST) > 0 ) { // Fix 3
      
              
              
   
            foreach($this->fields as $arr ){
          
                foreach ( $arr['data'] as $key => $field ) {
                //si l'entrée est un tableau
                if(!empty( preg_match("^(.*?)\[(.*?)\]^",$key,$match))){
                    // $match[0] == first Key
                    // $match[1] == second $key 
                    // $match[2] == second $key 
                    $new=null;
                        // var_dump( $match[0]);die();
                    $old = get_comment_meta($comment_id, $match[1], true);
                //  var_dump($old);die();
                    if (isset($_POST[$match[1]]) ) {
                        
                        $new = $_POST[$match[1]];
                    
                    }
                    else{
                         delete_comment_meta($comment_id,$match[1]);
                    }
                    if ($new !==null && $new !== $old ) {
                        
                        update_comment_meta($comment_id,$match[1], $new);
                    } elseif (( null === $new || '' == $new ) && $old ) {
                    
                        delete_comment_meta($comment_id,$match[1], $old);
                    }
                }else{
                    $new=null;
                    $old = get_comment_meta($comment_id, $key, true);
                    // $key = str_replace("[]","", $key);
                    if (isset($_POST[ $key ]) ) {
                        
                        $new = $_POST[ $key ];
                        
                    }
                    //  new = 0
                    else{
                         delete_comment_meta($comment_id, $key);
                    }
                  
                    if ($new !==null && $new !== $old ) {
                        
                        update_comment_meta($comment_id, $key, $new);
                    } elseif (( null === $new || '' == $new ) && $old ) {
                    
                        delete_comment_meta($comment_id, $key, $old);
                    }
                }
        
           
            
                 
                }
            }
        }

        return $comment_id;
    }


    public function multi_media_uploader_field($name, $value = '') {
        $image = '">Add Media';
        $image_str = '';
        $image_size = 'full';
        $display = 'none';

    if( strpos($value, ',') !== false ) {
         $value = explode(',', $value);
    }
          

        
        else{
            $value=[$value];
        }

        if (!empty($value)) {
            foreach ($value as $values) {
                if ($image_attributes = wp_get_attachment_image_src($values, $image_size)) {
                    $image_str .= '<li data-attechment-id=' . $values . '><a href="' . $image_attributes[0] . '" target="_blank"><img src="' . $image_attributes[0] . '" /></a><i class="dashicons dashicons-no delete-img"></i></li>';
                }
            }

        }

        if($image_str){
            $display = 'inline-block';
        }

        return '<div class="multi-upload-medias"><ul>' . $image_str . '</ul><a href="#" class="wc_multi_upload_image_button button' . $image . '</a><input type="hidden" class="attechments-ids ' . $name . '" name="' . $name . '" id="' . $name . '" value="' . esc_attr(implode(',', $value)) . '" /><a href="#" class="wc_multi_remove_image_button button button-danger" style="display:inline-block;display:' . $display . '">Remove media</a></div>';
    }
}
