<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Main Wpforms validation functions file
 *
 */


/*
 * Check the form validtion.
*/ 

add_action('wpforms_process_before', function( $entry, $form_data ) {
  $error_message = cfas_get_error_text();
  $spam = false;
  $reversed = array_reverse($form_data['fields']);
  $last = $reversed[0];

  // ip
  $ip = efas_getRealIpAddr();
  $reason = "";
  // Country IP Check 
    $CountryCheck = CountryCheck($ip,$spam,$reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;  
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;

    //If country or ip is in blacklist
  if ( $spam ) {
    efas_add_to_log($type = "General",$reason, $entry['fields'] , "Wpforms" );
    wpforms()->process->errors[ $form_data['id'] ][ $last['id'] ] = cfas_get_error_text($message);
      return;
  }
  
  
}, 10, 2);


/*
 * Check the single line text field.
*/ 
add_action( 'wpforms_process_validate_text', 'cfas_validate_wpforms_text_name', 10, 3);
add_action( 'wpforms_process_validate_name', 'cfas_validate_wpforms_text_name', 10, 3);
function cfas_validate_wpforms_text_name( $field_id, $field_submit, $form_data ) {
    $field_submit = is_array($field_submit) ?  implode(" ",$field_submit) : $field_submit;
  	$field_value = strtolower($field_submit) ; 

    if ( empty( $field_value ) ) {
      return;
    }

    $validateTextField = validateTextField($field_value);
    $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
    $message = isset($validateTextField['message']) ?  $validateTextField['message'] : 0;

    if($spam ) {
      efas_add_to_log($type = "text/name","$spam", $_POST, "Wpforms");          
      wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = cfas_get_error_text($message);
      return;
    }
}


/*
 * Check the email field.
*/ 
add_action( 'wpforms_process_validate_email', function( $field_id, $field_submit, $form_data ) {
  	$field_value = strtolower($field_submit); 
    if(!$field_value){
      return;
    }
	$spam = checkEmailForSpam($field_value);
    if( $spam) {
      $error_message = cfas_get_error_text();
      efas_add_to_log($type = "email","Email $field_value is block $spam", $_POST, "Wpforms");
      wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = $error_message;
    }
}, 10, 3 );

/*
 * Check the phone field.
*/ 
add_action( 'wpforms_process_validate_phone', function( $field_id, $field_submit, $form_data ) {
  	$field_value = strtolower($field_submit); 
    if ( empty( $field_value ) ) {
        return false; // Not spam if the field is empty or no formats are provided.
    }
  	$checkTelForSpam = checkTelForSpam($field_value);
 	$reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : 0 ;      
 	$valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes" ;   
    $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : 0 ;  
  
    if(!$valid){
         efas_add_to_log($type = "tel","Telephone number '$field_value' not feet the given format ", $_POST, "Wpforms");
      	 wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = cfas_get_error_text($message);
      }
}, 10, 3 );


/*
 * Check the textarea field.
*/ 
add_action( 'wpforms_process_validate_textarea', function( $field_id, $field_submit, $form_data ) {
  	$field_value = strtolower($field_submit); 

    if(!$field_value){
      return;
    }
    $checkTextareaForSpam = checkTextareaForSpam($field_value);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;

    if ( $spam ) {
          efas_add_to_log($type = "textarea", $spam , $_POST, "Wpforms");
          wpforms()->process->errors[ $form_data['id'] ][ $field_id ] = cfas_get_error_text($message);
    }

}, 10, 3 );

//Disable because not stable yet 
//add_action('wpforms_display_submit_before', 'maspik_add_field_wpforms');
function maspik_add_field_wpforms($form_data) {
    if (!get_option('Maspik_human_verification')) {
        return;
    }
    $ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
    ?>
    <div class="maspik-captcha"></div>
    <style>
        .maspik-captcha {
            width: 1px;
            height: 1px;
        }
        form:focus-within .maspik-captcha {
            background-image: url('<?php echo esc_url($ajax_url); ?>');
            /* Add other background properties if necessary */
        }
    </style>
    <?php
}