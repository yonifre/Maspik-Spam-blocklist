<?php
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
function efas_add_to_log($type='',$input = '',$post = null, $source = "Elementor forms") {
   if( get_option( 'maspik_Store_log' ) == "no" ) { return false;}
   $emptylog = '{ 
	"spammer":[
    {
        "Type":"",
        "value":"",
        "Ip":"",
        "Country":"",
        "User agent":"", 
        "Date":"",
        "Source":"",
   },
  ]}'; 
  $errorlog = get_option( 'errorlog' ) ? get_option( 'errorlog' ) : $emptylog;
  $text = print_r($post, 1);
  $ip = efas_getRealIpAddr();
  $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$ip);
  $countryName = $xml ? strtolower($xml->geoplugin_countryName) : "Other (Unknown)" ;
  $user_agent = $_SERVER['HTTP_USER_AGENT']; 
  $browser_name = efas_get_browser_name($user_agent);  
  $date = wp_date("d-m-Y H:i:s", null, wp_timezone() );;
    $arr = json_decode($errorlog, TRUE);
	if(is_array($arr) && count($arr) > 100){
		$arr = array_reverse($arr);
		$arr  = array_slice($arr, 0, 100, true);
		$arr = array_reverse($arr);
	}
    $arr[] =['Type' => $type,'value' => $input."  Data: ".$text , 'Ip' => $ip, 'Country' => $countryName, 'User agent' => $browser_name, 'Date' => $date, 'Source' => $source];
    $json = json_encode($arr);
  update_option( 'errorlog',$json , false );
}


function cfes_is_spam_email_domain($email,$block_list) {
  $email = strstr($email, '@');
  if(in_array($email, $block_list))
      return true;
  else
      return false;
}


function cfes_build_table($array){
    // start table
    $html = '<table style="
    width: 80%;
    border-spacing: 0px;
    padding: 20px 0;
">';
    // header row
    $html .= '<tr>';
  	$count = count($array) - 1;
    foreach($array[$count] as $key=>$value){
            $html .= '<th style="
    border: 1px solid #333;
    padding: 3px ;
">' . htmlspecialchars($key)  .  '</th>';
        }
    $html .= '</tr>';
	$array = array_reverse($array);
    // data rows
    foreach( $array as $key=>$value){
        $html .= '<tr >';
        foreach($value as $key2=>$value2){
            $html .= '<td style=" border: 1px solid #333; padding: 5px ;text-align: start;">' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }

    // finish table and return it

    $html .= '</table>';
    return $html;
}

function efas_getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
//make Array
function efas_makeArray($string){
  if(  !$string ||  is_array($string) ){
    return  is_array($string) ? $string : array();
  }
  $string = strtolower($string);
  // to improve 
	return explode( "\n", str_replace("\r", "", $string) );
}

function efas_is_field_value_exist_in_string($string,$field_value){
  if( $string == "" || $field_value == "" ){
    return false;
  }
  $string = strtolower($string);
  if(strpos($field_value, $string) !== false) {
        return true;
  }
  // to improve 
	return false;
}

function efas_is_field_value_equwl_to_string($string,$field_value){
  if(  $string == "" || $field_value == "" ){
    return false;
  }
	
  $string = trim(strtolower($string));
 // if (strpos($string, $field_value) !== false) {
 	if ( trim($string)  == trim($field_value)) {
      return true;
  	}
	return false;
}

function efas_get_spam_api($field = "text_field"){
  	if( !is_array( get_option( "spamapi" ) ) || !cfes_is_supporting() ){
  		return false;
    }
  	return get_option( "spamapi" )[$field];
}

function efas_is_lang($langs ,$string){
  if(!is_array($langs)){
    return false;
  }
  $is_lang = 0;
   foreach ($langs as $lang) {
   	$is_lang = preg_match("/$lang/u", $string) ? ++$is_lang :  $is_lang;
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
            '[А-Яа-яЁё]' => __('Russian', 'contact-forms-anti-spam' ),
            '\p{Unknown}' => __('Unknown language', 'contact-forms-anti-spam' ),
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
      return in_array('elementor-pro/elementor-pro.php', apply_filters('active_plugins', get_option('active_plugins')));
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

function mergePerKey($array1,$array2){
  $rr = array();
  foreach ($array1 as $key => $value) {
    $value = is_array($value) ? $value :  efas_makeArray($value);
    if( array_key_exists($key, $array2) ){
      $value2 =  $array2[$key]  ?  efas_makeArray($array2[$key]) : array();
       $values = array_merge($value, $value2);
       $values = array_unique($values);
      $rr[$key]=$values;
    }
  }
  return $rr;
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
function cfas_refresh_api() {
  if( !cfes_is_supporting() ){return;}
  $apis = array();
  $private_file_id = get_option( "private_file_id" );
  $mergePerKey = array();
  $num = 0;

  //if( $cfas_public_api = get_option("public_file") ){
    $apis['cfas_public_api']="https://wpmaspik.com/wp-json/acf/v3/options/public_api";
  //}

  if( $private_file_id ){
    $private_file_id = explode(',', $private_file_id);
      foreach($private_file_id as $id ){
        $apis['api'.$num]="https://wpmaspik.com/wp-json/acf/v3/apis/$id";
        $num++;
      }
  }
  $apiarray = get_option('spamapi') ? get_option('spamapi') : array();  
  foreach($apis as $api => $file ){
    $n = 1;
  	$domain = $_SERVER['SERVER_NAME'];
    $file = "$file?num=2367816&site=$domain"; 
    $file = file_get_contents( $file );
    $file =  json_decode($file, true) ; // decode the JSON into an associative array
    $file = $file['acf'];
    if ( empty($file) ) {
      continue; 
    }
    $opt_name  = $api;
    $opt_value =  $file;
    $$api = $opt_value;
    //$existing_val = get_option( $opt_name );
    $mergePerKey =  $cfas_public_api=  mergePerKey( $opt_value,$cfas_public_api ) ;
    $n++;
        // option exist
  }
  if ( $mergePerKey ==  get_option( "spamapi" ) ) {
    echo "<script>alert('You have the most new version already.');</script>";
  } else {
    echo "<script>alert('New version applied successfully.');</script>";
  }

  if( get_option( "spamapi" ) ) {
    update_option( "spamapi", $mergePerKey); 
  }else{
    add_option( "spamapi", $mergePerKey );
 }
      update_option( "spamapi", $mergePerKey );

 // echo "<pre>".print_r( get_option( "spamapi" ), true )."</pre>";
}

function cfes_is_supporting()  {
   if(get_option( 'to_include_api' ) || get_option( 'private_file_id' )){
  	 return get_option( "maspik_is_supporting" )  ? 1 : 0;
   }
   return 0 ;
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


//*  spampixel  *//
function cfas_add_spampixel_to_form() { 
    if (!get_option( 'spampixel' )) {
      return;
    }
	$ajax_url = admin_url('admin-ajax.php') . '?action=cfas_pixel_submit';
	echo '<div class="spx-captcha"></div>';
	echo <<<EOT
		<style>
			.spx-captcha {
				width: 1px;
				height: 1px;
                position: absolute;
    			opacity: 0;
			}
			form:focus-within .spx-captcha {
				background-image: url($ajax_url);
			}
		</style>
EOT;
}
// Add admin-ajax endpoint -  spampixel
add_action('wp_ajax_cfas_pixel_submit', 'cfas_pixel_submit');
add_action('wp_ajax_nopriv_cfas_pixel_submit', 'cfas_pixel_submit');
function cfas_pixel_submit() {
	set_transient('spx_allow_' . efas_getRealIpAddr(), '1', 'DAY_IN_SECONDS');//DAY_IN_SECONDS
	wp_die();
}


/*add_action( 'wp_footer', function(  ) {
 echo $rr =  efas_get_spam_api() ? "yes" : "no";
    echo print_r( efas_get_spam_api('email_field'), true);

} );*/

//AbuseIPDB
function check_abuseipdb($ip)
{

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

//AbuseIPDB
function check_proxycheckio($ip)
{

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

//CIDR Filter
function cidr_match($ip, $cidr)
{
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