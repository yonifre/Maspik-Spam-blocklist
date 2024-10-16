<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Provide a admin area view for the plugin
 */
$spamcounter = maspik_spam_count();
?>
<div class="wrap maspik-mainpage">



      <div id="icon-themes" class="icon32"></div>  
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->


<?php
    settings_errors(); 

    $error_message = "";
    $save_notif = "";

    //Submit button command

        if(isset($_POST['maspik-save-btn']) || isset($_POST['maspik-api-save-btn'])) {

            if (!isset($_POST['maspik_save_settings_nonce']) || !wp_verify_nonce($_POST['maspik_save_settings_nonce'], 'maspik_save_settings_action')) {
                die('Invalid nonce');
            }

             maspik_save_command($error_message);

            $save_notif = "yes";
            
        }
    //Submit button command - END

    //Save Commands
        
        function maspik_save_command($error = ''){

            global $wpdb;
            $error_message = $error;
            $result_check= "";

            
            //Text field

                if( maspik_save_settings( 'text_blacklist' , ((sanitize_textarea_field(stripslashes($_POST['text_blacklist']))  ))) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //main list

                
                if( maspik_save_settings( 'text_limit_toggle' , sanitize_text_field(isset( $_POST['text_limit_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text limit toggle


                if( maspik_save_settings( 'MinCharactersInTextField' , sanitize_text_field($_POST['MinCharactersInTextField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text min character limit

                
                if( maspik_save_settings( 'MaxCharactersInTextField' , sanitize_text_field($_POST['MaxCharactersInTextField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text max character limit


                if( maspik_save_settings( 'text_custom_message_toggle' , sanitize_text_field(isset( $_POST['text_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_MaxCharactersInTextField' , sanitize_text_field( stripslashes($_POST['custom_error_message_MaxCharactersInTextField']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message

                


            //Text field END --

            //Email field

                if( maspik_save_settings( 'emails_blacklist' , sanitize_textarea_field( stripslashes($_POST['emails_blacklist']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //main list

            //Email field END --

            //Textarea field

                if( maspik_save_settings( 'textarea_blacklist' , sanitize_textarea_field( stripslashes($_POST['textarea_blacklist']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //main list

                if( maspik_save_settings( 'textarea_link_limit_toggle' , sanitize_text_field(isset( $_POST['textarea_link_limit_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text limit toggle


                if( maspik_save_settings( 'contain_links' , sanitize_text_field($_POST['contain_links'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //max link limit

                
                if( maspik_save_settings( 'textarea_limit_toggle' , sanitize_text_field(isset( $_POST['textarea_limit_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text limit toggle

                if( maspik_save_settings( 'MinCharactersInTextAreaField' , sanitize_text_field($_POST['MinCharactersInTextAreaField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text min character limit

                
                if( maspik_save_settings( 'MaxCharactersInTextAreaField' , sanitize_text_field($_POST['MaxCharactersInTextAreaField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //text max character limit


                if( maspik_save_settings( 'textarea_custom_message_toggle' , sanitize_text_field(isset( $_POST['textarea_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_MaxCharactersInTextAreaField' , sanitize_text_field( stripslashes($_POST['custom_error_message_MaxCharactersInTextAreaField']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message


            //Textarea field END --

            //Phone field

                if( maspik_save_settings( 'tel_formats' , sanitize_textarea_field( stripslashes($_POST['tel_formats']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //main list

                if( maspik_save_settings( 'tel_limit_toggle' , sanitize_text_field(isset( $_POST['tel_limit_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //phone limit toggle


                if( maspik_save_settings( 'MinCharactersInPhoneField' , sanitize_text_field($_POST['MinCharactersInPhoneField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //phone min character limit

                
                if( maspik_save_settings( 'MaxCharactersInPhoneField' , sanitize_text_field($_POST['MaxCharactersInPhoneField'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //phone max character limit

                if( maspik_save_settings( 'phone_limit_custom_message_toggle' , sanitize_text_field(isset( $_POST['phone_limit_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom limit message toggle

                if( maspik_save_settings( 'custom_error_message_MaxCharactersInPhoneField' , sanitize_text_field( stripslashes($_POST['custom_error_message_MaxCharactersInPhoneField']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message for limit

                if( maspik_save_settings( 'phone_custom_message_toggle' , sanitize_text_field(isset( $_POST['phone_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_tel_formats' , sanitize_text_field( stripslashes($_POST['custom_error_message_tel_formats']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message


            //Phone field END --

            //Language Needed field
                if (isset($_POST['lang_needed']) && !empty($_POST['lang_needed'])) {
                    $selectedLangNeeded = $_POST['lang_needed'];
                    $selectedLangNeededVal = "";

                    foreach ($selectedLangNeeded as $langNeedValue) {
                        $escapedValue =  $langNeedValue;
                        $selectedLangNeededVal .= $escapedValue . " ";
                    }
                    $selectedLangNeededVal = str_replace("\\p", "p", $selectedLangNeededVal);


                    if( maspik_save_settings( 'lang_needed' , $selectedLangNeededVal ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    }
                }else{
                    if( maspik_save_settings( 'lang_needed' , '' ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    }
                } //main list


                if( maspik_save_settings( 'lang_need_custom_message_toggle' , sanitize_text_field(isset( $_POST['lang_need_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_lang_needed' , sanitize_text_field( stripslashes($_POST['custom_error_message_lang_needed']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message


            //Language Needed field END --

            //Language Forbidden field
                if (isset($_POST['lang_forbidden']) && !empty($_POST['lang_forbidden'])) {
                    $selectedLangForbidden = $_POST['lang_forbidden'];
                    $selectedLangForbiddenVal = "";

                    foreach ($selectedLangForbidden as $langForbiddenValue) {
                        $escapedFValue =  $langForbiddenValue;
                        $selectedLangForbiddenVal .= $escapedFValue . " ";
                        }
                        $selectedLangForbiddenVal = str_replace("\\p", "p", $selectedLangForbiddenVal);


                    if( maspik_save_settings( 'lang_forbidden' , $selectedLangForbiddenVal ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    } 
                }else{
                    if( maspik_save_settings( 'lang_forbidden' , '' ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    } 
                }//main list


                if( maspik_save_settings( 'lang_forbidden_custom_message_toggle' , sanitize_text_field(isset( $_POST['lang_forbidden_custom_message_toggle'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_lang_forbidden' , sanitize_text_field( stripslashes($_POST['custom_error_message_lang_forbidden'])) ) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message


            //Language Forbidden field END --

            //Country field

                if (isset($_POST['country_blacklist']) && !empty($_POST['country_blacklist'])) {
                    $selectedCountry = $_POST['country_blacklist'];
                    $selectedCountryVal = "";

                    foreach ($selectedCountry as $countryValue) {
                        $escapedCValue =  $countryValue;
                        $selectedCountryVal .= $escapedCValue . " ";
                    }
                    $selectedCountryVal = str_replace("\\p", "p", $selectedCountryVal);


                    if( maspik_save_settings( 'country_blacklist' , $selectedCountryVal ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    }
                }else{
                    if( maspik_save_settings( 'country_blacklist' , '' ) != "success" ){ 
                        $error_message .= $result_check . " ";
                    }

                } //main list
                

                if( maspik_save_settings( 'AllowedOrBlockCountries' , sanitize_text_field( isset( $_POST['AllowedOrBlockCountries'] ) ?  $_POST['AllowedOrBlockCountries'] : "block") ) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //Allow or block dropdown


                if( maspik_save_settings( 'country_custom_message_toggle' , sanitize_text_field(sanitize_text_field(isset( $_POST['country_custom_message_toggle'] ))) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message toggle


                if( maspik_save_settings( 'custom_error_message_country_blacklist' , sanitize_text_field( stripslashes($_POST['custom_error_message_country_blacklist'])) ) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //custom error message


            //Country field END --

            //MASPIK API field

                if( maspik_save_settings( 'private_file_id' , sanitize_text_field($_POST['private_file_id'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //main list

                
                if( maspik_save_settings( 'popular_spam' ,sanitize_text_field( isset( $_POST['popular_spam'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //popular spam toggle



            //MASPIK API field END --

            //General field
                if( maspik_save_settings( 'maspikDbCheck' , sanitize_text_field(isset( $_POST['maspikDbCheck'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspikDbCheck

                if( maspik_save_settings( 'maspikHoneypot' , sanitize_text_field(isset( $_POST['maspikHoneypot'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //honeypot
                if( maspik_save_settings( 'maspikYearCheck' , sanitize_text_field(isset( $_POST['maspikYearCheck'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //honeypot
                if( maspik_save_settings( 'maspikTimeCheck' , sanitize_text_field(isset( $_POST['maspikTimeCheck'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //honeypot

                if( maspik_save_settings( 'NeedPageurl' , sanitize_text_field(isset( $_POST['NeedPageurl'] )) ? 1 : 0) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //require url

                if( maspik_save_settings( 'ip_blacklist' , sanitize_textarea_field( stripslashes($_POST['ip_blacklist']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //IP blacklist

                if( maspik_save_settings( 'error_message' , sanitize_text_field( stripslashes($_POST['error_message']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //General error message

                if( maspik_save_settings( 'abuseipdb_api' , sanitize_text_field( stripslashes($_POST['abuseipdb_api']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //Abuse API code

                if( maspik_save_settings( 'abuseipdb_score' , sanitize_text_field( $_POST['abuseipdb_score'] )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //Abuse API threshold

                if( maspik_save_settings( 'proxycheck_io_api' , sanitize_text_field( stripslashes($_POST['proxycheck_io_api']) )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //ProxycheckIO API code

                if( maspik_save_settings( 'proxycheck_io_risk' , sanitize_text_field( $_POST['proxycheck_io_risk'] )) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //ProxycheckIO API threshold

            //General field END --
             
            //Form option field

                if( maspik_save_settings( 'maspik_support_Elementor_forms' , sanitize_text_field(isset( $_POST['maspik_support_Elementor_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //elementor

                if( maspik_save_settings( 'maspik_support_cf7' , sanitize_text_field(isset( $_POST['maspik_support_cf7'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //Contact form 7

                if( maspik_save_settings( 'maspik_support_wp_comment' , sanitize_text_field(isset( $_POST['maspik_support_wp_comment'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //WP Comment
                
                if( maspik_save_settings( 'maspik_support_registration' , sanitize_text_field(isset( $_POST['maspik_support_registration'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_registration

                if( maspik_save_settings( 'maspik_support_woocommerce_review' , sanitize_text_field(isset( $_POST['maspik_support_woocommerce_review'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_woocommerce_review

                if( maspik_save_settings( 'maspik_support_Woocommerce_registration' , sanitize_text_field(isset( $_POST['maspik_support_Woocommerce_registration'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_Woocommerce_registration

                if( maspik_save_settings( 'maspik_support_Wpforms' , sanitize_text_field(isset( $_POST['maspik_support_Wpforms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_Wpforms

                if( maspik_save_settings( 'maspik_support_formidable_forms' , sanitize_text_field(isset( $_POST['maspik_support_formidable_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_formidable_forms

                if( maspik_save_settings( 'maspik_support_forminator_forms' , sanitize_text_field(isset( $_POST['maspik_support_forminator_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_forminator_forms

                if( maspik_save_settings( 'maspik_support_fluentforms_forms' , sanitize_text_field(isset( $_POST['maspik_support_fluentforms_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_fluentforms_forms

                if( maspik_save_settings( 'maspik_support_gravity_forms' , sanitize_text_field(isset( $_POST['maspik_support_gravity_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_gravity_forms

                if( maspik_save_settings( 'maspik_support_bricks_forms' , sanitize_text_field(isset( $_POST['maspik_support_bricks_forms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_bricks_forms


                if( maspik_save_settings( 'maspik_support_ninjaforms' , sanitize_text_field(isset( $_POST['maspik_support_ninjaforms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_ninja_forms

                if( maspik_save_settings( 'maspik_support_jetforms' , sanitize_text_field(isset( $_POST['maspik_support_jetforms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_jet_forms

                if( maspik_save_settings( 'maspik_support_everestforms' , sanitize_text_field(isset( $_POST['maspik_support_everestforms'] )) ? "yes" : "no") != "success" ){ 
                    $error_message .= $result_check . " ";
                } //maspik_support_everest_forms


            //Form option field END --

            //Other Options

                
                if( maspik_save_settings( 'maspik_Store_log' , sanitize_text_field(isset( $_POST['maspik_Store_log'] )) ? 'yes' : 'no') != "success" ){ 
                    $error_message .= $result_check . " ";
                } //spam log toggle

                
                if( maspik_save_settings( 'spam_log_limit' , sanitize_text_field($_POST['spam_log_limit'])) != "success" ){ 
                    $error_message .= $result_check . " ";
                } //spam log entity limit

                if( update_option( 'shere_data' , sanitize_text_field(isset( $_POST['shere_data'] )) ? 1 : 0)){ 
                    $error_message .= $result_check . " ";
                } //disable comment toggle

                if( maspik_save_settings( 'shere_data' , sanitize_text_field(isset( $_POST['shere_data'] )) ? 1 : 0)){ 
                    $error_message .= $result_check . " ";
                } //disable comment toggle

                


            //Other Options END --
        }


    //Save Commands - END

    //Refresh Maspik API button Command

        if ( (isset( $_POST['maspik-api-refresh-btn'] ) || isset( $_POST['maspik-api-save-btn'] ) ) && cfes_is_supporting() ) {
            
            // Verify nonce
            if (isset($_POST['maspik_save_settings_nonce']) && wp_verify_nonce($_POST['maspik_save_settings_nonce'], 'maspik_save_settings_action')) {
                // Nonce is valid, proceed with refreshing API
                cfas_refresh_api();
                //$current_page = esc_url(admin_url("admin.php?page=maspik"));
                // Redirect to avoid resubmission on page refresh
                //echo "<script>window.location.replace('" . esc_js($current_page) . "');</script>";
            } else {
                // Nonce verification failed, handle accordingly
                echo "<p>Error: Nonce verification failed.</p>";
            }
        }

    //Refresh Maspik API button Command - END

?>
   
    <div class= "maspik-settings">
        <div class= "maspik-setting-head-wrap"><!-- Head section-->
            <div class= "maspik-setting-header">
                <div class="notice-pointer"><h2></h2></div>

                <?php 
                echo "<div class='upsell-btn " . esc_attr(maspik_add_pro_class()) . "'>";
                maspik_get_pro();
                maspik_activate_license();
                echo "</div>";
                
                ?>
                <div class="maspik-setting-header-wrap">
                    <h1 class="maspik-title">MASPIK.</h1>
                <?php
                    echo '<h3 class="maspik-protag ' . esc_attr(maspik_add_pro_class()) . '">Pro</h3>';
                ?>
                </div>          
           
            </div>

            <?php  

             //Update database button command
             /*
                if ( isset( $_POST['maspik-update-database-btn'] ) ) {
                    create_maspik_log_table();
                    create_maspik_table();
                    maspik_run_transfer();
                }
                */
                        
            
            //Update table banner
           /*
                if(!maspik_table_exists()){
            
                    echo "<div class = 'maspik-update-db'>
                    <form method='POST' action='' class='maspik-form-update-db'>";
                    
                    echo "<div class='db-update-text'>";

                        echo '<h3>' . esc_html__('We have improved our plugin! Before continuing, please update the database.', 'contact-forms-anti-spam') . '</h3>';
                        echo '<span>' . esc_html__('( We recommend backing up your data first.)', 'contact-forms-anti-spam') . '</span>';

                    echo "</div>";
        
                    maspik_save_button_show('Update database', 'maspik-update-database maspik-btn-outline','maspik-update-database-btn'); 
        
                    echo "</form></div>";
                }
            */
            //Update table banner - END

            
            ?> 

            <h1 class="maspik-main-title"><?php esc_html_e('Block spam options', 'contact-forms-anti-spam'); ?></h1>
            <p class="up-line"><?php esc_html_e('Review the settings and adjust options as needed.', 'contact-forms-anti-spam'); ?></p>
            <div class="maspik-aform">
                <span class="mpk-form-list-lbl"><?php esc_html_e('Affected forms:', 'contact-forms-anti-spam'); ?>  </span>
                <?php
                    echo "<ul class='supportforms'>";       
                    
                    foreach ( efas_array_supports_plugin() as $key => $value) {
                      	
                        if(maspik_if_plugin_is_active($key)){
                          $class = $value ? "pro" : "free";

                          $class.= efas_if_plugin_is_affective($key) ? ' enabled' : ' disabled';

                          $value = $value ? " <span>($value)</span>" : "";
                          echo  "<li class='form-opt-toggle " . esc_attr($class) . "'>" . esc_html($key) . " " . wp_kses_post($value) . "</li>";
                          }
                    	}
                        echo "</ul>";
                ?>    
        
                <div class="mpk-aform-list"></div>

            </div>
        </div>
   
    <div class="maspik-setting-body">
    
        
        <div class="maspik-blacklist-options">

            <div class="maspik-save-message-wrap"><?php
                    if($save_notif == "yes"){
                        if($error_message){
                            global $wpdb;
                            echo "<div class='maspik-save-message error'> Error updating record: " . esc_html($wpdb->last_error) . "</div>";
                                
                        } else {
                            echo "<div class='maspik-save-message success'> Successfully Saved! </div>";
                        }
                    }
                ?></div>


        <!--accordions here-->
        <div class="maspik-accordion">            
            
            <form method="POST" action="" class="maspik-form">

            <!--  Main check -->
            <div class="main-spam-check togglewrap maspik-main-check--wrap maspik-accordion-content-wrap">
            <h3 class="maspik-header maspik-accordion-subtitle"><?php esc_html_e('Main Options', 'contact-forms-anti-spam'); ?></h3>
            <p><?php esc_html_e('Our recommendation: Take a few moments to browse through the settings, see what works best for your site, and customize your spam protection accordingly. Most features work automatically, but you can maximize protection by setting custom keywords.', 'contact-forms-anti-spam'); ?></p>
                <div class="maspik-txt-custom-msg-head togglewrap maspik-db-check--wrap">
                    <?php echo maspik_toggle_button('maspikDbCheck', 'maspikDbCheck', 'maspikDbCheck', 'maspik-DbCheck togglebutton',"",""); ?>
                    <div>
                        <h4> <?php esc_html_e('IP Verification', 'contact-forms-anti-spam'); ?></h4> 
                        <?php maspik_popup("", "",  "See details" , "visibility"); ?>

                        <span style="display:block;"><?php esc_html_e("Check if the sender's IP address is flagged as spam in the Maspik database.", 'contact-forms-anti-spam'); ?><br>
                            <?php
                            $api_data = get_option('maspik_api_requests', array('months' => array()));
                            $current_month = date('Ym');
                            $actual_calls_current_month = 0;
                            if (isset($api_data['months'][$current_month])) {
                                $actual_calls_current_month = intval($api_data['months'][$current_month]['actual_calls']);
                            }
                            
                            $max_checks = cfes_is_supporting() ? 1000 : 100;
                            
                            $over_max_checks = $actual_calls_current_month > $max_checks;
                            echo $over_max_checks ? '<span class="text-caution">' : '';
                            printf(
                                /* translators: %1$d is the number of checks used, %2$d is the total number of checks allowed */
                                esc_html__('You have used %1$d out of %2$d checks this month', 'contact-forms-anti-spam'),
                                $actual_calls_current_month,
                                $max_checks
                            );
                            echo $over_max_checks ? '.</span>' : '';

                            if (!cfes_is_supporting()) {
                                echo ' ';
                                esc_html_e('Upgrade to Pro for 1,000 checks/month', 'contact-forms-anti-spam');
                            }else{
                                echo ' ';
                                esc_html_e(', thanks to your Pro subscription.', 'contact-forms-anti-spam');
                            }
                            ?>
                        </span>
                    </div><!-- end of maspik-db-check--wrap -->
                </div>    
                <div class="maspik-txt-custom-msg-head togglewrap maspik-honeypot-wrap">
                    <?php echo maspik_toggle_button('maspikHoneypot', 'maspikHoneypot', 'maspikHoneypot', 'maspik-honeypot togglebutton',"",""); ?>
                    <div>
                        <h4> <?php esc_html_e('Honeypot Trap', 'contact-forms-anti-spam'); ?>
                        </h4>
                        <span><?php esc_html_e('Adds an invisible field to your form. Humans can\'t see it, but bots often fill it. If this hidden field has data, the submission is blocked as spam. This traps bots without affecting real users.', 'contact-forms-anti-spam'); ?></span>
                    </div>  
                </div><!-- end of maspik-honeypot-wrap -->

                <?php if( efas_if_plugin_is_active('elementor-pro') ) {  ?>
                    <div class="maspik-txt-custom-msg-head togglewrap maspik-block-inquiry-wrap">
                        <?php echo maspik_toggle_button('NeedPageurl', 'NeedPageurl', 'NeedPageurl', 'maspik-needpageurl togglebutton',"","",['NeedPageurl']); ?>
                            <div>
                                <h4> <?php esc_html_e('Elementor Bot detector', 'contact-forms-anti-spam'); ?> </h4>
                                <span><?php esc_html_e('In this option we block bots from sending spam automatically, its mostly succeed to catch about 30% of the spam', 'contact-forms-anti-spam'); ?></span>
                        </div>  
                    </div><!-- end of maspik-block-inquiry-wrap -->
                <?php  } 

                 maspik_save_button_show() 
                 ?>


            </div>

            <!-- Accordion Item - End main check -->
            <!-- Accordion Item - Language Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-lang-field <?php echo maspik_add_pro_class() ?> ">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Language restrictions', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                        <span class="maspik-accordion-subheader"></span>
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <?php maspik_get_pro() ?>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-accordion-subtitle-wrap">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e('Language Required', 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("ONLY accepts form submissions containing at least one character in one of your selected languages.");
                            ?>
                        </div> <!--end of maspik-accordion-subtitle-wrap-->
                                
                        <div class="maspik-main-list-wrap maspik-select-list">

                            <?php 
                                echo create_maspik_select("lang_needed", "maspik-lang-need", efas_array_of_lang_needed());                                 
                                maspik_spam_api_list('lang_needed', efas_array_of_lang_needed());
                            ?>    

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext">
                            <span class="text-caution">
                                <?php esc_html_e('Caution:', 'contact-forms-anti-spam'); ?>
                            </span>
                        <?php esc_html_e('When specifying Latin-based languages (e.g., Dutch, French), the check includes language-specific punctuation and letters A to Z (including English). This is to avoid false positives when certain punctuation marks are not used.', 'contact-forms-anti-spam'); ?>
                        </span>
                                    
                        <div class="maspik-custom-msg-wrap">
                            <div class="maspik-txt-custom-msg-head togglewrap">
                                <?php echo maspik_toggle_button('lang_need_custom_message_toggle', 'lang_need_custom_message_toggle', 'lang_need_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",['custom_error_message_lang_needed']); ?>
                                    
                                <h4> <?php esc_html_e('Custom validation error message', 'contact-forms-anti-spam'); ?> </h4>
                            </div>

                            <div class="maspik-custom-msg-box togglebox">
                                <?php echo create_maspik_textarea('custom_error_message_lang_needed', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                    
                            </div>
                                    
                        </div><!-- end of maspik-custom-msg-wrap -->


                        <!---- Language section divider S---------->
                        <div class = 'maspik-simple-divider'></div>
                        <!---- Language section divider E---------->



                        <div class="maspik-accordion-subtitle-wrap">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e('Language Forbidden', 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("Select the languages you wish to block from filling out your forms.");
                            ?>
                        </div> <!--end of aspik-accordion-subtitle-wrap-->
                                
                        <div class="maspik-main-list-wrap maspik-select-list">

                            <?php 
                                echo create_maspik_select("lang_forbidden", "maspik-lang-forbidden", efas_array_of_lang_forbidden());
                                maspik_spam_api_list('lang_forbidden', efas_array_of_lang_forbidden());                           
                            ?>      

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext"><?php 
                                        esc_html_e('If there is even one character from one of these languages in the text fields, it will be marked as spam and blocked.', 'contact-forms-anti-spam'); 
                                        echo "<br>";
                                        echo "<span class='text-caution'>";
                                        esc_html_e('Caution:', 'contact-forms-anti-spam');
                                        echo " </span>";
                                        esc_html_e('When blocking Latin languages in an individual (such as: Dutch, French), the chack is in the punctuation letters (But they are not always in use). Its to prevent false positive.', 'contact-forms-anti-spam'); ?>
                                        
                        </span>
                                    
                        <div class="maspik-custom-msg-wrap">
                            <div class="maspik-txt-custom-msg-head togglewrap">
                                <?php echo maspik_toggle_button('lang_forbidden_custom_message_toggle', 'lang_forbidden_custom_message_toggle', 'lang_forbidden_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",['custom_error_message_lang_forbidden']); ?>
                                    
                                <h4> <?php esc_html_e('Custom validation error message', 'contact-forms-anti-spam'); ?> </h4>
                            </div>

                            <div class="maspik-custom-msg-box togglebox">
                                <?php echo create_maspik_textarea('custom_error_message_lang_forbidden', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                    
                            </div>
                                    
                        </div><!-- end of maspik-custom-msg-wrap -->

                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Country Field - Custom -->
            <div class="maspik-accordion-item has-high-tooltip maspik-accordion-country-field <?php echo maspik_add_pro_class() ?> ">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('Geolocation restrictions', 'contact-forms-anti-spam'); ?>
                        </h4><!--Accordion Title-->
                        <?php 
                                maspik_tooltip("Choose either to allow or to block and enter the countries in the next field.
                                <em><br><br>If <strong>allowed</strong>, only forms from these countries will be accepted.
                                <br>If <strong>blocked</strong>, all countries in the following list will be blocked.</em>");
                        ?>
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <?php maspik_get_pro() ?>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                                
                        <div class="maspik-select-list">
                            <?php 
                            $is_spi_stronger = efas_get_spam_api('country_blacklist') && efas_get_spam_api('AllowedOrBlockCountries') && efas_get_spam_api('AllowedOrBlockCountries',"string") != 'ignore';
                        
                            $attr = $is_spi_stronger ? "disabled='disabled'" : false;
                            echo $is_spi_stronger ? "<span><b>Setting disabled and managed by Maspik deshbord</b></span>" : "";
                            ?>
                            <div class="maspik-main-list-wrap">
                                
                                <?php 
                                    echo maspik_simple_dropdown('AllowedOrBlockCountries', 'maspik-country-dropdown' , 
                                    array(
                                        'Allow' => 'allow',
                                        'Block' => 'block'

                                    ),$attr);
                                    echo create_maspik_select("country_blacklist", "country_blacklist", efas_array_of_countries(),$attr);                                 
                                ?> 
                            </div>
                                <?php
                                if($is_spi_stronger){ 
                                    maspik_spam_api_list('AllowedOrBlockCountries'); 
                                    maspik_spam_api_list('country_blacklist', efas_array_of_countries());
                                }
                            ?>
                        </div> <!-- end of maspik-main-list-wrap -->
                                    
                        <div class="maspik-custom-msg-wrap">
                            <div class="maspik-txt-custom-msg-head togglewrap">
                                <?php echo maspik_toggle_button('country_custom_message_toggle', 'country_custom_message_toggle', 'country_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",["custom_error_message_country_blacklist"]); ?>
                                    
                                <h4> <?php esc_html_e('Custom validation error message', 'contact-forms-anti-spam'); ?> </h4>
                            </div>

                            <div class="maspik-custom-msg-box togglebox">
                                <?php echo create_maspik_textarea('custom_error_message_country_blacklist', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                    
                            </div>
                                    
                        </div><!-- end of maspik-custom-msg-wrap -->
                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Maspik API Field - Custom -->
            <div class="maspik-accordion-item has-high-tooltip maspik-accordion-maspik-api-field <?php echo maspik_add_pro_class() ?> ">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('MASPIK Dashboard', 'contact-forms-anti-spam'); ?>
                        </h4><!--Accordion Title-->
                        <?php 
                                maspik_tooltip("Every day, the API file downloads new data from the API server.
                                <em>If you would like to manually refresh now, just click the Reset API File button.</em>");
                        ?>
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <?php maspik_get_pro() ?>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                                                            
                        <div class="maspik-popular-spam-wrap">
                            <div class="maspik-txt-custom-msg-head togglewrap">
                                <?php echo maspik_toggle_button('popular_spam', 'popular_spam', 'popular_spam', 'maspik-toggle-custom-message'); ?>
                                 <div>
                                    <h4> <?php esc_html_e('Auto-populate spam phrases', 'contact-forms-anti-spam'); ?> </h4>
                                    <span><?php esc_html_e('Popular spam words from', 'contact-forms-anti-spam'); ?> <a target = "_blank" href="https://wpmaspik.com/public-api/">
                                        <?php esc_html_e('Maspik spam blacklist', 'contact-forms-anti-spam'); ?></a></span>
                                </div>   
                            </div>
                            
                                    
                        </div><!-- end of maspik-popular-spam-wrap -->
                        
                        <!---- Language section divider S---------->
                        <div class = 'maspik-simple-divider'></div>
                        <!---- Language section divider E---------->

                        <div class="maspik-setting-info">
                            <h4><?php esc_html_e('Maspik Dashboard ID', 'contact-forms-anti-spam'); ?></h4>
                            <div class = 'maspik-status-wrap'>
                                
                                
                                    <?php
                                    
                                        echo "<span>" . esc_html__('Status', 'contact-forms-anti-spam') . "</span>";
                                        echo "<span class='maspik-api-status ";
                                        if( check_maspik_api_values() ){
                                            echo "connected'> " . esc_html__('Connected', 'contact-forms-anti-spam');
                                        }else{
                                            echo "not-connected'> " . esc_html__('Not Connected', 'contact-forms-anti-spam');
                                        }
                                    
                                    ?>
                                </span>

                            </div>

                        </div> <!--end of maspik-setting-info-->
                        <span><?php esc_html_e('Creat your own single Dashboard for managing multiple websites, at', 'contact-forms-anti-spam'); ?> <a target = "_blank" href="https://wpmaspik.com/add-your-private-api/?inplugin">
                        <?php esc_html_e('WpMaspik', 'contact-forms-anti-spam'); ?></a> <?php esc_html_e('website', 'contact-forms-anti-spam'); ?></span>

                        <div class="maspik-main-list-wrap">
                            <?php 
                                echo create_maspik_input('private_file_id', 'maspik-inputbox', 'number');                      
                            ?> 
                        </div> <!-- end of maspik-main-list-wrap -->

                        <div class="maspik-api-buttons-warp">
                        <?php 
                            maspik_save_button_show('Refresh API file', 'maspik-api-refresh maspik-btn-outline','maspik-api-refresh-btn');


                            maspik_save_button_show('Verify and Save', 'maspik-api-save','maspik-api-save-btn') ?>
                        </div>
                    </div>
                </div>
            </div>




            <div class="maspik-section-head maspik-more-setting">
                
                <h2 class='maspik-title maspik-bl-title'><?php esc_html_e('By Field Options', 'contact-forms-anti-spam'); ?></h2>
                <p>
                    <?php esc_html_e('Create a list of words or phrases you want to block.', 'contact-forms-anti-spam'); ?><br>
                    <?php esc_html_e('Each term should be on a separate line.', 'contact-forms-anti-spam'); ?><br>
                    <?php esc_html_e('The system is not case-sensitive', 'contact-forms-anti-spam'); ?><br>
                </p>
                <p><?php esc_html_e('Learn more about those option in our documentation', 'contact-forms-anti-spam'); ?> <a target="_blank" href="https://wpmaspik.com/documentation/?fromplugin"><?php esc_html_e('here', 'contact-forms-anti-spam'); ?></a>.</p>


            </div>

            <!-- Accordion Item - Text Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-text-field">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Text Fields', 'contact-forms-anti-spam');?></h4><!--Accordion Title-->
                        <span class="maspik-accordion-subheader">
                            <?php esc_html_e('(Usually Name/Subject)', 'contact-forms-anti-spam');?></span>
                    </div>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-setting-info">
                            <?php 
                                maspik_tooltip("If the text value CONTAINS one of the given values, it will be marked as spam and blocked.");
                                    
                                maspik_popup("Eric jones|SEO|ranking|currency|click here", "Text field", "See examples" ,"visibility");
                            ?>
                        </div> <!--end of maspik-setting-info-->
                                
                        <div class="maspik-main-list-wrap maspik-textfield-list">

                            <?php
                                echo create_maspik_textarea('text_blacklist', 6, 80, 'maspik-textarea' , 'Seo&#10;Eric jones&#10;Crypto&#10;...');
                                    
                                maspik_spam_api_list('text_field');
                            ?>      

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext">
                        <?php esc_html_e('Wildcard patterns are accepted. asterisk * symbol is necessary for the recognition of the wildcard.', 'contact-forms-anti-spam'); ?></span>
                                    
                        <div class="maspik-limit-char-wrap">
                            <div class="maspik-limit-char-head togglewrap">
                                <?php
                                    echo maspik_toggle_button('text_limit_toggle', 'text_limit_toggle', 'text_limit_toggle', 'maspik-toggle-text-limit togglebutton',"","",['MinCharactersInTextField','MaxCharactersInTextField','custom_error_message_MaxCharactersInTextField']);
                                            
                                    echo "<h4>" . esc_html__('Limit Characters', 'contact-forms-anti-spam') . "</h4>";

                                    maspik_tooltip("If the text field contains more characters that this value, it will be considered spam and it will be blocked.");                             
                                ?>
                            </div>

                            <div class="maspik-limit-char-box togglebox">
                                <div class = 'maspik-minmax-wrap'>
                                <?php 

                                echo create_maspik_numbox("text_limit_min", "MinCharactersInTextField", "character-limit" , "Min" ,'' ,1,30);
                                
                                echo create_maspik_numbox("text_limit_max", "MaxCharactersInTextField", "character-limit" , "Max",'' ,6,1000);

                                ?>
                                </div>
                                        
                                <span class="maspik-subtext">
                                        <?php 
                                        esc_html_e('Entries with less than Min or more than Max characters will be blocked', 'contact-forms-anti-spam');
                                        ?>
                                </span>
                           

                                <div class="maspik-custom-msg-wrap">
                                    <div class="maspik-txt-custom-msg-head togglewrap">
                                        <?php echo maspik_toggle_button('text_custom_message_toggle', 'text_custom_message_toggle', 'text_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",['custom_error_message_MaxCharactersInTextField']); ?>
                                            
                                        <h4> <?php esc_html_e('Character limit custom validation error message', 'contact-forms-anti-spam'); ?> </h4>
                                    </div>

                                    <div class="maspik-custom-msg-box togglebox">
                                        <?php echo create_maspik_textarea('custom_error_message_MaxCharactersInTextField', 2, 80, 'maspik-textarea', 'error-message'); ?>      
                                    </div>
                                    
                                </div><!-- end of maspik-custom-msg-wrap -->


                            </div><!-- end of togglebox -->
                        </div><!-- end of maspik-limit-char-wrap -->

                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Email Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-email-field">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Email Fields', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                        <span class="maspik-accordion-subheader"></span>
                    </div>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-setting-info">
                            <?php 
                            maspik_tooltip("If the text value is EQUAL to one of the values above, MASPIK will tag it as spam and it will be blocked.");
                                
                            maspik_popup("georginahaynes620@gmail.com|ericjonesonline@outlook.com|*.ru|*+*@*.*|/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.ru\b/|eric*@*.com|xrumer888@outlook.com", "Email field", "See examples" ,"visibility");
                                ?>
                        </div> <!--end of maspik-setting-info-->
                            
                        <div class="maspik-main-list-wrap maspik-textfield-list">

                            <?php 
                                echo create_maspik_textarea('emails_blacklist', 6, 80, 'maspik-textarea');
                                 
                                maspik_spam_api_list('email_field');
                            ?>      

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext"><?php esc_html_e('Wildcard patterns are accepted. asterisk * Or question mark ? symbol are necessary for the recognition of the wildcard.', 'contact-forms-anti-spam'); ?><br>
                        <?php esc_html_e('Regex must start and end with a slash /', 'contact-forms-anti-spam'); ?>

                        </span>
                                
                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Textarea Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-textarea-field">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Textarea Fields', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                        <span class="maspik-accordion-subheader"><?php esc_html_e('(Usually Message/Long text)', 'contact-forms-anti-spam'); ?></span>
                    </div>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-setting-info">
                            <?php 
                                maspik_tooltip("If the Textarea value CONTAINS one of the given values, it will be marked as spam and blocked.");
                                    
                                echo "<div class = 'maspik-small-btn-wrap'>";
                                    maspik_popup("submit your website|seo|ranking|currency|click here", "Textarea field",  "See examples" ,"visibility");
                                echo "</div>";
                            ?>
                        </div> <!--end of maspik-setting-info-->
                                
                        <div class="maspik-main-list-wrap maspik-textareafield-list">

                            <?php 
                                echo create_maspik_textarea('textarea_blacklist', 6, 80, 'maspik-textarea' , 'Seo&#10;Ranking&#10;Click here&#10;Google&#10;Crypto&#10;...');
                                    
                                maspik_spam_api_list('textarea_field');
                            ?>      

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext"><?php esc_html_e('Try to use full sentences that you typically find in spam emails, rather than single words.', 'contact-forms-anti-spam'); ?></span>

                        <div class="maspik-limit-char-head togglewrap">
                                <?php
                                            
                                    echo maspik_toggle_button('textarea_link_limit_toggle', 'textarea_link_limit_toggle', 'textarea_link_limit_toggle', 'maspik-toggle-text-limit togglebutton',"","",['contain_links']);
                                            
                                    echo "<h4> Limit Links </h4>";

                                    maspik_tooltip("Spammers tend to include links.
                                    <br>If there is no reason for anyone to send links when completing your forms, set this to 0");
                                    ?>
                            </div>

                            <div class="maspik-limit-char-box togglebox">
                                <?php echo create_maspik_numbox("text_limit_link", "contain_links", "link-limit" , "Maximum number of Links", "", "0") ?>

                        </div><!-- end of maspik-limit-link-wrap -->
                                    
                        <div class="maspik-limit-char-wrap">
                            <div class="maspik-limit-char-head togglewrap">
                                <?php
                                            
                                    echo maspik_toggle_button('textarea_limit_toggle', 'textarea_limit_toggle', 'textarea_limit_toggle', 'maspik-toggle-textarea-limit togglebutton',"","",['MinCharactersInTextAreaField','MaxCharactersInTextAreaField']);
                                            
                                    echo "<h4> Limit Characters </h4>";

                                    maspik_tooltip("If the text field contains more characters that this value, it will be considered spam and it will be blocked.");                             
                                ?>
                            </div>

                            <div class="maspik-limit-char-box togglebox">

                            <div class = 'maspik-minmax-wrap'>
                                <?php 

                                echo create_maspik_numbox("text_limit_min", "MinCharactersInTextAreaField", "character-limit" , "Min",'' ,1,30);
                                
                                echo create_maspik_numbox("textarea_limit_max", "MaxCharactersInTextAreaField", "character-limit" , "Max", '', 6,100000) 
                                ?>
                            </div>
                                        
                            <span class="maspik-subtext">
                                        <?php esc_html_e("Entries with less than <strong>Min</strong> or more than <strong>Max</strong> characters will be blocked", "contact-forms-anti-spam"); ?>
                                </span>

                                <div class="maspik-custom-msg-wrap">
                                    <div class="maspik-txt-custom-msg-head togglewrap">
                                        <?php echo maspik_toggle_button('textarea_custom_message_toggle', 'textarea_custom_message_toggle', 'textarea_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",['custom_error_message_MaxCharactersInTextField']); ?>
                                            
                                        <h4> <?php esc_html_e('Character limit custom validation error message', 'contact-forms-anti-spam'); ?> </h4>
                                    </div>

                                    <div class="maspik-custom-msg-box togglebox">
                                        <?php echo create_maspik_textarea('custom_error_message_MaxCharactersInTextAreaField', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                            
                                    </div>
                                        
                                </div><!-- end of maspik-custom-msg-wrap -->

                            </div><!-- end of togglebox -->
                        </div><!-- end of maspik-limit-char-wrap -->

                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Phone Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-phone-field">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Whitelist Phone Fields formts', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                    </div>
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-setting-info">
                            <?php 
                                maspik_tooltip("List of accepted phone formats, one per line; if the phone field contains a phone number that does not fit into one of the following formats, it will be marked as spam.");
                                echo '<span class="help-text">'.esc_html__("Only the following phone formats will be allowed.", 'contact-forms-anti-spam');
                                echo "<br>".esc_html__("leave empty to disable this option.", 'contact-forms-anti-spam');
                                echo "</span>";  
                                maspik_popup("???-???-????|+*|+[1-9]-*|{+*-*,???-???-????}|[0-9][0-9][0-9]-*|/[0-9]{3}-[0-9]{3}-[0-9]{4}/|0*", "Phone field", "See examples" ,"visibility");
                            ?>
                        </div> <!--end of maspik-setting-info-->
                                
                        <div class="maspik-main-list-wrap maspik-textfield-list">

                            <?php 
                                echo create_maspik_textarea('tel_formats', 6, 80, 'maspik-textarea'); 
                                    
                                maspik_spam_api_list('phone_format');
                            ?>   

                        </div> <!-- end of maspik-main-list-wrap -->
                        <span class="maspik-subtext">
                        <?php esc_html_e('? represents any single digit.', 'contact-forms-anti-spam'); ?><br>
                        <?php esc_html_e('* represents any sequence of digits.', 'contact-forms-anti-spam'); ?><br>
                            <?php esc_html_e(' You can get more information', 'contact-forms-anti-spam'); ?>
                            <a href="https://wpmaspik.com/documentation/phone-field/" target="_blank">
                            <?php esc_html_e('HERE', 'contact-forms-anti-spam'); ?></a>    
                        </span>

                        <div class="maspik-custom-msg-wrap">
                            <div class="maspik-txt-custom-msg-head togglewrap">
                                <?php echo maspik_toggle_button('phone_custom_message_toggle', 'phone_custom_message_toggle', 'phone_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",['custom_error_message_tel_formats']); ?>
                                    
                                <h4> Custom validation error message </h4>
                            </div>

                            <div class="maspik-custom-msg-box togglebox">
                                <?php echo create_maspik_textarea('custom_error_message_tel_formats', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                    
                             </div>
                                    
                        </div><!-- end of maspik-custom-msg-wrap -->

                        <div class="maspik-limit-char-wrap">
                            <div class="maspik-limit-char-head togglewrap">
                                <?php
                                            
                                    echo maspik_toggle_button('tel_limit_toggle', 'tel_limit_toggle', 'tel_limit_toggle', 'maspik-toggle-tel-limit togglebutton',"","",['MinCharactersInPhoneField',"MaxCharactersInPhoneField","custom_error_message_MaxCharactersInPhoneField"]);
                                            
                                    echo '<h4>' . esc_html__('Limit Characters', 'contact-forms-anti-spam') . '</h4>';

                                    maspik_tooltip("If the text field contains more characters that this value, it will be considered spam and it will be blocked.");                             
                                ?>
                            </div>

                            <div class="maspik-limit-char-box togglebox">
                                <div class = 'maspik-minmax-wrap'>
                                    <?php 

                                    echo create_maspik_numbox("phone_limit_min", "MinCharactersInPhoneField", "character-limit" , "Min");
                                    
                                    echo create_maspik_numbox("phone_limit_max", "MaxCharactersInPhoneField", "character-limit" , "Max");

                                    ?>
                                </div>
                                            
                                <span class="maspik-subtext">
                                        <?php esc_html_e("Entries with less than <strong>Min</strong> or more than <strong>Max</strong> characters will be blocked", "contact-forms-anti-spam"); ?>
                                </span>
                            
                                <div class="maspik-custom-msg-wrap">
                                    <div class="maspik-txt-custom-msg-head togglewrap">
                                            <?php echo maspik_toggle_button('phone_limit_custom_message_toggle', 'phone_limit_custom_message_toggle', 'phone_limit_custom_message_toggle', 'maspik-toggle-custom-message togglebutton',"","",["custom_error_message_MaxCharactersInPhoneField"]); ?>
                                        
                                        <h4><?php esc_html_e('Character limit custom validation error message', 'contact-forms-anti-spam'); ?></h4>
                                        
                                    </div>

                                    <div class="maspik-custom-msg-box togglebox">
                                        <?php echo create_maspik_textarea('custom_error_message_MaxCharactersInPhoneField', 2, 80, 'maspik-textarea', 'error-message'); ?>
                                        
                                    </div>
                                        
                                </div><!-- end of maspik-custom-msg-wrap -->


                            </div><!-- end of togglebox -->
                        </div><!-- end of maspik-limit-char-wrap -->                                    

                        <?php maspik_save_button_show() ?>
                    </div>
                </div>
            </div>


            <!-- MORE OPTIONS HEADER -->
            <div class="maspik-section-headhead maspik-more-setting"> 
                <h2 class='maspik-title maspik-bl-header'><?php esc_html_e('More Options', 'contact-forms-anti-spam'); ?></h2>
            </div>
            <!-- MORE OPTIONS HEADER - END -->

            <!-- Accordion Item - Ongoing Experiments Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-general-field">
                <div class="maspik-accordion-header">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Ongoing Experiments', 'contact-forms-anti-spam'); ?></h4>
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                        <div class="maspik-txt-custom-msg-head togglewrap maspik-honeypot-wrap">
                            <?php echo maspik_toggle_button('maspikTimeCheck', 'maspikTimeCheck', 'maspikTimeCheck', 'maspik-honeypot togglebutton',"",""); ?>
                                <div>
                                    <h4> <?php esc_html_e('Time Trap', 'contact-forms-anti-spam'); ?>
                                    </h4>
                                    <span><?php esc_html_e('This feature checks the time elapsed between a user first visiting the site and submitting the form. If the submission happens too quickly (within 4 seconds), it\'s likely automated and will be blocked. This helps prevent rapid-fire spam submissions from bots.', 'contact-forms-anti-spam'); ?></span>
                            </div>  
                        </div><!-- end of maspik-maspikTimeCheck -->
                        <div class="maspik-txt-custom-msg-head togglewrap maspik-honeypot-wrap">
                            <?php echo maspik_toggle_button('maspikYearCheck', 'maspikYearCheck', 'maspikYearCheck', 'maspik-honeypot togglebutton',"",""); ?>
                                <div>
                                    <h4> <?php esc_html_e('JavaScript check', 'contact-forms-anti-spam'); ?> 
                                    </h4>
                                    <span><?php esc_html_e('JavaScript check - This feature adds a hidden field that is automatically filled with the current year using JavaScript. If the submitted year does not match the server\'s current year, it likely means JavaScript is disabled or the form was submitted by a bot. In either case, the submission will be blocked as a security measure.', 'contact-forms-anti-spam'); ?></span>
                            </div>  
                        </div><!-- end of maspik-maspikYearCheck -->

                        <?php 
                            maspik_save_button_show() ?>
                        
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Other Options Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-other-option-field" >
                <div class="maspik-accordion-header" id="ip-blacklist-accordion">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('IP Blacklist and 3rd Party APIs', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content" id="maspik-form-options">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                            
                        <div class="maspik-accordion-subtitle-wrap short-tooltip">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e("List of block IPs", 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("Any IP you enter above will be blocked.
                                One IP per line.");
                            ?>
                        </div> <!--end of maspik-accordion-subtitle-wrap-->
                        <div class="maspik-ip-wrap maspik-main-list-wrap maspik-textfield-list">
                            <?php
                                echo create_maspik_textarea('ip_blacklist', 6, 80, 'maspik-textarea');   
                                maspik_spam_api_list('ip');
                            ?> 
                        </div> <!-- end of maspik-ip-wrap  -->
                        <span class="maspik-subtext"><?php esc_html_e('You can also filter entire CIDR range such as 134.209.0.0/16', 'contact-forms-anti-spam'); ?></span>

                        <!---- 3rd party API divider S---------->
                        <div class = 'maspik-simple-divider'></div>
                        <!---- 3rd party API divider E---------->

                        <div class="maspik-accordion-subtitle-wrap short-tooltip">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e('AbuseIPDB API', 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("AbuseIPDB.com API<br>
                                <em>Recommend not lower than 25 for less false positives.</em>");
                            ?>
                        </div> <!--end of maspik-accordion-subtitle-wrap-->
                        

                        <div class="maspik-abuse-api-wrap maspik-main-list-wrap maspik-textfield-list">

                            <?php echo create_maspik_input('abuseipdb_api', 'maspik-inputbox'); ?>
                            <div class="maspik-threshold-wrap">
                            <?php echo create_maspik_numbox("abuseipdb_score", "abuseipdb_score", "threshold-limit" , "Risk Threshold", "") ?>
                            </div>

                        </div> <!-- end of maspik-abuse-api-wrap  -->
                        <span class="maspik-subtext"><?php esc_html_e('For more infromation', 'contact-forms-anti-spam'); ?> <a target = "_blank" href="https://www.abuseipdb.com/?Maspik-plugin">
                        <?php esc_html_e('AbuseIPDB', 'contact-forms-anti-spam'); ?></a></span>
                        <span class="maspik-subtext"><?php esc_html_e('Leave blank to disable', 'contact-forms-anti-spam'); ?>.</span>
                               <?php maspik_spam_api_list('abuseipdb_api');?>
                        <div class="maspik-accordion-subtitle-wrap short-tooltip add-space-top">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e('Proxycheck.io API', 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("Proxycheck.io API<br>
                                <em>Low risk 0-33, MidHigh risk = 33-66, Dangerous = 66 Above</em>");
                            ?>
                        </div> <!--end of maspik-accordion-subtitle-wrap-->


                         <div class="maspik-abuse-api-wrap maspik-main-list-wrap maspik-textfield-list">

                            <?php echo create_maspik_input('proxycheck_io_api', 'maspik-inputbox'); ?>
                            <div class="maspik-threshold-wrap">
                            <?php echo create_maspik_numbox("proxycheck_io_risk", "proxycheck_io_risk", "threshold-limit" , "Risk Threshold", "") ?>
                            </div>

                        </div> <!-- end of maspik-abuse-api-wrap  -->
                        <span class="maspik-subtext"><?php esc_html_e('For more infromation', 'contact-forms-anti-spam'); ?> <a target = "_blank" href="https://proxycheck.io/?Maspik-plugin">
                        <?php esc_html_e('ProxyCheck', 'contact-forms-anti-spam'); ?></a></span>

                        <span class="maspik-subtext"><?php esc_html_e('Leave blank to disable.', 'contact-forms-anti-spam'); ?></span>
                           
                        <?php maspik_spam_api_list('proxycheck_io_api');?>          

                            <?php  maspik_save_button_show() ?>
                        
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Form Options Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-form-option-field" >
                <div class="maspik-accordion-header" id="form-option-accordion">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Supported forms', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content" id="maspik-form-options">
                    <div class="maspik-accordion-content-wrap hide-form-title">

                        <div class="maspik-cf7-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('contact-form-7') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_cf7', 'maspik_support_cf7', 'maspik_support_cf7', 'maspik-form-switch togglebutton', "form-toggle",efas_if_plugin_is_active('contact-form-7')); 
                            
                            ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Contact from 7', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-cf7-switch-wrap -->

                        <div class="maspik-wp-comment-switch-wrap togglewrap maspik-form-switch-wrap">
                            <?php echo maspik_toggle_button('maspik_support_wp_comment', 'maspik_support_wp_comment', 'maspik_support_wp_comment', 'maspik-form-switch togglebutton', "form-toggle", maspik_get_settings( "maspik_support_wp_comment", 'form-toggle' )); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support wp comment', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-wp-comment-switch-wrap -->

                        <div class="maspik-formidable-switch-wrap togglewrap maspik-form-switch-wrap  <?php echo efas_if_plugin_is_active('formidable') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_formidable_forms', 'maspik_support_formidable_forms', 'maspik_support_formidable_forms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('formidable')); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Formidable', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-formidable-switch-wrap -->

                        <div class="maspik-forminator-switch-wrap togglewrap maspik-form-switch-wrap  <?php echo efas_if_plugin_is_active('forminator') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_forminator_forms', 'maspik_support_forminator_forms', 'maspik_support_forminator_forms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('forminator')); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Forminator', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-forminator-switch-wrap -->
                        
                        <div class="maspik-fluentform-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('fluentforms') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_fluentforms_forms', 'maspik_support_fluentforms_forms', 'maspik_support_fluentforms_forms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('fluentforms')); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Fluentforms', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-fluentform-switch-wrap -->

                        <div class="maspik-bricks-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('bricks') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_bricks_forms', 'maspik_support_bricks_forms', 'maspik_support_bricks_forms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('bricks')); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Bricks forms', 'contact-forms-anti-spam'); ?> </h4>
                            </div>  
                        </div><!-- end of maspik-bricks-switch-wrap -->

                        <div class="maspik-wp-registration-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('Wordpress Registration') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_registration', 'maspik_support_registration', 'maspik_support_registration', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('Wordpress Registration')); ?>
                                <div class="wp-reg">
                                        <h4> <?php esc_html_e('Support WP Registration', 'contact-forms-anti-spam'); ?></h4>
                                        <?php maspik_tooltip('If Anyone can register is checked <br><em>(At WP Options => General).</em>') ?>
                                        
                                </div>  
                        </div><!-- end of maspik-wp-registration-switch-wrap -->

                        <div class="maspik-elementor-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('elementor-pro') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_Elementor_forms', 'maspik_support_Elementor_forms', 'maspik_support_Elementor_forms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('elementor-pro')); ?>
                                <div>
                                    <h4> <?php esc_html_e('Support Elementor forms', 'contact-forms-anti-spam'); ?> </h4>
                                </div>  
                        </div><!-- end of maspik-elementor-switch-wrap -->

                        <div class="maspik-wp-ninjaform-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('ninjaforms') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_ninjaforms', 'maspik_support_ninjaforms', 'maspik_support_ninjaforms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('ninjaforms')); ?>
                                <div class="wp-reg">
                                        <h4> <?php esc_html_e('Support Ninja Forms', 'contact-forms-anti-spam'); ?></h4>
                                        
                                </div>  
                        </div><!-- end of maspik-wp-ninjaform-switch-wrap-->

                        <div class="maspik-wp-jetform-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('jetforms') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_jetforms', 'maspik_support_jetforms', 'maspik_support_jetforms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('jetforms')); ?>
                                <div class="wp-reg">
                                        <h4> <?php esc_html_e('Support Jet Form', 'contact-forms-anti-spam'); ?></h4>
                                        
                                </div>  
                        </div><!-- end of maspik-wp-jetform-switch-wrap-->

                        <div class="maspik-wp-jetform-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('everestforms') == 1 ? 'enabled':'disabled' ?>">
                            <?php echo maspik_toggle_button('maspik_support_everestforms', 'maspik_support_everestforms', 'maspik_support_everestforms', 'maspik-form-switch togglebutton', "form-toggle", efas_if_plugin_is_active('everestforms')); ?>
                                <div class="wp-reg">
                                        <h4> <?php esc_html_e('Support Everest Forms', 'contact-forms-anti-spam'); ?></h4>
                                        
                                </div>  
                        </div><!-- end of maspik-wp-jetform-switch-wrap-->

                        <div class="pro-btn-wrapper <?php echo maspik_add_pro_class() ?>"><?php maspik_get_pro() ?></div>
                        <div class="forms-pro-block <?php echo maspik_add_pro_class() ?>" >
                            

                            <div class="maspik-gravity-form-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('gravityforms') == 1 ? 'enabled':'disabled' ?>">
                                <?php echo maspik_toggle_button('maspik_support_gravity_forms', 'maspik_support_gravity_forms', 'maspik_support_gravity_forms', 'maspik-form-switch togglebutton', "form-toggle", 
                                (efas_if_plugin_is_active('gravityforms') && maspik_proform_togglecheck('Gravityforms')) == 1 ); ?>
                                    <div>
                                        <h4> <?php esc_html_e('Support Gravity Forms', 'contact-forms-anti-spam'); ?> </h4>
                                </div>  
                            </div><!-- end of maspik-gravity-form-switch-wrap -->

                            <div class="maspik-wpforms-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('wpforms') == 1 ? 'enabled':'disabled' ?>">
                                <?php echo maspik_toggle_button('maspik_support_Wpforms', 'maspik_support_Wpforms', 'maspik_support_Wpforms', 'maspik-form-switch togglebutton', "form-toggle", (efas_if_plugin_is_active('Wpforms') && maspik_proform_togglecheck('Wpforms')) == 1 ); 
                                
                                ?>
                                    <div>
                                        <h4> <?php esc_html_e('Support WPforms', 'contact-forms-anti-spam'); ?> </h4>
                                </div>  
                            </div><!-- end of maspik-wpforms-switch-wrap -->

                            <div class="maspik-woo-review-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('woocommerce') == 1 ? 'enabled':'disabled' ?>">
                                <?php echo maspik_toggle_button('maspik_support_woocommerce_review', 'maspik_support_woocommerce_review', 'maspik_support_woocommerce_review', 'maspik-form-switch togglebutton', "form-toggle", 
                                 (efas_if_plugin_is_active('woocommerce') && maspik_proform_togglecheck('Woocommerce Review')) == 1 ); 
                                 ?>
                                    <div>
                                        <h4> <?php esc_html_e('Support Woocommerce Review', 'contact-forms-anti-spam'); ?> </h4>
                                </div>  
                            </div><!-- end of maspik-woo-review-switch-wrap -->

                            <div class="maspik-woo-registration-switch-wrap togglewrap maspik-form-switch-wrap <?php echo efas_if_plugin_is_active('woocommerce') == 1 ? 'enabled':'disabled' ?>">
                                <?php echo maspik_toggle_button('maspik_support_Woocommerce_registration', 'maspik_support_Woocommerce_registration', 'maspik_support_Woocommerce_registration', 'maspik-form-switch togglebutton', "form-toggle", 
                                (efas_if_plugin_is_active('woocommerce') && maspik_proform_togglecheck('Woocommerce Registration')) == 1 ); ?>
                                    <div>
                                        <h4> <?php esc_html_e('Support Woocommerce Registration', 'contact-forms-anti-spam'); ?> </h4>
                                </div>  
                            </div><!-- end of maspik-woo-registration-switch-wrap -->

                        </div>
                    
                        <?php 
                            maspik_save_button_show() ?>
                        
                    </div>
                </div>
            </div>

            <!-- Accordion Item - Other Options Field - Custom -->
            <div class="maspik-accordion-item maspik-accordion-other-option-field" >
                <div class="maspik-accordion-header" id="spam-log-accordion">
                    <div class="mpk-acc-header-texts">
                        <h4 class="maspik-header maspik-accordion-header-text"><?php esc_html_e('Spam Log and Validation message', 'contact-forms-anti-spam'); ?></h4><!--Accordion Title-->
                    </div>
                    <div class = "maspik-pro-button-wrap">
                        <span class="maspik-acc-arrow"><span class="dashicons dashicons-arrow-right"></span>
                    </div>
                </div>
                    
                <div class="maspik-accordion-content" id="maspik-form-options">
                    <div class="maspik-accordion-content-wrap hide-form-title">
                            
                        <div class="maspik-accordion-subtitle-wrap add-space-top short-tooltip">
                            <h3 class="maspik-accordion-subtitle"><?php esc_html_e('Default validation error message', 'contact-forms-anti-spam'); ?></h3>
                            <?php 
                                maspik_tooltip("This is the error message that the user/spammer will receive.");
                            ?>
                        </div> <!--end of maspik-accordion-subtitle-wrap-->

                        <div class="maspik-general-custom-msg-wrap maspik-main-list-wrap maspik-textfield-list">

                            <?php
                                echo create_maspik_textarea('error_message', 2, 80, 'maspik-textarea', 'error-message');
                            ?>  

                        </div> <!-- end of maspik-general-custom-msg-wrap  -->
                        <?php maspik_spam_api_list('error_message'); ?>

                        <!---- Language section divider S---------->
                        <div class = 'maspik-simple-divider'></div>
                        <!---- Language section divider E---------->
                    
                        <div class="maspik-limit-char-head togglewrap">
                            <?php          
                                echo maspik_toggle_button('maspik_Store_log', 'maspik_Store_log', 'maspik_Store_log', 'maspik-toggle-store-log togglebutton', 'yes-no' , 1);
                                                
                                echo "<h4>" . esc_html__("Spam Log", "contact-forms-anti-spam"). "</h4>";

                                maspik_tooltip(esc_html("If disabled, the Log of the blocked spam will not be Saved"));
                            ?>
                        </div>

                        <div class="maspik-limit-char-box togglebox">
                            <?php 
                                echo create_maspik_numbox("spam_log_limit", "spam_log_limit", "spam_log_limit" , "Entry limit", "2000", "","");
                            ?>
                        </div> <!-- end of spam log toggle box -->
                             <?php 

                            /* // Remove add_country_to_emails

                        <!-- <div class="maspik-add-country-switch-wrap togglewrap maspik-more-options-switch-wrap">

                           
                                echo maspik_toggle_button('add_country_to_emails', 'add_country_to_emails', 'add_country_to_emails', 'maspik-more-options-switch togglebutton', 'other_options' );
                                
                                echo "<h4>". esc_html__("Add country name to the bottom of email content", "contact-forms-anti-spam"). "</h4>";
                                    
                                maspik_tooltip(esc_html("To identify countries that send spam, and block them"));
                        </div> --> <!-- end of mmaspik-add-country-switch-wrap -->
                             

                        <div class="maspik-disable-comment-switch-wrap togglewrap maspik-more-options-switch-wrap">

                            <?php 
                            
                                echo maspik_toggle_button('disable_comments', 'disable_comments', 'disable_comments', 'maspik-more-options-switch togglebutton' , 'other_options' );
                                
                                echo "<h4>". esc_html__(" Disable comments in WordPress", "contact-forms-anti-spam"). "</h4>";
                                    
                                maspik_tooltip(esc_html__("Disable comments on *ALL* types of posts and remove/hide any existing comments from displaying, as well as hiding the comment forms.
                                <br>If you toggle <strong>on</strong> comments will be disable.", "contact-forms-anti-spam") );
                                    
                             ?>
                        </div><!-- end of mmaspik-add-country-switch-wrap -->
                        */   
                        ?>
                        <div class="maspik-shere-data-switch-wrap togglewrap maspik-more-options-switch-wrap">

                            <?php 
                            
                                echo maspik_toggle_button('shere_data', 'shere_data', 'shere_data', 'maspik-more-options-switch togglebutton' , 'other_options' );
                                
                                echo "<h4>". esc_html__("Share anonymous data to improve spam protection", "contact-forms-anti-spam"). "</h4>";
                                    
                                maspik_tooltip(esc_html__("By allowing us to track usage data, we can better help you by knowing which WordPress configurations, themes, and plugins to test and which options are needed.") );
                                    
                             ?>
                        </div><!-- end of mmaspik-add-country-switch-wrap -->


                    
                        <?php 
                            maspik_save_button_show() ?>
                        
                    </div>
                </div>
            </div>
            

            <?php wp_nonce_field('maspik_save_settings_action', 'maspik_save_settings_nonce'); ?>
            </form> <!--End of content to submit -->
        
            </div><!-- End of Accordion content -->
            
            
            <?php echo get_maspik_footer(); ?>


            </div><!--end of blacklist opts -->
            
        <!--Test form here -->
            <div class="maspik-test-form form-container test-form">
                <form name="frmContact"  id="frmContact" method="post"  enctype="multipart/form-data">
                        <h3 class="maspik-test-form-head maspik-header"><?php esc_html_e('Playground - Test form', 'contact-forms-anti-spam'); ?></h3>    
                        
                        <p  class="maspik-test-form-sub"><?php esc_html_e('This form allows you to test your entries to see if they will be blocked.', 'contact-forms-anti-spam'); ?>
                    <div class="input-row">
                        <label><?php esc_html_e('Name (Text field)', 'contact-forms-anti-spam'); ?></label> <span
                            id="userName-info" class="info"></span> <input type="text"
                            class="input-field" name="userName" id="userName" />
                        <span class="note" id="note-name"></span>
                    </div>
                    <div class="input-row">
                        <label><?php esc_html_e('Email (Email field)', 'contact-forms-anti-spam'); ?></label> <span id="userEmail-info" class="info"></span>
                        <input type="email" class="input-field" name="userEmail"
                            id="userEmail" />
                        <span class="note" id="note-email"></span>
                    </div>
                    <div class="input-row">
                        <label><?php esc_html_e('Phone (Phone field)', 'contact-forms-anti-spam'); ?></label> <span id="subject-info" class="info"></span>
                        <input type="tel" class="input-field" name="tel" id="tel" />
                        <span class="note" id="note-tel"></span>
                    </div>
                    <div class="input-row">
                        <label><?php esc_html_e('Message (Text area field)', 'contact-forms-anti-spam'); ?></label> <span id="userMessage-info" class="info"></span>
                        <textarea name="content" id="content" class="input-field" cols="60"
                            rows="3"></textarea>
                        <span class="note" id="note-textarea"></span>
                    </div>
                    <div>
                        <input type="submit" name="send" class="btn-submit maspik-btn" value="<?php esc_html_e('Check', 'contact-forms-anti-spam'); ?>" />
                    </div>
                    <br><strong><?php esc_html_e('* Please save changes before checking.', 'contact-forms-anti-spam'); ?></strong>
                    <br><div id="statusMessage"></div>
                </form>

            </div> 
        <!-- end test form -->


        </div><!--end of body -->
    </div>
      
    <div class=forms-warp>
        <div id="popup-background"></div>        
    </div><!-- end forms warp -->

    <div id="pop-up-example" class="maspik-popup-wrap">
        <h3 class="maspik-popup-title-wrap"><?php esc_html_e('Example for', 'contact-forms-anti-spam'); ?> <span class="maspik-popup-title"><?php esc_html_e('Text field', 'contact-forms-anti-spam'); ?></span></h3>
        <p class="pop-up-subtext"><?php esc_html_e('Here you can see an example options for the', 'contact-forms-anti-spam'); ?>  <span class="maspik-popup-title"><?php esc_html_e('text field', 'contact-forms-anti-spam'); ?></span></p>
        <button class="close-popup"><span class="dashicons dashicons-no-alt"></span></button>
            <div class="data-array-wrap"><div class="data-array-here maspik-custom-scroll">
                <ul>
                <!-- Example words will be dynamically inserted here -->
                </ul>
            </div>
        </div>
        <div class="maspik-copy-btn-wrap">
            <button class="copy maspik-btn"><?php esc_html_e('Copy list', 'contact-forms-anti-spam'); ?></button>
        </div>
        <div id="copy-message" style="display: none;"><?php esc_html_e('List copied!', 'contact-forms-anti-spam'); ?></div>

    </div>

    <div id="pop-up-shortcode" class="maspik-popup-wrap">
        <h3 class="maspik-popup-title-wrap"><span class="maspik-popup-title"><?php esc_html_e('Shortcode List', 'contact-forms-anti-spam'); ?></span></h3>
        <p class="pop-up-subtext"><?php esc_html_e('You can also use the following shortcodes:', 'contact-forms-anti-spam'); ?></p>
        <button class="close-popup"><span class="dashicons dashicons-no-alt"></span></button>
            <div class="data-array-wrap"><div class="data-array-here maspik-custom-scroll">
                <ul>
                <!-- Example words will be dynamically inserted here -->
                </ul>
            </div>
        </div>
    </div>

    <div id="pop-up-ip-verification" class="maspik-popup-wrap" style="width: 700px; max-width: 90%; overflow: scroll; max-height: 60vh;">
        <button class="close-popup"><span class="dashicons dashicons-no-alt"></span></button>
        <?php echo IP_Verification_popup_content(); ?>
    </div>

<?php
wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);

?>


<script>

//Accordion JS code

    var acc = document.getElementsByClassName("maspik-accordion-header");
        var i;
        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }
               
            });
        }


    var formacc = document.getElementsByClassName("form-opt-toggle");
    var target = document.getElementById("form-option-accordion");
    var transitionDuration = 200;


        for (i = 0; i < formacc.length; i++) {
            formacc[i].addEventListener("click", function() {
                if (target && !target.classList.contains("active")) {
                    target.classList.add("active");
                    var panel = target.nextElementSibling;
                    if (panel.style.maxHeight) {
                        panel.style.maxHeight = null;
                    }else {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    }
                    
                }

                setTimeout(function() {
                    target.scrollIntoView({ behavior: "smooth" });
                }, transitionDuration);
               
            });
        }

        
    

//Accordion JS code END

// Hide - Show on Toggle

    var checkboxes = document.querySelectorAll('.togglebutton');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {

        var nextDiv = this.closest('div').nextElementSibling;
       
            while (nextDiv) {
                if (nextDiv.classList.contains('togglebox')) {
                    if (this.checked) {
                        nextDiv.classList.add('showontoggle');
                    } else {
                        nextDiv.classList.remove('showontoggle');
                    }
                    break;
                }
                nextDiv = nextDiv.nextElementSibling;
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
    var checkboxes = document.querySelectorAll('.togglebutton');
    
        checkboxes.forEach(function(checkbox) {
            
        var nextDiv = checkbox.closest('div').nextElementSibling;

        
        
       
            while (nextDiv) {
                if (nextDiv.classList.contains('togglebox')) {
                    if (checkbox.checked) {
                        nextDiv.classList.add('showontoggle');
                    } else {
                        nextDiv.classList.remove('showontoggle');
                    }
                    break;
                }
                nextDiv = nextDiv.nextElementSibling;
            }
        });
    });


// Hide - Show on Toggle - END

    jQuery(document).ready(function() {
        jQuery('.maspik-select').select2({
          multiple: true,
          placeholder:"<?php esc_html_e('Select', 'contact-forms-anti-spam' ) ;?>",
        });
    });

    
    // Function to check if the "imported" parameter is present in the URL
    function checkImportedParameter() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('imported') && urlParams.get('imported') === '1') {
            // Show alert
            alert('The import completed successfully.');
            // Remove "imported" parameter from the URL
            urlParams.delete('imported');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            // Redirect to the new URL, effectively refreshing the page
            window.location.href = newUrl;
        }
    }

    // Call the function when the page is loaded
    window.onload = checkImportedParameter;

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.your-button-class');
        const popups = document.querySelectorAll('.maspik-popup-wrap');
        const closeButtons = document.querySelectorAll('.close-popup');
        const popupBackground = document.getElementById('popup-background');

        buttons.forEach((button, index) => {
            button.addEventListener('click', (event) => {
                event.preventDefault(); // Prevent default link behavior
                const popupId = button.dataset.popupId;
                const popup = document.getElementById(popupId);

                if (popup) {
                    popup.classList.toggle('active');
                    popupBackground.style.display = 'block'; // Show background
                    // Update title-here spans
                const titleHereSpans = popup.querySelectorAll('.maspik-popup-title');
                const buttonTitle = button.dataset.title || 'Text field'; // Default value if not provided
                titleHereSpans.forEach(span => {
                    span.innerHTML = buttonTitle;
                });
                
                // Update data-array-here content if provided
                const dataArrayElement = popup.querySelector('.data-array-here ul');
                const dataArray = button.dataset.array;
                if (dataArrayElement && dataArray) {
                    const dataArrayItems = dataArray.split('|');
                    dataArrayElement.innerHTML = ''; // Clear previous data
                    dataArrayItems.forEach(item => {
                        const listItem = document.createElement('li');
                        listItem.textContent = item.trim();
                        dataArrayElement.appendChild(listItem);
                    });
                }
                }
            });
        });

    
        closeButtons.forEach((closeButton, index) => {
            closeButton.addEventListener('click', () => {
                const popup = closeButton.closest('.maspik-popup-wrap');
                if (popup) {
                    popup.classList.remove('active');
                    popupBackground.style.display = 'none'; // Hide background
                }
            });
        });

        // Close popup when clicking outside of it
        document.addEventListener('click', (event) => {
            if (!event.target.closest('.maspik-popup-wrap') && !event.target.closest('.your-button-class')) {
                const activePopups = document.querySelectorAll('.maspik-popup-wrap.active');
                activePopups.forEach(popup => {
                    popup.classList.remove('active');
                    popupBackground.style.display = 'none'; // Hide background
                });
            }
        });

        const copyMessage = document.getElementById('copy-message');

        // Copy list button functionality
        const copyButtons = document.querySelectorAll('.copy');
        
        copyButtons.forEach(copyButton => {
            copyButton.addEventListener('click', () => {
                const popup = copyButton.closest('.maspik-popup-wrap');
                const dataArrayElement = popup.querySelector('.data-array-here');
                const listItems = dataArrayElement.querySelectorAll('li');
                const listText = Array.from(listItems).map(li => li.textContent).join('\n');
                
                // Copy list text to clipboard
                navigator.clipboard.writeText(listText)
                .then(() => {
                        // Show the copy message
                        copyMessage.style.display = 'block';

                        // Hide the message after a short delay (e.g., 2 seconds)
                        setTimeout(() => {
                            copyMessage.style.display = 'none';
                        }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy list to clipboard: ', err);
                });
            });
        });
    });
    
    document.addEventListener('DOMContentLoaded', function () {
        const triggers = document.querySelectorAll('.custom-validation-trigger');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', function () {
                const box = this.parentNode.nextElementSibling;
                box.classList.toggle('open');
            });
        });
    });


</script>

</div>
<?php

    wp_enqueue_script('custom-ajax-script', plugin_dir_url(__DIR__). 'maspik-ajax-script.js', array('jquery'), MASPIK_VERSION, true);
    wp_localize_script('custom-ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));


?>