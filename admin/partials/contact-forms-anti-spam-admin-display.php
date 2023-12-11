<?php
/**
 * Provide a admin area view for the plugin
 */
$spamcounter = get_option( 'spamcounter' );
$errorlog = get_option( 'errorlog' ) ? get_option( 'errorlog' )  : "Empty";
?>
<div class="wrap">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

      <div id="icon-themes" class="icon32"></div>  
      <h2><?php _e("Maspik- Spam Blacklist", 'contact-forms-anti-spam' ) ;?></h2>  
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
      <form method="POST" action="options.php">
        <h2><?php _e('Nobody likes receiving spam, so we developed MASPIK.', 'contact-forms-anti-spam' ) ;?></h2>
        <h3><?php _e('There are several ways in which you can customize this app to block contact-from spam.', 'contact-forms-anti-spam' ) ;?></h2>
        <p><?php _e('Choose phrases/strings/words to blacklist from your forms and key them into the input (types) fields below.', 'contact-forms-anti-spam' ) ;?></p>
        <p><?php _e('The words that you insert into each input field will be automatically blocked and will trigger a validation error message that the spammer receives.', 'contact-forms-anti-spam' ) ;?></p>
        <p><b><u><?php _e('Instructions', 'contact-forms-anti-spam' ) ;?></u></b></p>
        <ul style="list-style: disc;padding-inline-start: 20px;">
          <li><?php _e('Insert your choice of blacklisted characters/ settings, as appropriate for each field.', 'contact-forms-anti-spam' ) ;?></li>
          <li><?php _e('The system is not sensitive to uppercase vs. lowercase letters.', 'contact-forms-anti-spam' ) ;?></li>
          <li><?php _e('You may insert multiple values per field, placing one string per line.', 'contact-forms-anti-spam' ) ;?></li>
        </ul>
			<?php
		  if (!cfes_is_supporting() ) { ?>
			<a target="_blank" href="https://wpmaspik.com/?ref=inpluginad"><img src="https://wpmaspik.com/wp-content/uploads/maspikpng1.png" style="max-width: 60%;"></a>
        	<?php }

          if($spamcounter > 0){?>
          	<br><p><?php _e('You have blocked', 'contact-forms-anti-spam' ); ?> <?php echo $spamcounter; ?> <?php _e('spam so far!', 'contact-forms-anti-spam' ); ?> =)</p>
          <?php } 
        
        echo "<div class='supportforms'><p style='margin: auto;padding: 0 10px;'>".__('Maspik affect', 'contact-forms-anti-spam' ).":</p><ul style='display: inline-flex;margin: 0;'>";       
		foreach ( efas_array_supports_plugin() as $key => $value) {
          if( !efas_if_plugin_is_affective($key) ){continue;}
          $class = $value ? "pro" : "free";
          $value = $value ? " <small>($value)</small>" : "";
			echo  "<li class='$class'>$key $value</li>";
		}
        echo "</ul></div>";
        
        settings_fields( 'settings_page_general_settings_page' );
        do_settings_sections( 'settings_page_general_settings' );
        ?>
        <!--<div style="display: none;" id="bonus-sec">-->
        <!-- </div> open in the php Class -->
      <?php

      submit_button(); ?>  
      </form> 

<?php echo get_maspik_footer(); ?>

<script>
      jQuery(document).ready(function() {
        jQuery('.select2').select2({
          multiple: true,
          placeholder:"<?php _e('Select', 'contact-forms-anti-spam' ) ;?>",
        });
    });

    </script>
<style>
.select2.select2-container.select2-container--default {
    width: 590px !important;
    max-width: 90%;
}
.api  {
    float: right;
    font-size: 13px;
    line-height: 8px;
}
.api div {
    text-align: left;
    max-height: 100px;
    overflow: auto;
    background: #fff;
    padding: 5px;
    color: #333;
    border: 1px solid #3333;
}
.rtl .api  {
    float: left;
}
textarea#emails_blacklist {
    direction: ltr;
    text-align: left;
}
.supportforms li {
	padding: 4px 10px;
    border-inline-end: 1px solid #cfd1cf;
}
.supportforms li:before {
	content: "";
    display: inline-block;
    background: #39b54a;
    margin-inline-end: 5px;
    height: 10px;
    width: 10px;
    border-radius: 50%;
    border: 2px solid #fff;
    -webkit-box-shadow: 0 2px 4px rgb(0 0 0 / 10%);
    box-shadow: 0 2px 4px rgb(0 0 0 / 10%);
    position: relative;
    top: 3px;
  }
body:not(.maspik-pro) .supportforms li.pro:before {background: red;}
</style>

</div>