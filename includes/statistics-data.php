<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/*
Statistics Data- If enabled  
*/


// Function to schedule the API request once a week
function schedule_weekly_to_maspik_api_request() {

    if ( ! wp_next_scheduled( 'weekly_to_r_maspik_request' ) ) {
        // Schedule the event to run once a week
      	wp_clear_scheduled_hook( 'weekly_to_r_maspik_request' );
        wp_schedule_event( time(), "weekly", 'weekly_to_r_maspik_request' ); // weekly 
    }
}
add_action( 'init', 'schedule_weekly_to_maspik_api_request' );
// Callback function for the scheduled event
function weekly_api_to_maspik_request_callback() {
    // Check if the transient exists or has expired
    $domain = $_SERVER['SERVER_NAME'];
    global $wpdb;
    $table_name = $wpdb->prefix . 'maspik_options';

    // Fetch all settings from the database
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    // Sanitize the data
    $data = array(); 

    foreach ($results as $setting) {
        $data[sanitize_text_field($setting['option_name'])] = sanitize_text_field($setting['option_value']);
    }

    // Add system information directly to the main $data array
    $data['wordpress_version'] = get_bloginfo('version');
    $data['plugin_version'] = MASPIK_VERSION; 
    $data['wordpress_language'] = get_bloginfo('language');
    $data['php_version'] = phpversion();
    $data['theme_name'] = wp_get_theme()->get('Name');
    $data['spamcounter'] = get_option('spamcounter');
    $data['maspik_api_requests'] = get_option('maspik_api_requests');

    
    // URL of the REST API endpoint
    $api_url = "https://receiver.wpmaspik.com/wp-json/statistics-maspik/v1/data?id=" . urlencode($domain) . "&key=plug1n";

    // Send the POST request
    $response = wp_remote_post(
        $api_url,
        array(
            'body'    => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
            'method'  => 'POST'
        )
    );

    // Check if the API call was successful
    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
        // Do nothing if work or if not
        //echo '<pre>'; print_r($data); echo '</pre>';
    }
}
add_action( 'weekly_to_r_maspik_request', 'weekly_api_to_maspik_request_callback' );//add_action( 'weekly_to_r_maspik_request', 'weekly_api_to_maspik_request_callback' );




////// export log

// Function to schedule the API request once a week for spam logs
function schedule_weekly_spam_logs_request() {

    if ( ! wp_next_scheduled( 'weekly_spam_logs_request' ) ) {
        // Schedule the event to run once a week
        wp_clear_scheduled_hook( 'weekly_spam_logs_request' );
        wp_schedule_event( time(), "weekly", 'weekly_spam_logs_request' ); // weekly
    }
}
add_action( 'init', 'schedule_weekly_spam_logs_request' );

// Callback function for the scheduled event
function weekly_spam_logs_request_callback() {
    global $wpdb;
    $domain = $_SERVER['SERVER_NAME'];
    $table_name = $wpdb->prefix . 'maspik_spam_logs';

    // Query to fetch the next 100 rows that have not been exported yet
    $results = $wpdb->get_results("
        SELECT 
            id, spam_type, spam_value, 
            IF(spam_tag = 'spam', spam_detail, '') AS spam_detail, 
            spam_ip, spam_country, spam_agent, 
            spam_date, spam_source, spamsrc_label, spamsrc_val, spam_tag 
        FROM 
            $table_name
        WHERE 
            spam_tag NOT LIKE '%exported%'
        ORDER BY 
            id ASC
        LIMIT 300
    ", ARRAY_A);
    if (empty($results)) {
        return; // No new data to send
    }

    // Sanitize the data
    $data = array(); 
    $ids_to_update = array();

    foreach ($results as $log) {
        $sanitized_log = array_map('sanitize_text_field', $log);
        $data[] = $sanitized_log;
        $ids_to_update[] = intval($log['id']);
    }

    // URL of the REST API endpoint
    $api_url = "https://receiver.wpmaspik.com/wp-json/statistics-maspik/v1/spam_logs?id=" . urlencode($domain) . "&key=plug1n";

    // Send the POST request
    $response = wp_remote_post(
        $api_url,
        array(
            'body'    => json_encode($data),
            'headers' => array('Content-Type' => 'application/json'),
            'method'  => 'POST'
        )
    );

    // Check if the API call was successful
    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
        // Update the spam_tag column to include "exported"
        if (!empty($ids_to_update)) {
            $ids_string = implode(',', $ids_to_update);
            $wpdb->query("
                UPDATE $table_name 
                SET spam_tag = CONCAT(spam_tag, ', exported') 
                WHERE id IN ($ids_string)
            ");
        }
    }
}
add_action( 'weekly_spam_logs_request', 'weekly_spam_logs_request_callback' );// weekly_spam_logs_request