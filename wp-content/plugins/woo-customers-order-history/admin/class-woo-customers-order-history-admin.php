<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Customers_Order_History
 * @subpackage Woo_Customers_Order_History/admin
 * @author     javmah <jaedmah@gmail.com>
 */
class Woo_Customers_Order_History_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	*/
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
    private $version;

    /**
	 * The version of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
    private $orderCacheStatus;

    /**
	 * The version of this plugin.
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	*/
    private $displayUserNote;
    
    /**
	 * orderCacheData is a caching array , lets see what happening 
	 *
	 * @since    5.0.0
	 * @access   private
	 * @var      array    $version    The current version of this plugin.
	*/
    private $orderCacheData = array();
    
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    5.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	*/
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name      = $plugin_name;
        $this->version          = $version;
        $this->orderCacheStatus = get_option( "wcchpo_orderCacheStatus" );
        $this->displayUserNote  = get_option( "wcchpo_displayUserNote" );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    5.0.0
	*/
	public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) .'css/woo-customers-order-history-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 * @since    5.0.0
	*/
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) .'js/woo-customers-order-history-admin.js', array( 'jquery' ), $this->version, false );
	}
    
    /**
	 * Add menu & submenu to the Plugin 
	 * @since    5.0.0
	*/
	public function wcchpo_plugin_options_create_menu(){
	    add_menu_page(
	        'customers order history ',
	        'Customers Order History',
	        'manage_woocommerce',
	        'woo-customers-order-history',
	        array( $this, 'wcchpo_Order_history_settings' ),
	        'dashicons-list-view',
	        99
	    );
	    // Change The Name
	    add_submenu_page(
	        'woo-customers-order-history',
	        "Order Status Colours",
	        "Order Status Colours",
	        "publish_posts",
	        "woo-customers-order-history",
	        array( $this, 'wcchpo_Order_history_settings' )
	    );
	    add_submenu_page(
	        'woo-customers-order-history',
	        'Users Order History',
	        'User Order history',
	        'manage_woocommerce',
	        'wcchpo-history',
	        array( $this, 'wcchpo_history_page' )
	    );
	}

    /**
	 * Display notice . Notice will show only fry and mon day & Init the Order page Cache 
     * also Display Notice after 7 day's of installation 
	 * @since    5.1.0
	*/
	public function wcchpo_admin_notice(){
        if ( get_current_screen()->id == "edit-shop_order" ||  get_current_screen()->id == "users" ){
            $this->orderCacheData  = get_option("wcchpo_orderCacheData");
        }

	    # Starts After 7 day's
	    $inst_unix_time = 604800;
	    if ( get_option( "wcchpo_install_date" ) ) {
	        $inst_unix_time += get_option( "wcchpo_install_date" );
        }

        # Notice cancel date ;
        $wcchpo_message_date = (isset( get_user_meta(get_current_user_id(), "wcchpo_message_date" )[0]) && !empty(get_user_meta(get_current_user_id(), "wcchpo_message_date" )[0])) ? get_user_meta(get_current_user_id(), "wcchpo_message_date" )[0] : null ;
        # Display Notice 
	    if ( ! freemius_wcoh()->can_use_premium_code()  ) {
	        if ( time() > $inst_unix_time && $wcchpo_message_date != date("Ymd") ) {
	            # Purchase Notice Will Display After 7 day's Of Install
	            if ( date( "D" ) == "Fri" ) {
	                echo  "<div id='wcchpo_notice'  class='notice notice-success is-dismissible'>" ;
	                echo  "<p>Please, update <a href='" . freemius_wcoh()->get_upgrade_url() . "'> woocommerce customers order history to paid version. </a> </p>" ;
	                echo  "</div>" ;
	            }
	        }
	    }

        # Testing Starts 
        echo"<pre>";


        echo"</pre>";
        # Testing Ends 
        
	}
    
    /**
	 *  Meta Box inside order detail.
	 * @since    5.0.0
	*/
	public function adding_custom_meta_boxes_wcchpo(){
	    add_meta_box(
	        'wcchpo-customers-order-history-metabox',
	        __( 'Previous Orders', 'wcchpo' ),
	        array( $this, 'wcchpo_render_meta_box_render' ),
	        'shop_order',
	        'side',
	        'high'
	    );
	}
    
    /**
	 *  single order meta box render ; Display meta_box inside single order Page.
	 * @since    5.0.0
	 * @param      string    $post_info        Post detail info
	 * @param      string    $meta_box_info    meta box info
	*/
	public function wcchpo_render_meta_box_render( $post_info, $meta_box_info ){
        $order_id       = $post_info->ID;
        $customer_id    = get_post_meta( $order_id, '_customer_user', true );
        $billing_email  = get_post_meta( $order_id, '_billing_email', true );
        
        if ( empty( $customer_id ) && empty( $billing_email ) ){
            echo "<div id='circle_holder'> <span  class='tips' data-tip='there is no user ID or Email address'>\r\n\t\t \t\t\t<code> <b> Guest </b> </code> </span> </div> " ;
            return;
        }
        
        # Getting Order Details
        if ( $customer_id ){
            # getting orders with User ID 
            $orders = wc_get_orders( array(
                'limit'       => -1,
                'return'      => 'objects',
                'orderby'     => 'date',
                'customer_id' =>  $customer_id,
            ));
        } else {
            # if there is no user ID USE Billing Email to get the Orders 
            $orders = wc_get_orders( array(
                'limit'       => -1,
                'return'      => 'objects',
                'orderby'     => 'date',
                'customer'    => $billing_email,
            ));
        }
       
        # Total Number of Order of This User
        $total_number_of_orders     = count( $orders );
        $statuses_orders            = array();
        $this_user_orders_id_status = array();
        $order_statuses             = wc_get_order_statuses();

        # Removing wc- from every item
        foreach ( $order_statuses as $key => $value ) {
            $statuses_orders[substr( $key, 3 )] = 0;
        }

        # Count order by order_statuses of this user
        foreach ( $orders as $value ) {
            if ( isset( $statuses_orders[$value->get_status()] ) ) {
                $statuses_orders[$value->get_status()]++;
            }
            // Inserting Order id and it's Status
            $this_user_orders_id_status[$value->get_id()] = $value->get_status();
        }

        echo  "<div id='circle_holder'>" ;
        # Displaying The Counts
        if ( !empty($total_number_of_orders) &&   $total_number_of_orders != 1 ) {
            $total_colour = ( empty(get_option( 'wcchpo_colour_total' )) ? "#cccccc" : get_option( 'wcchpo_colour_total' ) );
            echo  "<span class='Circle tips' style='background-color:" . $total_colour . "' data-tip='Total number of orders by this user'>" . $total_number_of_orders . "</span>" ;
        }
        
        foreach ( $statuses_orders as $status_key => $status_number ) {
            if ( !empty($status_number) ) {
                echo  "<span class='Circle tips " . $status_key . "' style='background-color:" . $this->colour_func( $status_key, get_option( "wcchpo_colour_" . $status_key ) ) . "' data-tip='" . $status_key . "'>" . $status_number . "</span>" ;
            }
        }
        # user note
        $wcchpo_user_note = get_user_meta( $customer_id, 'wcchpo_user_note', true );
        if ( !empty($wcchpo_user_note) ) {
            echo  "<span class='tips dashicons dashicons-admin-comments' style='padding:2px;' data-tip='" . $wcchpo_user_note . "' > </span>" ;
        }

        # Meta Box History Link
        if ( $total_number_of_orders > 1 ) {
            if ( isset( $customer_id ) && $customer_id ){
                echo  "<a href='" . esc_url( add_query_arg( array(
                'action' => 'order',
                'userID' => $customer_id,
                ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;'  data-tip='This user orders history'>  </a>" ;
            } else {
                if ( isset( $billing_email ) && $billing_email  ){
                    echo  "<a href='" . esc_url( add_query_arg( array(
                    'action' => 'order',
                    'userEmail' => $billing_email,
                    ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;'  data-tip='This user orders history'>  </a>" ;
                }
            }
        }
        echo  " </div>" ;
        ?>
		<style type="text/css">	
			.Circle{
				background: #cccccc;
				border-radius: 0.8em;
				-moz-border-radius: 0.8em;
				-webkit-border-radius: 0.8em;
				color: #ffffff;
				display: inline-block;
				font-weight: bold;
				line-height: 1.6em;
				/*margin-right: 5px;*/
				margin: 2px;
				text-align: center;
				width: 1.6em; 
			}

			#circle_holder{
				text-align : center;
			}
		</style>
		<?php 
    }

    /**
	 * helper function four colour; it will render colour accordingly  options.
	 * @since    5.0.0
	 * @param      string    $value              The name of this plugin.
	 * @param      string    $colour_options     The version of this plugin.
	*/
    public function colour_func( $value = '', $colour_options = '' ){
        $colour_code = "";
        
        if ( $value == 'pending' ) {
            $colour_code = ( empty($colour_options) ? "#AFFD71" : $colour_options );
        } elseif ( $value == 'processing' ) {
            $colour_code = ( empty($colour_options) ? "#c6e1c6" : $colour_options );
        } elseif ( $value == 'on-hold' ) {
            $colour_code = ( empty($colour_options) ? "#f8dda7" : $colour_options );
        } elseif ( $value == 'completed' ) {
            $colour_code = ( empty($colour_options) ? "#61B329" : $colour_options );
        } elseif ( $value == 'cancelled' ) {
            $colour_code = ( empty($colour_options) ? "#FF2400" : $colour_options );
        } elseif ( $value == 'refunded' ) {
            $colour_code = ( empty($colour_options) ? "#092366" : $colour_options );
        } elseif ( $value == 'failed' ) {
            $colour_code = ( empty($colour_options) ? "#CD0000" : $colour_options );
        } else {
            $colour_code = ( empty($colour_options) ? "#000000" : $colour_options );
        }
        
        return $colour_code;
    }
    
    /**
	 * Option Settings .
	 * @since    5.0.0
	*/
    public function register_wcchpo_plugin_settings(){
        # register our settings
        register_setting( 'wcchpo-settings-group', 'wcchpo_colour_total' );
        $statuses_orders    = array();
        $order_statuses     = wc_get_order_statuses();

        # Removing wc- from every item
        foreach ( $order_statuses as $key => $value ) {
            $statuses_orders[] = substr( $key, 3 );
        }
        # 
        foreach ( $statuses_orders as $value ) {
            register_setting( 'wcchpo-settings-group', "wcchpo_colour_" . $value );
        }
    }

    /**
	 * includes Order_history_settings view page 
	 * @since    5.0.0
	*/
    public function wcchpo_Order_history_settings(){
        # Inclued Settings Page.
        require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/wcchpo_settings.php';
    }
    
    /**
	 * router , for menu pages;
	 * @since    5.0.0
	*/
    public function wcchpo_history_page(){
        # Save User Note
        if ( isset( $_POST["wcchpo_user_note"] ) and !empty( $_GET['userID'] ) ) {
            $note = sanitize_text_field( $_POST['wcchpo_user_note'] );
            update_user_meta( $_GET['userID'], 'wcchpo_user_note', $note );
        }
        
        # router
        if ( isset( $_GET['action'] ) and $_GET['action'] == "order" ) {
            require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/wcchpo_order_history.php';
        } elseif ( isset( $_GET['action'] ) and $_GET['action'] == "product" ) {
        	require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/wcchpo_product_history.php';
        } elseif ( isset( $_GET['action'] ) and $_GET['action'] == "download" ) {
        	require_once plugin_dir_path(dirname(__FILE__)).'admin/partials/wcchpo_download_history.php';
        } else {
            wp_redirect( site_url( '/wp-admin/users.php' ) );
        }
    }
    
    /**
	 * This Function will display data to the Column on the order list page 
	 * @since    5.0.0
	 * @param      string    $columns       The name of the column .
	*/
    public function wcchpo_Order_table_Previous_Orders_columns( $columns ){
        $reordered_columns = array();
        # Inserting columns to a specific location
        foreach ( $columns as $key => $column ) {
            $reordered_columns[$key] = $column;
            if ( $key == 'order_status' ) {
                // Inserting after "Status" column
                $reordered_columns['wcchpo_previous_orders'] = __( 'Previous Orders', 'wcchpo' );
            }
        }
        return $reordered_columns;
    }
    
    /**
	 * this will process the row data ;
	 * @since    5.0.0
	 * @param      string    $column       Column ID .
	 * @param      string    $post_id      post ID.
	*/
    public function wcchpo_Order_table_Previous_Orders_columns_content_row( $column, $post_id ){
       
        if ( $column == 'wcchpo_previous_orders' ) {
           # getting post meta 
            $post_meta        = get_post_meta( $post_id );
            $customer_id      = ( isset( $post_meta["_customer_user"][0] ) && !empty( $post_meta["_customer_user"][0] ) ) ?  $post_meta["_customer_user"][0] : null  ;
            $billing_email    = ( isset( $post_meta["_billing_email"][0] ) && !empty( $post_meta["_billing_email"][0] ) ) ?  $post_meta["_billing_email"][0] : null  ;
          
            #  return if ID and email address are empty;
            if ( empty( $customer_id ) && empty( $billing_email ) ){
                return;
            }
            #  If Caching is Enabled this Portion of Code Will Work ;
            if ( $this->orderCacheStatus ){

                # outPut will Hold the txt; its a Holder 
                $outPut  = "";
                # if Customer ID or Billing email set on cache 
                if ( ( isset($this->orderCacheData[ $customer_id ]["total"]) && in_array( $post_id, $this->orderCacheData[ $customer_id ]["total"]) )  ||  ( isset($this->orderCacheData[ $billing_email ]["total"]) && in_array($post_id, $this->orderCacheData[ $billing_email ]["total"]) ) ) {

                    # Check & Balance for customer ID & ...
                    if ( isset($this->orderCacheData[ $customer_id ]) && is_array( $this->orderCacheData[$customer_id] ) ){
                        foreach( $this->orderCacheData[ $customer_id ] as $key => $value ) {
                            if ( count( $this->orderCacheData[ $customer_id]["total"] ) == 1   ){
                                if ( $key != "total" ){
                                    $outPut .= "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value ) . "</span>" ; 
                                }
                            } else {
                                $outPut .= "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value ) . "</span>" ;
                            }
                        }
                    }
                    # for email address 
                    if ( isset( $this->orderCacheData[ $billing_email ] ) && is_array( $this->orderCacheData[ $billing_email ] ) ){
                        foreach( $this->orderCacheData[ $billing_email ] as $key => $value ) {
                            if ( count( $this->orderCacheData[ $billing_email ]["total"] ) == 1   ){
                                if ( $key != "total" ){
                                    $outPut .= "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value  ) . "</span>" ; 
                                }
                            } else {
                                $outPut .= "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value  ) . "</span>" ;
                            }
                        }
                    }
                } else {
                    # if customer is a User not a Guest 
                    if ( $customer_id ) {
                        # Regular User with a ID
                        $orders = wc_get_orders( array(
                            'limit'       => -1,
                            'return'      => 'objects',
                            'orderby'     => 'date',
                            'customer_id' => $customer_id,
                        ));

                        foreach ( $orders as $order ) {
                            # if user is set but Order id not inside total;
                            if( isset( $this->orderCacheData[$customer_id]["total"] ) &&  ! in_array ( $order->get_id() ,  $this->orderCacheData[$customer_id]["total"] )  ){
                                $this->orderCacheData[$customer_id]["total"][] = $order->get_id() ;
                                $this->orderCacheData[$customer_id] [$order->get_status()][] = $order->get_id() ;
                            }
                            # If user is not set;
                            if( !isset( $this->orderCacheData[$customer_id]) ){
                                $this->orderCacheData[$customer_id]["total"][] = $order->get_id() ;
                                $this->orderCacheData[$customer_id] [$order->get_status()][] = $order->get_id() ;
                            }
                        }

                        foreach ( $this->orderCacheData[$customer_id] as $key => $value) {
                            if ( count( $this->orderCacheData[ $customer_id ]["total"] ) == 1   ){
                                if ( $key != "total" ){
                                    $outPut .= "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value ) . "</span>" ; 
                                }
                            } else {
                                $outPut .=  "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $value ) . "</span>" ;
                            }
                        }

                    } else {
                        #  If Guest and no user ID , have the user email ;
                        $orders = wc_get_orders( array(
                            'limit'       => -1,
                            'return'      => 'objects',
                            'orderby'     => 'date',
                            'customer'    => $billing_email,
                        ) );

                        # Looping the Orders 
                        foreach ( $orders as $order ) {
                            # check & Balance bro for ...
                            if( isset( $this->orderCacheData[ $billing_email]["total"] ) &&  ! in_array ( $order->get_id() ,  $this->orderCacheData[ $billing_email ]["total"] )  ){
                                $this->orderCacheData[ $billing_email ]["total"][] = $order->get_id() ;
                                $this->orderCacheData[ $billing_email ] [$order->get_status()][] = $order->get_id() ;
                            }
                            # check & Balance bro for ...
                            if( !isset( $this->orderCacheData[ $billing_email ] ) ){
                                $this->orderCacheData[ $billing_email ]["total"][] = $order->get_id() ;
                                $this->orderCacheData[ $billing_email ] [$order->get_status()][] = $order->get_id() ;
                            }
                        }

                        # Looping for 
                        foreach ( $this->orderCacheData[ $billing_email ] as $key => $value) {
                            # if total is not zero 
                            if ( count( $this->orderCacheData[ $billing_email ]["total"] ) == 1   ){
                                if ( $key != "total" ){
                                    $outPut .=  "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $this->orderCacheData[$billing_email]["total"] ) . "</span>" ; 
                                }
                            } else {
                                $outPut .=  "<span class='Circle tips " . $key . "' style='background-color:" . $this->colour_func( $key, get_option( 'wcchpo_colour_' . $key ) ) . "' data-tip='" . $key . "'>" . count( $this->orderCacheData[$billing_email]["total"] ) . "</span>" ;
                            }
                        }
                    }
                } 
                # user note on Order table
                $wcchpo_user_note = get_user_meta( $customer_id, 'wcchpo_user_note', true );
                # check & Balance 
	            if ( $this->displayUserNote  && !empty( $wcchpo_user_note ) ) {
	                $outPut .=  "<span class='tips dashicons dashicons-admin-comments' style='padding:2px;' data-tip='" . $wcchpo_user_note . "' > </span>" ;
                }
                # Display the History 
                if ( isset( $this->orderCacheData[ $customer_id ]["total"] ))  {
                    # if ... not empty 
                    if ( count( $this->orderCacheData[ $customer_id ]["total"] ) != 1   ){
                         $outPut .= "<a href='" . esc_url( add_query_arg( array(
                        'action' => 'order',
                        'userID' => $customer_id,
                        ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;' target='_blank' data-tip='This user orders history'>  </a>";
                    }
                } else {
                    # if ... is not empty 
                    if ( isset(  $this->orderCacheData[ $billing_email ]["total"] ) &&  count( $this->orderCacheData[ $billing_email ]["total"] ) != 1   ){
                         $outPut .= "<a href='" . esc_url( add_query_arg( array(
                        'action'    => 'order',
                        'userEmail' => $billing_email,
                        ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;' target='_blank' data-tip='This user orders history'>  </a>";
                    }
                }
                # Print the Formatted txt 
                echo  $outPut ;

            } else {

                # This part Will Work if Order cache is Enabled 
                # Check & Balance to see Customer ID
                if ( $customer_id ) {
                    # Getting order by customer ID
                    $orders = wc_get_orders( array(
                        'limit'       => -1,
                        'return'      => 'objects',
                        'orderby'     => 'date',
                        'customer_id' => $customer_id,
	                ));
                } else {
                    # Getting orders by customer email address
                    $orders = wc_get_orders( array(
                        'limit'       => -1,
                        'return'      => 'objects',
                        'orderby'     => 'date',
                        'customer'    => $billing_email,
                    ));
                }
	            # Total Number of Order of This User
	            $total_number_of_orders = count( $orders );
	            # Getting All Order Statuses
	            $statuses_orders            = array();
	            $this_user_orders_id_status = array();
	            $order_statuses             = wc_get_order_statuses();
	            # Removing wc- from every item
	            foreach ( $order_statuses as $key => $value ) {
	                $statuses_orders[ substr( $key, 3 ) ] = 0;
                }
                
	            # Count order by order_statuses of this user
	            foreach ( $orders as $value ) {
	                if ( isset( $statuses_orders[ $value->get_status() ] ) ) {
	                    $statuses_orders[ $value->get_status() ]++;
	                }
	                # User Order ids and There Status in below array
	                $this_user_orders_id_status[ $value->get_id() ] = $value->get_status();
                }
                
	            # Printing Statuses
	            if ( !empty($total_number_of_orders) && $total_number_of_orders > 1 ) {
	                $total_colour = ( empty( get_option( 'wcchpo_colour_total' )) ? "#cccccc" : get_option( 'wcchpo_colour_total' ) );
	                echo  "<span class='Circle tips' style='background-color:" . $total_colour . "' data-tip='Total number of orders by this user'>" . $total_number_of_orders . "</span>" ;
                }
                
                # Looping orders 
	            foreach ( $statuses_orders as $status_key => $status_number ) {
	                if ( !empty( $status_number ) ) {
	                    echo  "<span class='Circle tips " . $status_key . "' style='background-color:" . $this->colour_func( $status_key, get_option( 'wcchpo_colour_' . $status_key ) ) . "' data-tip='" . $status_key . "'>" . $status_number . "</span>" ;
	                }
                }

                # user note on Order table
	            $wcchpo_user_note = get_user_meta( $customer_id, 'wcchpo_user_note', true );
	            if ( $this->displayUserNote  && !empty($wcchpo_user_note) ) {
	                echo  "<span class='tips dashicons dashicons-admin-comments' style='padding:2px;' data-tip='" . $wcchpo_user_note . "' > </span>" ;
                }

	            # Order table history Link if show or hide 
	            if (  $customer_id ) {
                    # check & balance is number of orders is not zero Do the task
                    if ( $total_number_of_orders > 1 ){
                        echo  "<a href='" . esc_url( add_query_arg( array(
	                    'action' => 'order',
	                    'userID' => $customer_id,
	                    ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;' data-order-id='" . $post_id . "' target='_blank' data-tip='This user orders history'>  </a>" ;
                    }
	            } else {
                    # check & balance is number of orders is not zero Do the task
                    if ( $total_number_of_orders > 1 ) {
                        echo  "<a href='" . esc_url( add_query_arg( array(
	                    'action' => 'order',
	                    'userEmail' => $billing_email,
	                    ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;' data-order-id='" . $post_id . "' target='_blank' data-tip='This user orders history'>  </a>" ;
                    }
                }
            }
        }
    }
    
    /**
	 * Adding Column on Users Table.
	 * @since    5.0.0
	 * @param      string    $column       column ID.
	*/
    public function wcchpo_User_table_order_history_column( $column ){
        $column['wcchpo_user_order_details'] = __( 'Order history', 'wcchpo' );
        return $column;
    }
    
    /**
	 * Display user table order history cell content;
	 * @since      5.0.0
	 * @param      string    $content        Column content .
	 * @param      string    $column_name    Column name.
	 * @param      string    $customer_id    Customer id 
	*/
    public function wcchpo_User_table_order_history_column_rows( $content, $column_name, $customer_id ){
        if ( $column_name == 'wcchpo_user_order_details' ) {               
            # is in the Cache or not ;
            if ( empty($customer_id) ) {
                $content .= "<div id='circle_holder'> <span  class='tips' data-tip=' This order is from a Guest or generated by software'>\r\n \t\t\t\t\t\t<code> <b> Guest </b> </code> </span> </div> ";
                return $content;
            }

            # getting orders of the customer by ID 
            $orders = wc_get_orders( array(
                'limit'       => -1,
                'return'      => 'objects',
                'orderby'     => 'date',
                'customer_id' => $customer_id,
            ));

            // Total Number of Order of This User
            $total_number_of_orders = count( $orders );
            // SET user Total Number of order into Users meta information # Important if don't Set it Up It will Show Error || On Sortable User table Order Column
            update_user_meta( $customer_id, "wc_total_number_of_orders", $total_number_of_orders );
            # Getting All Order Statuses
            $statuses_orders = array();
            $order_statuses = wc_get_order_statuses();

            # Removing wc- from every item
            foreach ( $order_statuses as $key => $value ) {
                $statuses_orders[substr( $key, 3 )] = 0;
            }

            # Count order by order_statuses of this user
            foreach ( $orders as $value ) {
                if ( isset( $statuses_orders[$value->get_status()] ) ) {
                    $statuses_orders[$value->get_status()]++;
                }
            }
            
            # if total number of order is not empty!
            if ( ! empty($total_number_of_orders) ) {
                # getting the colours 
                $total_colour = ( empty( get_option( 'wcchpo_colour_total' ) ) ? "#cccccc" : get_option( 'wcchpo_colour_total' ) );
                $content .= "<span class='Circle tips' style='background-color: " . $total_colour . "; cursor: default;' title='Total number of orders by this user'>" . $total_number_of_orders . "</span>";
                # Looping the Order statuses
                foreach ( $statuses_orders as $status_key => $status_number ) {
                    if ( ! empty( $status_number ) ) {
                        $content .= "<span class='Circle tips " . $status_key . "' style='background-color:" . $this->colour_func( $status_key, get_option( 'wcchpo_colour_' . $status_key ) ) . "; cursor: default;' title='" . $status_key . "'>" . $status_number . "</span>";
                    }
                }

                # user note
                $wcchpo_user_note = get_user_meta( $customer_id, 'wcchpo_user_note', true );
                if ( ! empty( $wcchpo_user_note ) ) {
                    $content .= "<span class='dashicons dashicons-admin-comments' style='padding:2px;' title='" . $wcchpo_user_note . "' ></span>";
                }
            
                if ( empty( $total_number_of_orders ) ) {
                    return $content;
                } elseif ( $total_number_of_orders == 1 ) {
                    $content .= "<a href='" . esc_url( add_query_arg( array(
                        'post'   => $orders[0]->get_id() ,
                        'action' => 'edit',
                    ), site_url( '/wp-admin/post.php' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; color:#27AE60 ; padding:2px ;' target='_blank' title='This order history'>  </a>";
                } else {
                    $content .= "<a href='" . esc_url( add_query_arg( array(
                        'action' => 'order',
                        'userID' => $customer_id,
                    ), site_url( '/wp-admin/admin.php?page=wcchpo-history' ) ) ) . "' class='dashicons dashicons-external tips' style='cursor: alias; padding:2px;' target='_blank' title='This user orders history'>  </a>";
                }

                return $content;
            }
        }
    }

    /**
	 * User table Column.
	 * @since    5.0.0
	 * @param    string    $columns       The name of this plugin.
	*/
    public function wcchpo_User_table_order_history_column_add_sortable( $columns ){
        $columns['wcchpo_user_order_details'] = 'wcchpo_user_order_details';
        return $columns;
    }
    
    /**
	 * User table Column content.
	 * @since    5.0.0
	 * @param      string    $user_query    user Query .
	*/
    public function wcchpo_User_table_order_history_column_add_sortable_query( $user_query ){
        if ( isset( $user_query->query_vars['orderby'] ) && 'wcchpo_user_order_details' == $user_query->query_vars['orderby'] ) {
            $user_query->query_vars = array_merge( $user_query->query_vars, array(
                'meta_key' => 'wc_total_number_of_orders',
                'orderby'  => 'meta_value_num',
            ) );
        }
        return $user_query;
    }
    
    /**
	 * Function  User_agent.
	 * @since    5.0.0
	 * @param    string    $user_agent_txt    browser user agent txt.
	*/
    public function wcchpo_useragent( $user_agent_txt = '' ){
        
        if ( strpos( $user_agent_txt, 'Windows' ) !== false ) {
            //Windows
            if ( strpos( $user_agent_txt, 'Mobile' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Windows Mobile   </span>";
            } elseif ( strpos( $user_agent_txt, 'Phone' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Windows phone    </span>";
            } else {
                return "<span title='".$user_agent_txt."'> ¯\_(ツ)_/¯        </span>";
            }
            
            return "<span title='".$user_agent_txt."'> Windows";
        } elseif ( strpos( $user_agent_txt, 'Linux' ) !== false ) {
            // Linux
            if ( strpos( $user_agent_txt, 'Android' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Android  </span>";
            } elseif ( strpos( $user_agent_txt, 'Ubuntu' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Ubuntu   </span>";
            } elseif ( strpos( $user_agent_txt, 'NetCast' ) !== false ) {
                return "<span title='".$user_agent_txt."'> NetCast  </span>";
            } elseif ( strpos( $user_agent_txt, 'SMART-TV' ) !== false ) {
                return "<span title='".$user_agent_txt."'> SMART-TV </span>";
            } elseif ( strpos( $user_agent_txt, 'X11' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Linux    </span>";
            } elseif ( strpos( $user_agent_txt, 'Web0S' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Web0S    </span>";
            } else {
                return "<span title='".$user_agent_txt."'> ¯\_(ツ)_/¯</span>";
            }
            
            return "Linux";
        } elseif ( strpos( $user_agent_txt, 'Mac OS X' ) !== false ) {
            // Mack
            if ( strpos( $user_agent_txt, 'iPhone' ) !== false ) {
                return "<span title='".$user_agent_txt."'> iPhone   </span>";
            } elseif ( strpos( $user_agent_txt, 'iPad' ) !== false ) {
                return "<span title='".$user_agent_txt."'> iPad     </span>";
            } elseif ( strpos( $user_agent_txt, 'Macintosh' ) !== false ) {
                return "<span title='".$user_agent_txt."'> Macintosh </span>";
            } else {
                return "<span  title='".$user_agent_txt."'>¯\_(ツ)_/¯ </span>";
            }
            
            return "<span title='".$user_agent_txt."'> Mac os    </span>";
        } elseif ( strpos( $user_agent_txt, 'Nokia' ) !== false ) {
            // Symbion
            return "<span title='".$user_agent_txt."'> Symbian   </span>";
        } else {
            return "<span title='".$user_agent_txt."'> ¯\_(ツ)_/¯ </span>";;
        }
    }
    
    /**
	 * Ajax Setting User Notice Date .
	 *
	 * @since    5.1.0
	*/
    public function wcchpo_ajax(){

        # For Removing Notice for that Day ; for Displaying  Messaging on user Admin Dashboard
        if ( isset( $_POST['userID'] ) && $_POST['userID'] == get_current_user_id()  ){
            update_user_meta( get_current_user_id(), "wcchpo_message_date", date("Ymd") );
            echo json_encode( array( 1,"wcchpo message date is set;" ) );
        }

        # Use Cache; orderCacheStatus
        if ( isset( $_POST['orderCacheStatus'] ) && $_POST['orderCacheStatus'] == "alternative"  ){
            if ( get_option( "wcchpo_orderCacheStatus" ) ){
                $r = update_option( "wcchpo_orderCacheStatus",  FALSE  );
            } else {
                $r = update_option( "wcchpo_orderCacheStatus", TRUE   );
            }
            echo json_encode( array(1, $r ) );
        }

        # Display User note On order page;
        if ( isset( $_POST['displayUserNote'] ) && $_POST['displayUserNote'] == "alternative"  ){
            if ( get_option( "wcchpo_displayUserNote" ) ){
                $r = update_option( "wcchpo_displayUserNote",  FALSE  );
            } else {
                $r = update_option( "wcchpo_displayUserNote", TRUE );
            }
            echo json_encode( array(1, $r  ) );
        }

        # For removing Cache;
        if ( isset( $_POST['deleteOrderCache'] ) && $_POST['deleteOrderCache'] == 1  ){
            $this->orderCacheData = [];
            $r = delete_option("wcchpo_orderCacheData");
            echo json_encode( array(1, $r) );
        }

        exit ;
    }

    /**
	 * Admin footer function .
	 *
	 * @since    5.1.0
	*/
    public function wcchpo_admin_footer( ){
        # setting Cache to  the Database ;
        if ( get_current_screen()->id == "edit-shop_order"  ||  get_current_screen()->id == "users" ){
            if ( $this->orderCacheStatus ){      // Do if Cache is Enabled ;
                update_option( "wcchpo_orderCacheData",  $this->orderCacheData );
            }
        }
        # Below for Ajax 

        ?>
        <script>

            // Hold purchase message for that day 
            jQuery(document).on( 'click', '#wcchpo_notice > button', function() {
                var data = {
                    'action': "wcchpo_ajax",
                    'userID': "<?php echo get_current_user_id() ?> " 
                };
                //  AJAX request 
                jQuery.post("<?php echo admin_url( 'admin-ajax.php' ) ?>", data, function(response) {
                    console.log(response);
                });
            });

            // Use Order Cache 
            jQuery(document).on( 'click', '#orderCacheStatus', function() {
                var data = {
                    'action': "wcchpo_ajax",
                    'orderCacheStatus':"alternative"
                };
                //  AJAX request 
                jQuery.post("<?php echo admin_url( 'admin-ajax.php' ) ?>", data, function(response) {
                    console.log(response);
                });
            });

            //  Display User note On order page;
            jQuery(document).on( 'click', '#displayUserNote', function() {
                var data = {
                    'action': "wcchpo_ajax",
                    'displayUserNote': "alternative"
                };
                //  AJAX request 
                jQuery.post("<?php echo admin_url( 'admin-ajax.php' ) ?>", data, function(response) {
                    console.log(response);
                });
            });

            // Reset Cache 
            jQuery(document).on( 'click', '#deleteOrderCache', function() {
                var data = {
                    'action': "wcchpo_ajax",
                    'deleteOrderCache': 1 
                };
                //  AJAX request 
                jQuery.post("<?php echo admin_url( 'admin-ajax.php' ) ?>", data, function(response) {
                    console.log(response);
                });
            });

            // Yep 
        </script>
        <?php
    }
}
# Class Ends Here 


#--------------------------- For test and Debug ----------------------------
# Testing Starts From Here || Past this Code on Admin Message Callback function;
# ++++++++++++++++++++++++++++ Hello From Here +++++++++++++++++++++++++++++
// echo"<pre>";
//     echo"wcchpo_orderCacheStatus : ";
//     print_r( get_option( "wcchpo_orderCacheStatus" ) );
//     echo"<br>";
//     echo"wcchpo_displayUserNote : ";
//     print_r( get_option( "wcchpo_displayUserNote" ) );
//     echo"<br>";
//     echo"this->orderCacheStatus : ";
//     print_r( $this->orderCacheStatus );
//     echo"<br>";
//     echo"wcchpo_orderCacheData : ";
//     print_r( get_option( "wcchpo_orderCacheData" ) );
//     --------------------------------------------------------------------
//     error_log( print_r( " writing error log to log file ", true ) );
//
//
// echo"</pre>";
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++