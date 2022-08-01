<?php

/**
 * The admin-specific functionality of the plugin.
 *
 */

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    Settings_Page
 * @subpackage Settings_Page/admin
 */
class Settings_Page_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);   
		add_action('admin_init', array( $this, 'registerAndBuildFields' ));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */ 
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Settings_Page_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Settings_Page_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		//wp_enqueue_style( "css_select2_".$this->plugin_name, 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Settings_Page_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Settins_Page_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( "js_select2_".$this->plugin_name, 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), $this->version, false );

	}
	public function addPluginAdminMenu() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(  $this->plugin_name, 'Anti spam', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminDashboard' ), 'dashicons-welcome-comments', 85 );
		
		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
      	$numlogspam =  get_option( 'spamcounter' ) ? "(".get_option( 'spamcounter' ).")" : false;
		add_submenu_page( $this->plugin_name, 'Spam log', 'Spam log '.$numlogspam, 'edit_pages', $this->plugin_name.'-log.php', array( $this, 'displayPluginAdminSettings' ));
		add_submenu_page( $this->plugin_name, 'Maspik Pro', 'Maspik Pro', 'administrator', $this->plugin_name.'-pro.php', array( $this, 'displayPluginAdminPro' ));
		add_submenu_page( $this->plugin_name, 'Options', 'Options', 'administrator', $this->plugin_name.'-options.php', array( $this, 'displayPluginAdminOptions' ));
	}
	public function displayPluginAdminDashboard() {
		require_once 'partials/'.$this->plugin_name.'-admin-display.php';
		//require_once 'partials/'.$this->plugin_name.'-log.php';
  }
	public function displayPluginAdminSettings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
				add_action('admin_notices', array($this,'settingsPageSettingsMessages'));
				do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-log.php';
	}
	public function displayPluginAdminPro() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
				add_action('admin_notices', array($this,'settingsPageSettingsMessages'));
				do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-pro.php';
	}
	public function displayPluginAdminOptions() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
				add_action('admin_notices', array($this,'settingsPageSettingsMessages'));
				do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-options.php';
	}
	public function settingsPageSettingsMessages($error_message){
		switch ($error_message) {
				case '1':
						$message = __( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'contact-forms-anti-spam' );                 $err_code = esc_attr( 'settings_page_example_setting' );                 $setting_field = 'settings_page_example_setting';                 
						break;
		}
		$type = 'error';
		add_settings_error(
					$setting_field,
					$err_code,
					$message,
					$type
			);
	}
  
	public function registerAndBuildFields() {
			/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */     
		add_settings_section(
			// ID used to identify this section and with which to register options
			'settings_page_general_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
				array( $this, 'settings_page_display_general_account' ),    
			// Page on which to add this section of options
			'settings_page_general_settings'                   
		);
            add_settings_section(
			// ID used to identify this section and with which to register options
			'settings_page_bonus_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
				array( $this, 'settings_page_display_bonus_text' ),    
			// Page on which to add this section of options
			'settings_page_general_settings'                   
		);
            add_settings_section(
			// ID used to identify this section and with which to register options
			'settings_page_pro_section', 
			// Title to be displayed on the administration page
			'Connect your site to the SPAM API',  
			// Callback used to render the description of the section
				array( $this, 'settings_page_display_general_account' ),    
			// Page on which to add this section of options
			'settings_page_pro_settings_page'                   
		);
            add_settings_section(
			// ID used to identify this section and with which to register options
			'settings_page_option_section', 
			// Title to be displayed on the administration page
			'Supporting manager',  
			// Callback used to render the description of the section
				array( $this, 'settings_page_display_general_account' ),    
			// Page on which to add this section of options
			'settings_page_option_settings_page'                   
		);

      		

		unset($args);
// First field
		$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'text_blacklist',
          'placeholder'      => 'Eric Jones',          
          'name'      => 'text_blacklist',
          'description'      => __( 'Example:Eric jones<br>SEO expert<br>If the text value is EQUAL to one of the values above, MASPIK will tag it as spam and it will be blocked.', 'contact-forms-anti-spam' ),
          'required' => 'true',
          'api'=>'text_field',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'text_blacklist',
			__("Text field", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
      	unset($args);
// MaxCharactersInTextField 
		$args = array (
          'type'      => 'input',
          'subtype'   => 'number',
          'id'    => 'MaxCharactersInTextField',
          'placeholder'      => '',          
          'name'      => 'MaxCharactersInTextField',
          'description'      => __('If the text field contains more characters that this value, it will be considered spam and it will be blocked.<br>Recommended character limit: 30.', 'contact-forms-anti-spam' ),
          'required' => '',
          'api'=>'MaxCharactersInTextField',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'MaxCharactersInTextField',
			__("Limit text field to X characters.", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
      	unset($args);
      
// secend field
		$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'emails_blacklist',
          'name'      => 'emails_blacklist',
          'description'      => __('Example:<br>test@gmail.com<br>ericjonesonline@outlook.com
<br>If the text value is EQUAL to one of the values above, MASPIK will tag it as spam and it will be blocked.
<br>You can enter ending of email as well, like: @gmail.com will block all the email comming from @gmail.com (xyz@gmail.com) 
<br><br>You can use the Regex format, for example: /\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.ru\b/, 
<br>This example will block all email that ends with ".ru".<br>*Note - Regex must start and end with a slash / ', 'contact-forms-anti-spam' ),
          'placeholder'      => 'ericjonesonline@outlook.com',          
          'attr' => false,
          'api'=>'email_field',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'emails_blacklist',
			__("Email field", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
unset($args);
$name = get_bloginfo('name');  
$url = get_bloginfo('url');  
$description = get_bloginfo('description');  
// Text area field
		$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'textarea_blacklist',
          'name'      => 'textarea_blacklist',
          'description'      => __("Try to use full sentences that you typically find in spam emails, rather than single words
          <br>If the text area field CONTAINS one of the values above, MASPIK will tag it as spam and it will not be accepted.
          <br>You can also use the following shortcodes:<br>", 'contact-forms-anti-spam' ).__("
[name] - Title of the web site- $name<br>
[url] - URL of the web site- $url<br>
[description] - Description of the web site- $description", 'contact-forms-anti-spam' ),
          'atter' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'api'=>'textarea_field',
          'wp_data' => 'option'
					);
		add_settings_field(
			'textarea_blacklist',
			__("Text area field", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
unset($args);
/*$sitetitle = get_bloginfo ( 'name' );      
$sitedescription = get_bloginfo ( 'description' );      
$siteurl = get_bloginfo ( 'url' );      
// Contain site details
		$args = array (
          'type'      => 'input',
          'subtype'   => 'select',
          'id'    => 'forbidden_strings',
          'name'      => 'forbidden_strings',
          'array' => array(
            				$sitetitle => __("Site title", 'contact-forms-anti-spam' )." ($sitetitle)" ,
            				$sitedescription => __("Site description", 'contact-forms-anti-spam' )." ($sitedescription)" ,
            				$siteurl => __("Site URL", 'contact-forms-anti-spam' )." ($siteurl)" ,
                          ),
          'description'      => '',
          'atter' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'forbidden_strings',
			__("Don't allow leads that textarea field <u>Contain</u> those strings", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
unset($args);  */   
// Contain links 
		$args = array (
          'type'      => 'input',
          'subtype'   => 'number',
          'id'    => 'contain_links',
          'name'      => 'contain_links',
          'description'      => __('Spammers tend to include links.<br>If there is no reason for anyone to send links when completing your forms, set this to 1.', 'contact-forms-anti-spam' ),
          'atter' => false,
          'min' => '0',
          'step' => '1', 
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'contain_links',
			__("Mark as spam if the text area contains X or more number of links.", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
unset($args);  
// Lang
	$args = array (
          'type'      => 'input',
          'subtype'   => 'select',
          'id'    => 'lang_needed',
          'name'      => 'lang_needed',
          'description'      => __('If you use this field, it will ONLY accept form submissions that contain at least one character of the chosen language.<br>Leave blank if you prefer to forbid certain languages.', 'contact-forms-anti-spam' ),
          'array' => efas_array_of_lang(),
          'get_options_list' => '',
          'api'=>'lang_needed',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'lang_needed',
			__("Languages required", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);  
unset($args);
//lang_forbidden      
	$args = array (
          'type'      => 'input',
          'subtype'   => 'select',
          'id'    => 'lang_forbidden',
          'name'      => 'lang_forbidden',
          'description'      => __('Select the languages you wish to block from filling out your forms.<br>Even one character in the text field from any of these languages will be caught by MASPIK, tagged as spam, and blocked.', 'contact-forms-anti-spam' ),
          'array' => efas_array_of_lang(),
          'get_options_list' => '',
          'api'=>'lang_forbidden',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'lang_forbidden',
			__("Languages forbidden", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);  
unset($args);

// tel format
	$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'tel_formats',
          'name'      => 'tel_formats',
          'description'      => __('If you want more than one format, use the next line.<br>For example, if you want the  XXX-XXX-XXXX format, please add:<br> /[0-9]{3}-[0-9]{3}-[0-9]{4}/<br>
You can get more ideas here: <a target="_blank" href="https://regex101.com/library?orderBy=MOST_POINTS&search=phone%20number%20validation">https://regex101.com/library?orderBy=MOST_POINTS&search=phone%20number%20validation</a><br> ', 'contact-forms-anti-spam' ),
          'attr' => false, 
          'get_options_list' => '',
          'value_type'=>'normal',
          'api'=>'phone_format',
          'wp_data' => 'option'
					);
		add_settings_field(
			'tel_formats',
			__("Only allow phone numbers in this format:", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);  
unset($args);    
// Ip field
		$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'ip_blacklist',
          'name'      => 'ip_blacklist',
          'description'      => 'Any IP you enter above will be blocked. One IP per line.<br>You can also filter entire CIDR range such as 134.209.0.0/16<br>The submitter IP will be loop through this list.',
          'attr' => false,
          'api'=>'ip',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'ip_blacklist',
			__("IP", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);
unset($args);
// allowed Or block Countries
	$args = array (
          'type'      => 'input',
          'subtype'   => 'radio',
          'id'    => 'AllowedOrBlockCountries',
          'name'      => 'AllowedOrBlockCountries',
          'description'      => __('Choose one of the options above and enter the countries in the next field.
          <br>If <b>allowed</b>, only forms from these countries will be accepted, if blocked, all countries in the following list will be blocked', 'contact-forms-anti-spam' ),
          'attr' => false,
          'array' => array(
            'block' => 'Block the following countries',
            'allow' => 'Allow only the following countries',
          ),
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'AllowedOrBlockCountries',
			__("Countries allowed/blocked:", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);  
unset($args);
// country field
	$args = array (
          'type'      => 'input',
          'subtype'   => 'select',
          'id'    => 'country_blacklist',
          'name'      => 'country_blacklist',
          'description'      => __('You can choose as many as you like.', 'contact-forms-anti-spam' ),
          'attr' => false,
          'array' => efas_array_of_countries(),
          'get_options_list' => '', 
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'country_blacklist',
			__("Allow/block the following countries to allow/block (based on your selection above):", 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);  
unset($args);
//NeedPageurl
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'NeedPageurl',
          'name'      => 'NeedPageurl',
          'description'=>__('Some of the bots that fill out forms will send a form missing a source URL. If you check this box, any empty source URL forms will be considered to be spam.', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'api'=>'block_empty_source',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
          add_settings_field(
              'NeedPageurl',
              __('Block inquiries devoid of source URL. <small>(Elementor forms only)</small>', 'contact-forms-anti-spam' ),
              array( $this, 'settings_page_render_settings_field' ),
              'settings_page_general_settings',
              'settings_page_general_section',
              $args
          );  
unset($args); 
//Ross's spam pixel idea
/*
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'spampixel',
          'name'    => 'spampixel',
          'description'      => __('Idea from Ross Wintle - https://rosswintle.uk/2021/03/css-only-spam-prevention-allow-list-captcha/', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'api'=>'spampixel',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
          add_settings_field(
              'spampixel',
              __('Smart spam bot capture', 'contact-forms-anti-spam' ),
              array( $this, 'settings_page_render_settings_field' ),
              'settings_page_general_settings',
              'settings_page_general_section',
              $args
          );  */
//error_message
 unset($args); 
		$args = array (
          'type'      => 'input',
          'subtype'   => 'text',
          'id'    => 'error_message',
          'name'      => 'error_message',
          'description'      => __('Default: "This looks like spam. Try to rephrase, or contact us in an alternative way." <br>You can leave this as the default or rephrase it as you like. This is the error message that the spammer will receive.<br>It’s a good idea to put your phone number or alternative method of contact, just in case it’s not really a spammer.', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
		add_settings_field(
			'error_message',
			__('Validation error message', 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);   
     
unset($args); 
//spamcounter      
		$args = array (
          'type'      => 'input',
          'subtype'   => 'number',
          'id'    => 'spamcounter',
          'name'      => 'spamcounter',
          'description'      => '',
          'attr'      => "style='display:none;'",          
          'default' => '0',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'spamcounter',
			'',
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
		);       
unset($args);
      
//error log      
		$args = array (
          'type'      => 'textarea',
          'subtype'   => 'textarea',
          'id'    => 'errorlog',
          'name'      => 'errorlog',
          'description'      => '',
          'attr'      => "style='display:none;'",          
          'default' => '0',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'errorlog',
			'',
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_general_settings',
			'settings_page_general_section',
			$args
	);       
      
//AbuseIPDB API (Thanks to @josephcy95)
unset($args);
$args = array(
  'type'      => 'input',
  'subtype'   => 'text',
  'id'    => 'abuseipdb_api',
  'name'      => 'abuseipdb_api',
  'description'      => __('AbuseIPDB.com API (Leave blank to disable)', 'contact-forms-anti-spam'),
  'label'      => 0,
  'attr' => false,
  'get_options_list' => '',
  'value_type' => 'normal',
  'wp_data' => 'option'
);
add_settings_field(
  'abuseipdb_api',
  __('AbuseIPDB API', 'contact-forms-anti-spam'),
  array($this, 'settings_page_render_settings_field'),
  'settings_page_general_settings',
  'settings_page_general_section',
  $args
);

// AbuseIPDB Score Threshold
unset($args);
$args = array(
  'type'      => 'input',
  'subtype'   => 'number',
  'id'    => 'abuseipdb_score',
  'name'      => 'abuseipdb_score',
  'description'      => __('Recommend not lower than 25 for less false positives.', 'contact-forms-anti-spam'),
  'atter' => false,
  'min' => '0',
  'max' => '100',
  'step' => '1',
  'value_type' => 'normal',
  'wp_data' => 'option'
);
add_settings_field(
  'abuseipdb_score',
  __('AbuseIPDB Risk Threshold', 'contact-forms-anti-spam'),
  array($this, 'settings_page_render_settings_field'),
  'settings_page_general_settings',
  'settings_page_general_section',
  $args
);

// Proxycheck.io API
unset($args);
$args = array(
  'type'      => 'input',
  'subtype'   => 'text',
  'id'    => 'proxycheck_io_api',
  'name'      => 'proxycheck_io_api',
  'description'      => __('Proxycheck.io API (Leave blank to disable)', 'contact-forms-anti-spam'),
  'label'      => 0,
  'attr' => false,
  'get_options_list' => '',
  'value_type' => 'normal',
  'wp_data' => 'option'
);
add_settings_field(
  'proxycheck_io_api',
  __('Proxycheck.io API', 'contact-forms-anti-spam'),
  array($this, 'settings_page_render_settings_field'),
  'settings_page_general_settings',
  'settings_page_general_section',
  $args
);

// Proxycheck.io Risk Score
unset($args);
$args = array(
  'type'      => 'input',
  'subtype'   => 'number',
  'id'    => 'proxycheck_io_risk',
  'name'      => 'proxycheck_io_risk',
  'description'      => __('Low risk 0-33, MidHigh risk = 33-66, Dangerous = 66 Above', 'contact-forms-anti-spam'),
  'atter' => false,
  'min' => '0',
  'max' => '100',
  'step' => '1',
  'value_type' => 'normal',
  'wp_data' => 'option'
);
add_settings_field(
  'proxycheck_io_risk',
  __('Proxycheck.io Risk Threshold', 'contact-forms-anti-spam'),
  array($this, 'settings_page_render_settings_field'),
  'settings_page_general_settings',
  'settings_page_general_section',
  $args
);

unset($args);       
//Pro
//to active API license files?
        $args = array (
            'type'      => 'input',
            'subtype'   => 'checkbox',
            'id'    => 'to_include_api',
            'name'      => 'to_include_api',
            'description'      => __('Check to activate. By checking, an License library will be added.<br><b>This works only with PHP 7 or higher.</b>', 'contact-forms-anti-spam' ),
            'label'      => 0,
            'attr' => false,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'to_include_api',
            __('Do you want to activate the API/Pro options?', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_pro_settings_page',
            'settings_page_pro_section',
            $args
        );
        unset($args);
//Privet API file (Post ID)

		$args = array (
          'type'      => 'input',
          'subtype'   => 'text', 
          'id'    => 'private_file_id',
          'name'      => 'private_file_id',
          'description'      => __('After you create an API file, you will see an ID number on the API page.<br>If you want to use more than one, separate them with a comma.<br>For example, 64,72', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
		add_settings_field(
			'private_file_id',
			__('The ID of the API you create', 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_pro_settings_page',
			'settings_page_pro_section',
			$args
		);   
unset($args); 

////////////////////////options page
//support Elementor forms 
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_Elementor_forms',
            'name'      => 'maspik_support_Elementor_forms',
            'description'      => __('If plugin is active and Maspik pro available.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'elementor-pro',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_Elementor_forms',
            __('Support Elementor forms', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support CF7 
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_cf7',
            'name'      => 'maspik_support_cf7',
            'description'      => __('If plugin is active.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => "contact-form-7",
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_cf7',
            __('Support Contact from 7', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
      
//support wp comment
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_wp_comment',
            'name'      => 'maspik_support_wp_comment',
            'description'      => __('', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 1,
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_wp_comment',
            __('Support wp comment', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support Wp Registration
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_registration',
            'name'      => 'maspik_support_registration',
            'description'      => __('If Anyone can register is checked (At WP Options =>  General).', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'Wordpress Registration',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_registration',
            __('Support wp Registration', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support woocommerce review
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_woocommerce_review',
            'name'      => 'maspik_support_woocommerce_review',
            'description'      => __('If plugin is active and Maspik pro available.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'woocommerce',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_woocommerce_review',
            __('Support woocommerce review', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support Woocommerce Registration
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_Woocommerce_registration',
            'name'      => 'maspik_support_Woocommerce_registration',
            'description'      => __('If plugin is active and Maspik pro available.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'woocommerce',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_Woocommerce_registration',
            __('Support Woocommerce Registration', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support Wpforms 
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_Wpforms',
            'name'      => 'maspik_support_Wpforms',
            'description'      => __('If plugin is active and Maspik pro available.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'wpforms',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'default' => 1,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_Wpforms',
            __('Support Wpforms', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
//support gravity forms 
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_gravity_forms',
            'name'      => 'maspik_support_gravity_forms',
            'description'      => __('If plugin is active and Maspik pro available.', 'contact-forms-anti-spam' ),
            'label'      => 0,
            'attr' => false,
          	'depends' => 'gravityforms',
            'default' => 'yes',
            'array' => array(
              'yes' => 'Support',
              'no' => "Don't support",
            ),
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_support_gravity_forms',
            __('Support Gravity Forms', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
 //Store log 
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_Store_log',
            'name'      => 'maspik_Store_log',
            'description'      => __('If disabled, the Log of the blocked spam will not be saved.', 'contact-forms-anti-spam' ),
            'label'      => 0,
            'attr' => false,
            'default' => 'yes',
            'array' => array(
              'yes' => 'Save spam Log (Only last 100 blockages)',
              'no' => "Disable Spam Log",
            ),
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'maspik_Store_log',
            __('Spam Log', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
     
/*
//Public API file      
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'public_file',
          'name'      => 'public_file',
          'description'      => __('A black list file with common spam words', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
		add_settings_field(
			'public_file',
			__('Do you wanna use the Public API file?', 'contact-forms-anti-spam' ),
			array( $this, 'settings_page_render_settings_field' ),
			'settings_page_pro_settings_page',
			'settings_page_pro_section',
			$args
		);   

*/

// register fields
        register_setting('settings_page_general_settings_page', 'abuseipdb_api');
        register_setting('settings_page_general_settings_page', 'abuseipdb_score');
        register_setting('settings_page_general_settings_page', 'proxycheck_io_api');
        register_setting('settings_page_general_settings_page', 'proxycheck_io_risk');

      	register_setting('settings_page_option_settings_page','maspik_support_Elementor_forms');
      	register_setting('settings_page_option_settings_page','maspik_support_wp_comment');
      	register_setting('settings_page_option_settings_page','maspik_support_woocommerce_review');
      	register_setting('settings_page_option_settings_page','maspik_support_registration');
      	register_setting('settings_page_option_settings_page','maspik_support_Woocommerce_registration');
      	register_setting('settings_page_option_settings_page','maspik_support_cf7');
      	register_setting('settings_page_option_settings_page','maspik_support_Wpforms');
      	register_setting('settings_page_option_settings_page','maspik_support_gravity_forms');
      	register_setting('settings_page_option_settings_page','maspik_Store_log');

      	register_setting('settings_page_pro_settings_page','private_file_id');
      	register_setting('settings_page_pro_settings_page','public_file');
      	register_setting('settings_page_pro_settings_page','to_include_api');

		register_setting('settings_page_general_settings_page','text_blacklist');

      	register_setting('settings_page_general_settings_page','MaxCharactersInTextField');
      	register_setting('settings_page_general_settings_page','emails_blacklist');
      	register_setting('settings_page_general_settings_page','textarea_blacklist');
      	//register_setting('settings_page_general_settings_page','forbidden_strings');
  		register_setting('settings_page_general_settings_page','blockRussianlang');
        register_setting('settings_page_general_settings_page','contain_links');
      	register_setting('settings_page_general_settings_page','lang_needed');
      	register_setting('settings_page_general_settings_page','lang_forbidden');
        register_setting('settings_page_general_settings_page','ip_blacklist');
      	register_setting('settings_page_general_settings_page','AllowedOrBlockCountries');
      	register_setting('settings_page_general_settings_page','country_blacklist');
      	register_setting('settings_page_general_settings_page','NeedPageurl');
      //	register_setting('settings_page_general_settings_page','spampixel');
        register_setting('settings_page_general_settings_page','error_message');
        register_setting('settings_page_general_settings_page','tel_formats');
	}

	public function settings_page_display_general_account() {
		//echo '<p>These settings apply to all Plugin Name functionality.</p>';
	} 
  	public function settings_page_display_bonus_text() {

	} 
	public function settings_page_render_settings_field($args) {   
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}

		$description =(isset($args['description'])) ? $args['description'] : '';
        $placeholder = (isset($args['placeholder'])) ? 'placeholder="'.$args['placeholder'].'"' : '';
		$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
		$attr = isset($args['attr']) ? $args['attr'] : "";
        $default =(isset($args['default'])) ? $args['default'] : '';
        $value = $value ? $value : $default;
        $is_depends = isset($args['depends']) && !efas_if_plugin_is_active($args['depends']) ? "disabled" : false;
		$api = isset($args['api']) ? get_option( "spamapi" )[$args['api']] : false;
		switch ($args['type']) {

			case 'input':
					if($args['subtype'] != 'checkbox' && $args['subtype'] != 'select' && $args['subtype'] != 'radio'){
							$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
							$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
							$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
							$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
							$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
							if(isset($args['disabled'])){
									// hide the actual input bc if it was just a disabled input the info saved in the database would be wrong - bc it would pass empty values and wipe the actual information
									echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$attr.' '.$placeholder.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
							} else {
									echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" '.$attr.' '.$step.' '.$max.' '.$placeholder.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
							}
							/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/

					} else if ($args['subtype'] == 'select'){
                      	$label = (isset($args['label'])) ? $args['label'] : "";
                      	$id = $args['id'];
                        $curr_val = $value;
                        $num = 1;
                      	$the_array = $args['array'];  
                    
                        if ($label != '') echo '  <label for="'.$id.'">'.$label.'</label>';
                            echo '<select class="js-states form-control select2" multiple="multiple"  name="'.$id.'[]" id="'.$id.'"  >';
                            foreach ($the_array as $key => $value) {
                                echo ' <option value="'.$key.'"';
                                if(is_array($curr_val)){
                                    foreach ($curr_val as $curr) {
                                        if ($key == $curr) {
                                            echo ' selected="selected"';
                                        }
                                    }
                                }
                              	echo '>'.$value.'</option>';
                              $num ++;
                            }
                            echo '</select>';
                    } else if ($args['subtype'] == 'radio'){
                      	$label = (isset($args['label']) && $args['label'] != 0 )? $args['label'] : "";
                      	$id = $args['id'];
                        $curr_val = $value;
                        $num = 1;
                      	$the_array = $args['array'];  
                    
                        if ($label != '') echo '  <label for="'.$id.'">'.$label.'</label>';
                            foreach ($the_array as $key => $value) {
                                echo ' <input type="radio" '.$is_depends.' name="'.$id.'" value="'.$key.'"';
                                    if ( ($curr_val && $key == $curr_val) || ($curr_val == "" && $key == "block") || ($curr_val != "no" && $key == "yes") ) {
										echo ' checked ';
                                  }
                              	echo '>';
                      			echo '  <label for="'.$id.'">'.$value.'</label><br>';
                            }
                    } else {
							$checked = ($value) ? 'checked' : '';
							echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" '.$attr.' name="'.$args['name'].'" size="40" value="1" '.checked(   get_option( $args['id'] ), 1, false ) .' />';
					}
            
					break;
            	case 'textarea':
                    		$placeholder = (isset($args['placeholder'])) ? $args['placeholder'] : '';
            				$has_value = ($value) ? $value : $placeholder;
            				echo '<textarea '.$attr.' id="'.$args['id'].'" name="'.$args['name'].'" rows="5" cols="80">' . esc_attr($has_value) . ' </textarea>';

			default:
					# code...
					break;
		}
      if ($api){ 
		echo "<div class='api'><small style='display: block;'>".__('Options already added automatically from the API', 'contact-forms-anti-spam' )."</small><div>";
        foreach ($api as $line){
          echo "<pre>$line</pre>";
       	}
		echo "</div></div>";
      }

      	echo "<small style='display: block;'>$description</small>";

	}
}