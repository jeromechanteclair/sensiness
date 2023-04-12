<?php 

	if ( !isset( $_GET['userID'] )  && !isset( $_GET['userEmail'] ) ) {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# Getting User Orders 
	if( isset( $_GET['userID'] )  &&  is_numeric($_GET['userID']) ){
		$orders = wc_get_orders(array(
		'limit'    		=> -1,
		'return' 		=> 'objects',
		'orderby' 		=> 'date',
		'customer_id'	=> $_GET['userID']
		));
	}  elseif ( isset( $_GET['userEmail'] )  &&  filter_var( $_GET['userEmail'], FILTER_VALIDATE_EMAIL) ) {
		$orders = wc_get_orders( array(
			'limit'       => -1,
			'return'      => 'objects',
			'orderby'     => 'date',
			'customer'    =>  $_GET['userEmail'],
		));
	}else{
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# If There is No Order Than Redirect 
	if ( ! count($orders) ) {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# Get user Name 
	$userName = '_';
	if ( isset(  $_GET['userID'] ) ) {
		# getting User Details 
		$userObj = get_user_by('id', $_GET['userID'] );
		if( is_object( $userObj ) && isset( $userObj->display_name ) ){
			$userName = $userObj->display_name; 
		}
	} else {
		# this is a Guest so No User name
		$userName = $_GET['userEmail'];
	} 

	# CSV Starts From Here 
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'. date(get_option('date_format')) .'_'.$userName.'_orders.csv"');

	ob_end_clean();
	$file = fopen('php://output', 'w');
	# CSV file Heading row
	fputcsv($file, array("ID", "Status", "Date", "IP Address", "Order Products", "Payment Method", "Billing", "Phone", "Shipping totals", "Total")); 

	$i = 0 ;
	$ShippingTotals = 0;
	$grandTotal		= 0;
	$numberOfOrder 	= count( $orders);

	foreach ( $orders as $order => $order_data) {
		$i++ ;
		$items = $order_data->get_items();
		$product_list_text = "";
		# Order item list txt 
		foreach ($items as $item) {
			$product_list_text .= " ".$item['name'] ."\n ";
		}

		# Calculating Shipping total;
		$ShippingTotals += $order_data->get_total_shipping();
		# Calculating Grand total;
		$grandTotal	+= $order_data->get_total();

		fputcsv($file, 
			array(
				$order_data->get_id(), 
				$order_data->get_status(), 
				date("D,M-j,y -g:ia", strtotime( $order_data->get_date_created() )), 
				$order_data->get_customer_ip_address(),
				$product_list_text ,
				$order_data->get_payment_method_title() , 
				$order_data->get_billing_city(). " - " . $order_data->get_billing_postcode() .", ". $order_data->get_billing_country() ,
				$order_data->get_billing_phone(), 
				$order_data->get_total_shipping(), 
				$order_data->get_total()
			) 
		); 

		# if Not Paid Version 
		if ( ! freemius_wcoh()->can_use_premium_code() ) {
			# Free And Paid Version Blocker STARTS
			if ($i >= 2) {
				fputcsv($file, array("Please buy the full version, only $39. "));
				break;
			}
			# Free And Paid Version Blocker ENDS
		}
	}

	# Printing totals on the CSV file End row and only for Paid version;
	if ( $numberOfOrder != 0 && freemius_wcoh()->can_use_premium_code()   ){
		fputcsv($file, 
			array(
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'Total : ' . $ShippingTotals, 
				'Total : ' . $grandTotal
			)
		); 
	}

	fclose( $file );
	exit;

// CSV Ends 

