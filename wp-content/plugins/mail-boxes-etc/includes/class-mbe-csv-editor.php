<?php

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Mbe_Shipping_Csv_Editor extends WP_List_Table
{
	protected $tableName;
	protected $title;
	protected $csvType;
	protected $default;
	protected $columns = [];
	protected $sortable_columns = [];


	public function __construct( $args = array() ) {
		global $wpdb;
		$this->tableName = $wpdb->prefix . $this->tableName;
		parent::__construct( $args );
	}

	/**
	 * Prepares the list of items for displaying.
	 */
	public function prepare_items() {
		global $wpdb;

		$this->_column_headers = [
			$this->get_columns(),
			array(),			// hidden
			$this->get_sortable_columns()
		];
		$this->process_bulk_action();
		$order = $this->get_items_query_order();

		$this->items = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$this->tableName} {$order}"
		)
			, ARRAY_A);

		$this->set_pagination_args([]);

	}

	public function get_tablename(){
		return $this->tableName;
	}

	public function get_title($singular = true){
		if($singular) {
			return ucwords(__($this->_args['singular'], 'mail-boxes-etc'));
		}
		return ucwords(__($this->_args['plural'], 'mail-boxes-etc'));
	}

	public function get_defaults()
	{
		return $this->default;
	}

	function get_columns()
	{
		return $this->columns;
	}

	function get_sortable_columns()
	{
		return $this->sortable_columns;
	}

	function get_bulk_actions()
	{
		return array(
			'delete' => 'Delete'
		);
	}

	function process_bulk_action()
	{
		global $wpdb;
		$table_name = $this->tableName;

		if ('delete' === $this->current_action()) {
			$ids = isset($_REQUEST['id']) ? (array)$_REQUEST['id'] : [];
			$ids = implode(',', $ids);

			if (!empty($ids)) {
				$wpdb->query("DELETE FROM $table_name WHERE id IN ( $ids )");
			}
		}
	}

	function column_default( $item, $column_name ) {
		echo $item[$column_name];
	}

	function column_id($item)
	{
		$actions = array(
			'edit' => sprintf( '<a href="?page=' . MBE_ESHIP_ID . '_csv_edit_form&csv=%s&id=%s">%s</a>', $_REQUEST['csv'], $item['id'], __('Edit', 'mail-boxes-etc')),
			'delete' => sprintf('<a href="?page=%s&action=delete&csv=%s&id=%s">%s</a>', $_REQUEST['page'], $_REQUEST['csv'], $item['id'], __('Delete', 'mail-boxes-etc')),
		);

		return sprintf('%s %s',
			$item['id'],
			$this->row_actions($actions)
		);
	}

	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item['id']
		);
	}

	protected function get_items_query_order() {
		if ( ! empty( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys($this->sortable_columns) ) ) {
			$by =esc_sql( wc_clean( $_REQUEST['orderby'] ));

			if ( ! empty( $_REQUEST['order'] ) && 'asc' === strtolower( $_REQUEST['order'] ) ) {
				$order = 'ASC';
			} else {
				$order = 'DESC';
			}

			return "ORDER BY {$by} {$order}";
		}
		return 'ORDER BY ID ASC';
	}

	protected function extra_tablenav( $which ) {
		static $has_items;

		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}

		echo '<div class="alignright actions">';

		if ( 'top' === $which ) {
			echo sprintf( '<a class="add-new-h2" href="?page=' . MBE_ESHIP_ID . '_csv_edit_form&action=new&csv=' . $this->csvType . '&id=%s">%s</a>', null, __('Add row', 'mail-boxes-etc'));
		}

		echo '</div>';
	}

	public function validate_row($item){}

	public function has_duplicates($field, $value, $type, $id = null)
	{
		global $wpdb;
		$idParam = !empty($id)?" AND id <> $id":'';
		$wpdb->get_results($wpdb->prepare("SELECT id FROM $this->tableName WHERE $field = $type $idParam", $value));
		return $wpdb->num_rows>0;
	}

	public function isReservedCode($value)
	{
		if($value === Mbe_Shipping_Helper_Data::MBE_CSV_PACKAGES_RESERVED_CODE) {
			return true;
		}
		return false;
	}

}