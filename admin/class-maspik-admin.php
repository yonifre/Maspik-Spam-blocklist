<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/* The admin-specific functionality of the plugin.
 *
 *
 */


    function maspik_auto_update_db(){
                
        $min_php_version = '7.0';
        $current_php_version = phpversion();

        if (version_compare($current_php_version, $min_php_version, '>=') && !maspik_table_exists()) { 

            create_maspik_log_table();
            create_maspik_table("auto");
            echo maspik_run_transfer();
            
            add_action('admin_footer', function() {
                ?>
                <script type="text/javascript">
                    // Refresh the page
                    window.location.reload(true);
                </script>
                <?php
            });

    
        }

    }

    add_action('admin_init', 'maspik_auto_update_db');

     //Check for PRO -addclass- 
        function maspik_add_pro_class(){
            if(cfes_is_supporting()){
                return "maspik-pro";
            }
            else{
                return "maspik-not-pro";
            }
        }
    //Check for PRO - END

    //Small buttons

        function maspik_tooltip($message){
            echo "<div class='maspik-tooltip'>
            <span class='dashicons dashicons-info'></span>
            <span class='maspik-tooltiptxt'>". $message ."</span></div>";
        }

        function maspik_popup($data, $subject, $label , $icon){

            if($icon == 'visibility'){
                $popuptype = "example";
            }
            else{
                $popuptype = "shortcode";
            }

            echo "<div class='maspik-small-btn btns'>
                <a class='your-button-class' 
                data-array='". esc_attr($data) . "' 
                data-title ='" .esc_attr($subject) . "' 
                href='#' 
                data-popup-id='pop-up-".  esc_attr($popuptype) ."'>
                <span class='dashicons dashicons-". esc_attr($icon) ."'></span>". 
                esc_html($label) .
                "</a> </div>";
        }

        function maspik_get_pro(){    

            echo "<div class='maspik-small-btn btns get-pro'>
                <a class='maspik-get-pro-a' href='https://wpmaspik.com/?ref=inpluginad' target='_blank'><span class='dashicons dashicons-star-empty'></span> Get Maspik PRO</a> </div>";
        }

        function maspik_activate_license(){    

            echo "<div class='maspik-small-btn btns get-pro activate-license'>
                <a class='maspik-get-pro-a' href='". get_site_url() ."/wp-admin/admin.php?page=maspik_activator' target='self'><span class='dashicons dashicons-admin-network'></span> Activate License</a> </div>";
        }


    //Small buttons - END

    // Generate Elements

        function maspik_simple_dropdown($name, $class , $array, $attr = ""){
            $dbresult = maspik_get_settings($name);
                
            $dropdown= "  <select name=". esc_attr($name) ." class=". esc_attr($class) ."  $attr >";
                foreach($array as $entries => $value){
                    $dropdown .="<option value='". esc_attr($value) . "'";
                    if(  $dbresult == $value){
                        $dropdown .= " selected='select'";
                    }
                    $dropdown .= ">". esc_html($entries) ."</option>";   
                }

            
            $dropdown .= "</select>";

            return $dropdown;

        }

function maspik_toggle_button($name, $id, $dbrow_name, $class, $type = "", $manual_switch = "", $api_array = false){
    toggle_ready_check($dbrow_name); //make db row if there's none yet

    if($type == "form-toggle"){
        $checked = maspik_get_settings($dbrow_name, 'form-toggle') == 1 ? 'checked': "";
    }
    elseif($type == "yes-no"){
        $checked = maspik_get_settings($dbrow_name) == 'yes' ? 'checked': "";

    } elseif($type == "other_options"){
        $checked = maspik_get_settings($dbrow_name, '', 'old') == 'yes' ? 'checked': "";
    } else {
        $checked = maspik_get_settings($dbrow_name, 'toggle');
        $checked = maspik_is_contain_api($api_array) ? 'checked' : $checked ;
    }

    if($manual_switch === 0){
        $checked = "";
    } elseif($manual_switch === 1 && maspik_get_settings($dbrow_name) == ""){
        $checked = "checked";
    }

    $toggle= " <label class='maspik-toggle' >
                <input type='checkbox' id=". esc_attr($id) ." name='". esc_attr($name) . "' " . esc_attr($checked) . " class='". esc_attr($class) ."'> 
                <span class='maspik-toggle-slider'></span>
                </label>";
    $apitext = __('Active from Dashboard', 'contact-forms-anti-spam');
    $toggle .= maspik_is_contain_api($api_array) ? "<span class='limit-api-value'>$apitext</span>" : "";
    return $toggle;
}


        function maspik_save_button_show($label = "Save", $add_class = "", $name = "maspik-save-btn" ){

            echo "<div class='submit'><input type='submit' name='". $name."' value='". esc_attr($label) ."' id='submit' class='". esc_attr($add_class) ."'></div>";

        } 

        function create_maspik_textarea($name, $rows = 4, $cols = 50, $class = '', $pholder = "") { 

        
            if($pholder == "error-message"){
                $txtplaceholder = maspik_get_settings( "error_message" ) ? maspik_get_settings( "error_message" ) : __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
            } else{
                $txtplaceholder = $pholder;
            }
            
            $data = maspik_get_settings($name);

            $class_attr = !empty($class) ? ' class="' . esc_attr($class) . '"' : '';
            $textarea = '<textarea name="' . esc_attr($name) . '" rows="' . esc_attr($rows) . '" cols="' . esc_attr($cols) . '"' . $class_attr . '"';
            if($txtplaceholder!= ""){
                $textarea .= ' placeholder="' . $txtplaceholder . '"';
            }
            $textarea .= '>' . esc_html($data) . '</textarea>';

            


            return $textarea;
        }

        function create_maspik_input($name, $class = '', $mode = "text") {      
            
            $data = maspik_get_settings($name);

            $class_attr = !empty($class) ? ' class="' . esc_attr($class . " is-". $mode) . '"' : '';
            $input = "<input  name='" . esc_attr($name) . "' id='". esc_attr($name) . " '" . $class_attr . " type='" . $mode . "' value='". esc_attr($data) ."'></input>";


            return $input;
        }

        function create_maspik_numbox($id, $name, $class, $label, $default = '', $min = 2, $max = 10000) {      
            
            $data = maspik_get_settings($name);
            if(is_array(efas_get_spam_api($name))){
                $api_value = efas_get_spam_api($name)[0];
            }else{
                $api_value = efas_get_spam_api($name);
            }

           

            $class_attr = !empty($class) ? ' class="' . esc_attr($class) . '"' : '';

            $numbox = "";
            $numbox .= "<div class='maspik-numbox-wrap'><label for=". esc_attr($id) .">". esc_html($label) .":</label>
            <input type='number' id=". esc_attr($id) ." name=". esc_attr($name) ." ". $class_attr ." min='". $min ." ' max='" . $max . "' step='1' value='";


            if($data != ''){
                $numbox .=  esc_attr($data);

            }else{
                    $numbox .= esc_attr($default);

            }

            $numbox .= "'>";
            
             if($api_value){
                $numbox .= " <div class='limit-api-wrap'><span class='limit-api-label'>API: </span><span class='limit-api-value'>" . $api_value . "</span></div>";
            }

            
            
            $numbox.= "</div>";
                    
            return $numbox;
        }

        function create_maspik_select($name, $class, $array, $attr="") {      
            
            $the_array = $array;
            $setting_value = maspik_get_dbvalue();
            
            $results = $data = maspik_get_settings($name, 'select');
            $class_attr = !empty($class) ? ' class="js-states form-control maspik-select ' . esc_attr($class) . '"' : '';
            foreach ($results as $result){
                $result_array = explode(" ", $result -> $setting_value);
            }

            

                $select =  '<select '. $class_attr .' multiple="multiple" '.$attr.' name="'.esc_attr($name).'[]" id="'.esc_attr($name).'"  >';
                foreach ($the_array as $key => $value) {
                    $select .=  ' <option value="'.esc_attr($key).'" ';
                    foreach ($result_array as $aresult) {
                        if ($key == preg_replace('/\s+/', '', $aresult)) {
                            $select .=  ' selected="selected"';
                        }
   
                    }
                    $select .= '>'. esc_html($value) .'</option>';
                }

                $select .= "</select>";
                       
            return $select;
            
        }

         
    // Generate Elements - END ---

    //Check if DB has toggle rows, if none, make them
        function toggle_ready_check($name){
            global $wpdb;
                
            $table = maspik_get_dbtable();
            $setting_label = maspik_get_dblabel();
            $setting_value = maspik_get_dbvalue();

            // Check DB if data exists
            $toggle_lim_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE $setting_label = %s", $name ) );

            if ( $toggle_lim_exists == 0 ) {
                // If the row doesn't exist, insert a new row
                $wpdb->insert(
                    $table,
                    array(
                        $setting_label => $name, 
                        $setting_value => 0,
                    )
                );
        
            }


        }
    //Check if DB has toggle rows, if none, make them - END --

    //Maspik API
        function maspik_spam_api_list($name, $array = ""){
            $api = efas_get_spam_api($name);
 
            $apitext = '';

            if ($api) { 
                echo "<div class='maspik-form-api-list'><h5 class='maspik-api-title'>From Maspik Dashboard & Auto-populate</h5>";
                if (!is_array($api)) {
                    $api =  explode( "\n", str_replace("\r", "", $api) );
                }
                if (is_array($api)) { 
                    foreach ($api as $line){
                        if( is_array($array) ){
                           
                            foreach ($array as $key => $value) {
                               if ($key == preg_replace('/\s+/', '', $line)) {
                                    $apitext .='<span class="api-entry">' . esc_html($value) . '</span> ';
                                }
                            }
                        } else $apitext .= esc_html($line) . '<br>';
                    }
                } else { // else is_array $api
                    $apitext = $api;
                }

                echo "<div class='maspik-api-text-wrap'><div class='maspik-api-text ";
                if( !is_array($array) ) echo "maspik-custom-scroll";
                echo "'>" . $apitext . "</div></div></div>";
            }
        }
    //Maspik API - END

    //Maspik API status checker
        function check_maspik_api_values(){
            if(
                efas_get_spam_api("text_field") ||
                efas_get_spam_api("email_field") ||
                efas_get_spam_api("textarea_field") 
            ){
                return true;

            }
        }
    //Maspik API status checker - END

    
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
            'MaxCharactersInTextAreaField',
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
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 800 800"><path fill="#a7abae" d="M659.46,2.52H140.54C65.13,2.52,4,63.65,4,139.06v521.88c0,75.41,61.13,136.54,136.54,136.54h518.92c75.41,0,136.54-61.13,136.54-136.54V139.06c0-75.41-61.13-136.54-136.54-136.54ZM524.27,653.8l-10.19-231.45-4.27-92.38c-1.54,17.75-3.29,34.68-5.26,50.79-1.97,16.11-4.39,31.72-7.23,46.85l-43.07,226.19h-111.78l-36.82-226.52c-2.19-13.15-5.15-35.72-8.88-67.72-.44-4.82-1.43-14.68-2.96-29.59l-3.29,93.7-12.16,230.13h-145.97l59.18-507.61h170.63l28.6,170.96c2.41,14.03,4.55,29.26,6.41,45.7,1.86,16.44,3.56,34.3,5.1,53.59,2.85-32.22,6.79-61.04,11.84-86.46l34.85-183.78h169.31l49.31,507.61h-143.34Z"/></svg>';
        $base64 = base64_encode($svg);
        $icon_url = 'data:image/svg+xml;base64,' . $base64;

        add_menu_page($this->plugin_name, 'Maspik Spam', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'), $icon_url, 85);

        $numlogspam = maspik_spam_count() ? "(" . maspik_spam_count() . ")" : false;

        add_submenu_page($this->plugin_name, 'Blacklist Option', 'Blacklist Options', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'));

        add_submenu_page($this->plugin_name, 'Spam Log', 'Spam Log ' . $numlogspam, 'edit_pages', $this->plugin_name . '-log.php', array($this, 'displayPluginAdminSettings'));

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
        // Check if $input is not null
        if ($input !== null) {
            $input = str_replace("\n", ",,", $input);
            $sanitize_input = sanitize_text_field($input);
            $sanitize_input = str_replace(",,", "\n", $sanitize_input);
            return $sanitize_input;
        } else {
            return $input;
        }
    }

        
	public function registerAndBuildFields() {
			/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */ 



        //Making Isolated Setting groups for each Accordion segments

       

        //End of Accordion segments

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
			'settings_page_option_section', 
			// Title to be displayed on the administration page
			'Supporting manager',  
			// Callback used to render the description of the section
				array( $this, 'settings_page_display_general_account' ),    
			// Page on which to add this section of options
			'settings_page_option_settings_page'                   
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



    public function settings_page_render_settings_field($args) {  
         
        if($args['wp_data'] == 'option'){
            $wp_data_value = get_option($args['name']);
        } elseif($args['wp_data'] == 'post_meta'){
            $wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
        }
        $description = (isset($args['description'])) ? $args['description'] : '';
        $tooltip = (isset($args['tooltip'])) ? $args['tooltip'] : '';
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
        
        if( ($tooltip) /*&& !$not_print*/){
            echo '<div class="maspik-tooltip"><span class="dashicons dashicons-info maspik-tooltip"></span><span class="maspik-tooltiptxt">'. $tooltip;
            echo "</span></div>"; // end .btns OR validation-box 
        } // 
        
        // Example Button
        if( ($have_example) /*&& !$not_print*/){
            echo "<div class='maspik-small-btn btns'>";
                
            echo "<a class='your-button-class' data-array='$have_example' data-title='$subject' href='#' data-popup-id='popup-id'><span class='dashicons dashicons-visibility'></span> See examples</a>";
            
            echo "</div>"; // end .btns OR validation-box 
        } //  

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

        if( ($have_custom_error_message) ){
            echo "<div class='btns'>";
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

