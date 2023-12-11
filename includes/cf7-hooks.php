<?php
/*
* CF7 Hooks
*/

add_filter( 'wpcf7_validate', 'efas_wpcf7_validate_process' , 10, 2 );
function efas_wpcf7_validate_process ( $result, $tags ) {
  $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  $error_message = cfas_get_error_text();
  $reversed = array_reverse($tags);
  $spam = false;
  $tag = $tags ? new WPCF7_FormTag(  $reversed[1]  ) : null;
  $name = ! empty( $tag ) ? $tag->name : null;
  $id = $tag->get_id_option();
  $reason ="";
  // ip
  $ip =  efas_getRealIpAddr();
  
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
  
  // Maspik_human_verification check
  if (get_option('Maspik_human_verification') ) {
    if (false === get_transient('maspik_allow_' . $ip)) {
      $spam = true;
      $reason = "Maspik - Customized human verification";
    }
  }

  
  //If country or ip is in blacklist
  if ( $spam ) {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "Country/IP",$reason, $_POST, "Contact from 7" );
          $result['valid'] = false;
      	  $result->invalidate( $tag, $error_message.$countryName );
  	}
	return $result;
}


// Add custom validation for CF7 text fields
function efas_cf7_text_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
  	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($_POST[$name]); 
    if ( empty( $field_value ) ) {
      return $result;
    }
  
	$spam = validateTextField($field_value);
  
    if($spam ) {
      $error_message = cfas_get_error_text();
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "text","$spam", $_POST, "Contact from 7");          
      $result['valid'] = false;
      $result->invalidate( $tag, $error_message );
    }
    
	return $result;
}
add_filter('wpcf7_validate_text','efas_cf7_text_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_text*', 'efas_cf7_text_validation_filter', 10, 2); // Req. field

// Add custom validation for CF7 email fields
function efas_cf7_email_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
	$the_value = $_POST[$name];
  	$field_value = strtolower($the_value); 
    if ( empty( $field_value ) ) {
      return $result;
    }
	// check Email For Spam
	$spam = checkEmailForSpam($field_value);

   if( $spam ) {
  	  $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
      $error_message = cfas_get_error_text();
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "email","Email $field_value is block $spam" , $_POST, "Contact from 7");
      $result['valid'] = false;
      $result->invalidate( $tag, $error_message );
   }
   return $result;
}
add_filter('wpcf7_validate_email','efas_cf7_email_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_email*', 'efas_cf7_email_validation_filter', 10, 2); // Req. field


// Add custom validation for CF7 tel fields
function efas_cf7_tel_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
	$field_value = $_POST[$name];
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
    if ( empty( $field_value ) ) {
		return $result;
    }
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = $checkTelForSpam['reason'];      
 	$valid = $checkTelForSpam['valid'];   

  	if(!$valid){
        $error_message = cfas_get_error_text();  
        update_option( 'spamcounter', ++$spamcounter );
        efas_add_to_log($type = "tel","Telephone number $field_value not feet the given format ", $_POST, "Contact from 7");
        $result['valid'] = false;
        $result->invalidate( $tag, $error_message );
    } 

	return $result;
}
add_filter('wpcf7_validate_tel','efas_cf7_tel_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_tel*', 'efas_cf7_tel_validation_filter', 10, 2); // Req. field

// Add custom validation for CF7 textarea fields
function efas_cf7_textarea_validation_filter($result,$tag){
	$type = $tag['type'];
	$name = $tag['name'];
  	$field_value = strtolower( $_POST[$name] ) ; 
  	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
    if($field_value == "" || !$field_value ){
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
        efas_add_to_log($type = "textarea","field_value include $bad_string ", $_POST, "Contact from 7");
        $result['valid'] = false;
      	$result->invalidate( $tag, $error_message );
        return $result;
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
          efas_add_to_log($type = "textarea","Needed lang is missing ", $_POST , "Contact from 7");
          $result['valid'] = false;
          $result->invalidate( $tag, $error_message );
          return $result;
    }
  //  lang_forbidden
    $lang_forbidden = get_option( 'lang_forbidden' ) ? get_option( 'lang_forbidden' ) : array();
  	/*if ( efas_get_spam_api( 'lang_forbidden') ){
    	$lang_needed_api = efas_get_spam_api('lang_forbidden') ;
      	$lang_forbidden = array_merge($lang_forbidden, $lang_needed_api);
    }*/
    if( $lang_forbidden && efas_is_lang($lang_forbidden,$field_value) ){
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","Forbidden lang is exists ", $_POST, "Contact from 7");
          $result['valid'] = false;
          $result->invalidate( $tag, $error_message );
          return $result;
    }
  
  
  	//contain_links (in right order)
  	$max_links_json =  is_array( efas_get_spam_api( 'contain_links') ) ? efas_get_spam_api( 'contain_links')[0] : false;    
    $max_links =  get_option( 'contain_links' ) ;//?  get_option( 'contain_links' ) : $max_links_json;
    if ( $max_links ) {
      $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	  $num_links = preg_match_all( $reg_exUrl, $field_value );
		if ( $num_links >= $max_links ) {
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "textarea","contain more then  $max_links links ", $_POST, "Contact from 7");
          $result['valid'] = false;
      	  $result->invalidate( $tag, $error_message );
          return $result;
		}
	}
  
	return $result;
}
add_filter('wpcf7_validate_textarea','efas_cf7_textarea_validation_filter', 10, 2); // Normal field
add_filter('wpcf7_validate_textarea*', 'efas_cf7_textarea_validation_filter', 10, 2); // Req. field