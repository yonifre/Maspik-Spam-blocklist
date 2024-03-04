<?php
// Load WordPress
$root = dirname(dirname(dirname(dirname(__FILE__))));
require_once($root . '/wp-load.php');

// Handle AJAX request
add_action('wp_ajax_handle_contact_form', 'handle_contact_form');
add_action('wp_ajax_nopriv_handle_contact_form', 'handle_contact_form');

function handle_contact_form() {
    if (!empty($_POST["send"])) {
        $name = $_POST["userName"];
        $email = $_POST["userEmail"];
        $subject = $_POST["subject"];
        $content = $_POST["content"];

        $spam = 1;
        if (!$spam) {
                $response = array("status" => "success", "message" => "Your contact information is received successfully.");
        } else {
            $response = array("status" => "error", "message" => "The email address is invalid.");
        }

        wp_send_json($response);
    }
}
