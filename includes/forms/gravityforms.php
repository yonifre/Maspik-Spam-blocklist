<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}



//general
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
  GFCommon::log_debug( __METHOD__ . '(): Running...' );
  $count = count($form['fields'])+1 ;
  $reason = "";
  $spam = false;
  if($field['id'] == $count){
  
    if ( !$result['is_valid'] ) {
      return $result;
    }

    // ip
    $ip = efas_getRealIpAddr();
    
  	// Country IP Check 
    $GeneralCheck = GeneralCheck($ip,$spam,$reason,$_POST,"gravityforms");
    $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
    $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
    $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
    $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;

      //If country or ip is in blacklist
    if ( $spam ) {
      efas_add_to_log($type = "General",$reason, $_POST ,'gravityforms', $message,  $spam_val);
      GFCommon::log_debug( __METHOD__ . '(): '.$reason.': ' . $reason );
      $result['is_valid'] = false;
      $result['message'] = cfas_get_error_text($message);
    }
  }
  return $result;
}, 10, 4 );


//text
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single Line Text.
  if ( $field->type == 'text' || $field->type == 'name' ) {
      $field_value = is_array($value) ?  implode(" ",$value) : $value;
      if ( !$result['is_valid'] ||  empty( $field_value ) ) {
          return $result;
      }
      $field_value = strtolower($field_value); 

    $validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
    $message = isset($validateTextField['message']) ? $validateTextField['message'] : 0;
    $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
    $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

      if( $spam ) {
          $error_message = cfas_get_error_text($message);
          efas_add_to_log($type = "text",$spam, $_POST,'gravityforms', $spam_lbl, $spam_val);          
          GFCommon::log_debug( __METHOD__ . '(): '.$spam.': ' . $field_value );
          $result['is_valid'] = false;
          $result['message']  = $error_message;
      }
  }     // end $field->type == 'text' || $field->type == 'name'

    return $result;
}, 10, 4 );

//email
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for email
  if ( $field->type == 'email' ) {
    if ( !$result['is_valid'] || empty( $value ) ) {
        return $result;
    }
  
  $field_value = is_array($value) ? array_values($value)[0]  : strtolower($value); 
	$spam = checkEmailForSpam($field_value);
  $spam_val = $field_value;

    if( $spam ) {
        $error_message = cfas_get_error_text();
        
        efas_add_to_log($type = "email","Email $field_value is block $spam", $_POST, "GravityForms", "emails_blacklist", $spam_val);
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $field_value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
  	}

  }
    return $result;
}, 10, 4 );

//phone
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Single phone.
  if ( $field->type == 'phone' && $field->phoneFormat != 'standard' ) {
      if ( !$result['is_valid'] || empty( $value ) ) {
        return $result;
      }
      $field_value = strtolower($value); 
        $checkTelForSpam = checkTelForSpam($field_value);
        $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
        $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
        $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
        $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
        $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;

      if(!$valid){
        $error_message = cfas_get_error_text($message); 
        efas_add_to_log($type = "tel", $reason, "Gravityforms", $spam_lbl, $spam_val);
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
      }
  }
  return $result;
}, 10, 4 );

//textarea    
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
    GFCommon::log_debug( __METHOD__ . '(): Running...' );
    // Only for Paragraph fields.
  if ( $field->type == 'textarea' ) {
  		$field_value = strtolower($value); 
    
      if ( !$result['is_valid']  || !$field_value) {
        return $result;
      }
    
    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
    $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
    $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

    if ( $spam ) {
   	    $error_message = cfas_get_error_text($message);
        efas_add_to_log($type = "textarea",$spam, $_POST, "Gravityforms", $spam_lbl, $spam_val);
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
    }
      
 
  }
    return $result;
}, 10, 4 );



add_filter('gform_submit_button', 'add_maspikhp_html_to_gform', 99 , 2 );
function add_maspikhp_html_to_gform($button, $form) {
    if ( is_admin() ){
        return $button;
    }
    $addhtml = "";

    if (maspik_get_settings('maspikHoneypot')) {
        $honeypot_name = maspik_HP_name();
        $addhtml .= '<div class="gfield gfield--type-text maspik-field">
            <label for="' . $honeypot_name . '" class="ginput_container_text">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="' . $honeypot_name . '" id="' . $honeypot_name . '" class="ginput_text" placeholder="Leave this field empty">
        </div>';
    }

    if (maspik_get_settings('maspikYearCheck')) {
        $addhtml .= '<div class="gfield gfield--type-text maspik-field">
            <label for="Maspik-currentYear" class="ginput_container_text">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="ginput_text" placeholder="">
        </div>';
    }

    if (maspik_get_settings('maspikTimeCheck')) {
        $addhtml .= '<div class="gfield gfield--type-text maspik-field">
            <label for="Maspik-exactTime" class="ginput_container_text">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="ginput_text" placeholder="">
        </div>';
    }

    return $addhtml . $button;
}

