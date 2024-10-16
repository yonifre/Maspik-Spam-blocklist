<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
/**
 * The file that defines the core plugin class 
 */
class Maspik {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MASPIK_VERSION' ) ) {
			$this->version = MASPIK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'maspik';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-maspik-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-maspik-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-maspik-admin.php';
      
     // functions
      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';
     // spam block functions
      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/spam-block.php';

      /**
      * Forms functions
      */

	  // Everest-Forms
      if( maspik_get_settings( "maspik_support_everestforms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'everest-forms/everest-forms.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/everest.php';
		}
	}

	  // Jet-Forms
      if( maspik_get_settings( "maspik_support_jetforms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'jetformbuilder/jet-form-builder.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/jetform.php';
		}
	}

	  // Ninja-Forms
      if( maspik_get_settings( "maspik_support_ninjaforms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/ninjaforms.php';
		}
	}

      // wp-general
      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/wp-general.php';

      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/playground.php';
      
      if( maspik_get_settings( "maspik_support_Elementor_forms" ) != "no"  ){
	// if elementor pro active
        if ( maspik_is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/elementor.php';
        }
      }
    // if cf7 active
      if( maspik_get_settings( "maspik_support_cf7" ) != "no" ){
        if ( maspik_is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/cf7.php';
        }
      }

	  //wpforms
      if( maspik_get_settings( "maspik_support_Wpforms" ) != "no" ){
        if (
            (  maspik_is_plugin_active( 'wpforms-lite/wpforms.php' )|| maspik_is_plugin_active( 'wpforms/wpforms.php' ))
             && cfes_is_supporting() ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/wpforms.php';
        }
      }

	  // Gravity Forms
      if( maspik_get_settings( "maspik_support_gravity_forms" ) != "no" ){
        if ( maspik_is_plugin_active( 'gravityforms/gravityforms.php' ) && cfes_is_supporting() ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/gravityforms.php';
        }
      }

	  //  Formidable hook file
      if( maspik_get_settings( "maspik_support_formidable_forms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'formidable/formidable.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/formidable.php';
        }
      }
        
      //Forminator-hooks.php
      if( maspik_get_settings( "maspik_support_forminator_forms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'forminator/forminator.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/forminator.php';
        }
      }

      //fluentforms-hooks.php
      if( maspik_get_settings( "maspik_support_fluentforms_forms" ) != "no" ){ 
        if ( maspik_is_plugin_active( 'fluentform/fluentform.php' ) ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/fluentforms.php';
        }
      }

      //Bricks-hooks.php
      if( maspik_get_settings( "maspik_support_bricks_forms" ) != "no" ){ 
        if ( maspik_if_bricks_exist() ) {
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/forms/bricks.php';
        }
      }
      
      // If agree to shere Non sensitive information 
      if( maspik_get_settings("shere_data", '', 'old') == 1 ){ 
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/statistics-data.php';
      }

		$this->loader = new Maspik_Spam_Blacklist_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 */
	private function set_locale() {

		$plugin_i18n = new Maspik_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Maspik_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 */
	public function get_version() {
		return $this->version;
	}

}