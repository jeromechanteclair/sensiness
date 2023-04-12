<?php

if (!class_exists('Mbe_Shipping_Csv_Editor')) {
	require_once(__DIR__ . '/class-mbe-csv-editor.php');
}

class Mbe_Shipping_Csv_Editor_Package extends Mbe_Shipping_Csv_Editor
{

	function __construct( $args = array(
		'singular' => 'package',
		'plural'   => 'packages'
	) ) {
		$this->columns = [
			'cb' => '<input type="checkbox" />',
			'id' => __('ID', 'mail-boxes-etc'),
			'package_code' => __('Package code', 'mail-boxes-etc'),
			'package_label' => __('Package label', 'mail-boxes-etc'),
			'length' => __('Length', 'mail-boxes-etc'),
			'width' => __('Width', 'mail-boxes-etc'),
			'height' => __('Height', 'mail-boxes-etc'),
			'max_weight' => __( 'Maximum Package Weight', 'mail-boxes-etc' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')',
		];

		$this->sortable_columns = [
			'id'           => ['id', true],
			'package_code' => ['package_code'],
		];

		$this->csvType = 'packages';

		$this->default = [
			'id' =>'0' ,
			'max_weight' =>0 ,
			'length' =>0 ,
			'width' =>0 ,
			'height' => 0,
			'package_label' =>'' ,
			'package_code' =>'' ,
		];

		$this->tableName =  Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_TABLE_NAME;

		parent::__construct( $args );
	}

	public function validate_row($item) {
		$messages = array();

		if (empty(trim($item['package_label']))) $messages[] = __('Package label', 'mail-boxes-etc') . ' ' . __('required');
		if (empty(trim($item['package_code']))) $messages[] = __('Package code', 'mail-boxes-etc') . ' ' . __('required');
		if (empty(trim($item['max_weight']))) $messages[] = __('Maximum Package Weight', 'mail-boxes-etc') . ' ' . __('required');
		if (empty(trim($item['length']))) $messages[] = __('Length', 'mail-boxes-etc') . ' ' . __('required');
		if (empty(trim($item['width']))) $messages[] = __('Width') . ' ' . __('required');
		if (empty(trim($item['height']))) $messages[] = __('Height') . ' ' . __('required');

		if ($this->has_duplicates('package_code', $item['package_code'], '%s',$item['id'])) $messages[] = __('Package code', 'mail-boxes-etc') . ' ' . __('exists');
        if ($this->isReservedCode($item['package_code'])) $messages[] = __('Reserved package code cannot be used', 'mail-boxes-etc');

		if (empty($messages)) return true;
		return implode('<br />', $messages);
	}

	public function form_meta_box_handler( $item ) {
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
					<label for="package_label"><?php _e( 'Package label', 'mail-boxes-etc' ) ?></label>
				</th>
				<td>
					<input id="package_label" name="package_label" type="text" style="width: 95%"
					       value="<?php echo esc_attr( $item['package_label'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Package label', 'mail-boxes-etc' ) ?>"
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
                    <label for="length"><?php _e( 'Length', 'mail-boxes-etc' ) ?></label>
                </th>
                <td>
                    <input id="length" name="length" type="number" style="width: 95%"
                           value="<?php echo esc_attr( $item['length'] ) ?>"
                           size="50" class="code" placeholder="<?php _e( 'Length', 'mail-boxes-etc'  ) ?>"
                           required>
                </td>
            </tr>
			<tr class="form-field">
				<th scope="row">
					<label for="width"><?php _e( 'Width') ?></label>
				</th>
				<td>
					<input id="width" name="width" type="number" style="width: 95%"
					       value="<?php echo esc_attr( $item['width'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Width' ) ?>"
					       required>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="height"><?php _e( 'Height') ?></label>
				</th>
				<td>
					<input id="height" name="height" type="number" style="height: 95%"
					       value="<?php echo esc_attr( $item['height'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Height' ) ?>"
					       required>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="max_weight"><?php echo __( 'Maximum Package Weight', 'mail-boxes-etc' ) .' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')' ?></label>
				</th>
				<td>
					<input id="max_weight" name="max_weight" type="number" style="height: 95%"
					       value="<?php echo esc_attr( $item['max_weight'] ) ?>"
					       size="50" class="code" placeholder="<?php _e( 'Maximum Package Weight', 'mail-boxes-etc' ) ?>"
					       required>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}


}