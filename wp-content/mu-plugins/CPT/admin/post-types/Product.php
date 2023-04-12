<?php

/**
 * Product class File : ajoute le post type Product 
 *
 * @category  Class
 * @package   Guiti
 * @author    Bigbump contact@bigbump.fr
 * @copyright Copyright 2015 Company, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://bigbump.fr
 */

class Product extends MetaboxGenerator
{

    /**
     * @var string
     */
    protected $title = 'Product';

 
    /**
     * @var array
     */
    protected $fields = array( 
      
           array(
                'slot'   => 'side',
                'title'  => 'Image alternative',
                'priority'=>'default',
                'data'   =>
						array(
                                'alt_file' => array(
								'label'   => 'Fichier téléversé',
								'type'    => 'file',
								'wrapper' => 'col-md-6',
								
								
							),
                               
                            ),
                          
                ),
          
            array(
                'slot'   => 'side',
                'title'  => 'Gif de présentation',
                'priority'=>'default',
                'data'   =>
                            array(
                                'gif' => array(
                                    'label'   => 'Fichier téléversé',
                                    'type'    => 'file',
                                    'wrapper' => 'col-md-6',
                                   
                                  
                                ),
                               
                            ),
                          
                ),
           
        
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
