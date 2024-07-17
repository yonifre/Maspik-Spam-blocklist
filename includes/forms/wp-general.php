<?php

function maspik_comments_checker( array $data ){
  // Extracting data from the comment
  $content = strtolower($data['comment_content']);
  $email = strtolower($data['comment_author_email']);
  $name = strtolower($data['comment_author']);
  $comment_type = $data['comment_type'];

  $run = false;
  if( maspik_get_settings( "maspik_support_wp_comment" ) != "no" && $comment_type == 'comment'){
      $run = "ok";
  }else if( maspik_get_settings( "maspik_support_woocommerce_review" )  != "no" && $comment_type == 'review'){
  	  $run = "ok";
  }
  if(!$run){
    return $data;
  }

  
  $spam = false;
  // ip
  $ip = efas_getRealIpAddr();
  $reason = false;
    
  // Country IP Check 
  $CountryCheck = CountryCheck($ip,$spam,$reason);
  $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
  $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
  $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;

  // Name chack
  if( !empty($name) && !$spam ){   
	$validateTextField  = validateTextField($name);
    $spam = $reason = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0 ;
    $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : 0 ;
  } // end Name

    // Email Spam check.
  if( !empty($email) && !$spam ){   
	$spam = checkEmailForSpam($email);
    if($spam){
		$reason = "Email $email is block $spam";
    }
  } // end Email

  // $content
  if( !empty($content) && !$spam ){   
    $checkTextareaForSpam = checkTextareaForSpam($content);
    $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : 0;
    $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : 0;
  } // end $content

  if($spam){
    // If identified as spam, handle the action (logging, error message, etc.)
    $message = cfas_get_error_text($message);
    $args = array('response' => 200);
    efas_add_to_log($type = "Comments", $reason, $data, $comment_type);
    wp_die($message, $title = "Spam error", $args);
    exit(0);
  }
  
  return $data;
} 

add_filter('preprocess_comment','maspik_comments_checker');



/*
Wp registration
*/
add_action('register_form', 'Maspim_add_honeypot_to_register_form');
function Maspim_add_honeypot_to_register_form() {
    ?>
        <p style="opacity: 0; position: absolute; top: 0; left: 0; height: 0; width: 0; z-index: -1;"><label for="Maspikname">Leave this field unfill</label><input type="text" name="full_name_r" value="" tabindex="-1" autocomplete="off"></p>
    <?php
}
add_action('registration_errors', 'Maspik_validate_honeypot_field', 10, 3);
function Maspik_validate_honeypot_field($errors, $sanitized_user_login, $user_email) {
    if (!empty($_POST['full_name_r'])) {
    	$error_message = cfas_get_error_text();
        $errors->add('honeypot_error', "<strong>ERROR</strong>: $error_message (Maspik)");
    }
    return $errors;
}

function maspik_check_wp_registration_form($errors) {
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : sanitize_email($_POST['email']);
    $spam = false;
    $ip = efas_getRealIpAddr();
    $reason = "";

    // Country IP Check 
    $CountryCheck = CountryCheck($ip, $spam, $reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;


    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason = $spam;
        }
    }
    $error_message = cfas_get_error_text($message);
    if  ($spam && isset($_POST['wp-submit']) && maspik_get_settings("maspik_support_registration") != "no") {
        efas_add_to_log($type = "Registration", $reason, $_POST, 'Wp registration');
        $errors->add('maspik_error', $error_message);
    } else {
      efas_add_to_log($type = "Registration", "No Error", $_POST, 'No Error - WP registration');
    }

    return $errors;
}
add_filter('registration_errors', 'maspik_check_wp_registration_form', 10, 1);

///
add_action( 'woocommerce_register_form', 'maspik_register_form_honeypot_in_woocommerce_registration', 9999 );
 
function maspik_register_form_honeypot_in_woocommerce_registration() {
    echo '<p style="opacity: 0; position: absolute; top: 0; left: 0; height: 0; width: 0; z-index: -1;"><input type="text" name="full_name_r" value="" tabindex="-1" autocomplete="off"></p>';
}
 
add_filter( 'woocommerce_registration_errors', 'maspik_register_form_honeypot_check_in_woocommerce_registration', 9999, 3 );
 
function maspik_register_form_honeypot_check_in_woocommerce_registration( $errors, $username, $email ) {
    $error_message = cfas_get_error_text();

    if ( isset( $_POST['full_name_r'] ) && ! empty( $_POST['full_name_r'] ) ) {
      	efas_add_to_log($type = "Registration", 'registration-error-invalid-honeypot', $_POST, 'Woocommerce registration');
    	wp_die($error_message, "Spam error", array('response' => 200) );
    }

    if (! isset($_POST['billing_first_name']) && ! isset($_POST['full_name_r']) ) {
        if (  ! isset($_POST['full_name_r']) ) {
            efas_add_to_log("Registration", "Look like robot | Maspik boot check", $_POST, "Woocommerce registration");
    		wp_die($error_message, "Spam error", array('response' => 200) );
        }
    }

    $user_email = sanitize_email($email);
    $spam = false;
    $ip = efas_getRealIpAddr();
    $reason = "";

    // Country IP Check 
    $CountryCheck = CountryCheck($ip, $spam, $reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : false ;
    $reason = isset($CountryCheck['reason']) ? $CountryCheck['reason'] : false ;
    $message = isset($CountryCheck['message']) ? $CountryCheck['message'] : false ;

    $error_message = cfas_get_error_text($message);

    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason = $spam;
        }
    }
    if ($spam && maspik_get_settings("maspik_support_Woocommerce_registration") != "no") {
        efas_add_to_log($type = "Registration", $reason, $_POST, 'Woocommerce registration');
    	wp_die($error_message, "Spam error", array('response' => 200) );
    }else{
     	efas_add_to_log($type = "Registration", "No Error", $_POST, 'No Error - Woocommerce registration');
    }
    return $errors;

}