jQuery(document).ready(function($) {

    

    ///////////////////////////////////////////////////////
    // VARIABLES
    ///////////////////////////////////////////////////////
    /*
     * Variables GLOBALES récupérées depuis probance-optin.php
     */
    var debug = probance_newsletter.debug;
	  var path = probance_newsletter.ajaxurl;
    var names = probance_newsletter.cbnames;
    var sitekey = probance_newsletter.rc_sitekey;
    var recaptcha = probance_newsletter.cb_recaptcha;

    ///////////////////////////////////////////////////////
    // FUNCTIONS
    ///////////////////////////////////////////////////////
    // Function to validate email field
    function validateEmail(email) {
      const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
    }

    // Function to validate name fields
    function validateName(name) {
      const re = /^[a-zA-Z-àèìòùÀÈÌÒÙáéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇßØøÅåÆæœ'. ]+$/;

      if(name == "") return true;

      return re.test(name);
    }

    // Function to clear error messages
    function cleanErrorMsg() {
      
      // Hide error messages
      $('span[data-type="message"]').addClass('hidden');
      $('p[data-type="message"]').addClass('hidden');

      // Remove red border on inputs fields
      $('input.error').removeClass("error");
      

    }

    // Function to clear fileds values
    function cleanFormFields() {
      $('input#probance_newsletter_email').val("");
      $('input#probance_newsletter_fname').val("");
      $('input#probance_newsletter_lname').val("");
    }

    // Function to POST form to the server
    function sendToServerAction (el) {

      cleanErrorMsg();

      /*
      * Retrieve form data
      */
      var form_data = el;
      var form_id=el.id;

      // Valeur par défaut des variables
      var fname = null;
      var lname = null;
      var email = null;
      var hp = null;

      if(names == 1) {
        var gtoken =form_data[0].value;
        var fname =form_data[1].value;
        var lname =form_data[2].value;
        var email =form_data[3].value;
        var hp =form_data[4].value;
      } else {
        var gtoken =form_data[0].value;
        var email =form_data[1].value;
        var hp =form_data[2].value;
      }

      // Mode DEBUG
      if(debug == 1) {
          console.log("URL for AJAX : " + path);
          console.log("gtoken (reCAPTCHA): " + gtoken);
          console.log("First name : " + fname );
          console.log("Last name : "    + lname );
          console.log("Email address : "  + email );
      }

      /*
      * Field verification
      */
      $fields_are_valid=true;

      if(!validateName(fname)) {

        $fields_are_valid=false;

        // Display error msg
        $(`form#${form_id} span.probance_msg_wrong_fname`).removeClass('hidden');
        $(`form#${form_id} input#probance_newsletter_fname`).addClass("error");
        
        // Mode DEBUG
        if(debug == 1) console.log("[NEWSLETTER FORM] Le first name est incorrecte");
        
      }

      if(!validateName(lname)) {

        $fields_are_valid=false;

        // Display error msg
        $(`form#${form_id} span.probance_msg_wrong_lname`).removeClass('hidden');
        $(`form#${form_id} input#probance_newsletter_lname`).addClass("error");

        // Mode DEBUG
        if(debug == 1) console.log("[NEWSLETTER FORM] Le last name est incorrecte");

      }
      
      if(!validateEmail(email)) {

        $fields_are_valid=false;

        // If email empty we init another error msg
        if(email == "") {
          $(`form#${form_id} span.probance_msg_empty_email`).removeClass("hidden");
        } else {
          // Display error msg
          $(`form#${form_id} span.probance_msg_wrong_email`).removeClass("hidden");
          $(`form#${form_id} input#probance_newsletter_email`).addClass("error");
        }

        // Mode DEBUG
        if(debug == 1) console.log("[NEWSLETTER FORM] L'adresse email est incorrecte");

      }

      // If there is an error we exit the function
      if (!$fields_are_valid) return false; 

      /*
      * POST REQUEST
      */
      // Mode DEBUG
      if(debug == 1) console.log("[NEWSLETTER FORM] Envoi du formulaire vers le serveur.");

      // Check the honey pot field. If not empty, a robot is detected. Else we can send POST request.
      if (hp != '' && hp != null) {
        
        // Mode DEBUG
        if(debug == 1) console.log("Suspicious action detected by Honey Pot.");

        // Fake news
        $(`form#${form_id} .probance_message_submit`).removeClass("hidden");

        // Clear fields
        cleanFormFields();

        return false;

      } else {
        
        /*
        * POST REQUEST -> load_newsletter_form.
        * SERVER SIDE handle by the probance_submit_newletter_form function into probance-optin.php
        */
        $.ajax({
            url: path,
            type: "POST",
            data: {
                    action: 'load_newsletter_form', 
                    fname: fname,
                    lname: lname,
                    email: email,
                    'g-token': gtoken
            },
            success : function(data) {

              console.log('Data retrieved from PHP  : ' + Object.values(data));

              if (Object.values(data)[1] == "200") {

                $(`form#${form_id} .probance_message_submit`).removeClass("hidden");
            
                cleanFormFields();

                return true;

              } else {
                
                $(`form#${form_id} .probance_message_submit_faillure`).removeClass("hidden");  
          
                return false;
              }
              
            }
        });
      }
    }

    $('form[id^="probance_form_"]').on('submit', function(e){

      e.preventDefault();
      
      if (recaptcha==1){
        // Mode DEBUG
        if(debug == 1) console.log("reCAPTCHA verification...");

        grecaptcha.ready(function() {
          console.log("sitekey : " + sitekey);
          grecaptcha.execute(sitekey, {action: 'submit'}).then(function(token) {
              // Mode DEBUG
              if(debug == 1)console.log("g-token : " + token);

              $("#g-token").val(token);

              if(debug == 1)console.log("reCAPTCHA security. Form is send to the server with the secure method.");
              
              sendToServerAction(e.target);
          });
        }); } else {
          if(debug == 1)console.log("No reCAPTCHA security. Form is send to the server with the standard method.");
          sendToServerAction(e.target);
      }
    });

    $('p.icon_close').on('click', function(e){
      // console.log(e.target.parentNode);
      $(`div.${e.target.parentNode.className}`).addClass('hidden');
    });
});
