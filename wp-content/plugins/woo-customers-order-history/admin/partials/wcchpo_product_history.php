<?php
	# Checking user ID or Email 
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
	if (!count($orders)) {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# Refund Code is starts
	# Refunded Items Holder array is rf
	$rf = array();
	foreach ( $orders as $order_id => $order ) {
		# getting the refunded item 
		$order_refunds = $order->get_refunds();
		# if not empty 
		if ( ! empty( $order_refunds ) ){
			# Every order refund 
			foreach( $order_refunds as $refund ) {
				# if refunded it is not empty 
				if (  ! empty(  $refund->get_items() ) ) {
					# Looping the refunded items 
					foreach( $refund->get_items() as $item_id => $item ) {
						# if There are Multiple same item, Add if not define new Value;
						if ( isset( $rf[ $order->ID ] [ $item['product_id'] ] ) ){
							$rf[ $order->ID ][ $item['product_id'] ] += $item['quantity'];
						} else {
							$rf[ $order->ID ][ $item['product_id'] ] = $item['quantity'];
						}
					}
				}
			}
		}
	}

	# Getting Order Status 
	$order_statuses = wc_get_order_statuses() ;
	$current_statuses_orders = array() ;
	# Populating empty array;
	foreach ($orders as $order_id => $order) {
		$current_statuses_orders[$order->get_status()][] = $order_id ;
	}
	# if Not Paid Version 

	if ( ! freemius_wcoh()->can_use_premium_code() ) {
		$current_statuses_orders['cancelled'] 	= array();
		$current_statuses_orders['completed'] 	= array();
		$current_statuses_orders['on-hold'] 	= array();
	}

	# Free And Paid Version Blocker ENDS
?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h1>
		<?php esc_attr_e( 'User Orders Product', 'wcchpo' ); ?>
		<?php
			if ( isset($_GET['userID']) &&  $_GET['userID'] ) {
				echo "<a href='". esc_url( add_query_arg( array('action'=>'order', 'userID'=> $_GET['userID']) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."'class='dashicons dashicons-external tips' class='page-title-action' style=' color: red;' title='This user orders history'>  </a>"; 
			}else{
				echo "<a href='". esc_url( add_query_arg( array('action'=>'order', 'userEmail'=> $_GET['userEmail'] ) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' class='dashicons dashicons-external tips' class='page-title-action' style=' color: red;' title='This user orders history'>  </a>";  
			}
		?>
	</h1>

	<?php
		# if Not Paid Version 
		if ( ! freemius_wcoh()->can_use_premium_code()  ) {
			echo "<div class='notice notice-info'><p><b>Note:</b> Canceled, Completed and On-hold are for Paid Version. <a href='" . freemius_wcoh()->get_upgrade_url() . "'> Please buy the  full version, only $39. </a></p></div>";
		}
		# Free And Paid Version Blocker ENDS
	?>

	<div id="poststuff">
		<!-- Table Starts From Here  -->
		<?php
			foreach ($current_statuses_orders as $order_status_arr_name => $order_status_arr ) {

				echo "<p><b style='padding:3px; text-transform: capitalize;'>".$order_status_arr_name .":</b> </p>";
				?>
					<table class="widefat">
						<thead>
							<tr>
								<th><b>#</b></th>
								<th class="row-title"><b><?php esc_attr_e(  'Order ID', 'WpAdminStyle' );?>  </b></th>
								<th><b><?php esc_attr_e( 'Product ID', 		'WpAdminStyle' );?>				</b></th>
								<th><b><?php esc_attr_e( 'Product Name', 	'WpAdminStyle' );?>				</b></th>
								<th><b><?php esc_attr_e( 'Qty', 			'WpAdminStyle' ); ?>			</b></th>
								<th><b><?php esc_attr_e( 'Qty * Price', 	'WpAdminStyle' );?>				</b></th>
							</tr>
						</thead>

						<tbody>
							<?php
								$i 	=	0;
								$an = 	0;
								foreach ($order_status_arr as  $order_id) {
									$items = $orders[$order_id]->get_items();
									foreach ($items as $item) {
										echo (++$i % 2) ? "<tr class='alternate'>" : "<tr>";
											echo "<td>". $an ."</td>";
											echo "<td><a href='". esc_url( add_query_arg( array('post'=>$orders[$order_id]->get_id() ,'action'=>'edit') , site_url('/wp-admin/post.php') ) )  ."' >". $orders[$order_id]->get_id() ." </a></td>";
											$url = get_the_permalink( $item['product_id'] );
											echo "<td> <a href='".$url."'> ". $item['product_id']  ."</a></td>";
											echo "<td> <a href='".$url."'> ". $item['name'] ."</a></td>";
											echo "<td>";
											echo $item['quantity'];
											if ( isset( $rf[ $orders[$order_id]->get_id() ] , $rf[ $orders[$order_id]->get_id() ][$item['product_id']] )  ){
												echo "  <code> <span style='color: red;' >" . abs( $rf[ $orders[$order_id]->get_id() ][$item['product_id']] ) . "</span> Refunded </code> ";
											}
											echo "</td>";
											echo "<td>". $item['total'] ."</td>";
										echo "</tr>";
										$an ++;
									}
								}
							?>
						</tbody>
					</table>
					<br>
				<?php
			}
		?>
		<!-- Table Ends  Here  -->
		<br class="clear">
	</div>
	<!-- #poststuff -->
</div> <!-- .wrap	 -->	


