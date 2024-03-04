<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * Pro page of admin area view for the plugin
 */

if( isset( $_GET['refresh_api_file'] ) && ! empty( $_GET['refresh_api_file'] ) && cfes_is_supporting() ) {
    // Verify nonce
    if ( isset( $_GET['cfas_nonce'] ) && wp_verify_nonce( $_GET['cfas_nonce'], 'cfas_refresh_api_nonce' ) ) {
        // Nonce is valid, proceed with refreshing API
        cfas_refresh_api();
        $current_page = admin_url( "admin.php?page=maspik-api.php" );
        // Redirect to avoid resubmission on page refresh
        echo "<script>window.location.replace('$current_page');</script>";
    } else {
        // Nonce verification failed, handle accordingly 
        echo "<p>Error: Nonce verification failed.</p>";
    }
}
?>
<div class="wrap">
      <div id="icon-themes" class="icon32"></div>  
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
      <form method="POST" action="options.php">
        <h1><?php echo esc_html__('Manage all your websites in one place!', 'contact-forms-anti-spam'); ?></h1>

        <p><?php echo esc_html__('Create an API file (in the API website) with your blacklist setting and insert the ID here, it will save you time to manage all your websites settings from a single dashboard!', 'contact-forms-anti-spam'); ?></p>

        <p><?php echo esc_html__('To use the Pro features, you need to purchase a license key from the API website and insert it into the license page found in this plugin.', 'contact-forms-anti-spam'); ?></p>

        <p><?php echo esc_html__('Without a valid license key, the API/Pro features options will not work.', 'contact-forms-anti-spam'); ?></p>

        <?php 
        $link = "https://wpmaspik.com/?plugin-api-page";
        $text = __('API Website link', 'contact-forms-anti-spam' );
		echo "<p>$text: <a href='$link' target='_blank'>$link</a></p>" ;

  		if( cfes_is_supporting() ){
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__('your licence is Active, you can use API option', 'contact-forms-anti-spam' )."</h2>";
        }else {
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__("Your license is NOT active, so you can’t use the API option, Please activate your license first.", 'contact-forms-anti-spam' )."</h2>";

        }?>
        
        
        <?php
        settings_fields( 'settings_page_pro_settings_page' );
        do_settings_sections( 'settings_page_pro_settings_page' );
        ?>
       <!-- </div> open in the php Class -->
  		<p> <?php _e('Every day, the API file downloads new data from the API server.<br>If you would like to manually refresh now, just click the Reset API File button.', 'contact-forms-anti-spam' );?></p>
		<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'maspik-api.php', 'refresh_api_file' => 1, 'cfas_nonce' => wp_create_nonce( 'cfas_refresh_api_nonce' ) ), admin_url( 'admin.php' ) ) ); ?>" class="button"><?php _e('Refresh API file', 'contact-forms-anti-spam' );?></a>

      <?php

      submit_button(); ?>  
      </form> 


	<?php echo get_maspik_footer(); ?>


  </div>