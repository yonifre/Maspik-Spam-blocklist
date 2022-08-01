<?php

function maspik_comments_checker( array $data ){
  $content = strtolower($data['comment_content']); 
  $email = strtolower($data['comment_author_email']); 
  $name = strtolower($data['comment_author']); 
  $comment_type = $data['comment_type'] ; 
  $run = false;
  if( get_option( "maspik_support_wp_comment" ) != "no" && $comment_type == 'comment'){
      $run = "ok";
  }else if( get_option( "maspik_support_woocommerce_review" )  != "no" && $comment_type == 'review'){
  	  $run = "ok";
  }
  if(!$run){
    return $data;
  }

  
  $spam = false;
  $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  $error_message = cfas_get_error_text();
  // ip
  $ip = efas_getRealIpAddr();
  $ip_blacklist =  get_option( 'ip_blacklist' ) ? efas_makeArray( get_option( 'ip_blacklist' ) ) : array();
  // country
  $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$ip);
  $countryCode = $xml ? $xml->geoplugin_countryCode : false ;
  $country_blacklist =  get_option( 'country_blacklist' ) ? efas_makeArray(get_option( 'country_blacklist')) : array();
  $AllowedOrBlockCountries = get_option( 'AllowedOrBlockCountries' ) == "allow" ? "allow" : "block" ;            
  $reason = false;
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
  
  if( !empty($name) ){   
      $text_blacklist = get_option( 'text_blacklist' ) ? efas_makeArray(get_option('text_blacklist') ) : array('eric jones');
      if ( efas_get_spam_api() ){
          $text_blacklist_json =  efas_get_spam_api();
          $text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
      }  
      if( is_array($text_blacklist) ){
         foreach($text_blacklist as $bad_string) {
            if( efas_is_field_value_equwl_to_string($bad_string, $name) ) {
              $reason = "Name = $name ";
				$spam = true;
            }
         }
      }
  } // end Name

  if( !empty($email) ){   
      $text_blacklist = efas_makeArray( get_option( 'emails_blacklist' ) );
      if ( efas_get_spam_api('email_field') ){
          $blacklist_json = efas_get_spam_api('email_field') ;
          $text_blacklist = array_merge($text_blacklist, $blacklist_json);
      }
      foreach ($text_blacklist as $bad_string) {         
        if($bad_string[0] === "/" ){ // check
          if ( preg_match( $bad_string, $email ) ) {
            $spam = true;
            $reason = "Email = $email ";
          }
        }
        $spam = efas_is_field_value_equwl_to_string($bad_string, $email) ? true : $spam ;
     }
    // check if spam email-domain enter, like: @xyz.com , @gmail.com ...
    $spam = cfes_is_spam_email_domain($email,$text_blacklist)  ? true : $spam;
    if(!$reason){
		$reason = "Email = $email";
    }
  } 
  // $content
  if( !empty($content) ){   
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
      if(strpos($content, $bad_string) !== false) {
        $spam = true;
        $reason = "Field value include: $bad_string |";
        break;
      }
    }


	//  lang_needed
    $lang_needed = get_option( 'lang_needed' ) ? get_option( 'lang_needed' ) : array();
  	/*if ( efas_get_spam_api( 'lang_needed') ){
    	$lang_needed_api = efas_get_spam_api('lang_needed') ;
      	$lang_needed = array_merge($lang_needed, $lang_needed_api);
    }*/

    if( $lang_needed && !efas_is_lang($lang_needed,$content) ){
      $spam = true;
      $reason = "Needed language is missing";
    }
  //  lang_forbidden
    $lang_forbidden = get_option( 'lang_forbidden' ) ? get_option( 'lang_forbidden' ) : array();
  	/*if ( efas_get_spam_api( 'lang_forbidden') ){
    	$lang_needed_api = efas_get_spam_api('lang_forbidden') ;
      	$lang_forbidden = array_merge($lang_forbidden, $lang_needed_api);
    }*/
    if( $lang_forbidden && efas_is_lang($lang_forbidden,$content) ){
      $spam = true;
      $reason = "Forbidden language is exists";
    }
  
  	//contain_links (in right order)
  	$max_links_json =  is_array( efas_get_spam_api( 'contain_links') ) ? efas_get_spam_api( 'contain_links')[0] : false;

    $max_links =  get_option( 'contain_links' );// ?  get_option( 'contain_links' ) : $max_links_json;
    if ( $max_links ) {
      $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	  $num_links = preg_match_all( $reg_exUrl, $content );
		if ( $num_links >= $max_links ) {
            $spam = true;
            $reason = "Contain more then  $max_links links";
		}
	}
  } // end $content

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
  
  
  if($spam){
    $message = __( 'ERROR: Sorry, look like a spam - maspik.' , 'contact-forms-anti-spam' );
    $args = array('response' => 200);
    update_option( 'spamcounter', ++$spamcounter );
    efas_add_to_log($type = "Comments",$reason, $data,$comment_type );
    wp_die( $message, $title="Spam error",  $args );
    exit(0);
  }
  
  return $data;
} 

add_filter('preprocess_comment','maspik_comments_checker');



/*
Wp registration
*/
function maspik_check_wp_registration_form( $errors ) {

  //$sanitized_user_login = array_key_exists('user_login',$_POST) ? $_POST['user_login'] : $_POST['user'] ;
  $user_email = array_key_exists('user_email',$_POST) ? $_POST['user_email'] : $_POST['email'];
  $spam = false;
  $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  $error_message = cfas_get_error_text();
  // ip
  $ip = efas_getRealIpAddr();
  $ip_blacklist =  get_option( 'ip_blacklist' ) ? efas_makeArray( get_option( 'ip_blacklist' ) ) : array();
  // country
  $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$ip);
  $countryCode = $xml ? $xml->geoplugin_countryCode : false ;
  $country_blacklist =  get_option( 'country_blacklist' ) ? efas_makeArray(get_option( 'country_blacklist')) : array();
  $AllowedOrBlockCountries = get_option( 'AllowedOrBlockCountries' ) == "allow" ? "allow" : "block" ;            
  $reason = false;
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
    $reason = false;
  }
  if($AllowedOrBlockCountries == 'allow' &&  !in_array($countryCode , $country_blacklist ) ) {
    $spam = true;
    $reason = "Country $countryCode is not in the whitelist";
  }

  if ( in_array($ip , $ip_blacklist ) ) {
    $spam = true;
    $reason = "IP $ip is blacked";
  }
  // Email
    if ( $user_email ) {
          $text_blacklist = efas_makeArray( get_option( 'emails_blacklist' ) );
          if ( efas_get_spam_api('email_field') ){
              $blacklist_json = efas_get_spam_api('email_field') ;
              $text_blacklist = array_merge($text_blacklist, $blacklist_json);
          }
          foreach ($text_blacklist as $bad_string) {         
            if($bad_string[0] === "/" ){ // check
              if ( preg_match( $bad_string, $user_email ) ) {
                $spam = true;
                $reason = "Email = $user_email ";
              }
            }
            $spam = efas_is_field_value_equwl_to_string($bad_string, $user_email) ? true : $spam ;
         }
        // check if spam email-domain enter, like: @xyz.com , @gmail.com ...
        $spam = cfes_is_spam_email_domain($user_email,$text_blacklist)  ? true : $spam;
        if($spam && !$reason){
          $reason = "Email = $user_email ";
        }
    }
    if($spam && array_key_exists('register',$_POST) && get_option( "maspik_support_Woocommerce_registration") != "no" ) {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "Registration",$reason, $_POST,'Woocommerce registration' );
      wc_add_notice( __( 'ERROR: Look like spam.' ), 'contact-forms-anti-spam' );
      return array();
    } else if($spam && array_key_exists('wp-submit',$_POST) && get_option( "maspik_support_registration") != "no") {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "Registration",$reason, $_POST ,'Wp registration');
      $errors->add( 'Maspik_error', __( 'ERROR: Look like spam.', 'contact-forms-anti-spam' ) );
    }

    return $errors;
}

add_filter( 'registration_errors', 'maspik_check_wp_registration_form', 10, 1 );
add_filter('woocommerce_new_customer_data', 'maspik_check_wp_registration_form', 10, 1 );
//add_action('wp_footer', function(){echo "dd";});