<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( 'Order Status Colours', 'wcchpo' );?></h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">
			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
						<!-- Toggle -->

						<h2 class="hndle"><span><?php esc_attr_e( 'Colour codes for the various Order statuses', 'wcchpo' ); ?></span>
						</h2>

						<div class="inside">
							<!--  -->
							<form method="post" action="options.php">
								<?php settings_fields( 'wcchpo-settings-group' ); ?>
								<?php do_settings_sections( 'wcchpo-settings-group' ); ?>
								<table class="form-table">

									<tr valign="top">
										<td scope="row">
											<label for="tablecell">
												<?php esc_attr_e(
													'Colour of Total number circle :', 'wcchpo'
												); ?>	
											</label>
										</td>

										<td> <input type="color" name="wcchpo_colour_total" value="<?php echo empty(get_option('wcchpo_colour_total'))? "#cccccc" : get_option('wcchpo_colour_total') ;?>" id='wcchpo_colour_total'  class="all-options" /> </td>
									</tr>

									<?php
										$statuses_orders 	= array();
										$order_statuses 	= wc_get_order_statuses();
										
										// Removing wc- from every item
										foreach ($order_statuses as $key => $value) {
											$statuses_orders[ substr($key, 3) ] = 0;
										}

										$i = 0;
										// Looping Settings Fields
										foreach ($statuses_orders as $key => $value) {

											if( $key == "pending" ) {
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>" >
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#AFFD71" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} elseif ( $key ==  "processing" ){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#c6e1c6" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/> 
														</td>
													</tr>
													<?php

											} elseif ( $key ==  "on-hold"){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#f8dda7" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} elseif ( $key ==  "completed"){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#61B329" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} elseif ( $key ==  "refunded"){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#092366" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} elseif ( $key ==  "cancelled"){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#FF2400" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} elseif ( $key ==  "failed"){
													?>
													<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
														<td scope="row">
															<label for="tablecell">
																<?php esc_attr_e(
																	'Colour of '.$key, 'wcchpo'
																); ?> 
															</label>
														</td>
														<td> 
															<input 
																type="color" 
																name="<?php echo "wcchpo_colour_".$key; ?>" 
																value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#CD0000" : get_option("wcchpo_colour_".$key) ;?>" 
																id="<?php echo "wcchpo_colour_".$key; ?>" 
																class="all-options" 
															/>  
														</td>
													</tr>
													<?php
											} else {
												?>
												<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
													<td scope="row">
														<label for="tablecell">
															<?php esc_attr_e(
																'Colour of '.$key, 'wcchpo'
															); ?> 
														</label>
													</td>
													<td> 
														<input 
															type="color" 
															name="<?php echo "wcchpo_colour_".$key; ?>" 
															value="<?php echo empty(get_option("wcchpo_colour_".$key))? "#17202A" : get_option("wcchpo_colour_".$key) ;?>" 
															id="<?php echo "wcchpo_colour_".$key; ?>" 
															class="all-options" 
														/>  
													</td>
												</tr>
												<?php
											}	
										}
									?>

									<tr valign="top" class="<?php echo (++$i % 2) ? "alternate" : ""; ?>">
										<td scope="row">
											<label for="tablecell">
												
											</label>
										</td>
										<td>  
											<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
											<input class="button-secondary" onclick="change_default_value()" type="submit" name="Example" value="Reset default" />
										</td>
									</tr> 

								</table>
								<!--  -->
							</form>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables .ui-sortable -->
			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<!-- .meta-box-sortables starts -->
				<div class="meta-box-sortables">

					<div class="postbox">

						<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
						<!-- Toggle -->

						<h2 class="hndle"><span><?php esc_attr_e(
									'Cache Settings', 'WpAdminStyle' 
								); ?></span></h2>

						<div class="inside">
							<?php
								if ( get_option( "wcchpo_orderCacheStatus" ) ){
									echo"<p> Use cache in order page :  <input type='checkbox' id='orderCacheStatus' name='orderCacheStatus' checked > </p>";
								} else {
									echo"<p> Use cache in order page :  <input type='checkbox' id='orderCacheStatus' name='orderCacheStatus'> </p>";
								}
							?>

							<?php
								if( get_option( "wcchpo_displayUserNote" ) ){
									echo"<p> Display user note on order page :  <input type='checkbox' id='displayUserNote' name='displayUserNote' checked > </p>";
								}else{
									echo"<p> Display user note on order page :  <input type='checkbox' id='displayUserNote' name='displayUserNote'> </p>";
								}
							?>
							<p style='color:#e27730;' ><i> If you use cache & order number circle is not Correct. Pleas Reset the cache. </i> </p>
							<p><a class="button-secondary" id="deleteOrderCache" href="#" title="<?php esc_attr_e( 'reset Cache ' ); ?>"> <span style="padding-top: 3px;" class="dashicons dashicons-trash"></span>  Reset Cache  </a> </p>
							
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

				<!-- .meta-box-sortables starts -->
				<div class="meta-box-sortables">

					<div class="postbox">

						<!-- <div class="handlediv" title="Click to toggle"><br></div> -->
						<!-- Toggle -->

						<h2 class="hndle"><span><?php esc_attr_e(
									'Note from the Developer', 'WpAdminStyle' 
								); ?></span></h2>

						<div class="inside">
							<p>
                                <i>
                                    This Plugin has <b> 26 </b> files and  <b>3,130‬</b> lines of code, I work hard and try to make the user happy, 
									but as you know everything is not my hand. 
									Development, Testing, and Debugging takes a lot of time & patience. I hope you will appreciate my effort. 
									<br>
									<br>
									<i>Thank you & best regards </i>
								</i>
							</p>
						</div>
						<!-- .inside -->
					</div>
					<!-- .postbox -->
				</div>
				<!-- .meta-box-sortables -->
			</div>
			<!-- #postbox-container-1 .postbox-container -->
		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->

<script>
	function change_default_value() {
		document.getElementById("wcchpo_colour_total").value 		= "#cccccc";
		document.getElementById("wcchpo_colour_pending").value 		= "#AFFD71";
		document.getElementById("wcchpo_colour_processing").value 	= "#c6e1c6";
		document.getElementById("wcchpo_colour_on-hold").value 		= '#f8dda7';
		document.getElementById("wcchpo_colour_completed").value 	= "#61B329";
	  	document.getElementById("wcchpo_colour_cancelled").value 	= '#FF2400';
	  	document.getElementById("wcchpo_colour_refunded").value 	= '#092366';
	  	document.getElementById("wcchpo_colour_failed").value 		= '#CD0000';
	}
</script>

<!-- Help reference  -->
<!-- class-woo-customers-order-history-admin >> wcchpo_admin_footer [function] && wcchpo_ajax  -->
<!-- wcchpo_admin_footer >> function is for Javascript   -->
<!-- wcchpo_ajax >> function is for Processing Data   -->
