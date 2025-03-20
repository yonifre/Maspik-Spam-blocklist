<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// Buddypress

/**
 * Check BuddyPress registration form for spam
 */
function maspik_check_bp_registration_form() {
    $error_message   = cfas_get_error_text();
    $spam            = false;
    $reason          = '';
    $message         = '';
    $spam_lbl        = '';
    $spam_val        = '';
  //  $user_email      = isset($_POST['signup_email']) ? sanitize_email($_POST['signup_email']) : '';
    $ip = maspik_get_real_ip();
    global $bp;

    $user_email = sanitize_email($bp->signup->email);
    $user_login = sanitize_text_field($bp->signup->username);

    // General Check
    if (!$spam) {
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST, "buddypress_registration");
        $spam         = $GeneralCheck['spam'] ?? false;
        $reason       = $GeneralCheck['reason'] ?? '';
        $message      = $GeneralCheck['message'] ?? '';
        $spam_val     = $GeneralCheck['value'] ?? '';
        $spam_lbl     = $GeneralCheck['reason'] ?? '';
        $type         = "General";
    }

    // Email check
    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason     = "Email $user_email is blocked";
            $spam_lbl   = 'emails_blacklist';
            $spam_val   = $user_email;
            $type       = "Email";
        }
    }

    if ($user_login && !$spam) {
        $validateTextField = validateTextField( $user_login );
        $spam  = $reason = isset( $validateTextField['spam'] ) ? $validateTextField['spam'] : false;
        $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : '';
        $spam_lbl = isset( $validateTextField['label'] ) ? $validateTextField['label'] : '';
        $spam_val = isset( $validateTextField['option_value'] ) ? $validateTextField['option_value'] : '';
        $type = "Username";
    }

    // Log and display error if spam is detected
    if ($spam) {  
        efas_add_to_log("$type", $reason, $_POST, 'BuddyPress registration', $spam_lbl, $spam_val);
        $error_message = cfas_get_error_text($message);
//        bp_core_add_message($error_message, 'error');
        if ($type == "Email") {
            $bp->signup->errors['signup_email'] = $error_message;
        } else {
            $bp->signup->errors['signup_username'] = $error_message;
        }
        return;
    }
}
add_action('bp_signup_validate', 'maspik_check_bp_registration_form');

/**
 * Add honeypot field to the BuddyPress registration form
 */
function maspik_add_honeypot_to_bp_registration_form() {
    
    if (maspik_get_settings('maspikHoneypot')) {

        echo '<div class="register-section maspik-field" id="maspik-honeypot-section" style="display: none;">
        <label for="full-name-maspik-hp">Leave this field empty</label>
            <input type="text" name="full-name-maspik-hp" id="full-name-maspik-hp" value="" tabindex="-1" autocomplete="off" />
        </div>';
    }

    if ( maspik_get_settings( 'maspikYearCheck' ) ) {
        echo '<div class="register-section maspik-field" style="display: none;">
            <label for="Maspik-currentYear" class="bp-form-control-label"></label>
            <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="buddypress-form-control" placeholder="">
        </div>';
    }


}
add_action('bp_before_registration_submit_buttons', 'maspik_add_honeypot_to_bp_registration_form', 9999);