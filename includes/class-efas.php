<?php

/**
 * The file that defines the core plugin class 
 */
class contact_forms_anti_spam {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SETTINGS_PAGE_VERSION' ) ) {
			$this->version = SETTINGS_PAGE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'contact-forms-anti-spam';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Settings_Page_Loader. Orchestrates the hooks of the plugin.
	 * - Settings_Page_i18n. Defines internationalization functionality.
	 * - Settings_Page_Admin. Defines all hooks for the admin area.
	 * - Settings_Page_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-efas-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-efas-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-efas-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-efas-public.php';

      
      /**
      * Forms functions
      */
     // functions
      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

      // functions
      	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wp-general.php';
      
      if( get_option( "maspik_support_Elementor_forms" ) != "no" ){
	// if elementor pro active
        if( in_array('elementor-pro/elementor-pro.php', apply_filters('active_plugins', get_option('active_plugins'))) ){ 
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/elementor-hooks.php';
        }
      }
    // if cf7 active
      if( get_option( "maspik_support_cf7" ) != "no" ){
        if( in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins'))) ){ 
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cf7-hooks.php';
        }
      }
      

	  //wpforms
      if( get_option( "maspik_support_Wpforms" ) != "no" ){
        if( in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins'))) || in_array('wpforms/wpforms.php', apply_filters('active_plugins', get_option('active_plugins'))) && cfes_is_supporting() ){ 
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wpforms-hooks.php';
        }
      }

	  //gravityforms
      if( get_option( "maspik_support_gravity_forms" ) != "no" ){
        if( in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')))  && cfes_is_supporting() ){ 
          require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gravityforms-hooks.php';
        }
      }

      
		$this->loader = new Elementor_forms_anti_spam_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Settings_Page_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Efas_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Settings_Page_Admin( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Settings_Page_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Settings_Page_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}