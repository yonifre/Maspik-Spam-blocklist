<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Provide a admin area view for the plugin
 */
$spamcounter = maspik_spam_count();
//$errorlog = get_option( 'errorlog' ) ? get_option( 'errorlog' )  : "Empty";

if(isset($_POST['clear_log'])){

  global $wpdb;
  
  $table = maspik_get_logtable();
        
  $wpdb->query("DELETE FROM $table");

  // Redirect to the same page to avoid resubmission
  wp_redirect(admin_url('admin.php?page=maspik-log.php'));
  exit;
}

?>

<div class="wrap">

<div class= "maspik-setting-header">
  <div class="notice-pointer"><h2></h2></div>

    <?php 
      echo "<div class='upsell-btn " . maspik_add_pro_class() . "'>";
      maspik_get_pro();
      maspik_activate_license();
      echo "</div>";
                
    ?>
    <div class="maspik-setting-header-wrap">
      <h1 class="maspik-title">MASPIK.</h1>
        <?php
          echo '<h3 class="maspik-protag '. maspik_add_pro_class() .'">Pro</h3>';
        ?>
    </div> 
    
</div>

<div class="maspik-spam-head">
                
  <h2 class='maspik-header maspik-spam-header'><?php _e('Spam Log', 'contact-forms-anti-spam'); ?></h2>
    <p>
      <?php _e('Whenever a bot/person tries to spam your contact forms and MASPIK blocks the spam, you will see a new line below showing the details.<br>The log containing these details resides on your database and you can reset it at any time.<br>Resetting the log doesn’t change anything – it just removes the history.', 'contact-forms-anti-spam' ); ?>
    </p>

    <div class='spam-log-button-wrapper'>
      <form method="post" onsubmit="return confirm('Are you sure you want to clear the Spam log? This action cannot be undone.')">
        <?php wp_nonce_field( 'cfes_clear_log_action', 'cfes_clear_log_nonce' ); ?>
        <button class="button log-reset maspik-btn" type="submit" name="clear_log" id="clear_log"><?php _e('Reset Log', 'contact-forms-anti-spam' ); ?></button>
      </form>


      <button class="button log-expand maspik-btn" type="submit"  name="expand-all" id="expand-all"><?php _e('Expand all', 'contact-forms-anti-spam' ); ?></button>
    </div>

      <p><?php echo "<b>".maspik_spam_count()."</b> ";  _e('Spams blocked by MASPIK since the last reset', 'contact-forms-anti-spam' ); 

  if( get_option("spamcounter") ){ ?>
    <?php echo ", <b>".get_option("spamcounter")."</b> ";  _e('and since installing', 'contact-forms-anti-spam' ); ?></p>
  <?php } 
  ?>

</div>

          
  <div id="icon-themes" class="icon32"></div>   
  <?php settings_errors(); ?>  
  <div class="log-warp" style="padding: 20px 0;">
      
    <?php
    if(maspik_spam_count()){
      echo cfes_build_table();
    } else {
      echo "<div class='spam-empty-log'><h4>Empty log</h4></div>";
    }
      
  ?>
  </div>
  </div>
<?php echo get_maspik_footer(); ?>
    <style>
    .log-warp tbody {
        max-width: 100%;
    }
    </style>

<script>



var acc = document.getElementsByClassName("maspik-accordion-header");
var toggleAllBtn = document.getElementById("expand-all");
var allExpanded = false;  // Track whether all sections are expanded or not

// Existing code for individual accordion toggle
for (var i = 0; i < acc.length; i++) {
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

// Function to toggle all accordion sections
toggleAllBtn.addEventListener("click", function() {
    if (allExpanded) {
        // Collapse all sections
        for (var i = 0; i < acc.length; i++) {
            var panel = acc[i].nextElementSibling;
            acc[i].classList.remove("active");
            panel.style.maxHeight = null;
        }
        toggleAllBtn.textContent = "Expand All";  // Change button text
    } else {
        // Expand all sections
        for (var i = 0; i < acc.length; i++) {
            var panel = acc[i].nextElementSibling;
            acc[i].classList.add("active");
            panel.style.maxHeight = panel.scrollHeight + 'px';
        }
        toggleAllBtn.textContent = "Collapse All";  // Change button text
    }
    allExpanded = !allExpanded;  // Toggle the state
});
</script>

