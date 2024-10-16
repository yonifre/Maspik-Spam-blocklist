<?php

function maspik_comments_checker(array $data) {
    // Extracting data from the comment
    $content = strtolower($data['comment_content']);
    $email = strtolower($data['comment_author_email']);
    $name = strtolower($data['comment_author']);
    $comment_type = $data['comment_type'];

    // Determine if the check should run based on settings and comment type
    $run = false;
    if (maspik_get_settings("maspik_support_wp_comment") !== "no" && $comment_type === 'comment') {
        $run = true;
    } else if (maspik_get_settings("maspik_support_woocommerce_review") !== "no" && $comment_type === 'review') {
        $run = true;
    }
    if (!$run) {
        return $data;
    }
    if ( current_user_can( 'edit_posts' ) ) {
        // If user can edit posts
        // skip spam check
        return $data;
    }

    $spam = false;
    $ip = efas_getRealIpAddr();
    $reason = '';

    // Country IP + HP Check
    $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,$comment_type);
    $spam = $GeneralCheck['spam'] ?? false;
    $reason = $GeneralCheck['reason'] ?? '';
    $message = $GeneralCheck['message'] ?? '';
    $spam_val = $GeneralCheck['value'] ?? '';
    $spam_lbl = $GeneralCheck['reason'] ?? '';

    // Name check
    if (!empty($name) && !$spam) {
        $validateTextField = validateTextField($name);
        $spam = $validateTextField['spam'] ?? false;
        $reason = $validateTextField['reason'] ?? '';
        $message = $validateTextField['message'] ?? '';
        $spam_lbl = $validateTextField['label'] ?? '';
        $spam_val = $validateTextField['option_value'] ?? '';
    }

    // Email Spam check
    if (!empty($email) && !$spam) {
        $spam = checkEmailForSpam($email);
        if ($spam) {
            $reason = "Email $email is blocked";
            $spam_lbl = 'emails_blacklist';
            $spam_val = $email;
        }
    }

    // Content check
    if (!empty($content) && !$spam) {
        $checkTextareaForSpam = checkTextareaForSpam($content);
        $spam = $checkTextareaForSpam['spam'] ?? false;
        $message = $checkTextareaForSpam['message'] ?? '';
        $reason = $spam ?? '';
        $spam_lbl = $checkTextareaForSpam['label'] ?? '';
        $spam_val = $checkTextareaForSpam['option_value'] ?? '';
    }

    if ($spam) {
        // If identified as spam, handle the action (logging, error message, etc.)
        $message = cfas_get_error_text($message);
        $args = ['response' => 200];
        efas_add_to_log("Comments", $reason, $data, $comment_type, $spam_lbl, $spam_val);
        wp_die($message, "Spam error", $args);
    }

    return $data;
}

add_filter('preprocess_comment', 'maspik_comments_checker');

/**
 * Add honeypot field to the registration form
 */
function maspik_add_honeypot_to_register_form() {
    ?>
    <p style="opacity: 0; position: absolute; top: 0; left: 0; height: 0; width: 0; z-index: -1;">
        <label for="full-name-maspik-hp">Leave this field unfilled</label>
        <input type="text" name="full-name-maspik-hp" value="" tabindex="-1" autocomplete="off">
    </p>
    <?php
}
add_action('register_form', 'maspik_add_honeypot_to_register_form');

/**
 * Check registration form for spam
 */
function maspik_check_wp_registration_form($errors) {
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : sanitize_email($_POST['email']);
    $spam = false;
    $ip = efas_getRealIpAddr();
    $reason = "";
    $message = "";
    $maspikHoneypot = maspik_get_settings('maspikHoneypot');

    if ($maspikHoneypot && !empty($_POST['full-name-maspik-hp'])) {
        $spam = true;
        $reason = "Honeypot Triggered";
    }

    // Country IP Check
    if (!$spam) {
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,"wp_registration");
        $spam = $GeneralCheck['spam'] ?? false;
        $reason = $GeneralCheck['reason'] ?? '';
        $message = $GeneralCheck['message'] ?? '';
        $spam_val = $GeneralCheck['value'] ?? '';
        $spam_lbl = $GeneralCheck['reason'] ?? '';
    }

    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason = "Email $user_email is blocked";
            $spam_lbl = 'emails_blacklist';
            $spam_val = $user_email;
        }
    }
    $error_message = cfas_get_error_text($message);
    if ($spam && isset($_POST['wp-submit']) && maspik_get_settings("maspik_support_registration") !== "no") {
        efas_add_to_log("Registration", $reason, $_POST, 'WP registration', $spam_lbl, $spam_val);
        $errors->add('maspik_error', $error_message);
    }

    return $errors;
}
add_filter('registration_errors', 'maspik_check_wp_registration_form', 10, 1);

/**
 * Add honeypot field to the WooCommerce registration form
 */
add_action('woocommerce_register_form', 'maspik_add_honeypot_to_register_form', 9999);

/**
 * Check WooCommerce registration form for spam
 */
function maspik_register_form_honeypot_check_in_woocommerce_registration($errors, $username, $email) {
    $error_message = cfas_get_error_text();
    $maspikHoneypot = maspik_get_settings('maspikHoneypot');
    if ($maspikHoneypot && !empty($_POST['full-name-maspik-hp'])) {
        efas_add_to_log("Registration", 'Honeypot Triggered', $_POST, 'Woocommerce registration');
        wp_die($error_message, "Spam error", ['response' => 200]);
    }

    $user_email = sanitize_email($email);
    $spam = false;
    $ip = efas_getRealIpAddr();
    $reason = "";

    // Country IP Check
    $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,"woocommerce_registration");
    $spam = $GeneralCheck['spam'] ?? false;
    $reason = $GeneralCheck['reason'] ?? '';
    $message = $GeneralCheck['message'] ?? '';

    $error_message = cfas_get_error_text($message);

    if ($user_email && !$spam) {
        $spam = checkEmailForSpam($user_email);
        if ($spam && !$reason) {
            $reason = "Email $user_email is blocked";
        }
    }
    if ($spam && maspik_get_settings("maspik_support_Woocommerce_registration") !== "no") {
        efas_add_to_log("Registration", $reason, $_POST, 'Woocommerce registration');
        wp_die($error_message, "Spam error", ['response' => 200]);
    }
    return $errors;
}
add_filter('woocommerce_registration_errors', 'maspik_register_form_honeypot_check_in_woocommerce_registration', 9999, 3);




function add_custom_html_to_comment_form( $submit_button, $args ) {
    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $custom_html = "";

        if (maspik_get_settings('maspikHoneypot')) {
            $custom_html .= '<div class="comment-form-honeypot maspik-field">
                <label for="full-name-maspik-hp" class="comment-form-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="comment-form-input" placeholder="Leave this field empty">
            </div>';
        }

        if (maspik_get_settings('maspikYearCheck')) {
            $custom_html .= '<div class="comment-form-year-check maspik-field">
                <label for="Maspik-currentYear" class="comment-form-label">Leave this field with corrent year</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="comment-form-input" placeholder="">
            </div>';
        }

        if (maspik_get_settings('maspikTimeCheck')) {
            $custom_html .= '<div class="comment-form-time-check maspik-field">
                <label for="Maspik-exactTime" class="comment-form-label">Leave this with this number</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="comment-form-input" placeholder="">
            </div>';
        }

        $submit_before = $custom_html;
        return $submit_before . $submit_button;
    }
    return $submit_button;
}

add_filter( 'comment_form_submit_button', 'add_custom_html_to_comment_form', 10, 2 );
