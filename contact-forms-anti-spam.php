<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Maspik - Advanced Spam Protection
 * Plugin URI:        https://wpmaspik.com/
 * Description:       The best spam protection plugin. Block spam using advanced filters, blacklists, and IP verification...
 * Version:           2.4.4
 * Author:            WpMaspik
 * Author URI:        https://wpmaspik.com/?readme
 * Text Domain:       contact-forms-anti-spam
 * Domain Path:       /languages
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * 
 * Maspik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * Any spam blocking action taken by this plugin is solely at the user's own risk and discretion.
 * The plugin developers and contributors cannot be held responsible for any false positives
 * or legitimate messages that may be blocked.
 *
 * You should have received a copy of the GNU General Public License
 * along with Maspik. If not, see <http://www.gnu.org/licenses/>.
 * 
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 

/**
 * Currently plugin version.
 */
define( 'MASPIK_VERSION', '2.4.4' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-settings-page-activator.php
 */
// For future version
/*
function maspik_on_plugin_activation() {
	maspik_auto_update_db(); // Run the auto create database function
    if ( ! get_option( 'maspik_run_once' ) ) {
		maspik_auto_update_db();
        maspik_save_default_values();
        update_option( 'maspik_run_once', 1 ); // 1 means the function has run
    }
}
// Ensure the function runs on plugin activation
register_activation_hook( __FILE__, 'maspik_on_plugin_activation' );
*/


/**
 * The code that runs during plugin deactivation.
 */
// For future version
function deactivate_maspik() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-maspik-deactivator.php';
	//Settings_Page_Deactivator::deactivate();
}
//register_deactivation_hook( __FILE__, 'deactivate_maspik' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path(__FILE__) . 'includes/consts.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-maspik.php';


if (version_compare(PHP_VERSION, '7.0.0', '>=') && apply_filters( 'maspik_active_license_library', true )) {
  require plugin_dir_path(__FILE__) . 'license/license.php';
}



/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function Run_Maspik() {
	$plugin = new Maspik();
	$plugin->run();
}

Run_Maspik();

add_filter( 'plugin_row_meta', 'maspik_plugin_row_meta', 10, 2 );
function maspik_plugin_row_meta( $links, $file ) {
	if( strpos( $file, basename(__FILE__) ) ) {
		$maspik_links = array(
			'donat_link' => '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">'.__( 'Give us 5 stars', 'contact-forms-anti-spam' ).'</a>',
			'settings' => '<a href="'.admin_url().'admin.php?page=maspik" target="_blank">'.__( 'Setting page', 'contact-forms-anti-spam' ).'</a>',
		);
		
		$links = array_merge( $links, $maspik_links );
	}
	
	return $links;
}

add_action('admin_footer-plugins.php', 'maspik_deactivation_survey');
function maspik_deactivation_survey() {
    $nonce = wp_create_nonce('maspik_deactivation_survey');
    ?>
    <div id="maspik-deactivation-survey" style="display: none;">
        <button type="button" class="maspik-close-button" aria-label="Close">×</button>
        <!-- Add loader HTML -->
        <div id="maspik-loader" style="display: none;">
            <div class="maspik-spinner"></div>
            <div class="maspik-loader-text">
                <?php _e('Sending feedback...', 'contact-forms-anti-spam'); ?>
                <div class="maspik-loader-subtext"><?php _e('Thank you for helping us improve!', 'contact-forms-anti-spam'); ?></div>
            </div>
        </div>

        <h3><?php _e('Quick Feedback', 'contact-forms-anti-spam'); ?></h3>
        <h4><?php _e('Moment of your time means meaningful improvement for everyone', 'contact-forms-anti-spam'); ?></h4>
        <form method="post" id="maspik-deactivation-form">
            <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">
            
            <div class="maspik-survey-options">
                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="blocked_legitimate">
                    <?php _e('It\'s blocking legitimate submissions', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="blocked_legitimate">
                    <p><?php _e('Before deactivating, try these solutions:', 'contact-forms-anti-spam'); ?></p>
                    <ul>
                        <li><?php _e('Lower the protection level in settings', 'contact-forms-anti-spam'); ?></li>
                        <li><?php _e('Check your spam log to understand why submissions are blocked', 'contact-forms-anti-spam'); ?></li>
                        <li><a href="https://wpmaspik.com/documentation/spam-log/" target="_blank"><?php _e('Read our guide about preventing false positives', 'contact-forms-anti-spam'); ?></a></li>
						<li><a href="https://wpmaspik.com/#support" target="_blank"><?php _e('Contact our support team', 'contact-forms-anti-spam'); ?></a></li>
					</ul>
                </div>

                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="not_blocking_spam">
                    <?php _e('Not blocking enough spam', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="not_blocking_spam">
                    <p><?php _e('Try these steps to improve spam blocking:', 'contact-forms-anti-spam'); ?></p>
                    <ul>
                        <li><?php _e('Add some words to the blacklist by field type', 'contact-forms-anti-spam'); ?></li>
                        <li><?php _e('Enable additional spam filters', 'contact-forms-anti-spam'); ?></li>
                        <li><?php _e('Go through the options and make sure you have the right settings', 'contact-forms-anti-spam'); ?></li>
                        <li><a href="https://wpmaspik.com/documentation/getting-started/" target="_blank"><?php _e('Learn more about not blocking spam', 'contact-forms-anti-spam'); ?></a></li>
						<li><a href="https://wpmaspik.com/#support" target="_blank"><?php _e('Contact our support team', 'contact-forms-anti-spam'); ?></a></li>
					</ul>
                </div>

                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="not_sure_how_to_use">
                    <?php _e('Not sure how to use it', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="not_sure_how_to_use">
                    <p><?php _e('We\'re here to help:', 'contact-forms-anti-spam'); ?></p>
                    <ul>
                        <li><a href="https://wpmaspik.com/documentation/getting-started/" target="_blank"><?php _e('Read our quick start guide', 'contact-forms-anti-spam'); ?></a></li>
                        <li><a href="https://wpmaspik.com/#support" target="_blank"><?php _e('Contact our support team', 'contact-forms-anti-spam'); ?></a></li>
                    </ul>
                </div>

                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="found_better_plugin">
                    <?php _e('Switched to an alternative plugin', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="found_better_plugin">
                    <p><?php _e('Would you mind sharing which plugin? This helps us improve:', 'contact-forms-anti-spam'); ?></p>
                    <textarea name="other_reason" placeholder="<?php _e('Which plugin did you choose?', 'contact-forms-anti-spam'); ?>"></textarea>
                </div>

                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="temporary">
                    <?php _e('Temporary deactivation - I\'m just debugging an issue', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="temporary">
                    <p><?php _e('Good luck with your debugging!', 'contact-forms-anti-spam'); ?></p>
                </div>

                <label>
                    <input type="radio" name="maspik_deactivation_reason" value="other">
                    <?php _e('Other', 'contact-forms-anti-spam'); ?>
                </label>
                <div class="reason-text-wrapper" data-reason="other">
                    <textarea name="other_reason" placeholder="<?php _e('Your feedback helps us improve! Share your thoughts in 5 seconds...', 'contact-forms-anti-spam'); ?>"></textarea>
                </div>
            </div>

            <div class="maspik-survey-buttons">
                <button type="button" class="button" id="maspik-skip-survey"><?php _e('Skip', 'contact-forms-anti-spam'); ?></button>
                <button type="submit" class="button button-primary" id="maspik-submit-survey">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Submit & Deactivate', 'contact-forms-anti-spam'); ?>
                </button>
            </div>
        </form>
    </div>

    <style>
        #maspik-deactivation-survey {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            z-index: 999999;
            width: 500px;
        }

        .maspik-survey-options label {
            display: block;
            margin: 0;
            padding: 7px;
            border-radius: 4px;
        }

        .maspik-survey-options label:hover {
            background-color: #f8f8f8;
        }

        .maspik-survey-buttons {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        
        #maspik-submit-survey {
            margin-left: 10px;
        }
        
        #maspik-submit-survey .dashicons {
            margin: 4px 4px 0 0;
        }

        .reason-text-wrapper {
            display: none;
            margin: 10px 0 15px 25px;
            padding: 10px;
            background: #f8f8f8;
            border-left: 3px solid #ddd;
            border-radius: 2px;
        }
        
        .reason-text-wrapper ul {
            margin: 5px 0 5px 20px;
            list-style-type: disc;
        }
        
        .reason-text-wrapper textarea {
            width: 100%;
            min-height: 60px;
            margin-top: 5px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Loader styles */
        #maspik-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .maspik-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: maspik-spin 1s linear infinite;
            margin-bottom: 10px;
        }

        .maspik-loader-text {
            margin-top: 15px;
            font-size: 14px;
            color: #444;
        }

        .maspik-loader-subtext {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        @keyframes maspik-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .maspik-close-button {
            position: absolute;
            right: 10px;
            top: 10px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .maspik-close-button:hover {
            background: #f0f0f0;
            color: #000;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // check if jQuery is loaded
        if (typeof $ === 'undefined') {
            return;
        }

        var deactivateLink = $('tr[data-plugin="contact-forms-anti-spam/contact-forms-anti-spam.php"] .deactivate a');
        var isSubmitting = false; // prevent double submission

        deactivateLink.on('click', function(e) {
            e.preventDefault();
            $('#maspik-deactivation-survey').show();
            deactivationLink = $(this).attr('href');
        });

        // handle form submission
        $('#maspik-deactivation-form').on('submit', function(e) {
            e.preventDefault();

            if (isSubmitting) {
                return;
            }
            isSubmitting = true;

            $('#maspik-loader').fadeIn(200);

            // check if reason is selected
            var reason = $('input[name="maspik_deactivation_reason"]:checked').val();
            if (!reason) {
                alert('<?php _e('Please select a reason', 'contact-forms-anti-spam'); ?>');
                isSubmitting = false;
                $('#maspik-loader').fadeOut(200);
                return;
            }

            // check if text is entered if 'other' is selected
            if (reason === 'other' && !$('textarea[name="other_reason"]').val().trim()) {
                alert('<?php _e('Please provide more details', 'contact-forms-anti-spam'); ?>');
                isSubmitting = false;
                $('#maspik-loader').fadeOut(200);
                return;
            }

            var data = {
                reason: reason,
                other_reason:  $('textarea[name="other_reason"]').val().trim(),
                site_url: '<?php echo esc_js(get_site_url()); ?>',
                plugin_version: '<?php echo esc_js(MASPIK_VERSION); ?>',
                wp_version: '<?php echo esc_js(get_bloginfo("version")); ?>',
                php_version: '<?php echo esc_js(phpversion()); ?>',
                spam_count: '<?php echo esc_js(get_option("spamcounter", 0)); ?>'
            };

            console.log('Sending data:', data);

            try {
                var ajaxTimeout = setTimeout(function() {
                    console.log('Timeout reached - no response after 4 seconds');
                    isSubmitting = false;
                    $('#maspik-loader').fadeOut(200, function() {
                        proceedWithDeactivation();
                    });
                }, 4000);

                $.ajax({
                    url: 'https://receiver.wpmaspik.com/wp-json/statistics-maspik/v1/deactivation',
                    method: 'POST',
                    data: data,
                    timeout: 3500,
                    success: function(response) {
                        console.log('Success response:', response);
                        clearTimeout(ajaxTimeout);
                        $('#maspik-loader').fadeOut(200, function() {
                            proceedWithDeactivation();
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log('Error details:', {
                            status: status,
                            error: error,
                            response: xhr.responseText,
                            statusCode: xhr.status
                        });
                        clearTimeout(ajaxTimeout);
                        $('#maspik-loader').fadeOut(200, function() {
                            proceedWithDeactivation();
                        });
                    }
                });
            } catch (e) {
                console.log('Exception in AJAX:', e);
                $('#maspik-loader').fadeOut(200, function() {
                    proceedWithDeactivation();
                });
            }
        });

        // Skip survey
        $('#maspik-skip-survey').on('click', function() {
            proceedWithDeactivation();
        });

        // function to proceed with deactivation
        function proceedWithDeactivation() {
            if (deactivationLink) {
                window.location.href = deactivationLink;
            }
        }

        // Show/hide reason text based on selection
        $('input[name="maspik_deactivation_reason"]').on('change', function() {
            $('.reason-text-wrapper').slideUp(200);
            var selectedReason = $(this).val();
            $('[data-reason="' + selectedReason + '"]').slideDown(200);
        });

        $('.maspik-close-button').on('click', function() {
            $('#maspik-deactivation-survey').hide();
        });

        $(document).on('keyup', function(e) {
            if (e.key === "Escape") {
                $('#maspik-deactivation-survey').hide();
            }
        });
    });
    </script>
    <?php
}

