<?php

class woocommerce_mbe_tracking_admin
{
    const TRACKING_TITLE_DISPLAY = "MBE Shipment Tracking";

    const SHIPMENT_SOURCE_TRACKING_NUMBER = "woocommerce_mbe_tracking_number";
    const SHIPMENT_SOURCE_TRACKING_NAME = "woocommerce_mbe_tracking_name";
    const SHIPMENT_SOURCE_TRACKING_SERVICE = "woocommerce_mbe_tracking_service";
    const SHIPMENT_SOURCE_TRACKING_ZONE = "woocommerce_mbe_tracking_zone";
    const SHIPMENT_SOURCE_TRACKING_URL = "woocommerce_mbe_tracking_url";
    const SHIPMENT_SOURCE_TRACKING_FILENAME = 'woocommerce_mbe_tracking_filename';
	const SHIPMENT_SOURCE_TRACKING_CUSTOM_MAPPING = "woocommerce_mbe_tracking_custom_mapping";
	const TRACKING_METABOX_KEY = "Tracking_Mbe_box";

    protected $helper;

    function __construct()
    {
	    $this->helper = new Mbe_Shipping_Helper_Data();

        if (is_admin()) {

            $this->init();
        }
    }

    private function init()
    {
        if (isset($_GET['post'])) {

            $order_id = (int)$_GET['post'];
            $order = $this->load_order($order_id);
            if ($order) {
                if ($this->helper->isEnabled() && $this->helper->isMbeShipping($order)) {
                    $this->tracking_string = $this->helper->getTrackingsString($order_id);
                    $this->tracking_data = $this->helper->getTrackings($order_id);
                    $this->tracking_files = $this->helper->getFileNames($order_id);
                    $this->tracking_name = get_post_meta($order_id, self::SHIPMENT_SOURCE_TRACKING_NAME, true);
                    $this->tracking_url = get_post_meta($order_id, self::SHIPMENT_SOURCE_TRACKING_URL, true);
                    if (isset($this->tracking_data[0]) && !$this->helper->isTrackingOpen($this->tracking_data[0])) {
                        $this->closure_file = $this->helper->mbeUploadUrl().'/MBE_' . $this->tracking_data[0] . "_closed.pdf";
                    }
                    add_action('add_meta_boxes_shop_order', array($this, 'add_mbe_tracking_metabox'), 15);
                }
            }
        }
    }

    function add_mbe_tracking_metabox()
    {

        global $post;

        if (!$post) {
            return;
        }

        $order = $this->load_order($post->ID);
        if (!$order) {
            return;
        }


        if (!empty($this->tracking_data)) {
            add_meta_box(self::TRACKING_METABOX_KEY, __(self::TRACKING_TITLE_DISPLAY, 'mail-boxes-etc'), array($this, 'tracking_metabox_content'), 'shop_order', 'side', 'default');
        }
    }

    function tracking_metabox_content()
    {
        ?>
		<ul class="order_actions submitbox">
			<li id="actions" class="wide">
				<strong><?php echo __('Carrier name: ', 'mail-boxes-etc') ?></strong> <?php echo $this->tracking_name ?>
			</li>
            <?php if (count($this->tracking_data) > 1) { ?>
				<li id="actions" class="wide">
					<a target="_blank" href="<?php echo $this->tracking_url . $this->tracking_string ?>"><?php echo __('Track all', 'mail-boxes-etc') ?></a>
				</li>
            <?php } ?>

            <?php foreach ($this->tracking_data as $t) { ?>
				<li id="actions" class="wide">
					<strong><?php echo __('Tracking id: ', 'mail-boxes-etc') ?></strong> <a target="_blank" href="<?php echo $this->tracking_url . $t ?>"><?php echo $t ?></a>
				</li>
            <?php } ?>
            <?php for ($i = 0; $i < count($this->tracking_files); $i++) { ?>
				<li id="actions" class="wide">
					<strong><?php echo __('Label', 'mail-boxes-etc') . " " . ($i + 1) . ": "; ?></strong> <a target="_blank" href="<?php echo $this->helper->mbeUploadUrl(). DIRECTORY_SEPARATOR . $this->tracking_files[$i]; ?>"><?php echo __("link", 'mail-boxes-etc'); ?></a>
				</li>
            <?php } ?>
            <?php if (isset($this->closure_file)) { ?>
				<li id="actions" class="wide">
					<strong><?php echo __('Closure file', 'mail-boxes-etc') . ": "; ?></strong> <a target="_blank" href="<?php echo $this->closure_file; ?>"><?php echo __("link", 'mail-boxes-etc'); ?></a>
				</li>
            <?php } ?>
		</ul>
		<script>
            jQuery(document).ready(function ($) {
                $("date-picker").datepicker();
            });

            jQuery("a.woocommerce_shipment_tracking").on("click", function () {
                location.href = this.href + '&wc_track_shipment=' + jQuery('#tracking_shipment_ids').val().replace(/ /g, '') + '&shipping_service=' + jQuery("#shipping_service").val();
                return false;
            });
		</script>
        <?php
    }

    function load_order($orderId)
    {
        if (!class_exists('WC_Order')) {
            return false;
        }
        $order = false;

        try {
            $order = new WC_Order($orderId);
        }
        catch (Exception $e) {
        }
        return $order;
    }
}

new woocommerce_mbe_tracking_admin();

?>
