<?php
/**
 * Multi Row field.
 *
 * @var array $field Field data.
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

$field['value'] = empty( $field['value'] ) || ! is_array( $field['value'] ) ? array( false ) : $field['value'];
?>
<div class="wc-bogo-table-input">
	<table class="wc-bogo-table">
		<tbody>
			<tr>
			<?php foreach ( $field['fields'] as $_id => $_field ) : ?>
				<th><?php echo esc_html( $_field['label'] ); ?></td>
			<?php endforeach; ?>
				<th></th>
			</tr>
			<?php foreach ( $field['value'] as $row ) : ?>
			<tr class="row-input">
				<?php foreach ( $field['fields'] as $_field ) : ?>
				<td>
					<?php
					$_id             = $_field['id'];
					$_field['value'] = isset( $row[ $_id ] ) ? $row[ $_id ] : '';
					$_field['name']  = $_field['id'] . '[]';
					$_field['id']    = $_field['id'] . '_' . $_id;
					self::output_input( $_field );
					?>
				</td>
				<?php endforeach; ?>
				<td class="remove">
					<a class="wc-bogo-icon -minus remove-row" href="#"></a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<a class="button add-row" href="#">&plus;&nbsp;<?php echo esc_html( 'Add row', 'wc-buy-one-get-one-free' ); ?></a>
</div>
