<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// Fluent Forms hook file


add_filter('fluentform/validation_errors', 'maspik_validate_fluentform_general', 10, 4);
function maspik_validate_fluentform_general( $errors, $formData, $form, $fields){
    
  $spam = false;
  $reason ="";
  // ip
  $ip =  efas_getRealIpAddr();

// For HP
parse_str($_POST['data'], $parsed_data);
$extracted_data = array(
    'Maspik-exactTime' => isset($parsed_data['Maspik-exactTime']) ? $parsed_data['Maspik-exactTime'] : false,
    'Maspik-currentYear' => isset($parsed_data['Maspik-currentYear']) ? $parsed_data['Maspik-currentYear'] : false,
    'full-name-maspik-hp' => isset($parsed_data['full-name-maspik-hp']) ? $parsed_data['full-name-maspik-hp'] : false
);


  // Country IP Check 
  $CountryCheck = CountryCheck($ip,$spam,$reason,$extracted_data);
  $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
  $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
  $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;

   
  if ( $spam) {
    efas_add_to_log($type = "General",$reason, $_POST, "Fluent Forms" );
    $errors['spam'] = cfas_get_error_text($message);
  }
return $errors;
}


// Add custom validation for Fluentforms text fields
function maspik_validate_fluentforms_text($errorMessage, $field, $formData, $fields, $form){
    $fieldName = $field['name'];
    if (empty($formData[$fieldName])) {
        return $errorMessage;
    }
    $field_value = is_array($formData[$fieldName])  ?  strtolower( implode( " ", $formData[$fieldName] ) ) : strtolower( $formData[$fieldName] ) ; 

	$validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
    $message = $validateTextField['message'];

    if( $spam ) {
      $error_message = cfas_get_error_text($message);
      efas_add_to_log($type = "text",$spam, $formData, "Fluent Forms");          
      $errorMessage = $error_message;
    }
    
	return $errorMessage;
}
add_filter('fluentform/validate_input_item_input_text', 'maspik_validate_fluentforms_text', 10, 5);


// Add custom validation for fluentforms email fields
function maspik_validate_fluentforms_email($errorMessage, $field, $formData, $fields, $form){
    $fieldName = $field['name'];
    if (empty($formData[$fieldName])) {
        return $errorMessage;
    }
    $field_value = strtolower( $formData[$fieldName]); 

    $spam = checkEmailForSpam($field_value);

   if( $spam ) {
      $error_message = cfas_get_error_text();
      efas_add_to_log($type = "email","Email $field_value is block $spam" , $formData, "Fluent Forms");
      $errorMessage = $error_message;
   }
   return $errorMessage;
}
add_filter('fluentform/validate_input_item_input_email', 'maspik_validate_fluentforms_email', 10, 5);

// Add custom validation for Tel fields
function maspik_validate_fluentforms_tel($errorMessage, $field, $formData, $fields, $form){
    $fieldName = $field['name'];
    if (empty($formData[$fieldName])) {
        return $errorMessage;
    }
    $field_value = strtolower( $formData[$fieldName]); 
  
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	$valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
    $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  

  	if(!$valid){
        efas_add_to_log($type = "tel",$reason , $formData, "Fluent Forms");
        $errorMessage = cfas_get_error_text($message);  
    } 

   return $errorMessage;
}
add_filter('fluentform/validate_input_item_phone', 'maspik_validate_fluentforms_tel', 10, 5);


// Add custom validation for fluentforms textarea fields
function maspik_validate_fluentforms_textarea($errorMessage, $field, $formData, $fields, $form){
    $fieldName = $field['name'];
    if (empty($formData[$fieldName])) {
        return $errorMessage;
    }
    $field_value = strtolower( $formData[$fieldName]); 

    $error_message = cfas_get_error_text(); 
    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;

    if ( $spam ) {
      efas_add_to_log($type = "textarea",$spam, $formData, "Fluent Forms");
      return $errorMessage = cfas_get_error_text($message); 
    }

	return $errorMessage;
}
add_filter('fluentform/validate_input_item_textarea', 'maspik_validate_fluentforms_textarea', 10, 5);


// maspik_add_text_to_mail_components fluentforms
add_filter('fluentform/email_template_footer_text', 'maspik_add_text_to_mail_fluentforms', 10, 3);
function maspik_add_text_to_mail_fluentforms($footerText, $form, $notification) {
  $add_country_to_emails = maspik_get_settings("add_country_to_emails", '', 'old')  == "yes";
  if($footerText && $add_country_to_emails){
     $countryName = maspik_add_country_to_submissions($linebreak = "");
     $footerText = $footerText.$countryName;
    }
 return $footerText;
}