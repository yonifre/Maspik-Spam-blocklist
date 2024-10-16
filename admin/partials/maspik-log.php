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

<?php


function maspik_spam_item_option($row_id, $spam_value, $spam_type){

  if($spam_type == ""){
    $not_a_spam_btn = "";
  }else{
    $not_a_spam_btn = "<span class='filter-delete-button' data-row-id='" . $row_id . "'   data-spam-value='" . $spam_value . "'data-spam-type='" . $spam_type ."'>
    Not Spam?
  </span> | ";
  }

  return 
  "<div class='spam-option-wrap'>
    <span class='spam-delete-button' data-row-id='" . $row_id . "'   data-spam-value='" . $spam_value . "'data-spam-type='" . $spam_type ."'>
      Delete
    </span> | " . $not_a_spam_btn . " <span class='log-detail detail-show'> </span>
  </div>";  

}


function processArray($array, &$form_data, $parent_key = '') {
  foreach ($array as $key => $value) {
      // Building the full key
      $full_key = $parent_key === '' ? $key : $parent_key . '_' . $key;

      if (is_array($value)) {
          // If the value is an array, go over it
          processArray($value, $form_data, $full_key);
      } else {
          // Adding a row to the table with the full key and value
          $form_data .= '<tr>';
          $form_data .= '<td>' . esc_html($full_key) . '</td>';
          $form_data .= '<td>' . esc_html($value) . '</td>';
          $form_data .= '</tr>';
      }
  }
}


function cfes_build_table() {
  global $wpdb;
  if (maspik_logtable_exists()) {
      $table = maspik_get_logtable();

      // SQL query to select all rows from the table
      $sql = "SELECT * FROM $table ORDER BY id DESC";
      $results = $wpdb->get_results($sql, ARRAY_A);
      echo maspik_Download_log_btn();
      echo "<table class ='maspik-log-table'>";
    echo "<tr class='header-row'>
            <th class='maspik-log-column column-type'>" . esc_html__('Type', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-value'>" . esc_html__('Data & Reason', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-ip'>" . esc_html__('IP', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-country'>" . esc_html__('Country', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-agent'>" . esc_html__('User Agent', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-date'>" . esc_html__('Date', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-source'>" . esc_html__('Source', 'contact-forms-anti-spam') . "</th>
            <th class='maspik-log-column column-not-spam'></th>
        </tr>";
      $row_count = 0;
      foreach ($results as $row) {
        $row_class = ($row_count % 2 == 0) ? 'even' : 'odd';
        $row_id = $row['id'];
        $spam_value = esc_html($row['spamsrc_val']);
        $spam_type = esc_html($row['spam_type']);
        $spam_date = $row['spam_date'] ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row['spam_date'])) : esc_html($row['spam_date']);
        $spam_val_intext =  esc_html(maspik_var_value_convert($row['spamsrc_label']));
        $not_spam_tag = esc_html($row['spam_tag']) == "not spam" ? " not-a-spam" : "" ;
        $form_data_raw = $row['spam_detail'];
        $unserialize_array = @unserialize($form_data_raw);
        $form_data = "<pre>".esc_html($form_data_raw)."</pre>";

        if($row['spamsrc_label'] != ""){
          $spam_alt_text = "Entry has been blocked. Reason: " . $spam_val_intext . " = '" . $spam_value . "'";
        }else{
          $spam_alt_text = "";
        }

        if ( is_array( $unserialize_array ) ) {
          // Using the code
          $form_data = '<table border="1">';
          $form_data .= '<tr><th>Field</th><th>Value</th></tr>';

          if (is_array($unserialize_array)) {
            processArray($unserialize_array, $form_data);
          }

          $form_data .= '</table>';
        }
        
        if ($row['spam_tag'] != "spam"){
          echo "<tr class='row-entries row-$row_class $not_spam_tag'>
                  <td class='column-type column-entries'>".esc_html($row['spam_type']) ."</td>
                  <td class='column-value column-entries'>
                      <div class='maspik-accordion-item'>
                          <div class='maspik-accordion-header log-accordion'><div class='spam-value-text'>".esc_html($row['spam_value']) .
                          "<span class='span-alt-text'>" .  $spam_alt_text . "</span>" . 
                          "</div>" .
                          maspik_spam_item_option($row_id, $spam_value, $spam_val_intext)
                          ."
                          </div>
                          <div class='log-detail maspik-accordion-content'>$form_data</div>
                      </div>
                  </td>
                  <td class='column-ip column-entries'>".esc_html($row['spam_ip'])."</td>
                  <td class='column-country column-entries'>".esc_html($row['spam_country'])."</td>
                  <td class='column-agent column-entries'>".esc_html($row['spam_agent'])."</td>
                  <td class='column-date column-entries'>$spam_date</td>
                  <td class='column-source column-entries'>".esc_html($row['spam_source'])."</td>
                  <td class='maspik-log-column column-button'>
                
                  </td>
              </tr>";
          }
    
        $row_count++;
    }
    
      echo "</table>";
  }
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

<!-- Delete Confirmation Modal -->
<div id="confirmation-modal" class="modal">
    <div class="modal-content">
      <div class= "modal-content-inner">
        <span class="close-button">&times;</span>
        <p id="confirmation-message">Are you sure you want to delete this row?</p>
        <button id="confirm-delete" class="del-spam-button del-spam-button-primary">Yes, Delete</button>
        <button id="cancel-delete" class="del-spam-button del-spam-button-secondary">Cancel</button>
      </div>
    </div> 
</div>


<!-- Filter Confirmation Modal -->
<div id="filter-delete-modal" class="modal">
    <div class="modal-content">
      <div class= "modal-content-inner">
        <span class="close-button">&times;</span>
        <p id="filter-type">Delete this filter?</p>
        <button id="confirm-del-filter" class="del-filter-button del-filter-button-primary">Yes, Delete</button>
        <button id="cancel-del-filter" class="del-filter-button del-filter-button-secondary">Cancel</button>
      </div>
    </div> 
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

<?php
  wp_enqueue_script('maspik-spamlog', plugin_dir_url(__FILE__) . '../js/maspik-spamlog.js', array('jquery'), MASPIK_VERSION, true);
?>

//Accordion Script - START

var acc = document.getElementsByClassName("detail-show");
var toggleAllBtn = document.getElementById("expand-all");
var allExpanded = false;  // Track whether all sections are expanded or not

// Existing code for individual accordion toggle
for (var i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var panel = this.parentElement.parentElement.nextElementSibling;
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
            // todo: remove class from the row
            this.parentElement.parentElement.parentElement.classList.remove("expanded");
        } else {
            panel.style.maxHeight = (panel.scrollHeight) + 'px';
            // todo: add class to the row
            this.parentElement.parentElement.parentElement.classList.add("expanded");
        }
    });
}

// Function to toggle all accordion sections
toggleAllBtn.addEventListener("click", function() {
    if (allExpanded) {
        // Collapse all sections
        for (var i = 0; i < acc.length; i++) {
            var panel = acc[i].parentElement.parentElement.nextElementSibling;
            acc[i].classList.remove("active");
            panel.style.maxHeight = null;
            // todo: remove class from the row
            acc[i].parentElement.parentElement.parentElement.classList.remove("expanded");
        }
        toggleAllBtn.textContent = "Expand All";  // Change button text
    } else {
        // Expand all sections
        for (var i = 0; i < acc.length; i++) {
            var panel = acc[i].parentElement.parentElement.nextElementSibling;
            acc[i].classList.add("active");
            panel.style.maxHeight = panel.scrollHeight + 'px';
            // todo: add class to the row
            acc[i].parentElement.parentElement.parentElement.classList.add("expanded");
        }
        toggleAllBtn.textContent = "Collapse All";  // Change button text
    }
    allExpanded = !allExpanded;  // Toggle the state
});

//Accordion Script -- END


</script>

