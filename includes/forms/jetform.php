<?php

/////// jet-form-builder

add_action('jet-form-builder/form-handler/before-send', 'validate_jet_form_for_spam', 10, 1);

function validate_jet_form_for_spam($form_handler) {
    if (isset($form_handler->request_handler->_fields) && is_array($form_handler->request_handler->_fields)) {
        $form_fields = $form_handler->request_handler->_fields;
        $error_message = cfas_get_error_text();
        $ip = efas_getRealIpAddr();

        // Country IP Check
        $spam = false;
        $reason = "";
        $GeneralCheck = GeneralCheck($ip, $spam, $reason, $_POST,"jetform");
        $spam = isset($GeneralCheck['spam']) ? $GeneralCheck['spam'] : false;
        $reason = isset($GeneralCheck['reason']) ? $GeneralCheck['reason'] : "";
        $message = isset($GeneralCheck['message']) ? $GeneralCheck['message'] : "";
        $spam_val = $GeneralCheck['value'] ? $GeneralCheck['value'] : false ;

        if ($spam) {
            efas_add_to_log("Country/IP", $reason, $_POST, "JetFormBuilder", $message,  $spam_val);
            throw new \Jet_Form_Builder\Exceptions\Request_Exception(
                $error_message,
                array('ip_check' => cfas_get_error_text($message))
            );
        }

        foreach ($form_fields as $field) {
            if (isset($field['attrs']['name']) && !empty($field['attrs']['name'])) {
                $field_key = $field['attrs']['name'];
                $field_value = isset($_POST[$field_key]) ? sanitize_text_field($_POST[$field_key]) : '';

                if (!empty($field_value)) {
                    // Text field validation
                    if ($field['blockName'] === 'jet-forms/text-field' && (!isset($field['attrs']['field_type']) || $field['attrs']['field_type'] === 'text')) {
                        $validateTextField = validateTextField($field_value);
                        $spam = isset($validateTextField['spam']) ? $validateTextField['spam'] : false;
                        $message = isset($validateTextField['message']) ? $validateTextField['message'] : "";
                        $spam_lbl = isset($validateTextField['label']) ? $validateTextField['label'] : 0 ;
                        $spam_val = isset($validateTextField['option_value']) ? $validateTextField['option_value'] : 0 ;
                        if ($spam) {
                            efas_add_to_log("text", $spam, $_POST, "JetFormBuilder", $spam_lbl, $spam_val);
                            throw new \Jet_Form_Builder\Exceptions\Request_Exception(
                                $error_message,
                                array($field_key => cfas_get_error_text($message))
                            );
                        }
                    }

                    // Email field validation
                    if ($field['blockName'] === 'jet-forms/text-field' && isset($field['attrs']['field_type']) && $field['attrs']['field_type'] === 'email') {
                        $spam = checkEmailForSpam($field_value);
                        $spam_val = $field_value;
                        if ($spam) {
                            efas_add_to_log("email", "Email $field_value is block $spam", $_POST, "JetFormBuilder", "emails_blacklist", $spam_val);
                            throw new \Jet_Form_Builder\Exceptions\Request_Exception(
                                $error_message,
                                array($field_key => $error_message)
                            );
                        }
                    }

                    // Phone field validation
                    if ($field['blockName'] === 'jet-forms/text-field' && isset($field['attrs']['field_type']) && $field['attrs']['field_type'] === 'tel') {
                        $checkTelForSpam = checkTelForSpam($field_value);
                        $reason = isset($checkTelForSpam['reason']) ? $checkTelForSpam['reason'] : "";
                        $valid = isset($checkTelForSpam['valid']) ? $checkTelForSpam['valid'] : "yes";
                        $message = isset($checkTelForSpam['message']) ? $checkTelForSpam['message'] : "";
                        $spam_lbl = isset($checkTelForSpam['label']) ? $checkTelForSpam['label'] : 0 ;
                        $spam_val = isset($checkTelForSpam['option_value']) ? $checkTelForSpam['option_value'] : 0 ;

                        if (!$valid) {
                            efas_add_to_log("tel", $reason, $_POST, "JetFormBuilder", $spam_lbl, $spam_val);
                            throw new \Jet_Form_Builder\Exceptions\Request_Exception(
                                $error_message,
                                array($field_key => cfas_get_error_text($message))
                            );
                        }
                    }

                    // Textarea field validation
                    if ($field['blockName'] === 'jet-forms/textarea-field' || $field['blockName'] === 'jet-forms/wysiwyg-field') {
                        $checkTextareaForSpam = checkTextareaForSpam($field_value);
                        $spam = isset($checkTextareaForSpam['spam']) ? $checkTextareaForSpam['spam'] : false;
                        $message = isset($checkTextareaForSpam['message']) ? $checkTextareaForSpam['message'] : "";
                        $spam_lbl = isset($checkTextareaForSpam['label']) ? $checkTextareaForSpam['label'] : 0 ;
                        $spam_val = isset($checkTextareaForSpam['option_value']) ? $checkTextareaForSpam['option_value'] : 0 ;

                        if ($spam) {
                            efas_add_to_log("textarea/wysiwyg", $spam, $_POST, "JetFormBuilder", $spam_lbl, $spam_val);
                            throw new \Jet_Form_Builder\Exceptions\Request_Exception(
                                $error_message,
                                array($field_key => cfas_get_error_text($message))
                            );
                        }
                    }

                }
            }
        }
    }
}


///////
add_filter('jet-form-builder/before-render-field', 'add_maspikhp_html_to_jet_form', 10, 3);

function add_maspikhp_html_to_jet_form($content, $field_name, $attrs) {
    if ($field_name === 'submit-field') {
        $addhtml = "";

        if (maspik_get_settings('maspikHoneypot')) {
            $honeypot_name = maspik_HP_name();
            $addhtml .= '<div class="jet-form-builder__field-wrap maspik-field">
                <label for="' . $honeypot_name . '" class="jet-form-builder__label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="' . $honeypot_name . '" id="' . $honeypot_name . '" class="jet-form-builder__field jet-form-builder__field-text" placeholder="Leave this field empty">
            </div>';
        }

        if (maspik_get_settings('maspikYearCheck')) {
            $addhtml .= '<div class="jet-form-builder__field-wrap maspik-field">
                <label for="Maspik-currentYear" class="jet-form-builder__label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="jet-form-builder__field jet-form-builder__field-text" placeholder="">
            </div>';
        }

        if (maspik_get_settings('maspikTimeCheck')) {
            $addhtml .= '<div class="jet-form-builder__field-wrap maspik-field">
                <label for="Maspik-exactTime" class="jet-form-builder__label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" autofill="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="jet-form-builder__field jet-form-builder__field-text" placeholder="">
            </div>';
        }

        return $addhtml . $content;
    }

    return $content;
}
