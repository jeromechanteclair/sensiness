<?php

global $wpdb;

$helper = new Mbe_Shipping_Helper_Data();
$logger = new Mbe_Shipping_Helper_Logger();

if ($helper->isEnabled() && $helper->isClosureAutomatically()) {

    $ws = new Mbe_Shipping_Model_Ws();
    if ($ws->mustCloseShipments()) {

        $logger->log('Cron Close shipments');

        $time = time();
        $to = date('Y-m-d H:i:s', $time);
        $lastTime = $time - 60 * 60 * 24 * 30; // 60*60*24*2
        $from = date('Y-m-d H:i:s', $lastTime);
	    $shippingMethods = MBE_ESHIP_ID.'|wf_mbe_shipping'; // search also for orders created with the old plugin

        $post_status = implode("','", array('wc-processing', 'wc-completed'));

		$order_ids = $helper->select_mbe_ids();
	    $orders_custom_mapping_ids = $helper->select_custom_mapping_ids();
	    $order_filter = 'AND (ID IN ('.$order_ids.') OR ID IN ('.$orders_custom_mapping_ids.'))';

	    $results = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = 'shop_order' $order_filter AND post_status IN ('{$post_status}')" );

        $post_ids = array();
        foreach ($results as $order) {
            $post_ids[] = ($order->ID);
        }
        $logger->logVar($post_ids,'Order with shipments to close id');
        $toClosedIds = array();
        $alreadyClosedIds = array();
        $withoutTracking = array();

        foreach ($post_ids as $post_id) {
            if (!$helper->hasTracking($post_id)) {
                array_push($withoutTracking, $post_id);
            } elseif ($helper->isShippingOpen($post_id)) {
                array_push($toClosedIds, $post_id);
            } else {
                array_push($alreadyClosedIds, $post_id);
            }
        }

        $logger->logVar($toClosedIds,'Order with shipments to close id');

        $ws->closeShipping($toClosedIds);

        if (count($withoutTracking) > 0) {
            echo sprintf(__('%s - Total of %d order(s) without tracking number yet.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $withoutTracking);
        }
        if (count($toClosedIds) > 0) {
            echo sprintf(__('%s - Total of %d order(s) have been closed.', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $toClosedIds);
        }

        if (count($alreadyClosedIds) > 0) {
            echo sprintf(__('%s - Total of %d order(s) was already closed', 'mail-boxes-etc'), date('Y-m-d H:i:s'), $alreadyClosedIds);
        }

    }
}
die();