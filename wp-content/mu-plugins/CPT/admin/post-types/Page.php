<?php

/**
 * Page class File : ajoute le post type Page 
 *
 * @category  Class
 * @package   Guiti
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://bigbump.fr
 */

class Page extends MetaboxGenerator
{

    /**
     * @var string
     */
    protected $title = 'Page';

 
    /**
     * @var array
     */
    protected $fields = array( 
        // array(
        //     'slot'   => 'advanced',
        //     'title'  => 'Hero Banner',
        //     'priority'=>'default',
        //     'data'   =>
        //         array(
        //             'banner_activate' => array(
        //                 'label'   => 'Activé',
        //                 'type'    => 'checkbox',
        //                 'wrapper' => 'col-md-12',
        //                 'value' => '1'
                        
        //             ),
        //             'banner_title' => array(
        //                 'label'   => 'Titre',
        //                 'type'    => 'text',
        //                 'wrapper' => 'col-md-6',
        //                 // 'value' => '1'
                        
        //             ),
        //             'banner_content' => array(
        //                 'label'   => 'Contenu',
        //                 'type'    => 'textarea',
        //                 'wrapper' => 'col-md-6',
        //                 // 'value' => '1'
                        
        //             ), 

        //             'banner_button_label' => array(
        //                 'label'   => 'Label du bouton',
        //                 'type'    => 'text',
        //                 'wrapper' => 'col-md-6',
        //                 // 'value' => '1'
                        
        //             ), 
        //             'banner_button_url' => array(
        //                 'label'   => 'Lien du bouton',
        //                 'type'    => 'text',
        //                 'wrapper' => 'col-md-6',
        //                 // 'value' => '1'
                        
        //             ), 
                  
                    
        //         ),
                      
        //     ),
           
                      
           
 
           
        
    );
        

    /**
     * Constructor
     *
     * @return void 
     */
    public function __construct()
    {

        add_action('add_meta_boxes', array( $this, 'addListMetaBox' ));
        add_action('save_post', array( $this, 'saveMetaBox' ), 10, 2);
    }


  
}
