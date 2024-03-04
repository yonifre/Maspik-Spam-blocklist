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
?>
<div class="wrap maspik-mainpage">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

      <div id="icon-themes" class="icon32"></div>  
      <h2><?php _e("Maspik- Spam Blacklist", 'contact-forms-anti-spam' ) ;?></h2>  
      <!--NEED THE settings_errors below so that the errors/success messages are shown after submission - wasn't working once we started using add_menu_page and stopped using add_options_page so needed this-->
      <?php settings_errors(); ?>  
    <div class=forms-warp>
    <div id="popup-background"></div>
      <div class="introductions">
            <h3><?php _e('There are several ways in which you can customize this app to block contact-from spam.', 'contact-forms-anti-spam' ) ;?></h3>
            <p><b><u><?php _e('Instructions', 'contact-forms-anti-spam' ) ;?></u></b></p>
            <ul style="list-style: disc;padding-inline-start: 20px;">
              <li><?php _e('Insert your choice of blacklisted characters/ settings, as appropriate for each field.', 'contact-forms-anti-spam' ) ;?></li>
              <li><?php _e('The system is not sensitive to uppercase vs. lowercase letters.', 'contact-forms-anti-spam' ) ;?></li>
              <li><?php _e('You may insert multiple values per field, placing one string per line.', 'contact-forms-anti-spam' ) ;?></li>
            </ul>
                <?php
              if (!cfes_is_supporting() ) { ?>
                <a target="_blank" href="https://wpmaspik.com/?ref=inpluginad"><img src="<?php echo plugin_dir_url(__DIR__) ;?>maspik-banner.jpeg" style="max-width: 60%;"></a>
                <?php }

              if($spamcounter > 0){?>
              <br><p style="font-size: 15px;margin-bottom: 33px;margin-top: 5px;"><b><?php _e('You have blocked', 'contact-forms-anti-spam' ); ?> <?php echo "<u>".$spamcounter."</u>"; ?> <?php _e('spam so far!', 'contact-forms-anti-spam' ); ?></b></p>
              <?php } 

            echo "<div class='supportforms'><p style='margin: auto;padding: 0 10px;'>".__('Maspik affects the following forms', 'contact-forms-anti-spam' ).":</p><ul style='display: inline-flex;margin: 0;'>";       
            foreach ( efas_array_supports_plugin() as $key => $value) {
              if( !efas_if_plugin_is_affective($key) ){continue;}
              $class = $value ? "pro" : "free";
              $value = $value ? " <small>($value)</small>" : "";
                echo  "<li class='$class'>$key $value</li>";
            }
            echo "</ul></div>"; ?>
        </div>
        <div class="form-setting">
          <form method="POST" action="options.php" class="maspik-form">
        <h3 style="font-size: 23px;margin-bottom: 0;"><?php _e('Enter your blacklist words below', 'contact-forms-anti-spam' ) ;?></h3>  
          <p style="margin-top: 10px;"><?php _e('Choose phrases/strings/words to blacklist from your forms and key them into the input (types) fields below.', 'contact-forms-anti-spam' ) ;?><br>
           <?php _e('The words that you insert into each input field will be automatically blocked and will trigger a validation error message that the spammer receives.', 'contact-forms-anti-spam' ) ;?></p>

            <?php echo '<p style="background: #87cbc0;padding: 5px;border-radius: 5px;">' . esc_html__( 'Need clarity? See examples of proper plugin usage in our', 'your-text-domain' ) . ' <a href="https://wpmaspik.com/spam-block-guide-best-maspik-setting/?Viow-guide" target="_blank">' . esc_html__( 'recent post.', 'your-text-domain' ) . '</a></p>';

            settings_fields( 'settings_page_general_settings_page' );
            do_settings_sections( 'settings_page_general_settings' );
            ?>
            <!--<div style="display: none;" id="bonus-sec">-->
            <!-- </div> open in the php Class -->
          <?php

          submit_button(); ?>  
        </form> 
      </div>
      <div class="form-container test-form">
      <form name="frmContact" id="frmContact" method="post"  enctype="multipart/form-data">
        <h3 style="font-size: 23px;margin-bottom: 0;"><?php _e('Playground - Test form', 'contact-forms-anti-spam' ) ;?></h3>    
        <p style="margin-top: 10px;"><?php _e('This form allows you to test your entries to see if they will be blocked.', 'contact-forms-anti-spam' ) ;?>
<br> - <?php _e('Please save changes before submitting.', 'contact-forms-anti-spam' ) ;?></p>    
			<div class="input-row">
				<label style="padding-top: 20px;">Name</label> <span
					id="userName-info" class="info"></span><br /> <input type="text"
					class="input-field" name="userName" id="userName" />
                <span class="note" id="note-name"></span>
			</div>
			<div class="input-row">
				<label>Email</label> <span id="userEmail-info" class="info"></span><br />
				<input type="email" class="input-field" name="userEmail"
					id="userEmail" />
                <span class="note" id="note-email"></span>
			</div>
			<div class="input-row">
				<label>Phone</label> <span id="subject-info" class="info"></span><br />
				<input type="tel" class="input-field" name="tel" id="tel" />
                <span class="note" id="note-tel"></span>
			</div>
			<div class="input-row">
				<label>Message</label> <span id="userMessage-info" class="info"></span><br />
				<textarea name="content" id="content" class="input-field" cols="60"
					rows="6"></textarea>
                <span class="note" id="note-textarea"></span>
			</div>
			<div>
				<input type="submit" name="send" class="btn-submit" value="submit" />
                <div id="statusMessage"></div>
			</div>
		</form>
	</div> <!-- end text form -->
</div><!-- end forms warp -->
<div id="popup-id" class="your-popup-class">
    <h3 class="">Example for <span class="title-here">Text field</span></h3>
    <p class="">Here you can see an Example words for the  <span class="title-here">text field</span> blocklist</p>
    <button class="close-popup">X</button>
    <div class="data-array-here">
        <ul>
        <!-- Example words will be dynamically inserted here -->
        </ul>
    </div>
    <button class="copy">Copy list</button>
    <div id="copy-message" style="display: none;">List copied!</div>
</div>
    
    <?php echo get_maspik_footer(); ?>

<script>

      jQuery(document).ready(function() {
        jQuery('.select2').select2({
          multiple: true,
          placeholder:"<?php _e('Select', 'contact-forms-anti-spam' ) ;?>",
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
    const popups = document.querySelectorAll('.your-popup-class');
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
            const titleHereSpans = popup.querySelectorAll('.title-here');
            const buttonTitle = button.dataset.title || 'Text field'; // Default value if not provided
            titleHereSpans.forEach(span => {
                span.innerHTML = buttonTitle;
            });
            
            // Update data-array-here content if provided
            const dataArrayElement = popup.querySelector('.data-array-here ul');
            const dataArray = button.dataset.array;
            if (dataArrayElement && dataArray) {
                const dataArrayItems = dataArray.split(',');
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
            const popup = closeButton.closest('.your-popup-class');
            if (popup) {
                popup.classList.remove('active');
                popupBackground.style.display = 'none'; // Hide background
            }
        });
    });

    // Close popup when clicking outside of it
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.your-popup-class') && !event.target.closest('.your-button-class')) {
            const activePopups = document.querySelectorAll('.your-popup-class.active');
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
            const popup = copyButton.closest('.your-popup-class');
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


<style>
.forms-warp {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-column-gap: 20px;
    grid-row-gap: 20px;
    grid-template-rows: auto;
}

.introductions {
    grid-area: 1 / 1 / 2 / 3;
}

.form-setting {
    grid-area: 2 / 1 / 3 / 2;
    padding: 30px;
    background: #fff;
    border-radius: 20px;
}
.form-setting tbody {
    padding: 0;
}
.form-container.test-form form {
    position: sticky;
    top: 50px;
    padding: 20px;
    border-radius: 20px;
    background: #fff;
}

.form-container.test-form form input,.form-container.test-form form textarea {
    max-width: 400px;
    width: 100%;
}
.test-form input.btn-submit {
    background: #87cbc0;
    border: 0;
    padding: 7px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 18px;
    }
.test-form .input-row {
    margin-bottom: 5px;
}
.select2.select2-container.select2-container--default {
    width: 590px !important;
    max-width: 90%;
}
span.note {
    display: block;
}
.api {
    padding: 7px;
    background: #d2d2d2;
    float: right;
    font-size: 13px;
    line-height: 8px;
    border-radius: 5px;
    }
.api div {
    text-align: left;
    max-height: 60px;
    overflow: auto;
    background: #f2f2f2;
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
.input-row.red .input-field {
    border-color: red;
}
div#statusMessage p {
    font-size: 18px;
}
body:not(.maspik-pro) .supportforms li.pro:before {
    background: red;
}
.maspik-form .form-table th {
    width: 100%;
}
body:not(.maspik-pro) .maspik-form tr.pro {
    background: #eee;
    padding: 10px;
}
body:not(.maspik-pro) .maspik-form tr.lang_needed.pro {
    border-radius: 20px 20px 0 0;
}
body:not(.maspik-pro) .maspik-form tr.country_blacklist.pro {
    border-radius: 0 0 20px 20px ;
}

.your-popup-class {
  position: fixed; /* Keep the popup positioned */
  top: 50%; /* Center vertically */
  left: 50%; /* Center horizontally */
  transform: translate(-50%, -50%); /* Offset to center */
  background-color: #fff; /* Set background color */
  padding: 20px; /* Add padding */
  border: 1px solid #ddd; /* Add border */
  border-radius: 5px; /* Add rounded corners */
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Add shadow */
  display: none; /* Hide popup by default */
}

.your-popup-class.active { /* Style the popup when active */
    display: block; /* Show the popup */
    z-index: 999; /* Bring the popup to the front */
    border: 8px solid #87cbc0;
}

.close-popup {
    float: right; /* Position the close button */
    cursor: pointer; /* Indicate clickable behavior */
    font-size: 20px; /* Set font size */
    font-weight: bold; /* Set font weight */
    color: #ccc; /* Set close button color */
    background-color: transparent; /* Remove default background */
    border: none; /* Remove default border */
    position: absolute;
    right: -3px;
    top: 0;}

.close-popup:hover {
  color: #333; /* Change color on hover */
}
.data-array-here {
    background: #eee;
    padding: 10px;
    border-radius: 5px;
}
    button.copy {
    position: absolute;
    right: 20px;
    bottom: 20px;
    background: white;
    border: 2px solid #86cbbf;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
}
body.rtl button.copy {
    right: auto;
    left: 20px;
}
button.copy:hover {
    background: #86cbbf;
    color: white;
}
div#copy-message {
    position: absolute;
    right: 20px;
}
.maspik-form .btns {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    margin-bottom: 10px;
    margin-top: 5px;
}
.custom-validation-box {
    background: #87cbc0;
    padding: 10px;
    border-radius: 5px;
}

.custom-validation-box h4 {
    margin: 4px 2px;
}
.custom-validation-box {
    display: none;
    width: calc(100% - 30px);
}
.custom-validation-box.open {
    display: block;
}
.forms-warp {
    position: relative;
    z-index: 0;
}

#popup-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    z-index: 1; /* Behind other elements */
    display: none; /* Initially hidden */
}
.maspik-form .btns a {
    font-size: 11px;
    background: #87cbc0;
    color: black;
    padding: 3px 8px;
    border-radius: 5px;
    line-height: 20px;
    text-decoration: none;
    cursor: pointer;
}

</style>

    

</div>
<?php

wp_enqueue_script('custom-ajax-script', plugin_dir_url(__DIR__). 'maspik-ajax-script.js', array('jquery'), '1.7', true);
wp_localize_script('custom-ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));


?>