<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Access the database
global $wpdb;

// Define the table name
$table_name = $wpdb->prefix . 'bookings';

// Delete the table
$wpdb->query("DROP TABLE IF EXISTS $table_name");