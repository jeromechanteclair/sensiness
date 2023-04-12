<?php

if (!class_exists('Mbe_Shipping_Csv_Editor')) {
	require_once(__DIR__ . '/class-mbe-csv-editor.php');
}

class Mbe_Shipping_Csv_Editor_Package_Product extends Mbe_Shipping_Csv_Editor
{

	function __construct( $args = array(
		'singular' => 'product',
		'plural'   => 'products',
	) ) {
		$this->columns = [
			'cb' => '<input type="checkbox" />',
			'id' => __('ID', 'mail-boxes-etc'),
			'product_sku' => __('Product SKU', 'mail-boxes-etc'),
			'package_code' => __('Package code', 'mail-boxes-etc'),
			'custom_package' => __('Custom package', 'mail-boxes-etc'),
			'single_parcel' => __('Single parcel', 'mail-boxes-etc'),
		];
		$this->sortable_columns = [
			'id'           => ['id', false],
			'product_sku'  => ['product_sku'],
			'package_code' => ['package_code'],
		];

		$this->csvType = 'packages-products';

		$this->default = [
			'id' =>0,
			'custom_package' => 0,
			'single_parcel' => 0,
			'product_sku' => '',
			'package_code' => '',
        ];

        $this->tableName =  Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_PRODUCT_TABLE_NAME;

		parent::__construct( $args );
	}

	function column_custom_package($item)
	{
		return $item['custom_package']?__('Yes'):__('No');
	}

	function column_single_parcel($item)
	{
		return $item['single_parcel']?__('Yes'):__('No');
	}

	public function get_title($singular = true){
		if($singular) {
			return ucwords(__('package for product','mail-boxes-etc'));
		}
		return ucwords(__('packages for products','mail-boxes-etc'));
	}

	public function validate_row($item) {
		$messages = array();

		if (empty(trim($item['package_code']))) $messages[] = __('Package code', 'mail-boxes-etc') . ' ' . __('required');
		if (empty(trim($item['product_sku']))) $messages[] = __('SKU') . ' ' . __('required');

        if ($this->has_duplicates('product_sku', $item['product_sku'], '%s', $item['id'])) $messages[] = __('Product SKU', 'mail-boxes-etc') . ' ' . __('exists');
		if ($this->hasDuplicateCustomPackageProduct($item)) $messages[] = __('Package code', 'mail-boxes-etc') . ' ' . __('used more than once or already set as custom');
		if ($this->isReservedCode($item['package_code'])) $messages[] = __('Reserved package code cannot be used', 'mail-boxes-etc');

		if (empty($messages)) return true;
		return implode('<br />', $messages);
	}

    protected function hasDuplicateCustomPackageProduct($item)
    {
        global $wpdb;
        $id = $item['id'];
	    $value = $item['package_code'];
        $custom = $item['custom_package'];

	    $idParam = !empty($id)?" AND id <> $id ":'';
        $flagParam = empty($custom)? " AND custom_package = 1 ":"";

	    $wpdb->get_results("SELECT id FROM $this->tableName WHERE package_code = '$value' $flagParam $idParam");
        return $wpdb->num_rows>0;
    }

	function form_meta_box_handler($item){
		?>
		<table style="width: 100%;" class="form-table">
			<tbody>
			<?php if($item['id']!=0) { ?>
                <tr class="form-field">
                    <th scope="row">
                        <label for="package_id"><?php _e( 'ID', 'mail-boxes-etc' ) ?></label>
                    </th>
                    <td>
                        <label id="package_id" style="width: 95%"><?php echo esc_attr( $item['id'] ) ?></label>
                    </td>
                </tr>
			<?php } ?>
			<tr class="form-field">
				<th scope="row">
					<label for="product_sku"><?php _e( 'Product SKU', 'mail-boxes-etc' ) ?></label>
				</th>
				<td>
					<input id="product_sku" name="product_sku" type="text" style="width: 95%"
					       value="<?php echo esc_attr( $item['product_sku'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Product SKU', 'mail-boxes-etc' ) ?>"
					       required>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="package_code"><?php _e( 'Package code', 'mail-boxes-etc' ) ?></label>
				</th>
				<td>
					<input id="package_code" name="package_code" type="text" style="width: 95%"
					       value="<?php echo esc_attr( $item['package_code'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Package code', 'mail-boxes-etc' ) ?>"
					       required>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="custom_package"><?php _e( 'Custom package', 'mail-boxes-etc' ) ?></label>
				</th>
				<td>
					<input class="form-check-input" type="checkbox" name="custom_package" id="custom_package" value="1" <?php echo esc_attr( $item['custom_package']?'checked':'' ) ?>>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="single_parcel"><?php _e( 'Single parcel', 'mail-boxes-etc' ) ?></label>
				</th>
				<td>
					<input class="form-check-input" type="checkbox" name="single_parcel" id="single_parcel" value="1" <?php echo esc_attr( $item['single_parcel']?'checked':'' ) ?>>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}


}