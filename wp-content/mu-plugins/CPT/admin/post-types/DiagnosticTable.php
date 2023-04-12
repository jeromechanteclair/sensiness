<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class DiagnosticTable extends WP_List_Table
{

    function __construct(){
        global $status, $page;                

        parent::__construct( array(
            'singular'  => 'diagnostic_option',  
            'plural'    => 'diagnostic_options',   
            'ajax'      => false      
        ) );        
    }
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
   
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
    /**
     * 
     */
    public function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'email'     => 'Email'
        );
        return $actions;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'id'          => 'ID',
            'email'       => 'email',
            'gender' => 'Genre',
            'birthdate'        => 'Anniversaire',
            'texture'    => 'Texture',
            'etat_cheveux'    => 'Etat',
            'coupe'    => 'coupe',
            'protection'    => 'protection',
            'hydratation'    => 'hydratation',
            'manipulation'    => 'manipulation',
            'shampoing'    => 'shampoing',
            'objectif'    => 'objectif',
          
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('id' => array('id', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {

        global $wpdb;
        $table = $wpdb->prefix."diagnostic_results";
        $datas = $wpdb->get_results ( "
          SELECT * 
          FROM   $table
             
      ", ARRAY_A );
    


        return $datas;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
            case 'email':
            case 'gender':
            case 'birthdate':
            case 'texture':
            case 'etat_cheveux':
            case 'coupe':
            case 'protection':
            case 'hydratation':
            case 'manipulation':
            case 'shampoing':
            case 'objectif':

            $regex = '/^(a|b|c)\-/';
            //  replace column name with $regex
            $col = preg_replace($regex, ' ', $item[ $column_name ]);
            return $item[ $column_name ] =  $col;

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'id';
        $order = 'DESC';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}