<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * Provide a admin area view for the plugin
 */
$spamcounter = get_option( 'spamcounter' );
$errorlog = get_option( 'errorlog' ) ? get_option( 'errorlog' )  : "Empty";

if(isset($_POST['clear_log'])){
  if ( wp_verify_nonce( $_POST['cfes_clear_log_nonce'], 'cfes_clear_log_action' ) ) {
    $emptylog = '{ 
    "spammer":[
      {
          "Type":"",
          "value":"",
          "Ip":"",
          "Country":"",
          "User agent":"",
          "Date":"",
     },
  ]}';
    update_option( 'errorlog',$emptylog );
    wp_redirect( add_query_arg( array( 'cleared_log' => true ), admin_url('admin.php?page=maspik-log.php') ) );
    exit;
  } else {
    wp_die( 'Invalid request' );
  }
}

if( isset($_POST['clear_counter'] ) && 0 ){ // Disable clear counter for now on
  if ( wp_verify_nonce( $_POST['cfes_clear_counter_nonce'], 'cfes_clear_counter_action' ) ) {
    update_option( 'spamcounter',0 );
    wp_redirect( add_query_arg( array( 'cleared_counter' => true ), admin_url('admin.php?page=maspik-log.php') ) );
    exit;
  } else {
    wp_die( 'Invalid request' );
  }
}

?>
<div class="wrap">
  <div id="icon-themes" class="icon32"></div>  
  <h2><?php _e('Spam log', 'contact-forms-anti-spam' ); ?></h2>  
  <?php settings_errors(); ?>  
  <div class="log-warp" style="padding: 20px 0;">
    <p><?php _e('Whenever a bot/person tries to spam your contact forms and MASPIK blocks the spam, you will see a new line below showing the details.<br>The log containing these details resides on your database and you can reset it at any time.<br>Resetting the log doesn’t change anything – it just removes the history.', 'contact-forms-anti-spam' ); ?></p>
    <div>
      <form method="post" onsubmit="return confirm('Do you want to delete the Spam log?')">
        <?php wp_nonce_field( 'cfes_clear_log_action', 'cfes_clear_log_nonce' ); ?>
        <button class="button" type="submit" name="clear_log"><?php _e('Reset Log', 'contact-forms-anti-spam' ); ?></button>
      </form>
      </div>
     <?php /* <form method="post" onsubmit="return confirm('Do you want to delete Spam counter?')">
        <?php wp_nonce_field( 'cfes_clear_counter_action', 'cfes_clear_counter_nonce' ); ?>
        <button class="button" type="submit" name="clear_counter"><?php _e('Reset Counter', 'contact-forms-anti-spam' ); ?></button>
      </form> */ 
    if($spamcounter > 0){ ?>
      <p><?php echo $spamcounter." ";  _e('Form Spams blocked by MASPIK so far!', 'contact-forms-anti-spam' ); ?> =)</p>
    <?php } 
        
    $array = json_decode($errorlog, TRUE);
    if(is_array($array)){
      echo cfes_build_table($array);
    } else {
      echo "Empty log";
    }
  ?>
  </div>
<?php echo get_maspik_footer(); ?>
    <style>
    .log-warp tbody {
        max-width: 100%;
    }
    </style>