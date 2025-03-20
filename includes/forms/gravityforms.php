<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

add_filter('gform_field_validation', 'maspik_validation_process_gravity', 10, 4);
function maspik_validation_process_gravity($result, $value, $form, $field) {
    static $spam_check_done = false;
    
    // If we already found spam, no need to continue checking
    if ($spam_check_done) {
        $result['is_valid'] = false;
        return $result;
    }

    // General check first - if there is a general problem, no need to check the fields
    static $general_check_done = false;
    if (!$general_check_done) {
        $ip = maspik_get_real_ip();
        $spam = false;
        $reason = '';
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST, "gravityforms");
        
        if (isset($GeneralCheck['spam']) && $GeneralCheck['spam']) {
            $reason = $GeneralCheck['reason'] ?? '';
            $message = $GeneralCheck['message'] ?? '';
            $spam_val = $GeneralCheck['value'] ?? '';
            
            efas_add_to_log("General", $reason, $_POST, 'GravityForms', $message, $spam_val);
            GFCommon::log_debug(__METHOD__ . '(): ' . $reason);
            $result['is_valid'] = false;
            $result['message'] = cfas_get_error_text($message);
            $spam_check_done = true;
            return $result;
        }
        $general_check_done = true;
    }

    // If field is already invalid or empty, return early
    if (!$result['is_valid'] || empty($value)) {
        return $result;
    }

    $field_value = is_array($value) ? implode(" ", $value) : $value;
    $field_type = $field->type;
    $field_value = strtolower($field_value);

    switch ($field_type) {
        case 'text':
        case 'name':
            $validateTextField = validateTextField($field_value);
            if (isset($validateTextField['spam']) && $validateTextField['spam']) {
                efas_add_to_log("text", $validateTextField['spam'], $_POST, 'GravityForms', 
                    $validateTextField['label'] ?? '', $validateTextField['option_value'] ?? '');
                $result['is_valid'] = false;
                $result['message'] = cfas_get_error_text($validateTextField['message'] ?? '');
                $spam_check_done = true;
                return $result;
            }
            break;

        case 'email':
            $spam = checkEmailForSpam($field_value);
            if ($spam) {
                efas_add_to_log("email", "Email $field_value is blocked", $_POST, "GravityForms", "emails_blacklist", $field_value);
                $result['is_valid'] = false;
                $result['message'] = cfas_get_error_text();
                $spam_check_done = true;
                return $result;
            }
            break;

        case 'phone':
            $checkTelForSpam = checkTelForSpam($field_value);
            if (isset($checkTelForSpam['valid']) && !$checkTelForSpam['valid']) {
                efas_add_to_log("tel", $checkTelForSpam['reason'] ?? '', $_POST, "GravityForms", 
                    $checkTelForSpam['label'] ?? '', $checkTelForSpam['option_value'] ?? '');
                $result['is_valid'] = false;
                $result['message'] = cfas_get_error_text($checkTelForSpam['message'] ?? '');
                $spam_check_done = true;
                return $result;
            }
            break;

        case 'textarea':
            $checkTextareaForSpam = checkTextareaForSpam($field_value);
            if (isset($checkTextareaForSpam['spam']) && $checkTextareaForSpam['spam']) {
                efas_add_to_log("textarea", $checkTextareaForSpam['spam'], $_POST, "GravityForms", 
                    $checkTextareaForSpam['label'] ?? '', $checkTextareaForSpam['option_value'] ?? '');
                $result['is_valid'] = false;
                $result['message'] = cfas_get_error_text($checkTextareaForSpam['message'] ?? '');
                $spam_check_done = true;
                return $result;
            }
            break;
    }

    return $result;
}

add_filter('gform_submit_button', 'add_maspikhp_html_to_gform', 99, 2);
function add_maspikhp_html_to_gform($button, $form) {
    if (is_admin()) {
        return $button;
    }
    $addhtml = "";

    if (maspik_get_settings('maspikHoneypot')) {
        $honeypot_name = maspik_HP_name();
        $addhtml .= '<div class="gfield gfield--type-text maspik-field">
            <label for="' . $honeypot_name . '" class="ginput_container_text">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="' . $honeypot_name . '" id="' . $honeypot_name . '" class="ginput_text" placeholder="Leave this field empty">
        </div>';
    }

    if (maspik_get_settings('maspikYearCheck')) {
        $addhtml .= '<div class="gfield gfield--type-text maspik-field">
            <label for="Maspik-currentYear" class="ginput_container_text">Leave this field empty</label>
            <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="ginput_text" placeholder="">
        </div>';
    }

    return $addhtml . $button;
}

