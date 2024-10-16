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
    if (maspik_get_settings('maspikTimeCheck') && isset($post['Maspik-exactTime']) && is_numeric($post['Maspik-exactTime'])) {
        $inputTime = (int)$post['Maspik-exactTime'];
        $currentTime = time();
        $timeDifference = $currentTime - $inputTime;

        if ($timeDifference < maspik_submit_buffer()) {
            return [
                'spam' => true,
                'reason' => "Maspik Spam Trap - Submitted too fast, Only $timeDifference seconds (" . $currentTime . " - $inputTime)",
                'message' => "maspikTimeCheck"
            ];
        }
    }

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
    if( cfes_is_supporting() && !empty($country_blacklist) ){ 
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
    $field_value = strtolower($field_value);
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
                    $spam = "Input $field_value is blocked by wildcard pattern";
                    return array('spam' => $spam, 'message' => "text_blacklist");
                  	break;
                }
            } else {
                // Check if exist in string 
                if (maspik_is_field_value_exist_in_string($bad_string, $field_value) ) {
                    $spam =  "Forbidden input $field_value, because <u>$bad_string</u> is blocked";
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
                $spam = "More than $MaxCharacters characters";
                return array('spam' => $spam, 'message' => $message,"option_value" =>$MaxCharacters , 'label' => "MaxCharactersInTextField");
            }

            if ($CountCharacters < $MinCharacters ) {
                $spam = "Less than $MinCharacters characters";
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
    if (empty($field_value)) {
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
                return "because regular expression pattern '$bad_string' is in the blacklist";
            }
        }
        // Check for wildcard pattern using fnmatch
        elseif (strpbrk($bad_string_lower, '*?') !== false) {
            if (fnmatch($bad_string_lower, $field_value_lower, FNM_CASEFOLD)) {
                return "because wildcard pattern '$bad_string' is in the blacklist";
            }
        }
        // Check for exact match of the email domain
        elseif ($bad_string_lower[0] === '@') {
            if ($email_domain === substr($bad_string_lower, 1)) {
                return "because spam email domain '$bad_string' is in the blacklist";
            }
        }
        // Check for exact match
        else {
            if (maspik_is_field_value_equal_to_string($bad_string_lower, $field_value_lower)) {
                return "because email '$bad_string' is in the blacklist";
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

    $tel_formats = empty($tel_formats) ? [] : explode("\n", str_replace("\r", "", $tel_formats));
    // Check if there are additional blacklist entries from the spam API
    if ($additional_blacklist = efas_get_spam_api('phone_format')) {
        $tel_formats = array_merge($tel_formats, $additional_blacklist);
    }
    if (empty($tel_formats) || !is_array($tel_formats)) {
        return array('valid' => true, 'reason' => 'Empty formats', 'message' => 'Empty formats');
    }
    
    $reason = "Phone number " . $field_value . " does not meet the given format. ";

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
                return array('valid' => true, 'reason' => "Regular expression match: $format", 'message' => 'tel_formats');
            }
        } 
        // Wildcard pattern
        elseif (strpbrk($format, '*?') !== false) {
            if (fnmatch($format, $field_value, FNM_CASEFOLD)) {
                return array('valid' => true, 'reason' => "Wildcard pattern match: $format", 'message' => 'tel_formats');
            }
        } 
    }

    return array('valid' => false, 'reason' => $reason, 'message' => 'tel_formats', 'label' => 'tel_formats');
}


/**
* Textarea field check 
**/
function checkTextareaForSpam($field_value) {
    
    // Get the blacklist from options and merge with API data if available
    $textarea_blacklist = maspik_get_settings('textarea_blacklist') ? efas_makeArray(maspik_get_settings('textarea_blacklist')) : array();
    if (efas_get_spam_api('textarea_field')) {
        $blacklist_json = efas_get_spam_api('textarea_field');
        $textarea_blacklist = array_merge($textarea_blacklist, $blacklist_json);
    }
    
    foreach ($textarea_blacklist as $bad_string) {
        if ( maspik_is_field_value_exist_in_string($bad_string, $field_value) ) {
            return array('spam' => "field value includes <u>$bad_string</u>", 'message' => "textarea_field" , 'option_value' => $field_value, 'label' => "textarea_blacklist"  );
        }
    }

    // only if pro user
    if ( cfes_is_supporting() ) {
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
    $max_links = is_numeric( maspik_get_settings('contain_links') ) ? maspik_get_settings('contain_links') : $max_linksAPI ;
    if (is_numeric($max_links) && maspik_get_settings('textarea_link_limit_toggle') ) {
        $max_links = intval($max_links);
        $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        $num_links = preg_match_all($reg_exUrl, $field_value);
        if ($num_links > $max_links) {
            return array('spam' => "Contains <u>more than $max_links links</u>", 'message' => "contain_links");
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
        if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
            $CountCharacters = mb_strlen($field_value); // Use mb_strlen for multibyte characters
            if ($CountCharacters > $MaxCharacters) {
                $spam = "More than $MaxCharacters characters in Text Area field.";
                return array('spam' => $spam, 'message' =>  $message, "option_value" => $MaxCharacters , 'label' => "MaxCharactersInTextAreaField");
            }elseif ($CountCharacters < $MinCharacters) {
                $spam = "Less than $MinCharacters characters in Text Area field.";
                return array('spam' => $spam, 'message' =>  $message, "option_value" => $MinCharacters , 'label' => "MinCharactersInTextAreaField");
            }
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