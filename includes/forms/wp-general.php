<?php

// WP comments and WooCommerce reviews
function maspik_comments_checker(array $data) {
    // Extracting data from the comment with validation
    $content = isset($data['comment_content']) ? strtolower(sanitize_text_field($data['comment_content'])) : '';
    $email = isset($data['comment_author_email']) ? strtolower(sanitize_email($data['comment_author_email'])) : '';
    $name = isset($data['comment_author']) ? strtolower(sanitize_text_field($data['comment_author'])) : '';
    $comment_type = isset($data['comment_type']) ? sanitize_text_field($data['comment_type']) : 'comment';

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
    $ip = maspik_get_real_ip();
    $reason = '';

    // Country IP + HP Check
    $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,$comment_type);
    $spam = $GeneralCheck['spam'] ?? false;
    $reason = $GeneralCheck['reason'] ?? '';
    $message = $GeneralCheck['message'] ?? '';
    $spam_val = $GeneralCheck['value'] ?? '';
    $spam_lbl = $GeneralCheck['message'] ?? '';
    $type = "General";

    // Name check
    if (!empty($name) && !$spam) {
        $validateTextField = validateTextField($name);
        $spam =  $reason = $validateTextField['spam'] ?? false;
        $message = $validateTextField['message'] ?? '';
        $spam_lbl = $validateTextField['label'] ?? '';
        $spam_val = $validateTextField['option_value'] ?? '';
        $type = "Name";
    }

    // Email Spam check
    if (!empty($email) && !$spam) {
        $spam = checkEmailForSpam($email);
        if ($spam) {
            $reason = "Email $email is blocked";
            $spam_lbl = 'emails_blacklist';
            $spam_val = $email;
            $type = "Email";
        }
    }

    // Content check
    if (!empty($content) && !$spam) {
        $checkTextareaForSpam = checkTextareaForSpam($content);
        $spam =  $reason = $checkTextareaForSpam['spam'] ?? false;
        $message = $checkTextareaForSpam['message'] ?? '';
        $spam_lbl = $checkTextareaForSpam['label'] ?? '';
        $spam_val = $checkTextareaForSpam['option_value'] ?? '';
        $type = "Content";
    }

    if ($spam) {
        // If identified as spam, handle the action (logging, error message, etc.)
        $error_message = cfas_get_error_text($message);
        $args = ['response' => 200];
        efas_add_to_log("$type", $reason, $data, $comment_type, $spam_lbl, $spam_val);

        wp_die($error_message, "Spam error", $args);
    }

    return $data;
}

add_filter('preprocess_comment', 'maspik_comments_checker');


function add_custom_html_to_comment_form( $submit_button, $args ) {
    if ( maspik_get_settings('maspikHoneypot') || maspik_get_settings('maspikTimeCheck') || maspik_get_settings('maspikYearCheck') ) {
        $custom_html = "";

        if (maspik_get_settings('maspikHoneypot')) {
            $custom_html .= '<div class="comment-form maspik-field" style="display: none;">
                <label for="full-name-maspik-hp" class="comment-form-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autocomplete="new-password" autocomplete="false" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="comment-form-input" placeholder="Leave this field empty" data-form-type="other" data-lpignore="true">
            </div>';
        }

        if (maspik_get_settings('maspikYearCheck')) {
            $custom_html .= '<div class="comment-form maspik-field" style="display: none;">
                <label for="Maspik-currentYear" class="comment-form-label">Leave this field with corrent year</label>
                <input size="1" type="text" autocomplete="off" autocomplete="new-password" autocomplete="false" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="comment-form-input" placeholder="" data-form-type="other" data-lpignore="true">
            </div>';
        }

        $submit_before = $custom_html;
        return $submit_before . $submit_button;
    }
    return $submit_button;
}

add_filter( 'comment_form_submit_button', 'add_custom_html_to_comment_form', 10, 2 );



/**
 * Check WP registration form for spam
 */
function maspik_check_wp_registration_form($errors) {

    if ( maspik_get_settings("maspik_support_registration") !== "no" ) {
        $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : sanitize_email($_POST['email']);
        $user_login = isset($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : sanitize_text_field($_POST['username']);
        
        $spam = false;
        $ip = maspik_get_real_ip();
        $reason = "";
        $message = "";

        // General Check
        if (!$spam) {
            $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,"wp_registration");
            $spam = $GeneralCheck['spam'] ?? false;
            $reason = $GeneralCheck['reason'] ?? '';
            $message = $GeneralCheck['message'] ?? '';
            $spam_val = $GeneralCheck['value'] ?? '';
            $spam_lbl = $GeneralCheck['message'] ?? '';
            $type = "General";
        }

        // Email check
        if ($user_email && !$spam) {
            $spam = checkEmailForSpam($user_email);
            if ($spam && !$reason) {
                $reason = "Email $user_email is blocked";
                $spam_lbl = 'emails_blacklist';
                $spam_val = $user_email;
                $type = "Email";
            }
        }

        // Username check
        if ($user_login && !$spam) {
            $validateTextField = validateTextField( $user_login );
            $spam  = $reason = isset( $validateTextField['spam'] ) ? $validateTextField['spam'] : false;
            $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : '';
            $spam_lbl = isset( $validateTextField['label'] ) ? $validateTextField['label'] : '';
            $spam_val = isset( $validateTextField['option_value'] ) ? $validateTextField['option_value'] : '';
            $type = "Username";
        }
    
        $error_message = cfas_get_error_text($message);
        if ( $spam && isset($_POST['wp-submit']) ) {
            efas_add_to_log("$type", $reason, $_POST, 'WP registration', $spam_lbl, $spam_val);
            $errors->add('maspik_error', $error_message);
        }

    }

    return $errors;
}
add_filter('registration_errors', 'maspik_check_wp_registration_form', 10, 1);


/**
 * Check WooCommerce registration form for spam
 */
function maspik_register_form_honeypot_check_in_woocommerce_registration($errors, $username, $email) {
    if ( maspik_if_woo_support_is_enabled() ) {

        $user_email = sanitize_email($email);
        $user_login = sanitize_text_field($username);
        $spam = false;
        $ip = maspik_get_real_ip();
        $reason = "";

        // Country IP Check
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,"woocommerce_registration");
        $spam = $GeneralCheck['spam'] ?? false;
        $reason = $GeneralCheck['reason'] ?? '';
        $message = $GeneralCheck['message'] ?? '';
        $spam_val = $GeneralCheck['message'] ?? '';
        $error_message = cfas_get_error_text($message);
        $type = "General";
        if ($user_email && !$spam) {
            $spam = checkEmailForSpam($user_email);
            if ($spam && !$reason) {
                $reason = $spam;
                $spam_lbl = 'emails_blacklist';
                $spam_val = $user_email;
                $type = "Email";
            }
        }

        // do user_login check 
        if ($user_login && !$spam) {
            $validateTextField = validateTextField( $user_login );
            $spam  = $reason = isset( $validateTextField['spam'] ) ? $validateTextField['spam'] : false;
            $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : '';
            $spam_lbl = isset( $validateTextField['label'] ) ? $validateTextField['label'] : '';
            $spam_val = isset( $validateTextField['option_value'] ) ? $validateTextField['option_value'] : '';
            $type = "Username";
        }   


        if ($spam) {
            $error_message = cfas_get_error_text($message);
            efas_add_to_log("$type", $reason, $_POST, 'Woocommerce registration', $spam_lbl, $spam_val);
            wp_die($error_message, "Spam error", ['response' => 200]);
        }
    }
    return $errors;
}
add_filter('woocommerce_registration_errors', 'maspik_register_form_honeypot_check_in_woocommerce_registration', 9999, 3);




/**
 * Add honeypot field to the woocommerce + WP registration form
 */
function maspik_add_honeypot_to_register_form() {
    //if maspik_support_registration is no, and WooCommerce is not supported, don't add the honeypot
    if (maspik_get_settings("maspik_support_registration") === "no" && maspik_if_woo_support_is_enabled() === false) {
        return;
    }
    ?>
        <p class="form-row maspik-field" style="display: none;" aria-hidden="true">
            <label for="full-name-maspik-hp">Leave this field unfilled</label>
            <input type="text" 
                   name="full-name-maspik-hp" 
                   id="full-name-maspik-hp"
                   value="" 
                   tabindex="-1" 
                   autocomplete="off"
                   autocorrect="off"
                   autocapitalize="off"
                   spellcheck="false"
                   data-form-type="other"
                   aria-hidden="true">
        </p>
        <p class="form-row maspik-field" style="display: none;" aria-hidden="true">
            <label for="Maspik-currentYear">Leave this field unfilled</label>
            <input type="text" 
                   name="Maspik-currentYear" 
                   id="Maspik-currentYear"
                   value="<?php echo intval(date('Y')); // adding current year for admin area ?>"
                   tabindex="-1" 
                   autocomplete="off"
                   autocorrect="off"
                   autocapitalize="off"
                   spellcheck="false"
                   data-form-type="other"
                   aria-hidden="true">
        </p>


    <?php
}
/**
 * Add honeypot field to the WP registration form
 */
add_action('register_form', 'maspik_add_honeypot_to_register_form');

/**
 * Add honeypot field to the WooCommerce registration form
 */
add_action('woocommerce_register_form', 'maspik_add_honeypot_to_register_form', 9999);

