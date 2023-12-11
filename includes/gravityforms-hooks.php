<?php
//


//general
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
  GFCommon::log_debug( __METHOD__ . '(): Running...' );
  $count = count($form['fields']) ;
  $reason = "";
  $spam = false;
  if($field['id'] == $count){
  
    if ( !$result['is_valid'] ) {
      return $result;
    }

    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
    $error_message = cfas_get_error_text();

    // ip
    $ip = efas_getRealIpAddr();
    
  	// Country IP Check 
    $CountryCheck = CountryCheck($ip,$spam,$reason);
    $spam = $CountryCheck['spam'];
    $reason = $CountryCheck['reason'];

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

    // spampixel check
    if (get_option('Maspik_human_verification') ) {
      if (false === get_transient('maspik_allow_' . $ip)) {
        $spam = true;
        $reason = "Maspik - Customized human verification";
      }
    }

      //If country or ip is in blacklist
    if ( $spam ) {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "General",$reason, $_POST ,'gravityforms');
      GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $reason );
      $result['is_valid'] = false;
      $result['message'] = $error_message;
    }
  }
  return $result;
}, 10, 4 );


//text
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single Line Text.
  if ( $field->type == 'text' || $field->type == 'name' ) {
      $field_value = is_array($value) ?  implode(" ",$value) : $value;
      if ( !$result['is_valid'] ||  empty( $field_value ) ) {
          return $result;
      }
      $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
      $field_value = strtolower($field_value); 

      $spam = validateTextField($field_value);

      if( $spam ) {
          $error_message = cfas_get_error_text();
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "text",$spam, $_POST,'gravityforms');          
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $field_value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
      }
  }     // end $field->type == 'text' || $field->type == 'name'

    return $result;
}, 10, 4 );

//email
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for email
  if ( $field->type == 'email' ) {
    if ( !$result['is_valid'] || empty( $value ) ) {
        return $result;
    }
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($value); 
	$spam = checkEmailForSpam($field_value);

    if( $spam ) {
        $error_message = cfas_get_error_text();
        update_option( 'spamcounter', ++$spamcounter );
        efas_add_to_log($type = "email","Email $field_value is block $spam", $_POST, "GravityForms");
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
  	}

  }
    return $result;
}, 10, 4 );

//phone
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single phone.
  if ( $field->type == 'phone' && $field->phoneFormat != 'standard' ) {
      if ( !$result['is_valid'] || empty( $value ) ) {
        return $result;
      }
      $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
      $field_value = strtolower($value); 
      $checkTelForSpam = checkTelForSpam($field_value);
      $reason = $checkTelForSpam['reason'];      
      $valid = $checkTelForSpam['valid'];   

      if(!$valid){
        $error_message = cfas_get_error_text();
        efas_add_to_log($type = "tel","Phone number $field_value not feet the given format ", $_POST, "gravityforms");
        update_option( 'spamcounter', ++$spamcounter );
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
      }
  }
  return $result;
}, 10, 4 );

//textarea    
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Paragraph fields.
  if ( $field->type == 'textarea' ) {
        $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  		$field_value = strtolower($value); 
    
      if ( !$result['is_valid']  || !$field_value) {
        return $result;
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
          efas_add_to_log($type = "textarea","field_value include: $bad_string |", $_POST, "gravityforms");
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
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
          efas_add_to_log($type = "textarea","Needed language is missing ", $_POST, "gravityforms");
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
    }
  //  lang_forbidden
    $lang_forbidden = get_option( 'lang_forbidden' ) ? get_option( 'lang_forbidden' ) : array();
  	/*if ( efas_get_spam_api( 'lang_forbidden') ){
    	$lang_needed_api = efas_get_spam_api('lang_forbidden') ;
      	$lang_forbidden = array_merge($lang_forbidden, $lang_needed_api);
    }*/

    if( $lang_forbidden && efas_is_lang($lang_forbidden,$field_value) ){
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","Forbidden language is exists ", $_POST, "gravityforms");
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
    }
  
  	//contain_links (in right order)
  	$max_links_json =  is_array( efas_get_spam_api( 'contain_links') ) ? efas_get_spam_api( 'contain_links')[0] : false;

    $max_links =  get_option( 'contain_links' );// ?  get_option( 'contain_links' ) : $max_links_json;
    if ( $max_links ) {
      $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	  $num_links = preg_match_all( $reg_exUrl, $field_value );
		if ( $num_links >= $max_links ) {
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","contain more then  $max_links links", $_POST, "gravityforms");
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
		}
    }        
 
  }
    return $result;
}, 10, 4 );


add_filter('gform_submit_button', 'maspik_add_spampixel_to_gravity_form', 10 , 2 );
function maspik_add_spampixel_to_gravity_form($button, $form) {
    if (!get_option('Maspik_human_verification')) {
        return $button;
    }
    $ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
    $spampixel = '<div class="maspik-captcha"></div>';
    $spampixel .=
        "<style>
            .maspik-captcha {
                width: 1px;
                height: 1px;
                position: absolute;
                opacity: 0;
            }
            form:focus-within .maspik-captcha {
                background-image: url('$ajax_url');
            }
        </style>";

    return $button.$spampixel;

}