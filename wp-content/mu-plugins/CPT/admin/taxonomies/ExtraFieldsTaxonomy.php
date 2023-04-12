<?php


/**
 * EntretienTaxonomy class File : ajoute la catégorie au entretien
 *
 * @category  Class
 * @package   Guiti
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://bigbump.fr
 */

class ExtraFieldsTaxonomy{

    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var array
     */
    protected $fields =[];
     /**
     * Constructor
     *
     * @return void 
     */
    public function __construct($name)
    {
        $this->name = $name;
      
        
    }

    public function init(){
        add_action( $this->name.'_edit_form_fields', array( $this,'extra_fields'), 10, 2);
        // this adds the fields
        add_action( $this->name.'_add_form_fields',  array( $this,'extra_fields' ),10, 2);
        // this saves the fields
        add_action('created_'.$this->name,  array( $this,'save_extra_taxonomy_fields'), 10, 2);
        add_action('edited_'.$this->name,  array( $this,'save_extra_taxonomy_fields'), 10, 2);
    }

    public function add_field($name,$type,$label,$checkboxvalue=null,$choices=null){
        return $this->fields =[
            'name'=>
            $name,'type'=>$type,'label'=>$label,'checkboxvalue'=>$checkboxvalue,'choices'=>$choices
        ];
    }
    function extra_fields($t) {
        // var_dump();
        $field=$this->fields;
        $t_id ='';
        if (is_object($t)) {
            $t_id = $t->term_id;
        }
        // remove
        preg_match('/\[(.*)\]/', $field['name'], $matches);
        $value = get_term_meta($t_id,  $matches[1],true) ? get_term_meta($t_id, $matches[1],true):'';
        

        // regex find string between '[]'
      

        wp_nonce_field(basename(__FILE__), 'agenda_meta_box_nonce');

        $html = '<div class="form form-admin">
            <div class="form-admin__left row">';
        

            $attr = '';
            if (isset($field['attr']) && ! empty($field['attr']) ) {
                foreach ( $field['attr'] as $attrKey => $attribut ) {
                    $attr .= '' . $attrKey . '="' . $attribut . '" ';
                }
            }

            $html .= '<div class="form-group ' . ( isset($field['wrapper']) && ! empty($field['wrapper']) ? $field['wrapper'] : 'col-12' ) . '">';
            if ('textarea' == $field['type'] ) {
                $html .= '
                <label for="term_meta[' . strtolower($field['name']) . ']" class="form-label">' . $field['label'] . ' :</label>

                <textarea name="term_meta[' . strtolower($field['name']) . ']" id="' . strtolower($field['name']) . '" class="form-control " value=""> '.$value.' </textarea>';
            } elseif ('select' == $field['type'] ) {
                $html .= '<label for="term_meta[' . strtolower($field['name']) . ']" class="form-label">' . $field['label'] . ' :</label>';

                $html .= '<select name="term_meta[' . strtolower($field['name']) . ']" id="' . strtolower($field['name']) . '" class="form-control ' . ( isset($field['class']) ? $field['label'] : '' ) . '">';
                foreach ( $field['choices'] as  $choice ) {
                    $html .= '<option value="' . $choice . '" ' . (  $value == $choice ? 'selected' : '' ) . '>' . $choice . '</option>';
                }
                $html .= '</select>';
            } elseif ('file' == $field['type'] ) {

                // $banner_img = get_term_meta($t_id, $field['name'],true);
                // var_dump($banner_img)   ;
                // $html.='<table cellspacing="10" class="img"cellpadding="10">
                // <tr>
                    
                //     <td>';
				echo $this->multi_media_uploader_field( $field['name'],       $value );
            
            } elseif ('checkbox' == $field['type'] ) {
                $checked ='';
                if($field['checkboxvalue']=='1'){
                    $checked ='checked';
                }
                $html .= '
                <div class="form-check">
                    <input type="' . $field['type'] . '" name="term_meta[' . strtolower($field['name']) . ']" id="term_meta[' . strtolower($field['name']) . ']" value="' . $field['checkboxvalue'] . '"  class="form-check-input ' . ( isset($field['class']) ? $field['class'] : '' ) . '"'.$checked.' />
                    <label for="term_meta[' . strtolower($field['name']) . ']" class="form-check-label">' . $field['label'] . '</label>
                </div>';
            } else {
              
                $html .= '<label for="term_meta[' . strtolower($field['name']) . '" class="form-label components-base-control__label ">' . $field['label'] . ' :</label>';

                $html .= '<input type="' . $field['type'] . '" name="term_meta[' . strtolower( $matches[1]) . ']" id="' . strtolower($field['name']) . '" value="' .$value. '" /><br>';
            }

            $html .= '</div>';
      

        $html .= '</div>
        </div>';
        echo $html;
    }



    // save extra taxonomy fields callback function
    function save_extra_taxonomy_fields($term_id)
    {
        if (isset($_POST['term_meta'])) {
            $t_id = $term_id;

            $cat_keys = $_POST['term_meta'];
          
            foreach ($cat_keys as $key => $value) {
                update_term_meta($t_id, $key, $value);
            }
        }
    }

    public function multi_media_uploader_field($name, $value = '') {
        $image = '">Add Media';
        $image_str = '';
        $image_size = 'full';
        $display = 'none';
        $value = explode(',', $value);

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

        return '<div class="multi-upload-medias"><ul>' . $image_str . '</ul><a href="#" class="wc_multi_upload_image_button button' . $image . '</a><input type="hidden" class="attechments-ids ' . $name . '" name="' . $name . '" id="' . $name . '" value="' . esc_attr(implode(',', $value)) . '" /><a href="#" class="wc_multi_remove_image_button button" style="display:inline-block;display:' . $display . '">Remove media</a></div>';
    }


}