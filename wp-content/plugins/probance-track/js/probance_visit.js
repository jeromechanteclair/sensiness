
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
	
	//ProductID
	var pid=probance_visit_vars.pid; //will be '' if not product page
	var my_id = probance_visit_vars.email; //will be '' if customer is not logged-in
	var token = probance_visit_vars.token;
	var infra = probance_visit_vars.infra; 
	var debug = probance_visit_vars.debug; 
	var prefix = probance_visit_vars.prefix;
	var skuopt = probance_visit_vars.sku;
	var psku = probance_visit_vars.psku;
	
	if(debug == 1){
		console.log("productID : "+pid);
		console.log("productSKU : "+psku);
		console.log("email : "+my_id);
		console.log("token : "+token);
		console.log("infra : "+infra);
		console.log("prefix : "+prefix);
		console.log("SKU OPT : "+skuopt);
	}

	if(skuopt == 1 && psku != '' && psku != null && psku != undefined)
		pid=psku;

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

	if(pid != '')
		pid=prefix+pid;

	if(debug == 1)
		console.log("track("+url+",[['product_id',"+pid+"],['action','visit']])");

	PROBANCE_tracker.track(url,[["product_id",pid],["action","visit"]]);

});

