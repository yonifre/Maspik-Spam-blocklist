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
    $CountryCheck = CountryCheck($ip,$spam,$reason,$_POST);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;

      //If country or ip is in blacklist
    if ( $spam ) {
      efas_add_to_log($type = "General",$reason, $_POST ,'gravityforms');
      GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $reason );
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

      if( $spam ) {
          $error_message = cfas_get_error_text($message);
          efas_add_to_log($type = "text",$spam, $_POST,'gravityforms');          
          GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $field_value );
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

    if( $spam ) {
        $error_message = cfas_get_error_text();
        efas_add_to_log($type = "email","Email $field_value is block $spam", $_POST, "GravityForms");
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

      if(!$valid){
        $error_message = cfas_get_error_text($message); 
        efas_add_to_log($type = "tel", $reason, "Gravityforms");
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
    if ( $spam ) {
   	    $error_message = cfas_get_error_text($message);
        efas_add_to_log($type = "textarea",$spam, $_POST, "Gravityforms");
        GFCommon::log_debug( __METHOD__ . '(): '.$error_message.': ' . $value );
        $result['is_valid'] = false;
        $result['message']  = $error_message;    
    }
      
 
  }
    return $result;
}, 10, 4 );


//Disable because not stable yet 
//add_filter('gform_submit_button', 'maspik_add_spampixel_to_gravity_form', 10 , 2 );
function maspik_add_spampixel_to_gravity_form($button, $form) {
    if (!get_option('Maspik_human_verification')) {
        return $button;
    }
    $ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
    $spampixel = '<div class="maspik-captcha"></div>';
    $spampixel .=
        "<style>
            .maspik-captcha {
                width: 1px;
                height: 1px;
                position: absolute;
                opacity: 0;
            }
            form:focus-within .maspik-captcha {
                background-image: url('$ajax_url');
            }
        </style>";

    return $button.$spampixel;

}