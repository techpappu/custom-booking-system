<?php
/**
 * Plugin Name:     Custom Booking System
 * Description:     A simple booking system with date, time slots, and booking form.
 * Version:         1.0
 * Author:          Md. Saiduzzaman
 * Author URI:      https://saiduzzaman.stylebari.com/
 * Text Domain:     booking-system
 */

if (!defined('ABSPATH')) {
    exit;
}
require_once plugin_dir_path(__FILE__) . 'class-booking-list-table.php';

class CustomBookingSystem {
    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'create_database_table']);
        // Register shortcode
        add_shortcode('custom_booking_system', [$this, 'render_booking_shortcode']);
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // AJAX handlers for fetching time slots and processing bookings
        add_action('wp_ajax_get_time_slots', [$this, 'get_time_slots']);
        add_action('wp_ajax_nopriv_get_time_slots', [$this, 'get_time_slots']);
        add_action('wp_ajax_process_booking', [$this, 'process_booking']);
        add_action('wp_ajax_nopriv_process_booking', [$this, 'process_booking']);

        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Hook the enqueue function for admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    // Create database table for bookings
    public function create_database_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            booking_date DATE NOT NULL,
            time_slot VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Enqueue scripts and styles
    public function enqueue_scripts() {
        wp_enqueue_script('custom-booking-script', plugin_dir_url(__FILE__) . 'booking.js', ['jquery'], '1.0', true);
        wp_localize_script('custom-booking-script', 'bookingAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_style('custom-booking-style', plugin_dir_url(__FILE__) . 'admin-booking.css', [], '1.0');
        
    }

    // Enqueue admin styles
    public function enqueue_admin_styles($hook) {
        wp_enqueue_style('custom-booking-admin-style', plugin_dir_url(__FILE__) . 'admin-booking.css', [], '1.0');
    }



    // Render booking shortcode
    public function render_booking_shortcode() {
        ob_start();
        ?>
        <div id="booking-system">
            <label for="booking-date">Select a date:</label>
            <input type="date" id="booking-date">

            <div id="time-slots" style="margin-top: 20px;"></div>

            <div id="booking-form" style="display: none; margin-top: 20px;">
                <h3>Booking Form</h3>
                <form id="booking-form-element">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required><br>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required><br>
                    <label for="message">Message:</label>
                    <textarea id="message" name="message"></textarea><br>
                    <input type="hidden" id="selected-time-slot" name="time_slot">
                    <button type="submit">Book Now</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // AJAX handler for fetching available time slots
    public function get_time_slots() {
        global $wpdb;
        $date = sanitize_text_field($_POST['date']);
        $table_name = $wpdb->prefix . 'bookings';

        $time_slots = [
            '09:00 AM - 10:00 AM',
            '10:00 AM - 11:00 AM',
            '11:00 AM - 12:00 PM',
            '12:00 PM - 01:00 PM',
        ];

        $available_slots = [];

        foreach ($time_slots as $slot) {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE booking_date = %s AND time_slot = %s", $date, $slot));
            if ($count < 2) {
                $available_slots[] = $slot;
            }
        }

        wp_send_json($available_slots);
    }

    // AJAX handler for processing bookings
    public function process_booking() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bookings';

        $date = sanitize_text_field($_POST['date']);
        $time_slot = sanitize_text_field($_POST['time_slot']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE booking_date = %s AND time_slot = %s", $date, $time_slot));

        if ($count >= 2) {
            wp_send_json_error(['message' => 'This time slot is fully booked.']);
        }

        $wpdb->insert($table_name, [
            'booking_date' => $date,
            'time_slot' => $time_slot,
            'name' => $name,
            'email' => $email,
            'message' => $message,
        ]);

        wp_send_json_success(['message' => 'Booking successful!']);
    }

    // Admin menu
    public function add_admin_menu() {
        add_menu_page('Booking System', 'Bookings', 'manage_options', 'custom-booking-system', [$this, 'admin_page']);
    }
    public function admin_page() {
        $bookingListTable = new Booking_List_Table();
        $bookingListTable->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php _e('Bookings', 'booking-system'); ?></h1>
            <form method="post">
                <?php $bookingListTable->display(); ?>
            </form>
        </div>
        <?php
    }
}


new CustomBookingSystem();