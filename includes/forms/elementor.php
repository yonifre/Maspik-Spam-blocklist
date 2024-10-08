<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Main Elementor validation functions
 *
 */

add_action( 'elementor_pro/forms/validation', 'efas_validation_process' , 10, 2 );
function efas_validation_process ( $record, $ajax_handler ) {
  $spam = false;
  $reason ="";
  // ip
//  $ip = efas_getRealIpAddr();
  $meta = $record->get_form_meta( [ 'page_url', 'page_title', 'user_agent', 'remote_ip' ] );
  $ip =  $meta['remote_ip']['value'] ?   $meta['remote_ip']['value'] : efas_getRealIpAddr();

  // Country IP Check 
  $CountryCheck = CountryCheck($ip,$spam,$reason,$_POST);
  $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
  $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
  $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;
  $error_message = cfas_get_error_text($message);

  $NeedPageurl =  maspik_get_settings("NeedPageurl");   
  
    if ( efas_get_spam_api('NeedPageurl') ){
        $NeedPageurl = $NeedPageurl ? $NeedPageurl : efas_get_spam_api('NeedPageurl',"bool");
    }


  if( !array_key_exists('referrer', $_POST ) && $NeedPageurl ){
      $spam = true;
      $reason = "Page source url is empty";
      $error_message = cfas_get_error_text("block_empty_source");

  }
    
    $fields = $record->get_field(0);
    // Get the last element of the array
    $lastKey = end($fields);

    if ($lastKey['type'] === 'hidden') {
        // Move the internal pointer to the second-to-last element
        $secondLastKey = prev($fields);
        $lastKey = $secondLastKey;
    }

    // Retrieve the key of the last (or second-to-last) element
    $lastKeyId = key($fields);

  if ( $spam ) {
    efas_add_to_log($type = "General",$reason, $_POST['form_fields'] );
    $ajax_handler->add_error( $lastKeyId, $error_message );
  }
  
}

// Validate the Text fields.
add_action( 'elementor_pro/forms/validation/text', function( $field, $record, $ajax_handler ) {
    $field_value = strtolower($field['value']);
    if(!$field_value){
      return;
    }
	$validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
    $message = isset($validateTextField['message']) ? $validateTextField['message'] : 0;

    if( $spam ) {
        $error_message = cfas_get_error_text($message);
        efas_add_to_log($type = "text",$spam, $_POST['form_fields']);          
        $ajax_handler->add_error( $field['id'], $error_message );
    }
}, 10, 3 );

// Validate the Email fields.
add_action('elementor_pro/forms/validation/email', function ($field, $record, $ajax_handler) {
    
    $field_value = strtolower($field['value']);
    if (!$field_value) {
        return;
    }
	// check Email For Spam
	$spam = checkEmailForSpam($field_value);

    if ($spam) {
       	$error_message = cfas_get_error_text("emails_blacklist");
        efas_add_to_log($type = "email", "Email $field_value is block $spam", $_POST['form_fields']);
        $ajax_handler->add_error($field['id'], $error_message);
    }

}, 10, 3);


// preg_match the Tel field to the given format.
add_action( 'elementor_pro/forms/validation/tel', function( $field, $record, $ajax_handler ) {
  	$field_value = $field['value']; 
    if ( empty( $field_value ) ) {
        return false; // Not spam if the field is empty or no formats are provided.
    }
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	$valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
    $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
    
    if(!$valid){
      efas_add_to_log($type = "tel",$reason ,$_POST['form_fields']);
      $ajax_handler->add_error( $field['id'], cfas_get_error_text( $message) );
    }
    
}, 10, 3 );
           
// Validate the Textarea field.
add_action( 'elementor_pro/forms/validation/textarea', function( $field, $record, $ajax_handler ) {
  	$field_value = strtolower($field['value']); 

    if(!$field_value){
      return;
    }

    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam'])? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message'])? $checkTextareaForSpam['message'] : 0;
  	$error_message = cfas_get_error_text($message);

    if ( $spam ) {
          efas_add_to_log($type = "textarea",$spam, $_POST['form_fields']);
          $ajax_handler->add_error( $field['id'], $error_message );
    }

}, 10, 3 );

add_filter( 'elementor_pro/forms/wp_mail_message', function( $content ) {
  $add_country_to_emails = maspik_get_settings("add_country_to_emails", '', 'old')  == "yes";
  if( $content && $add_country_to_emails ){
    $countryName = maspik_add_country_to_submissions($linebreak = "<br>");
     return $content.$countryName;
  }
  return $content;
}, 10, 1 );

function add_maspikhp_html_to_elementor_form( $item, $item_index, $form ) {

    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $addhtml = "";

        $addhtml .= maspik_get_settings('maspikHoneypot') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="full-name-maspik-hp" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="'.maspik_HP_name().'" id="'.maspik_HP_name().'" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="Leave this field empty">
        </div>' : '';
        
        $addhtml .= maspik_get_settings('maspikYearCheck') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="Maspik-currentYear" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="">
        </div>' : '';
        
        $addhtml .= maspik_get_settings('maspikTimeCheck') ? '<div class="elementor-field-type-text elementor-field-group maspik-field">
            <label for="Maspik-exactTime" class="elementor-field-label">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="elementor-field elementor-size-sm elementor-field-textual" placeholder="">
        </div>' : '';

        $total_fields = count( $form->get_settings( 'form_fields' ) );

        if ( $item_index == $total_fields - 1 ) {
            echo $addhtml;
        }
    }

    return $item;
}
add_filter( 'elementor_pro/forms/render/item', 'add_maspikhp_html_to_elementor_form', 10, 3 );
