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

    if ( ! wp_next_scheduled( 'weekly_to_maspik_request' ) ) {
        // Schedule the event to run once a week
      	wp_clear_scheduled_hook( 'weekly_to_maspik_request' );
        wp_schedule_event( time(), "weekly", 'weekly_to_maspik_request' ); // weekly
    }
}
add_action( 'init', 'schedule_weekly_to_maspik_api_request' );
// Callback function for the scheduled event
function weekly_api_to_maspik_request_callback() {
    // Check if the transient exists or has expired
      $affective = [
          "Elementor pro" => efas_if_plugin_is_affective('Elementor pro', 0) ? 1 : 0,
          "Contact form 7" => efas_if_plugin_is_affective('Contact form 7', 0) ? 1 : 0,
          "Woocommerce Review" => efas_if_plugin_is_affective('Woocommerce Review', 0) ? 1 : 0,
          "Woocommerce Registration" => efas_if_plugin_is_affective('Woocommerce Registration', 0) ? 1 : 0,
          "Wpforms" => efas_if_plugin_is_affective('Wpforms', 0) ? 1 : 0,
          "Gravityforms" => efas_if_plugin_is_affective('Gravityforms', 0) ? 1 : 0,
          "Wordpress Registration" => efas_if_plugin_is_affective('Wordpress Registration', 0) ? 1 : 0,
          "Wordpress Comments" => efas_if_plugin_is_affective('Wordpress Comments', 0) ? 1 : 0,
      ];

       // The file runing only is user accept it
      // Construct the data to be sent in JSON format
      $domain = $_SERVER['SERVER_NAME'];
      $proxycheck = maspik_get_settings('proxycheck_io_risk') && maspik_get_settings('proxycheck_io_api') ? "Use" : 0;
      $abuseipdb = maspik_get_settings('abuseipdb_score') && maspik_get_settings('abuseipdb_api') ? "Use" : 0;
      $data = array(
        'domain' => $domain,
        'wpversion' => get_bloginfo( 'version' ),
        'lang' => get_bloginfo( 'language' ),
        'php' => bloginfo('version'),
        'maspikversion' => MASPIK_VERSION,
        'counter' => get_option('spamcounter'),
        'APIid' => maspik_get_settings('private_file_id'),
        'NeedPageurl' => maspik_get_settings('NeedPageurl'),
        'is_supporting' => cfes_is_supporting(),
        'popular_spam' => maspik_get_settings('popular_spam'),
        'text_blacklist' => maspik_get_settings('text_blacklist'),
        'MaxCharactersInTextField' => maspik_get_settings('MaxCharactersInTextField'),
        'emails_blacklist' => maspik_get_settings('emails_blacklist'),
        'textarea_blacklist' => maspik_get_settings('textarea_blacklist'),
        'contain_links' => maspik_get_settings('contain_links'),
        'tel_formats' => maspik_get_settings('tel_formats'),
        'ip_blacklist' => maspik_get_settings('ip_blacklist'),
        'popular_spam' => maspik_get_settings('popular_spam'),
        'AllowedOrBlockCountries' => maspik_get_settings('AllowedOrBlockCountries'),
        'country_blacklist' => maspik_get_settings('country_blacklist'),
        'lang_forbidden' => maspik_get_settings('lang_forbidden'),
        'lang_needed' => maspik_get_settings('lang_needed'),
        'lang_needed' => get_option('add_country_to_emails'),
        'lang_needed' => get_option('disable_comments'),
        'affective' => $affective,
        'proxycheck' => $proxycheck,
        'abuseipdb' => $abuseipdb,
      );

        // URL of the REST API endpoint on Site B
        $api_url = "https://wpmaspik.com/wp-json/statistics-maspik/v1/data?id=$domain&key=plugin";

        // Send the POST request
        $response = wp_remote_post(
            $api_url,
            array(
                'body'    => json_encode( $data ),
                'headers' => array( 'Content-Type' => 'application/json' ),
            )
        );

        // Check if the API call was successful
        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            // Set/update the transient to expire after a week
           // set_transient( 'weekly_api_request_transient', 'sent', 20 ); // WEEK_IN_SECONDS

        }
}
add_action( 'weekly_to_maspik_request', 'weekly_api_to_maspik_request_callback' );