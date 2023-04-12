<?php 

/**
 * @package probance-optin
 */

namespace Inc\Data;

use Inc\Data\Data;

class ApiFields extends Data 
{
        public function __construct()
        {       
                // Default values
                // ... Here some code

                // Create data array
                $data= array (                        
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_api-projectName',
                                'name'      => 'probance-optin_api-projectName',
                                'size'      => '',
                                'properties-label' => 'Project Name',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => ''
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_api-account',
                                'name'      => 'probance-optin_api-account',
                                'size'      => '',
                                'properties-label' => 'API Account',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => ''
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_api-login',
                                'name'      => 'probance-optin_api-login',
                                'size'      => '',
                                'properties-label' => 'API Login',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => ''
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_api-passwd',
                                'name'      => 'probance-optin_api-passwd',
                                'size'      => '',
                                'properties-label' => 'API Password',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => ''
                        ),
                        array (
                                'type'      => 'select',
                                'subtype'   => '',
                                'id'    => 'probance-optin_api-infra',
                                'name'      => 'probance-optin_api-infra',
                                'size'      => '',
                                'properties-label' => 'API Server',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => 'my-probance.one',
                                'select_options' => array("my-probance.one"=>"France","probance.jp"=>"Japan","probance.ca"=>"Canada")
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_api-cbdebug',
                                'name'      => 'probance-optin_api-cbdebug',
                                'size'      => '',
                                'properties-label' => 'Debug mode',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "1"
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_api-cbmulti',
                                'name'      => 'probance-optin_api-cbmulti',
                                'size'      => '',
                                'properties-label' => 'Multi-site',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "0"
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_api-multiprefix',
                                'name'      => 'probance-optin_api-multiprefix',
                                'size'      => '',
                                'properties-label' => 'Site Prefix',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => ''
                        )
                );

                // Set default value using translations/_default.json file
                $data=Translations::setDefaultValues($data);

                parent::__construct($data);
        }

        public function getSectionInfo()
        {
                return array("id" => "probance-optin_section-api", "title" => "Probance API configuration");
        }
}

?>