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
      <h2><?php _e("Let's stop spam TOGETHER!", 'contact-forms-anti-spam' ); ?></h2> 
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
      <form method="POST" action="options.php">
      <p><?php _e('You need the Pro version to use the MASPIK API feature and support Gravityforms and Wpforms - this is an extra feature.', 'contact-forms-anti-spam' );?></p>
      <p><?php _e('Create an API file (in the API website) of definitions or use a file created by other users by inserting their ID here. Insert multiple definition files by separating each ID with a comma.', 'contact-forms-anti-spam' );?></p>
      <p><?php _e('To use the Pro features, you will need to purchase a license key from the API website and insert it in the license page found in this plugin.<br>Without inserting a valid license key, the API/Pro features options will not work.', 'contact-forms-anti-spam' );?></p>
        <?php 
        $link = "https://wpmaspik.com/";
        $text = __('API Website link', 'contact-forms-anti-spam' );
		echo "<p>$text: <a href='$link' target='_blank'>$link</a></p>" ;

  		if( cfes_is_supporting() ){
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__('your licence is Active, you can use API option', 'contact-forms-anti-spam' )."</h2>";
        }else {
          echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__("Your license is NOT active, so you can’t use the API option, Please activate your license first.", 'contact-forms-anti-spam' )."</h2>";
          echo "<h3 style='font-size: 14px;padding: 10px;display: inline-block;background: #fbffab;'>".__("Want to get a free Pro license? Write an article about Maspik on your blog, and get a link from the plugin page and a professional license for free. <small> <a target='_blank' href='mailto:yonifre@gmail.com'>Email me</a> for more details</small>.", 'contact-forms-anti-spam' )."</h3>";

        }?>
        
        
        <?php
        settings_fields( 'settings_page_pro_settings_page' );
        do_settings_sections( 'settings_page_pro_settings_page' );
        ?>
        </div> <!-- open in the php Class -->
  		<p> <?php _e('Every day, the API file downloads new data from the API server.<br>If you would like to manually refresh now, just click the Reset API File button.', 'contact-forms-anti-spam' );?></p>
		<a href="/wp-admin/admin.php?page=contact-forms-anti-spam-pro.php&refresh_api_file=1" class="button" onclick="recheckapi()"><?php _e('Refresh API file', 'contact-forms-anti-spam' ) ;?></a>

      <?php

      submit_button(); ?>  
      </form> 


	<?php echo get_maspik_footer(); ?>


  </div>