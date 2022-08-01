<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Maspik - Spam blacklist
 * Plugin URI:        https://wpmaspik.com/
 * Description:       Eliminate spam. Block specific words, IP, country, languages, from contact-froms and more...
 * Version:           0.7.4
 * Author:            yonifre
 * Author URI:        https://wpmaspik.com/apis/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       contact-forms-anti-spam
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'SETTINGS_PAGE_VERSION', '0.7.4' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-settings-page-activator.php
 */
// For future version
function activate_contact_forms_anti_spam() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-efas-activator.php';
	//Settings_Page_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-settings-page-deactivator.php
 */
// For future version
function deactivate_contact_forms_anti_spam() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-efas-deactivator.php';
	//Settings_Page_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_efas' );
//register_deactivation_hook( __FILE__, 'deactivate_efas' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-efas.php';
if( get_option( 'to_include_api' ) || get_option( 'private_file_id' ) ){
	require plugin_dir_path( __FILE__ ) . 'license/license.php';
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_contact_forms_anti_spam() {

	$plugin = new contact_forms_anti_spam();
	$plugin->run();

}
run_plugin_contact_forms_anti_spam();

add_filter( 'plugin_row_meta', 'efas_plugin_row_meta', 10, 2 );
function efas_plugin_row_meta( $links, $file ) {
	if( strpos( $file, basename(__FILE__) ) ) {
		$leads_link = array(
			'donat_link' => '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">'.__( 'Give me 5 stars', 'contact-forms-anti-spam' ).'</a>',
			'settings' => '<a href="'.admin_url().'admin.php?page=contact-forms-anti-spam" target="_blank">'.__( 'Setting page', 'contact-forms-anti-spam' ).'</a>',
		);
		
		$links = array_merge( $links, $leads_link );
	}
	
	return $links;
}


function efas_update_message($plugin_data, $r) {
  if ($plugin_data['update']) {
    echo "<b style='padding-top: 10px; display: inline-block;'>You may need to activate the plugin again after the update, as the plugin name changed</b>";
    return;
    $readme = wp_remote_fopen('https://plugins.svn.wordpress.org/contact-forms-anti-spam/trunk/README.txt');
    if (! $readme)
      return;
	$v = SETTINGS_PAGE_VERSION;
	$pattern = "/==\s*Changelog\s*==(.*)\s*=\s*$v\s*=\s*(upgrade_notice:)/";
    if (
      false === preg_match($pattern, $readme, $matches)
      
    )
      return;
    $changelog = (array) preg_split('/[\r\n]+/', trim($matches[1]));
    if (empty($changelog))
      return;

    $output = '<div style="margin: 8px 0 0 26px;">';
    $output .= '<ul style="margin-left: 14px; line-height: 1.5; list-style: disc outside none;">';

    $item_pattern = '/^\s*\*\s*/';
    foreach ($changelog as $line)
      if (preg_match($item_pattern, $line))
        $output .= '<li>'.preg_replace('/`([^`]*)`/', '<code>$1</code>', htmlspecialchars(preg_replace($item_pattern, '', trim($line)))).'</li>';

    $output .= '</ul>';
    $output .= '</div>';

    echo $output;
  }
} // function update_message
//add_action('in_plugin_update_message-'.basename(dirname(__FILE__)).'/'.basename(__FILE__), 'efas_update_message', 10, 2);