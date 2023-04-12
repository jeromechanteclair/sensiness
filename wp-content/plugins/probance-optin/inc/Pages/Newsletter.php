<?php 

/**
 * @package probance-optin
 */

namespace Inc\Pages;

use Inc\Common\Utils;
use Inc\Common\ProbanceAPI;
use Inc\Data\Translations;

class Newsletter 
{
    public function register()
    {
        add_action( 'wp_ajax_load_newsletter_form', array( $this, 'probance_submit_newletter_form') );
        add_action( 'wp_ajax_nopriv_load_newsletter_form', array( $this, 'probance_submit_newletter_form') );

        // shortcode const

        if (!shortcode_exists(NEWSLETTER_ATTR['name'])) {
            add_shortcode( NEWSLETTER_ATTR['name'], array( $this, 'probance_wbanner_shortcode') );
        } else {
            remove_shortcode(NEWSLETTER_ATTR['name']);
            add_shortcode( NEWSLETTER_ATTR['name'], array( $this, 'probance_wbanner_shortcode') );
        }

        update_option('probance-optin_banner-lbshortcode', NEWSLETTER_ATTR['shortcode']);

        add_action( 'wp_enqueue_scripts', array( $this, 'probance_newsletter_form_style') , 9999);

    }

    public function probance_wbanner_shortcode($atts = [], $content = null) 
    {
        // Atts
        // normalize attribute keys, lowercase
	    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

        wp_nonce_field( 'probance-signin-newsletter', 'probance-newsletter-verif');

        // checkbox options 
        $names_checked=get_option('probance-optin_banner-cbnames');
        $recaptcha_checked=get_option('probance-optin_banner-cbrecaptcha');

        // Maintitle variables
        $maintitle_class=get_option('probance-optin_banner-lbtitle-edit-class');
        $maintitle_html_tag= (get_option('probance-optin_banner-lbtitle-edit-html-tag') !== '') ? get_option('probance-optin_banner-lbtitle-edit-html-tag') : $mainTitleHtmlTag;

        // Subtitle variables
        $subtitle_class=get_option('probance-optin_banner-lbsubtitle-edit-class');
        $subtitle_html_tag= (get_option('probance-optin_banner-lbsubtitle-edit-html-tag') !== '') ? get_option('probance-optin_banner-lbsubtitle-edit-html-tag') : $subTitleHtmlTag;
        $recaptcha_sitekey=get_option('probance-optin_banner-recaptchasitekey');

        // First name variables
        $fname_label_class=get_option('probance-optin_banner-lbfname-edit-label-class');
        $fname_input_class=get_option('probance-optin_banner-lbfname-edit-input-class');

        // Last name variables
        $lname_label_class=get_option('probance-optin_banner-lblname-edit-label-class');
        $lname_input_class=get_option('probance-optin_banner-lblname-edit-input-class');

        // Email variables
        $email_label_class=get_option('probance-optin_banner-lbemail-edit-label-class');
        $email_input_class=get_option('probance-optin_banner-lbemail-edit-input-class');

        // Button variables
        $btn_class=get_option('probance-optin_banner-lbbtn-edit-class');

        $two_cols=get_option('probance-optin_banner-cbtwocols');

        // Message Submit Form
        $conf_msg_style=get_option('probance-optin_banner-conf-message-edit-css-style');
        $err_msg_style=get_option('probance-optin_banner-conf-error-message-edit-css-style');

        $user_locale=Utils::getUserLanguage();

        $arr=Translations::getTranslatedFields('newsletter', $user_locale);

        // Shortcode Args
        $form_id=(isset($atts['id']) ? strval($atts['id']) : '0');
        $probance_form_block_style=(isset($atts['block_style']) ? $atts['block_style'] : 'width: 100%;');
        $probance_form_style=(isset($atts['form_style']) ? $atts['form_style'] : '');

        $content .= '<div id="probance_form_block" style="'.$probance_form_block_style.'">
                        <'.$maintitle_html_tag.' class="p-nlform-maintitle '.$maintitle_class.'">'. $arr['probance-optin_banner-lbtitle'] .'</'.$maintitle_html_tag.'>
                        <'.$subtitle_html_tag.' class="p-nlform-subtitle '.$subtitle_class.'">'. $arr['probance-optin_banner-lbsubtitle'] .'</'.$subtitle_html_tag.'>
                        <form id="probance_form_'.$form_id.'" data-probance-form-id="'.$form_id.'" style="'.$probance_form_style.'" method="post" action="/">
                            <input type="hidden" id="g-token" name="g-token" />';

        if($names_checked){

            $content .=     '<div class="fields_block">
                                <div class="names_block'. ($two_cols == 1 ? ' two-cols' : ' one-col') .'">
                                    <div class="fname_block">
                                    <label for="fname" class="p-nlform-fname-label '.$fname_label_class.'" >'. $arr['probance-optin_banner-lbfname'] .'</label>
                                    <input type="text" id="probance_newsletter_fname" name="fname" value="" class="p-nlform-fname-input '.$fname_input_class.'" placeholder="'.$arr['probance-optin_banner-lbfname'].'">
                                    <span class="probance_msg_wrong_fname hidden" data-type="message">'.$arr['probance-optin_banner-fname-error-msg'].'</span><br>
                                </div>
                                <div class="lname_block">
                                    <label for="lname" class="p-nlform-lname-label '.$lname_label_class.'">'. $arr['probance-optin_banner-lblname'] .'</label>
                                    <input type="text" id="probance_newsletter_lname" name="lname" value="" class="p-nlform-lname-input '.$lname_input_class.'" placeholder="'.$arr['probance-optin_banner-lblname'].'">
                                    <span class="probance_msg_wrong_lname hidden" data-type="message">'.$arr['probance-optin_banner-lname-error-msg'].'</span><br>
                                </div>
                            </div>';
        }

            $content .=     '<div class="email_block">
                                <label for="email" class="p-nlform-email-label '.$email_label_class.'">'. $arr['probance-optin_banner-lbemail'] .' *</label>
                                <input type="text" id="probance_newsletter_email" name="email" class="p-nlform-email-input '.$email_input_class.'" placeholder="'.$arr['probance-optin_banner-lbemail'].'">
                                <span class="probance_msg_wrong_email hidden" data-type="message">'.$arr['probance-optin_banner-email-error-msg'].'</span><br>
                                <span class="probance_msg_empty_email hidden" data-type="message">'.$arr['probance-optin_banner-email-empty-msg'].'</span><br>
                            </div>  
                            <input type="hidden" id="probance_newsletter_info_fk" name="info_fk"/>
                        ';
        
                    
        $content .= '   
                        <div id="btn_block">
                            <input type="submit" id="probance_newsletter_submit_btn" value="'. $arr['probance-optin_banner-lbbtn'] .'" class="p-nlform-btn-input '.$btn_class.'"/>
                            <div style="'.$conf_msg_style.'" class="probance_message_submit hidden" data-type="message">
                                <p class="message">'.$arr['probance-optin_banner-conf-message'].'</p>
                                <p class="icon_close">x</p> 
                            </div>
                            <div style="'.$err_msg_style.'" class="probance_message_submit_faillure hidden" data-type="message">
                                <p class="message">'.$arr['probance-optin_banner-conf-error-message'].'</p>
                                <p class="icon_close">x</p> 
                            </div>
                        </div>
                    </form>
                </div>';

        return $content;
    }

    public function probance_submit_newletter_form () {

        $api=new ProbanceAPI();

        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            $debug=0;
        }

        // Retrieving checkboxes to check reCAPTCHA is activated
        $recaptcha_checked=get_option('probance-optin_banner-cbrecaptcha');

        $recaptcha_error=0;
        if($debug == 1) Utils::write_log($_POST);

        // reCAPTCHA verification of the client-side token
        if ( $recaptcha_checked==1 && isset($_POST['g-token']) ) {
            if($debug == 1) Utils::write_log('[PROBANCE - optin] reCAPTCHA is verifying the client-side token.');
            $secret = get_option('probance-optin_banner-recaptchaprivatekey');
            $verifyResponse = file_get_contents ('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-token']); 
            $responseData = json_decode($verifyResponse); 
            if ($responseData->success) {
                Utils::write_log('[PROBANCE - optin] reCAPTCHA response - [SUCCESS]'); 
            } else {
                Utils::write_log('[PROBANCE - optin] reCAPTCHA response - [ERROR]');
                $recaptcha_error+=1;
                return 0;
            }
        }


        if($debug == 1)
                Utils::write_log('[PROBANCE - optin] NEWSLETTER FORM - BEGIN ###################################################################');
        /*
        * Retrieve form fields via l'AJAX js/probance_submit_newsletter.js
        */
        try {
            $email = $_REQUEST['email'];

            if($debug == 1)
                Utils::write_log('[PROBANCE - optin] Récupération de l\'email ('.$email.') dans formulaire de Newsletter.');

            $fname = $_REQUEST['fname'];

            if($debug == 1)
                Utils::write_log('[PROBANCE - optin] Récupération du prénom ('.$fname.') dans formulaire de Newsletter.');

            $lname = $_REQUEST['lname'];

                if($debug == 1)
                    Utils::write_log('[PROBANCE - optin] Récupération du nom ('.$lname.') dans formulaire de Newsletter.');

            if ($email == "vide" && $fname == "vide" && $lname == "vide" ) {
                Utils::write_log('HoneyPot a reçu une tentative de spam');
                return;
            }
        } catch (Exception $e) { Utils::write_log('[PROBANCE - optin] Erreur dans la récupération des données du formulaire de Newsletter.');}

        $error=0;

        /*
        * Retrieve API parameters
        */
        try{
            $apiprojectName=get_option('probance-optin_api-projectName');
            $apiaccount=get_option('probance-optin_api-account');
            $apiLogin=get_option('probance-optin_api-login');
            $apiPass=get_option('probance-optin_api-passwd');
            $apiInfra=get_option('probance-optin_api-infra');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération option API : '.$e);
            $error=1;
        }

        if($error==0){

            //Call WP API 
            $user_id=get_current_user_id();

            // Prepare array parameter
            $param=array('optin_flag'=> 1);
            if($email != null) $param['email']=$email;
            if($fname != null) $param['name1']=$fname;
            if($lname != null) $param['name2']=$lname;
            if($user_id!=0) $param['customer_id']=$user_id;

            if($debug==1) {
                foreach ($param as $key => $value) {
                    Utils::write_log('[PROBANCE - optin] paramètre(s) pour l\'API - '.$key.' : '.$value);
                }
            }

            /*
            * Block conditionnal update/create a customer
            */
            // If customer_id doesn't exist in Probance, we check if we know the email, If OK we update the optin and the cust_id if it exists in WP, else we create the customer with max. information
            if(!$api->apicontact_exist(array('customer_id'=>$user_id))){
                if($debug==1)
                    Utils::write_log('[PROBANCE - optin] il existe pas chez nous (basé sur cust_id), on check si on connait l\'email');

                // Do we know the email ?
                if($api->apicontact_exist(array('email'=>$email))){
                    if($debug==1)
                        Utils::write_log('[PROBANCE - optin] on connait l\'email');
                        
                        //If yes we update the contact
                        $result=$api->apicontact_update('email',$param);

                        if($debug==1)
                            Utils::write_log('[PROBANCE - optin] API response : ' . $result[0]);

                        return wp_send_json_success($result[0]);
                }else{
                    if($debug==1)
                        Utils::write_log('[PROBANCE - optin] on connait pas l\'email, on crée le user');

                    $param['registration_date']=date('Y-m-d H:i:s');
                    
                    // Création du contact avec Prénom, Nom (si non null) date d'enregistrement, email et opt-in à 1          
                    $result=$api->apicontact_create($param);

                    if($debug==1)
                        Utils::write_log('[PROBANCE - optin] API response : ' . $result[0]);

                    return wp_send_json_success($result[0]);
                } 
            }else{
                if($debug==1)
                    Utils::write_log('[PROBANCE - optin] Il existe chez nous');
                
                // If he exists, updating email and optin + last name and first name
                $result=$api->apicontact_update('customer_id',$param);

                if($debug==1)
                    Utils::write_log('[PROBANCE - optin] API response ' . $result[0]);

                return wp_send_json_success($result[0]);
            }
        }
        if($debug == 1)
                Utils::write_log('[PROBANCE - optin] NEWSLETTER FORM - END #####################################################################');
    }

    public function probance_newsletter_form_style () {

        // Main title properties
        $maintitle_css=get_option('probance-optin_banner-lbtitle-edit-h-style');

        // Subtitle properties
        $subtitle_css=get_option('probance-optin_banner-lbsubtitle-edit-h-style');

        // First name properties
        $fname_input_css=get_option('probance-optin_banner-lbfname-edit-input-style');
        $fname_input_hover_css=get_option('probance-optin_banner-lbfname-edit-input-hover-style');
        $fname_input_focus_css=get_option('probance-optin_banner-lbfname-edit-input-focus-style');
        $fname_label_css=get_option('probance-optin_banner-lbfname-edit-label-style');

        // Last name properties
        $lname_input_css=get_option('probance-optin_banner-lblname-edit-input-style');
        $lname_input_hover_css=get_option('probance-optin_banner-lblname-edit-input-hover-style');
        $lname_input_focus_css=get_option('probance-optin_banner-lblname-edit-input-focus-style');
        $lname_label_css=get_option('probance-optin_banner-lblname-edit-label-style');

        // Email properties
        $email_input_css=get_option('probance-optin_banner-lbemail-edit-input-style');
        $email_input_hover_css=get_option('probance-optin_banner-lbemail-edit-input-hover-style');
        $email_input_focus_css=get_option('probance-optin_banner-lbemail-edit-input-focus-style');
        $email_label_css=get_option('probance-optin_banner-lbemail-edit-label-style');

        // Button properties
        $btn_css=get_option('probance-optin_banner-lbbtn-edit-css-style');
        $btn_hover_css=get_option('probance-optin_banner-lbbtn-edit-hover-style');
        $btn_focus_css=get_option('probance-optin_banner-lbbtn-edit-focus-style');

        // Error message properties
        $fname_error_css=get_option('probance-optin_banner-fname-error-msg-edit-css-style');
        $lname_error_css=get_option('probance-optin_banner-lname-error-msg-edit-css-style');
        $email_error_css=get_option('probance-optin_banner-email-error-msg-edit-css-style');

        // Conf. message properties
        $conf_message_css=get_option('probance-optin_banner-conf-message-edit-css-style');


        // CSS variable
        $css = '.p-nlform-maintitle {'.$maintitle_css.'}
    .p-nlform-subtitle {'.$subtitle_css.'}
    input.p-nlform-fname-input{'.$fname_input_css.'}
    input.p-nlform-fname-input:hover{'.$fname_input_hover_css.'}
    input.p-nlform-fname-input:focus{'.$fname_input_focus_css.'}
    label.p-nlform-fname-label{'.$fname_label_css.'}
    input.p-nlform-lname-input{'.$lname_input_css.'}
    input.p-nlform-lname-input:hover{'.$lname_input_hover_css.'}
    input.p-nlform-lname-input:focus{'.$lname_input_focus_css.'}
    label.p-nlform-lname-label{'.$lname_label_css.'}
    input.p-nlform-email-input{'.$email_input_css.'}
    input.p-nlform-email-input:hover{'.$email_input_hover_css.'}
    input.p-nlform-email-input:focus{'.$email_input_focus_css.'}
    label.p-nlform-email-label{'.$email_label_css.'}
    input.p-nlform-btn-input{'.$btn_css.'}
    input.p-nlform-btn-input:hover{'.$btn_hover_css.'}
    input.p-nlform-btn-input:focus{'.$btn_focus_css.'}
    span.probance_msg_wrong_fname{'.$fname_error_css.'}
    span.probance_msg_wrong_lname{'.$lname_error_css.'}
    span.probance_msg_wrong_email, span.probance_msg_empty_email{'.$email_error_css.'}
    p.probance_message_submit{'.$conf_message_css.'}';

        // css add to the .css file
        wp_add_inline_style('probance-newsletter-style', $css);
    }

}

?>