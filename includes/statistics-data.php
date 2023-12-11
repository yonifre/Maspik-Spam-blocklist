<?php

 // The file runing only is user accept it
// Construct the data to be sent in JSON format
$data = array(
  'field_name' => 'spamcounter',
  'field_value' => get_option('spamcounter')
);

// URL of the REST API endpoint on Site B
$api_url = 'https://wpmaspik.com/wp-json/statistics-maspik/v1/data';

// Send the POST request
$response = wp_remote_post( $api_url, array(
  'body' => json_encode( $data ),
  'headers' => array( 'Content-Type' => 'application/json' )
) );

// Check for errors and process the response
if ( is_wp_error( $response ) ) {
  echo 'Error sending data: ' . esc_html( $response->get_error_message() );
} else {
  $response_data = wp_remote_retrieve_body( $response ); // Response from Site B
  echo 'Response from DB Site: ' . esc_html( $response_data );
}