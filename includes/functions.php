<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 


/*
* Main function
*/


//check if table exists

    function maspik_table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'maspik_options';
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
    }

    function maspik_logtable_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'maspik_spam_logs';
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name;
    }

//check if table exists - END

// Save to DB Function 
    function maspik_save_settings($col_name, $new_value) {
        global $wpdb;
            
        $table = maspik_get_dbtable();
        $setting_value = maspik_get_dbvalue();
        $setting_label = maspik_get_dblabel();

        // SQL query to update

        if(is_numeric($new_value)){
            $sql = $wpdb->prepare("UPDATE $table SET $setting_value = %d WHERE $setting_label = %s", $new_value, $col_name);
        }else{
            $sql = $wpdb->prepare("UPDATE $table SET $setting_value = %s WHERE $setting_label = %s", $new_value, $col_name);
        }

        $result = $wpdb->query($sql);
            
        if ($result !== false) {
            $result_check = "success";
        } else {
            $result_check =  $wpdb->last_error;
        }

        return $result_check;
    }
// Save to DB Function - END

//Set DB table variables

    function maspik_get_logtable(){
        global $wpdb;
        
        $table = $wpdb->prefix . 'maspik_spam_logs';
        return $table;
    }

    function maspik_get_dbtable(){
        global $wpdb;
        if(maspik_table_exists()){
            $table = $wpdb->prefix . 'maspik_options'; // new table
        } else {
            $table = $wpdb->options; // wp options table
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
                    $data = $result->$setting_value  == 'yes' ? 1 : 0;
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
        $data = " --- "; 
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
        function create_maspik_table($val = "") {
            global $wpdb;
        
            $table_name = $wpdb->prefix . 'maspik_options';
            
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                option_name varchar(191) NOT NULL,
                option_value longtext NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        
            if($val != "auto"){
                echo "<div class='maspik-update-db-result'>Database updated successfully.</div>";
            }
        }

    //make new log table
        function create_maspik_log_table() {
            global $wpdb;
        
            $table_name = $wpdb->prefix . 'maspik_spam_logs';
            
            $charset_collate = $wpdb->get_charset_collate();
            
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
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }

    //transfer data from wp_options to the new table
        function transfer_data_to_table($data) {
            global $wpdb;

            $source_table = $wpdb->prefix . 'options';
            $target_table = $wpdb->prefix . 'maspik_options';

            foreach ($data as $option_name) {
                // Prepare and execute the transfer query for each option
                $query = $wpdb->prepare("
                    INSERT INTO $target_table (option_name, option_value)
                    SELECT option_name, option_value
                    FROM $source_table
                    WHERE option_name = %s
                ", $option_name);


                if(!$wpdb->query($query)){
                    $wpdb->insert(
                        $target_table,
                        array(
                            'option_name' => $option_name, 
                            'option_value' => '',
                        )
                    );
                }
            }
        }

    //make default value for spam log
        function set_spam_log_limit_default(){
            $log_active = maspik_get_settings("maspik_Store_log");
            $limit_value = maspik_get_settings("spam_log_limit");

            if( ($log_active == "yes") && ($limit_value == '') ){

                maspik_save_settings('spam_log_limit', 2000);

            }

        }

    //runs transfer - list of rows to be transfered
        function maspik_run_transfer(){
            $data = array(
                //text field
                'text_blacklist', 
                'MinCharactersInTextField',
                'MaxCharactersInTextField',
                'custom_error_message_MaxCharactersInTextField',
                //email field
                'emails_blacklist', 
                //textarea field
                'textarea_blacklist',
                'MinCharactersInTextAreaField',
                'MaxCharactersInTextAreaField',
                'contain_links',
                'custom_error_message_MaxCharactersInTextAreaField',
                //Phone field
                'tel_formats',
                'MinCharactersInPhoneField',
                'MaxCharactersInPhoneField',
                'custom_error_message_MaxCharactersInPhoneField',
                'custom_error_message_tel_formats',
                //Language needed
                'lang_needed',
                'custom_error_message_lang_needed',
                //Language needed
                'lang_forbidden',
                'custom_error_message_lang_forbidden',
                //Countries
                'country_blacklist',
                'AllowedOrBlockCountries',  
                'custom_error_message_country_blacklist',
                //Maspik API
                'private_file_id',
                'popular_spam',
                //General
                'NeedPageurl',
                'ip_blacklist',
                'error_message',
                'abuseipdb_api',
                'abuseipdb_score',
                'proxycheck_io_api',
                'proxycheck_io_risk',
                //form-support
                'maspik_support_Elementor_forms',
                'maspik_support_cf7',
                'maspik_support_woocommerce_review',            
                'maspik_support_Woocommerce_registration',
                'maspik_support_Wpforms',
                'maspik_support_gravity_forms',
                'maspik_support_formidable_forms',
                'maspik_support_fluentforms_forms',
                'maspik_support_bricks_forms',
                'maspik_support_forminator_forms',
                'maspik_support_ninjaforms',
                'maspik_support_registration',
                'maspik_support_wp_comment',
                //extra
                'maspik_Store_log',
                'spam_log_limit',
                //toggles
                'text_limit_toggle',
                'text_custom_message_toggle',
                'textarea_limit_toggle',
                'textarea_link_limit_toggle',
                'textarea_custom_message_toggle',
                'tel_limit_toggle',
                'phone_limit_custom_message_toggle',
                'phone_custom_message_toggle',
                'lang_need_custom_message_toggle',
                'lang_forbidden_custom_message_toggle',
                'country_custom_message_toggle',
            );
            transfer_data_to_table($data);

            $togglearray = array(
                "MaxCharactersInTextField",
                "custom_error_message_MaxCharactersInTextField",
                "contain_links",
                "MaxCharactersInTextAreaField",
                "custom_error_message_MaxCharactersInTextAreaField",
                "MaxCharactersInPhoneField",
                "custom_error_message_MaxCharactersInPhoneField",
                "custom_error_message_tel_formats",
                "lang_needed",
                "lang_forbidden",
                "country_blacklist",
            );


            foreach($togglearray as $toggledata){
                if(  trim(maspik_get_settings($toggledata)) != "" ){
                    maspik_save_settings(maspik_toggle_match($toggledata), "1");
                }

            }
                if(  trim(maspik_get_settings('maspik_Store_log')) == "" || !maspik_get_settings('maspik_Store_log')){
                    maspik_save_settings('maspik_Store_log', "yes");
                }

        }

// New table management functions - END


function efas_get_browser_name($user_agent){
        // Make case insensitive.
        $t = strtolower($user_agent);

        // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
        // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
        //     http://php.net/manual/en/function.strpos.php
        $t = " " . $t;

        // Humans / Regular Users     
        if     (strpos($t, 'opera'     ) || strpos($t, 'opr/')     ) return 'Opera'            ;
        elseif (strpos($t, 'edge'      )                           ) return 'Edge'             ;
        elseif (strpos($t, 'chrome'    )                           ) return 'Chrome'           ;
        elseif (strpos($t, 'safari'    )                           ) return 'Safari'           ;
        elseif (strpos($t, 'firefox'   )                           ) return 'Firefox'          ;
        elseif (strpos($t, 'msie'      ) || strpos($t, 'trident/7')) return 'Internet Explorer';

        // Search Engines 
        elseif (strpos($t, 'google'    )                           ) return '[Bot] Googlebot'   ;
        elseif (strpos($t, 'bing'      )                           ) return '[Bot] Bingbot'     ;
        elseif (strpos($t, 'slurp'     )                           ) return '[Bot] Yahoo! Slurp';
        elseif (strpos($t, 'duckduckgo')                           ) return '[Bot] DuckDuckBot' ;
        elseif (strpos($t, 'baidu'     )                           ) return '[Bot] Baidu'       ;
        elseif (strpos($t, 'yandex'    )                           ) return '[Bot] Yandex'      ;
        elseif (strpos($t, 'sogou'     )                           ) return '[Bot] Sogou'       ;
        elseif (strpos($t, 'exabot'    )                           ) return '[Bot] Exabot'      ;
        elseif (strpos($t, 'msn'       )                           ) return '[Bot] MSN'         ;

        // Common Tools and Bots
        elseif (strpos($t, 'mj12bot'   )                           ) return '[Bot] Majestic'     ;
        elseif (strpos($t, 'ahrefs'    )                           ) return '[Bot] Ahrefs'       ;
        elseif (strpos($t, 'semrush'   )                           ) return '[Bot] SEMRush'      ;
        elseif (strpos($t, 'rogerbot'  ) || strpos($t, 'dotbot')   ) return '[Bot] Moz or OpenSiteExplorer';
        elseif (strpos($t, 'frog'      ) || strpos($t, 'screaming')) return '[Bot] Screaming Frog';
       
        // Miscellaneous
        elseif (strpos($t, 'facebook'  )                           ) return '[Bot] Facebook'     ;
        elseif (strpos($t, 'pinterest' )                           ) return '[Bot] Pinterest'    ;
       
        // Check for strings commonly used in bot user agents  
        elseif (strpos($t, 'crawler' ) || strpos($t, 'api'    ) ||
                strpos($t, 'spider'  ) || strpos($t, 'http'   ) ||
                strpos($t, 'bot'     ) || strpos($t, 'archive') ||
                strpos($t, 'info'    ) || strpos($t, 'data'   )    ) return '[Bot] Other'   ;
       
        return 'Other (Unknown)';
}


//Spam log limiter
    function maspik_log_limiter(){
        
        global $wpdb;

        $max_logs = maspik_get_settings('spam_log_limit') == "" ? 2000 : maspik_get_settings('spam_log_limit');
        $current_count = maspik_spam_count();
        $table = $wpdb->prefix . 'maspik_spam_logs';

        

    
        if ($current_count > $max_logs) {
            // Calculate the number of entries to delete
            $entries_to_delete = $current_count - $max_logs;

            // Get the IDs of the oldest entries to delete
            $oldest_entries = $wpdb->get_results("
                SELECT id FROM $table
                ORDER BY id ASC 
                LIMIT $entries_to_delete
            ");

            // Delete the oldest entries using $wpdb->delete
            if (!empty($oldest_entries)) {
                foreach ($oldest_entries as $entry) {
                    $wpdb->delete($table, array('id' => $entry->id), array('%d'));
                }

            }
        }
    }

//Spam log limiter - END


//Save Error logs to table
    function maspik_save_log($type, $value, $detail, $ip, $country, $agent, $date, $source) {
        global $wpdb;

        if(maspik_logtable_exists()){
            $table = maspik_get_logtable();

            $data = array(
                'spam_type'    => $type,
                'spam_value'   => $value,
                'spam_detail'   => $detail,
                'spam_ip'      => $ip,
                'spam_country' => $country,
                'spam_agent'   => $agent,
                'spam_date'    => $date,
                'spam_source'  => $source
            );

            // Insert data into the database
            $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'); // Format for each field

            $result = $wpdb->insert($table, $data, $format);
            
                    
            if ($result) {
                $result_check = "success";
            } else {
                $result_check = $wpdb->last_error;
            }

            return $result_check;
        }
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


function efas_add_to_log($type = '', $input = '', $post = null, $source = "Elementor forms") {
    $spamcounter = get_option('spamcounter') ? get_option('spamcounter') : 0;
    update_option('spamcounter', ++$spamcounter);

    // Sanitize and escape user inputs
    if (maspik_get_settings("maspik_Store_log") == 'yes') {
        $text = wp_kses_data(print_r($post, true)); 
        $ip = efas_getRealIpAddr();
        $countryName = "Other (Unknown)";
        $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);
        if ($xml) {
            $countryName = strtolower($xml->geoplugin_countryName);
        }
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $browser_name = efas_get_browser_name($user_agent);
        $date = wp_date("d-m-Y H:i:s", null, wp_timezone());

        maspik_save_log(
            sanitize_text_field($type),
            sanitize_text_field($input),
            sanitize_textarea_field($text),
            sanitize_text_field($ip),
            sanitize_text_field($countryName),
            sanitize_text_field($browser_name),
            sanitize_text_field($date),
            sanitize_text_field($source)
        );

        echo maspik_log_limiter();
    }
}

function maspik_Download_log_btn(){
        ?><form method="post" class="downloadform" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="Maspik_spamlog_download_csv">
        <input type="submit" value="Download CSV" class="maspik-btn">
    </form><?php
}

function cfes_build_table() {
    global $wpdb;
    if(maspik_logtable_exists()){
        $table = maspik_get_logtable();

        // SQL query to select all rows from the table
        $sql = "SELECT * FROM $table ORDER BY id DESC";
        $results = $wpdb->get_results($sql, ARRAY_A);
        echo maspik_Download_log_btn();
        echo "<table class ='maspik-log-table'>";
        echo "<tr class='header-row'>
                <th class='maspik-log-column column-type'>Type</th>
                <th class='maspik-log-column column-value'>Value</th>
                <th class='maspik-log-column column-ip'>IP</th>
                <th class='maspik-log-column column-country'>Country</th>
                <th class='maspik-log-column column-agent'>User Agent</th>
                <th class='maspik-log-column column-date'>Date</th>
                <th class='maspik-log-column column-source'>Source</th>
            </tr>";

            $row_count = 0;
            foreach ($results as $row) {
                $row_class = ($row_count % 2 == 0) ? 'even' : 'odd';
                echo "<tr class='row-entries row-$row_class'>
                        <td class='column-type column-entries'>".esc_html($row['spam_type']) ."</td>
                        <td class='column-value column-entries'>
                            <div class = 'maspik-accordion-item'>
                                <div class='maspik-accordion-header log-accordion'>".esc_html($row['spam_value']). "
                                <span class='log-detail detail-show'></span>
                                </div>
                                <div class='log-detail maspik-accordion-content'><pre>".esc_html($row['spam_detail'])."</pre></div>
                            </div>
                        </td>
                        <td class='column-ip column-entries'>".esc_html($row['spam_ip'])."</td>
                        <td class='column-country column-entries'>".esc_html($row['spam_country'])."</td>
                        <td class='column-agent column-entries'>".esc_html($row['spam_agent'])."</td>
                        <td class='column-date column-entries'>".esc_html($row['spam_date'])."</td>
                        <td class='column-source column-entries'>".esc_html($row['spam_source'])."</td>
                    </tr>";

                $row_count++;
            }
            echo "</table>";

        if (!empty($results)) {}
    }
    
}


function efas_getRealIpAddr() {
    $default_ip_header = 'REMOTE_ADDR'; // Default header to fetch the IP address

    // If no custom header is provided or the IP is not valid, use the default REMOTE_ADDR header
    $ip_address = $_SERVER[$default_ip_header];

    // Validate the default IP address
    if (filter_var($ip_address, FILTER_VALIDATE_IP)) {
        return $ip_address;
    }

    // If validation fails, return a default value or handle it as needed
    return '127.0.0.1'; // You can customize this default value
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
function maspik_is_field_value_exist_in_string($bad_string, $field_value) {
    if (!$bad_string || !$field_value) {
        return false;
    }
    $bad_string_lower = strtolower(trim($bad_string));
    $field_value_lower = strtolower(trim($field_value));    
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
   
    if (!is_array($spamapi_option) || !cfes_is_supporting() || !isset($spamapi_option[$field])) {
        return false;
    }

    $api_field = $spamapi_option[$field];

    //$api_field = "";

    if ($type != "array") {
            // Keep the field value if it's not an array
            $api_field = is_array($spamapi_option[$field]) ? $spamapi_option[$field][0] :$spamapi_option[$field] ;
    } else {
        // Convert non-array fields to an array using efas_makeArray 
        $api_field = efas_makeArray($spamapi_option[$field],$type);
        // Remove empty values from Array
        $api_field = array_filter($api_field, function($value) {
            return !empty($value);
        });
    }

    return $api_field ? $api_field : false;
}

function maspik_is_contain_api($array) {
    $spamapi_option = get_option('spamapi');
    if ( !is_array($spamapi_option) ||  !cfes_is_supporting() || !is_array($array) ) {
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

    foreach ($langs as $lang) {
        if (preg_match("/$lang/u", $string)) {
            return $lang;
        }
    }

    return '';
}

function efas_array_of_lang_forbidden(){
  return array(
            '\p{Arabic}' => __('Arabic', 'contact-forms-anti-spam' ),
            '\p{Armenian}' => __('Armenian', 'contact-forms-anti-spam' ),
            '\p{Bengali}' => __('Bengali', 'contact-forms-anti-spam' ),
            '\p{Braille}' => __('Braille', 'contact-forms-anti-spam' ),
            '\p{Ethiopic}' => __('Ethiopic', 'contact-forms-anti-spam' ),
            '\p{Georgian}' => __('Georgian', 'contact-forms-anti-spam' ),
            '\p{Greek}' => __('Greek', 'contact-forms-anti-spam' ),
            '\p{Han}' => __('Han (Chinese)', 'contact-forms-anti-spam' ),
            '\p{Katakana}' => __('Katakana', 'contact-forms-anti-spam' ),
            '\p{Hiragana}' => __('Hiragana', 'contact-forms-anti-spam' ),
            '\p{Hebrew}' => __('Hebrew', 'contact-forms-anti-spam' ),
            '\p{Syriac}' => __('Syriac', 'contact-forms-anti-spam' ),
            '\p{Latin}' => __('Latin', 'contact-forms-anti-spam' ),
            '\p{Mongolian}' => __('Mongolian', 'contact-forms-anti-spam' ),
            '\p{Thai}' => __('Thai', 'contact-forms-anti-spam' ),
            '[À-ÿ]' => __('French (À-ÿ)', 'contact-forms-anti-spam'),
            '[ÄäÖöÜüß]' => __('German ([ÄäÖöÜüß])', 'contact-forms-anti-spam'),
            '[ÉÍÓÚáéíóúüÜñÑ]' => __('Spanish (ÁÉÍÓÚáéíóúüÜñÑ)', 'contact-forms-anti-spam'),
            '[A-Za-z]' => __('English (A-Za-z)', 'contact-forms-anti-spam'),
            '[ÀàÉéÈèÌìÍíÒòÓóÙùÚú]' => __('Italian (ÀàÉéÈèÌìÍíÒòÓóÙùÚú)', 'contact-forms-anti-spam'),
            '[А-Яа-яЁё]' => __('Russian (А-Яа-яЁ)', 'contact-forms-anti-spam' ),
            '\p{Unknown}' => __('Unknown language', 'contact-forms-anti-spam' ),
            '[À-ÖØ-öø-ÿ]' => __('Dutch (À-ÖØ-öø-ÿ)', 'contact-forms-anti-spam'),
            '[ÇçĞğİıÖöŞşÜü]' => __('Turkish (ÇçĞğİıÖöŞşÜü)', 'contact-forms-anti-spam'),
            '[ĄąĆćĘęŁłŃńÓóŚśŹźŻż]' => __('Polish (ĄąĆćĘęŁłŃńÓóŚśŹźŻż)', 'contact-forms-anti-spam'),
            '[ĂăÂâÎîȘșȚț]' => __('Romanian (ĂăÂâÎîȘșȚț)', 'contact-forms-anti-spam'),
            '[ÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž]' => __('Czech (ÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
            '[А-ЩЬЮЯЇІЄҐа-щьюяїієґЁё]' => __('Ukrainian (А-ЩЬЮЯЇІЄҐа-щьюяїієґЁё)', 'contact-forms-anti-spam'),
            '[ÁáÉéÍíÓóÚúÜüŐőŰű]' => __('Hungarian (ÁáÉéÍíÓóÚúÜüŐőŰ)', 'contact-forms-anti-spam'),
            '[ÄäÅåÖö]' => __('Swedish (ÄäÅåÖö)', 'contact-forms-anti-spam'),
            '[ÆæØøÅå]' => __('Danish (ÆæØøÅå)', 'contact-forms-anti-spam'),
            '[ÆæØøÅå]' => __('Norwegian (ÆæØøÅå)', 'contact-forms-anti-spam'),
            '[ÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž]' => __('Slovak (ÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
            '[А-Яа-яЋћĆć]' => __('Serbian (А-Яа-яЋћĆć)', 'contact-forms-anti-spam'),

     );
} 
function efas_array_of_lang_needed(){
  return array(
            '\p{Arabic}' => __('Arabic', 'contact-forms-anti-spam' ),
            '\p{Armenian}' => __('Armenian', 'contact-forms-anti-spam' ),
            '\p{Bengali}' => __('Bengali', 'contact-forms-anti-spam' ),
            '\p{Braille}' => __('Braille', 'contact-forms-anti-spam' ),
            '\p{Ethiopic}' => __('Ethiopic', 'contact-forms-anti-spam' ),
            '\p{Georgian}' => __('Georgian', 'contact-forms-anti-spam' ),
            '\p{Greek}' => __('Greek', 'contact-forms-anti-spam' ),
            '\p{Han}' => __('Han (Chinese)', 'contact-forms-anti-spam' ),
            '\p{Katakana}' => __('Katakana', 'contact-forms-anti-spam' ),
            '\p{Hiragana}' => __('Hiragana', 'contact-forms-anti-spam' ),
            '\p{Hebrew}' => __('Hebrew', 'contact-forms-anti-spam' ),
            '\p{Syriac}' => __('Syriac', 'contact-forms-anti-spam' ),
            '\p{Latin}' => __('Latin', 'contact-forms-anti-spam' ),
            '\p{Mongolian}' => __('Mongolian', 'contact-forms-anti-spam' ),
            '\p{Thai}' => __('Thai', 'contact-forms-anti-spam' ),
            '[A-Za-zÀ-ÿ]' => __('French (A-Za-zÀ-ÿ)', 'contact-forms-anti-spam'),
            '[A-Za-zÄäÖöÜüß]' => __('German ([A-Za-zÄäÖöÜüß])', 'contact-forms-anti-spam'),
            '[A-Za-zÁÉÍÓÚáéíóúüÜñÑ]' => __('Spanish (A-Za-zÁÉÍÓÚáéíóúüÜñÑ)', 'contact-forms-anti-spam'),
            '[A-Za-z]' => __('English (A-Za-z)', 'contact-forms-anti-spam'),
            '[A-Za-zÀàÉéÈèÌìÍíÒòÓóÙùÚú]' => __('Italian (A-Za-zÀàÉéÈèÌìÍíÒòÓóÙùÚú)', 'contact-forms-anti-spam'),
            '[А-Яа-яЁё]' => __('Russian (А-Яа-яЁ)', 'contact-forms-anti-spam' ),
            '\p{Unknown}' => __('Unknown language', 'contact-forms-anti-spam' ),
            '[A-Za-zÀ-ÖØ-öø-ÿ]' => __('Dutch (A-Za-zÀ-ÖØ-öø-ÿ)', 'contact-forms-anti-spam'),
            '[A-Za-zÇçĞğİıÖöŞşÜü]' => __('Turkish (A-Za-zÇçĞğİıÖöŞşÜü)', 'contact-forms-anti-spam'),
            '[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]' => __('Polish (A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż)', 'contact-forms-anti-spam'),
            '[A-Za-zĂăÂâÎîȘșȚț]' => __('Romanian (A-Za-zĂăÂâÎîȘșȚț)', 'contact-forms-anti-spam'),
            '[A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž]' => __('Czech (A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
            '[A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё]' => __('Ukrainian (A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё)', 'contact-forms-anti-spam'),
            '[A-Za-zÁáÉéÍíÓóÚúÜüŐőŰű]' => __('Hungarian (A-Za-zÁáÉéÍíÓóÚúÜüŐőŰ)', 'contact-forms-anti-spam'),
            '[A-Za-zÄäÅåÖö]' => __('Swedish (A-Za-zÄäÅåÖö)', 'contact-forms-anti-spam'),
            '[A-Za-zÆæØøÅå]' => __('Danish )A-Za-zÆæØøÅå)', 'contact-forms-anti-spam'),
            '[A-Za-zÆæØøÅå]' => __('Norwegian (A-Za-zÆæØøÅå)', 'contact-forms-anti-spam'),
            '[A-Za-zÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž]' => __('Slovak (A-Za-zÁáÄäČčĎďÉéÍíĹĺĽľŇňÓóÔôŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
            '[A-Za-zА-Яа-яЋћĆć]' => __('Serbian (A-Za-zА-Яа-яЋћĆć)', 'contact-forms-anti-spam'),

     );
} 
function efas_array_of_countries(){
  return Array(
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegovina',
		'BW' => 'Botswana',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CG' => 'Congo',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote D\'ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CD' => 'Democratic Republic of the Congo',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FO' => 'Faroe Islands',
		'FM' => 'Federated States Of Micronesia',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GN' => 'Guinea',
		'GW' => 'Guinea Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IM' => 'Isle of Man',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'MX' => 'Mexico',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'MD' => 'Republic Of Moldova',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'KN' => 'Saint Kitts And Nevis',
		'LC' => 'Saint Lucia',
		'VC' => 'Saint Vincent And The Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome And Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'ZA' => 'South Africa',
		'KR' => 'South Korea',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TO' => 'Tonga',
		'TT' => 'Trinidad And Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands British',
		'VI' => 'Virgin Islands U.S.',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);
} 

function maspik_is_plugin_active( $plugin ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $plugin );
}

function efas_array_supports_plugin(){
  $info = cfes_is_supporting() ? "" : "Pro"; 
  return array(
    'Contact form 7' => 0,
    'Elementor pro' => 0,
    'Wordpress Comments' => 0,
    'Wordpress Registration' => 0,
    'Formidable' => 0,
    'Forminator' => 0,
    'Fluentforms' => 0,
    'Bricks' => 0,
    'Ninjaforms'=> 0,
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

function maspik_if_plugin_is_active($plugin){

	if($plugin == 'Elementor pro'){
      return efas_if_plugin_is_active('elementor-pro') ;
    }else if($plugin == 'Contact form 7'){
      return  efas_if_plugin_is_active('contact-form-7');
    }else if($plugin == 'Woocommerce Review'){
      return efas_if_plugin_is_active('woocommerce');
    }else if($plugin == 'Woocommerce Registration'){
      return efas_if_plugin_is_active('woocommerce');
    }else if($plugin == 'Wpforms'){
	  return  efas_if_plugin_is_active('wpforms');
    }else if($plugin == 'Gravityforms'){
      return efas_if_plugin_is_active('gravityforms');
    }else if($plugin == 'Formidable'){
      return efas_if_plugin_is_active('formidable') ;
    }else if($plugin == 'Fluentforms'){
      return efas_if_plugin_is_active('fluentforms');
    }else if($plugin == 'Bricks'){
      return efas_if_plugin_is_active('bricks') ;
    }else if($plugin == 'Forminator'){
      return efas_if_plugin_is_active('forminator') ;
    }else if($plugin == 'Wordpress Registration'){
      return efas_if_plugin_is_active('Wordpress Registration') ;
    }else if($plugin == 'Ninjaforms'){
        return efas_if_plugin_is_active('ninjaforms') ;
    }else if($plugin == 'Wordpress Comments'){
      return 1;
    }else{
      return 0;
    }
}

function efas_if_plugin_is_affective($plugin , $status = "no"){

   

	if($plugin == 'Elementor pro'){
      return efas_if_plugin_is_active('elementor-pro') && maspik_get_settings( "maspik_support_Elementor_forms", 'form-toggle' ) != $status ;
    }else if($plugin == 'Contact form 7'){
      return  efas_if_plugin_is_active('contact-form-7') && maspik_get_settings( "maspik_support_cf7", 'form-toggle' ) != $status ;
    }else if($plugin == 'Woocommerce Review'){
      return efas_if_plugin_is_active('woocommerce') && cfes_is_supporting() && maspik_get_settings( "maspik_support_woocommerce_review", 'form-toggle' ) != $status ;
    }else if($plugin == 'Woocommerce Registration'){
      return efas_if_plugin_is_active('woocommerce') && cfes_is_supporting() && maspik_get_settings( "maspik_support_Woocommerce_registration", 'form-toggle' ) != $status;
    }else if($plugin == 'Wpforms'){
	  return  efas_if_plugin_is_active('wpforms') && cfes_is_supporting() && maspik_get_settings( "maspik_support_Wpforms", 'form-toggle' ) != $status  ;
    }else if($plugin == 'Gravityforms'){
      return efas_if_plugin_is_active('gravityforms') && cfes_is_supporting() && maspik_get_settings( "maspik_support_gravity_forms", 'form-toggle' ) != $status ;
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
    }else if($plugin == 'woocommerce'){
      return maspik_is_plugin_active( 'woocommerce/woocommerce.php');
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
    }else if($plugin == 'Wordpress Registration'){
      return get_option('users_can_register') == 1;
    }else{
      return 1;
    }
}

//Display admin notices 
function contact_forms_anti_spam_plugin_admin_notice(){
    $screen = get_current_screen();
    if ( $screen->id !== 'toplevel_page_contact-forms-anti-spam') return; 
            ?><div class="notice notice-warning is-dismissible">
                <p><?php _e('Use this plugin with caution and only if you understand the risk, blacklisting some words can lead to the termination of valid leads.', 'contact-forms-anti-spam') ?></p>
            </div><?php
}
add_action( 'admin_notices', 'contact_forms_anti_spam_plugin_admin_notice' );

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

// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
add_filter( 'cron_schedules', 'contact_forms_anti_spam_add_weekly' );
function contact_forms_anti_spam_add_weekly( $schedules ) {
    // add a 'weekly' schedule to the existing set
  if(!isset($schedules["daily"])){
      $schedules[ 'daily' ] = array( 
          'interval' => 60 * 60 * 24,
          'display' => __( 'daily' )
      );
    }
  	if(!isset($schedules["weekly"])){
      $schedules[ 'weekly' ] = array( 
          'interval' => 60 * 60 * 24 * 7,
          'display' => __( 'Weekly' )
      );
    }

    return $schedules;
}
// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'contact_forms_anti_spam_add_weekly' ) ) {
    wp_schedule_event( time(), 'weekly', 'contact_forms_anti_spam_add_weekly' );
}
// Hook into that action that'll fire every five minutes
add_action( 'contact_forms_anti_spam_add_weekly', 'cfas_refresh_api' );


function cfes_is_supporting() {

	if ( function_exists( 'maspik_license_checker' ) ) {
		try {
			if ( maspik_license_checker()->license()->isLicenseValid() ) {
				return 1;
			}
		} catch ( \Exception $e ) {
			error_log( 'Error happened: ' . $e->getMessage() );
		}
	}

	return 0;
}
add_action('after_setup_theme', 'cfes_is_supporting');


function cfas_refresh_api() {
    if (!cfes_is_supporting()) {
        return;
    }

    $private_file_id = maspik_get_settings('private_file_id');
    $domain = $_SERVER['SERVER_NAME'];

    // Initialize $file as an empty array
    $file = array();

    // Check if the first API is available and fetch data
    if (!empty($private_file_id)) {
        $Api_file = "https://wpmaspik.com/wp-json/acf/v3/apis/$private_file_id";
        $fileContent = file_get_contents("$Api_file?num=2367816&site=$domain");
        if ($fileContent !== false) {
            $file = json_decode($fileContent, true);
            $file = $file['acf'] ?? array();
        }
    }

    // Initialize $combinedAPI with the data from the first API
    $combinedAPI = $file;

    // Check if the second API should be accessed
    $popular_spam = maspik_get_settings("popular_spam"); 
    if ($popular_spam) {
        $Api_popular_spam_file = "https://wpmaspik.com/wp-json/acf/v3/options/public_api?num=234442&site=$domain";
        $popularSpamContent = file_get_contents($Api_popular_spam_file);
        if ($popularSpamContent !== false) {
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
        echo "<script>alert('You have the most new version already.');</script>";
    } else {
        update_option('spamapi' , $newAPI); 
        echo "<script>alert('New version applied successfully.');</script>";
    }
}

function maspik_toggle_match($data){
    //text
    if($data == "MaxCharactersInTextField"){
        return "text_limit_toggle";
    }elseif($data == "custom_error_message_MaxCharactersInTextField"){
        return "text_custom_message_toggle";

    //textarea
    }elseif($data == "MaxCharactersInTextAreaField"){
        return "textarea_limit_toggle";
    }elseif($data == "contain_links"){
        return "textarea_link_limit_toggle";
    }elseif($data == "custom_error_message_MaxCharactersInTextAreaField"){
        return "textarea_custom_message_toggle";

    //phone
    }elseif($data == "MaxCharactersInPhoneField"){
        return "tel_limit_toggle";
    }elseif($data == "custom_error_message_MaxCharactersInPhoneField"){
        return "phone_limit_custom_message_toggle";
    }elseif($data == "custom_error_message_tel_formats"){
        return "phone_custom_message_toggle";

    //Language
    }elseif($data == "lang_needed"){
        return "lang_need_custom_message_toggle";
    }elseif($data == "lang_forbidden"){
        return "lang_forbidden_custom_message_toggle";

    //country
    }elseif($data == "country_blacklist"){
        return "country_custom_message_toggle";

    }else{
        return "";
    }
}


/*
// old //
 function cfas_get_error_text($field = "error_message") {
    
    $textAPI = efas_get_spam_api('error_message') ? efas_get_spam_api('error_message') : false;
    if( efas_get_spam_api( "custom_error_message_$field") ){
        $textAPI = efas_get_spam_api("custom_error_message_$field") ? efas_get_spam_api("custom_error_message_$field") : $textAPI ;
    }

    if( maspik_check_table( "custom_error_message_$field") && maspik_get_settings( maspik_toggle_match($field))  == 1 ){
        if(maspik_get_settings( "custom_error_message_$field") != ""){
            $text =  maspik_get_settings( "custom_error_message_$field") ? maspik_get_settings( "custom_error_message_$field" ) : $textAPI;
        }else{
            $text = maspik_get_settings( "error_message" ) ? maspik_get_settings( "error_message" ) : $textAPI;
        }
    }else{
            $text = maspik_get_settings( "error_message" ) ? maspik_get_settings( "error_message" ) : $textAPI;
    }
    $text = $text ? $text : __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
    return  sanitize_text_field($text) : 
}*/

function cfas_get_error_text($field = "error_message") {
    $default_text = __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
     $textAPI = efas_get_spam_api("custom_error_message_$field",$type = "text") ? efas_get_spam_api("custom_error_message_$field",$type = "text") : efas_get_spam_api('error_message',$type = "text");
    if (maspik_check_table("custom_error_message_$field") && maspik_get_settings(maspik_toggle_match($field)) == 1) {
        $text = maspik_get_settings("custom_error_message_$field") ? maspik_get_settings("custom_error_message_$field") : $textAPI;
    } else {
        $text = maspik_get_settings("error_message") ? maspik_get_settings("error_message") : $textAPI;
    }
    $text = $text ? $text : $default_text;
    return sanitize_text_field($text);
}

function get_maspik_footer(){
    ?> 
    <footer class="maspik-footer" style="background: #FFBBA6;padding: 20px;text-align: center;margin-top: 30px;border-radius: 20px;">
        <h3><?php _e('DO YOU LIKE MASPIK?', 'contact-forms-anti-spam'); ?></h3>
        <?php echo __('Please, ', 'contact-forms-anti-spam') . '<a href="https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post" target="_blank">' . __('Give us 5 stars', 'contact-forms-anti-spam') . '</a>.'; ?>
        <h3><?php _e('Join Our Facebook Community!', 'contact-forms-anti-spam'); ?></h3>
        <p><?php echo __('Ask questions, share spam examples, get ideas on how to block them, share feedback, and suggest new features. Join us at ', 'contact-forms-anti-spam') . '<a href="https://www.facebook.com/groups/maspik" target="_blank">' . __('WP Maspik Community - Stopping Spam Together', 'contact-forms-anti-spam') . '</a>.'; ?></p>
        <p><?php _e('Need support? Do you have ideas on how to improve MASPIK?', 'contact-forms-anti-spam'); ?><br>
        <?php echo __('We would love to hear from you at', 'contact-forms-anti-spam') . ' <a href="mailto:hello@wpmaspik.com" target="_blank">hello@wpmaspik.com</a>'; ?></p>

    </footer>
    <?php
}

add_filter( 'admin_body_class', 'cfas_admin_classes' );
function cfas_admin_classes( $classes ) {
      $screen = get_current_screen()->id;
    if ( strpos($screen, 'maspik') !== false ){
        $classes .=  cfes_is_supporting() ? "maspik-pro" : false;
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

function CountryCheck($ip, &$spam, &$reason) {
    $message = 0;
    $opt_value = maspik_get_dbvalue();
    $ip_blacklist = maspik_get_settings('ip_blacklist') ? efas_makeArray(maspik_get_settings('ip_blacklist')) : array();
    $AllowedOrBlockCountries = maspik_get_settings('AllowedOrBlockCountries') == 'allow' ? 'allow' : 'block';
    $country_blacklist_array = $data =  maspik_get_settings('country_blacklist','select');
    foreach($country_blacklist_array as $value){
        $cleanval = trim($value -> $opt_value);
        if(!empty($cleanval)){
            $country_blacklist = explode(" ", $value -> $opt_value);
        }else{
            $country_blacklist = array();
        }
    }

        // Countries API
    if (efas_get_spam_api('country_blacklist') && efas_get_spam_api('AllowedOrBlockCountries',"string") != 'ignore') {
        $countries_blacklist_api = efas_get_spam_api('country_blacklist');
        $AllowedOrBlockCountries = efas_get_spam_api('AllowedOrBlockCountries',"string");
        $country_blacklist = $countries_blacklist_api;
    }

    

    // Check country blacklist only if is pro user
    if( cfes_is_supporting() ){ 
    $xml_data = @file_get_contents("http://www.geoplugin.net/xml.gp?ip=" . $ip);
        if ($xml_data) {
            $xml = simplexml_load_string($xml_data);
            $countryCode = $xml && $xml->geoplugin_countryCode ? (string) $xml->geoplugin_countryCode : false;

            if ($countryCode && in_array($countryCode, $country_blacklist) && $AllowedOrBlockCountries === 'block' ) {
                $spam = true;
                $message = "country_blacklist";
                $reason = "Country code $countryCode is blacklisted ($AllowedOrBlockCountries)";
                return array('spam' => $spam, 'reason' => $reason, 'message' => $message);
            }
            if ($AllowedOrBlockCountries === 'allow' && !in_array($countryCode, $country_blacklist) && !empty(maspik_get_settings('country_blacklist'))) {
                $spam = true;
                $message = "country_blacklist";
                $reason = "Country $countryCode is not in the whitelist ($AllowedOrBlockCountries)";
                return array('spam' => $spam, 'reason' => $reason, 'message' => $message);
            }
        }
    }

    // Check IP blacklist
    if (in_array($ip, $ip_blacklist)) {
        $spam = true;
        $reason = "IP $ip is blacklisted";
        return array('spam' => $spam, 'reason' => $reason, 'message' => "ip_blacklist");
    }

    // CIDR Filter
    foreach ($ip_blacklist as $cidr) {
        if (ip_is_cidr($cidr) && cidr_match($ip, $cidr)) {
            $spam = true;
            $reason = "IP $ip is in CIDR: $cidr";
            return array('spam' => $spam, 'reason' => $reason, 'message' => "ip_blacklist");
        }
    }
    
    // AbuseIPDB API  (Thanks to @josephcy95)
      $abuseipdb_api = maspik_get_settings('abuseipdb_api') ? maspik_get_settings('abuseipdb_api') : false;
      $pabuseipdb_score = maspik_get_settings('abuseipdb_score');
      //Check if have abuseipdb_api in the API Setting page (WpMaspik)
      if ( efas_get_spam_api('abuseipdb_api') ){
        $abuseipdb_api_json = null !== efas_get_spam_api('abuseipdb_api') ? efas_get_spam_api('abuseipdb_api') : false;
        $abuseipdb_api = $abuseipdb_api ? $abuseipdb_api : $abuseipdb_api_json; // Site setting is stronger
        $abuseipdb_score_json = null !== efas_get_spam_api('abuseipdb_score') ? efas_get_spam_api('abuseipdb_score') : '50';
        $pabuseipdb_score = $pabuseipdb_score ? $pabuseipdb_score : $abuseipdb_score_json; // Site setting is stronger
      }

      if (($abuseipdb_api != false) && !$spam  && $pabuseipdb_score > 10) { // $pabuseipdb_score > 10 for more save
        $abuseconfidencescore = check_abuseipdb($ip);
        if ($abuseconfidencescore && $abuseconfidencescore >= (int)$pabuseipdb_score) {
          $spam = true;
          $reason = "AbuseIPDB Risk: $abuseconfidencescore ";
          return array('spam' => $spam, 'reason' => $reason, 'message' => "abuseipdb_api");

        }
      }

    // Proxycheck.io Risk Check  (Thanks to @josephcy95)
      $proxycheck_io_api = maspik_get_settings('proxycheck_io_api') ? maspik_get_settings('proxycheck_io_api') : false;
      $proxycheck_io_risk = maspik_get_settings('proxycheck_io_risk');
      //Check if have proxycheck_io_api in the API Setting page (WpMaspik)
      if ( null !== efas_get_spam_api('proxycheck_io_api') ){
        $proxycheck_io_api_json = is_array( efas_get_spam_api('proxycheck_io_api') ) ? efas_get_spam_api('proxycheck_io_api')[0] : false;
        $proxycheck_io_risk_json = is_array( efas_get_spam_api('proxycheck_io_risk') ) ? efas_get_spam_api('proxycheck_io_risk')[0] : false;
        $proxycheck_io_api = $proxycheck_io_api ? $proxycheck_io_api : $proxycheck_io_api_json; // Site setting is stronger
        $proxycheck_io_risk = $proxycheck_io_risk ? $proxycheck_io_risk : $proxycheck_io_risk_json; // Site setting is stronger
      }

      if ($proxycheck_io_risk && $proxycheck_io_api && !$spam && (int)$proxycheck_io_risk > 10 ) {
        $proxycheck_io_riskscore = check_proxycheckio($ip);
        if ( $proxycheck_io_riskscore && $proxycheck_io_riskscore >= (int)$proxycheck_io_risk) {
          $spam = true;
          $reason = "Proxycheck.io Risk: $proxycheck_io_riskscore max is $proxycheck_io_risk";
          return array('spam' => $spam, 'reason' => $reason, 'message' => "proxycheck_io_api");
        }
      }

    return array('spam' => $spam, 'reason' => $reason, 'message' => $message);
}

function checkEmailForSpam($field_value) {
    // Check if the field is empty
    if (!$field_value) {
        return false; // Not spam if the field is empty.
    }

    // Get the emails blacklist
    $emails_blacklist = efas_makeArray(maspik_get_settings('emails_blacklist'));

    // Check if there are additional blacklist entries from the spam API
    if (efas_get_spam_api('email_field')) {
        $additional_blacklist = efas_get_spam_api('email_field');
        $emails_blacklist = array_merge($emails_blacklist, $additional_blacklist);
    }
    // Convert the field value to lowercase for case-insensitive comparison
    $field_value_lower = strtolower($field_value);

    // Extract the domain part of the email address
    $email_parts = explode('@', $field_value_lower);
    $email_domain = end($email_parts);

    // Loop through the blacklist entries
    foreach ($emails_blacklist as $bad_string) {
        // Skip empty or whitespace strings
        if (empty(trim($bad_string))) {
            continue;
        }

        // Convert the blacklist string to lowercase for case-insensitive comparison
        $bad_string_lower = trim(strtolower($bad_string));
        
        if (strpos($bad_string_lower, '*') !== false) {
            // V - Check for wildcard pattern using strpos
            if (fnmatch($bad_string_lower, $field_value_lower, FNM_CASEFOLD)) {
                return "because wildcard pattern = $bad_string is in the black list";
            }
        } elseif (isset($bad_string_lower[0]) && $bad_string_lower[0] === "/") {
            // V -Check for regular expression patterns
            if (preg_match("$bad_string_lower", $field_value_lower)) {
                return "because regular expression pattern '$bad_string' is in the blacklist";
            }
        } elseif (isset($bad_string[0]) && $bad_string[0] === "@") {
            // V - Check for exact match of the email domain
            if ($bad_string_lower[0] === '@' && $email_domain === substr($bad_string_lower, 1)) {
                return "because spam email domain '$bad_string' is in the blacklist";
            }
        } else {
            // V - Check for exact match 
            $spam = maspik_is_field_value_equal_to_string($bad_string, $field_value) ? "because email '$bad_string' is in the blacklist" : false;
            if ($spam) {
                return $spam;
            }
        }

    }

    return false;
}


function checkTelForSpam($field_value) {
    $valid = false; 
    $tel_formats = maspik_get_settings('tel_formats');

    $MaxCharacters = maspik_get_settings('MaxCharactersInPhoneField');
    $MinCharacters = maspik_get_settings('MinCharactersInPhoneField');

    if (maspik_get_settings('phone_limit_custom_message_toggle') == 1) {
        $message = 'MaxCharactersInPhoneField';
    } else {
        $message = '';
    }

    // Check if the maximum character limit is valid  
    if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
        $CountCharacters = mb_strlen(strval($field_value)); // Use mb_strlen for multibyte characters
        if (maspik_get_settings(maspik_toggle_match('MaxCharactersInPhoneField')) == 1) {
            if ($CountCharacters > $MaxCharacters) {
                $reason = "More than $MaxCharacters characters in Phone Number";
                return array('valid' => false, 'reason' => $reason, 'message' => $message);
            } elseif ($CountCharacters < $MinCharacters) {
                $reason = "Less than $MinCharacters characters in Phone Number";
                return array('valid' => false, 'reason' => $reason, 'message' => $message);
            }
        }
    }

    if (empty($tel_formats)) {
        return array('valid' => true, 'reason' => 'Empty formats', 'message' => 'Empty formats');
    }

    $tel_formats = explode("\n", str_replace("\r", "", $tel_formats));
    $reason = "Phone number " . $field_value . " does not meet the given format. ";

    foreach ($tel_formats as $format) {
        $format = trim($format);
        if (empty($format)) {
            continue;
        }
        // Regular expression format
        if (strpos($format, '/') === 0) {
            if (@preg_match($format, '') === false) {
                $reason .= "Invalid regular expression: $format. ";
                continue;
            }

            if (preg_match($format, $field_value)) {
                return array('valid' => true, 'reason' => "Regular expression match: $format", 'message' => 'tel_formats');
            }
        } 
        // Wildcard pattern
        elseif (strpbrk($format, '*?') !== false) {
            if (fnmatch($format, $field_value, FNM_CASEFOLD)) {
                return array('valid' => true, 'reason' => "Wildcard pattern match: $format", 'message' => 'tel_formats');
            }
        } 
    }

    return array('valid' => $valid, 'reason' => $reason, 'message' => 'tel_formats');
}

// validate Text Field
function validateTextField($field_value) {  
    // Convert the field value to lowercase.
    $field_value = strtolower($field_value);
  	$text_blacklist = maspik_get_settings( 'text_blacklist' ) ? efas_makeArray(maspik_get_settings('text_blacklist') ) : array();
	$spam = false;
  	if ( efas_get_spam_api() ){
    	$text_blacklist_json =  efas_get_spam_api();
      	$text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
    }  

    // Check for exact string matches and wildcard patterns in the blacklist.
    if (is_array($text_blacklist)) {
        foreach ($text_blacklist as $bad_string) {
            if ( empty($bad_string) ) {
               continue;
            }
            $bad_string = trim(strtolower($bad_string));
 			if (strpos($bad_string, '*') !== false) {
                // Handle wildcard pattern using fnmatch
                if (fnmatch($bad_string, $field_value, FNM_CASEFOLD)) {
                    $spam = "Input $field_value is blocked by wildcard pattern";
                    return array('spam' => $spam, 'message' => "text_blacklist");
                  	break;
                }
            } else {
                // Check if exist in string 
                if (maspik_is_field_value_exist_in_string($bad_string, $field_value) ) {
                    $spam =  "Forbidden input $field_value, because <u>$bad_string</u> is blocked";
                    return array('spam' => $spam, 'message' => "text_blacklist");
                   	break;
                }
            }
        }
    }

    // Get the maximum character limit from the spam API or options
    $MaxCharacters = efas_get_spam_api('MaxCharactersInTextField') ? efas_get_spam_api('MaxCharactersInTextField',$type = "number") : false;
    if ( (empty($MaxCharacters) || $MaxCharacters < 3 ) || maspik_get_settings('MaxCharactersInTextField') >= 30) {
        $MaxCharacters = maspik_get_settings('MaxCharactersInTextField');
        $MinCharacters = maspik_get_settings('MinCharactersInTextField');
    }
    if(maspik_get_settings('text_custom_message_toggle')== 1){
        $message = 'MaxCharactersInTextField';
    }else{
        $message = '';
    }
    // Check if the maximum character limit is valid
    if(maspik_get_settings(maspik_toggle_match('MaxCharactersInTextField')) == 1  || maspik_is_contain_api(['MaxCharactersInTextField', 'MinCharactersInTextField'])){
        if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
            $CountCharacters = mb_strlen($field_value); // Use mb_strlen for multibyte characters
            if ($CountCharacters > $MaxCharacters ) {
                $spam = "More than $MaxCharacters characters";
                return array('spam' => $spam, 'message' => $message);
            }

            if ($CountCharacters < $MinCharacters ) {
                $spam = "Less than $MinCharacters characters";
                return array('spam' => $spam, 'message' => $message);
            }
        }
    }

	return false;
}


//Textarea - check Textarea For Spam Function
function checkTextareaForSpam($field_value) {
    
    // Get the blacklist from options and merge with API data if available


    $textarea_blacklist = maspik_get_settings('textarea_blacklist') ? efas_makeArray(maspik_get_settings('textarea_blacklist')) : array();
    if (efas_get_spam_api('textarea_field')) {
        $blacklist_json = efas_get_spam_api('textarea_field');
        $textarea_blacklist = array_merge($textarea_blacklist, $blacklist_json);
    }
    
    foreach ($textarea_blacklist as $bad_string) {
        
        if (!empty($field_value) && $bad_string[0] === "[") {
            // Handle special cases for shortcodes
            $search = array('[', ']');
            $bad_string = str_replace($search, "", $bad_string);
            $bad_string = "url" || "name" || "description" ? get_bloginfo($bad_string) : "Error - Shortcode not exist";
        }
        if ( maspik_is_field_value_exist_in_string($bad_string, $field_value) ) {
            return array('spam' => "field value includes <u>$bad_string</u>", 'message' => "textarea_field");
        }
    }

    // only if pro user
    if ( cfes_is_supporting() ) {
        // Check for required language

        $opt_value = maspik_get_dbvalue();
        

        $lang_need_array = $data = maspik_get_settings('lang_needed','select' );
        foreach($lang_need_array as $value){
            $cleanval = trim($value -> $opt_value);
            if(!empty($cleanval)){
                $lang_needed = explode(" ", $value -> $opt_value);
            }
        }


        if (efas_get_spam_api('lang_needed')) {
            $blacklist_json = efas_get_spam_api('lang_needed');
            $lang_needed = array_merge($lang_needed, $blacklist_json);
        }

        if( !empty($lang_needed) ){
        
            $missing_lang = maspik_detect_language_in_string($lang_needed, $field_value);

            if ($lang_needed && empty($missing_lang)) {
                $listofNeededlanguage = implode(", ",$lang_needed);
                return array('spam' => "Needed language is missing ($listofNeededlanguage)", 'message' => "lang_needed");
            }
        }

        

        // Check for forbidden language

        $lang_x_array = maspik_get_settings('lang_forbidden', 'select' );
        $lang_forbidden = array(); // Initialize as an empty array

        foreach($lang_x_array as $value){
            $cleanval = trim($value->$opt_value);
            if(!empty($cleanval)){
                $lang_forbidden = explode(" ", $value->$opt_value);
            }
        }

        if (efas_get_spam_api('lang_forbidden')) {
            $blacklist_json = efas_get_spam_api('lang_forbidden');
            $lang_forbidden = array_merge($lang_forbidden, $blacklist_json);
        }
        // Remove empty values from blacklist_json after merging
        $lang_forbidden = array_filter($lang_forbidden, function($value) {
            return !empty($value);
        });

        if( !empty($lang_forbidden) ){
            $detected_forbidden_lang = maspik_detect_language_in_string($lang_forbidden, $field_value);

            if (!empty($detected_forbidden_lang)) {
                return array('spam' => "Forbidden language '$detected_forbidden_lang' exists", 'message' => "lang_forbidden");
            }
        }
            
    }
    
    $max_links = maspik_get_settings('contain_links');
    if (efas_get_spam_api('contain_links',$type = "bool")) {
        $max_links_api = efas_get_spam_api('contain_links',$type = "bool") ? efas_get_spam_api('contain_links',$type = "bool") : false;
        $max_links = $max_links ? $max_links : $max_links_api;
    }
    // Check for maximum number of links
    if ($max_links >= 0) {
        $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        $num_links = preg_match_all($reg_exUrl, $field_value);
        if ( $num_links > $max_links ) {
            return array('spam' => "Contains <u>more than $max_links links</u>", 'message' => "contain_links");
        }
    }
    
    // Get the maximum character limit from the spam API or options
    if(is_array(efas_get_spam_api('MaxCharactersInTextAreaField',$type = "number"))){
        $MaxCharacters = efas_get_spam_api('MaxCharactersInTextAreaField',$type = "number");
    }

    if ( (empty($MaxCharacters) || $MaxCharacters < 3 ) || maspik_get_settings('MaxCharactersInTextAreaField') >= 30) {
        $MaxCharacters = maspik_get_settings('MaxCharactersInTextAreaField');
        $MinCharacters = maspik_get_settings('MinCharactersInTextAreaField');
    }


    if(maspik_get_settings('textarea_custom_message_toggle')== 1){
        $message = 'MaxCharactersInTextAreaField';
    }else{
        $message = '';
    }


    // Check if the maximum character limit is valid
    if (maspik_get_settings(maspik_toggle_match('MaxCharactersInTextAreaField')) == 1 || maspik_is_contain_api(['MaxCharactersInTextAreaField', 'MinCharactersInTextAreaField'])) {
        if (is_numeric($MaxCharacters) && $MaxCharacters > 3) {
            $CountCharacters = mb_strlen($field_value); // Use mb_strlen for multibyte characters
            if ($CountCharacters > $MaxCharacters) {
                $spam = "More than $MaxCharacters characters in Text Area field.";
                return array('spam' => $spam, 'message' =>  $message);
            }elseif ($CountCharacters < $MinCharacters) {
                $spam = "Less than $MinCharacters characters in Text Area field.";
                return array('spam' => $spam, 'message' =>  $message);
            }
        }
    }


    // No spam found in this field
    return false;
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
                <?php _e('We want to keep improving <b>Maspik plugin</b> - please allow us to track usage, we only collect non-sensitive information.', 'contact-forms-anti-spam'); ?>
                <button id="allow-sharing-button" class="button button-primary"> <?php _e('Allow', 'contact-forms-anti-spam'); ?></button>
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
        wp_die(__('Permission error', 'contact-forms-anti-spam'));
    }

    // Update option
    //update_option('shere_data', 1);
    maspik_save_settings('shere_data', '1');
    

    wp_die(); // Always use wp_die() at the end of an AJAX callback
}

// AJAX callback function to dismiss the notice
add_action('wp_ajax_Maspik_dismiss_notice_action', 'Maspik_dismiss_notice_callback');
function Maspik_dismiss_notice_callback() {
    set_transient('Mapik_dismissed_shereing_notice', true, MONTH_IN_SECONDS); // Set the transient to dismiss the notice
    wp_die();
}

function  maspik_add_country_to_submissions($linebreak = "<br>") {
    $ip =  efas_getRealIpAddr();
    $countryName = "Other (Unknown)";
    $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);
    $countryName = $xml->geoplugin_countryName ? $xml->geoplugin_countryName : $countryName;
    $sanitizedCountryName = sanitize_text_field($countryName);
    if ($xml && $sanitizedCountryName) {
      return " $linebreak Maspik:$linebreak Country: $sanitizedCountryName  - IP: $ip ";
    }
  return "";
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
    $maspik_settings = array(
        'text_blacklist' => maspik_get_settings('text_blacklist'),
        'text_limit_toggle' => maspik_get_settings('text_limit_toggle'),
        'text_custom_message_toggle' => maspik_get_settings('text_custom_message_toggle'),
        'MinCharactersInTextField' => maspik_get_settings('MinCharactersInTextField'),
        'MaxCharactersInTextField' => maspik_get_settings('MaxCharactersInTextField'),
        'custom_error_message_MaxCharactersInTextField' => maspik_get_settings('custom_error_message_MaxCharactersInTextField'),
        'emails_blacklist' => maspik_get_settings('emails_blacklist'),
        'textarea_blacklist' => maspik_get_settings('textarea_blacklist'),
        'textarea_limit_toggle' => maspik_get_settings('textarea_limit_toggle'),
        'textarea_link_limit_toggle' => maspik_get_settings('textarea_link_limit_toggle'),
        'textarea_custom_message_toggle' => maspik_get_settings('textarea_custom_message_toggle'),
        'MinCharactersInTextAreaField' => maspik_get_settings('MinCharactersInTextAreaField'),
        'MaxCharactersInTextAreaField' => maspik_get_settings('MaxCharactersInTextAreaField'),
        'contain_links' => maspik_get_settings('contain_links'),
        'custom_error_message_MaxCharactersInTextAreaField' => maspik_get_settings('custom_error_message_MaxCharactersInTextAreaField'),
        'tel_formats' => maspik_get_settings('tel_formats'),
        'tel_limit_toggle' => maspik_get_settings('tel_limit_toggle'),
        'MinCharactersInPhoneField' => maspik_get_settings('MinCharactersInPhoneField'),
        'MaxCharactersInPhoneField' => maspik_get_settings('MaxCharactersInPhoneField'),
        'phone_custom_message_toggle' => maspik_get_settings('phone_custom_message_toggle'),
        'custom_error_message_tel_formats' => maspik_get_settings('custom_error_message_tel_formats'),
        'lang_needed' => maspik_get_settings('lang_needed'),
        'lang_need_custom_message_toggle' => maspik_get_settings('lang_need_custom_message_toggle'),
        'custom_error_message_lang_needed' => maspik_get_settings('custom_error_message_lang_needed'),
        'lang_forbidden' => maspik_get_settings('lang_forbidden'),
        'lang_forbidden_custom_message_toggle' => maspik_get_settings('lang_forbidden_custom_message_toggle'),
        'custom_error_message_lang_forbidden' => maspik_get_settings('custom_error_message_lang_forbidden'),
        'country_blacklist' => maspik_get_settings('country_blacklist'),
        'AllowedOrBlockCountries' => maspik_get_settings('AllowedOrBlockCountries'),
        'country_custom_message_toggle' => maspik_get_settings('country_custom_message_toggle'),
        'custom_error_message_country_blacklist' => maspik_get_settings('custom_error_message_country_blacklist'),
        'private_file_id' => maspik_get_settings('private_file_id'),
        'popular_spam' => maspik_get_settings('popular_spam'),
        'NeedPageurl' => maspik_get_settings('NeedPageurl'),
        'ip_blacklist' => maspik_get_settings('ip_blacklist'),
        'error_message' => maspik_get_settings('error_message'),
        'abuseipdb_api' => maspik_get_settings('abuseipdb_api'),
        'abuseipdb_score' => maspik_get_settings('abuseipdb_score'),
        'proxycheck_io_api' => maspik_get_settings('proxycheck_io_api'),
        'proxycheck_io_risk' => maspik_get_settings('proxycheck_io_risk'),
        'maspik_support_Elementor_forms' => maspik_get_settings('maspik_support_Elementor_forms'),
        'maspik_support_cf7' => maspik_get_settings('maspik_support_cf7'),
        'maspik_support_woocommerce_review' => maspik_get_settings('maspik_support_woocommerce_review'),
        'maspik_support_Woocommerce_registration' => maspik_get_settings('maspik_support_Woocommerce_registration'),
        'maspik_support_Wpforms' => maspik_get_settings('maspik_support_Wpforms'),
        'maspik_support_gravity_forms' => maspik_get_settings('maspik_support_gravity_forms'),
        'maspik_support_formidable_forms' => maspik_get_settings('maspik_support_formidable_forms'),
        'maspik_support_fluentforms_forms' => maspik_get_settings('maspik_support_fluentforms_forms'),
        'maspik_support_bricks_forms' => maspik_get_settings('maspik_support_bricks_forms'),
        'maspik_support_forminator_forms' => maspik_get_settings('maspik_support_forminator_forms'),
        'maspik_support_registration' => maspik_get_settings('maspik_support_registration'),
        'maspik_support_wp_comment' => maspik_get_settings('maspik_support_wp_comment'),
        'shere_data' => maspik_get_settings('shere_data'),
        'maspik_Store_log' => maspik_get_settings('maspik_Store_log'),
        'spam_log_limit' => maspik_get_settings('spam_log_limit'),
        'add_country_to_emails' => maspik_get_settings('add_country_to_emails'),
        'disable_comments' => maspik_get_settings('disable_comments'),
        'phone_limit_custom_message_toggle' => maspik_get_settings('phone_limit_custom_message_toggle'),
        'maspik_ninjaforms' => maspik_get_settings('maspik_ninjaforms'),
        'maspik_support_ninjaforms' => maspik_get_settings('maspik_support_ninjaforms'),
    );
    // Get domain name of the site
    $domain_name = get_site_url();

    // Custom string
    $custom_string = "OnlyYouKnowWhatIsGoodForYou";

    // Convert settings array to JSON
    $json_data = json_encode($maspik_settings);

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

    // Define an array of options
$options = array(
    'text_blacklist',
    'text_limit_toggle',
    'text_custom_message_toggle',
    'MinCharactersInTextField',
    'MaxCharactersInTextField',
    'custom_error_message_MaxCharactersInTextField',
    'emails_blacklist',
    'textarea_blacklist',
    'textarea_limit_toggle',
    'textarea_link_limit_toggle',
    'textarea_custom_message_toggle',
    'MinCharactersInTextAreaField',
    'MaxCharactersInTextAreaField',
    'contain_links',
    'custom_error_message_MaxCharactersInTextAreaField',
    'tel_formats',
    'tel_limit_toggle',
    'MinCharactersInPhoneField',
    'MaxCharactersInPhoneField',
    'phone_custom_message_toggle',
    'custom_error_message_tel_formats',
    'lang_needed',
    'lang_need_custom_message_toggle',
    'custom_error_message_lang_needed',
    'lang_forbidden',
    'lang_forbidden_custom_message_toggle',
    'custom_error_message_lang_forbidden',
    'country_blacklist',
    'AllowedOrBlockCountries',
    'country_custom_message_toggle',
    'custom_error_message_country_blacklist',
    'private_file_id',
    'popular_spam',
    'NeedPageurl',
    'ip_blacklist',
    'error_message',
    'abuseipdb_api',
    'abuseipdb_score',
    'proxycheck_io_api',
    'proxycheck_io_risk',
    'phone_limit_custom_message_toggle',
);

    // Iterate over each option
    foreach ($options as $option) {
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
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
