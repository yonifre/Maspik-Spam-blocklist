<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// Forminator hook file

add_filter('forminator_custom_form_submit_errors', 'maspik_validate_forminator_general', 30, 3);
function maspik_validate_forminator_general($submit_errors, $form_id, $field_data_array){
    $spam = false;
    $reason ="";
    // ip
    $ip =  efas_getRealIpAddr();

    // Country IP Check 
    $CountryCheck = CountryCheck($ip,$spam,$reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;
    
    // find the last field ID to assigned the error message 
    $lastNonHiddenName = null;
    // Iterate through the array in reverse order
    for ($i = count($field_data_array) - 1; $i >= 0; $i--) {
        if ($field_data_array[$i]['field_type'] !== 'hidden') {
            // If the current element is not hidden, store its name and break the loop
            $lastNonHiddenName = $field_data_array[$i]['name'];
            break;
        }
    }

    if ( $spam) {
        $submit_errors[][$lastNonHiddenName] = cfas_get_error_text($message);
        efas_add_to_log($type = "Country/IP",$reason, $submit_errors, "Forminator" );
        return $submit_errors;
    }
    
    foreach( $field_data_array as $current ) {
        $field_id = $current['name'];
        $field_value = is_array($current['value'])  ?  strtolower( implode( " ", $current['value'] ) ) : strtolower( $current['value'] ) ; 
            
        // Validate Text Field
        if ( ($current['field_type'] === "name" || $current['field_type'] === "text" ) && ! empty($field_value) ) {
            $validateTextField = validateTextField($field_value);
            $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
            if($spam) {
                $message = $validateTextField['message'];
                efas_add_to_log($type = "text",$spam, $_POST, "Forminator");           
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
        }
        
        //Email
        if ( $current['field_type'] === "email" && ! empty($field_value) ) {
            $spam = checkEmailForSpam($field_value);
            if($spam) {
                efas_add_to_log($type = "email","Email $field_value is block $spam" , $_POST, "Forminator");
                $submit_errors[][$field_id] = $error_message;
                return $submit_errors;
            }
        }
        //
        if ( $current['field_type'] === "phone" && ! empty($field_value) ) {
            $checkTelForSpam = checkTelForSpam($field_value);
            $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
            $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
            $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
            if(!$valid) {
                $message = $checkTelForSpam['message'];
                efas_add_to_log($type = "tel","Telephone number $field_value not feet the given format ($reason)", $_POST, "Forminator");
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
        }
        
        // Textarea
        if ( $current['field_type'] === "textarea" && !empty($field_value) ) {
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
            if($spam) {
                $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
                efas_add_to_log($type = "textarea",$spam, $_POST, "Forminator");
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
        }
        
    // end foreach   
    }

	return $submit_errors;
}

