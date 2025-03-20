<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * CF7 Validation Hook
 */
add_filter( 'wpcf7_validate', 'maspik_validate_cf7_process', 10, 2 );
function maspik_validate_cf7_process( $result, $tags ) {
    $error_message = cfas_get_error_text();
    $spam = false;
    $reason = "";

    // Loop through each tag (field) in the form
    foreach ( $tags as $tag ) {
        $name = $tag->name;
        $type = $tag->basetype;

        if ( ! isset( $_POST[ $name ] ) ) {
            continue;
        }

        $field_value =  is_array( $_POST[ $name ] ) ? $_POST[ $name ] : sanitize_text_field( $_POST[ $name ] );

        if ( empty( $field_value ) ) {
            continue;
        }

        switch ( $type ) {
            case 'text':
                // Text Field Validation
                $validateTextField = validateTextField( $field_value );
                $spam = isset( $validateTextField['spam'] ) ? $validateTextField['spam'] : false;
                $message = isset( $validateTextField['message'] ) ? $validateTextField['message'] : '';
                $spam_lbl = isset( $validateTextField['label'] ) ? $validateTextField['label'] : '';
                $spam_val = isset( $validateTextField['option_value'] ) ? $validateTextField['option_value'] : '';

                if ( $spam ) {
                    $error_message = cfas_get_error_text( $message );
                    $post_entries = array_filter( $_POST, function( $key ) {
                        return strpos( $key, '_wpcf7' ) === false;
                    }, ARRAY_FILTER_USE_KEY );
                    efas_add_to_log( "text", $spam, $post_entries, "Contact Form 7", $spam_lbl, $spam_val );
                    $result->invalidate( $tag, $error_message );
                    return $result;
                }
                break;

            case 'email':
                // Email Field Validation
                $spam = checkEmailForSpam( $field_value );
                $spam_val = $field_value;

                if ( $spam ) {
                    $error_message = cfas_get_error_text( "emails_blacklist" );
                    $post_entries = array_filter( $_POST, function( $key ) {
                        return strpos( $key, '_wpcf7' ) === false;
                    }, ARRAY_FILTER_USE_KEY );
                    efas_add_to_log( "email", "Email $field_value is blocked", $post_entries, "Contact Form 7", "emails_blacklist", $spam_val );
                    $result->invalidate( $tag, $error_message );
                    return $result;
                }
                break;

            case 'tel':
                // Tel Field Validation
                $checkTelForSpam = checkTelForSpam( $field_value );
                $reason = isset( $checkTelForSpam['reason'] ) ? $checkTelForSpam['reason'] : '';
                $valid = isset( $checkTelForSpam['valid'] ) ? $checkTelForSpam['valid'] : true;
                $message = isset( $checkTelForSpam['message'] ) ? $checkTelForSpam['message'] : '';
                $spam_lbl = isset( $checkTelForSpam['label'] ) ? $checkTelForSpam['label'] : '';
                $spam_val = isset( $checkTelForSpam['option_value'] ) ? $checkTelForSpam['option_value'] : '';

                if ( ! $valid ) {
                    $post_entries = array_filter( $_POST, function( $key ) {
                        return strpos( $key, '_wpcf7' ) === false;
                    }, ARRAY_FILTER_USE_KEY );
                    $error_message = cfas_get_error_text( $message );
                    efas_add_to_log( "tel", $reason, $post_entries, "Contact Form 7", $spam_lbl, $spam_val );
                    $result->invalidate( $tag, $error_message );
                    return $result;
                }
                break;

            case 'textarea':
                // Textarea Field Validation
                $checkTextareaForSpam = checkTextareaForSpam( $field_value );
                $spam = isset( $checkTextareaForSpam['spam'] ) ? $checkTextareaForSpam['spam'] : false;
                $message = isset( $checkTextareaForSpam['message'] ) ? $checkTextareaForSpam['message'] : '';
                $spam_lbl = isset( $checkTextareaForSpam['label'] ) ? $checkTextareaForSpam['label'] : '';
                $spam_val = isset( $checkTextareaForSpam['option_value'] ) ? $checkTextareaForSpam['option_value'] : '';

                if ( $spam ) {
                    $post_entries = array_filter( $_POST, function( $key ) {
                        return strpos( $key, '_wpcf7' ) === false;
                    }, ARRAY_FILTER_USE_KEY );
                    $error_message = cfas_get_error_text( $message );
                    efas_add_to_log( "textarea", $spam, $post_entries, "Contact Form 7", $spam_lbl, $spam_val );
                    $result->invalidate( $tag, $error_message );
                    return $result;
                }
                break;

        }
    }

    // General Check
    $ip = maspik_get_real_ip();
    $GeneralCheck = GeneralCheck( $ip, $spam, $reason, $_POST, "cf7" );
    $spam = isset( $GeneralCheck['spam'] ) ? $GeneralCheck['spam'] : false;
    $reason = isset( $GeneralCheck['reason'] ) ? $GeneralCheck['reason'] : false;
    $message = isset( $GeneralCheck['message'] ) ? $GeneralCheck['message'] : false;
    $spam_val = isset( $GeneralCheck['value'] ) ? $GeneralCheck['value'] : false;

    if ( $spam ) {
        $result->invalidate( '', cfas_get_error_text( $message ) );
        $post_entries = array_filter( $_POST, function( $key ) {
            return strpos( $key, '_wpcf7' ) === false;
        }, ARRAY_FILTER_USE_KEY );
        efas_add_to_log( "General", $reason, $post_entries, "Contact Form 7", $message, $spam_val );
        return $result;
    }

    return $result;
}

function maspik_honeypot_to_cf7_form( $form_content ) {
    if ( maspik_get_settings( 'maspikHoneypot' ) || maspik_get_settings( 'maspikTimeCheck' ) || maspik_get_settings( 'maspikYearCheck' ) ) {
        $custom_html = '';

        if ( maspik_get_settings( 'maspikHoneypot' ) ) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="full-name-maspik-hp" class="wpcf7-form-control-label">Leave this field empty</label>
                <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="full-name-maspik-hp" id="full-name-maspik-hp" class="wpcf7-form-control wpcf7-text" placeholder="Leave this field empty">
            </div>';
        }

        if ( maspik_get_settings( 'maspikYearCheck' ) ) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="Maspik-currentYear" class="wpcf7-form-control-label"></label>
                <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-currentYear" id="Maspik-currentYear" class="wpcf7-form-control wpcf7-text" placeholder="">
            </div>';
        }

        if ( maspik_get_settings( 'maspikTimeCheck' ) ) {
            $custom_html .= '<div class="wpcf7-form-control-wrap maspik-field">
                <label for="Maspik-exactTime" class="wpcf7-form-control-label"></label>
                <input size="1" type="text" autocomplete="off" aria-hidden="true" tabindex="-1" name="Maspik-exactTime" id="Maspik-exactTime" class="wpcf7-form-control wpcf7-text" placeholder="">
            </div>';
        }

        $form_content .= $custom_html;
    }

    return $form_content;
}
add_filter( 'wpcf7_form_elements', 'maspik_honeypot_to_cf7_form' );