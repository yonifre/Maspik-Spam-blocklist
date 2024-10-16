<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Function to check if a type contains any word in the given array
function maspik_if_contains_string_in_array($type, $to_check_array) {
    foreach ($to_check_array as $word) {
        if (strpos($type, $word) !== false || $type == $word) {
            return true;
        }
    }
    return false;
}


/**
 * Main Ninja Forms validation functions
 *
 */

add_filter( 'ninja_forms_submit_data', 'my_ninja_forms_submit_data' );

function my_ninja_forms_submit_data( $form_data ) {
    
    $to_check_text = array("name", "text", "single-line");
    $to_check_tel = array("tel", "phone");
    $to_check_email = array("email", "contact");
    $to_check_textarea = array("textarea", "textbox","message");

    $spam = false;
    $reason ="";
    // ip
    $ip =  efas_getRealIpAddr();

    // Country IP Check 
    $GeneralCheck = GeneralCheck($ip,$spam,$reason,false,"ninjaforms");
    $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
    $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
    $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
    $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;


    // Iterate through the first key ID
    $fields = $form_data['fields'];
    $first_key = array_keys($fields)[0] ? array_keys($fields)[0] : $form_data['fields'][1];

    if ( $spam) {
        efas_add_to_log($type = "Country/IP",$reason , $fields, "Ninja Forms", $message,  $spam_val );
        $form_data['errors']['fields'][$first_key] =  __('General: ', 'contact-forms-anti-spam').cfas_get_error_text($message);
        return $form_data;
    }
    
    
    foreach( $form_data[ 'fields' ] as $current ) {
        
        $field_id    = $current[ 'id' ];
        $current_type   = $current[ 'key' ] ? $current[ 'key' ] : "none";

        $field_value = is_array($current['value'])  ?  strtolower( implode( " ", $current['value'] ) ) : strtolower( $current['value'] ) ; 
        
        // Skiping empty fields
        if( empty($field_value) ) continue;

            
        // Validate Text Field
        if ( maspik_if_contains_string_in_array($current_type, $to_check_text) ) {

            $validateTextField = validateTextField($field_value);

            $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
            if($spam) {
                $message = $validateTextField['message'];
                $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
                $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

                efas_add_to_log($type = "text",$spam, $fields, "Ninja Forms", $spam_lbl, $spam_val);           
                $form_data['errors']['fields'][$field_id] = cfas_get_error_text($message);
                return $form_data;
            }
        }
        
        //Email
        if ( maspik_if_contains_string_in_array($current_type, $to_check_email) ) {
            $spam = checkEmailForSpam($field_value);
            $spam_val = $field_value;
            if($spam) {
                efas_add_to_log($type = "email","Email $field_value is block $spam" , $fields, "Ninja Forms", "emails_blacklist", $spam_val);
                $form_data['errors']['fields'][$field_id] = cfas_get_error_text();
                return $form_data;
            }
        }
        // Phone
        if ( maspik_if_contains_string_in_array($current_type, $to_check_tel) ) {
            $checkTelForSpam = checkTelForSpam($field_value);
            $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
            $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
            $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
            $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
            $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;

            if(!$valid) {
                $message = $checkTelForSpam['message'];
                efas_add_to_log($type = "tel","Phone number <b>$field_value</b> not feet the given format ($reason)", $fields, "Ninja Forms", $spam_lbl, $spam_val);
                $form_data['errors']['fields'][$field_id] = cfas_get_error_text($message);
                return $form_data;
            }
        }
        
        // Textarea
        if ( maspik_if_contains_string_in_array($current_type, $to_check_textarea) ) {
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
            if($spam) {
                $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
                $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
                $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

                efas_add_to_log($type = "textarea",$spam, $fields, "Ninja Forms", $spam_lbl, $spam_val);
                $form_data['errors']['fields'][$field_id] = cfas_get_error_text($message);
                return $form_data;
            }
        }
        
    // end foreach   
    }

	return $form_data;
}



function add_custom_html_to_ninja_forms( $form_id, $settings, $form_fields ) {
    
    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $custom_html = "";

        if (maspik_get_settings('maspikHoneypot')) {
            $custom_html .= '<div class="ninja-forms-field maspik-field">
                <label for="full-name-maspik-hp" class="ninja-forms-field-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="ninja-forms-field-element" placeholder="Leave this field empty">
            </div>';
        }

        if (maspik_get_settings('maspikYearCheck')) {
            $custom_html .= '<div class="ninja-forms-field maspik-field">
                <label for="Maspik-currentYear" class="ninja-forms-field-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="ninja-forms-field-element" placeholder="">
            </div>';
        }

        if (maspik_get_settings('maspikTimeCheck')) {
            $custom_html .= '<div class="ninja-forms-field maspik-field">
                <label for="Maspik-exactTime" class="ninja-forms-field-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="ninja-forms-field-element" placeholder="">
            </div>';
        }

        echo $custom_html;
    }
}
add_action( 'ninja_forms_before_container', 'add_custom_html_to_ninja_forms', 10, 3 );
