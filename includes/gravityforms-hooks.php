<?php
//


//general
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
  GFCommon::log_debug( __METHOD__ . '(): Running...' );
  $count = count($form['fields']) ;
  if($field['id'] == $count){
  
    if ( !$result['is_valid'] ) {
      return $result;
    }


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
      $reason = "Country $countryCode is not in the whitelist".print_r($country_blacklist,1);
    }
    if ( in_array($ip , $ip_blacklist ) ) {
      $spam = true;
      $reason = "IP $ip is blacked";
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
    if ( !$result['is_valid'] || !$value ) {
        return $result;
    }

    // stert
    if($field->type == 'name' && rgar( $value, $field->id . '.3' ) ) {
          $value  = rgar( $value, $field->id . '.3' );
    }
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($value); 
  	$error_message = cfas_get_error_text();
    $text_blacklist = get_option( 'text_blacklist' ) ? efas_makeArray(get_option('text_blacklist') ) : array('eric jones');
  	if ( efas_get_spam_api() ){
    	$text_blacklist_json =  efas_get_spam_api();
      	$text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
    }  
	if( is_array($text_blacklist) ){
       foreach($text_blacklist as $bad_string) {
          if( efas_is_field_value_equwl_to_string($bad_string, $field_value) ) {
            $reason = "Input = $field_value ";
            update_option( 'spamcounter', ++$spamcounter );
            efas_add_to_log($type = "text",$reason, $_POST ,'gravityforms');
            GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
            $result['is_valid'] = false;
            $result['message']  = $error_message;

          }
       }
    }
  
  // Max Characters In Text Field in the rgiht order
	$MaxCharacters_API = false;
  	if ( efas_get_spam_api('MaxCharactersInTextField') ){
    	$MaxCharacters_API = efas_get_spam_api('MaxCharactersInTextField')[0];
    }
    $MaxCharacters = get_option( 'MaxCharactersInTextField' ) ? get_option( 'MaxCharactersInTextField' ) : $MaxCharactersInTextField ;
    $CountCharacters = strlen($field_value);
	if( $MaxCharacters && $CountCharacters ){
        if($MaxCharacters < $CountCharacters ) {
          update_option( 'spamcounter', ++$spamcounter );
          efas_add_to_log($type = "text","More then $MaxCharacters characters", $_POST,'gravityforms');          
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;

        }
    }     
    // end
  }
    return $result;
}, 10, 4 );

//email
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single Line Text.
  if ( $field->type == 'email' ) {
    if ( !$result['is_valid'] || !$value ) {
        return $result;
    }
    // stert
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($value); 
  	$error_message = cfas_get_error_text();
    $text_blacklist = efas_makeArray( get_option( 'emails_blacklist' ) );
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
	// check if spam email-domain enter, like: @xyz.com , @gmail.com ...
  	$spam = cfes_is_spam_email_domain($field_value,$text_blacklist)  ? true : $spam;

    if( $spam) {
      update_option( 'spamcounter', ++$spamcounter );
      efas_add_to_log($type = "email",$field_value, $_POST, "gravityforms");
	  $result['is_valid'] = false;
      $result['message']  = $error_message;    
  	}
    // end
  }
    return $result;
}, 10, 4 );

//phone
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single phone.
  if ( $field->type == 'phone' && $field->phoneFormat != 'standard' ) {
    if ( !$result['is_valid'] || !$value ) {
        return $result;
    }
    //start
    $spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
  	$field_value = strtolower($value); 
    $tel_formats = get_option( 'tel_formats' );
    if( !$tel_formats ){
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
        // Match this format XXX-XXX-XXXX, 123-456-7890 -- like: [0-9]{3}-[0-9]{3}-[0-9]{4}
        if ( preg_match( $format, $field_value ) ) {
          $valid = true;
          break;
        }
      }
      if(!$valid){
		efas_add_to_log($type = "tel","Phone number $field_value not feet the format $format ", $_POST, "gravityforms");
        update_option( 'spamcounter', ++$spamcounter );
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
      }
    }   
    // end
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

  
//add spampixel HTML
add_filter( 'gform_submit_button', 'cfas_sdd_spampixel_to_gravityforms', 10, 1 );
function cfas_sdd_spampixel_to_gravityforms( $button){
    $text = "";
  if (  get_option( 'spampixel' ) ) {
    $text = "";
	$ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
	$text .= '<div class="spx-captcha"></div>';
	$text .= <<<EOT
		<style>
			.spx-captcha {
				width: 1px;
				height: 1px;
                position: absolute;
    			opacity: 0;
			}
			form:focus-within .spx-captcha {
				background-image: url($ajax_url);
			}
		</style>
EOT;
  }
  return $text.$button;
}