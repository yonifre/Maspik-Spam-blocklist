<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// formidable hook file

add_filter('frm_validate_entry', 'maspik_validate_formidable_general', 20, 2);
function maspik_validate_formidable_general($errors, $values){
    
  $spam = false;
  $reason ="";
  $ip =  efas_getRealIpAddr();
  
  // Country IP Check 
  $CountryCheck = CountryCheck($ip,$spam,$reason);
  $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
  $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
  $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;
    
  if ( $spam) {
    efas_add_to_log($type = "Country/IP",$reason, $_POST['item_meta'], "Formidable" );
    $errors['spam'] = cfas_get_error_text($message);
  }
return $errors;
}

// Add custom validation for Formidable text fields
function maspik_validate_formidable_text($errors, $posted_field, $posted_value, $args){
  	$field_value = strtolower($posted_value); 
    if ( empty( $field_value ) || $posted_field->type != 'text') {
      return $errors;
    }
	$validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
  
    if($spam ) {
      $message = isset($validateTextField['message']) ? $validateTextField['message'] : false ;
      efas_add_to_log($type = "text",$spam, $_POST, "Formidable");          
      $errors[ 'field'. $posted_field->id ] = cfas_get_error_text($message);
    }
    
	return $errors;
}
add_filter('frm_validate_field_entry', 'maspik_validate_formidable_text', 10, 4);


// Add custom validation for Formidable email fields
function maspik_validate_formidable_email($errors, $posted_field, $posted_value, $args){
  	$field_value = strtolower($posted_value); 
    if ( empty( $field_value ) || $posted_field->type != 'email') {
      return $errors;
    }
	// check Email For Spam
	$spam = checkEmailForSpam($field_value);

   if( $spam ) {
      $error_message = cfas_get_error_text();
      efas_add_to_log($type = "email","Email $field_value is block $spam" , $_POST, "Formidable");
      $errors[ 'field'. $posted_field->id ] = $error_message;
   }
   return $errors;
}
add_filter('frm_validate_field_entry', 'maspik_validate_formidable_email', 10, 4);

// Add custom validation for Formidable email fields
function maspik_validate_formidable_tel($errors, $posted_field, $posted_value, $args){
  	$field_value = $posted_value; 

    if ( empty( $field_value ) || $posted_field->type != 'phone' ) {
      return $errors;
    }
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	$valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
    $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  

  	if(!$valid){
        efas_add_to_log($type = "tel","Phone number $field_value not feet the given format ($reason)", $_POST, "Formidable");
        $errors[ 'field'. $posted_field->id ] = cfas_get_error_text($message);  
    } 

   return $errors;
}
add_filter('frm_validate_field_entry', 'maspik_validate_formidable_tel', 10, 4);


// Add custom validation for Formidable textarea fields
function maspik_validate_formidable_textarea($errors, $posted_field, $posted_value, $args){
  	$field_value = strtolower( $posted_value ) ; 
    if($field_value == "" || !$field_value || $posted_field->type != 'textarea'){
		return $errors;
    }
    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    if ( $spam ) {
      $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
      efas_add_to_log($type = "textarea",$spam, $_POST, "Formidable");
      $errors[ 'field'. $posted_field->id ] = cfas_get_error_text($message); 
      return $errors;
    }

	return $errors;
}
add_filter('frm_validate_field_entry', 'maspik_validate_formidable_textarea', 10, 4);


// maspik_add_text_to_mail_components
add_filter('frm_email_message', 'maspik_add_text_to_mail_formidable', 10, 2);
function maspik_add_text_to_mail_formidable($message, $atts) {
  $add_country_to_emails = get_option( 'add_country_to_emails' );
  if($message && $add_country_to_emails){
     $countryName = maspik_add_country_to_submissions($linebreak = "");
     $message = $message.$countryName;
    }
 return $message;
}