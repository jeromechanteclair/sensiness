
var typeID= 'email'; 
var url=document.location.href;

jQuery(document).ready(function($) {

    var my_id = probance_ajax_cart_vars.email; //will be '' if customer is not logged-in
	var token = probance_ajax_cart_vars.token;
	var infra = probance_ajax_cart_vars.infra;
    var prefix = probance_ajax_cart_vars.prefix;
    var skuopt = probance_ajax_cart_vars.sku;
	var debug = probance_ajax_cart_vars.debug;
  

    $('body').on('added_to_cart', function(e,fragments,cart_hash,button){
        var pid=article_id=$(button).attr('data-product_id');
        var psku=$(button).attr('data-product_sku');
       
        if(debug == 1){
            console.log("productID/articleID : "+pid);
            console.log("SKU : "+psku);
            console.log("prefixe : "+prefix);
            console.log("email : "+my_id);
            console.log("token : "+token);
            console.log("infra : "+infra);
            console.log("SKU OPT : "+skuopt);
        }

        //si option SKU, on remplace l'id par le SKU. Pas de distinction article/produit sur cette fonctionnalité
        if(skuopt == 1 && psku != '' && psku != null && psku != undefined)
            pid=article_id=psku;
    
        //TOKEN_VISIT has to be replaced by the project token
        var crm = new PROBANCE_CRMTracker(token,"Prob_Track",90,infra + "/webtrax","idprob");
        // To Call Probance tracker 
        var PROBANCE_tracker = new PROBANCE_trackers();
        PROBANCE_tracker.setCRM(crm);
        
        //if there is a cookie on the customer computer, we use its id, else we use the one declared in variables
        if(my_id != null && my_id != undefined && my_id != ''){
            crm.setCustomer(prefix+my_id, typeID);
        }
        PROBANCE_tracker.doNotTrackOnInit();
        PROBANCE_tracker.init();
        
        // Tracking for Visit
        if(pid == null || pid == undefined){
            pid = '';
        }
    
        if(debug == 1)
            console.log('trackEvent('+url+',[["product_id",'+prefix+pid+'],["basket_id", ""],["action","cart"],["article_id",'+prefix+article_id+']])');
    
        PROBANCE_tracker.trackEvent(url,[["product_id", prefix+pid],["basket_id", ''],["action","cart"],["article_id",prefix+article_id]]);

    });

});