<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// bricks

add_filter('bricks/form/validate', 'maspik_validate_bricks_form', 10, 2);

function maspik_validate_bricks_form($errors, $form) {
    $settings = $form->get_settings();
    $form_fields = $settings['fields'];
	$values   = $form->get_fields();
    $error_message = cfas_get_error_text();
    $spam = false;
    $reason ="";
    $ip =  efas_getRealIpAddr();

    // Country IP Check 
    $CountryCheck = CountryCheck($ip,$spam,$reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = $CountryCheck['reason']? $CountryCheck['reason'] : false ;
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;
    
    if ( $spam) {
        efas_add_to_log($type = "Country/IP",$reason, $values, "Bricks" );
        $errors[] = cfas_get_error_text($message);
        return $errors;
    }

    // Perform spam validation for each form field
    foreach ($form_fields as $field) {
        $field_id = $field['id'];
        $field_value = $form->get_field_value( $field_id );

      	if ($field['type'] === 'text' && ! empty($field_value)) {
            $validateTextField = validateTextField($field_value);
            $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0 ;
            $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : 0 ;
            if( $spam ) {
                efas_add_to_log($type = "text",$spam, $values, "Bricks");           
                $errors[] = cfas_get_error_text($message);
                return $errors;
            }
        }

      	if ($field['type'] === 'email' && ! empty($field_value)) {
            $spam = checkEmailForSpam($field_value);
            if ($spam) {
                efas_add_to_log($type = "email","Email $field_value is block $spam" , $values, "Bricks");
                $errors[] = $error_message;
                return $errors;
            }
        }

      	if ($field['type'] === 'tel' && ! empty($field_value)) {
            $checkTelForSpam = checkTelForSpam($field_value);
            $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
            $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
            $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
            if(!$valid) {
                efas_add_to_log($type = "tel", $reason , $values, "Bricks");
                $errors[] = cfas_get_error_text($message);
                return $errors;
            }
        }
        if ($field['type'] === 'textarea' && ! empty($field_value)) {
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
            $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
            if ($spam) {
                efas_add_to_log($type = "textarea",$spam, $values, "Bricks");
                $errors[] = cfas_get_error_text($message);
                return $errors;
            }
        }
    }

    return $errors;
}