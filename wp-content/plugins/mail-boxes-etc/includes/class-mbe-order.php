<?php

use Dompdf\Dompdf;
use \iio\libmergepdf\Merger;
use \iio\libmergepdf\Driver\TcpdiDriver;

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Mbe_E_Link_Order_List_Table extends WP_List_Table
{

	protected $helper;
	protected $logger;
	protected $ws;
	protected $mustCloseShipments;

    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'order',     //singular name of the listed records
            'plural'   => 'orders',    //plural name of the listed records
            'ajax'     => true        //does this table support ajax?
        ));
	    $this->ws = new Mbe_Shipping_Model_Ws();
	    $this->helper = new Mbe_Shipping_Helper_Data();
	    $this->logger = new Mbe_Shipping_Helper_Logger();
    }

    function column_ID($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="' . get_home_url() . '/wp-admin/post.php?post=%s&action=edit">%s</a>', $item['ID'], __('Edit', 'mail-boxes-etc')),
        );

        return sprintf('%s %s',
            $item['ID'],
            $this->row_actions($actions)
        );

        return sprintf('%1$s <span style="color:silver"></span>%2$s', $item['id'], $this->row_actions($actions));
    }

    function column_post_author($item)
    {
        $order = new WC_Order($item['ID']);

        if (version_compare(WC()->version, '3', '>=')) {
            $billingFirstName = $order->get_billing_first_name();
            $billingLastName = $order->get_billing_last_name();
        }
        else {
            $billingFirstName = $order->billing_first_name;
            $billingLastName = $order->billing_last_name;
        }
        return sprintf('%1$s %2$s', $billingFirstName, $billingLastName);
    }

    function column_carrier($item)
    {
        //TODO: verify
        $order = new WC_Order($item["ID"]);
        $serviceName = $this->helper->getServiceName($order);
        return $serviceName;
        //return sprintf('%s', get_post_meta($item['ID'], 'woocommerce_mbe_tracking_name', true));
    }


    function column_tracking($item)
    {
        $trackings = $this->helper->getTrackings($item['ID']);
        if (empty($trackings)) {
            return '';
        }
        else {
            $html = '';
            $url = get_post_meta($item['ID'], 'woocommerce_mbe_tracking_url', true);

            $trackingString = $this->helper->getTrackingsString($item['ID']);
            if (count($trackings) > 1) {
                $html .= "<a target='_blank' href=" . $url . $trackingString . ">" . __('Track all', 'mail-boxes-etc') . "</a><br/>";
            }

            $i = 0;
            foreach ($trackings as $t) {
                if ($i > 0) {
                    $html .= "<br/>";
                }
                $html .= "<a target='_blank' href=" . $url . $t . ">" . $t . "</a>";
                $i++;
            }
            return $html;
        }
    }

    function column_post_date($item)
    {
        return $item['post_date'];
    }

    function column_total($item)
    {
        $order = new WC_Order($item['ID']);
        return $order->get_total() . ' &euro;';
    }

    function column_payment($item)
    {
        $order = new WC_Order($item['ID']);
        if (version_compare(WC()->version, '3', '>=')) {
            $paymentMethodTitle = $order->get_payment_method_title();
        }
        else {
            $paymentMethodTitle = $order->payment_method_title;
        }
        return $paymentMethodTitle;
    }

    function column_status($item)
    {
        return $this->helper->isShippingOpen($item['ID']) ? __('Opened', 'mail-boxes-etc') : __('Closed', 'mail-boxes-etc');
    }

    function column_files($item)
    {
        $files = $this->helper->getFileNames($item['ID']);

        $trackings = $this->helper->getTrackings($item['ID']);
        if (empty($files)) {
            return '';
        }
        else {
            $html = '';
            for ($i = 0; $i < count($files); $i++) {
                $filename = __('Label', 'mail-boxes-etc') . " " . ($i + 1);
                $path = $this->helper->mbeUploadUrl(). DIRECTORY_SEPARATOR . $files[$i];
                $html .= "<a target='_blank' href=" . $path . " style='margin-bottom:5px;display: inline-block;'>" . $filename . "</a></br>";
            }
            if (isset($trackings[0]) && !$this->helper->isTrackingOpen($trackings[0])) {
                $path = $this->helper->mbeUploadUrl(). DIRECTORY_SEPARATOR . 'MBE_' . $trackings[0] . "_closed.pdf";
                $html .= "<a target='_blank' href=" . $path . " style='margin-bottom:5px;display: inline-block;'>" . __('Closure file', 'mail-boxes-etc') . "</a></br>";
            }
            return $html;
        }
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['ID']
        );
    }

    function get_columns()
    {
        $columns = array('cb' => '<input type="checkbox" />');
        $columns['id'] = __('Id', 'mail-boxes-etc');

        if ($this->mustCloseShipments) {
            $columns['status'] = __('Status', 'mail-boxes-etc');
        }
        $columns['post_author'] = __('Customer', 'mail-boxes-etc');
        $columns['payment'] = __('Payment', 'mail-boxes-etc');
        $columns['post_date'] = __('Date', 'mail-boxes-etc');
        $columns['total'] = __('Total', 'mail-boxes-etc');
        $columns['carrier'] = __('Carrier', 'mail-boxes-etc');
        $columns['tracking'] = __('Tracking', 'mail-boxes-etc');
        $columns['files'] = __('Downloads', 'mail-boxes-etc');

        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'        => array('id', true),
            'post_date' => array('date', true),
        );

        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array();
        if (!$this->helper->isCreationAutomatically()) {
            $actions['creation'] = __('Create shipments in MBE Online', 'mail-boxes-etc');
        }
        if (!$this->helper->isClosureAutomatically()) {
            if ($this->mustCloseShipments) {
                $actions['closure'] = __('Close shipments in MBE Online', 'mail-boxes-etc');
            }
        }
		if (!$this->helper->isOnlineMBE()) {
			$actions['return'] = __('Create return shipment', 'mail-boxes-etc');
		}
        $actions['downloadLabels'] = __('Download shipping Labels', 'mail-boxes-etc');
        return $actions;
    }

    function process_bulk_action()
    {

        if (isset($_REQUEST['id'])) {
            $post_ids = array_map('absint', (array)$_REQUEST['id']);

            switch ($this->current_action()) {
                case 'creation':

                    $toCreationIds = array();
                    $alreadyCreatedIds = array();
                    $errorsIds = array();
                    foreach ($post_ids as $post_id) {
                    	$post_id = (int)$post_id;
                        if ($this->helper->hasTracking($post_id)) {
                            array_push($alreadyCreatedIds, $post_id);
                        }
                        else {
                            $order = new WC_Order($post_id);
                            if ($this->process_order_meta_box_actions($order)) {
                                array_push($toCreationIds, $post_id);
                            }
                            else {
                                array_push($errorsIds, $post_id);
                            }

                        }
                    }
                    if (count($toCreationIds) > 0) {
                        echo '<div class="updated"><p>' . sprintf(__('Total of %d order shipment(s) have been created.', 'mail-boxes-etc'), $toCreationIds) . '</p></div>';
                    }
                    if (count($alreadyCreatedIds) > 0) {
                        echo '<div class="error"><p>' . sprintf(__('Total of %d order shipment(s) was already created.', 'mail-boxes-etc'), $alreadyCreatedIds) . '</p></div>';
                    }
                    if (count($errorsIds) > 0) {
                        echo '<div class="error"><p>' . sprintf(__('WARNING %d shipments couldn\'t be created due to an error.', 'mail-boxes-etc'), $errorsIds) . '</p></div>';
                    }
                    break;
                case 'closure':
                    if (!$this->helper->isClosureAutomatically()) {
                        if ($this->mustCloseShipments) {

                            $toClosedIds = array();
                            $alreadyClosedIds = array();
                            $withoutTracking = array();

                            foreach ($post_ids as $post_id) {
                                if (!$this->helper->hasTracking($post_id)) {
                                    array_push($withoutTracking, $post_id);
                                }
                                elseif ($this->helper->isShippingOpen($post_id)) {
                                    array_push($toClosedIds, $post_id);
                                }
                                else {
                                    array_push($alreadyClosedIds, $post_id);
                                }
                            }
                            $this->ws->closeShipping($toClosedIds);
                            if (count($withoutTracking) > 0) {
                                echo '<div class="error"><p>' . sprintf(__('%s - Total of %d order(s) without tracking number yet.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $withoutTracking) . '</p></div>';
                            }
                            if (count($toClosedIds) > 0) {
                                echo '<div class="updated"><p>' . sprintf(__('%s - Total of %d order(s) have been closed.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $toClosedIds) . '</p></div>';
                            }

                            if (count($alreadyClosedIds) > 0) {
                                echo '<div class="error"><p>' . sprintf(__('%s - Total of %d order(s) was already closed', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $alreadyClosedIds) . '</p></div>';
                            }
                        }
                    }
                    break;
	            case 'downloadLabels':
	            	try {
			            $labelsPdf = [];
			            foreach ( $post_ids as $post_id ) {
				            $labels = $this->helper->getFileNames( $post_id );
				            if ( is_array( $labels ) ) {
					            foreach ( $labels as $l ) {
						            $labelType   = preg_replace( '/.*\./', '', $l );
						            $labelsPdf[] = $this->convertShippingLabelToPdf( $labelType, file_get_contents( $this->helper->mbeUploadDir() . DIRECTORY_SEPARATOR . $l ) );
					            }
				            }
			            }
			            if (!empty($labelsPdf)) {
				            $outputPdf     = $this->combineLabelsPdf($labelsPdf);
				            $outputPdfPath = $this->helper->mbeUploadDir() . DIRECTORY_SEPARATOR . current_datetime()->getTimestamp() . rand( 0, 999 ) . '.pdf';
				            if ( file_put_contents( $outputPdfPath, $outputPdf ) !== false ) {
					            // dowload the files
					            header( 'Content-Description: File Transfer' );
					            header( 'Content-Type: application/pdf' );
					            header( "Content-Disposition: attachment; filename=mbe-labels.pdf" );
					            header( 'Content-Transfer-Encoding: binary' );
					            header( 'Expires: 0' );
					            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
					            header( 'Pragma: public' );
					            header( 'Content-Length: ' . filesize( $outputPdfPath ) );
					            ob_clean();
					            flush();
					            readfile( $outputPdfPath );
					            wp_delete_file( $outputPdfPath );
					            exit;
				            } else {
					            $errMess = __( 'MBE Download shipping labels - error writing to file ' . $outputPdfPath );
					            $this->logger->log( $errMess );
				            }
			            } else {
				            $this->logger->log( __('MBE Download shipping labels - no label to download ') );
			            }
		            } catch (\Exception $e) {
	            		$errMess = __( 'MBE Download shipping labels - Unexpected error' ) . ' - ' . $e->getMessage() ;
			            $this->logger->log($errMess);
		            }
		            break;
	            case 'return' :
		            if (!$this->helper->isOnlineMBE()) {
			            $toReturnIds = array();
			            $alreadyReturnedIds = array();
			            $withoutTracking = array();

			            foreach ($post_ids as $post_id) {
				            if (!$this->helper->hasTracking($post_id)) {
					            array_push($withoutTracking, $post_id);
				            }
				            elseif ($this->helper->isReturned($post_id)) {
					            array_push($alreadyReturnedIds, $post_id);
				            }
				            else {
					            array_push($toReturnIds, $post_id);
				            }
			            }
			            $returnedShipping = $this->ws->returnShipping($toReturnIds);
			            $returnedIds = 0;
			            foreach ( $toReturnIds as $return_id ) {
				            $order = new WC_Order($return_id);
				            $returnTrackingMeta = '';
				            $returnedTrackings = 0;
				            $trackingList = $this->helper->getTrackings($this->helper->getOrderId($order));
				            $returnedIds += count(array_filter($returnedShipping[$return_id], function($x) { return !empty($x); }));
				            foreach ( $trackingList as $tracking ) {
								$returnedTrackings += (!empty($returnedShipping[$return_id][$tracking])?1:0);
					            $returnTrackingMeta .= (!empty($returnedShipping[$return_id][$tracking])?$returnedShipping[$return_id][$tracking]:'').Mbe_Shipping_Helper_Data::MBE_SHIPPING_TRACKING_SEPARATOR;
				            }
							if ($returnedTrackings>0) {
								// add or update metadata
								update_post_meta($this->helper->getOrderId($order), Mbe_Shipping_Helper_Data::SHIPMENT_SOURCE_RETURN_TRACKING_NUMBER, substr($returnTrackingMeta, 0,-1), true);
							}
			            }

			            if (count($withoutTracking) > 0) {
				            echo '<div class="error"><p>' . sprintf(__('%s - Total of %d order(s) without tracking number yet.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $withoutTracking) . '</p></div>';
			            }
//			            if ($returnedIds > 0) {
				            echo '<div class="'.($returnedIds>0?'updated':'error').'"><p>' . sprintf(__('%s - Total of %d return shipments created.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $returnedIds) . '</p></div>';
//			            }

			            if (count($alreadyReturnedIds) > 0) {
				            echo '<div class="error"><p>' . sprintf(__('%s - Total of %d order were already returned', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $alreadyReturnedIds) . '</p></div>';
			            }
		            }

					break;
            }
        }
        else {
            if ($this->current_action()) {
                echo '<div class="error"><p>' . sprintf(__('Please select items.', 'mail-boxes-etc')) . '</p></div>';
            }
        }
    }

    protected function process_order_meta_box_actions($order)
    {
        include_once 'class-mbe-tracking-factory.php';

	    $orderId = $this->helper->getOrderId($order);
        return mbe_tracking_factory::create($orderId);
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
	    $this->mustCloseShipments = $this->ws->mustCloseShipments(); // Use a local parameter to avoid calling the function multiple times for each row

	    $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $paged = $paged * $per_page;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? sanitize_text_field($_REQUEST['order']) : 'desc';

        $postmetaTableName = $wpdb->prefix . 'postmeta';
        $order_ids = $this->helper->select_mbe_ids();
	    $orders_custom_mapping_ids = $this->helper->select_custom_mapping_ids();
        $order_filter = 'AND (ID IN ('.$order_ids.') OR ID IN ('.$orders_custom_mapping_ids.'))';

        if (isset($_REQUEST["order_search"]) && $_REQUEST["order_search"] != "") {
            $search = esc_sql($_REQUEST["order_search"]);

            $total_items = $wpdb->get_var("SELECT COUNT(DISTINCT(p.ID)) FROM $table_name AS p
                                           LEFT JOIN $postmetaTableName AS pm ON pm.post_id = p.ID
                                           WHERE post_type='shop_order'
                                           {$order_filter}
                                           AND pm.meta_value LIKE '%$search%'
                                           ");

            $query = $wpdb->prepare("SELECT p.*, p.ID FROM $table_name AS p
                                       LEFT JOIN $postmetaTableName AS pm ON pm.post_id = p.ID
                                       WHERE post_type='shop_order'
                                       {$order_filter}
                                       AND pm.meta_key IN ('_billing_last_name', '_billing_first_name', 'woocommerce_mbe_tracking_name','woocommerce_mbe_tracking_number', '_order_total', '_payment_method_title')
                                       AND (pm.meta_value LIKE '%%$search%%')
                                       GROUP BY p.ID
                                       ORDER BY $orderby $order LIMIT %d OFFSET %d", array($per_page, $paged));
            $this->items = $wpdb->get_results($query, ARRAY_A);
        }
        else {
            $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name
                                            WHERE post_type='shop_order'
                                            {$order_filter}");
            $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name
                                                        WHERE post_type='shop_order'
                                                        {$order_filter}
                                                        ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page'    => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

	/**
	 * @param string $labelType
	 * @param $label
	 * @return mixed
	 */
	public function convertShippingLabelToPdf(string $labelType, $label)
	{
		$dompdf = new Dompdf();

		switch ($labelType) {
			case 'html':
				$domOptions = $dompdf->getOptions();
				$domOptions->setIsHtml5ParserEnabled(true);
				$domOptions->setIsRemoteEnabled(true);
				$domOptions->setDefaultMediaType('print');
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->setOptions($domOptions);
				$dompdf->loadHtml($label);
				$dompdf->render();
				return $dompdf->output();
			case 'gif':
				$gifHtml = '<img style="min-width:90%; max-height:90%" src="data:image/gif;base64,' . base64_encode($label) . '">';
				$dompdf->loadHtml($gifHtml);
				$dompdf->render();
				return $dompdf->output();
			default: //pdf
				return $label;
		}
	}

	/**
	 * Merge an array of PDF stream labels
	 *
	 * @param array $labelsContent
	 * @return string
	 */
	public function combineLabelsPdf(array $labelsContent)
	{
		$outputPdf = new Merger(new TcpdiDriver());
		foreach ( $labelsContent as $item ) {
			$outputPdf->addRaw($item);
		}
		return $outputPdf->merge();
	}

}