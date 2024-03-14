<?php
// If this file is called directly, abort.
if (!defined('ABSPATH')) exit; 


/*
* Main function
*/

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
	$spamcounter = get_option( 'spamcounter' ) ? get_option( 'spamcounter' ) : 0;
	$timestamp = wp_date("YmdHis", null, wp_timezone());
	$gettimestamp = get_option( 'maspik_timestamp' ) ? get_option( 'maspik_timestamp' ) : 123;
  
	update_option( 'maspik_timestamp', $timestamp );
    if (get_option('maspik_Store_log') == "no") {
        update_option( 'spamcounter', ++$spamcounter );
        return false;
    }
	if($gettimestamp != $timestamp){
    	update_option( 'spamcounter', ++$spamcounter );
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
        'Source' => sanitize_text_field($source)."<br>",
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
                max-width: 500px;
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

    return $string === $field_value ? true : false;
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


function efas_array_of_lang_forbidden(){
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
            '\p{Han}' => __('Han', 'contact-forms-anti-spam' ),
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
    'Woocommerce Review' => $info,
    'Woocommerce Registration' => $info,
    'Wpforms' => $info,
    'Gravityforms' => $info,
    'Formidable' => 0,
    'Forminator' => 0,
    'Fluentforms' => 0,
    'Bricks' => 0,
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
    }else if($plugin == 'Formidable'){
      return efas_if_plugin_is_active('formidable')  && get_option( "maspik_support_formidable_forms" ) != "no" ;
    }else if($plugin == 'Fluentforms'){
      return efas_if_plugin_is_active('fluentforms')  && get_option( "maspik_support_fluentforms_forms" ) != "no" ;
    }else if($plugin == 'Bricks'){
      return efas_if_plugin_is_active('bricks')  && get_option( "maspik_support_bricks_forms" ) != "no" ;
    }else if($plugin == 'Forminator'){
      return efas_if_plugin_is_active('forminator')  && get_option( "maspik_support_forminator_forms" ) != "no" ;
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
      return maspik_is_plugin_active('fluentforms/fluentforms.php');
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



function cfas_get_error_text($field = "error_message") {
    if( get_option( "custom_error_message_$field" ) ){
        return sanitize_text_field ( get_option( "custom_error_message_$field" ) );
    }
    $text = get_option( "error_message" ) ? get_option( "error_message" ) : __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
    return sanitize_text_field($text);
}
function old_cfas_get_error_text($field = "error_message") {
    $text = get_option( "error_message" ) ? get_option( "error_message" ) : __('This looks like spam. Try to rephrase, or contact us in an alternative way.', 'contact-forms-anti-spam');
    return sanitize_text_field($text);
}



function get_maspik_footer(){
  	return "<div style='background: #87cbc0;padding: 20px;text-align: center;margin-top: 30px;border-radius: 20px;'>
	<h3>DO YOU LIKE MASPIK?</h3>
Please, <a href='https://wordpress.org/support/plugin/contact-forms-anti-spam/reviews/#new-post' target='_blan'>Give us 5 stars</a>.
<p>Do you have ideas on how to improve MASPIK? Share them with us - <a href='mailto:yoni@wpmaspik.com' target='_blank'>yoni@wpmaspik.com</a></p></div>";

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

  return isset( $jsonreply["data"]["abuseConfidenceScore"] ) ? (int)$jsonreply["data"]["abuseConfidenceScore"] : false ;
}

function check_proxycheckio($ip){
    $apikey = get_option('proxycheck_io_api');

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
    $ip_blacklist = get_option('ip_blacklist') ? efas_makeArray(get_option('ip_blacklist')) : array();
    $country_blacklist = get_option('country_blacklist') ? efas_makeArray(get_option('country_blacklist')) : array();
    $AllowedOrBlockCountries = get_option('AllowedOrBlockCountries') == 'allow' ? 'allow' : 'block';
    $message = 0;
    // Countries API
    if (efas_get_spam_api('country_blacklist') && efas_get_spam_api('AllowedOrBlockCountries') != 'ignore') {
        $countries_blacklist_api = efas_get_spam_api('country_blacklist');
        $AllowedOrBlockCountries = efas_get_spam_api('AllowedOrBlockCountries');
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
            if ($AllowedOrBlockCountries === 'allow' && !in_array($countryCode, $country_blacklist) ) {
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
      $abuseipdb_api = get_option('abuseipdb_api') ? get_option('abuseipdb_api') : false;
      $pabuseipdb_score = get_option('abuseipdb_score');
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
      $proxycheck_io_api = get_option('proxycheck_io_api') ? get_option('proxycheck_io_api') : false;
      $proxycheck_io_risk = get_option('proxycheck_io_risk');
      //Check if have proxycheck_io_api in the API Setting page (WpMaspik)
      if ( null !== efas_get_spam_api('proxycheck_io_api') ){
        $proxycheck_io_api_json = null !== efas_get_spam_api('proxycheck_io_api') ? efas_get_spam_api('proxycheck_io_api') : false;
        $proxycheck_io_risk_json = null !== efas_get_spam_api('proxycheck_io_risk') ? efas_get_spam_api('proxycheck_io_risk') : false;
        $proxycheck_io_api = $proxycheck_io_api ? $proxycheck_io_api : $proxycheck_io_api_json; // Site setting is stronger
        $proxycheck_io_risk = $proxycheck_io_risk ? $proxycheck_io_risk : $proxycheck_io_risk_json; // Site setting is stronger
      }

      if ($proxycheck_io_risk && $proxycheck_io_api && !$spam  ) {
        $proxycheck_io_riskscore = check_proxycheckio($ip);
        if ($proxycheck_io_riskscore && $proxycheck_io_riskscore >= (int)$proxycheck_io_risk) {
          $spam = true;
          $reason = "Proxycheck.io Risk: $proxycheck_io_riskscore";
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
    $emails_blacklist = efas_makeArray(get_option('emails_blacklist'));

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
            $spam = efas_is_field_value_equal_to_string($bad_string, $field_value) ? "because email '$bad_string' is in the blacklist" : false;
            if ($spam) {
                return $spam;
            }
        }

    }

    return false;
}


function checkTelForSpam($field_value) {
    $valid = false;
    $tel_formats = get_option('tel_formats');
    
    if ( empty( $tel_formats ) ) {
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
                return array('valid' => $valid, 'reason' => $reason, 'message'  => 'tel_formats');
                break;
            }
        } elseif (strpos($format, '*') !== false) { 
          		$reason = "Wildcard pattern match: $format";
            // Wildcard pattern using fnmatch
            if (fnmatch($format, $field_value, FNM_CASEFOLD)) {
                $valid = true;
                return array('valid' => $valid, 'reason' => $reason, 'message'  => 'tel_formats');
                break;
            }
        } 
    }
    return array('valid' => $valid, 'reason' => $reason, 'message'  => 'tel_formats');
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
                if (efas_is_field_value_exist_in_string($bad_string, $field_value) || efas_is_field_value_equal_to_string($bad_string, $field_value) ) {
                    $spam =  "Forbidden input $field_value, because <u>$bad_string</u> is blocked";
                    return array('spam' => $spam, 'message' => "text_blacklist");
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
            return array('spam' => $spam, 'message' => "MaxCharactersInTextField");
        }
    }
	return false;
}


//Textarea - check Textarea For Spam Function
function checkTextareaForSpam($field_value) {
    // Get the blacklist from options and merge with API data if available
    $textarea_blacklist = get_option('textarea_blacklist') ? efas_makeArray(get_option('textarea_blacklist')) : array();
    if (efas_get_spam_api('textarea_field')) {
        $blacklist_json = efas_get_spam_api('textarea_field');
        $textarea_blacklist = array_merge($textarea_blacklist, $blacklist_json);
    }
    
    foreach ($textarea_blacklist as $bad_string) {
        if (!empty($sentence) && $bad_string[0] === "[") {
            // Handle special cases for shortcodes
            $search = array('[', ']');
            $bad_string = str_replace($search, "", $bad_string);
            $bad_string = "url" || "name" || "description" ? get_bloginfo($bad_string) : "Error - Shortcode not exist";
        }
        if ( efas_is_field_value_exist_in_string($bad_string, $field_value) ) {
            return array('spam' => "field_value includes <u>$bad_string</u>", 'message' => "textarea_field");
        }
    }

    // only if pro user
    if( cfes_is_supporting() ){ 
        // Check for required language
        $lang_needed = get_option('lang_needed') ? get_option('lang_needed') : array();
        if ($lang_needed && !efas_is_lang($lang_needed, $field_value) ) {
            return array('spam' => "Needed language is missing", 'message' => "lang_needed");
        }
        // Check for forbidden language
        $lang_forbidden = get_option('lang_forbidden') ? get_option('lang_forbidden') : array();
        if ($lang_forbidden && efas_is_lang($lang_forbidden, $field_value) ) {
            return array('spam' => "Forbidden language exists", 'message' => "lang_forbidden");
        }
    }
    
    // Check for maximum number of links
    $max_links = get_option('contain_links');
    if ($max_links) {
        $reg_exUrl = "/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        $num_links = preg_match_all($reg_exUrl, $field_value);
        if ($num_links >= $max_links) {
            return array('spam' => "Contains <u>more than $max_links links</u>", 'message' => "contain_links");
        }
    }

    // No spam found in this field
    return false;
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
// Disable because not stable yet 
//add_action('wp_footer', 'add_Maspik_human_verification_to_footer');


function Maspik_admin_notice() {
    // Check if the user has 'manage_options' capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the notice has been dismissed
    if (!get_transient('Mapik_dismissed_shereing_notice') && !get_option('shere_data')) {
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
    update_option('shere_data', 1);

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
    
    if( !cfes_is_supporting() ){
        wp_die('Pro license required for import/export settings.');
    }


    // Get Maspik settings
    $maspik_settings = array(
        'text_blacklist' => get_option('text_blacklist'),
        'emails_blacklist' => get_option('emails_blacklist'),
        'textarea_blacklist' => get_option('textarea_blacklist'),
        // Add more settings as needed
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
    
    if( !cfes_is_supporting() ){
        wp_die('Pro license required for import/export settings.');
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
    $options = array('text_blacklist', 'emails_blacklist', 'textarea_blacklist' ,'MaxCharactersInTextField' , 'contain_links','lang_needed', 'lang_forbidden','tel_formats','ip_blacklist' , 'AllowedOrBlockCountries','country_blacklist','NeedPageurl','error_message','popular_spam');

    // Iterate over each option
    foreach ($options as $option) {
        // Check if the option exists in $sanitized_data and is not empty
        if (isset($sanitized_data[$option]) && !empty($sanitized_data[$option])) {
            // Perform replacements only if the option exists and is not empty
            // Update the option with sanitized data
            update_option($option, str_replace(",,," , "\n" ,$sanitized_data[$option]));
        }
    }

    // Redirect after import
    wp_redirect(admin_url('admin.php?page=maspik&imported=1'));
    exit;
}


// monitor_jquery_ajax_requests - for Dev only.
//add_action( 'wp_footer', 'Maspik_monitor_jquery_ajax_requests' );
function Maspik_monitor_jquery_ajax_requests() {
    ?>
    <script>
let startTime;

jQuery(document).ajaxSend(function(event, xhr, options) {
    startTime = new Date().getTime();
}).ajaxComplete(function(event, xhr, options) {
    const endTime = new Date().getTime();
    const elapsedTime = endTime - startTime; // Time difference in milliseconds
    console.log('Elapsed time (ms):', elapsedTime);
    console.log('Elapsed time (s):', elapsedTime/1000);
});
    </script>
    <?php
}
