<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/* The admin-specific functionality of the plugin.
 *
 *
 */

    define('MASPIK_API_KEY', 'KVJS5BDFFYabnZkQ3Svty6z6CIsxp3YG5ny4lrFQ');

    function maspik_auto_update_db(){    
        if ( !maspik_table_exists('text_blacklist') ) { 
            create_maspik_log_table();
            create_maspik_table();
            if( get_option('text_blacklist') ){
                maspik_run_transfer();
            }
            maspik_make_default_values();
        }
    }
    add_action('admin_init', 'maspik_auto_update_db' , 10);

    // run the default values function only once if necessary
    function maspik_check_if_need_to_run_once() {
        $maspik_run_once = get_option( 'maspik_run_once', 0 ); // default to 0 if option doesn't exist
        if ( $maspik_run_once < 2 ) {
            maspik_make_default_values();
            update_option( 'maspik_run_once', $maspik_run_once + 1 ); // update to 2 to prevent reruns
        }
    }
    add_action( 'admin_init', 'maspik_check_if_need_to_run_once' , 20);

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
            <span class='maspik-tooltiptxt'>". esc_html($message) ."</span></div>";
        }

        function maspik_popup($data="", $subject="", $label="", $icon=""){

            
            if($icon == 'visibility'){
                $popuptype = "example";
            }else{
                $popuptype = "shortcode";
            }
            if(!$data){
                $popuptype = "ip-verification";
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
        $checked = maspik_get_settings($dbrow_name, 'form-toggle') == "yes" ? 'checked': "";
    }
    elseif($type == "yes-no"){
        $checked = maspik_get_settings($dbrow_name) == 'yes' ? 'checked': "";

    } elseif($type == "other_options"){
        $checked = maspik_get_settings($dbrow_name, '', 'old') ? 'checked': "";
    } else {
        $checked = maspik_get_settings($dbrow_name, 'toggle');
        $checked = maspik_is_contain_api($api_array) ? 'checked' : $checked ;
    }

    if($manual_switch === 0 ){
        $checked = "";
    } elseif($manual_switch && maspik_get_settings($dbrow_name) == ""){
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
                $textarea .= ' placeholder="' . esc_attr($txtplaceholder) . '"';
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
            <input type='number' id=". esc_attr($id) ." name=". esc_attr($name) ." ". $class_attr ." min='".  esc_attr($min) ."' max='" . esc_attr($max) . "' step='1' value='";


            if($data != ''){
                $numbox .=  esc_attr($data);

            }else{
                    $numbox .= esc_attr($default);

            }

            $numbox .= "'>";
            
             if($api_value){
                $numbox .= " <div class='limit-api-wrap'><span class='limit-api-label'>API: </span><span class='limit-api-value'>" . esc_html($api_value) . "</span></div>";
            }

            
            
            $numbox.= "</div>";
                    
            return $numbox;
        }

        function create_maspik_select($name, $class, $array, $attr="") {      
            
            $the_array = $array;
            $setting_value = maspik_get_dbvalue();
            
            $results = $data = maspik_get_settings($name, 'select');
            $class_attr = !empty($class) ? ' class="js-states form-control maspik-select ' . esc_attr($class) . '"' : '';

            $result_array = array();
            if (is_array($results) || is_object($results)) {
                foreach ($results as $result) {
                    $result_array = explode(" ", $result->$setting_value);
                }
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


	/**
	 * Initialize the class and set its properties.
	 *
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action('admin_menu', array( $this, 'Maspik_addPluginAdminMenu' ), 9);   
		//add_action('admin_init', array( $this, 'registerAndBuildFields' ));
	}

    
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */ 
    public function enqueue_styles() {
        $screen = get_current_screen();
        if ( false !== strpos($screen->id, 'maspik') ) { 
            wp_enqueue_style( "maspik-admin-style", plugin_dir_url(__DIR__) . 'admin/css/admin-style.css', array(), MASPIK_VERSION, 'all' ); 
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
    
    public function Maspik_addPluginAdminMenu() {
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
         
}