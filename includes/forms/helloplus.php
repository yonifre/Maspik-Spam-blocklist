<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Main HelloPlus validation functions
 *
 */

add_action( 'hello_plus/forms/validation', 'maspik_validation_process_hello_plus', 10, 2 );
function maspik_validation_process_hello_plus( $record, $ajax_handler ) {

    $is_spam = false;
    $error_fields = array();
    $form_data = array_map('sanitize_text_field', $_POST['form_fields'] ?? []);
    $NeedPageurl = maspik_get_settings( 'NeedPageurl' );   
    // Get all form fields
    $form_fields = $record->get( 'fields' );
    $keys = array_keys($form_fields);
    $lastKeyId = end($keys);
    
    
    
    // Loop through all fields
    foreach ( $form_fields as $field_id => $field ) {
        $field_id = $field['id']; // Custom ID of the field
        $field_value = isset( $field['value'] ) && !is_array( $field['value'] ) ? sanitize_text_field( $field['value'] ) : '';
        $field_type = $field['type'];

        if ( empty( $field_value ) ) {
            continue;
        }

        switch ( $field_type ) {
            case 'text':
                // Text Field Validation
                $validateTextField = validateTextField($field_value);
                $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
                $message = isset($validateTextField['message']) ? $validateTextField['message'] : 0;
                $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
                $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

                if ( $spam ) {
                    $error_message = cfas_get_error_text( $validateTextField['message'] );
                    efas_add_to_log( 'text', $validateTextField['spam'], $form_data, 'Hello Plus', $validateTextField['label'], $validateTextField['option_value'] );
                    $ajax_handler->add_error($field_id, $error_message);
                    return;
                }
                break;

            case 'email':
                // Check Email For Spam
                $spam = checkEmailForSpam($field_value);
                $spam_val = $field_value;

                if ($spam) {
                    $error_message = cfas_get_error_text("emails_blacklist");
                    efas_add_to_log($type = "email", "Email $field_value is block $spam", $form_data,"Hello Plus", "emails_blacklist", $spam_val);
                    $ajax_handler->add_error($field_id, $error_message);
                    return;
                }
                break;

            case 'ehp-tel':
                // Tel Field Validation
                $checkTelForSpam = checkTelForSpam($field_value);
                $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : true;
                if(!$valid){
                  $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : false;
                  $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
                  $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;
                  $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : "tel_formats" ;
      
                  $error_message = cfas_get_error_text($message);
                  efas_add_to_log($type = "tel", $reason, $form_data,"Hello Plus", $spam_lbl, $spam_val);
                  $ajax_handler->add_error($field_id, $error_message);
                  return;
                }
                break;

            case 'textarea':
                // Textarea Field Validation
                $checkTextareaForSpam = checkTextareaForSpam($field_value);
                $spam = isset($checkTextareaForSpam['spam'])? $checkTextareaForSpam['spam'] : 0;
                $message = isset($checkTextareaForSpam['message'])? $checkTextareaForSpam['message'] : 0;
                $error_message = cfas_get_error_text($message);
                $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
                $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;
            
                if ( $spam ) {
                      efas_add_to_log($type = "textarea",$spam, $form_data,"Hello Plus", $spam_lbl, $spam_val);
                      $ajax_handler->add_error($field_id, $error_message);
                      return;
                }
                break;

            // end
        }
    }

        // General Check
    if(!$spam ){
        $meta = $record->get_form_meta( [ 'page_url', 'remote_ip' ] );
        $ip =  $meta['remote_ip']['value'] ? $meta['remote_ip']['value'] : maspik_get_real_ip();
        // Country IP Check 
        $GeneralCheck = GeneralCheck($ip,$spam,$reason,$_POST,"HelloPlus");
        $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
        $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
        $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
        $error_message = cfas_get_error_text($message);
        $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;
        if($spam){
        efas_add_to_log($type = "General",$reason, $form_data,"Hello Plus", $message,  $spam_val);
        $ajax_handler->add_error( $lastKeyId, $error_message );
        return;
        }
    }

    if ( efas_get_spam_api( 'NeedPageurl' ) ) {
        $NeedPageurl = $NeedPageurl ? $NeedPageurl : efas_get_spam_api( 'NeedPageurl', 'bool' );
    }

    if ( ! isset( $_POST['referrer'] ) && $NeedPageurl ) {
        $is_spam = true;
        $reason = 'Page source url is empty';
        $message_key = 'block_empty_source';
        $error_message = cfas_get_error_text( $message_key );
        $spam_val = $reason;
        
        efas_add_to_log( 'General', $reason, $form_data, 'Hello Plus', $message_key, $spam_val );
        $ajax_handler->add_error( $lastKeyId, $error_message );
        return;
    }


}