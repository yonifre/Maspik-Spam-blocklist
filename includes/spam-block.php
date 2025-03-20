<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 

/*
*
*
* Per field functions
*
*
*/

/**
* Genegal block
**/
function maspik_make_extra_spam_check($post) {

    // Honeypot check
    if (maspik_get_settings('maspikHoneypot') && isset($post['full-name-maspik-hp']) && !empty($post['full-name-maspik-hp'])) {
        return [
            'spam' => true,
            'reason' => "Honeypot field is not empty",
            'message' => "maspikHoneypot"
        ];
    }

    // Spam key check, maspikTimeCheck is the old name
    if (maspik_get_settings('maspikTimeCheck')) {

            // Check if the spam key exists in the POST data
        if (!isset($post['maspik_spam_key']) || empty($post['maspik_spam_key'])) {
            // Spam detected, return error or handle as necessary
            return [
                'spam' => true,
                'reason' => "Spam key check failed (empty)",
                'message' => "maspikTimeCheck"
            ];
        }

        // Get the correct key
        $correct_spam_key = maspik_get_spam_key();

        // If the provided spam key does not match, mark as spam
        if ($post['maspik_spam_key'] !== $correct_spam_key) {
            return [
                'spam' => true,
                'reason' => "Spam key check failed (not match)",
                'message' => "maspikTimeCheck"
            ];
        }

    }

    // Year check

    if (maspik_get_settings('maspikYearCheck')) {
        $serverYear = intval(date('Y'));
        $submittedYear = sanitize_text_field($post['Maspik-currentYear']);
        if ($post['Maspik-currentYear'] != $serverYear) {
            return [
                'spam' => true,
                'reason' => "JavaScript check failed - The year submitted by JavaScript($submittedYear) does not match the current server year($serverYear)",
                'message' => "maspikYearCheck"
            ];
        }
    }
    

    // Time check
    // TODO: remove this check, it's not needed
    /*
    if (maspik_get_settings('maspikTimeCheck') && isset($post['Maspik-exactTime']) && is_numeric($post['Maspik-exactTime'])) {
        $inputTime = (int)$post['Maspik-exactTime'];
        $currentTime = time();
        $timeDifference = abs($currentTime - $inputTime);

        if ($inputTime > $currentTime) {
            // for prevent false positive
                return [
                    'spam' => false,
                    'reason' => false,
                    'message' => false
            
                // 'spam' => true,
                //'reason' => "Invalid submission time - future timestamp detected",
                // 'message' => "maspikTimeCheck"
            ];
        }

        if ($timeDifference < maspik_submit_buffer()) {
            return [
                'spam' => true,
                'reason' => "Maspik Spam Trap - Submitted too fast, Only {$timeDifference} seconds",
                'message' => "maspikTimeCheck"
            ];
        }
    }
    */

    // If we've made it this far, it's not spam
    return [
        'spam' => false,
        'reason' => false,
        'message' => false
    ];
}

function maspik_submit_buffer(){
    return 4;
}


function maspik_HP_name(){
    return "full-name-maspik-hp";
}


function GeneralCheck($ip, &$spam, &$reason, $post = "",$form = false) {
    
    $to_do_extra_spam_check = maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck');
    if( is_array($post) && $to_do_extra_spam_check ){ 
        $extra_spam_check =  maspik_make_extra_spam_check($post) ;
        $is_spam = isset($extra_spam_check['spam']) ? $extra_spam_check['spam'] : $spam ;
        if($is_spam){
            $reason = isset($extra_spam_check['reason']) ? $extra_spam_check['reason'] : $reason ;
            $message = $extra_spam_check['message'] ? $extra_spam_check['message'] : 0 ;
            return array('spam' => true, 'reason' => $reason, 'message' => $message, 'value' => 1);
        }
    }
    
    
      
    $message = 0;
    $opt_value = maspik_get_dbvalue();
    $ip_blacklist = maspik_get_settings('ip_blacklist') ? efas_makeArray(maspik_get_settings('ip_blacklist')) : array();
    
    // Todo: api to $ip_blacklist
    
    
    $AllowedOrBlockCountries = maspik_get_settings('AllowedOrBlockCountries') == 'allow' ? 'allow' : 'block';
    $country_blacklist_array =  maspik_get_settings('country_blacklist','select');
    foreach($country_blacklist_array as $value){
        $cleanval = trim($value -> $opt_value);
        if(!empty($cleanval)){
            $country_blacklist = explode(" ", $value -> $opt_value);
        }else{
            $country_blacklist = array();
        }
    }

        // Countries API
    if (efas_get_spam_api('country_blacklist') && 
        (efas_get_spam_api('AllowedOrBlockCountries',"string") == 'allow' || 
         efas_get_spam_api('AllowedOrBlockCountries',"string") == 'block')) {
        $countries_blacklist_api = efas_get_spam_api('country_blacklist');
        $AllowedOrBlockCountries = efas_get_spam_api('AllowedOrBlockCountries',"string");
        $country_blacklist = $countries_blacklist_api;
    }

    

    // Check country blacklist only if is pro user
    if( cfes_is_supporting("country_location") && !empty($country_blacklist) ){ 
        $xml_data = @file_get_contents("http://www.geoplugin.net/xml.gp?ip=" . $ip);
        if ($xml_data) {
            $xml = simplexml_load_string($xml_data);
            $countryCode = $xml && $xml->geoplugin_countryCode && $xml->geoplugin_countryCode != "" ? (string) $xml->geoplugin_countryCode : "Unknown";
            $continentCode = $xml && $xml->geoplugin_continentCode && $xml->geoplugin_continentCode != "" ? (string) $xml->geoplugin_continentCode : "Unknown";
            
            $selected_country_codes = array();
            $selected_continent_codes = array();
        
            foreach ($country_blacklist as $item) {
                if (strpos($item, 'Continent:') === 0) {
                    $selected_continent_codes[] = substr($item, strlen('Continent:'));
                } else {
                    $selected_country_codes[] = $item;
                }
            }
        
            if ($AllowedOrBlockCountries === 'block') {
                if (in_array($countryCode, $selected_country_codes) || in_array($continentCode, $selected_continent_codes)) {
                    $spam = true;
                    $message = "country_blacklist";
                    $reason = "Country code $countryCode or continent $continentCode is blacklisted (block)";
                    return array('spam' => $spam, 'reason' => $reason, 'message' => $message, 'value' => $countryCode);
                }
            } elseif ($AllowedOrBlockCountries === 'allow') {
                if (!in_array($countryCode, $selected_country_codes) && !in_array($continentCode, $selected_continent_codes)) {
                    $spam = true;
                    $message = "country_blacklist";
                    $reason = "Country code $countryCode or continent $continentCode is not in the whitelist (allow)";
                    return array('spam' => $spam, 'reason' => $reason, 'message' => $message, 'value' => $countryCode);
                }
            }
        }
    }

    // Check IP blacklist
    if (in_array($ip, $ip_blacklist)) {
        $spam = true;
        $reason = "IP $ip is blacklisted";
        return array('spam' => $spam, 'reason' => $reason, 'message' => "ip_blacklist", 'value' => $ip);
    }

    // CIDR Filter
    foreach ($ip_blacklist as $cidr) {
        if (ip_is_cidr($cidr) && cidr_match($ip, $cidr)) {
            $spam = true;
            $reason = "IP $ip is in CIDR: $cidr";
            return array('spam' => $spam, 'reason' => $reason, 'message' => "ip_blacklist", 'value' => $ip);
        }
    }
    
    // AbuseIPDB API  (Thanks to @josephcy95)
      $abuseipdb_api = maspik_get_settings('abuseipdb_api') ? maspik_get_settings('abuseipdb_api') : false;
      $pabuseipdb_score = maspik_get_settings('abuseipdb_score');
      //Check if have abuseipdb_api in the API Setting page (WpMaspik)
      if ( efas_get_spam_api('abuseipdb_api') ){
        $abuseipdb_api_json = null !== efas_get_spam_api('abuseipdb_api',"string") ? efas_get_spam_api('abuseipdb_api',"string") : false;
        $abuseipdb_api = $abuseipdb_api ? $abuseipdb_api : $abuseipdb_api_json; // Site setting is stronger
        $abuseipdb_score_json = null !== efas_get_spam_api('abuseipdb_score',"string") ? efas_get_spam_api('abuseipdb_score',"string") : '99';
        $pabuseipdb_score = $pabuseipdb_score ? $pabuseipdb_score : $abuseipdb_score_json; // Site setting is stronger
      }

      if (($abuseipdb_api != false) && !$spam  && $pabuseipdb_score > 10) { // $pabuseipdb_score > 10 for more save
        $abuseconfidencescore = check_abuseipdb($ip);
        if ($abuseconfidencescore && $abuseconfidencescore >= (int)$pabuseipdb_score) {
          $spam = true;
          $reason = "AbuseIPDB Risk: $abuseconfidencescore ";
          return array('spam' => $spam, 'reason' => $reason, 'message' => "abuseipdb_api", 'value' => "");

        }
      }

    // Proxycheck.io Risk Check  (Thanks to @josephcy95)
      $proxycheck_io_api = maspik_get_settings('proxycheck_io_api') ? maspik_get_settings('proxycheck_io_api') : false;
      $proxycheck_io_risk = maspik_get_settings('proxycheck_io_risk');
      //Check if have proxycheck_io_api in the API Setting page (WpMaspik)
      if ( null !== efas_get_spam_api('proxycheck_io_api') ){
        $proxycheck_io_api_json = is_array( efas_get_spam_api('proxycheck_io_api') ) ? efas_get_spam_api('proxycheck_io_api',"string") : false;
        $proxycheck_io_risk_json = is_array( efas_get_spam_api('proxycheck_io_risk') ) ? efas_get_spam_api('proxycheck_io_risk',"string") : false;
        $proxycheck_io_api = $proxycheck_io_api ? $proxycheck_io_api : $proxycheck_io_api_json; // Site setting is stronger
        $proxycheck_io_risk = $proxycheck_io_risk ? $proxycheck_io_risk : $proxycheck_io_risk_json; // Site setting is stronger
      }

      if ($proxycheck_io_risk && $proxycheck_io_api && !$spam && (int)$proxycheck_io_risk > 10 ) {
        $proxycheck_io_riskscore = check_proxycheckio($ip);
        if ( $proxycheck_io_riskscore && $proxycheck_io_riskscore >= (int)$proxycheck_io_risk) {
          $spam = true;
          $reason = "Proxycheck.io Risk: $proxycheck_io_riskscore max is $proxycheck_io_risk";
          return array('spam' => $spam, 'reason' => $reason, 'message' => "proxycheck_io_api", 'value' => "");
        }
      }
    
    //start check IP in api
    $do_ip_api_check = maspik_get_settings('maspikDbCheck');
    if ($do_ip_api_check && !$spam && $form) {
        $exists = check_ip_in_api($ip,$form);
        if($exists){
            $reason = "Ip: $ip, exists in Maspik blacklist" ;
            $message = "maspikDbCheck" ;
            return array('spam' => true, 'reason' => $reason, 'message' => $message, 'value' => 1);
        }
    } 
    //end check IP in api

    

    return array('spam' => $spam, 'reason' => $reason, 'message' => $message, 'value' => "");
}


/**
* Text field check 
**/
function validateTextField($field_value) {  
    // Convert the field value to lowercase.
    $field_value = is_array($field_value) ? strtolower(implode(" ",$field_value)) : strtolower($field_value);
  	$text_blacklist = maspik_get_settings( 'text_blacklist' ) ? efas_makeArray(maspik_get_settings('text_blacklist') ) : array();
	$spam = false;
  	if ( efas_get_spam_api() ){
    	$text_blacklist_json =  efas_get_spam_api();
      	$text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
    }  

    // Check for exact string matches and wildcard patterns in the blacklist.
    if (is_array($text_blacklist)) {
        foreach ($text_blacklist as $bad_string) {
            if ( empty($bad_string) ) {
               continue;
            }
            $bad_string = trim(strtolower($bad_string));
 			if (strpos($bad_string, '*') !== false) {
                // Handle wildcard pattern using fnmatch
                if (fnmatch($bad_string, $field_value, FNM_CASEFOLD)) {
                    $spam = "Input *$field_value* is blocked by wildcard pattern";
                    return array('spam' => $spam, 'message' => "text_blacklist");
                  	break;
                }
            } else {
                // Check if exist in string 
                if (maspik_is_field_value_exist_in_string($bad_string, $field_value) ) {
                    $spam =  "Forbidden input *$field_value*, because *$bad_string* is blocked";
                    return array('spam' => $spam, 'message' => "text_blacklist", "option_value" => $bad_string,  'label' => "text_blacklist");
                   	break;
                }
            }
        }
    }

    // Get the maximum character limit from the site if not, from API or 
    $MaxCharacters = maspik_get_settings('MaxCharactersInTextField') ? maspik_get_settings('MaxCharactersInTextField') : efas_get_spam_api('MaxCharactersInTextField',$type = "bool");
    $MinCharacters = maspik_get_settings('MinCharactersInTextField') ? maspik_get_settings('MinCharactersInTextField') : efas_get_spam_api('MinCharactersInTextField',$type = "bool");

    if(maspik_get_settings('text_custom_message_toggle')== 1){
        $message = 'MaxCharactersInTextField';
    }else{
        $message = '';
    }
    // Check if the maximum character limit is valid
    if(maspik_get_settings(maspik_toggle_match('MaxCharactersInTextField')) == 1  || maspik_is_contain_api(['MaxCharactersInTextField', 'MinCharactersInTextField'])){
        if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
            $CountCharacters = mb_strlen($field_value); // Use mb_strlen for multibyte characters
            if ($CountCharacters > $MaxCharacters ) {
                $spam = "More than *$MaxCharacters* characters";
                return array('spam' => $spam, 'message' => $message,"option_value" =>$MaxCharacters , 'label' => "MaxCharactersInTextField");
            }

            if ($CountCharacters < $MinCharacters ) {
                $spam = "Less than *$MinCharacters* characters";
                return array('spam' => $spam, 'message' => $message,"option_value" =>$MinCharacters, 'label' => "MinCharactersInTextField");
            }
        }
    }

	return false;
}


/**
* Email check 
**/

function checkEmailForSpam($field_value) {
    // Check if the field is empty
    if (empty($field_value) || is_array($field_value)) {
        return false; // Not spam if the field is empty.
    }

    // Get the emails blacklist
    $emails_blacklist = efas_makeArray(maspik_get_settings('emails_blacklist'));

    // Check if there are additional blacklist entries from the spam API
    $additional_blacklist = efas_get_spam_api('email_field');
    if ($additional_blacklist) {
        $emails_blacklist = array_merge($emails_blacklist, $additional_blacklist);
    }

    // Convert the field value to lowercase for case-insensitive comparison
    $field_value_lower = strtolower(trim($field_value));

    // Extract the domain part of the email address
    if (!filter_var($field_value_lower, FILTER_VALIDATE_EMAIL)) {
        return false; // Not look like email, so maspik not handeling 
    }
    
    $email_parts = explode('@', $field_value_lower);
    $email_domain = end($email_parts);

    // Loop through the blacklist entries
    foreach ($emails_blacklist as $bad_string) {
        // Skip empty or whitespace strings
        if (empty(trim($bad_string))) {
            continue;
        }

        // Convert the blacklist string to lowercase for case-insensitive comparison
        $bad_string_lower = trim(strtolower($bad_string));
        
        // Check for regular expression patterns
        if (strpos($bad_string_lower, '/') === 0) {
            // Suppress errors and check regex pattern validity
            set_error_handler(function() {}, E_WARNING);
            $is_valid_regex = @preg_match($bad_string_lower, '');
            restore_error_handler();

            if ($is_valid_regex === false) {
                // Log invalid regex patterns for debugging
                //error_log("Notice: Invalid regex pattern: $bad_string" on Maspik emails blacklist field);
                continue;
            }

            if (preg_match($bad_string_lower, $field_value_lower)) {
                return "because regular expression pattern *'$bad_string'* is in the blacklist";
            }
        }
        // Check for wildcard pattern using fnmatch
        elseif (strpbrk($bad_string_lower, '*?') !== false) {
            if (fnmatch($bad_string_lower, $field_value_lower, FNM_CASEFOLD)) {
                return "because wildcard pattern *'$bad_string'* is in the blacklist";
            }
        }else {
            if (maspik_is_field_value_exist_in_string($bad_string_lower, $field_value_lower,$make_space = 0)) {
                return "because email *'$bad_string'* is in the blacklist";
            }
        }
    }

    return false;
}


/**
* Phone check 
**/
function checkTelForSpam($field_value) {
    $valid = false; 
    $tel_formats = maspik_get_settings('tel_formats');

    
    $MaxCharacters = maspik_get_settings('MaxCharactersInPhoneField') ? maspik_get_settings('MaxCharactersInPhoneField') : efas_get_spam_api('MaxCharactersInPhoneField',$type = "bool");
    $MinCharacters = maspik_get_settings('MinCharactersInPhoneField') ? maspik_get_settings('MinCharactersInPhoneField') : efas_get_spam_api('MinCharactersInPhoneField',$type = "bool");


    if (maspik_get_settings('phone_limit_custom_message_toggle') == 1) {
        $message = 'MaxCharactersInPhoneField';
    } else {
        $message = '';
    }

    // Check if the maximum character limit is valid  
    if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
        $CountCharacters = mb_strlen(strval($field_value)); // Use mb_strlen for multibyte characters
        if (maspik_get_settings(maspik_toggle_match('MaxCharactersInPhoneField')) == 1) {
            if ($CountCharacters > $MaxCharacters) {
                $reason = "More than $MaxCharacters characters in Phone Number";
                return array('valid' => false, 'reason' => $reason, 'message' => $message, "option_value" =>$MaxCharacters , 'label' => "MaxCharactersInPhoneField");
                
            } elseif ($CountCharacters < $MinCharacters) {
                $reason = "Less than $MinCharacters characters in Phone Number";
                return array('valid' => false, 'reason' => $reason, 'message' => $message,"option_value" =>$MinCharacters , 'label' => "MinCharactersInPhoneField");
            }
        }
    }

    // Numverify API integration
    $numverify_api_key = sanitize_text_field(maspik_get_settings('numverify_api')); // Fetch the API key from plugin settings
    if (!empty($numverify_api_key)) {
        $numverify_result = maspik_numverify_validate_number($field_value, $numverify_api_key);
        if ($numverify_result['valid']) {
            // Do nothing, Numverify validation passed, continue with the next check
        } else {
            $reason = "Numverify validation failed: " . esc_html($numverify_result['error']);
            return array('valid' => false, 'reason' => $reason, 'message' => 'tel_formats', 'label' => 'tel_formats');
        }
    }

        

    $tel_formats = empty($tel_formats) ? [] : explode("\n", str_replace("\r", "", $tel_formats));
    // Check if there are additional blacklist entries from the spam API
    if ($additional_blacklist = efas_get_spam_api('phone_format')) {
        $tel_formats = array_merge($tel_formats, $additional_blacklist);
    }
    if (empty($tel_formats) || !is_array($tel_formats)) {
        return array('valid' => true, 'reason' => 'Empty formats', 'message' => 'Empty formats');
    }
    
    $reason = "Phone number *$field_value* does not meet the given format. ";

    foreach ($tel_formats as $format) {
        $format = trim($format);
        if (empty($format)) {
            continue;
        }
        // Regular expression format
        if (strpos($format, '/') === 0) {
            if (@preg_match($format, '') === false) {
                $reason .= "Invalid regular expression: $format. ";
                continue;
            }

            if (preg_match($format, $field_value)) {
                return array('valid' => true, 'reason' => "Regular expression match: *$format*", 'message' => 'tel_formats');
            }
        } 
        // Wildcard pattern
        elseif (strpbrk($format, '*?') !== false) {
            if (fnmatch($format, $field_value, FNM_CASEFOLD)) {
                return array('valid' => true, 'reason' => "Wildcard pattern match: *$format*", 'message' => 'tel_formats');
            }
        } 
    }    

    return array('valid' => false, 'reason' => $reason, 'message' => 'tel_formats', 'label' => 'tel_formats');
}


/**
* Textarea field check 
**/
function checkTextareaForSpam($field_value) {

    $field_value = is_array($field_value) ? strtolower(implode(" ",$field_value)) : strtolower($field_value);


    // Get the blacklist from options and merge with API data if available
    $textarea_blacklist = maspik_get_settings('textarea_blacklist') ? efas_makeArray(maspik_get_settings('textarea_blacklist')) : array();
    if (efas_get_spam_api('textarea_field')) {
        $blacklist_json = efas_get_spam_api('textarea_field');
        $textarea_blacklist = array_merge($textarea_blacklist, $blacklist_json);
    }
    
    foreach ($textarea_blacklist as $bad_string) {
        if (strpbrk($bad_string, '*?') !== false) {
            // If there are special characters, ensure wildcards on both sides
            $pattern = trim($bad_string, '*'); // Remove existing asterisks from each side
            $pattern = "*$pattern*";           // Add asterisks on both sides
            
            if (fnmatch($pattern, $field_value, FNM_CASEFOLD)) {
                return array(
                    'spam' => "field value matches pattern *$bad_string*", 
                    'message' => "textarea_field",
                    'option_value' => $bad_string,
                    'label' => "textarea_blacklist"
                );
            }
        } 
        elseif (maspik_is_field_value_exist_in_string($bad_string, $field_value)) {
            // Regular word check
            return array(
                'spam' => "field value includes *$bad_string*",
                'message' => "textarea_field",
                'option_value' => $bad_string,
                'label' => "textarea_blacklist"
            );
        }
    }

    // Check for emojis
    if(maspik_get_settings('emoji_check')){
        if (maspik_is_contains_emoji($field_value)) {
            return array(
                'spam' => "Emoji found in the field",
                'message' => "emoji_check",
                'option_value' => $field_value,
                'label' => "emoji_check"
            );
        }
    }

    // only if pro user
    if ( cfes_is_supporting("country_location") ) {
        // Check for required language
        $opt_value = maspik_get_dbvalue();
        $lang_need_array = maspik_get_settings('lang_needed','select' );
        $lang_needed = array(); // Initialize as an empty array
        foreach($lang_need_array as $value){
            $cleanval = trim($value -> $opt_value);
            if(!empty($cleanval)){
                $lang_needed = explode(" ", $value -> $opt_value);
            }
        }

        if (efas_get_spam_api('lang_needed')) {
            $blacklist_json = efas_get_spam_api('lang_needed');
            $lang_needed = array_merge($lang_needed, $blacklist_json);
        }
        // Remove empty values from blacklist_json after merging
        $lang_needed = array_filter($lang_needed, function($value) {
            return !empty($value);
        });

        if( !empty($lang_needed) ){
        
            $missing_lang = maspik_detect_language_in_string($lang_needed, $field_value);

            if ($lang_needed && empty($missing_lang)) {
                $listofNeededlanguage = implode(", ",$lang_needed);
                return array('spam' => "Needed language is missing ($listofNeededlanguage)", 'message' => "lang_needed", 'option_value' => $listofNeededlanguage, 'label' => "lang_needed");
            }
        }

        // Check for forbidden language
        $lang_x_array = maspik_get_settings('lang_forbidden', 'select' );
        $lang_forbidden = array(); // Initialize as an empty array

        foreach($lang_x_array as $value){
            $cleanval = trim($value->$opt_value);
            if(!empty($cleanval)){
                $lang_forbidden = explode(" ", $value->$opt_value);
            }
        }

        if (efas_get_spam_api('lang_forbidden')) {
            $blacklist_json = efas_get_spam_api('lang_forbidden');
            $lang_forbidden = array_merge($lang_forbidden, $blacklist_json);
        }
        // Remove empty values from blacklist_json after merging
        $lang_forbidden = array_filter($lang_forbidden, function($value) {
            return !empty($value);
        });

        if( !empty($lang_forbidden) ){
            $detected_forbidden_lang = maspik_detect_language_in_string($lang_forbidden, $field_value);

            if (!empty($detected_forbidden_lang)) {
                return array('spam' => "Forbidden language '$detected_forbidden_lang' exists", 'message' => "lang_forbidden", 'option_value' => $detected_forbidden_lang, 'label' => "lang_forbidden");
            }
        }
            
    } 
    
    // Check for maximum number of links
    $max_linksAPI = is_numeric( efas_get_spam_api('contain_links', $type = "bool") ) ? efas_get_spam_api('contain_links', $type = "bool") : false;
    $max_links = is_numeric( maspik_get_settings('contain_links') ) ? maspik_get_settings('contain_links') : $max_linksAPI;
    
    if (is_numeric($max_links) && maspik_get_settings('textarea_link_limit_toggle')) {
        $max_links = intval($max_links);
        
        
        // Count HTML links and http(s) links
        $patterns = array(
            '/<a[^>]*href[^>]*>/i',            // HTML links (<a href="...")
            '/https?:\/\/[^\s<>"\']+/i',       // http(s):// links with any valid URL chars
            '/www\.[a-z0-9][-a-z0-9.]+\.[a-z0-9-]+/i'  // www.domain.tld with www.
        );
        
        $num_links = 0;
        foreach ($patterns as $pattern) {
            $matches = array();
            $count = preg_match_all($pattern, $field_value, $matches);
            $num_links += ($count ? $count : 0);            
        }
        
        // If max_links is 0, block any links. Otherwise, block if more than max_links
        if (($max_links === 0 && $num_links > 0) || ($max_links > 0 && $num_links > $max_links)) {
            $message = $max_links === 0 ? 
                "Links are not allowed" : 
                "Contains <u>more than $max_links links</u>";
            
            return array(
                'spam' => $message,
                'message' => "contain_links",
                'option_value' => $num_links,
                'label' => "contain_links"
            );
        }
    }
    // Get the maximum character limit from the spam API or options
    $MaxCharacters = maspik_get_settings('MaxCharactersInTextAreaField') ? maspik_get_settings('MaxCharactersInTextAreaField') : efas_get_spam_api('MaxCharactersInTextAreaField',$type = "bool");
    $MinCharacters = maspik_get_settings('MinCharactersInTextAreaField') ? maspik_get_settings('MinCharactersInTextAreaField') : efas_get_spam_api('MinCharactersInTextAreaField',$type = "bool");


    if(maspik_get_settings('textarea_custom_message_toggle')== 1){
        $message = 'MaxCharactersInTextAreaField';
    }else{
        $message = '';
    }


    // Check if the maximum character limit is valid
    if (maspik_get_settings(maspik_toggle_match('MaxCharactersInTextAreaField')) == 1 || maspik_is_contain_api(['MaxCharactersInTextAreaField', 'MinCharactersInTextAreaField'])) {
        $CountCharacters = mb_strlen($field_value); // Use mb_strlen for multibyte characters
        
        // Check maximum characters if set, and if the character limit is greater than 2 (to)
        if (is_numeric($MaxCharacters) && $MaxCharacters > 2 && $CountCharacters > $MaxCharacters) {
            $spam = "More than $MaxCharacters characters in Text Area field.";
            return array('spam' => $spam, 'message' =>  $message, "option_value" => $MaxCharacters , 'label' => "MaxCharactersInTextAreaField");
        }
        
        // Check minimum characters if set
        if (is_numeric($MinCharacters) && $MinCharacters > 0 && $CountCharacters < $MinCharacters) {
            $spam = "Less than $MinCharacters characters in Text Area field.";
            return array('spam' => $spam, 'message' =>  $message, "option_value" => $MinCharacters , 'label' => "MinCharactersInTextAreaField");
        }
    }

    // No spam found in this field
    return false;
}



// Add custom JavaScript to the footer
function Maspik_add_hp_js_to_footer() {
    // Check if any of the settings are enabled
    $maspikHoneypot = maspik_get_settings('maspikHoneypot');
    $maspikTimeCheck = maspik_get_settings('maspikTimeCheck');
    $maspikYearCheck = maspik_get_settings('maspikYearCheck');

    // Only add the code if at least one of the settings is enabled
    if ($maspikHoneypot || $maspikTimeCheck || $maspikYearCheck) {
        ?>
        <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {

            // Function to check if localStorage is available
            function localStorageAvailable() {
                try {
                    var test = "__localStorage_test__";
                    localStorage.setItem(test, test);
                    localStorage.removeItem(test);
                    return true;
                } catch (e) {
                    return false;
                }
            }

            var exactTimeGlobal = null;
            if (localStorageAvailable()) {
                // Check if exactTimeGlobal is already stored in localStorage
                exactTimeGlobal = localStorage.getItem('exactTimeGlobal');
            }

            // Common attributes and styles for hidden fields
            var commonAttributes = {
                'aria-hidden': "true", // Accessibility
                tabindex: "-1", // Accessibility
                autocomplete: "off", // Prevent browser autofill
                class: "maspik-field"
            };

            var hiddenFieldStyles = {
                position: "absolute",
                left: "-99999px"
            };

            // Function to create a hidden field
            function createHiddenField(attributes, styles) {
                var field = document.createElement("input");
                for (var attr in attributes) {
                    field.setAttribute(attr, attributes[attr]);
                }
                for (var style in styles) {
                    field.style[style] = styles[style];
                }
                return field;
            }

            // Function to add hidden fields to the form if they do not already exist
            function addHiddenFields(formSelector, fieldClass) {
                document.querySelectorAll(formSelector).forEach(function(form) {
                    if (!form.querySelector('.maspik-field')) {
                        if (<?php echo json_encode($maspikHoneypot); ?>) {
                            var honeypot = createHiddenField({
                                type: "text",
                                name: "<?php echo maspik_HP_name(); ?>",
                                id: "<?php echo maspik_HP_name(); ?>",
                                class: fieldClass + " maspik-field",
                                placeholder: "Leave this field empty"
                            }, hiddenFieldStyles);
                            form.appendChild(honeypot);
                        }

                        if (<?php echo json_encode($maspikYearCheck); ?>) {
                            var currentYearField = createHiddenField({
                                type: "text",
                                name: "Maspik-currentYear",
                                id: "Maspik-currentYear",
                                class: fieldClass + " maspik-field"
                            }, hiddenFieldStyles);
                            form.appendChild(currentYearField);
                        }

                        if (<?php echo json_encode($maspikTimeCheck); ?>) {
                            var exactTimeField = createHiddenField({
                                type: "text",
                                name: "Maspik-exactTime",
                                id: "Maspik-exactTime",
                                class: fieldClass + " maspik-field"
                            }, hiddenFieldStyles);
                            form.appendChild(exactTimeField);
                        }
                    }
                });
            }

            // Add hidden fields to various form types
            //Not suported ninja form
            addHiddenFields('form.brxe-brf-pro-forms', 'brxe-brf-pro-forms-field-text');
            //formidable
            addHiddenFields('form.frm-show-form', 'frm_form_field');
            addHiddenFields('form.elementor-form', 'elementor-field-textual');
            //hello plus
            addHiddenFields('form.ehp-form', 'hello-plus-field-text');

            // Function to set the current year and exact time in the appropriate fields
            function setDateFields() {
                var currentYear = new Date().getFullYear();

                if (!exactTimeGlobal) {
                    exactTimeGlobal = Math.floor(Date.now() / 1000);
                    if (localStorageAvailable()) {
                        localStorage.setItem('exactTimeGlobal', exactTimeGlobal);
                    }
                }

                document.querySelectorAll('input[name="Maspik-currentYear"]').forEach(function(input) {
                    input.value = currentYear;
                });

                document.querySelectorAll('input[name="Maspik-exactTime"]').forEach(function(input) {
                    input.value = exactTimeGlobal;
                });
            }

            // Initial call to set date fields
            setDateFields();

            // Use MutationObserver to detect AJAX form reloads and reset hidden fields
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length) {
                        setTimeout(function() {
                            setDateFields();
                        }, 500);
                    }
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        });
        </script>
        <style>
        .maspik-field { display: none !important; }
        </style>
        <?php
    }
}
add_action('wp_footer', 'Maspik_add_hp_js_to_footer');

/**
 * Injects the spam key field dynamically into forms with specific classes using JavaScript.
 */
function maspik_add_spam_key_field_js() {
    if (!maspik_get_settings("maspikTimeCheck") ) {
        return;
    }
    // Define an array of classes for the forms you want to target
    $target_classes = array(
       /* 'wpcf7-form', // Add the class for Contact Form 7
        'elementor-form',  // Add the class for Elementor Forms
        'gform_wrapper',   // Add the class for Gravity Forms
        'wpforms-form',    // Add the class for WPForms
        'frm_forms form', // Add the class for formidable
        'forminator-ui', // Add the class for Forminator option 1
        'forminator_ajax', // Add the class for Forminator option 2
        'forminator-custom-form', // Add the class for Forminator option 3  
        'fluentform form', // Add the class for FluentForms
        'everest-forms', // Add the class for Everest Forms
        'jet-form-builder', // Add the class for Jet Form Builder
        'nf-form-layout form', // Add the class for Ninja Forms
        'nf-form-wrap form', // Add the class for Ninja Forms
        '.nf-after-form-content', // Add the class for Ninja Forms
        'gravityform', // Add the class for Gravity Forms
        'woocommerce-review', // Add the class for WooCommerce Reviews
        'woocommerce-registration', // Add the class for WooCommerce Registration
        'bricks-form', // Add the class for Bricks Forms
        'buddypress', // Add the class for BuddyPress
        'buddyforms', // Add the class for BuddyForms
        'wp-block-form', // Add the class for Gutenberg Forms */
        'form' // for any form
        // Add other form classes as needed

    );

    $spam_key = maspik_get_spam_key(); // Get the unique spam key

    // Convert the classes array to a string for JavaScript
    $target_classes_js = implode('", "', $target_classes);

    // Add a script that adds the hidden field dynamically via JavaScript when the form is submitted
    echo '
        <script type="text/javascript">
        // Maspik add key to forms
            document.addEventListener("DOMContentLoaded", function() {
                var spamKey = "' . esc_js( $spam_key ) . '";
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "maspik_spam_key";
                input.value = spamKey;
                input.setAttribute("autocomplete", "off");
                
                // Select all forms with the specified classes
                var forms = document.querySelectorAll("form");
                forms.forEach(function(form) {
                    // Only add the spam key if its not already added
                    if (!form.querySelector("input[name=maspik_spam_key]")) {
                        form.appendChild(input.cloneNode(true));
                    }
                });
            });
        </script>';
}
// add to all forms
add_action('wp_footer', 'maspik_add_spam_key_field_js' , 99); // Add to wp_footer()
// add to user registration form on admin side
add_action('register_form', 'maspik_add_spam_key_field_js' , 99); // Add to wp_footer()


/**
 * Validate phone number with Numverify API and optional country code.
 */
function maspik_numverify_validate_number($phone_number, $api_key) {
    $phone_number_clean = preg_replace('/[^0-9]/', '', $phone_number); // Will keep only digits 0-9, removing everything else including +

    // Check if country code is enabled in the settings
    $country_code_kye = trim(maspik_get_settings('numverify_country')) === 'none' ? '' : sanitize_text_field(maspik_get_settings('numverify_country')); // country code from the settings
    if ( !empty($country_code_kye) ) {
        $country_phone_code_array = $MASPIK_COUNTRIES_LIST_FOR_PHONE;
        $country_phone_code_from_country_code = $country_phone_code_array[$country_code_kye];
        $country_phone_code_clean = preg_replace('/[^0-9]/', '', $country_phone_code_from_country_code);
        if (strpos($phone_number_clean, $country_phone_code_clean) === 0) {
            $phone_number_clean = substr($phone_number_clean, strlen($country_phone_code_clean));
        }
    }

    // Build the API URL with optional country code
    $url = add_query_arg(array(
        'access_key' => $api_key,
        'number' => $phone_number_clean,
        'country_code' => $country_code_kye // will be added only if there is a country code
    ), 'https://apilayer.net/api/validate');

    $response = wp_remote_get($url, array('timeout' => 10, 'sslverify' => true)); // Force SSL verification

    // Handle errors in API response
    if (is_wp_error($response)) {
        return array('valid' => true, 'error' => 'API request failed');
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (empty($result) || isset($result['error'])) {
        return array(
            //'valid' => false,//isset($result['valid']) ? $result['valid'] : true,
            'valid' => true,
            'error' => $result['error']['info'] ?? 'Unknown error'
        );
    }

    // Return the actual validation result from the API
    return array(
        'valid' => isset($result['valid']) ? $result['valid'] : true,
        'error' => isset($result['error']) ? $result['error'] : "invalid phone number ($phone_number)",
    );
}
