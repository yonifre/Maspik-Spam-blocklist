<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/* The admin-specific functionality of the plugin.
 *
 *
 */
class Maspik_Admin {

	/**
	 * The ID of this plugin.
	 *
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 */
	private $version;

    
    private $custom_error_message_by_fields = array(
            'MaxCharactersInTextField',
            'lang_needed',
            'contain_links',
            'tel_formats',
            'lang_forbidden',
            'textarea_blacklist',
            'country_blacklist'
        );


	/**
	 * Initialize the class and set its properties.
	 *
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
        $screen = get_current_screen();
        if ( false !== strpos($screen->id, 'maspik') ) { 
            wp_enqueue_style( "Maspik-admin-style", plugin_dir_url(__DIR__).'/admin/css/admin-style.css', array(), $this->version, 'all' );
        }
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
		 */

		//wp_enqueue_script( "js_select2_".$this->plugin_name, 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), $this->version, false );

	}
    public function addPluginAdminMenu() {
        add_menu_page($this->plugin_name, 'Maspik Spam', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'), 'dashicons-welcome-comments', 85);

        $numlogspam = get_option('spamcounter') ? "(" . get_option('spamcounter') . ")" : false;
        add_submenu_page($this->plugin_name, 'Maspik List', 'Maspik List', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'));
        add_submenu_page($this->plugin_name, 'Spam Log', 'Spam Log ' . $numlogspam, 'edit_pages', $this->plugin_name . '-log.php', array($this, 'displayPluginAdminSettings'));
        add_submenu_page($this->plugin_name, 'Maspik API', 'Maspik API', 'administrator', $this->plugin_name . '-api.php', array($this, 'displayPluginAdminPro'));
        add_submenu_page($this->plugin_name, 'Options', 'Options', 'administrator', $this->plugin_name . '-options.php', array($this, 'displayPluginAdminOptions'));
        add_submenu_page($this->plugin_name, 'Import/Export Settings', 'Import/Export Settings', 'administrator', $this->plugin_name . '-import-export.php', array($this, 'Maspik_import_export_settings_page'));
    }

    public function displayPluginAdminDashboard() {
        require_once 'partials/' . $this->plugin_name . '-admin-display.php';
    }

    public function displayPluginAdminSettings() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-log.php';
    }

    public function displayPluginAdminPro() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-api.php';
    }

    public function displayPluginAdminOptions() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-options.php';
    }

    public function Maspik_import_export_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        if (isset($_GET['error_message'])) {
            add_action('admin_notices', array($this, 'settingsPageSettingsMessages'));
            do_action('admin_notices', $_GET['error_message']);
        }
        require_once 'partials/' . $this->plugin_name . '-import-export.php';
    }

    public function settingsPageSettingsMessages($error_message) {
        switch ($error_message) {
            case '1':
                $message = __('There was an error adding this setting. Please try again. If this persists, shoot us an email.', 'contact-forms-anti-spam');
                $err_code = esc_attr('Error');
                $setting_field = 'Error';
                break;
        }
        $type = 'error';
        add_settings_error($setting_field, $err_code, $message, $type);
    }

    public function maspik_settings_sanitize_callback($input) {
        $name = str_replace('sanitize_option_', '', current_filter());
        $arr = array('country_blacklist', 'lang_needed', 'lang_forbidden');
        if (in_array($name, $arr)) {
            return $input;
        }
        $input = str_replace("\n", ",", $input);
        $sanitize_input = sanitize_text_field($input);
        $sanitize_input = str_replace(",", "\n", $sanitize_input);
        return $sanitize_input;
    }
    
    public function maspik_build_settings_custom_error_message($input) {
        // Get the current filter name
        $name = str_replace('sanitize_option_', '', current_filter());

        // Get the existing custom error message array
        $custom_error_message = get_option('custom_error_message');

        if (!is_array($custom_error_message)) {
            // If the array doesn't exist yet, create a new one with the current key-value pair
            $array = array($name => $input);
        } else {
            // If the array already exists, add the current key-value pair to it
            $custom_error_message[$name] = $input;
            $array = $custom_error_message;
        }

        // Return the updated custom error message array
        return $array;
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
          'description'      => __( 'If the text value CONTAINS one of the given values, it will be marked as spam and blocked.
<br><b>Wildcard pattern</b> accepted as well, asterisk * symbol is nessery for the recognition of the wildcard.', 'contact-forms-anti-spam' ),
          'required' => 'true',
          'api'=>'text_field',
          'get_options_list' => '',
          'example'=>'Eric jones,SEO,ranking,currency,click here ',
          'subject'=>'Text field',
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'text_blacklist',
			__("Text field", 'contact-forms-anti-spam' )."<br><small>".__("(Usually Name/Subject)", 'contact-forms-anti-spam' )."</small>",
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
          'description'      => __('If the text value is EQUAL to one of the values above, MASPIK will tag it as spam and it will be blocked.
<br><b>Email Domain</b> You can enter ending of email as well, like: @gmail.com will block all the email comming from @gmail.com (xyz@gmail.com) 
<br>You can use the <b>Regex format</b><br>*Note - Regex must start and end with a slash / <br><b>Wildcard pattern</b> accepted as well, asterisk * symbol is nessery for the recognition of the wildcard.', 'contact-forms-anti-spam' ),
          'placeholder'      => 'ericjonesonline@outlook.com',          
          'attr' => false,
          'example'=>'georginahaynes620@gmail.com,ericjonesonline@outlook.com,*.ru,/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.ru\b/,eric*@*.com,xrumer888@outlook.com',
          'subject'=>'Email field',
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
          <br>If the Textarea value CONTAINS one of the given values, it will be marked as spam and blocked.
          <br>You can also use the following shortcodes:<br>", 'contact-forms-anti-spam' ).__("
[name] - Title of the web site- $name<br>
[url] - URL of the web site- $url<br>
[description] - Description of the web site- $description", 'contact-forms-anti-spam' ),
          'atter' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'api'=>'textarea_field',
          'subject'=>'Text area field',
          'example'=>'submit your website,seo,ranking,currency,click here ',
          'wp_data' => 'option'
					);
		add_settings_field(
			'textarea_blacklist',
			__("Text area field", 'contact-forms-anti-spam' )."<br><small>".__("(Usually Message/Long text)", 'contact-forms-anti-spam' )."</small>",
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
          'api'=>'contain_links',
          'atter' => false,
          'min' => '0',
          'step' => '1', 
          'value_type'=>'normal',
          'wp_data' => 'option'
					);
		add_settings_field(
			'contain_links',
			__("Considered as spam IF a text-area field containing at least X links (X or more).", 'contact-forms-anti-spam' ),
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
You can get more ideas here: <a target="_blank" href="https://regex101.com/library?orderBy=MOST_POINTS&search=phone%20number%20validation">https://regex101.com/library?orderBy=MOST_POINTS&search=phone%20number%20validation</a><br>
<b>Wildcard pattern</b> accepted as well, asterisk * symbol is nessery for the recognition of the wildcard. ', 'contact-forms-anti-spam' ),
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
// Lang needed
	$args = array (
          'type'      => 'input',
          'subtype'   => 'select',
          'id'    => 'lang_needed',
          'class' => 'lang_needed pro',         
          'pro' => '1',         
         'name'      => 'lang_needed',
          'description'      => __('If you use this field, it will ONLY accept form submissions that contain at least one character of the chosen language.<br>Leave blank if you prefer to forbid certain languages.', 'contact-forms-anti-spam' ),
          'array' => efas_array_of_lang_needed(),
      	  'title' =>  __('Beware that requiring Latin languages as an individual (Like: Dutch, French), the check is in the language punctuation letters and letters A to Z (So including English) because sometimes the punctuation letters as not used, Its to prevent false positive.'),
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
          'class' => 'lang_forbidden pro',         
          'pro' => '1',         
          'title' =>  __('Note that when blocking Latin languages in an individual (such as: Dutch, French), the chack is in the punctuation letters (But they are not always in use). Its to prevent false positive'),
          'name'      => 'lang_forbidden',
          'description'      => __('Select the languages you wish to block from filling out your forms.<br>Even one character in the text field from any of these languages will be caught by MASPIK, tagged as spam, and blocked.', 'contact-forms-anti-spam' ),
          'array' => efas_array_of_lang_forbidden(),
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
// allowed Or block Countries
	$args = array (
          'type'      => 'input',
          'subtype'   => 'radio',
          'id'    => 'AllowedOrBlockCountries',
          'pro' => '1',         
          'api'    => 'AllowedOrBlockCountries',
          'class' => 'AllowedOrBlockCountries pro', 
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
          'pro' => '1',         
          'api'      => 'country_blacklist',
          'description'      => __('You can choose as many as you like.', 'contact-forms-anti-spam' ),
          'attr' => false,
          'array' => efas_array_of_countries(),
          'get_options_list' => '', 
          'class' => 'country_blacklist pro', 
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
//Maspik_human_verification
/*
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'Maspik_human_verification',
          'name'    => 'Maspik_human_verification',
          'description'      => __('Bot capture - BETA', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'api'=>'Maspik_human_verification',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
          add_settings_field(
              'Maspik_human_verification',
              __('Maspik human verification', 'contact-forms-anti-spam' ),
              array( $this, 'settings_page_render_settings_field' ),
              'settings_page_general_settings',
              'settings_page_general_section',
              $args
          );  
      */
//error_message
 unset($args); 
		$args = array (
          'type'      => 'input',
          'subtype'   => 'text',
          'id'    => 'error_message',
          'name'      => 'error_message',
          'description'      => __('Default: "This looks like spam. Try to rephrase, or contact us in an alternative way." <br>You can leave this as the default or rephrase it as you like. This is the error message that the user/spammer will receive.', 'contact-forms-anti-spam' ),
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
//////
// List of setting names
$custom_error_message_by_fields = $this->custom_error_message_by_fields;

// Loop through each setting name
foreach ($custom_error_message_by_fields as $setting_name) {
    // Construct unique ID based on setting name
    $id = 'custom_error_message_' . $setting_name;

    // Configure settings field arguments
    $args = array(
        'type'             => 'input',
        'subtype'          => 'text',
        'id'               => $id,
        'name'             => $id, // Use setting name as name
        'description'      => __('custom error message per field', 'contact-forms-anti-spam'),
        'label'            => 0,
        'attr'             => false,
        'class' => 'hide',         
        'get_options_list' => '',
        'print'            => 'no',
        'value_type'       => 'normal',
        'wp_data'          => 'option'
    );

    // Add settings field
    add_settings_field(
        $id,
        __('Validation custom error message per field', 'contact-forms-anti-spam'),
        array($this, 'settings_page_render_settings_field'),
        'settings_page_general_settings',
        'settings_page_general_section',
        $args
    );
}
     
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
         'class' => 'hide',         
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
          'class' => 'hide',         
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
  'api'=>'abuseipdb_api',
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
  'api'=>'abuseipdb_score',
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
  'api'=>'proxycheck_io_api',
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
  'api'=>'proxycheck_io_risk',
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
//
unset($args);       
//Pro
//to active API license files?
/*
        $args = array (
            'type'      => 'input',
            'subtype'   => 'checkbox',
            'id'    => 'remove_pro_option',
            'name'      => 'remove_pro_option',
            'description'      => __('<b>For old version of PHP ( version  = < 6 ).</b>', 'contact-forms-anti-spam' ),
            'label'      => 0,
            'attr' => false,
            'get_options_list' => '',
            'value_type'=>'normal',
            'wp_data' => 'option'
        );
        add_settings_field(
            'remove_pro_option',
            __('Do you want to Disactivate the API/Pro options?', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_pro_settings_page',
            'settings_page_pro_section',
            $args
        );
        unset($args);
*/

// $popular_spam
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'popular_spam',
          'name'      => 'popular_spam',
          'description'      => __('Popular spam words from <a target="_blank" href="https://wpmaspik.com/public-api/">Maspik public API</a><br>If Maspik pro available.', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
		add_settings_field(
			'popular_spam',
			__('Automatically adding spam phrases from the MASPIK API (BETA)', 'contact-forms-anti-spam' ),
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
          'description'      => __('After you create an API file, you will see an ID number on the API page.<br>For example: 62 (Only one ID)', 'contact-forms-anti-spam' ),
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
            'description'      => __('If woocommerce is active and Maspik pro available.', 'contact-forms-anti-spam' ),
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
            'description'      => __('If Woocommerce is active and Maspik pro available.', 'contact-forms-anti-spam' ),
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
            'description'      => __('If Wpforms is active and Maspik pro available.', 'contact-forms-anti-spam' ),
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
    //maspik_support_formidabley_forms
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_formidable_forms',
            'name'      => 'maspik_support_formidable_forms',
            'description'      => __('If Formidable is active.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'formidable',
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
            'maspik_support_formidable_forms',
            __('Support Formidable', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
    //maspik_support_forminator_forms
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_forminator_forms',
            'name'      => 'maspik_support_forminator_forms',
            'description'      => __('If Forminator is active.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'forminator',
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
            'maspik_support_forminator_forms',
            __('Support Forminator', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
    //maspik_support_forminator_forms
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_fluentforms_forms',
            'name'      => 'maspik_support_fluentforms_forms',
            'description'      => __('If Fluentforms is active.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'fluentforms',
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
            'maspik_support_fluentforms_forms',
            __('Support Fluentforms', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
            $args
        );
        unset($args);
    //maspik_support_forminator_forms
        $args = array (
            'type'      => 'input',
            'subtype'   => 'radio',
            'id'    => 'maspik_support_bricks_forms',
            'name'      => 'maspik_support_bricks_forms',
            'description'      => __('If theme is Bricks.', 'contact-forms-anti-spam' ),
            'label'      => 0,
          	'depends' => 'bricks',
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
            'maspik_support_bricks_forms',
            __('Support Bricks forms', 'contact-forms-anti-spam' ),
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
            'description'      => __('If Gravity_forms is active and Maspik pro available.', 'contact-forms-anti-spam' ),
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
 //add_country_to_emails    
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'add_country_to_emails',
          'name'      => 'add_country_to_emails',
          'description'=>__('To identify countries that send spam, and block them', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'api'=>'add_country_to_emails',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
          add_settings_field(
              'add_country_to_emails',
              __('Add country name to the bottom of email content.', 'contact-forms-anti-spam' ),
              array( $this, 'settings_page_render_settings_field' ),
              'settings_page_option_settings_page',
              'settings_page_option_section',
              $args
          );  
unset($args);       
//Disable comments 
		$args = array (
          'type'      => 'input',
          'subtype'   => 'checkbox',
          'id'    => 'disable_comments',
          'name'      => 'disable_comments',
          'description'=> __('Disable comments on *ALL* types of posts and remove/hide any existing comments from displaying, as well as hiding the comment forms.<br>
If you check this box comments will be disable.', 'contact-forms-anti-spam' ),
          'label'      => 0,          
          'attr' => false,
          'api'=>'disable_comments',
          'get_options_list' => '',
          'value_type'=>'normal',
          'wp_data' => 'option'
		);
          add_settings_field(
              'disable_comments',
              __('Completely disable comments in wordPress.', 'contact-forms-anti-spam' ),
            array( $this, 'settings_page_render_settings_field' ),
            'settings_page_option_settings_page',
            'settings_page_option_section',
              $args
          );  

 unset($args); 
 //shere_data 
      $args = array (
        'type'      => 'input',
        'subtype'   => 'checkbox',
        'id'    => 'shere_data',
        'name'      => 'shere_data',
        'description'=> __('By allowing us to track usage data, we can better help you by knowing which WordPress configurations, themes, and plugins to test and which options are needed.', 'contact-forms-anti-spam' ),
        'label'      => 0,          
        'attr' => false,
        'api'=>'shere_data',
        'get_options_list' => '',
        'value_type'=>'normal',
        'wp_data' => 'option'
      );
      add_settings_field(
        'shere_data',
        __('Allow usage tracking', 'contact-forms-anti-spam' ),
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

        $general_settings_array = array (
            'abuseipdb_api',
            'abuseipdb_score',
            'proxycheck_io_api',
            'proxycheck_io_risk',
            'text_blacklist',
            'MaxCharactersInTextField',
            'emails_blacklist',
            'textarea_blacklist',
            'contain_links',
            'lang_needed',
            'lang_forbidden',
            'ip_blacklist',
            'AllowedOrBlockCountries',
            'country_blacklist',
            'NeedPageurl',
            'error_message',
            //'custom_error_message',
            //'custom_error_message_MaxCharactersInTextField',
            //'custom_error_message_lang_needed',
            //'custom_error_message_lang_forbidden',
            //'custom_error_message_tel_formats',
            //'custom_error_message_country_blacklist',
            //'custom_error_message_contain_links',
            'tel_formats'
        );

        $option_settings_array = array (
            'maspik_support_Elementor_forms',
            'maspik_support_wp_comment',
            'maspik_support_woocommerce_review',
            'maspik_support_registration',
            'maspik_support_Woocommerce_registration',
            'maspik_support_cf7',
            'maspik_support_Wpforms',
            'maspik_support_formidable_forms',
            'maspik_support_forminator_forms',
            'maspik_support_fluentforms_forms',
            'maspik_support_bricks_forms',
            'maspik_support_gravity_forms',
            'maspik_Store_log',
            'add_country_to_emails',
            'disable_comments',
            'shere_data'
        );

        $pro_settings_array = array (
            'popular_spam',
            'private_file_id',
            'public_file'
        );

        $pro_settings_array = array (
            'popular_spam',
            'private_file_id',
            'public_file'
        );

        foreach ($general_settings_array as $setting) {
            register_setting('settings_page_general_settings_page', $setting, array( 'sanitize_callback' => array( $this, 'maspik_settings_sanitize_callback' ) ) );
        }

        foreach ($option_settings_array as $setting) {
            register_setting('settings_page_option_settings_page', $setting, array( 'sanitize_callback' => array( $this, 'maspik_settings_sanitize_callback' ) ) );
        }

        foreach ($pro_settings_array as $setting) {
            register_setting('settings_page_pro_settings_page', $setting, array( 'sanitize_callback' => array( $this, 'maspik_settings_sanitize_callback' ) ) );
        }

        foreach ($this->custom_error_message_by_fields as $setting) {
            register_setting('settings_page_general_settings_page', "custom_error_message_$setting", array( 'sanitize_callback' => array( $this, 'maspik_settings_sanitize_callback' ) ) );
        }
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
        $description = (isset($args['description'])) ? $args['description'] : '';
        $placeholder = (isset($args['placeholder'])) ? 'placeholder="'.$args['placeholder'].'"' : '';
        $value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
        $attr = isset($args['attr']) ? $args['attr'] : "";
        $default = (isset($args['default'])) ? $args['default'] : '';
        $value = $value ? $value : $default;
        $is_depends = isset($args['depends']) && !efas_if_plugin_is_active($args['depends']) ? "disabled" : false;
        $api = isset($args['api']) ? efas_get_spam_api( $args['api'] ) : false;
        $not_print = isset($args['print']) && $args['print'] === "no" ? true : false;
        $need_pro = (isset($args['pro']) && $args['pro'] && !cfes_is_supporting()) ? 1 : 0;
        $article = "<a style='color: #43a18c;' href='https://wpmaspik.com/announcement-changes-to-maspik-plugin/?in-plugin-info' target='_blank'>Read the Maspik changes post</a>";
        echo $need_pro ? "<p class='only-pro' style='color: #43a18c;'>Maspik Pro needed for this option - $article </p>" : "";
        $have_example = (isset($args['example'])) ? $args['example'] : false;
        $subject = (isset($args['subject'])) ? $args['subject'] : false;
        $id = $args['id'];
        $custom_error_id = "custom_error_message_$id";
        
        if( isset($args['title']) ){
            echo "<h4>".$args['title']."</h4>" ;
        }
        $have_custom_error_message = in_array($id, $this->custom_error_message_by_fields);

        switch ($args['type']) {
            case 'input':
                if($not_print) {break;}
                if($args['subtype'] != 'checkbox' && $args['subtype'] != 'select' && $args['subtype'] != 'radio'){
                    $step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
                    $min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
                    $max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
                    if(isset($args['disabled'])){
                        // hide the actual input bc if it was just a disabled input the info saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$attr.' '.$placeholder.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />';
                    } else {
                        echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" '.$attr.' '.$step.' '.$max.' '.$placeholder.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />';
                    }

                } elseif ($args['subtype'] == 'select') {
                    $label = (isset($args['label'])) ? $args['label'] : "";
                    $curr_val = $need_pro ? "" : $value;
                    $num = 1;
                    $the_array = $args['array'];  
                    $disabled = $need_pro ?  "disabled='disabled'" : "";

                    if ($label != '') echo '  <label for="'.$id.'">'.$label.'</label>';
                    echo '<select class="js-states form-control select2" multiple="multiple" '.$disabled.' name="'.$id.'[]" id="'.$id.'"  >';
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
                } elseif ($args['subtype'] == 'radio') {
                    $label = (isset($args['label']) && $args['label'] != 0 )? $args['label'] : "";
                    $curr_val = $need_pro ? "" : $value;
                    $num = 1;
                    $the_array = $args['array'];  
                    $disabled = $need_pro ?  "disabled='disabled'" : "";

                    if ($label != '') echo '  <label for="'.$id.'">'.$label.'</label>';
                    foreach ($the_array as $key => $value) {
                        echo ' <input type="radio" '.$is_depends.' '.$disabled.' name="'.$id.'" value="'.$key.'"';
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
                if($not_print) {break;}
                $placeholder = (isset($args['placeholder'])) ? $args['placeholder'] : '';
                $has_value = ($value) ? $value : $placeholder;
                echo '<textarea '.$attr.' id="'.$args['id'].'" name="'.$args['name'].'" rows="5" cols="80" style="direction: ltr;">' . esc_attr($has_value) . ' </textarea>';

            default:
                # code...
                break;
        }
        //echo "</div></div>";
        if( ($have_example || $have_custom_error_message) /*&& !$not_print*/){
            echo "<div class='btns'>";
            if($have_example){
                
                echo "<a class='your-button-class' data-array='$have_example' data-title='$subject' href='#' data-popup-id='popup-id'><span class='dashicons dashicons-visibility'></span> See examples</a>";
            }
            if($have_custom_error_message){
                $custom_error_message_value =  esc_html( get_option("$custom_error_id") );
                    echo "<a class='custom-validation-trigger'><span class='dashicons dashicons-edit'></span>  Create custom Validation error message for this option</a>";
                echo "</div>";// end .btns and make new div for custom-validation-box
                echo "<div class='custom-validation-box'>";
                    echo '<h4>Custom Validation error for for this option</h4>';
                    echo '<input  type="text" id="'.$custom_error_id.'" name="'.$custom_error_id.'" size="40" value=" '.$custom_error_message_value.' " />';
            }
            echo "</div>"; // end .btns OR validation-box 
        } //  
        
        // start api grey box
        if ($api && !$not_print) { 
            echo "<div class='api'><small style='display: block;padding: 3px 0;'>".__('Options already added automatically from the API', 'contact-forms-anti-spam' )."</small><div>";
            if (!is_array($api)) {
                $api =  explode( "\n", str_replace("\r", "", $api) );
            }
            if (is_array($api)) { 
                foreach ($api as $line){
                    echo "<pre class='array'>$line</pre>";
                }
            } else { // else is_array $api
                echo "<pre class='not-array'>$api</pre>";
            }
             echo "</div>";
            echo "</div>";
        }

        echo "<small style='display: block;'>$description</small>";
    } // render_settings_field

}