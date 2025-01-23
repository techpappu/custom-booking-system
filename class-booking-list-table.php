<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Booking_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => __('Booking', 'booking-system'),
            'plural'   => __('Bookings', 'booking-system'),
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        return [
            'cb'        => '<input type="checkbox" />',
            'booking_date' => __('Date', 'booking-system'),
            'time_slot' => __('Time Slot', 'booking-system'),
            'name'      => __('Name', 'booking-system'),
            'status'    => __('Status', 'booking-system'),
            'email'     => __('Email', 'booking-system'),
            'message'   => __('Message', 'booking-system'),
        ];
    }

    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="booking[]" value="%s" />', $item['id']);
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'booking_date':
            case 'time_slot':
            case 'name':
            case 'email':
            case 'message':
                return esc_html($item[$column_name]);
            case 'status':
                $status_class = 'status-' . strtolower($item['status']);
                return sprintf('<span class="%s">%s</span>', esc_attr($status_class), esc_html($item['status']));
            default:
                return print_r($item, true);
        }
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';

        $query = "SELECT * FROM $table_name ORDER BY booking_date, time_slot";
        $total_items = $wpdb->query($query);

        $this->_column_headers = array( 
            $this->get_columns(),		
            array(),			
            $this->get_sortable_columns(),	
       );

        $this->process_bulk_action();
        $per_page = 3;
        $current_page = $this->get_pagenum();
        $total_pages = ceil($total_items / $per_page);
        $data=$wpdb->get_results($query,ARRAY_A);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => $total_pages
        ]);
        $this->get_bulk_actions();
        $this->items = $data;
    }
    
    function get_bulk_actions()
    {
            $actions = array(
                    'pending' => __('change status to pending', 'booking-system'),
                    'approved' => __('change status to approved', 'booking-system'),
                    'complete' => __('change status to complete', 'booking-system'),
                    'hold' => __('change status to hold', 'booking-system'),
                    'Cancelled' => __('change status to Cancelled', 'booking-system'),
                    'Rejected' => __('change status to Rejected', 'booking-system'),
                    'in-progress' => __('change status to In Progress', 'booking-system'),
                    'delete_all'    => __('Delete', 'booking-system')
            );
            return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        $action = $this->current_action();
        $table_name = $wpdb->prefix . 'bookings';
        if ($action) {
            $booking_ids = isset($_REQUEST['booking']) ? array_map('intval', $_REQUEST['booking']) : [];
            if (!empty($booking_ids)) {
                switch ($action) {
                    case 'delete_all':
                        foreach ($booking_ids as $id) {
                            $wpdb->delete($table_name, ['id' => $id]);
                        }
                        break;
                    case 'approved':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'approved'], ['id' => $id]);
                        }
                        break;
                    case 'pending':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'pending'], ['id' => $id]);
                        }
                        break;
                    case 'complete':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'complete'], ['id' => $id]);
                        }
                        break;
                    case 'hold':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'hold'], ['id' => $id]);
                        }
                        break;
                    case 'Cancelled':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'Cancelled'], ['id' => $id]);
                        }
                        break;
                    case 'Rejected':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'Rejected'], ['id' => $id]);
                        }
                        break;
                    case 'in-progress':
                        foreach ($booking_ids as $id) {
                            $wpdb->update($table_name, ['status' => 'In Progress'], ['id' => $id]);
                        }
                        break;
                        
                }
            }
        }
        
    }
}