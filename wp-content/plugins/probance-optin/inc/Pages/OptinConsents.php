<?php 

/**
 * @package probance-optin
 */

namespace Inc\Pages;

use Inc\Common\ProbanceAPI;
use Inc\Common\Utils;
use Inc\Data\Translations;

class OptinConsents 
{
    public function register()
    {
        // Add the checkbox into checkout page
        if(get_option('probance-optin_webel-cborder')==1){
            add_action('woocommerce_review_order_before_submit', array( $this, 'probance_add_optin_cb') );
            // Call API during checkout
            add_action('woocommerce_checkout_process', array( $this, 'probance_call_api') ); 
        }
        //Add the checkbox into register page
        if(get_option('probance-optin_webel-cbsignin')==1){
            add_action('woocommerce_register_form', array( $this, 'probance_add_optin_cb') );
            // Call API during registering
            add_action( 'user_register', array( $this, 'probance_call_api'));
        }
        //Add the checkbox into details section of the account page
        if(get_option('probance-optin_webel-cbaccount')==1){  
            add_action('woocommerce_edit_account_form', array( $this, 'probance_add_optin_cb') );
            // Call API during mofication
            add_action( 'woocommerce_save_account_details', array( $this, 'probance_call_api') );
        }
    }

    //fonction permettant l'ajout de la checkbox optin
    public function probance_add_optin_cb(){
        
        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            $debug=0;
        }

        if($debug==1)
            Utils::write_log('[PROBANCE - optin] Checkout page : '.is_checkout().' UserID : '.get_current_user_id().' Option : '.get_option('probance-optin_webel-cborder-behave'));
        
        
        // If we are on checkout page AND customer is logged in AND the option is activated, nothingis displayed
        if(!is_checkout() || get_current_user_id()==0 || get_option('probance-optin_webel-cborder-behave')!=1){


            //
            try{
                $probanceLabelCB=get_option('probance-optin_webel-cblabel');
                $probanceCBStyleCss=get_option('probance-optin_webel-cblabel-edit-css-style');
            }catch(Exception $e){
                Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_webel_cblabel" : '.$e);
            }

            //Get API infos
            $error=0;
            try{
                $api=new ProbanceApi();
                $apiMulti=get_option('probance-optin_api-cbmulti');
                $apiPrefix=get_option('probance-optin_api-multiprefix');
            }catch(Exception $e){
                Utils::write_log('[PROBANCE - optin] Erreur récupération option API : '.$e);
                $error=1;
            }

            $checked='';
            if($error==0){
                //Call API to know default value
                $param=array();
                $user_id=get_current_user_id();

                if($user_id != 0 && $apiMulti==1)
                    $user_id=$apiPrefix.$user_id;

                if($debug==1)
                    Utils::write_log('[PROBANCE - optin] User id : '.$user_id);
                
                if($user_id != 0){
                    $param['customer_id']=$user_id;

                    if($debug==1)
                        Utils::write_log('[PROBANCE - optin] User ID : '.$param['customer_id']);
                    
                    $result=$api->apicontact_getInfos($param);


                    if(strpos($result, "ERROR")===false){
                        //customer exists
                        $data=json_decode($result,true);
                        if($debug==1)
                            Utils::write_log('[PROBANCE - optin] optin_flag : '.$data['client']['optin_flag']);
                        if($data['client']['optin_flag']==1)
                            $checked='checked';
                    }
                }

                $user_locale=Utils::getUserLanguage();

                $arr=Translations::getTranslatedFields('optin', $user_locale);

                if(isset($arr['probance-optin_webel-cblabel']))
                    echo "<div id='probance_optin_cb_wrapper'><input type='checkbox' id='probance_optin_cb' style='$probanceCBStyleCss' name='probance_optin_cb' $checked /><span>" . $arr['probance-optin_webel-cblabel'] . "</span></div>";
            }
        }
        
    }

    public function probance_call_api($user_id) {

        try{
            $debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération "probance-optin_api-cbdebug" : '.$e);
            $debug=0;
        }
    
        if($debug==1){
            Utils::write_log('[PROBANCE - optin] User_id parameter : '.$user_id);
            
            $message='';
            if(is_checkout()){
                $message.="Page de checkout ";
                if(get_current_user_id()!=0){
                    $message.="User logué ";
                    if(get_option('probance-optin_webel-cborder-behave')==1)
                        $message.="Option ne pas afficher la CB active ";
                    else
                        $message.="Option ne pas afficher la CB désactivée ";
                }else
                    $message.="User non logué ";
            
            Utils::write_log('[PROBANCE - optin] '.$message);
            }
        }
            
        
        // If we are on checkout page AND customer is logged in AND the option is activated, nothingis displayed
        if(!is_checkout() || get_current_user_id()==0 || get_option('probance-optin_webel-cborder-behave')!=1){
            
            // The function could be called on 3 different submited form. On each of them, the emailfield is differently called
            if ((isset($_POST['email']) && $_POST['email'] != '') || (isset($_POST['billing_email']) && $_POST['billing_email'] != '') || (isset($_POST['account_email']) && $_POST['account_email'] != '')) {
                
                // Checkbox verification
                if(isset($_POST['probance_optin_cb']))
                    $optin=1;
                else
                    $optin=0;
    
                //Call probance API
                $error=0;

                try{
                    $api=new ProbanceAPI();
                    $apiMulti=get_option('probance-optin_api-cbmulti');
                    $apiPrefix=get_option('probance-optin_api-multiprefix');
                }catch(Exception $e){
                    Utils::write_log('[PROBANCE - optin] Erreur récupération option API : '.$e);
                    $error=1;
                }
    
                // Find the good email field
                if(isset($_POST['email']))
                    $email=$_POST['email'];
                elseif(isset($_POST['billing_email']))
                    $email=$_POST['billing_email'];
                elseif(isset($_POST['account_email']))
                    $email=$_POST['account_email'];
    
                $prefixedEmail=$apiPrefix.$email;
    
                if($user_id == ''){}
                    $user_id=get_current_user_id();
    
                if($apiMulti==1 && $user_id != 0)
                    $user_id=$apiPrefix.$user_id;
    
                if($debug==1)
                    Utils::write_log('[PROBANCE - optin] UserID : '.$user_id);
    
                if($error==0){
    
                    if($apiMulti==1){
                        $emailKeyField="email_site";
                        $emailKeyVal=$prefixedEmail;
                    }else{
                        $emailKeyField="email";
                        $emailKeyVal=$email;
                    }
                    $param=array($emailKeyField=>$emailKeyVal,'optin_flag'=>$optin);
                        
                    if($user_id!=0)
                        $param['customer_id']=$user_id;
                    
                    //If multi-site, we add the real email information on registered fields
                    if($apiMulti==1)
                        $param['email']=$email;
    
                    
                    // If customer_id doesn't exist in Probance, we check if we know the email, If OK we update the optin and the cust_id if it exists in WP, else we create the customer with max. information
                    if(!$api->apicontact_exist(array('customer_id'=>$user_id))){
                        if($debug==1)
                            Utils::write_log('[PROBANCE - optin] il existe pas chez nous (basé sur cust_id), on check si on connait lemail');
                        // Do we know the email ?
                        if($api->apicontact_exist(array($emailKeyField=>$emailKeyVal))){
                            if($debug==1)
                                Utils::write_log('[PROBANCE - optin] on connait lemail');
                                //If yes we update the contact
                                $result=$api->apicontact_update($emailKeyField,$param);
                        }else{
                            if($debug==1)
                                Utils::write_log('[PROBANCE - optin] on connait pas lemail, on crée le user');
                            
                            // Else we retrieve all available information
                            $param['registration_date']=date('Y-m-d H:i:s');
    
                            if(isset($_POST['billing_first_name'])){
                                $param['name1']=$_POST['billing_first_name'];
                            }elseif(isset($_POST['account_first_name'])){
                                $param['name1']=$_POST['account_first_name'];
                            }
    
                            if(isset($_POST['billing_last_name'])){
                                $param['name2']=$_POST['billing_last_name'];
                            }elseif(isset($_POST['account_last_name'])){
                                $param['name2']=$_POST['account_last_name'];
                            }               
                            $result=$api->apicontact_create($param);
                        } 
                    }else{
                        if($debug==1)
                            Utils::write_log('[PROBANCE - optin] Il existe chez nous');
                        
                        // If he exists, updating email and optin + last name and first name if we are on the account page (we don't update the last and first name if we are on the checkout page)
                        if(isset($_POST['account_first_name'])){
                            $param['name1']=$_POST['account_first_name'];
                        }
                        if(isset($_POST['account_last_name'])){
                            $param['name2']=$_POST['account_last_name'];
                        } 
                        $result=$api->apicontact_update('customer_id',$param);
                    }
                }
            } 
        }
    }  
}

?>