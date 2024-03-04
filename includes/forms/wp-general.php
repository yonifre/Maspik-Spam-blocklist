<?php

function maspik_comments_checker( array $data ){
  // Extracting data from the comment
  $content = strtolower($data['comment_content']);
  $email = strtolower($data['comment_author_email']);
  $name = strtolower($data['comment_author']);
  $comment_type = $data['comment_type'];

  $run = false;
  if( get_option( "maspik_support_wp_comment" ) != "no" && $comment_type == 'comment'){
      $run = "ok";
  }else if( get_option( "maspik_support_woocommerce_review" )  != "no" && $comment_type == 'review'){
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
    $checkTextareaForSpam = checkTextareaForSpam($field_value);
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
            $reason = "Email = $user_email ";
        }
    }
    $error_message = cfas_get_error_text($message);
    if ($spam && isset($_POST['register']) && get_option("maspik_support_Woocommerce_registration") != "no") {
        efas_add_to_log($type = "Registration", $reason, $_POST, 'Woocommerce registration');
        wc_add_notice(__("$error_message"), 'contact-forms-anti-spam');
        return new WP_Error('registration-error', __("Registration Error", 'contact-forms-anti-spam'));
    } elseif ($spam && isset($_POST['wp-submit']) && get_option("maspik_support_registration") != "no") {
        efas_add_to_log($type = "Registration", $reason, $_POST, 'Wp registration');
        $errors->add('maspik_error', __("$error_message", 'contact-forms-anti-spam'));
    }

    return $errors;
}

add_filter('registration_errors', 'maspik_check_wp_registration_form', 10, 1);
add_filter('woocommerce_new_customer_data', 'maspik_check_wp_registration_form', 10, 1);