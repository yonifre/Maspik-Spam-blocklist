<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Playground Form

add_action('wp_ajax_maspik_handle_playground_form', 'maspik_handle_playground_form');
add_action('wp_ajax_nopriv_maspik_handle_playground_form', 'maspik_handle_playground_form');

function maspik_handle_playground_form() {
    

    //check_ajax_referer('contact_form_nonce', 'nonce');

    $name = sanitize_text_field($_POST['userName']);
    $email = sanitize_email($_POST['userEmail']);
    $tel = sanitize_text_field($_POST['tel']);
    $content = sanitize_textarea_field($_POST['content']);

    // Example: Save form data to database or send an email
    $success = 1;
    $name_spam = "";
    $email_spam = "";
    $tel_spam = "";
    $textarea_spam = "";
    if (empty($name) && empty($tel) && empty($email) && empty($content)) {
        // Process the form data (e.g., save to database, send email, etc.)
        $response = array(
            'status' => 'Error',
            'name' => '',
            'email' => '',
            'tel' => '',
            'textarea' => '',
            'message' => 'Please fill in at least one of the fields.'
        );
        wp_send_json($response);
        wp_die();
    }
    $spam = false;
    // ip
    $ip = efas_getRealIpAddr();
    $reason = false;
    // Country IP Check 
    $CountryCheck = CountryCheck($ip,$spam,$reason);
    $spam = isset($CountryCheck['spam']) ? $CountryCheck['spam'] : 0;
    $Country_reason = $CountryCheck['reason'] ? "<b>SPAM - ".$CountryCheck['reason']."</b><br>" : "";  
    if($name){
        $validateTextField = validateTextField($name);
        $name_spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : 0;
        $name_spam = $name_spam ? "SPAM - ".$name_spam : "";
    }
    if($email){
         $email_spam = checkEmailForSpam($email);
         $email_spam = $email_spam ? "SPAM - Email $email is block $email_spam" : "";
    }
    if($tel){
         $tel_spam = checkTelForSpam($tel);  
         $tel_spam_reason = $tel_spam['reason'];      
         $tel_spam_valid = $tel_spam['valid'];   
         $tel_spam = $tel_spam_valid ? "" : $tel_spam_reason;
    }
    if($content){
        $checkTextareaForSpam = checkTextareaForSpam($content);
        $textarea_spam = isset($checkTextareaForSpam['spam'])? $checkTextareaForSpam['spam'] : 0;
        $textarea_spam = $textarea_spam ? "SPAM - ".$textarea_spam : "";       
    }
    $message = 'Spam check was finish - No spam found.';
    if( $name_spam || $email_spam || $tel_spam || $textarea_spam ){
        $message = 'Spam check was finish - See note above.';
    }
    // Prepare response
    if ($success) {
        $response = array(
            'status' => 'success',
            'name' => $name_spam,
            'email' => $email_spam,
            'tel' => $tel_spam,
            'textarea' => $textarea_spam,
            'message' => $Country_reason.$message
        );
    } else {
        $response = array(
            'status' => 'Error',
            'name' => $name_spam,
            'email' => $email_spam,
            'tel' => $tel_spam,
            'textarea' => $textarea_spam,
            'message' => 'error occurred (002).'
        );
    }

    // Return JSON response
    wp_send_json($response);
    wp_die();
}

