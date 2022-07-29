<?php
/**
 * Provide a admin area view for the plugin
 */
if( isset($_GET['refresh_api_file']) && !empty($_GET['refresh_api_file'])  && cfes_is_supporting() ) {
  cfas_refresh_api();
 // echo "<script>window.location.replace('/wp-admin/admin.php?page=contact-forms-anti-spam-pro.php');</script>";
}

?>
<div class="wrap">
      <div id="icon-themes" class="icon32"></div>  
      <h2><?php _e("Options", 'contact-forms-anti-spam' ); ?></h2> 
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
      <form method="POST" action="options.php">

        
        
        <?php
        settings_fields( 'settings_page_option_settings_page' );
        do_settings_sections( 'settings_page_option_settings_page' );
        ?>

      <?php

      submit_button(); ?>  
      </form> 
  
      <p><?php _e('To use the Pro features, you will need to purchase a license key from the API website and insert it in the license page found in this plugin.<br>Without inserting a valid license key, the API/Pro features options will not work.', 'contact-forms-anti-spam' );?></p>
        <?php 

  		if( cfes_is_supporting() ){
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__('your licence is Active, you can use Pro options', 'contact-forms-anti-spam' )."</h2>";
        }else{
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__("Your license is NOT active, so you can’t use the Pro options, Please activate your license first.", 'contact-forms-anti-spam' )."</h2>";
        }
        
        $link = "https://wpmaspik.com/";
        $text = __('API/Pro Website link', 'contact-forms-anti-spam' );
		echo "<p>$text: <a href='$link' target='_blank'>$link</a></p>" ;

?>


	<?php echo get_maspik_footer(); ?>


  </div>