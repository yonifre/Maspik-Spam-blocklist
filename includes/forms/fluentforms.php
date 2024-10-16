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
  $GeneralCheck = GeneralCheck($ip,$spam,$reason,$extracted_data,"fluentforms");
  $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false ;
  $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : false ;
  $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : false ;
  $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;

   
  if ( $spam) {
    efas_add_to_log($type = "General",$reason, $_POST, "Fluent Forms", $message,  $spam_val );
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
    $message = isset($validateTextField['message']) ? $validateTextField['message'] : '';
    $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
    $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;

    if( $spam ) {
      $error_message = cfas_get_error_text($message);
      efas_add_to_log($type = "text",$spam, $formData, "Fluent Forms", $spam_lbl, $spam_val);          
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
    $spam_val = $field_value;

   if( $spam ) {
      $error_message = cfas_get_error_text();
      efas_add_to_log($type = "email","Email $field_value is block $spam" , $formData, "Fluent Forms", "emails_blacklist", $spam_val);
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
    $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
    $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;

  	if(!$valid){
        efas_add_to_log($type = "tel",$reason , $formData, "Fluent Forms", $spam_lbl, $spam_val);
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
    $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
    $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

    if ( $spam ) {
      efas_add_to_log($type = "textarea",$spam, $formData, "Fluent Forms", $spam_lbl, $spam_val);
      return $errorMessage = cfas_get_error_text($message); 
    }

	return $errorMessage;
}
add_filter('fluentform/validate_input_item_textarea', 'maspik_validate_fluentforms_textarea', 10, 5);


// maspik_add_text_to_mail_components fluentforms
//add_filter('fluentform/email_template_footer_text', 'maspik_add_text_to_mail_fluentforms', 10, 3);
function maspik_add_text_to_mail_fluentforms($footerText, $form, $notification) {
  $add_country_to_emails = maspik_get_settings("add_country_to_emails", '', 'old')  == "yes";
  if($footerText && $add_country_to_emails){
     $countryName = maspik_add_country_to_submissions($linebreak = "");
     $footerText = $footerText.$countryName;
    }
 return $footerText;
}


// add Maspik Honeypot fields to fluentform
add_filter('fluentform/rendering_form', function($form){
    
    $last_field = end($form->fields['fields']);
    // Retrieve the element and index values
    $last_element = isset($last_field['element']) ? $last_field['element'] : null;
    $last_index = isset($last_field['index']) ? $last_field['index'] : null;
    
    add_filter("fluentform/rendering_field_html_$last_element", function ($html, $data, $form) {
        
        if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
            $custom_html = "";

            if (maspik_get_settings('maspikHoneypot')) {
                $custom_html .= '<div class="ff-el-group maspik-field">
                    <label for="full-name-maspik-hp" class="ff-el-input--label">Leave this field empty</label>
                    <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="ff-el-form-control" placeholder="Leave this field empty">
                </div>';
            }

            if (maspik_get_settings('maspikYearCheck')) {
                $custom_html .= '<div class="ff-el-group maspik-field">
                    <label for="Maspik-currentYear" class="ff-el-input--label">Leave this field empty</label>
                    <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="ff-el-form-control" placeholder="">
                </div>';
            }

            if (maspik_get_settings('maspikTimeCheck')) {
                $custom_html .= '<div class="ff-el-group maspik-field">
                    <label for="Maspik-exactTime" class="ff-el-input--label">Leave this field empty</label>
                    <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="ff-el-form-control" placeholder="">
                </div>';
            }
         return   $html . $custom_html;
           
        }
            
        return   $html;
            
    }, 10, 3);  
    
   return $form;
}, 10, 1);




