<style>
	#wpo-wcpdf-settings { display: none; }
</style>
<form method="post" action="" id="wcpdf-pro-bulk-export">
	<table class="form-table">
		<?php
			// Allow 3rd parties to prepend elements to the settings page
			// @author Aelia
			do_action( 'wpo_wcpdf_export_bulk_before_settings' );
		?>
		<tr>
			<td width="180px"><?php _e( 'Document' ); ?></td>
			<td>
				<?php $documents = WPO_WCPDF()->documents->get_documents(); ?>
				<select name="template_type" id="template_type">
					<?php
					foreach ($documents as $document) {
						printf('<option value="%s">%s</option>', $document->get_type(), $document->get_title());
					}

					// Allow to add extra options to the template type select,
					// which aren't linked to actual documents.
					do_action('wpo_wcpdf_export_bulk_template_type_options');

					?>
				</select>
			</td>
		</tr>
		<?php if( isset($cloud_service_slug) ) : ?>
		<tr>
			<td width="100px"><?php _e( 'From', 'wpo_wcpdf_pro' ); ?></td>
			<td>
				<?php $last_export = get_option( 'wpo_wcpdf_'.$cloud_service_slug.'_last_export', array('date'=>'','hour'=>'','minute'=>'') ); ?>
				<input type="text" id="date-from" name="date-from" value="<?php echo $last_export['date']; ?>" size="10">@
				<input type="text" class="hour" placeholder="h" name="hour-from" id="hour-from" maxlength="2" size="2" value="<?php echo $last_export['hour']; ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})">:<input type="text" class="minute" placeholder="m" name="minute-from" id="minute-from" maxlength="2" size="2" value="<?php echo $last_export['minute']; ?>" pattern="[0-5]{1}[0-9]{1}"> (<?php _e( 'optional', 'wpo_wcpdf_pro' ); ?>)
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td><?php _e( 'To', 'wpo_wcpdf_pro' ); ?></td>
			<td>
				<?php $now = array('date'=>date_i18n('Y-m-d'),'hour'=>date_i18n('H'),'minute'=>date_i18n('i')); ?>
				<input type="text" id="date-to" name="date-to" value="<?php echo $now['date']; ?>" size="10">@
				<input type="text" class="hour" placeholder="h" name="hour-to" id="hour-to" maxlength="2" size="2" value="<?php echo $now['hour']; ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})">:<input type="text" class="minute" placeholder="m" name="minute-to" id="minute-to" maxlength="2" size="2" value="<?php echo $now['minute']; ?>" pattern="[0-5]{1}[0-9]{1}">
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Date type', 'wpo_wcpdf_pro' ); ?></td>
			<td>
				<select name="date_type" id="date_type">
					<option value="order_date"><?php _e( 'Order date', 'wpo_wcpdf_pro' ); ?></option>
					<option value="document_date"><?php _e( 'Document date', 'wpo_wcpdf_pro' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php _e( 'Filter status', 'wpo_wcpdf_pro' ); ?>
			</td>
			<td>
				<fieldset>
					<input type="checkbox" class="checkall" /> <?php _e( 'All statuses', 'wpo_wcpdf_pro' ); ?><br />
					<hr/ style="width:100px;text-align:left;margin-left:0;height: 1px;border: 0; border-top: 1px solid #ccc;padding: 0;">
					<?php
					// get list of WooCommerce statuses
					$order_statuses = array();
					if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
						$statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
						foreach ( $statuses as $status ) {
							$order_statuses[esc_attr( $status->slug )] = esc_html__( $status->name, 'woocommerce' );
						}
					} else {
						$statuses = wc_get_order_statuses();
						foreach ( $statuses as $status_slug => $status ) {
							// $status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
							$order_statuses[$status_slug] = $status;
						}
					}

					// list status checkboxes
					foreach ($order_statuses as $status_slug => $status) {
						printf('<input type="checkbox" class="status-filters" name="status_filter[]" value="%s" /> %s<br />', $status_slug, $status);
					}
					?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Only existing documents', 'wpo_wcpdf_pro' ); ?></td>
			<td>
				<input type="checkbox" id="only_existing" checked />
			</td>
		</tr>
		<tr>
			<td><?php _e( 'Skip free orders', 'wpo_wcpdf_pro' ); ?></td>
			<td>
				<input type="checkbox" id="skip_free" />
			</td>
		</tr>
		<?php
			// Allow 3rd parties to append elements to the settings page
			// @author Aelia
			do_action( 'wpo_wcpdf_export_bulk_after_settings' );
		?>
	</table>

	<?php do_action( 'wpo_wcpdf_export_bulk_before_action_buttons' ); ?>

	<?php if( WPO_WCPDF_Pro()->bulk_export->check_zip_archive() ) : ?>
		<span class="button bulk-export button-primary wpo_wcpdf_zip_bulk_export"><?php _e( 'Download ZIP', 'wpo_wcpdf_pro' ); ?></span>
	<?php else : ?>
		<?php printf('<div class="notice notice-error inline"><p><strong>%s:</strong> %s</p></div>', __( 'ZIP export disabled', 'wpo_wcpdf_pro' ), __( 'The PHP ZipArchive library could not found, contact your host to enable bulk downloading PDF files in a ZIP.', 'wpo_wcpdf_pro' ) ); ?>
	<?php endif; ?>

	<?php if( isset($cloud_api_is_enabled) && $cloud_api_is_enabled !== false && isset($cloud_service_name) ): ?>
		<span class="button bulk-export button-primary wpo_wcpdf_cloud_service_bulk_export"><?= sprintf( __( 'Export to %s', 'wpo_wcpdf_pro' ), $cloud_service_name ); ?></span>
	<?php endif ?>

	<?php do_action( 'wpo_wcpdf_export_bulk_after_action_buttons' ); ?>

	<span class="bulk-export-waiting" style="display: none;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'spinner.gif'; ?>" style="margin-top: 7px;"></span>
</form>
<?php
$output_compression = ini_get('zlib.output_compression');
if ( $output_compression && $output_compression !== 'off') {
	printf("<p><strong>%s</strong> %s</p>", __( 'Warning!', 'wpo_wcpdf_pro' ), __( 'zlib.output_compression is enabled in PHP for this site, this may cause issues when downloading ZIP files', 'wpo_wcpdf_pro' ) );
}
?>
<p style="width: 500px"><em><?php _e('Only exporting a few orders? You can also export by selecting orders in the WooCommerce order overview and then select one of the actions from the bulk dropdown!', 'wpo_wcpdf_pro' ); ?></em></p>
