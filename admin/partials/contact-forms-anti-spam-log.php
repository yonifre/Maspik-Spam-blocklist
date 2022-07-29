<?php
/**
 * Provide a admin area view for the plugin
 */
$spamcounter = get_option( 'spamcounter' );
$errorlog = get_option( 'errorlog' ) ? get_option( 'errorlog' )  : "Empty";

if(isset($_GET['clear_log'])){
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
}
if(isset($_GET['clear_counter'])){
  update_option( 'spamcounter',0 );
}

?>
<div class="wrap">
      <div id="icon-themes" class="icon32"></div>  
      <h2><?php _e('Spam log', 'contact-forms-anti-spam' ); ?></h2>  
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
  <div class="log-warp" style="padding: 20px 0;">
        <p><?php _e('Whenever a bot/person tries to spam your contact forms and MASPIK blocks the spam, you will see a new line below showing the details.<br>The log containing these details resides on your database  and you can reset it at any time.<br>Resetting the log doesn’t change anything – it just removes the history.', 'contact-forms-anti-spam' ); ?></p>
    <div>
		<button class="button" onclick="deletespam()"><?php _e('Reset Log', 'contact-forms-anti-spam' ); ?></button>
		<button class="button" onclick="deletecount()"><?php _e('Reset Counter', 'contact-forms-anti-spam' ); ?></button>
    </div>
			<?php
          if($spamcounter > 0){?>
          	<p><?php echo $spamcounter." ";  _e('Form Spams blocked by MASPIK so far!', 'contact-forms-anti-spam' ); ?> =)</p>
          <?php } 
        
$array = json_decode($errorlog, TRUE);
if(is_array($array)){
	echo cfes_build_table($array);
}else{
  echo "Empty log";
}
?>
        
        </div> <!-- open in the php Class -->



<?php echo get_maspik_footer(); ?>
  
<script>
function deletespam() {
  if (confirm("Do you want to delete the Spam log?")) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('clear_log', '1');
    window.location.search = urlParams;
  //  location.reload();

  }
}

function deletecount() {
  if (confirm("Do you want to delete Spam counter?")) {
   const urlParams = new URLSearchParams(window.location.search);
   urlParams.set('clear_counter', '1');
   window.location.search = urlParams;
    //location.reload();

  }
}
if (window.location.href.indexOf("clear_counter") != -1 || window.location.href.indexOf("clear_log") != -1) {
  window.location.replace(location.protocol + '//' + location.host + location.pathname + "?page=contact-forms-anti-spam-log.php");
}




  
</script>


</div>