<?php
/*
Plugin Name: User Info 
Description: Get the login user basic info
Version: 1.0
Author: Haider Zaman
Author URI: https://facebook.com/haideryousafzay
*/

// Function to create the custom table during plugin activation
function login_users_details_plugin_install() {
    global $wpdb, $charset_collate;
    $table_name = $wpdb->prefix . 'login_users_details';

    // Define the collation
    $charset_collate = $wpdb->get_charset_collate();

    // SQL query to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        IP VARCHAR(50) NOT NULL, 
        is_user_logged_in VARCHAR(3) NOT NULL, 
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// this function will be called when the user activates the plugin 
register_activation_hook(__FILE__, 'login_users_details_plugin_install');

// Function to log user IP and status on successful login
function login_users_details_log_login($user_login, $user) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'login_users_details';

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $is_user_logged_in = 'YES';

    $wpdb->insert($table_name, array(
        'IP' => $user_ip,
        'is_user_logged_in' => $is_user_logged_in,
    ));
}

add_action('wp_login', 'login_users_details_log_login', 10, 2);

// Function to log user IP and status on failed login
function login_users_details_log_failed_login($username) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'login_users_details'; // Prefix with WordPress database prefix

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $is_user_logged_in = 'NO';

    $wpdb->insert($table_name, array(
        'IP' => $user_ip,
        'is_user_logged_in' => $is_user_logged_in,
    ));
}
add_action('wp_login_failed', 'login_users_details_log_failed_login');




// Plugin main file
function my_plugin_menu() {
    // Add a top-level menu for your plugin
    add_menu_page(
        'My Plugin Menu',    
        'Login Users IP',      
        'manage_options', 
        'user_ip_info',
        'my_plugin_settings_page_callback', 
        'dashicons-admin-plugins',
        2 // Optional position of the menu item in the admin menu (5 is below Posts, 10 is below Media, etc.)
    );
}
add_action('admin_menu', 'my_plugin_menu');

// Callback function to render the settings page content
function my_plugin_settings_page_callback() {
    global $wpdb;

    // Retrieve data from the custom table
    $table_name = $wpdb->prefix . 'login_users_details';
    $data = $wpdb->get_results("SELECT * FROM $table_name");

    // Display the dashboard page content
    echo '<div class="wrap">';
    echo '<h1>Login Users Ip</h1>';

    // Check if there is data to display
    if (count($data) > 0) {
        // Create the table and table headers
        echo '<table class="widefat">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>IP Address</th>';
        echo '<th>Logged In</th>';
        echo '</tr></thead>';

        // Output the table rows with data
        echo '<tbody>';
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->IP) . '</td>';
            echo '<td>' . esc_html($row->is_user_logged_in) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';
    } else {
        // Display a message if there is no data
        echo '<p>No data found.</p>';
    }

    echo '</div>';
}

?>

