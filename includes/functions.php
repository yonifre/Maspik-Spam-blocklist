<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 


/*
* Main function
*/

function maspik_get_field_display_name($field_id) {
    global $MASPIK_FIELD_DISPLAY_NAMES;
    return isset($MASPIK_FIELD_DISPLAY_NAMES[$field_id]) ? $MASPIK_FIELD_DISPLAY_NAMES[$field_id] : $field_id;
}


function maspik_delete_filter() {
    global $wpdb;

    // Add nonce verification
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'maspik_delete_action')) {
        wp_send_json_error('Invalid security token.');
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action.');
        return;
    }

    $row_id = intval($_POST['row_id']);
    $logtable = maspik_get_logtable();

    // Get the value of "option_name" from the row with the given id
    $spam_label = $wpdb->get_var( $wpdb->prepare(
        "SELECT spamsrc_label FROM $logtable WHERE id = %d",
        $row_id
    ));

    $spam_val = $wpdb->get_var( $wpdb->prepare(
        "SELECT spamsrc_val FROM $logtable WHERE id = %d",
        $row_id
    ));

    if ($spam_label) {

        $update_data = array('spam_tag' => 'not spam');
        $where = array('id' => $row_id);

        // Convert textarea_field to textarea_blacklist for consistency
        $spam_label = ($spam_label === "textarea_field") ? "textarea_blacklist" : $spam_label;

        if($spam_label == "text_blacklist" || $spam_label == "textarea_blacklist" || $spam_label == "emails_blacklist" || $spam_label == "ip_blacklist"){
            
            $option_arval = efas_makeArray(maspik_get_settings($spam_label));

            if (is_array($option_arval)) {
                // Initialize an empty array to hold the filtered values
                $filtered_list = array();
                
                // Convert $spam_val to lowercase for case-insensitive comparison
                $spam_val_lower = strtolower($spam_val);
                
                // Iterate through the array and add values that are not equal to $spam_val (case-insensitive) to $filtered_list
                foreach ($option_arval as $val) {
                    $val = strtolower(rtrim($val));

                    if ($val !== $spam_val_lower) {
                        $filtered_list[] = $val;
                    }
                }
            
                // Convert the filtered list to a string with each item separated by a newline
                $filtered_list_string = implode("\n", $filtered_list);
                
                // Save the filtered list as a newline-separated string
                if(maspik_get_settings($spam_label) != $filtered_list_string ){
                    if (maspik_save_settings($spam_label, $filtered_list_string)) {
                        $wpdb->update($logtable, $update_data, $where);
                        wp_send_json_success(array('spam_label' => $spam_label));
                    } else {
                        wp_send_json_error('Failed to save settings.');
                    }
                }else{
                    wp_send_json_error('Failed to save settings.');
                }
            
            } else {

                if(maspik_get_settings($spam_label) != ""){
                    // If it's not an array, save an empty string
                    if (maspik_save_settings($spam_label, "")) {
                        $wpdb->update($logtable, $update_data, $where);
                        wp_send_json_success(array('spam_label' => $spam_label));
                    } else {
                        wp_send_json_error('Failed to save empty settings.');
                    }
                } else { 
                    wp_send_json_error('No settings found.');
                } 
            }

        } else { 
            if(maspik_get_settings($spam_label)!=""){
                // If it's not an array, save an empty string
                if (maspik_save_settings($spam_label, "")) {
                    $wpdb->update($logtable, $update_data, $where);
                    wp_send_json_success(array('spam_label' => $spam_label));
                } else {
                    wp_send_json_error('Failed to save empty settings.');
                }
            } else { 
                wp_send_json_error('No settings found.');
            } 
        } 
    } // Added closing brace for the outer `if ($spam_label)`

} // Closing brace for the function

    add_action('wp_ajax_delete_filter', 'maspik_delete_filter');


    function maspik_delete_row() {
        global $wpdb;
    
        // Add nonce verification
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'maspik_delete_action')) {
            wp_send_json_error('Invalid security token.');
            return;
        }
    
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error('You do not have permission to perform this action.');
            return;
        }
    
        $row_id = intval($_POST['row_id']);
        $table = maspik_get_logtable();

        $spam_tag = $wpdb->get_var( $wpdb->prepare(
            "SELECT spam_tag FROM $table WHERE id = %d",
            $row_id
        ));
    
        // Update the is_deleted column instead of deleting the row
        $update_data = array('spam_tag' => 'spam');
        $where = array('id' => $row_id);

        if($spam_tag == "not spam"){
            $spam_action = $wpdb->delete($table, array('id' => $row_id));
        }else{
            $spam_action = $wpdb->update($table, $update_data, $where);
        }     
    
        if ($spam_action !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to update row.');
        }
    }
    add_action('wp_ajax_delete_row', 'maspik_delete_row');

//Spam log delete functions -- END

function maspik_insert_to_table() {
    static $already_run = false;
    
    // Run only once per request
    if ($already_run) {
        return;
    }
    
    // Check if we already checked the columns today (saved in options)
    $last_check = get_option('maspik_insert_last_check');
    if ($last_check && $last_check > strtotime('-48 hours')) {
        $already_run = true;
        return;
    }

    global $wpdb;
    $table = maspik_get_dbtable();
    $setting_value = maspik_get_dbvalue();
    $setting_label = maspik_get_dblabel();

    // Rows to be inserted if they don't exist
    $rows = [
        ['MaspikHoneypot', 1], // Honeypot ver 2.1.2
        ['maspik_support_jetforms', 'yes'], // jetforms ver 2.1.2
        ['maspik_support_everestforms', 'yes'], // everestforms ver 2.1.2
        ['maspikDbCheck', 1], // maspikDbCheck ver 2.1.6
        ['maspik_support_buddypress_forms', 'yes'] // buddypress ver 2.2.7
    ];

    // Check if the rows already exist
    $existing_rows = $wpdb->get_col("
        SELECT $setting_label 
        FROM $table 
        WHERE $setting_label IN ('" . implode("','", array_column($rows, 0)) . "')
    ");

    // Prepare rows to add
    $rows_to_insert = [];
    foreach ($rows as [$name, $value]) {
        if (!in_array($name, $existing_rows)) {
            $rows_to_insert[] = $wpdb->prepare(
                "(%s, %s)",
                $name,
                $value
            );
        }
    }

    // Insert all new rows in one query
    if (!empty($rows_to_insert)) {
        $wpdb->query("
            INSERT INTO $table ($setting_label, $setting_value) 
            VALUES " . implode(',', $rows_to_insert)
        );
    }

    // Update the last check time
    update_option('maspik_insert_last_check', time());
    $already_run = true;
}

// Hook the function on load
// this function is not needed anymore, TODO next: remove it from the code completely
//add_action('init', 'maspik_insert_to_table');




//check if table exists

    function maspik_table_exists($rowtocheck = false) {
        static $table_exists = null;     
        static $row_exists = array();    
        
        // First time $table_exists will be null
        if ($table_exists === null) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'maspik_options';
            // Check and save the result
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        }
        // In the next times, use the saved value
        
        if (!$table_exists) {
            return false;
        }
        
        if ($rowtocheck == 'text_blacklist') {
            // Check if we already checked this row
            if (!isset($row_exists['text_blacklist'])) {
                global $wpdb; 
                $table_name = $wpdb->prefix . 'maspik_options'; 
                // If we didn't check this row, check and save the result
                $row_exists['text_blacklist'] = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE option_name = %s",
                    $rowtocheck
                )) > 0;
            }
            // In the next times, use the saved value
            return $row_exists['text_blacklist'];
        }
        
        return true;
    }

    function maspik_logtable_exists() {
        static $table_exists = null;
        
        if ($table_exists === null) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'maspik_spam_logs';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
        }
        
        return $table_exists;
    }

//check if table exists - END

// Save to DB Function 
    function maspik_save_settings($col_name, $new_value) {
        // check if the values are valid
        if (empty($col_name) || $col_name === '0' || $col_name === 0) {
            return ;
        }

        global $wpdb;
        $table = maspik_get_dbtable();
        $setting_value = maspik_get_dbvalue();
        $setting_label = maspik_get_dblabel();

            // sanitize the values
        $col_name = sanitize_text_field($col_name);
        $new_value = is_numeric($new_value) ? intval($new_value) : wp_strip_all_tags($new_value);

        // check if the row exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE $setting_label = %s",
            $col_name
        ));

        if ($exists) {
            $result = $wpdb->update(
                $table,
                array($setting_value => $new_value),
                array($setting_label => $col_name),
                array('%s'), // always use %s because the value has already been sanitized
                array('%s')
            );
        } else {
            $result = $wpdb->insert(
                $table,
                array(
                    $setting_label => $col_name,
                    $setting_value => $new_value
                ),
                array('%s', '%s')
            );
        }

        return ($result !== false) ? "success" : $wpdb->last_error;
    }
// Save to DB Function - END

//Set DB table variables

    function maspik_get_logtable(){
        global $wpdb;
        
        $table = $wpdb->prefix . 'maspik_spam_logs';
        return $table;
    }

    function maspik_get_dbtable() {
        global $wpdb;
        $table = $wpdb->prefix . 'maspik_options';
        
        // if the table doesn't exist, create it
        if (!maspik_table_exists()) {
            create_maspik_table();
        }
        
        return $table;
    }

    function maspik_get_dbvalue(){
        $setting_value = 'option_value'; //variable for row where values are

        return $setting_value;
    }

    function maspik_get_dblabel(){
        $setting_label = 'option_name'; //variable for column name for setting label

        return $setting_label;
    }
//Set DB table variables - END

//Get data from DB

function maspik_get_settings($data_name, $type = '', $table_var = 'new'){
    if (!maspik_table_exists()) {
        return '';
    }

    global $wpdb;
    if($table_var == 'old'){
        $table = $wpdb->prefix . 'options'; // old table
        $setting_label = 'option_name';
        $setting_value = 'option_value';
    } else {
        $table = maspik_get_dbtable();
        $setting_label = maspik_get_dblabel();
        $setting_value = maspik_get_dbvalue();
    }

    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE $setting_label = %s", $data_name)
    );

    // Check if there are any results
    if ($results) {
        $data = ''; // clean variable
        if($type == "toggle"){// data for toggles
            foreach ($results as $result) {
                $data = $result->$setting_value  == 1 ? 'checked' : '';
            }
        } elseif($type == "form-toggle"){// data for toggles
            foreach ($results as $result) {
                if (!$result->$setting_value){
                    $data = 1;
                } else {
                    $data = $result->$setting_value  == 'yes' ? 'yes' : 'no';
                }
            }
        } elseif($type == "select"){// just return raw for select
            $data = $results;
        } else {// for everything else
            foreach ($results as $result) {
                $data .= $result->$setting_value; 
            }
        }
    } else { 
        $data = null; 
    }
    
    return $data;
}
//Get data from DB - END

// New table management functions

//check if data is in the new table
function maspik_check_table($value) {
    global $wpdb;
    $table_name =$wpdb->prefix . 'maspik_options';

    $column_name = maspik_get_dblabel(); 
    $specific_data = $value;

    $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE $column_name = %s", $specific_data);
    $count = $wpdb->get_var($query);

    if($count == 0) {
        return false;
    }else{
        return true;
    }
}

//make new main table
function create_maspik_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'maspik_options';
    
    // check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        return; // the table already exists, no need to create it
    }
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        option_name varchar(191) NOT NULL,
        option_value longtext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);        
}

//make new log table
function create_maspik_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'maspik_spam_logs';
    
    // define the structure of the table
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        spam_type varchar(191) NOT NULL,
        spam_value varchar(191) NOT NULL,
        spam_detail longtext NOT NULL,
        spam_ip varchar(191) NOT NULL,
        spam_country varchar(191) NOT NULL,
        spam_agent varchar(191) NOT NULL,
        spam_date varchar(191) NOT NULL,
        spam_source varchar(191) NOT NULL,
        spamsrc_label varchar(191) NOT NULL,
        spamsrc_val varchar(191) NOT NULL,
        spam_tag varchar(191) NOT NULL,
        PRIMARY KEY  (id)
    ) " . $wpdb->get_charset_collate();

    // if the table doesn't exist or if we need to update the structure
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // mark the function as run successfully
    update_option('maspik_columns_last_check', '2');
}



function maspik_limit_log_size() {
    global $wpdb;

    $max_logs = maspik_get_settings('spam_log_limit') ? maspik_get_settings('spam_log_limit') : 1000;


    $table = maspik_get_logtable();

    // Count the current number of records
    $current_count = $wpdb->get_var("SELECT COUNT(*) FROM $table");

    if ($current_count > $max_logs) {
        // Calculate the number of records to delete
        $entries_to_delete = $current_count - $max_logs;

        // Delete the oldest records
        $wpdb->query("
            DELETE FROM $table
            ORDER BY id ASC
            LIMIT $entries_to_delete
        ");
    }
}

//Save Error logs to table
function efas_add_to_log($type = '', $input = '', $post = null, $source = "Elementor forms", $spamsrc_name = "", $spamsrc_val = "") {
    $spamcounter = get_option('spamcounter', 0);
    $spamcounter++;
    update_option('spamcounter', $spamcounter);
    
    // Sanitize and escape user inputs
    if (maspik_get_settings("maspik_Store_log") == 'yes') {
        // Check if the post is an array
        if (is_array($post)) {
            // Loop through the post array
            foreach ($post as $key => $value) {
                // Check if the key contains the word password or pass
                if (stripos($key, 'password') !== false || stripos($key, 'pass') !== false) {
                    $post[$key] = '********'; // Replace the password with asterisks
                }
            }
        }
        
        $serialize_data = is_array($post) ? serialize(array_map('sanitize_text_field', $post)) : '';

        $ip = maspik_get_real_ip();
        $countryName = "Other (Unknown)";
        
        $response = wp_remote_get("http://www.geoplugin.net/json.gp?ip=" . $ip );
        if ( !is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200 ) {
            $body = wp_remote_retrieve_body($response);
            $geoData = json_decode($body, true);
            if ( isset($geoData['geoplugin_countryName']) && !empty($geoData['geoplugin_countryName']) ) {
                $countryName = sanitize_text_field($geoData['geoplugin_countryName']);
            }
        }
        
        $spamsrc_val = substr(wp_slash(sanitize_textarea_field($spamsrc_val)), 0, 190);
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $browser_name = maspik_get_browser_name($user_agent);
        $date = current_time('mysql'); // timestamp
        $result = maspik_save_log(
            substr(wp_slash(sanitize_text_field($type)), 0, 190),
            substr(wp_slash(sanitize_text_field($input)), 0, 190),
            $serialize_data,
            sanitize_text_field($ip),
            sanitize_text_field($countryName),
            sanitize_text_field($browser_name),
            sanitize_text_field($date),
            substr(wp_slash(sanitize_text_field($source)), 0, 190),
            substr(wp_slash(sanitize_text_field($spamsrc_name)), 0, 190),
            substr(wp_slash(sanitize_text_field($spamsrc_val)), 0, 190)
        );
        
        if ($result !== "success") {
            // Handle the error
            //error_log("Failed to save spam log: " . $result);
        }

    }
}

// Save Error logs to table
function maspik_save_log($type, $value, $detail, $ip, $country, $agent, $date, $source, $spamsrc_name, $spamsrc_val) {
    global $wpdb;
    global $wp;

    if (maspik_logtable_exists()) {

        $table = maspik_get_logtable();
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        $spam_source = $url ? "$source|||$url" : $source;

        $data = array(
            'spam_type'    => $type,
            'spam_value'   => $value,
            'spam_detail'  => $detail,
            'spam_ip'      => $ip,
            'spam_country' => $country,
            'spam_agent'   => $agent,
            'spam_date'    => $date,
            'spam_source'  => $spam_source,
            'spamsrc_label' => $spamsrc_name, 
            'spamsrc_val'  => $spamsrc_val, 
        );

        // Insert data into the database
        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'); // Format for each field

        $result = $wpdb->insert($table, $data, $format);
   
        if ($result) {
            maspik_limit_log_size();
            return "success";
        } else {
            return $wpdb->last_error;
        }
    }

    return "Table does not exist";
}
//Output current spam count
    function maspik_spam_count(){
        global $wpdb;

        if(maspik_logtable_exists()){

            $table = maspik_get_logtable();

            $sql = "SELECT COUNT(*) AS total FROM $table";
            $result = $wpdb->get_var($sql);
            
            
            return $result;
        }

    }

//Output spam count since install
    function maspik_spam_log_total(){
        global $wpdb;

        if(maspik_logtable_exists()){
            $table = maspik_get_logtable();

            $sql = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
            $last_id = $wpdb->get_var($sql);
            
            
            return $last_id;
        }
    }
//Save Error logs to table - END


function maspik_Download_log_btn(){
        ?><form method="post" class="downloadform" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="Maspik_spamlog_download_csv">
        <input type="submit" value="Download CSV" class="maspik-btn">
    </form><?php
}


function maspik_get_real_ip() {
    $headers = [
        'CF-Connecting-IP', // Cloudflare (most accurate for Cloudflare)
        'HTTP_CF_CONNECTING_IP', // Cloudflare (less popular)
        'HTTP_X_REAL_IP', // Nginx 
        'HTTP_X_FORWARDED_FOR',  // Proxy forwarding
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR' // Default
    ];

    foreach ($headers as $key) {
        if (!empty($_SERVER[$key])) {
            $ip_list = explode(',', $_SERVER[$key]);
            foreach ($ip_list as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
    }

    return '127.0.0.1';
}


// Make Array
function efas_makeArray($string,$type="") {
    if (!$string || is_array($string)) {
        return is_array($string) ? $string : [];
    }
    if ($type = "select") {
        $array  = explode("\n", str_replace("\r", "", $string));
        return  array_filter($array); //removes all null values
    }

    $string = strtolower($string);
    return explode("\n", str_replace("\r", "", $string));
}

// Check if field value exists in string
function maspik_is_field_value_exist_in_string($bad_string, $field_value, $make_space = 1) {
    // Return false if either string is empty
    if (!$bad_string || !$field_value) {
        return false;
    }

    // Convert both strings to lowercase and trim whitespace
    $bad_string_lower = strtolower(trim($bad_string));
    $field_value_lower = strtolower(trim($field_value));
    
    // If make_space is 1, check for word boundaries and optional punctuation
    if ($make_space == 1) {
        $bad_string_lower = preg_quote($bad_string_lower, '/');
        return preg_match("/(?:^|\s)" . $bad_string_lower . "[.,!?]?(?:$|\s)/i", $field_value_lower);
    }
    
    // Otherwise, check if string exists anywhere in the text
    return strpos($field_value_lower, $bad_string_lower) !== false;
}

// Check if field value is equal to string
function maspik_is_field_value_equal_to_string($string, $field_value) {
    if ($string === "" || $field_value === "") {
        return false;
    }
    $string = trim(strtolower($string));
    $field_value = trim(strtolower($field_value));

    return $string === $field_value ? true : false;
}

function efas_get_spam_api($field = "text_field",$type = "array") {
    $spamapi_option = get_option('spamapi'); 
   
    if ((!maspik_get_settings('private_file_id') && !maspik_get_settings("popular_spam") ) || !is_array($spamapi_option) || !cfes_is_supporting("api") || !isset($spamapi_option[$field])) {
        return false;
    }

    $api_field = $spamapi_option[$field];

    //$api_field = "";

    if ($type != "array") {
            // Keep the field value if it's not an array
            $api_field = is_array($spamapi_option[$field]) ? $spamapi_option[$field][0] : $spamapi_option[$field] ;
            $clean = sanitize_text_field($api_field);
    } else {
        // Convert non-array fields to an array using efas_makeArray 
        $api_field = efas_makeArray($spamapi_option[$field],$type);

        //Better to sanitize
        $clean = array_map('sanitize_text_field', $api_field);

        // Remove empty values from Array
        $clean = array_filter($clean, function($value) {
            return !empty($value);
        });

    }

    return $clean ? $clean : false;
}

function maspik_is_contain_api($array) {
    $spamapi_option = get_option('spamapi');
    if ( !is_array($spamapi_option) ||  !cfes_is_supporting("api") || !is_array($array) ) {
        return false;
    }
    // Check if any of the fields in the array are set in the spam API option
    foreach ($array as $field) {
        if (!empty($spamapi_option[$field])) {
            return true; // Found a match, return early
        }
    }
    return false; // No matches found
}

function maspik_detect_language_in_string($langs, $string) {
    if (!is_array($langs) || empty($string)) {
        return '';
    }

    // fix for old versions added in version 2.2.3
    $langs = maybe_unserialize($langs);

    foreach ($langs as $lang) {
        if (preg_match("/$lang/u", $string)) {
            return $lang;
        }
    }
    return '';
}



  
function maspik_is_plugin_active( $plugin ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $plugin );
}

function efas_array_supports_plugin(){
  $info = cfes_is_supporting("plugin") ? "" : "Pro"; 
  return array(
    'Contact form 7' => 0,
    'Elementor pro' => 0,
    'Hello Plus' => 0,
    'Wordpress Comments' => 0,
    'Wordpress Registration' => 0,
    'Formidable' => 0,
    'Forminator' => 0,
    'Fluentforms' => 0,
    'Bricks' => 0,
    'Ninjaforms'=> 0,
    'Jetforms'=> 0,
    'Everestforms'=> 0,
    'Buddypress' => 0,
    'Woocommerce Review' => $info,
    'Woocommerce Registration' => $info,
    'Wpforms' => $info,
    'Gravityforms' => $info,
  );
} 

function maspik_proform_togglecheck($plugin){
    
    foreach ( efas_array_supports_plugin() as $key => $value) {
    
        if($key == $plugin){
            //echo $key;
            if($value == "Pro"){  
                return 0;
            }else{
                return 1;
            } 
        }
    }

    
}

// Check if WooCommerce support is enabled
function maspik_if_woo_support_is_enabled() {
    return cfes_is_supporting("plugin") && class_exists('WooCommerce') && maspik_get_settings("maspik_support_Woocommerce_registration") != "no";
}



function maspik_if_plugin_is_active($plugin) {
    global $MASPIK_PLUGIN_MAP;
    
    if (!isset($MASPIK_PLUGIN_MAP[$plugin])) {
        return 0;
    }
    
    if ($plugin === 'Wordpress Comments') {
        return 1;
    }
    
    return efas_if_plugin_is_active($MASPIK_PLUGIN_MAP[$plugin]);
}

function efas_if_plugin_is_affective($plugin , $status = "no"){

   

	if($plugin == 'Elementor pro'){
      return efas_if_plugin_is_active('elementor-pro') && maspik_get_settings( "maspik_support_Elementor_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Contact form 7'){
      return  efas_if_plugin_is_active('contact-form-7') && maspik_get_settings( "maspik_support_cf7", 'form-toggle' ) != $status ;
    }else if($plugin == 'Hello Plus'){
      return efas_if_plugin_is_active('hello-plus') && maspik_get_settings( "maspik_support_helloplus_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Buddypress'){
      return efas_if_plugin_is_active('buddypress') && maspik_get_settings( "maspik_support_buddypress_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Woocommerce Review'){
      return efas_if_plugin_is_active('woocommerce') && cfes_is_supporting("plugin") && maspik_get_settings( "maspik_support_woocommerce_review", 'form-toggle' ) != $status ;
    }else if($plugin == 'Woocommerce Registration'){
      return efas_if_plugin_is_active('woocommerce') && cfes_is_supporting("plugin") && maspik_get_settings( "maspik_support_Woocommerce_registration", 'form-toggle' ) != $status;
    }else if($plugin == 'Wpforms'){
      return  efas_if_plugin_is_active('wpforms') && cfes_is_supporting("plugin") && maspik_get_settings( "maspik_support_Wpforms", 'form-toggle' ) != $status  ;
    }else if($plugin == 'Gravityforms'){
      return efas_if_plugin_is_active('gravityforms') && cfes_is_supporting("plugin") && maspik_get_settings( "maspik_support_gravity_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Formidable'){
      return efas_if_plugin_is_active('formidable')  && maspik_get_settings( "maspik_support_formidable_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Fluentforms'){
      return efas_if_plugin_is_active('fluentforms')  && maspik_get_settings( "maspik_support_fluentforms_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Bricks'){
      return efas_if_plugin_is_active('bricks')  && maspik_get_settings( "maspik_support_bricks_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Forminator'){
      return efas_if_plugin_is_active('forminator')  && maspik_get_settings( "maspik_support_forminator_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Wordpress Registration'){
      return efas_if_plugin_is_active('Wordpress Registration') && maspik_get_settings( "maspik_support_registration", 'form-toggle' ) != $status ;
    }else if($plugin == 'Ninjaforms'){
        return efas_if_plugin_is_active('ninjaforms') && maspik_get_settings( "maspik_support_ninjaforms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Jetforms'){
        return efas_if_plugin_is_active('jetforms') && maspik_get_settings( "maspik_support_jetforms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Everestforms'){
        return efas_if_plugin_is_active('everestforms') && maspik_get_settings( "maspik_support_everestforms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Wordpress Comments'){
      return maspik_get_settings( "maspik_support_wp_comment", 'form-toggle' ) != $status ;
    }else{
      return 1;
    }
}

function efas_if_plugin_is_active($plugin){
	if($plugin == 'elementor-pro'){
      return class_exists( '\ElementorPro\Plugin' );
    }else if($plugin == 'contact-form-7'){
      return maspik_is_plugin_active( 'contact-form-7/wp-contact-form-7.php' );
    }else if($plugin == 'hello-plus'){
      return maspik_is_plugin_active( 'hello-plus/hello-plus.php' );
    }else if($plugin == 'woocommerce'){
      return maspik_is_plugin_active( 'woocommerce/woocommerce.php');
    }else if($plugin == 'buddypress'){
      return maspik_is_plugin_active( 'buddypress/bp-loader.php');
    }else if($plugin == 'wpforms'){
	  return ( maspik_is_plugin_active('wpforms-lite/wpforms.php') || maspik_is_plugin_active('wpforms/wpforms.php') );
    }else if($plugin == 'gravityforms'){
      return maspik_is_plugin_active('gravityforms/gravityforms.php');
    }else if($plugin == 'forminator'){
      return maspik_is_plugin_active('forminator/forminator.php');
    }else if($plugin == 'formidable'){
      return maspik_is_plugin_active('formidable/formidable.php');
    }else if($plugin == 'bricks'){
      return maspik_if_bricks_exist();
    }else if($plugin == 'fluentforms'){
      return maspik_is_plugin_active('fluentform/fluentform.php');
    }else if($plugin == 'ninjaforms'){
        return maspik_is_plugin_active('ninja-forms/ninja-forms.php');
    }else if($plugin == 'jetforms'){
        return maspik_is_plugin_active('jetformbuilder/jet-form-builder.php');
    }else if($plugin == 'everestforms'){
        return maspik_is_plugin_active('everest-forms/everest-forms.php');
    }else if($plugin == 'Wordpress Registration'){
      return get_option('users_can_register') == 1;
    }else{
      return 1;
    }
}

//Display admin notices 
function contact_forms_anti_spam_plugin_admin_notice(){
    $screen = get_current_screen()->id;
    if ( strpos($screen, 'maspik') !== false ){
        ?><div class="notice notice-warning is-dismissible">
        <p><?php esc_html_e('Use this plugin with caution and only if you understand the risk, blacklisting some words can lead to the termination of valid leads.', 'contact-forms-anti-spam') ?></p>
        </div><?php  
        // Change the footer text
        add_filter('admin_footer_text', 'maspik_change_footer_admin');
        // Add script to footer admin to open external links in new tab
        add_action('admin_footer', function() {
            ?>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var links = document.querySelectorAll('#toplevel_page_maspik li a');
                for (var i = 0; i < links.length; i++) {
                    if (links[i].href.startsWith('https://') && !links[i].href.includes(window.location.hostname)) {
                        links[i].target = '_blank';
                    }
                }
            });
            </script>
            <?php
        });
     }
   
}
add_action( 'admin_notices', 'contact_forms_anti_spam_plugin_admin_notice' );

function maspik_change_footer_admin () {
    echo '<p id="footer-left" class="alignleft">
		<strong>Maspik</strong> is helping you block spam? Please leave us a <a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">★★★★★</a> rating. We really appreciate your support!</p>';
}


function mergePerKey($array1, $array2) {
    $result = array();

    foreach ($array1 as $key => $value) {
        $value = is_array($value) ? $value : efas_makeArray($value);

        if (array_key_exists($key, $array2)) {
            $value2 = efas_makeArray($array2[$key]);
            $mergedValues = array_merge($value, $value2);
            $uniqueValues = array_unique($mergedValues);
            $result[$key] = $uniqueValues;
        }
    }

    return $result;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'cfas_daily_api_refresh' ) ) {
 wp_schedule_event( time(), 'daily', 'cfas_daily_api_refresh' );
}
// Hook into that action that'll fire daily
add_action( 'cfas_daily_api_refresh', 'cfas_refresh_api' );


function cfes_is_supporting($type = "") {

	if ( function_exists( 'maspik_license_checker' ) ) {
		try {
			if ( maspik_license_checker()->license()->isLicenseValid() ) {
				return 1;
			}
		} catch ( \Exception $e ) {
			//error_log( 'Error happened: ' . $e->getMessage() );
		}
	}

	return 0;
}
add_action('after_setup_theme', 'cfes_is_supporting');

function cfas_refresh_api($type = 'regular') {
    if (!cfes_is_supporting("api")) {
        return;
    }

    $private_file_id = (int)maspik_get_settings('private_file_id');
    $domain = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '';

    // Initialize $file as an empty array
    $file = array();

    // Check if the first API is available and fetch data
    if (!empty($private_file_id)) {
        $Api_file = "https://wpmaspik.com/wp-json/acf/v3/apis/$private_file_id";
        
        $response = wp_remote_get($Api_file);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $content = wp_remote_retrieve_body($response);
            $file = json_decode($content, true);
            $file = $file['acf'] ?? array();
        }
    }

    // Initialize $combinedAPI with the data from the first API
    $combinedAPI = $file;

    // Check if the second API should be accessed
    $popular_spam = maspik_get_settings("popular_spam"); 
    if ($popular_spam) {
        $Api_popular_spam_file = "https://wpmaspik.com/wp-json/acf/v3/options/public_api?num=234442&site=$domain";
        
        $response = wp_remote_get($Api_popular_spam_file);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $popularSpamContent = wp_remote_retrieve_body($response);
            $popularSpamFile = json_decode($popularSpamContent, true);
            $popularSpamFile = $popularSpamFile['acf'] ?? array();

            // Combine "text_field", "email_field", "textarea_field", and "contain_links" values from both APIs
            if (isset($popularSpamFile['text_field'])) {
                if (isset($combinedAPI['text_field'])) {
                    $combinedAPI['text_field'] .= "\r\n" . $popularSpamFile['text_field'];
                } else {
                    $combinedAPI['text_field'] = $popularSpamFile['text_field'];
                }
            }
            if (isset($popularSpamFile['email_field'])) {
                if (isset($combinedAPI['email_field'])) {
                    $combinedAPI['email_field'] .= "\r\n" . $popularSpamFile['email_field'];
                } else {
                    $combinedAPI['email_field'] = $popularSpamFile['email_field'];
                }
            }
            if (isset($popularSpamFile['textarea_field'])) {
                if (isset($combinedAPI['textarea_field'])) {
                    $combinedAPI['textarea_field'] .= "\r\n" . $popularSpamFile['textarea_field'];
                } else {
                    $combinedAPI['textarea_field'] = $popularSpamFile['textarea_field'];
                }
            }
            if (isset($popularSpamFile['contain_links'])) {
                $combinedAPI['contain_links'] = $combinedAPI['contain_links'] ?? $popularSpamFile['contain_links'];
            }
        }
    }

    // Update your option with the combined API result
    $previousAPI = get_option('spamapi') ?? array();
    $newAPI = $combinedAPI;
    
    if ($newAPI == $previousAPI) {
        if ($type == 'regular') {
            echo "<script>alert('You have the most new version already.');</script>";
        }
    } else {
        update_option('spamapi', $newAPI); 
        if ($type == 'regular') {
            echo "<script>alert('New version applied successfully.');</script>";
        }
    }
}

// Get the toggle match
function maspik_toggle_match($data) {
    global $MASPIK_TOGGLE_MAP;
    return isset($MASPIK_TOGGLE_MAP[$data]) ? $MASPIK_TOGGLE_MAP[$data] : '';
}



function cfas_get_error_text($field = "error_message") {
    $default_text = esc_html__('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');

    // Fetch texts in the order of priority
    $textAPI_specific = efas_get_spam_api("custom_error_message_$field", "text");
    $textAPI_general = efas_get_spam_api('error_message', "text");
    $text_general = maspik_get_settings("error_message");

    // Check if specific field has a toggle enabled and fetch the appropriate text
    if (maspik_check_table("custom_error_message_$field") && maspik_get_settings(maspik_toggle_match($field)) == 1) {
        $text_specific = maspik_get_settings("custom_error_message_$field");
    } else {
        $text_specific = null;
    }

    // Determine the text to use based on the order of priority
    $text = $text_specific ? $text_specific : ($textAPI_specific ? $textAPI_specific : ($text_general ? $text_general : ($textAPI_general ? $textAPI_general : $default_text)));

    return sanitize_text_field($text);
}

function get_maspik_footer(){
    ?> 
    <footer class="maspik-footer">
        <h3><?php esc_html_e('Is Maspik helping you block spam?', 'contact-forms-anti-spam'); ?></h3>
        <p><?php echo esc_html__('We would be incredibly grateful if you could ', 'contact-forms-anti-spam') . '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">' . esc_html__('leave us a 5-star review', 'contact-forms-anti-spam') . '</a>. ' . esc_html__('Your feedback not only helps others discover our plugin but also fuels our passion to keep enhancing it. It helps us grow and continue improving. Thank you for your support!', 'contact-forms-anti-spam'); ?></p>
        <h4><?php esc_html_e('Join Our Facebook Community!', 'contact-forms-anti-spam'); ?></h4>
        <p><?php echo esc_html__('Ask questions, share spam examples, get ideas on how to block them, share feedback, and suggest new features. Join us at ', 'contact-forms-anti-spam') . '<a href="https://www.facebook.com/groups/maspik" target="_blank">' . esc_html__('WP Maspik Community - Stopping Spam Together', 'contact-forms-anti-spam') . '</a>.'; ?></p>
        <h4><?php esc_html_e('Contact Us', 'contact-forms-anti-spam'); ?></h4>
        <p><?php esc_html_e('Need support? Do you have ideas on how to improve MASPIK?', 'contact-forms-anti-spam'); ?><br>
        <?php echo esc_html__('We would love to hear from you at', 'contact-forms-anti-spam') . ' <a href="mailto:hello@wpmaspik.com" target="_blank">hello@wpmaspik.com</a>'; ?></p>

    </footer>
    <?php
}

add_filter( 'admin_body_class', 'cfas_admin_classes' );
function cfas_admin_classes( $classes ) {
      $screen = get_current_screen()->id;
    if ( strpos($screen, 'maspik') !== false ){
        $classes .=  cfes_is_supporting() ? " maspik-pro " : false;
    }
    return $classes;
}

//AbuseIPDB (Thanks to @josephcy95)
function check_abuseipdb($ip){
  $apikey = maspik_get_settings( 'abuseipdb_api' );
  // By Default use RapidAPI
  $apiEndpoint = "https://api.abuseipdb.com/api/v2/check?ipAddress=" . $ip . "&maxAgeInDays=90";
  $headers = array(
    'content-type' => 'application/json',
    'accept' => 'application/json',
    'Key' => $apikey
  );

  $args = array(
    'headers' => $headers,
    'timeout' => 20
  );

  $jsonreply = wp_remote_get($apiEndpoint, $args);
  $jsonreply = wp_remote_retrieve_body($jsonreply);
  $jsonreply = json_decode($jsonreply, TRUE);

  return isset( $jsonreply["data"]["abuseConfidenceScore"] ) ? (int)$jsonreply["data"]["abuseConfidenceScore"] : false ;
}

function check_proxycheckio($ip){
    $apikey = maspik_get_settings('proxycheck_io_api');

    // By Default use RapidAPI
    $apiEndpoint = "https://proxycheck.io/v2/" . $ip . "?key=" . $apikey . "&risk=1&vpn=1";
    $headers = array(
        'content-type' => 'application/json',
        'accept' => 'application/json',
        'Key' => $apikey
    );

    $args = array(
        'headers' => $headers,
        'timeout' => 20
    );

    $jsonreply = wp_remote_get($apiEndpoint, $args);

    // Check if $jsonreply is not null and is a successful response
    if (!is_wp_error($jsonreply) && wp_remote_retrieve_response_code($jsonreply) === 200) {
        $jsonreply = wp_remote_retrieve_body($jsonreply);
        $jsonreply = json_decode($jsonreply, TRUE);

        // Check if $jsonreply is not null and if the IP address exists as a key
        if ($jsonreply !== null && isset($jsonreply[$ip])) {
            return (int)$jsonreply[$ip]["risk"];
        }
    }

    // Return a default risk value or handle the case where the response is not as expected
    return -1;
} 

function cidr_match($ip, $cidr){
    $cidr_parts = explode('/', $cidr);

    // Check if $cidr_parts contains at least two elements
    if (count($cidr_parts) < 2) {
        // Handle the case where $cidr is not in the expected format
        return false;
    }

    list($subnet, $bits) = $cidr_parts;

    if ($bits === null) {
        $bits = 32;
    }
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
    return ($ip & $mask) == $subnet;
} 

function ip_is_cidr($ip) {
    // CIDR notation validation pattern
    $pattern = '/^(\d{1,3}\.){3}\d{1,3}(\/(\d|[1-2]\d|3[0-2]))?$/';
    return preg_match($pattern, $ip) ? $ip : false;
}



function Maspik_admin_notice() {
    // Check if the user has 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the notice has been dismissed
    if (!get_transient('Mapik_dismissed_shereing_notice') && (maspik_get_settings('shere_data') == "")) {
        
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php esc_html_e('Maspik: Help us improve spam blocking! Please allow us to collect non-sensitive information.', 'contact-forms-anti-spam'); ?>
                <button id="allow-sharing-button" class="button button-primary"> <?php esc_html_e('of course!', 'contact-forms-anti-spam'); ?></button>
            </p>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#allow-sharing-button').on('click', function(e) {
                    e.preventDefault();
                    // AJAX call to update wp_options upon button click
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'Maspik_allow_sharing_action',
                            allow_sharing: true,
                            security: '<?php echo wp_create_nonce("maspik_allow_sharing_nonce"); ?>',
                        },
                        success: function(response) {
                            // Reload the page or perform any other action
                            location.reload();
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });

                // Dismiss notice on close button click
                $('.notice.is-dismissible').on('click', '.notice-dismiss', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'Maspik_dismiss_notice_action'
                        },
                        success: function(response) {
                            // Hide the notice
                            $('.notice.is-dismissible').remove();
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });
            });
        </script>
        <?php
    }else{
        
    }
}
add_action('admin_notices', 'Maspik_admin_notice');

// AJAX callback function to update wp_options for allowing sharing
add_action('wp_ajax_Maspik_allow_sharing_action', 'Maspik_allow_sharing_callback');
function Maspik_allow_sharing_callback() {
    // Check nonce
    check_ajax_referer('maspik_allow_sharing_nonce', 'security');

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Permission error', 'contact-forms-anti-spam'));
    }

    // Update option
    //update_option('shere_data', 1);
    maspik_save_settings('shere_data', 1);
    

    wp_die(); // Always use wp_die() at the end of an AJAX callback
}

// AJAX callback function to dismiss the notice
add_action('wp_ajax_Maspik_dismiss_notice_action', 'Maspik_dismiss_notice_callback');
function Maspik_dismiss_notice_callback() {
    set_transient('Mapik_dismissed_shereing_notice', true, MONTH_IN_SECONDS); // Set the transient to dismiss the notice
    wp_die();
}

// Maspik if bricks exist
add_action('init', 'maspik_if_bricks_exist');
function maspik_if_bricks_exist(){
  $theme = wp_get_theme( get_template() );
  $theme_name = $theme['Name'];
  return $theme_name === 'Bricks';
}


//
// Handle export settings
add_action('admin_post_Maspik_export_settings', 'Maspik_export_settings');

function Maspik_export_settings() {
    // Check nonce
    if (!isset($_POST['Maspik_export_settings_nonce_field']) || !wp_verify_nonce($_POST['Maspik_export_settings_nonce_field'], 'Maspik_export_settings_nonce')) {
        wp_die('Security check failed');
    }

    // Get Maspik settings
    global $wpdb;
    $table_name = $wpdb->prefix . 'maspik_options';
    
    // Fetch all settings from the database
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    
    // Initialize $maspik_settings array
    $maspik_settings = array();
    
    // Populate $maspik_settings with all data from the table
    foreach ($results as $setting) {
        $option_name = sanitize_text_field($setting['option_name']);
        $option_value = sanitize_text_field($setting['option_value']);
        $maspik_settings[$option_name] = $option_value;
    }
    
    // Add system information directly to the $maspik_settings array
    $maspik_settings['wordpress_version'] = get_bloginfo('version');
    $maspik_settings['plugin_version'] = MASPIK_VERSION; 
    $maspik_settings['wordpress_language'] = get_bloginfo('language');
    $maspik_settings['php_version'] = phpversion();
    $maspik_settings['theme_name'] = wp_get_theme()->get('Name');
    $maspik_settings['spamcounter'] = get_option('spamcounter');
    $maspik_settings['shere_data'] = get_option('shere_data');
    $maspik_settings['maspik_api_requests'] = get_option('maspik_api_requests');
   
    // Get domain name of the site
    $domain_name = get_site_url();

    // Custom string
    $custom_string = "OnlyYouKnowWhatIsGoodForYou";

    // Convert settings array to JSON
    $json_data = wp_json_encode($maspik_settings);

    $exported_data = $custom_string . "\n\n" . $domain_name . "\n\n" . $json_data;

    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="maspik-settings.json"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($exported_data));

    // Output JSON data
    echo $exported_data;
    exit;
}

// Handle import settings
add_action('admin_post_Maspik_import_settings', 'Maspik_import_settings');

function Maspik_import_settings() {
    // Check nonce
    if (!isset($_POST['Maspik_import_settings_nonce_field']) || !wp_verify_nonce($_POST['Maspik_import_settings_nonce_field'], 'Maspik_import_settings_nonce')) {
        wp_die('Security check failed');
    }

    // Check if a file was uploaded
    if (!isset($_FILES['maspik-settings']) || $_FILES['maspik-settings']['error'] !== UPLOAD_ERR_OK) {
        wp_die('Invalid file upload');
    }

    $uploaded_file = $_FILES['maspik-settings'];

    // Perform file validation
    $allowed_mime_types = array('application/json');
    $allowed_extensions = array('json');
    $file_type = wp_check_filetype_and_ext($uploaded_file['tmp_name'], $uploaded_file['name'], $allowed_mime_types, $allowed_extensions);

    // Perform file validation
    $allowed_extensions = array('json');
    $uploaded_file_extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($uploaded_file_extension), $allowed_extensions, true)) {
        wp_die('Invalid file type');
    }

    // Read the JSON data from the uploaded file
    $json_data = file_get_contents($uploaded_file['tmp_name']);

    // Separate domain name, custom string, and JSON data
    $parts = explode("\n\n", $json_data, 3);
    if (count($parts) !== 3) {
        wp_die('Invalid file format');
    }

    // Extract domain name, custom string, and JSON data
    $custom_string = $parts[0];
    $domain_name = $parts[1];
    $maspik_settings = json_decode($parts[2], true);

    // Check if the custom string matches the expected value
    $expected_custom_string = "OnlyYouKnowWhatIsGoodForYou";
    if ($custom_string !== $expected_custom_string) {
        wp_die('Invalid custom string');
    }

    // Validate JSON data
    if ($maspik_settings === null) {
        wp_die('Invalid JSON data');
    }

    // Check for JSON decoding errors
    if ($maspik_settings === null && json_last_error() !== JSON_ERROR_NONE) {
        wp_die('Error decoding JSON data');
    }
    $maspik_settings = str_replace("\n" , ",,," , $maspik_settings);
    // Sanitize imported data
    $sanitized_data = array_map('sanitize_text_field', $maspik_settings);

    global $MASPIK_IMPORT_OPTIONS;
    
    // Iterate over each option
    foreach ($MASPIK_IMPORT_OPTIONS as $option) {
        // Check if the option exists in $sanitized_data and is not empty
        if (isset($sanitized_data[$option]) && !empty($sanitized_data[$option])) {
            // Perform replacements only if the option exists and is not empty
            // Update the option with sanitized data
            maspik_save_settings($option, str_replace(",,," , "\n" ,$sanitized_data[$option]));
        }
    }

    // Redirect after import
    wp_redirect(admin_url('admin.php?page=maspik&imported=1'));
    exit;
}

function maspik_array_to_html_table($array) {
    if (empty($array)) {
        return '<p>No data available.</p>';
    }
    
    $html = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    $html .= '<thead><tr>';

    // Table headers
    foreach (array_keys($array) as $key) {
        $html .= '<th>' . htmlspecialchars($key) . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody><tr>';

    // Table data
    foreach ($array as $value) {
        $html .= '<td>' . htmlspecialchars($value) . '</td>';
    }

    $html .= '</tr></tbody>';
    $html .= '</table>';

    return $html;
}


add_action('admin_post_Maspik_spamlog_download_csv', 'Maspik_spamlog_download_csv');

function Maspik_spamlog_download_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'maspik_spam_logs';

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    if (empty($results)) {
        wp_die('No data found.');
    }

    $filename = 'spam_log_export_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    fputcsv($output, array_keys($results[0]));

    foreach ($results as $row) {

        if (isset($row['spam_detail'])) {
            $spam_detail = @maybe_unserialize($row['spam_detail']);
            $row['spam_detail'] = is_array($spam_detail) ? json_encode($spam_detail, JSON_UNESCAPED_UNICODE) : $row['spam_detail'];
        }
        
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

function maspik_is_reach_limit_and_increment_api_requests() {

    $max_requests = cfes_is_supporting("ip_verification") ? 10000 : 100;
    
    $api_data = get_option("maspik_api_requests", array(
        'months' => array()
    ));

    $current_month = date('Ym');

    // Check if the current month already exists in the array
    if (!isset($api_data['months'][$current_month])) {
        // If not, add it
        $api_data['months'][$current_month] = array(
            'attempts' => 0,
            'actual_calls' => 0,
            'blocks' => 0
        );
    }

    // Increase the number of attempts
    $api_data['months'][$current_month]['attempts']++;

    // Check if we reached the maximum number of requests
    if ($api_data['months'][$current_month]['actual_calls'] < $max_requests) {
        $api_data['months'][$current_month]['actual_calls']++;
        // we didnt reach the limit
        $result = false;
    } else {
        // we reached the limit
        $result = true;
    }

    // Save only the last 6 months
    $api_data['months'] = array_slice($api_data['months'], -6, 6, true);

    update_option("maspik_api_requests", $api_data);
    return $result;
}

// Check if the IP exists in the API
function check_ip_in_api($ip, $form) {

    try {
        // Get the existing array or create a new one if it doesn't exist
        $recent_checks = get_option( "maspik_recent_ip_checks", array());

        // Check if $recent_checks is an array, and if not, initialize it as an empty array
        if (!is_array($recent_checks)) {
            $recent_checks = array();
        }

        // Check if the IP already exists in the array
        foreach ($recent_checks as $check) {
            if (isset($check['ip']) && $check['ip'] === $ip) {
                // If the IP already exists and is blocked, increase the number of blocks
                if (isset($check['result']) && $check['result'] === true) {
                    maspik_increment_blocks();
                }
                return isset($check['result']) ? $check['result'] : false;
            }
        }

        // Check if we can make an API call
        if (maspik_is_reach_limit_and_increment_api_requests()) {
            //error_log("Maspik: Monthly API request limit reached");
            return false; // return request when we reach the limit
        }

        $site_url = get_site_url();
        $api_key = MASPIK_API_KEY;
    
        // Perform the API call
        $url = add_query_arg(array(
            'ip' => $ip,
            'form' => $form,
            'referer' => $site_url
        ), 'https://api.wpmaspik.com/check_ip');

        $args = array(
            'headers' => array(
                'x-api-key' => $api_key 
            ),
            'timeout' => 5  // Set timeout for the request
        );

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            //error_log('Maspik IP Check API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (empty($body) || !is_array($result)) {
            //error_log('Maspik IP Check API Error: Invalid response');
            return false;
        }

        $exists = isset($result['exists']) && $result['exists'] === true;

        // Add the new result to the beginning of the array
        array_unshift($recent_checks, array(
            'ip' => $ip,
            'result' => $exists
        ));

        // Save only the last 10 checks
        $recent_checks = array_slice($recent_checks, 0, 10);

        // Update the option
        update_option("maspik_recent_ip_checks", $recent_checks);

        // If the IP is blocked, increase the number of blocks
        if ($exists) {
            maspik_increment_blocks();
        }

        return $exists;
    } catch (Exception $e) {
        //error_log('Maspik IP Check Error: ' . $e->getMessage());
        return false;
    }
}

function maspik_increment_blocks() {
    $api_data = get_option("maspik_api_requests", array('months' => array()));
    
    $current_month = date('Ym');
    
    if (!isset($api_data['months'][$current_month])) {
        $api_data['months'][$current_month] = array(
            'attempts' => 0,
            'actual_calls' => 0,
            'blocks' => 0
        );
    }
    
    $api_data['months'][$current_month]['blocks']++;
    
    update_option("maspik_api_requests", $api_data);
}

// Set default values for various settings
function maspik_save_default_values() {
    global $MASPIK_DEFAULT_SETTINGS;
    
    foreach ($MASPIK_DEFAULT_SETTINGS as $setting => $value) {
        maspik_save_settings($setting, $value);
    }
}

function maspik_pointer_scripts() {
    // Check if the user has already dismissed the pointer
    $dismissed = get_user_meta( get_current_user_id(), 'maspik_pointer_dismissed', true );
    if ( $dismissed ) {
        return;
    }

    // Enqueue WP Pointer scripts and styles
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'wp-pointer' );

    add_action( 'admin_footer', 'maspik_pointer_footer_script' );
}
add_action( 'admin_enqueue_scripts', 'maspik_pointer_scripts' );

function maspik_pointer_footer_script() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var content = '<h3>' + <?php echo wp_json_encode(__('Welcome to Maspik Advanced Spam Protection', 'contact-forms-anti-spam')); ?> + '</h3>';
        content += "<p>" + <?php echo wp_json_encode(__("Maspik offers a wide range of options to protect your website from getting spam. In the settings page, you'll find easy-to-use tools for setting the desired level of protection.", 'contact-forms-anti-spam')); ?> + "</p>";
        content += '<p><a class="button button-primary maspik-settings-button" href="<?php echo admin_url('admin.php?page=maspik'); ?>">' + <?php echo wp_json_encode(__('Go to Settings', 'contact-forms-anti-spam')); ?> + '</a></p>';

        // Use a more general selector
        var element = $('#toplevel_page_maspik').first();

        if (element.length) {
            var pointer = element.pointer({
                content: content,
                position: {
                    edge: '<?php echo is_rtl() ? 'right' : 'left'; ?>',
                    align: 'center'
                },
                close: function() {
                    dismissPointer();
                }
            }).pointer('open');

            // Add click event to the settings button
            $('.maspik-settings-button').on('click', function(e) {
                dismissPointer();
                // The default action (following the link) will still occur
            });
        }

        function dismissPointer() {
            $.post(ajaxurl, {
                action: 'maspik_dismiss_pointer',
                security: '<?php echo wp_create_nonce("maspik_dismiss_pointer_nonce"); ?>'
            });
        }
    });
    </script>
    <?php
}

function maspik_dismiss_pointer() {
    check_ajax_referer( 'maspik_dismiss_pointer_nonce', 'security' );
    
    $user_id = get_current_user_id();
    update_user_meta( $user_id, 'maspik_pointer_dismissed', true );
    
    wp_die();
}
add_action( 'wp_ajax_maspik_dismiss_pointer', 'maspik_dismiss_pointer' );

// IP Verification popup content
function IP_Verification_popup_content() {
    // Start output buffering
    ob_start();
    ?>
    <div class="maspik-popup-content">
        <h2><?php esc_html_e('What is IP Verification?', 'contact-forms-anti-spam'); ?></h2>

        <p><?php esc_html_e('IP Verification checks if the sender\'s IP address is flagged as spam in the Maspik database.', 'contact-forms-anti-spam'); ?></p>

        <h4><?php esc_html_e('Your IP Verification Activity', 'contact-forms-anti-spam'); ?></h4>
        <table class="maspik-stats-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Month', 'contact-forms-anti-spam'); ?></th>
                    <th><?php esc_html_e('IP Checks Attempted', 'contact-forms-anti-spam'); ?></th>
                    <th><?php esc_html_e('API Calls Made', 'contact-forms-anti-spam'); ?></th>
                    <th><?php esc_html_e('IPs Blocked', 'contact-forms-anti-spam'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $api_data = get_option('maspik_api_requests', array('months' => array()));
                if (!isset($api_data['months']) || empty($api_data['months'])) {
                    echo '<tr><td colspan="4">' . esc_html__('No data available. Please wait for some submissions.', 'contact-forms-anti-spam') . '</td></tr>';
                } else {
                    // Sort the months in descending order
                    krsort($api_data['months']);
                    $months_displayed = 0;
                    $max_requests = cfes_is_supporting("ip_verification") ? 10000 : 100;
                    foreach ($api_data['months'] as $month => $data) {
                        if ($months_displayed >= 6) break; // Limit to last 12 months
                        // Convert $month from 'YYYYMM' to a readable format
                        $dateObj = DateTime::createFromFormat('Ym', $month);
                        if ($dateObj) {
                            $monthName = $dateObj->format('F Y');
                        } else {
                            $monthName = esc_html($month);
                        }
                        $actual_calls = intval($data['actual_calls']);
                        if ( $actual_calls > $max_requests ) {
                            $max_requests = $max_requests . ' ' . esc_html__('(Reached Limit)', 'contact-forms-anti-spam');
                        }
                        echo '<tr>';
                        echo '<td>' . esc_html($monthName) . '</td>';
                        echo '<td>' . intval($data['attempts']) . '</td>';
                        echo "<td> " . esc_html("$actual_calls/$max_requests") . " </td>";
                        echo '<td>' . intval($data['blocks']) . '</td>';
                        echo '</tr>';
                        $months_displayed++;
                    }
                }
                ?>
            </tbody>
        </table>

        <h4><?php esc_html_e('Understanding the Data', 'contact-forms-anti-spam'); ?></h4>
        <ul>
            <li><strong><?php esc_html_e('IP Checks Attempted', 'contact-forms-anti-spam'); ?></strong>: <?php esc_html_e('The total number of times your site tried to verify an IP address.', 'contact-forms-anti-spam'); ?></li>
            <li><strong><?php esc_html_e('API Calls Made', 'contact-forms-anti-spam'); ?></strong>: <?php esc_html_e('The number of IP checks sent to Maspik\'s servers (counts toward your monthly limit).', 'contact-forms-anti-spam'); ?></li>
            <li><strong><?php esc_html_e('IPs Blocked', 'contact-forms-anti-spam'); ?></strong>: <?php esc_html_e('The number of IP addresses identified and blocked as spam.', 'contact-forms-anti-spam'); ?></li>
        </ul>
        <p><em><?php esc_html_e('Note: The number of IPs Blocked can be higher than API Calls Made because Maspik caches the results of the last 10 IP verifications. If an IP was recently checked and is in the cache, it doesn\'t count against your API limit but still helps in blocking spam.', 'contact-forms-anti-spam'); ?></em></p>
        <?php if ( !cfes_is_supporting("ip_verification") ) { ?>
            <hr>
            <h4><?php esc_html_e('Need More API Requests?', 'contact-forms-anti-spam'); ?></h4>
            <p><?php esc_html_e('Upgrade to Maspik Pro to get up to 10,000 API requests per month and improve your site\'s spam protection!', 'contact-forms-anti-spam'); ?></p>
            <?php maspik_get_pro(); ?>
        <?php } ?>
    </div>
    <?php
    // Output the content
    echo ob_get_clean();
}


function maspik_handle_activation_popup() {

    if (!current_user_can('manage_options')) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $popup = isset($_GET['popup']) ? absint($_GET['popup']) : 0;
    
    $maspik_api_id = get_option("maspik_api_id");
    if (empty($maspik_api_id)) {
        return;
    }

    if ($page !== 'maspik_activator' || 
        $status !== 'success' || 
        $popup !== 1) {
        return;
    }

    $api_ids = array_map('trim', explode(',', $maspik_api_id));
    $first_maspik_api_id = $api_ids[0];
    
    if (!is_numeric($first_maspik_api_id)) {
        return;
    }

    $dashboard_url = esc_url('https://wpmaspik.com/?page_id=' . absint($first_maspik_api_id));

    $nonce = wp_create_nonce('maspik_activation_popup_nonce');
    
    $select_options = '';
    foreach ($api_ids as $id) {
        if (is_numeric(trim($id))) {
            $id = absint(trim($id));
            $select_options .= sprintf(
                '<option value="%1$d">%1$d</option>',
                $id
            );
        }
    }
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var popup_content = '<h2><?php echo esc_js(__("License Activated Successfully!", "contact-forms-anti-spam")); ?></h2>' +
                            '<p><?php echo esc_js(__("We found a control panel ID associated with this license, would you like us to automatically assign it to this site?", "contact-forms-anti-spam")); ?></p>' +
                            '<div class="warp">' +
                            '<div class="select-wrapper">' +
                            '<label for="dashboard_id_select"><?php echo esc_js(__("Select Dashboard ID:", "contact-forms-anti-spam")); ?></label>' +
                            '<select id="dashboard_id_select" class="dashboard-select"><?php echo $select_options; ?></select>' +
                            '</div>' +
                            '<div class="buttons-wrapper">' +
                            '<button id="add_dashboard_id" class="button button-primary" data-nonce="<?php echo esc_attr($nonce); ?>"><?php echo esc_js(__("Add Dashboard ID", "contact-forms-anti-spam")); ?></button>' +
                            '<a target="_blank" href="<?php echo esc_js($dashboard_url); ?>" class="button button-secondary"><?php echo esc_js(__("Open Dashboard", "contact-forms-anti-spam")); ?></a>' +
                            '</div>' +
                            '</div>' +
                            '<button class="close-popup">&times;</button>';

        var $popup = $('<div id="maspik_activation_popup">').html(popup_content).appendTo('body').css({
            'position': 'fixed',
            'top': '50%',
            'left': '50%',
            'transform': 'translate(-50%, -50%)',
            'background': 'white',
            'padding': '20px',
            'border': '1px solid #ccc',
            'box-shadow': '0 0 10px rgba(0,0,0,0.1)',
            'z-index': '9999',
            'width': '400px'
        });

        // Add overlay
        var $overlay = $('<div id="maspik_popup_overlay">').appendTo('body').css({
            'position': 'fixed',
            'top': 0,
            'left': 0,
            'right': 0,
            'bottom': 0,
            'background': 'rgba(0,0,0,0.5)',
            'z-index': 9998
        });

        // Close popup function
        function closePopup() {
            $popup.remove();
            $overlay.remove();
        }

        // Close button click handler
        $('.close-popup').on('click', closePopup);

        // Close on overlay click
        $overlay.on('click', closePopup);

        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                closePopup();
            }
        });

        // Update dashboard URL when select changes
        $(document).on('change', '#dashboard_id_select', function() {
            var selectedId = $(this).val();
            var newUrl = 'https://wpmaspik.com/?page_id=' + selectedId;
            $('.button-secondary').attr('href', newUrl);
        });

        // Update AJAX call to use selected ID
        $('#add_dashboard_id').on('click', function() {
            var selectedId = $('#dashboard_id_select').val();
            
            // Get WordPress admin URL from PHP
            var adminUrl = '<?php echo admin_url("admin.php"); ?>';
            
            // Create URL object
            var url = new URL(adminUrl);
            
            // Add parameters
            url.searchParams.set('page', 'maspik');
            url.searchParams.set('private_file_id', selectedId);
            
            // Redirect
            window.location.href = url.toString();
        });
    });
    </script>
    <style>
    #maspik_activation_popup {
        position: relative;
    }
    #maspik_activation_popup .warp {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    #maspik_activation_popup .select-wrapper {
        margin-bottom: 15px;
    }
    #maspik_activation_popup .dashboard-select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
    }
    #maspik_activation_popup .buttons-wrapper {
        display: flex;
        justify-content: space-between;
    }
    #maspik_activation_popup .buttons-wrapper > * {
        width: 48%;
        text-align: center;
    }
    #maspik_activation_popup .close-popup {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 24px;
        color: #666;
        cursor: pointer;
        background: none;
        border: none;
    }
    #maspik_activation_popup .close-popup:hover {
        color: #000;
    }
    </style>
    <?php
}

add_action('admin_footer', 'maspik_handle_activation_popup');

/**
 * Retrieves the spam key (generating if needed).
 *
 * @return string The unique spam key.
 */
function maspik_get_spam_key() {
    // Retrieve the key from the plugin settings or generate one if it doesn't exist
    $key = get_option( 'maspik_spam_key' );

    if ( ! $key ) {
        // If no key exists, generate one and save it
        $key = wp_generate_password( 64, false, false );
        update_option( 'maspik_spam_key', $key, false );
    }

    return $key;
}

function maspik_get_browser_name($user_agent) {
    // If there is no user agent, return a suspicious message
    if (empty($user_agent)) {
        return '[Suspicious] Empty UA';
    }

    // Clean the user agent and convert to lowercase
    $t = strtolower(trim($user_agent));
    
    // Add a space at the beginning to prevent false positives with strpos
    $t = " " . $t;

    // Array of trusted browsers
    $trusted_browsers = [
        'instagram' => '[Trusted] Instagram App',
        'fb_iab'    => '[Trusted] Facebook App',
        'fbav'      => '[Trusted] Facebook App',
        'whatsapp'  => '[Trusted] WhatsApp',
        'telegram'  => '[Trusted] Telegram',
        'line/'     => '[Trusted] LINE'
    ];

    // Array of suspicious browsers
    $suspicious_browsers = [
        'headless'  => '[Suspicious] Headless',
        'phantomjs' => '[Suspicious] PhantomJS',
        'selenium'  => '[Suspicious] Selenium',
        'puppet'    => '[Suspicious] Puppeteer'
    ];

    // Array of regular browsers
    $regular_browsers = [
        'chrome'    => 'Chrome',
        'firefox'   => 'Firefox',
        'safari'    => 'Safari',
        'edge'      => 'Edge',
        'opera'     => 'Opera',
        'opr/'      => 'Opera'
    ];

    // Array of known bots
    $known_bots = [
        'google'    => '[Bot] Googlebot',
        'bing'      => '[Bot] Bingbot',
        'yandex'    => '[Bot] Yandex'
    ];

    // Array of suspicious tools
    $suspicious_tools = [
        'curl'      => '[Suspicious] Curl',
        'wget'      => '[Suspicious] Wget',
        'python'    => '[Suspicious] Python',
        'ruby'      => '[Suspicious] Ruby',
        'perl'      => '[Suspicious] Perl'
    ];

    // Check for short User Agent
    if (strlen($t) < 30) {
        return '[Suspicious] Short UA';
    }

    // Check for trusted browsers
    foreach ($trusted_browsers as $key => $value) {
        if (strpos($t, $key) !== false) {
            return $value;
        }
    }

    // Check for suspicious browsers
    foreach ($suspicious_browsers as $key => $value) {
        if (strpos($t, $key) !== false) {
            return $value;
        }
    }

    // Check for regular browsers
    foreach ($regular_browsers as $key => $value) {
        if (strpos($t, $key) !== false) {
            return $value;
        }
    }

    // Check for known bots
    foreach ($known_bots as $key => $value) {
        if (strpos($t, $key) !== false) {
            return $value;
        }
    }

    // Check for suspicious tools
    foreach ($suspicious_tools as $key => $value) {
        if (strpos($t, $key) !== false) {
            return $value;
        }
    }

    // Check for generic bot patterns
    $bot_patterns = ['bot', 'crawler', 'spider', 'http'];
    foreach ($bot_patterns as $pattern) {
        if (strpos($t, $pattern) !== false) {
            return '[Bot] Generic';
        }
    }

    // If no match found, return the first 50 characters of the UA
    return '[Unknown] ' . substr($user_agent, 0, 50);
}

function maspik_is_contains_emoji($text) {
    $pattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';
    return preg_match($pattern, $text) === 1;
}

