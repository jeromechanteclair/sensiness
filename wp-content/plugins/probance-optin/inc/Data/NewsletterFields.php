<?php 

/**
 * @package probance-optin
 */

namespace Inc\Data;

use Inc\Data\Data;
use Inc\Data\Translations; 

class NewsletterFields extends Data 
{
        public function __construct()
        {       
                // Default values
                $mainTitleHtmlTag="h2";
                $subTitleHtmlTag="h4";
                $defaultFNameErrMsgCss='color:red; font-size: 12px;';
                $defaultLNameErrMsgCss=$defaultFNameErrMsgCss;
                $defaultEmailErrMsgCss=$defaultFNameErrMsgCss;
                
                // Create data array
                $data= array (                        
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbtitle',
                                'name'      => 'probance-optin_banner-lbtitle',
                                'properties' => array ( 'edit-h-style'  => array('label' => 'Title CSS', 'default_value' => ''),
                                                        'edit-html-tag' => array('label' => 'HTML tag', 'default_value' => "$mainTitleHtmlTag"),  
                                                        'edit-class' => array('label' => 'Edit Class', 'default_value' => '')),
                                'properties-label' => 'Main Title',
                                'size'      => '60',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbsubtitle',
                                'name'      => 'probance-optin_banner-lbsubtitle',
                                'properties' => array ( 'edit-h-style'  => array('label' => 'Title CSS', 'default_value' => ''),
                                                        'edit-html-tag' => array('label' => 'HTML tag', 'default_value' => "$subTitleHtmlTag"),  
                                                        'edit-class' => array('label' => 'Edit Class', 'default_value' => '')),
                                'properties-label' => 'Subtitle',
                                'size'      => '60',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_banner-cbnames',
                                'name'      => 'probance-optin_banner-cbnames',
                                'size'      => '',
                                'properties-label' => 'With First Name & Last Name',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "0",
                                'to_translate' => false
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_banner-cbtwocols',
                                'name'      => 'probance-optin_banner-cbtwocols',
                                'size'      => '',
                                'properties-label' => 'Display Names same row',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => "0",
                                'to_translate' => false
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbfname',
                                'name'      => 'probance-optin_banner-lbfname',
                                'properties' => array ('edit-input-style' => array ( 'label' => 'Input CSS', 'default_value' => ''),
                                                        'edit-input-hover-style' => array( 'label' => 'Input Hover CSS', 'default_value' => ''),
                                                        'edit-input-focus-style' => array( 'label' => 'Input Focus CSS', 'default_value' => ''),
                                                        'edit-label-style' => array( 'label' => 'Label CSS', 'default_value' => ''), 
                                                        'edit-label-class' => array( 'label' => 'Label Class', 'default_value' => ''),  
                                                        'edit-input-class' => array( 'label' => 'Input Class', 'default_value' => '')),
                                'properties-label' => 'First Name',
                                'size'      => '',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-fname-error-msg',
                                'name'      => 'probance-optin_banner-fname-error-msg',
                                'size'      => '',
                                'properties' => array ('edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => $defaultFNameErrMsgCss)),
                                'properties-label'=> 'Fisrt Name Error Msg',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lblname',
                                'name'      => 'probance-optin_banner-lblname',
                                'size'      => '',
                                'properties' =>  array ('edit-input-style' => array ( 'label' => 'Input CSS', 'default_value' => ''),
                                                        'edit-input-hover-style' => array( 'label' => 'Input Hover CSS', 'default_value' => ''),
                                                        'edit-input-focus-style' => array( 'label' => 'Input Focus CSS', 'default_value' => ''),
                                                        'edit-label-style' => array( 'label' => 'Label CSS', 'default_value' => ''), 
                                                        'edit-label-class' => array( 'label' => 'Label Class', 'default_value' => ''),  
                                                        'edit-input-class' => array( 'label' => 'Input Class', 'default_value' => '')),
                                'properties-label' => 'Last Name',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lname-error-msg',
                                'name'      => 'probance-optin_banner-lname-error-msg',
                                'size'      => '',
                                'properties' => array ('edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => $defaultLNameErrMsgCss)),
                                'properties-label' => 'Last Name Error Msg',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbemail',
                                'name'      => 'probance-optin_banner-lbemail',
                                'properties' =>  array ('edit-input-style' => array ( 'label' => 'Input CSS', 'default_value' => ''),
                                                        'edit-input-hover-style' => array( 'label' => 'Input Hover CSS', 'default_value' => ''),
                                                        'edit-input-focus-style' => array( 'label' => 'Input Focus CSS', 'default_value' => ''),
                                                        'edit-label-style' => array( 'label' => 'Label CSS', 'default_value' => ''), 
                                                        'edit-label-class' => array( 'label' => 'Label Class', 'default_value' => ''),  
                                                        'edit-input-class' => array( 'label' => 'Input Class', 'default_value' => '')),
                                'properties-label' => 'Email',
                                'size'      => '',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-email-error-msg',
                                'name'      => 'probance-optin_banner-email-error-msg',
                                'size'      => '',
                                'properties' =>array (  'edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => $defaultEmailErrMsgCss)),
                                'properties-label'      => 'Email Error Msg',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-email-empty-msg',
                                'name'      => 'probance-optin_banner-email-empty-msg',
                                'size'      => '',
                                'properties' =>array (  'edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => $defaultEmailErrMsgCss)),
                                'properties-label'      => 'Email Empty Msg',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbbtn',
                                'name'      => 'probance-optin_banner-lbbtn',
                                'size'      => '',
                                'properties' => array ( 'edit-css-style' => array( 'label' => 'Button CSS', 'default_value' => ''), 
                                                        'edit-hover-style' => array( 'label' => 'Hover CSS', 'default_value' => ''), 
                                                        'edit-focus-style' => array( 'label' => 'Focus CSS', 'default_value' => ''),  
                                                        'edit-class' => array( 'label' => 'Edit Class', 'default_value' => '')),
                                'properties-label' => 'Button',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-conf-message',
                                'name'      => 'probance-optin_banner-conf-message',
                                'size'      => '',
                                'properties' => array ( 'edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => '')),
                                'properties-label' => 'Confirmation Message',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-conf-error-message',
                                'name'      => 'probance-optin_banner-conf-error-message',
                                'size'      => '',
                                'properties' => array ( 'edit-css-style' => array( 'label' => 'Style CSS', 'default_value' => '')),
                                'properties-label' => 'Error Message',
                                'required' => 'required="required"',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'checkbox',
                                'id'    => 'probance-optin_banner-cbrecaptcha',
                                'name'      => 'probance-optin_banner-cbrecaptcha',
                                'size'      => '',
                                'properties-label'      => 'Add reCAPTCHA v3',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '0',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-recaptchasitekey',
                                'name'      => 'probance-optin_banner-recaptchasitekey',
                                'size'      => '60',
                                'properties-label'      => 'Site Key',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-recaptchaprivatekey',
                                'name'      => 'probance-optin_banner-recaptchaprivatekey',
                                'size'      => '60',
                                'properties-label'      => 'Secret Key',
                                'required' => '',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        ),
                        array (
                                'type'      => 'input',
                                'subtype'   => 'text',
                                'id'    => 'probance-optin_banner-lbshortcode',
                                'name'      => 'probance-optin_banner-lbshortcode',
                                'size'      => '',
                                'properties-label' => 'Shortcode',
                                'required' => '',
                                'disabled' => 'disabled',
                                'value_type'=>'normal',
                                'wp_data' => 'option',
                                'default_value' => '',
                                'to_translate' => true
                        )

                );

                // Set default value using translations/_default.json file
                $data=Translations::setDefaultValues($data);

                parent::__construct($data);
        }

        public function getSectionInfo()
        {
                return array("id" => "probance-optin_section-wbanner", "title" => "Newsletter");
        }
}

?>