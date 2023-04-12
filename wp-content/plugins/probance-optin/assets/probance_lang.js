jQuery(document).ready(function($) {

    /**
     * Variables
     */
    var debug = probance_lang.debug;
    var translations = probance_lang.translations;

    /**
     * Execute
     */
    var currLang = getCurrentLanguauge(); 

    if (currLang == '') return;

    var trans = translations[currLang];

    if (trans==undefined || trans == null) return;
    
    if (debug==1) console.log(`Current language : ${currLang}.`);
    if (debug==1) console.log(`Translations : ${Object.values(translations)}.`);
    if (debug==1) console.log(`Current Translations : ${Object.values(trans)}.`);

    // Modify label into newsletters 
    modifyNewsletterLabels();

    // Modify optin label. Using sleep to handle label translation in checkout page.
    sleep(1000).then(() => {
        modifyOptinLabels();
    });

    // sleep time expects milliseconds
    function sleep (time) {
        return new Promise((resolve) => setTimeout(resolve, time));
    }

    /**
     * Funtion to modify optin labels from default to current language
     */
    function modifyOptinLabels()
    {   
        var translations = trans;

        if (debug==1) console.log(`Try to display : ${translations['probance-optin_webel-cblabel']}`);

        if (debug==1) console.log($('div#probance_optin_cb_wrapper span'));

        // Modification du text du span
        $('div#probance_optin_cb_wrapper span').text(translations['probance-optin_webel-cblabel']);
    }

    /**
     * Funtion to modify newsletter labels from default to current language
     */
    function modifyNewsletterLabels()
    {   
        var translations = trans;
        // Titles
        jQuery('.p-nlform-maintitle').text(translations['probance-optin_banner-lbtitle']);
        jQuery('.p-nlform-subtitle').text(translations['probance-optin_banner-lbsubtitle']);

        // Email
        jQuery('label.p-nlform-email-label').text(translations['probance-optin_banner-lbemail']);
        jQuery('input#probance_newsletter_email').attr('placeholder', translations['probance-optin_banner-lbemail']);
        jQuery('span.probance_msg_wrong_email').text(translations['probance-optin_banner-email-error-msg']);
        jQuery('span.probance_msg_empty_email').text(translations['probance-optin_banner-email-empty-msg']);

        // First Name
        jQuery('label.p-nlform-fname-label').text(translations['probance-optin_banner-lbfname']);
        jQuery('input#probance_newsletter_fname').attr('placeholder', translations['probance-optin_banner-lbfname']);
        jQuery('span.probance_msg_wrong_fname').text(translations['probance-optin_banner-fname-error-msg']);

        // Last Name 
        jQuery('label.p-nlform-lname-label').text(translations['probance-optin_banner-lblname']);
        jQuery('input#probance_newsletter_lname').attr('placeholder', translations['probance-optin_banner-lblname']);
        jQuery('span.probance_msg_wrong_lname').text(translations['probance-optin_banner-lname-error-msg']);

        // Button
        btns=jQuery('input#probance_newsletter_submit_btn');
        Object.keys(btns).forEach(function(key) {btns[key].value = translations['probance-optin_banner-lbbtn'];});

        // Conf Message
        jQuery('div.probance_message_submit p.message').text(translations['probance-optin_banner-conf-message']);
        jQuery('div.probance_message_submit_faillure p.message').text(translations['probance-optin_banner-conf-error-message']);
    }
    

    /**
     * Function to retrieve cookie by name
     * @param {string} cname 
     * @returns 
     */
    function getCookie(cname) {

        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');

        for(let i = 0; i <ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }

        return "";
    } 

    /**
     * Function to get the current language.
     * 1 - Using Cookie
     * 2 - Using <html> class WPML
     * 3 - Using <html> lang attribute
     * 4 - Using <body> data attribute
     * 
     */
    function getCurrentLanguauge()
    {
        // Try to retrieve lang using WPML cookie
        if (debug==1) console.log('Trying to get WPML current language using WPML Cookie.');

        var cname = "wp-wpml_current_language";
        var lang = getCookie(cname); 

        if ( lang!="") 
        {
            if (debug==1) console.log(`Language from WPML Cookie : ${lang}`);
            return lang;
        }

        // Try to retrieve lang using <html> class WPML
        if (debug==1) console.log('Trying to get current language using <html> class WPML.');

        var langs = jQuery('body')[0].attributes.class.nodeValue.match('wpml-([a-z]{2})');
        
        if (langs != null)
        {
            lang = langs[1];
            
            if (lang!=undefined && lang != '')
            {
                return lang;
            }
        }
        

        // Try to retrieve lang using <html> lang attribute
        if (debug==1) console.log('Trying to get current language using <html> lang attribute.');

        var lang = jQuery('html')[0].lang.split('-')[0];

        if (lang!=undefined && lang != '')
        {
            return lang;
        }

        // Try to retrieve lang using <html> lang attribute
        if (debug==1) console.log('Trying to get current language using <body> data attribute.');

        var lang = jQuery('body')[0].attributes.data.nodeValue;

        if (lang!=undefined && lang != '')
        {
            return lang;
        }

        return "";
    }


    




});