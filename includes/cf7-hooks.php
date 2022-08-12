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

  // ip
  $ip = efas_getRealIpAddr();
  $ip_blacklist =  get_option( 'ip_blacklist' ) ? efas_makeArray( get_option( 'ip_blacklist' ) ) : array();
  // country
  $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$ip);
  $countryCode = $xml ? $xml->geoplugin_countryCode : false ;
  $country_blacklist =  get_option( 'country_blacklist' ) ? efas_makeArray(get_option( 'country_blacklist')) : array();
  $AllowedOrBlockCountries = get_option( 'AllowedOrBlockCountries' ) == "allow" ? "allow" : "block" ;            
  
  if ( is_array( efas_get_spam_api("ip") ) ){
    $ip_blacklist_api =  efas_get_spam_api("ip")  ;
    $ip_blacklist = array_merge($ip_blacklist, $ip_blacklist_api);
  }  
  /*if ( efas_get_spam_api("countries") ){
    $countries_blacklist_api =  efas_get_spam_api("countries")  ;
   // disable countries API
	// $country_blacklist = array_merge($country_blacklist, $countries_blacklist_api);
  }  */

  if (in_array($countryCode , $country_blacklist ) ) {
    $spam = true;
    $reason = "Country code $countryCode is blacked";

  }
  if($AllowedOrBlockCountries == 'allow' &&  in_array($countryCode , $country_blacklist ) ) {
    $spam = false;
  }
  if($AllowedOrBlockCountries == 'allow' &&  !in_array($countryCode , $country_blacklist ) ) {
    $spam = true;
    $reason = "Country $countryCode is not in the whitelist";
  }

  if ( in_array($ip , $ip_blacklist ) ) {
    $spam = true;
    $reason = "IP $ip is blacked";
  }

  
  // CIDR Filter (Thanks to @josephcy95)
  if($spam != true){
    foreach ($ip_blacklist as $cidr){
      if( ip_is_cidr($cidr) ){
        if (cidr_match($ip, $cidr)){
          $spam = true;
          $reason = "IP is in CIDR: $cidr";
          break;
        }
      }
    }
  }
 
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
    if(!$field_value){
      return $result;
    }
  	$error_message = cfas_get_error_text();
    $text_blacklist = get_option( 'text_blacklist' ) ? efas_makeArray(get_option('text_blacklist') ) : array('eric jones');
  	
	if ( efas_get_spam_api() ){
    	$text_blacklist_json =  efas_get_spam_api() ;
      	$text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
    }  

     if( is_array($text_blacklist) ){
       foreach ($text_blacklist as $bad_string) {
        if( efas_is_field_value_equwl_to_string($bad_string, $field_value) ) {
          update_option( 'spamcounter', ++$spamcounter );
           efas_add_to_log($type = "text",$field_value, $_POST, "Contact from 7");
          $result['valid'] = false;
      	  $result->invalidate( $tag, $error_message );
   		break;
        }
       }
    }

	$MaxCharacters_API = false;
  	if ( efas_get_spam_api('MaxCharactersInTextField') ){
    	$MaxCharacters_API = efas_get_spam_api('MaxCharactersInTextField')[0];
    }
    $CountCharacters = strlen($field_value);
    $MaxCharacters = get_option( 'MaxCharactersInTextField' ) ? get_option( 'MaxCharactersInTextField' ) : $MaxCharactersInTextField ;
	if( $MaxCharacters && $CountCharacters ){
        if($MaxCharacters < $CountCharacters ) {
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "text","More then $MaxCharacters characters", $_POST, "Contact from 7");          
          $result['valid'] = false;
      	  $result->invalidate( $tag, $error_message );
        }
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
	
  	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($the_value); 
    if(!$field_value){
      return $result;;
    }
  	$error_message = cfas_get_error_text();
    $text_blacklist =  efas_makeArray( get_option('emails_blacklist') ) ;

  	if ( efas_get_spam_api('email_field') ){
    	$blacklist_json = efas_get_spam_api('email_field') ;
      	$text_blacklist = array_merge($text_blacklist, $blacklist_json);
    }
  
    $spam = false;
    foreach ($text_blacklist as $bad_string) {         
      if($bad_string[0] === "/" ){ // check
        if ( preg_match( $bad_string, $field_value ) ) {
          $spam = true;
        }
      }
      $spam = efas_is_field_value_equwl_to_string($bad_string, $field_value) ? true : $spam ;
      if($spam){
      	break;
      }
   }
   
   $spam = cfes_is_spam_email_domain($field_value,$text_blacklist) ? true : $spam;

   if( $spam ) {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "email",$field_value , $_POST, "Contact from 7");
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
    $tel_formats = get_option( 'tel_formats' );
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
    if($field_value == "" || !$field_value || !$tel_formats){
		return $result;
    }
    $tel_formats = explode( "\n", str_replace("\r", "", $tel_formats) );
  	if ( efas_get_spam_api('tel_formats') ){
    	$blacklist_json = efas_get_spam_api('tel_formats') ;
      	$tel_formats = array_merge($tel_formats, $blacklist_json);
    }

  	$error_message = cfas_get_error_text();
    $valid = true;
    if( is_array($tel_formats) ){
      $valid = false;
      foreach ($tel_formats as $format) {
        if ( preg_match( $format, $field_value ) ) {
          $valid = true;
          break;
        }
      }
      if(!$valid){
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "tel","Telephone number $field_value not feet the format $format ", $_POST, "Contact from 7");
          $result['valid'] = false;
      	  $result->invalidate( $tag, $error_message );
      }
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