<?php
/*
* Main function
*/

function maspik_is_plugin_active( $plugin ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $plugin );
}



function efas_get_browser_name($user_agent)
{
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

function efas_add_to_log($type = '', $input = '', $post = null, $source = "Elementor forms") {
    if (get_option('maspik_Store_log') == "no") {
        return false;
    }

    $errorlog = get_option('errorlog');
    $errorlog = empty($errorlog) ? [] : json_decode($errorlog, true);

    // Sanitize and escape user inputs
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

    $log_entry = [
        'Type' => sanitize_text_field($type),
        'value' => "<b>" . sanitize_text_field($input) . "</b><br> Data: <pre>{$text}</pre>",
        'Ip' => sanitize_text_field($ip),
        'Country' => sanitize_text_field($countryName),
        'User agent' => sanitize_text_field($browser_name),
        'Date' => sanitize_text_field($date),
        'Source' => sanitize_text_field($source)
    ];

    if (is_array($errorlog) && count($errorlog) > 100) {
        $errorlog = array_slice($errorlog, -100, null, true);
    }

    $errorlog[] = $log_entry;
    update_option('errorlog', wp_json_encode($errorlog), false);
}

function cfes_build_table($array) {
    // start table
    $html = '<table style="
        width: 80%;
        border-collapse: collapse;
        padding: 20px 0;
    ">';

    // header row
    $headerRow = end($array);
    $html .= '<tr>';
    foreach ($headerRow as $key => $value) {
        $html .= '<th style="
            border: 1px solid #333;
            padding: 5px;
        ">' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';

    // data rows
    $array = array_reverse($array);
    foreach ($array as $value) {
        $html .= '<tr>';
        foreach ($value as $value2) {
            $html .= '<td style="
                max-width: 700px;
                overflow-y: auto;
                border: 1px solid #333;
                padding: 5px;
                text-align: start;
            ">' . $value2 . '</td>';
        }
        $html .= '</tr>';
    }

    // finish table and return it
    $html .= '</table>';
    return $html;
}

function efas_getRealIpAddr() {
    $default_ip_header = 'REMOTE_ADDR'; // Default header to fetch the IP address

	//  if IP is not real TODO next:
    // Check if a custom header value is provided in the plugin settings
    $custom_ip_header = get_option('maspik_custom_ip_header');
    if (!empty($custom_ip_header) && isset($_SERVER[$custom_ip_header])) {
        return sanitize_text_field($_SERVER[$custom_ip_header]);
    }

    // If no custom header is provided, use the default REMOTE_ADDR header
    return sanitize_text_field($_SERVER[$default_ip_header]);
}


// Make Array
function efas_makeArray($string) {
    if (!$string || is_array($string)) {
        return is_array($string) ? $string : [];
    }
    $string = strtolower($string);
    return explode("\n", str_replace("\r", "", $string));
}

// Check if field value exists in string
function efas_is_field_value_exist_in_string($string, $field_value) {
    if ($string === "" || $field_value === "") {
        return false;
    }

    $string = strtolower($string);
  
	return strpos(strtolower($field_value), $string) !== false;
}

// Check if field value is equal to string
function efas_is_field_value_equal_to_string($string, $field_value) {
    if ($string === "" || $field_value === "") {
        return false;
    }
    $string = trim(strtolower($string));
    $field_value = trim(strtolower($field_value));

    return $string === $field_value;
}

function efas_get_spam_api($field = "text_field") {
    $spamapi_option = get_option("spamapi");

    if (!is_array($spamapi_option) || !cfes_is_supporting() || !isset($spamapi_option[$field])) {
        return false;
    }

    $api_field = $spamapi_option[$field];

    if (!is_array($api_field)) {
        if ($field === "AllowedOrBlockCountries") {
            // Keep the field value if it's for AllowedOrBlockCountries
            $api_field = $spamapi_option[$field];
        } else {
            // Convert non-array fields to an array using efas_makeArray (assuming it's a custom function)
            $api_field = efas_makeArray($spamapi_option[$field]);
        }
    }

    return $api_field ? $api_field : false;
}

function efas_is_lang($langs, $string) {
    if (!is_array($langs) || empty($string)) {
        return 0;
    }

    $is_lang = 0;
    foreach ($langs as $lang) {
        $is_lang += preg_match("/$lang/u", $string);
    }

    return $is_lang;
}


function efas_array_of_lang(){
  return array(
            '\p{Arabic}' => __('Arabic', 'contact-forms-anti-spam' ),
            '\p{Armenian}' => __('Armenian', 'contact-forms-anti-spam' ),
            '\p{Bengali}' => __('Bengali', 'contact-forms-anti-spam' ),
            '\p{Braille}' => __('Braille', 'contact-forms-anti-spam' ),
            '\p{Ethiopic}' => __('Ethiopic', 'contact-forms-anti-spam' ),
            '\p{Georgian}' => __('Georgian', 'contact-forms-anti-spam' ),
            '\p{Greek}' => __('Greek', 'contact-forms-anti-spam' ),
            '\p{Han}' => __('Han', 'contact-forms-anti-spam' ),
            '\p{Katakana}' => __('Katakana', 'contact-forms-anti-spam' ),
            '\p{Hiragana}' => __('Hiragana', 'contact-forms-anti-spam' ),
            '\p{Hebrew}' => __('Hebrew', 'contact-forms-anti-spam' ),
            '\p{Syriac}' => __('Syriac', 'contact-forms-anti-spam' ),
            '\p{Latin}' => __('Latin', 'contact-forms-anti-spam' ),
            '\p{Mongolian}' => __('Mongolian', 'contact-forms-anti-spam' ),
            '\p{Thai}' => __('Thai', 'contact-forms-anti-spam' ),
            '[À-ÿ]' => __('French (À-ÿ)', 'contact-forms-anti-spam'),
            '[ÄäÖöÜüß]' => __('German ([ÄäÖöÜüß])', 'contact-forms-anti-spam'),
            '[ÁÉÍÓÚáéíóúüÜñÑ]' => __('Spanish (ÁÉÍÓÚáéíóúüÜñÑ)', 'contact-forms-anti-spam'),
            '[A-Za-z]' => __('English (A-Za-z)', 'contact-forms-anti-spam'),
            '[ÀàÉéÈèÌìÍíÒòÓóÙùÚú]' => __('Italian (ÀàÉéÈèÌìÍíÒòÓóÙùÚú)', 'contact-forms-anti-spam'),

            '[А-Яа-яЁё]' => __('Russian (А-Яа-яЁ)', 'contact-forms-anti-spam' ),
            '\p{Unknown}' => __('Unknown language', 'contact-forms-anti-spam' ),
            '[A-Za-zÀ-ÖØ-öø-ÿ]' => __('Dutch (A-Za-zÀ-ÖØ-öø-ÿ)', 'contact-forms-anti-spam'),
            '[A-Za-zÇçĞğİıÖöŞşÜü]' => __('Turkish (A-Za-zÇçĞğİıÖöŞşÜü)', 'contact-forms-anti-spam'),
            '[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]' => __('Polish (A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż)', 'contact-forms-anti-spam'),
            '[A-Za-zĂăÂâÎîȘșȚț]' => __('Romanian (A-Za-zĂăÂâÎîȘșȚț)', 'contact-forms-anti-spam'),
            '[A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž]' => __('Czech (A-Za-zÁáČčĎďÉéÍíĹĺĽľŇňÓóŔŕŠšŤťÚúÝýŽž)', 'contact-forms-anti-spam'),
            '[A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё]' => __('Ukrainian (A-Za-zА-ЩЬЮЯЇІЄҐа-щьюяїієґЁё)', 'contact-forms-anti-spam'),

            // More languages with character ranges
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

function efas_array_supports_plugin(){
  $info = cfes_is_supporting() ? "" : "Pro"; 
  return array(
    'Contact form 7' => 0,
    'Elementor pro' => 0,
    'Wordpress Comments' => 0,
    'Wordpress Registration' => 0,
    'Woocommerce Review' => $info,
    'Woocommerce Registration' => $info,
    'Wpforms' => $info,
    'Gravityforms' => $info,
  );
} 

function efas_if_plugin_is_affective($plugin){
	if($plugin == 'Elementor pro'){
      return efas_if_plugin_is_active('elementor-pro') && get_option( "maspik_support_Elementor_forms" ) != "no";
    }else if($plugin == 'Contact form 7'){
      return  efas_if_plugin_is_active('contact-form-7') && get_option( "maspik_support_cf7" ) != "no" ;
    }else if($plugin == 'Woocommerce Review'){
      return efas_if_plugin_is_active('woocommerce') && get_option( "maspik_support_woocommerce_review" ) != "no" ;
    }else if($plugin == 'Woocommerce Registration'){
      return efas_if_plugin_is_active('woocommerce') && get_option( "maspik_support_Woocommerce_registration" ) != "no";
    }else if($plugin == 'Wpforms'){
	  return  efas_if_plugin_is_active('wpforms') && get_option( "maspik_support_Wpforms" ) != "no"  ;
    }else if($plugin == 'Gravityforms'){
      return efas_if_plugin_is_active('gravityforms')  && get_option( "maspik_support_gravity_forms" ) != "no" ;
    }else if($plugin == 'Wordpress Registration'){
      return efas_if_plugin_is_active('Wordpress Registration') && get_option( "maspik_support_registration" ) != "no" ;
    }else if($plugin == 'Wordpress Comments'){
      return get_option( "maspik_support_wp_comment" ) != "no" ;
    }else{
      return 1;
    }
}

function efas_if_plugin_is_active($plugin){
	if($plugin == 'elementor-pro'){
      return  maspik_is_plugin_active( 'elementor-pro/elementor-pro.php' );
    }else if($plugin == 'contact-form-7'){
      return in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')));
    }else if($plugin == 'woocommerce'){
      return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
    }else if($plugin == 'wpforms'){
	  return ( in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins'))) || in_array('wpforms/wpforms.php', apply_filters('active_plugins', get_option('active_plugins'))) );
    }else if($plugin == 'gravityforms'){
      return in_array('gravityforms/gravityforms.php', apply_filters('active_plugins', get_option('active_plugins')));
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
    $schedules['daily'] = array(
        'interval' => 86400,
        'display' => __('daily')
    );
    return $schedules;
}
// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'contact_forms_anti_spam_add_weekly' ) ) {
    wp_schedule_event( time(), 'daily', 'contact_forms_anti_spam_add_weekly' );
}
// Hook into that action that'll fire every five minutes
add_action( 'contact_forms_anti_spam_add_weekly', 'cfas_refresh_api' );
add_action( 'contact_forms_anti_spam_add_weekly', 'cfas_refresh_api' );


function cfes_is_supporting() {
  $maspik_is_supporting = apply_filters('maspik_is_supporting', 0);
  return get_option( "maspik_is_supporting" ) ? 1 : $maspik_is_supporting ;
} 
add_action('after_setup_theme', 'cfes_is_supporting');


function cfas_refresh_api() {
    if (!cfes_is_supporting()) {
        return;
    }
    $private_file_id = get_option("private_file_id");
    $mergePerKey = array();

    if (strpos($private_file_id, ',') !== false) {
        $private_file_id = substr($private_file_id, 0, strpos($private_file_id, ','));
    }

    $domain = $_SERVER['SERVER_NAME'];

    // Check if the first API is available and fetch data
    if (!empty($private_file_id)) {
        $Api_file = "https://wpmaspik.com/wp-json/acf/v3/apis/$private_file_id";
        $file = "$Api_file?num=2367816&site=$domain";
        $file = file_get_contents($file);
        $file = json_decode($file, true);
        $file = $file['acf'];
    } else {
        $file = array(); // Initialize as an empty array
    }

    // Check if the second API should be accessed
    $popular_spam = get_option("popular_spam"); 
    if ($popular_spam) {
        $Api_popular_spam_file = "https://wpmaspik.com/wp-json/acf/v3/options/public_api?num=2367333&site=$domain";

        $popularSpamFile = file_get_contents($Api_popular_spam_file);
        $popularSpamFile = json_decode($popularSpamFile, true);
        $popularSpamFile = $popularSpamFile['acf'];

        // Combine "text_field", "email_field", "textarea_field", and "contain_links" values from both APIs
        $combinedAPI = $file;
        if (isset($popularSpamFile['text_field'])) {
            $combinedAPI['text_field'] .= "\r\n" . $popularSpamFile['text_field'];
        }
        if (isset($popularSpamFile['email_field'])) {
            $combinedAPI['email_field'] .= "\r\n" . $popularSpamFile['email_field'];
        }
        if (isset($popularSpamFile['textarea_field'])) {
            $combinedAPI['textarea_field'] .= "\r\n" . $popularSpamFile['textarea_field'];
        }
        if (isset($popularSpamFile['contain_links'])) {
            $combinedAPI['contain_links'] = $popularSpamFile['contain_links'];
        }
    } else {
        $combinedAPI = $file; // Use only the first API result
    }

    // Update your option with the combined API result
    $previousAPI = get_option('spamapi') ? get_option('spamapi') : array();
    $newAPI = $combinedAPI;
	//echo print_r( $newAPI ) ; // Dev
    if ($newAPI == $previousAPI) {
        echo "<script>alert('You have the most new version already.');</script>";
    } else {
        update_option("spamapi", $newAPI);
        echo "<script>alert('New version applied successfully.');</script>";
    }
}



function cfas_get_error_text() {
  $text =  __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
 return get_option( 'error_message' ) ? get_option( 'error_message' ) : $text;
}


function get_maspik_footer(){
  	return "<div style='background: #e8e8e8; padding: 20px;  text-align: center;'>
	<h3>Did you like my plugin?</h3>
	<p>I’m a developer and I’m developing free plugins!<br>Why?<br>Because I love doing this! </p>
	<p>I plan on developing this plugin further and could use your help! If MASPIK is helping you battle your spam, please donate.</p>
	<p>For every 10 donations, I will upload a newer version of this plugin.</p>
	<a href='https://paypal.me/yonifre' target='_blank'>Donate here 🙂</a>
<p>Do you have ideas on how to improve MASPIK? Share them with me - <a href='mailto:yonifre@gmail.com' target='_blank'>yonifre@gmail.com</a></p></div>";

}

add_filter( 'admin_body_class', 'cfas_admin_classes' );
function cfas_admin_classes( $classes ) {
      $screen = get_current_screen()->id;
    if ( strpos($screen, 'contact-forms-anti-spam') !== false ){
        $classes .=  cfes_is_supporting() ? "maspik-pro" : false;
    }
    return $classes;
}

//AbuseIPDB (Thanks to @josephcy95)
function check_abuseipdb($ip){
  $apikey = get_option( 'abuseipdb_api' );
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

  return (int)$jsonreply["data"]["abuseConfidenceScore"];
}

//proxycheck.io (Thanks to @josephcy95)
function check_proxycheckio($ip){

  $apikey = get_option( 'proxycheck_io_api' );

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
  $jsonreply = wp_remote_retrieve_body($jsonreply);
  $jsonreply = json_decode($jsonreply, TRUE);

  return (int)$jsonreply[$ip]["risk"];
} 

//CIDR Filter (Thanks to @josephcy95)
function cidr_match($ip, $cidr){
    list ($subnet, $bits) = explode('/', $cidr);
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
    $ip_blacklist = get_option('ip_blacklist') ? efas_makeArray(get_option('ip_blacklist')) : array();
    $country_blacklist = get_option('country_blacklist') ? efas_makeArray(get_option('country_blacklist')) : array();
    $AllowedOrBlockCountries = get_option('AllowedOrBlockCountries') == 'allow' ? 'allow' : 'block';

    // Countries API
    if (efas_get_spam_api('country_blacklist') && efas_get_spam_api('AllowedOrBlockCountries') != 'ignore') {
        $countries_blacklist_api = efas_get_spam_api('country_blacklist');
        $AllowedOrBlockCountries = efas_get_spam_api('AllowedOrBlockCountries');
        $country_blacklist = $countries_blacklist_api;
    }

    // Check country blacklist
    $xml_data = @file_get_contents("http://www.geoplugin.net/xml.gp?ip=" . $ip);
    if ($xml_data) {
        $xml = simplexml_load_string($xml_data);
        $countryCode = $xml && $xml->geoplugin_countryCode ? (string) $xml->geoplugin_countryCode : false;

        if ($countryCode && in_array($countryCode, $country_blacklist) && $AllowedOrBlockCountries === 'block') {
            $spam = true;
            $reason = "Country code $countryCode is blacklisted ($AllowedOrBlockCountries)";
            return array('spam' => $spam, 'reason' => $reason);
        }
        if ($AllowedOrBlockCountries === 'allow' && !in_array($countryCode, $country_blacklist)) {
            $spam = true;
            $reason = "Country $countryCode is not in the whitelist ($AllowedOrBlockCountries)";
            return array('spam' => $spam, 'reason' => $reason);
        }
    }

    // Check IP blacklist
    if (in_array($ip, $ip_blacklist)) {
        $spam = true;
        $reason = "IP $ip is blacklisted";
        return array('spam' => $spam, 'reason' => $reason);
    }

    // CIDR Filter
    foreach ($ip_blacklist as $cidr) {
        if (ip_is_cidr($cidr) && cidr_match($ip, $cidr)) {
            $spam = true;
            $reason = "IP $ip is in CIDR: $cidr";
            return array('spam' => $spam, 'reason' => $reason);
        }
    }

    return array('spam' => $spam, 'reason' => $reason);
}

function cfes_is_spam_email_domain($email, $block_list) {
    $email_parts = explode('@', $email);
    if (count($email_parts) === 2) {
        $domain = str_replace("@","", $email_parts[1] ); // Extract the domain part
        return in_array($domain, $block_list) ? $domain : false;
    }
    return false; // Return false if the email is invalid or doesn't contain '@'
}

function checkEmailForSpam($field_value) {
    if (!$field_value) {
        return false; // Not spam if the field is empty.
    }
    $text_blacklist = efas_makeArray(get_option('emails_blacklist'));

    if (efas_get_spam_api('email_field')) {
        $blacklist_json = efas_get_spam_api('email_field');
        $text_blacklist = array_merge($text_blacklist, $blacklist_json);
    }
    $spam = false;
    foreach ($text_blacklist as $bad_string) {
      	if ( empty($bad_string) || $bad_string == " " ) {
          continue;
        }

      	if (strpos($bad_string, '*') !== false) {
                // Handle wildcard pattern using fnmatch // 1
                if (fnmatch($bad_string, $field_value, FNM_CASEFOLD)) {
                    $spam = "because wildcard pattern = $bad_string is in the black list";
                  	break;
                }
        } elseif (isset($bad_string[0]) && $bad_string[0] === "/") {
            // Check for regular expression patterns // 1
            if (preg_match($bad_string, $field_value)) {
                $spam = "because regular expression patterns = $bad_string is in the black list";
                break;
            }
        }elseif ( isset($bad_string[0]) && $bad_string[0] === "@" ) {
            // Check for Extract the domain part // 1 
              $email_parts = explode('@', $field_value);
          	  $bad_string = str_replace("@","", $bad_string ); // Extract the domain part
              if (count($email_parts) === 2 && $email_parts[1] === $bad_string) {                
                  $spam = "because spam email domain @$bad_string is in the black list";
                  break;
              }      
        } else {
            // Check for exact match
            $spam = efas_is_field_value_equal_to_string($bad_string, $field_value) ? true : $spam;
            if ($spam) {
                break;
            }
        }
    }
    // Check if spam email-domain is entered, like: @xyz.com, @gmail.com ...
//    $spam = cfes_is_spam_email_domain($field_value, $text_blacklist) ? "by Email Domain match" : $spam;
    return $spam;
}

function checkTelForSpam($field_value) {
    $valid = false;
    $tel_formats = get_option('tel_formats');
    
    if (empty($tel_formats)) {
        return array('valid' => 'Empty formats', 'reason' => 'Empty formats');
    }

    $tel_formats = explode("\n", str_replace("\r", "", $tel_formats));
    $reason = '0';

    foreach ($tel_formats as $format) {
        $format = trim($format);
        if (empty($format)) {
            continue;
        }
        if (strpos($format, '/') === 0) {
        	$reason = "Regular expression match: $format";
            // Regular expression format
            if (preg_match($format, $field_value)) {
                $valid = true;
                $reason = "Regular expression match: $format";
                break;
            }
        } elseif (strpos($format, '*') !== false) {
          		$reason = "Wildcard pattern match: $format";
            // Wildcard pattern using fnmatch
            if (fnmatch($format, $field_value, FNM_CASEFOLD)) {
                $valid = true;
                break;
            }
        } 
    }

    return array('valid' => $valid, 'reason' => $reason);
}

// validate Text Field
function validateTextField($field_value) {  
    // Convert the field value to lowercase.
    $field_value = strtolower($field_value);
  	$text_blacklist = get_option( 'text_blacklist' ) ? efas_makeArray(get_option('text_blacklist') ) : array('eric jones');
	$spam = false;
  	if ( efas_get_spam_api() ){
    	$text_blacklist_json =  efas_get_spam_api();
      	$text_blacklist = array_merge($text_blacklist, $text_blacklist_json);
    }  

    // Check for exact string matches and wildcard patterns in the blacklist.
    if (is_array($text_blacklist)) {
        foreach ($text_blacklist as $bad_string) {
            if ( empty($bad_string) || $bad_string == " " ) {
               continue;
            }
 			if (strpos($bad_string, '*') !== false) {
                // Handle wildcard pattern using fnmatch
                if (fnmatch($bad_string, $field_value, FNM_CASEFOLD)) {
                    $spam = "Input $field_value is blocked by wildcard pattern";
                  	break;
                }
            } else {
                // Check for exact string matches
                if (efas_is_field_value_equal_to_string($bad_string, $field_value)) {
                    $spam =  "Input $field_value is blocked"; // == true // No need to continue validation if a match is found.
                   	break;
                }
            }
        }
    }

    // Check the maximum character limit.
	$MaxCharacters_API = false;
  	if ( efas_get_spam_api('MaxCharactersInTextField') ){
    	$MaxCharacters_API = efas_get_spam_api('MaxCharactersInTextField')[0];
    }
    $MaxCharacters = get_option( 'MaxCharactersInTextField' ) ? get_option( 'MaxCharactersInTextField' ) : $MaxCharacters_API ;

    if ( is_numeric($MaxCharacters) && $MaxCharacters > 3) {
        $CountCharacters = strlen($field_value);
        if ($CountCharacters > $MaxCharacters) {
          	$spam =  "More than $MaxCharacters characters"; // == true // No need to continue validation if a match is found.
        }
    }
	return $spam;
}


//*  spampixel  *//
// Add admin-ajax endpoint -  spampixel
add_action('wp_ajax_cfas_pixel_submit', 'cfas_pixel_submit');
add_action('wp_ajax_nopriv_cfas_pixel_submit', 'cfas_pixel_submit');
function cfas_pixel_submit() {
	set_transient('maspik_allow_' . efas_getRealIpAddr(), '1', 'DAY_IN_SECONDS');//DAY_IN_SECONDS
	wp_die();
}


//add spampixel HTML if CF7 
function add_Maspik_human_verification_to_footer() {
  if (!get_option('Maspik_human_verification') ) {
        return;
  }
  if(  efas_if_plugin_is_affective("Contact form 7") || efas_if_plugin_is_affective("Elementor pro")  ){
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Function to add the maspik-captcha div to a form
            function addMaspikCaptcha(formElements) {
                formElements.forEach(form => {
                    const maspikCaptchaDiv = document.createElement('div');
                    maspikCaptchaDiv.className = 'maspik-captcha';

                    // Append maspik-captcha div inside the form element
                    form.appendChild(maspikCaptchaDiv);
                });
            }
            // Add maspik-captcha div inside "wpcf7-form" elements OR elementor-form
            addMaspikCaptcha(document.querySelectorAll('.wpcf7-form'));
            addMaspikCaptcha(document.querySelectorAll('.elementor-form'));

            // Create a <style> element
            const style = document.createElement('style');
            style.innerHTML = `
                .maspik-captcha {
                    width: 1px;
                    height: 1px;
                }
                form:focus-within .maspik-captcha {
                    background-image: url('<?php echo admin_url('admin-ajax.php') ?>?action=cfas_pixel_submit');
                }
            `;
            // Append the style to the document head
            document.head.appendChild(style);
        });
    </script>
    <?php
    }
}
add_action('wp_footer', 'add_Maspik_human_verification_to_footer');