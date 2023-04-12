<?php
	# Gatting User ID
	if ( !isset( $_GET['userID'] )  && !isset( $_GET['userEmail'] ) ) {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# Getting User Orders 
	if ( isset( $_GET['userID'] )  &&  is_numeric($_GET['userID']) ){
		$orders = wc_get_orders( array(
			'limit'    		=> -1,
			'return' 		=> 'objects',
			'orderby' 		=> 'date',
			'customer_id'	=> $_GET['userID']
		));
	} elseif ( isset( $_GET['userEmail'] )  &&  filter_var( $_GET['userEmail'], FILTER_VALIDATE_EMAIL) ) {
		$orders = wc_get_orders( array(
			'limit'       => -1,
			'return'      => 'objects',
			'orderby'     => 'date',
			'customer'    =>  $_GET['userEmail'],
		));
	} else {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# If There is No Order Than Redirect 
	if (! count($orders)) {
		wp_redirect( site_url('/wp-admin/users.php') );
		exit;
	}

	# Getting Customer Address
	$customer_billing_address 		= 	$orders[0]->get_formatted_billing_address();
	$customer_billing_phone 		=  	$orders[0]->get_billing_phone();
	$customer_billing_email 		=  	$orders[0]->get_billing_email();
	$customer_currency 				=  	$orders[0]->get_currency();

	# Getting Order Status 
	$order_statuses = wc_get_order_statuses() ;
	$current_statuses_orders = array() ;
	# Looping the WooCommerce Order Statuses 
	foreach ( $orders as $order_id => $order ) {
		$current_statuses_orders[$order->get_status()][] = $order_id ;
	}

	# if Not Paid Version || empty The array
    if ( ! freemius_wcoh()->can_use_premium_code() ) {
		$current_statuses_orders['cancelled'] 	= array();
		$current_statuses_orders['completed'] 	= array();
		$current_statuses_orders['on-hold'] 	= array();
	}
?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1>
		<?php esc_attr_e( 'User Order history', 'wcchpo' ); ?>
		<?php
			if ( isset($_GET['userID']) && $_GET['userID'] ) {
				echo "<a href='". esc_url( add_query_arg( array('action'=>'product', 'userID'=> $_GET['userID']) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."'class='dashicons dashicons-external tips' class='page-title-action' style=' color: red;' title='This user orders history'>  </a>"; 
			}

			if( isset($_GET['userEmail']) && $_GET['userEmail'] ){
				echo "<a href='". esc_url( add_query_arg( array('action'=>'product', 'userEmail'=> $_GET['userEmail'] ) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' class='dashicons dashicons-external tips' class='page-title-action' style=' color: red;' title='This user orders history'>  </a>";  
			}
		?>
	</h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">

				<!-- warning message starts -->
				<div class="notice notice-warning"><p><b>Note:</b> If an user was deleted, and another one was created with that same ID, this report becomes invalid.</p></div>
				<!-- warning message ends -->
				<?php
					# if Not Paid Version Show this Warning Message 
					if ( ! freemius_wcoh()->can_use_premium_code() ) {
						echo "<div class='notice notice-info'><p><b>Note:</b> Canceled, Completed and On-hold are for Paid Version. <a href='" . freemius_wcoh()->get_upgrade_url() . "'> Please buy the full version, only $39. </a></p></div>";
					}
					# Free And Paid Version Blocker ENDS
				?>

				<!-- NEW CODE STARTS  -->
				<?php

					foreach ( $current_statuses_orders as $order_status_arr_name => $order_status_arr ) {
						?>
						<div class='meta-box-sortables ui-sortable'>
							<div class='postbox'>
								<!-- <div class='handlediv' title='Click to toggle'><br></div> -->
								<!-- Toggle -->
								<h2 class='hndle'><span><?php esc_attr_e( $order_status_arr_name , 'wcchpo' ); ?></span></h2>

								<div class='inside'>
									<table class='widefat'>
										<thead>
											<tr>
												<th class="row-title"> <b> <?php esc_attr_e( 'Order', 'wcchpo' ); ?> 	</b></th>
												<th> <b> <?php esc_attr_e( 'Date', 'wcchpo' ); ?>    					</b></th>
												<th> <b> <?php esc_attr_e( 'IP Address', 'wcchpo' ); ?>    				</b></th>
												<th> <b> <?php esc_attr_e( 'Insight', 'wcchpo' ); ?>    				</b></th>
												<th> <b> <?php esc_attr_e( 'Payment Method', 'wcchpo' ); ?>    			</b></th>
												<th> <b> <?php esc_attr_e( 'Billing Address', 'wcchpo' );  ?> 			</b></th>
												<th> <b> <?php esc_attr_e( 'Phone', 'wcchpo' ); ?> 						</b></th>
												<th> <b> <?php esc_attr_e( 'Shipping Costs', 'wcchpo' );?>   			</b></th>
												<th> <b> <?php esc_attr_e( 'Refund', 'wcchpo' ); ?>   					</b></th>
												<th> <b> <?php esc_attr_e( 'Total', 'wcchpo' ); ?>   					</b></th>
											</tr>
										</thead>

										<tbody>
											<?php
												$i = 0;
												$total_amount 	= 0;
												$total_shipping = 0;
												foreach ( $order_status_arr as $order_id ) {
													?>
													<tr class="<?php echo ( ++$i % 2 ) ? "alternate" : ""; ?>">
														<!-- Customer Full name -->
														<td class='row-title'>
															<a href="<?php echo  esc_url( add_query_arg( array('post'=>$orders[ $order_id ]->get_id() ,'action'=>'edit') , site_url('/wp-admin/post.php') ) )   ?>">
																<?php esc_attr_e( "# ". $orders[ $order_id ]->get_id() , 'wcchpo'); ?>	
																<?php echo $orders[ $order_id ]->get_formatted_billing_full_name(); ?>	
															</a>
														</td>
														<!-- Date  -->
														<td><?php esc_attr_e( date("D,M-j,y -g:ia", strtotime( $orders[ $order_id ]->get_date_created() )), 'wcchpo') ?></td>
														<!-- IP address -->
														<td> <?php echo $orders[ $order_id ]->get_customer_ip_address(); ?>  </td>

														<!-- Insight -->
														<td> <?php echo  $this->wcchpo_useragent( $orders[ $order_id ]->get_customer_user_agent() ); ?></td>

														<!-- Payment Method  -->
														<td> <?php esc_attr_e( $orders[ $order_id ]->get_payment_method_title() , 'wcchpo' ) ?> </td>
														
														<!-- Billing Details -->
														<td>  
															<!-- billing details  -->
															<span title=" Shipping Address : <?php 
																echo  esc_html( $orders[ $order_id ]->get_shipping_city() . " - " . $orders[$order_id]->get_shipping_postcode() .", " );
																echo  esc_html( $orders[ $order_id ]->get_shipping_country() );
															?>"> 

															<?php
																echo  esc_html( $orders[ $order_id ]->get_billing_city() . " - " . $orders[$order_id]->get_billing_postcode() .", " );
																echo  esc_html( $orders[ $order_id ]->get_billing_country() );
															?>
															</span>
														</td>
														<!-- Billing Phone Number  -->
														<td> <?php echo $orders[ $order_id ]->get_billing_phone()  ?></td>
														<!-- Shipping total  -->
														<td> 
															<?php echo   wc_price( $orders[ $order_id ]->get_total_shipping() ); ?> 
															<?php $total_shipping += $orders[ $order_id ]->get_total_shipping(); ?> 
														</td>
														<!-- Refund  -->
														<td style='color:red;' > 
															<?php 
																if ( ! empty( $orders[ $order_id ]->get_total_refunded() ) ){
																	echo wc_price( $orders[ $order_id ]->get_total_refunded() );
																}
															?> 
														</td>
														<!-- Order total -->
														<td> 
															<?php  echo wc_price( $orders[ $order_id ]->get_total()); ?> 
															<?php $total_amount += $orders[ $order_id ]->get_total();  ?> 
														</td>
													</tr>
													<?php
												}

												if ( $i > 1 ) {
													echo ( ++$i % 2 ) ? "<tr class='alternate'>" : "<tr>";
													echo "<td></td> <td></td> <td></td> <td></td>  <td></td> <td></td> <td></td> 
													<td><b> ". wc_price( $total_shipping ) ."</b></td> 
													<td></td>
													<td><b> ". wc_price( $total_amount ) ."</b></td>  ";
													echo "</tr>";
												}
											?>
										</tbody>	
									</table>
								</div>
								<!-- .inside -->
							</div>
							<!-- .postbox -->
						</div>
						<?php
					}
				?>			
				<!-- NEW CODE ENDS -->
			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<!-- @@@@@@@@@@  Actions  @@@@@@@@@@@ -->
				<div class="meta-box-sortables">
					<div class="postbox">
						<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
						<!-- Toggle -->
						<h2 class="hndle"><span><?php esc_attr_e(' Actions ', 'wcchpo'); ?></span></h2>
						<div class="inside">
							<p> 
								<?php
									if ( isset($_GET['userID']) &&  $_GET['userID'] ) {
										echo "<a href='". esc_url( add_query_arg( array('action'=>'download', 'userID'=> $_GET['userID'] ) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' class='button-primary' style='cursor: alias;' target='_blank'  > Download order history  </a>";
									}

									if( isset( $_GET['userEmail'] ) && $_GET['userEmail'] ){
										echo "<a href='". esc_url( add_query_arg( array('action'=>'download', 'userEmail'=> $_GET['userEmail'] ) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' class='button-primary' style='cursor: alias;' target='_blank'  > Download order history  </a>";
									}
								?>
							</p>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables -->

				<!-- @@@@@@@@@@  Notes  @@@@@@@@@@@ -->
				<!-- Conditional statement  -->
				<!-- Show this Portion if User is not a Guest || if User a Gust Hide  -->
				<?php if ( isset( $_GET['userID'] ) ): ?>
					<!-- New Right Side Box -->
					<div class="meta-box-sortables">
						<div class="postbox">
							<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
							<!-- Toggle -->
							<h2 class="hndle"><span><?php esc_attr_e(' Note ', 'wcchpo'); ?></span></h2>
							<div class="inside">
								<form action="" method="POST">
								<textarea name="wcchpo_user_note" placeholder="Note for this user.. " rows="4" style="width: 100%;" > 
									<?php 
										if ( isset( $_GET['userID'] ) && ! empty( $_GET['userID'] ) ) {
											$wcchpo_user_note = get_user_meta( $_GET['userID'],  'wcchpo_user_note', true) ;
											if ( ! empty( $wcchpo_user_note ) ) {
												echo $wcchpo_user_note;
											}
										}
									?>
								</textarea>
								<input type="submit" class="button-primary" value="save" name="save">
								</form>
							</div>
							<!-- .inside -->
						</div>
						<!-- .postbox -->
					</div>
					<!-- .meta-box-sortables -->
				<?php endif; ?>

				<!-- @@@@@@@@@@  Address @@@@@@@@@@@ -->
				<!-- New Right Side Box -->
				<div class="meta-box-sortables">

					<div class="postbox">
						<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
						<!-- Toggle -->
						<h2 class="hndle"> <span> <?php esc_attr_e(' User Address ', 'wcchpo'); ?> </span> </h2>
						<div class="inside">
							<p>
								<?php echo $customer_billing_address ; ?>
								<br>
								Phone : <?php echo $customer_billing_phone ; ?>
								<br>
								Email : <?php echo $customer_billing_email ; ?>                	
							</p>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
					<?php
						if ( isset( $_GET['userID'] ) && ! empty( $_GET['userID'] ) ) {
							echo "<a href='". esc_url( add_query_arg( array('action'=>'product', 'userID'=> $_GET['userID']) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' style='cursor: alias;' > Product history  </a>";
						}

						if ( isset($_GET['userEmail']) && ! empty( $_GET['userEmail'] ) ) {
							echo "<a href='". esc_url( add_query_arg( array('action'=>'product', 'userEmail'=> $_GET['userEmail'] ) , site_url('/wp-admin/admin.php?page=wcchpo-history') ) ) ."' style='cursor: alias;'  >  Product history  </a>";
						}
					?>
					</div>
				<!-- .meta-box-sortables -->
			</div>
			<!-- #postbox-container-1 .postbox-container -->
		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->
</div> <!-- .wrap