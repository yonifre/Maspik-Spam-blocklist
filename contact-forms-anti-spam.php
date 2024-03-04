<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Maspik - Spam blacklist
 * Plugin URI:        https://wpmaspik.com/
 * Description:       Anti spam. Block specific words, IP, country, languages, from contact-froms and more...
 * Version:           0.13.0
 * Author:            yonifre
 * Author URI:        https://wpmaspik.com/blog/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       contact-forms-anti-spam
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 

/**
 * Currently plugin version.
 */
define( 'MASPIK_VERSION', '0.13.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-settings-page-activator.php
 */
// For future version
function activate_maspik() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-maspik-activator.php';
	//Settings_Page_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
// For future version
function deactivate_maspik() {
	//require_once plugin_dir_path( __FILE__ ) . 'includes/class-maspik-deactivator.php';
	//Settings_Page_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_maspik' );
//register_deactivation_hook( __FILE__, 'deactivate_maspik' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-maspik.php';


if (version_compare(PHP_VERSION, '7.0.0', '>=') && apply_filters( 'maspik_active_license_library', true )) {
  require plugin_dir_path(__FILE__) . 'license/license.php';
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
function Run_Maspik() {

	$plugin = new Maspik();
	$plugin->run();

}
Run_Maspik();

add_filter( 'plugin_row_meta', 'maspik_plugin_row_meta', 10, 2 );
function maspik_plugin_row_meta( $links, $file ) {
	if( strpos( $file, basename(__FILE__) ) ) {
		$leads_link = array(
			'donat_link' => '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">'.__( 'Give me 5 stars', 'contact-forms-anti-spam' ).'</a>',
			'settings' => '<a href="'.admin_url().'admin.php?page=maspik" target="_blank">'.__( 'Setting page', 'contact-forms-anti-spam' ).'</a>',
		);
		
		$links = array_merge( $links, $leads_link );
	}
	
	return $links;
}