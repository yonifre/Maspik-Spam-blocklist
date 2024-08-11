<?php
/**
 * This function checks the custom form submissions for spam.
 * 
 * Filters:
 * - 'maspik_should_check_form': Controls whether the form should be processed.
 * - 'maspik_nonce_action': Allows customization of the nonce action.
 * - 'maspik_nonce_field': Allows customization of the nonce field name.
 * - 'maspik_post_text_field': Allows customization of the text field name.
 * - 'maspik_post_email_field': Allows customization of the email field name.
 * - 'maspik_post_phone_field': Allows customization of the phone field name.
 * - 'maspik_post_textarea_field': Allows customization of the textarea field name.
 * 
 * Example usage in your form:
 * 
 * <form method="post">
 *     <?php 
 *     wp_nonce_field(apply_filters('maspik_nonce_action', 'maspik_custom_form'), 
 *                    apply_filters('maspik_nonce_field', 'maspik_nonce')); 
 *     ?>
 *     <input type="hidden" name="maspik_do_check" value="1">
 *     <input type="text" name="<?php echo apply_filters('maspik_post_text_field', 'text_field'); ?>" placeholder="Text Field">
 *     <input type="email" name="<?php echo apply_filters('maspik_post_email_field', 'email_field'); ?>" placeholder="Email Field">
 *     <input type="text" name="<?php echo apply_filters('maspik_post_phone_field', 'phone_field'); ?>" placeholder="Phone Field">
 *     <textarea name="<?php echo apply_filters('maspik_post_textarea_field', 'textarea_field'); ?>" placeholder="Textarea Field"></textarea>
 *     <button type="submit">Submit</button>
 * </form>
 */



// Hook into the init action
add_action('init', 'maspik_custom_form_check');

function maspik_custom_form_check() {
    // Get the nonce action and field name from filters
    $nonce_action = apply_filters('maspik_nonce_action', 'maspik_custom_form');
    $nonce_field = apply_filters('maspik_nonce_field', 'maspik_nonce');

    // Ensure it's a POST request and verify the nonce
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
        return;
    }

    // Check if the form should be processed
    if (!apply_filters('maspik_should_check_form', false)) {
        return;
    }

    try {
        $errors = [];
        $spam = false;
        $reason = "";

        // Get IP address
        $ip = efas_getRealIpAddr();

        // Country IP Check (example implementation)
        $CountryCheck = CountryCheck($ip, $spam, $reason,$_POST);
        if ($CountryCheck['spam']) {
            efas_add_to_log("Country/IP", $CountryCheck['reason'], $_POST, "Custom PHP form");
            $errors['ip_country'] = cfas_get_error_text($CountryCheck['message']);
        }

        // Check text fields
        $text_field_key = apply_filters('maspik_post_text_field', 'text_field');
        if (isset($_POST[$text_field_key])) {
            $field_value = sanitize_text_field($_POST[$text_field_key]);
            $validateTextField = validateTextField($field_value);
            if ($validateTextField['spam']) {
                efas_add_to_log("text", $validateTextField['reason'], $_POST, "Custom PHP form");
                $errors[$text_field_key] = cfas_get_error_text($validateTextField['message']);
            }
        }

        // Check email fields
        $email_field_key = apply_filters('maspik_post_email_field', 'email_field');
        if (isset($_POST[$email_field_key])) {
            $field_value = sanitize_email($_POST[$email_field_key]);
            $spam = checkEmailForSpam($field_value);
            if ($spam) {
                efas_add_to_log("email", "Email $field_value is blocked", $_POST, "Custom PHP form");
                $errors[$email_field_key] = 'Invalid email field';
            }
        }

        // Check phone fields
        $phone_field_key = apply_filters('maspik_post_phone_field', 'phone_field');
        if (isset($_POST[$phone_field_key])) {
            $field_value = sanitize_text_field($_POST[$phone_field_key]);
            $checkTelForSpam = checkTelForSpam($field_value);
            if (!$checkTelForSpam['valid']) {
                efas_add_to_log("tel", $checkTelForSpam['reason'], $_POST, "Custom PHP form");
                $errors[$phone_field_key] = cfas_get_error_text($checkTelForSpam['message']);
            }
        }

        // Check textarea fields
        $textarea_field_key = apply_filters('maspik_post_textarea_field', 'textarea_field');
        if (isset($_POST[$textarea_field_key])) {
            $field_value = sanitize_textarea_field($_POST[$textarea_field_key]);
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            if ($checkTextareaForSpam['spam']) {
                efas_add_to_log("textarea", $checkTextareaForSpam['reason'], $_POST, "Custom PHP form");
                $errors[$textarea_field_key] = cfas_get_error_text($checkTextareaForSpam['message']);
            }
        }

        // If there are any errors, display them or take any appropriate action
        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors]);
        }

        // If no errors, continue with form processing
        wp_send_json_success(['message' => 'Form submitted successfully']);

    } catch (Exception $e) {
        wp_send_json_error(['message' => 'An unexpected error occurred']);
    }
}