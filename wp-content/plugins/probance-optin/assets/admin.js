jQuery(document).ready(function($) {

    ///////////////////////////////////////////////////////
    // VARIABLES
    ///////////////////////////////////////////////////////
    /*
    * path : used for ajax calls
    * names_fields_to_handle : used to handle show or hide optionnals fields
    * recaptcha_fields_to_handle : used to handle show or hide optionnals fields
    */
    var debug = admin.debug;
    var path = admin.ajaxurl;
    var names_fields_to_handle = ["#probance-optin_banner-lbfname", "#probance-optin_banner-lblname", "#probance-optin_banner-cbtwocols", "#probance-optin_banner-lname-error-msg", "#probance-optin_banner-fname-error-msg"];
    var recaptcha_fields_to_handle = ["#probance-optin_banner-recaptchasitekey", "#probance-optin_banner-recaptchaprivatekey"];

    ///////////////////////////////////////////////////////
    // FUNCTIONS
    ///////////////////////////////////////////////////////

    Object.defineProperty(String.prototype, 'capitalize', {
        value: function() {
          return this.charAt(0).toUpperCase() + this.slice(1);
        },
        enumerable: false
    });

    /*
    * Function to show names fileds
    */
    function show_names_fields() {
        names_fields_to_handle.forEach(field => {
            $(field).parents("tr").show();
        });
        
        return ;
    }

    /*
    * Function to show recaptcha fileds
    */
    function show_recaptcha_fields() {
        recaptcha_fields_to_handle.forEach(field => {
            $(field).parents("tr").show();
        });
        
        return ;
    }

    /*
    * Function to hide names fileds
    */
    function hide_names_fields() {
        
        names_fields_to_handle.forEach(field => {
            $(field).parents("tr").hide();
        });

        return;
    }

    /*
    * Function to hide recaptcha fileds
    */
    function hide_recaptcha_fields() {
        recaptcha_fields_to_handle.forEach(field => {
            $(field).parents("tr").hide();
        });
        
        return ;
    }

    /*
    * Function to to toggle required args 
    */
    function recaptcha_fields_required(value) {
        recaptcha_fields_to_handle.forEach(field => {
            $(field).prop('required', value);
        });
        return ;
    }

    ///////////////////////////////////////////////////////
    // INIT THE DISPLAY
    ///////////////////////////////////////////////////////
    /*
    * Init. textarea status (visible or hidden)
    */
   $("textarea.hidden").hide();
   $("textarea.visible").show();


    /*
    * Init names field to visible or hidden (HTML visible by default)
    */
    if(!$("#probance-optin_banner-cbnames").is(":checked")) {
        hide_names_fields();
    }

    /*
    * Init recaptcha fields to visible or hidden and set required to true or false
    */
    if(!$("#probance-optin_banner-cbrecaptcha").is(":checked")) {
        hide_recaptcha_fields();
        recaptcha_fields_required(false);
    } else {
        recaptcha_fields_required(true);
    }

    ///////////////////////////////////////////////////////
    // EVENT LISTENERS
    ///////////////////////////////////////////////////////
    /*
    * Event listener to hide/show name fields
    */
    $("#probance-optin_banner-cbnames").change(function() {

        if($(this).is(":checked")) show_names_fields();
        else hide_names_fields();
        
    });

    /*
    * Event listener to hide/show recaptcha fields
    */
    $("#probance-optin_banner-cbrecaptcha").change(function() {

        if($(this).is(":checked")) {
            show_recaptcha_fields();
            recaptcha_fields_required(true);
        }
        else {
            hide_recaptcha_fields();
            recaptcha_fields_required(false);
        }
        
    });

    /*
    * Event listener to handle the "Properties" button in main admin window
    * It opens the properties pop-up assoiciated to the button
    */
    // Cancel redirection due to the submit action
    $('input[name="btn-open-properties"], input[name="btn-open-translations"]').on('submit', function() {return false;});
    $('input[name="btn-open-properties"], input[name="btn-open-translations"]').click( function(e) {

        e.preventDefault()

        var idInput = e.target.id;


        dataType=e.target.dataset.type;

        var popupTargeted = 'div#' + idInput.replace(`-btn-open-${dataType}`, '') + `-block-${dataType}`;

        if(debug==1) console.log('Click on open button');

        $(popupTargeted).css('display', 'flex');
        $('body.wp-admin').css('overflow', 'hidden');
        
        return false;
        
    });

    /*
    * Event listener to handle the "Close" button in the properties pop-up
    */
   // Cancel redirection due to the submit action
    $('input[name="close-block-properties"], input[name="close-block-translations"]').on('submit', function() {return false;});
    $('input[name="close-block-properties"], input[name="close-block-translations"]').click( function(e) {

        if(debug==1) console.log('Click on close button');

        // Change css properties
        $('div[data-type="block-popup"]').css('display', 'none');
        $('body.wp-admin').css('overflow', 'visible');

        return false;        
    });

    /*
    * Event listener to handle the "Save" button in the properties pop-up
    */
    // Cancel redirection due to the submit action
    $('input[name="save-block-properties"], input[name="save-block-translations"]').on('submit', function(e) {return false;});
    $('input[name="save-block-properties"], input[name="save-block-translations"]').click( function(e) {

        if(debug==1) console.log('Click on save button');

        // Variables
        var  idInput = e.target.id;
        var dataType = e.target.dataset.type;
        var commonId = idInput.replace(`-save-block-${dataType}`, '');
        var valuesToSave = new Array();
        var optionsToSave = new Array();

        if (debug==1)
        {
            console.log(idInput);
            console.log(dataType);
            console.log(commonId);
        }

        // Stock each options and textarea values into arrays
        $(`div[name="block-${dataType}"] textarea[id^=${commonId}]`).each( function () {
            
            valuesToSave.push($(this).val());
            optionsToSave.push($(this).attr('id'));

            console.log($(this).val());
            console.log($(this).attr('id'));
        })
        
        if(debug==1){ 
            console.log('URL for AJAX : ' + path);
            console.log('Values to save : ' + valuesToSave);
            console.log('Options to save : ' + optionsToSave);
        }

        /*
        * POST REQUEST -> admin_load_properties or admin_load_translations. 
        * SERVER SIDE handle by the probance_submit_update_properties or probance_submit_update_translations function into probance-optin.php
        */
        $.ajax({
            url: path,
            type: "POST",
            data: {
                    action: `admin_load_option`,
                    'values': valuesToSave,
                    'options' : optionsToSave
            }
          }).done(function() {
            if(debug==1) console.log("POST REQUEST : success"); 

          }).fail(function() {
            console.log("POST REQUEST : failed"); 
          });
          
        return false;
    });

    /*
    * Event listener to toggle textareas on click to option buttons
    */
    // Cancel redirection due to the submit action
    $("a[id*=-btn-toggle-properties-], a[id*=-btn-toggle-translations-]").on('submit', function() {return false;});
    $("a[id*=-btn-toggle-properties-], a[id*=-btn-toggle-translations-]").click( function (e) {
        e.preventDefault();

        // Variables
        var  idA = e.target.id;
        var dataType= e.target.dataset.type;    
        var idElements = idA.substr( 0 ,idA.search(`-btn-toggle-${dataType}`));
        var id = idA.replace(`btn-toggle-${dataType}-`, '');
        // var newLabel = `${e.target.text} > ${dataType.capitalize()}`;

        if(debug==1){ 
            console.log('button ID : ' + idA);
            console.log('common ID : ' + idElements);
            console.log('textarea ID : ' + id);
            console.log(e);
        }

        // $(`div#${idElements}-block-${dataType} label.${dataType}-panel`).text(newLabel);
        // Handle changing class on the link (option button)
        $(`div[name="block-${dataType}"] a[id^=${idElements}]`).removeClass("button-primary");
        $('#' + idA).addClass("button-primary");

        // Handle changing hide/show of the textarea
        $(`div[name="block-${dataType}"] textarea[id^=${idElements}], div[name="block-${dataType}"] div[id^=${idElements}]`).hide();
        $(`div[name="block-${dataType}"] textarea[id^=${idElements}], div[name="block-${dataType}"] div[id^=${idElements}]`).addClass("hidden");
        $(`div[name="block-${dataType}"] textarea[id^=${idElements}], div[name="block-${dataType}"] div[id^=${idElements}]`).removeClass("visible");

        $('#' + id).show();
        $('#' + id).addClass("visible");
        $('#' + id).removeClass("hidden");

        
        return false;
        
    });

    $("input.add_lang_btn").click(function(e){

        e.preventDefault();

        var parentID = e.target.parentNode.id;
        var select = $(`div#${parentID} select`)[0];

        if (debug==1){
            console.log(`Parent ID : ${parentID}`);
            console.log(`Select lang : ` + select.value);
        } 

        if (select.value == '0')
        {
            if (debug==1){
                console.log(`No language selected`);
            } 
        }
        else
        {
            language = select.value;
            
            /*
            * POST REQUEST -> admin_load_properties or admin_load_translations. 
            * SERVER SIDE handle by the probance_submit_update_properties or probance_submit_update_translations function into probance-optin.php
            */
            $.ajax({
                url: path,
                type: "POST",
                data: {
                        action: `admin_add_language`,
                        'value': language,
                        'option' : 'probance-optin-languages'
                }
            }).done(function() {
                if(debug==1) console.log("POST REQUEST : success"); 

                // refresh the page
                location.reload();

            }).fail(function() {
                if(debug==1) console.log("POST REQUEST : failed"); 
            });
            }
    });
});