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


    
    foreach( $field_data_array as $current ) {
        $field_id = $current['name'];
        $field_value = is_array($current['value'])  ?  strtolower( implode( " ", $current['value'] ) ) : strtolower( $current['value'] ) ; 
            
        // Validate Text Field
        if ( ($current['field_type'] === "name" || $current['field_type'] === "text" ) && ! empty($field_value) ) {
            $validateTextField = validateTextField($field_value);
            $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
            $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
            $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

            if($spam) {
                $message = $validateTextField['message'];
                efas_add_to_log($type = "text",$spam, $_POST, "Forminator", $spam_lbl, $spam_val);           
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
            continue;
        }
        
        //Email
        if ( $current['field_type'] === "email" && ! empty($field_value) ) {
            $spam = checkEmailForSpam($field_value);
            $spam_val = $field_value;
            if($spam) {
                efas_add_to_log($type = "email","Email $field_value is block $spam" , $_POST, "Forminator", "emails_blacklist", $spam_val);
                $submit_errors[][$field_id] = cfas_get_error_text();
                return $submit_errors;
            }
            continue;
        }
        //
        if ( $current['field_type'] === "phone" && ! empty($field_value) ) {
            $checkTelForSpam = checkTelForSpam($field_value);
            $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
            $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
            $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;
            $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
            $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;
            
            if(!$valid) {
                $message = $checkTelForSpam['message'];
                efas_add_to_log($type = "tel", $reason, $_POST, "Forminator", $spam_lbl, $spam_val);
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
            continue;
        }
        
        // Textarea
        if ( $current['field_type'] === "textarea" && !empty($field_value) ) {
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
            if($spam) {
                $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
                $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
                $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

                efas_add_to_log($type = "textarea",$spam, $_POST, "Forminator", $spam_lbl, $spam_val);
                $submit_errors[][$field_id] = cfas_get_error_text($message);
                return $submit_errors;
            }
            continue;
        }
        
    // end foreach   
    }

    // Country IP Check 
    $GeneralCheck = GeneralCheck($ip,$spam,$reason,$_POST,"forminator");
    $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
    $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
    $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
    $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;
    $field_id = $field_id ? $field_id : 0 ;
    

    if ($spam) {
        $submit_errors[][$field_id] = cfas_get_error_text($message);
        efas_add_to_log($type = "Country/IP",$reason, $_POST, "Forminator", $message,  $spam_val );
        return $submit_errors;
    }

        

	return $submit_errors;
}



add_action( 'forminator_render_form_submit_markup', function( $html, $form_id, $post_id, $nonce ){

	if ( is_admin() ) {
		return $html;
	}
    
    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $custom_html = "";
        if (maspik_get_settings('maspikHoneypot')) {
            $custom_html .= '<div class="forminator-row maspik-field">
                <label for="full-name-maspik-hp" class="forminator-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="forminator-input" placeholder="Leave this field empty">
            </div>';
        }
        if (maspik_get_settings('maspikYearCheck')) {
            $custom_html .= '<div class="forminator-row maspik-field">
                <label for="Maspik-currentYear" class="forminator-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="forminator-input" placeholder="">
            </div>';
        }
        if (maspik_get_settings('maspikTimeCheck')) {
            $custom_html .= '<div class="forminator-row maspik-field">
                <label for="Maspik-exactTime" class="forminator-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="forminator-input" placeholder="">
            </div>';
        }
     return   $custom_html . $html  ;

    }

	return  $html ;
}, 20, 4 );
