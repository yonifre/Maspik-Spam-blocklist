<?php
/**
 * Main Wpforms validation functions file
 *
 */


/*
 * Check the form validtion.
*/ 

add_action('wpforms_process_before', function( $entry, $form_data ) {
  $error_message = cfas_get_error_text();
  $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  $spam = false;
  // ip
  $ip = efas_getRealIpAddr();
  $reason = "";
  // Country IP Check 
  $CountryCheck = CountryCheck($ip,$spam,$reason);
  $spam = $CountryCheck['spam'];
  $reason = $CountryCheck['reason'];
    
  // spampixel check
  if ( get_option('Maspik_human_verification') ) {
    if (false === get_transient('maspik_allow_' . $ip)) {
      $spam = true;
      $reason = "Maspik - human verification - IP: $ip";
    }
  }
    // CIDR Filter (Thanks to @josephcy95)
 
  // AbuseIPDB API  (Thanks to @josephcy95)
  $abuseipdb_api = get_option('abuseipdb_api') ? get_option('abuseipdb_api') : false;
  if (($abuseipdb_api != false) && ($spam != true)) {
    $abuseconfidencescore = check_abuseipdb($ip);
    $abuseipdbscore = (int)get_option('abuseipdb_score');
    if ($abuseconfidencescore >= $abuseipdbscore) {
      $spam = true;
      $reason = "AbuseIPDB Risk: $abuseconfidencescore";
    }
  }

  // Proxycheck.io Risk Check  (Thanks to @josephcy95)
  $proxycheck_io_api = get_option('proxycheck_io_api') ? get_option('proxycheck_io_api') : false;
  if (($proxycheck_io_api != false) && ($spam != true)) {
    $proxycheck_io_riskscore = check_proxycheckio($ip);
    $proxycheck_io_risk = (int)get_option('proxycheck_io_risk');
    if ($proxycheck_io_riskscore >= $proxycheck_io_risk) {
      $spam = true;
      $reason = "Proxycheck.io Risk: $proxycheck_io_riskscore";
    }
  }


    //If country or ip is in blacklist
  if ( $spam ) {
    update_option( 'spamcounter', ++$spamcounter );
    efas_add_to_log($type = "General",$reason, $_POST , "Wpforms" );
	wp_die('Your submission has been flagged as potential spam. Please contact the administrator for assistance.');
  }
  
  
}, 10, 2);


/*
 * Check the single line text field.
*/ 
add_action( 'wpforms_process_validate_text', 'cfas_validate_wpforms_text_name', 10, 3);
add_action( 'wpforms_process_validate_name', 'cfas_validate_wpforms_text_name', 10, 3);
function cfas_validate_wpforms_text_name( $field_id, $field_submit, $form_data ) {
    $field_submit = is_array($field_submit) ?  implode(" ",$field_submit) : $field_submit;
  	$field_value = strtolower($field_submit) ; 

    if ( empty( $field_value ) ) {
      return;
    }

	$spam = validateTextField($field_value);

    if($spam ) {
      $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
      $error_message = cfas_get_error_text();
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "text/name","$spam", $_POST, "Wpforms");          
      wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
      return;
    }
}


/*
 * Check the email field.
*/ 
add_action( 'wpforms_process_validate_email', function( $field_id, $field_submit, $form_data ) {
	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($field_submit); 
    if(!$field_value){
      return;
    }
	$spam = checkEmailForSpam($field_value);
    if( $spam) {
      $error_message = cfas_get_error_text();
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "email","Email $field_value is block $spam", $_POST, "Wpforms");
      wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
    }
}, 10, 3 );

/*
 * Check the phone field.
*/ 
add_action( 'wpforms_process_validate_phone', function( $field_id, $field_submit, $form_data ) {
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($field_submit); 
    if ( empty( $field_value ) ) {
        return false; // Not spam if the field is empty or no formats are provided.
    }
	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = $checkTelForSpam['reason'];      
 	$valid = $checkTelForSpam['valid'];   
   
    if(!$valid){
      	 $error_message = cfas_get_error_text();
         efas_add_to_log($type = "tel","Telephone number '$field_value' not feet the given format ", $_POST, "Wpforms");
         update_option( 'spamcounter', ++$spamcounter );
      	 wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
      }
}, 10, 3 );


/*
 * Check the textarea field.
*/ 
add_action( 'wpforms_process_validate_textarea', function( $field_id, $field_submit, $form_data ) {
  	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($field_submit); 

    if(!$field_value){
      return;
    }
  	$error_message = cfas_get_error_text();
    $textarea_blacklist = get_option( 'textarea_blacklist' ) ? efas_makeArray(get_option( 'textarea_blacklist' )) : array();

  	if ( efas_get_spam_api('textarea_field') ){
    	$blacklist_json = efas_get_spam_api('textarea_field') ;
      	$textarea_blacklist = array_merge($textarea_blacklist, $blacklist_json);
    }

  	foreach ($textarea_blacklist as $bad_string) {
        if($bad_string[0] === "[" ){ // check
          $search  = array('[', ']');
          $bad_string = str_replace($search, "", $bad_string);
          $bad_string = "url" || "name" || "description" ? get_bloginfo($bad_string) : "Error - Shortcode not exsist";
        }
        if(efas_is_field_value_exist_in_string($bad_string, $field_value)) {
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","field_value include: $bad_string |", $_POST);
          wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
        }
    }
	//  lang_needed
    $lang_needed = get_option( 'lang_needed' ) ? get_option( 'lang_needed' ) : array();
  	/*if ( efas_get_spam_api( 'lang_needed') ){
    	$lang_needed_api = efas_get_spam_api('lang_needed') ;
      	$lang_needed = array_merge($lang_needed, $lang_needed_api);
    }*/

    if( $lang_needed && !efas_is_lang($lang_needed,$field_value) ){
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","Needed language is missing ", $_POST, "Wpforms");
          wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
    }
  //  lang_forbidden
    $lang_forbidden = get_option( 'lang_forbidden' ) ? get_option( 'lang_forbidden' ) : array();
  	/*if ( efas_get_spam_api( 'lang_forbidden') ){
    	$lang_needed_api = efas_get_spam_api('lang_forbidden') ;
      	$lang_forbidden = array_merge($lang_forbidden, $lang_needed_api);
    }*/
    if( $lang_forbidden && efas_is_lang($lang_forbidden,$field_value) ){
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","Forbidden language is exists ", $_POST, "Wpforms");
          wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
    }
  
  	//contain_links (in right order)
  	$max_links_json =  is_array( efas_get_spam_api( 'contain_links') ) ? efas_get_spam_api( 'contain_links')[0] : false;

    $max_links =  get_option( 'contain_links' );// ?  get_option( 'contain_links' ) : $max_links_json;
    if ( $max_links ) {
      $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	  $num_links = preg_match_all( $reg_exUrl, $field_value );
		if ( $num_links >= $max_links ) {
              update_option( 'spamcounter', ++$spamcounter );
              efas_add_to_log($type = "textarea","contain more then  $max_links links", $_POST, "Wpforms");
	          wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
		}
	}

}, 10, 3 );


add_action('wpforms_display_submit_before', 'maspik_add_field_wpforms');
function maspik_add_field_wpforms($form_data) {
    if (!get_option('Maspik_human_verification')) {
        return;
    }
    $ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
    ?>
    <div class="maspik-captcha"></div>
    <style>
        .maspik-captcha {
            width: 1px;
            height: 1px;
        }
        form:focus-within .maspik-captcha {
            background-image: url('<?php echo esc_url($ajax_url); ?>');
            /* Add other background properties if necessary */
        }
    </style>
    <?php
}