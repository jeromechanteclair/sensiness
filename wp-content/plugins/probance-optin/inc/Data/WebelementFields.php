<?php 

/**
 * @package probance-optin
 */

namespace Inc\Data;

use Inc\Data\Data;

class WebelementFields extends Data 
{
        public function __construct()
        {       
                // Create data array
                $data= array (                        
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_webel-cblabel',
                                'name'      => 'probance-optin_webel-cblabel',
                                'size'      => '60',
                                'properties' => array ('edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => '')),
                                'properties-label' => 'Checkboxes labels',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "",
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_webel-cbaccount',
                                'name'      => 'probance-optin_webel-cbaccount',
                                'size'      => '',
                                'properties-label' => 'Display checkbox into "Sign-in" page',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "1"
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_webel-cborder',
                                'name'      => 'probance-optin_webel-cborder',
                                'size'      => '',
                                'properties-label' => 'Display checkbox into "My Account" page',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "1"
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_webel-cborder-behave',
                                'name'      => 'probance-optin_webel-cborder-behave',
                                'size'      => '',
                                'properties-label' => 'Display checkbox into "Checkout" page',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "1"
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_webel-cbsignin',
                                'name'      => 'probance-optin_webel-cbsignin',
                                'size'      => '',
                                'properties-label' => 'Do NOT display the checkbox on "Checkout" page when the customer already exists into the database',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "1"
                        )
                );
                
                // Set default value using translations/_default.json file
                $data=Translations::setDefaultValues($data);

                parent::__construct($data);
        }

        public function getSectionInfo()
        {
                return array("id" => "probance-optin_section-webelements", "title" => "Web Elements");
        }
}

?>