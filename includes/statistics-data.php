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
          "Elementor pro" => efas_if_plugin_is_affective('Elementor pro') ? 1 : 0,
          "Contact form 7" => efas_if_plugin_is_affective('Contact form 7') ? 1 : 0,
          "Woocommerce Review" => efas_if_plugin_is_affective('Woocommerce Review') ? 1 : 0,
          "Woocommerce Registration" => efas_if_plugin_is_affective('Woocommerce Registration') ? 1 : 0,
          "Wpforms" => efas_if_plugin_is_affective('Wpforms') ? 1 : 0,
          "Gravityforms" => efas_if_plugin_is_affective('Gravityforms') ? 1 : 0,
          "Wordpress Registration" => efas_if_plugin_is_affective('Wordpress Registration') ? 1 : 0,
          "Wordpress Comments" => efas_if_plugin_is_affective('Wordpress Comments') ? 1 : 0,
      ];

       // The file runing only is user accept it
      // Construct the data to be sent in JSON format
      $domain = $_SERVER['SERVER_NAME'];
      $proxycheck = get_option('proxycheck_io_risk') && get_option('proxycheck_io_api') ? "Use" : 0;
      $abuseipdb = get_option('abuseipdb_score') && get_option('abuseipdb_api') ? "Use" : 0;
      $data = array(
        'domain' => $domain,
        'wpversion' => get_bloginfo( 'version' ),
        'lang' => get_bloginfo( 'language' ),
        'counter' => get_option('spamcounter'),
        'publicAPI' => get_option('popular_spam'),
        'APIid' => get_option('private_file_id'),
        'NeedPageurl' => get_option('NeedPageurl'),
        'is_supporting' => cfes_is_supporting(),
        'popular_spam' => get_option('popular_spam'),
        'text_blacklist' => get_option('text_blacklist'),
        'MaxCharactersInTextField' => get_option('MaxCharactersInTextField'),
        'emails_blacklist' => get_option('emails_blacklist'),
        'textarea_blacklist' => get_option('textarea_blacklist'),
        'contain_links' => get_option('contain_links'),
        'tel_formats' => get_option('tel_formats'),
        'ip_blacklist' => get_option('ip_blacklist'),
        'popular_spam' => get_option('popular_spam'),
        'AllowedOrBlockCountries' => get_option('AllowedOrBlockCountries'),
        'country_blacklist' => get_option('country_blacklist'),
        'lang_forbidden' => get_option('lang_forbidden'),
        'lang_needed' => get_option('lang_needed'),
        'affective' => $affective,
        'proxycheck' => $proxycheck,
        'affective' => $abuseipdb,
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