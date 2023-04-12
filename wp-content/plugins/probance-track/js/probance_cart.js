
//TO DO :Please fill this variables with the right value ============

/*
 * For these fields, please use your customer identifiant. It can be
 * an email, a customer_id (sent by profile flow) as it can be the 
 * crypted crc32 version of one of them
 *
 * Please put the value in 'my_id' and the choosen type in 'typeID'
 * /!\ If you prefer to use only cookie's identification please leave '' /!\
 *
 * /!\ Don't forget to change "TOKEN_VISIT" with the project token /!\
 */
 

var typeID= 'email'; 
var url=document.location.href;

jQuery(document).ready(function($) {	
	var my_id = probance_cart_vars.email; //will be '' if customer is not logged-in
	var token = probance_cart_vars.token;
	var infra = probance_cart_vars.infra;
	var debug = probance_cart_vars.debug;
	var prefix = probance_cart_vars.prefix;
	var pid = probance_cart_vars.pid;
	var ptype = probance_cart_vars.ptype;
	var psku = probance_cart_vars.psku;
	var skuopt = probance_cart_vars.sku;
	var path = probance_cart_vars.ajaxurl;

		
	$('.single_add_to_cart_button').click(function(){

		if(debug == 1){
			console.log("productID : "+pid);
			console.log("product SKU : "+psku);
			console.log("email : "+my_id);
			console.log("token : "+token);
			console.log("infra : "+infra);
			console.log("prefix : "+prefix);
			console.log("SKU OPT : "+skuopt);
		}

		//si produit variable, on chope l'id variation pour id article
		if(ptype == 'variable'){
			var article_id=document.querySelector('input[name=variation_id]').value;

			if(debug == 1)
				console.log("[Produit variable] article ID : "+article_id);
			
			if(article_id != 0 && article_id != null && article_id != 'undefined' && skuopt == 1)
			{
				//s'il n'est pas nul et que l'option SKU est active, on va chercher le SKU via la fonction php accessible en ajax
				var data = {
					'action': 'p_getsku',
					'pid': article_id
				};
				
				$.ajax({
					async: false, 
					type: "POST",   
					url: path,  
					data: data,   
					success: function( asku ) {
						if(debug == 1)
							console.log("article SKU : "+asku);
						if( asku != '' && asku != null && asku != undefined)
							article_id=asku;
					},  
				});
			}
		}

		if(skuopt == 1 && psku != '' && psku != null && psku != undefined)
			pid=psku;

		if(article_id == 0 || article_id == null || article_id == 'undefined')
			article_id=pid;

			
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



