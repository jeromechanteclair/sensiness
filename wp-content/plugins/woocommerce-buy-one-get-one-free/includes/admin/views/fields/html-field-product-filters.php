<?php
/**
 * Product filter field.
 *
 * @var array $field Field data.
 * @package WC_BOGOF
 */

defined( 'ABSPATH' ) || exit;

$filters = ! empty( $field['value'][0] ) ? $field['value'][0] : array(
	array(
		'type' => 'all_products',
	),
);
?>

<div class="wc-bogo-table-input" id="<?php echo esc_attr( $field['id'] ); ?>">
	<table class="wc-bogo-table">
		<tbody>
			<?php foreach ( $filters as $row_index => $filter ) : ?>
				<?php include dirname( __FILE__ ) . '/html-product-filters-table-row.php'; // phpcs:ignore ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<a class="button add-row" href="wc-bogo-product-filter-<?php echo esc_attr( $field['id'] ); ?>">&plus;&nbsp;<?php esc_html_e( 'Add condition', 'wc-buy-one-get-one-free' ); ?></a>
</div>
<script type="text/html" id="tmpl-wc-bogo-product-filter-<?php echo esc_attr( $field['id'] ); ?>">
<?php
$row_index = '{{{data.rowId}}}';
$filter    = array(
	'type'     => false,
	'modifier' => false,
	'value'    => false,
);
include dirname( __FILE__ ) . '/html-product-filters-table-row.php'; // phpcs:ignore
?>
</script>
