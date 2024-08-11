=== Maspik - Advanced Spam Protection ===
Contributors: yonifre
Donate link: paypal.me/yonifre
Tags: spam, blacklist, antispam, contact form, security
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Maspik makes unwanted messages a rarity and spam a thing of the past!

== Description ==

## Say Goodbye to Spam with Maspik! ##
Maspik uses a highly efficient "blacklist" method that surpasses traditional CAPTCHA services like Google's in both efficiency and accuracy, with a success rate of over 95%.

With Maspik, you have the power to define what is considered spam by adding phrases to your blacklist. Fast and precise blocking of spam submissions takes as little as 2 minutes to set up.

## How It Works ##

Maspik allows you to specify words, email addresses, phone formats, IP addresses, and more. If submissions contain links, originate from certain countries, or are in specified languages, Maspik flags them as spam and keeps them out of your inbox.

## Features ##

* **Blacklisting by Field Type:**
  * Text fields (Name/Subject)
  * Email fields (supports regex/wildcard patterns)
  * Text area fields
  * Phone number verification with regex/wildcard format
* **Character Control:**
  * Maximum number of characters in text fields
  * Maximum number of characters in text area fields
* **Link Limitation:**
  * Limit the number of links allowed in text areas (ideally 1)
* **Blocking:**
  * Specific IP addresses
  * Spam submissions in WordPress comments and subscription forms
* **Spam Log:**
  * Review blocked submissions
* **Advance Blocking:**
  * Honeypot
  * Block submissions without source URLs (Elementor)
* **API Integrations:**
  * Proxycheck.io
  * AbuseIPDB.com

## Supported Forms ##

Maspik integrates seamlessly with a wide range of popular contact forms:

* Elementor forms
* Contact Form 7
* NinjaForms
* Formidable forms
* Forminator forms
* Fluentforms
* Bricksbuilder forms
* WPForms*
* GravityForms*
* WordPress comments
* WordPress registration form
* WooCommerce registration form*
* WooCommerce review* 
(*) Pro license required

## We offer also a Pro version! ##

### Pro Version Features ###

The Pro version offers advanced functionality:

* Integration with the Maspik Spam API
* Create and use your own SPAM API across multiple websites
* Import/Export Settings
* Blocking based on specific languages (e.g. block Russian/Chinese/Arabic content)
* Country-specific blocking or allowing submissions (e.g. block USA/China/Russia)

##Important Note##

Be cautious when selecting words to blacklist as each website has different needs. For example, if you're a digital marketing agency and blacklist the word "SEO," you may lose some valid leads.

The plugin is GDPR compliant.

For more information, visit our website: [WpMaspik.com](https://wpmaspik.com/?readme-file)



== Installation ==

Search for "Maspik - Spam Blacklist" in the Wordpress Plugin repository through the 'Plugins' menu in Wordpress.
Install and activate the plugin.
In the Wordpress dashboard menu, find the Maspik - Spam Blacklist setting page.
Add spam words as needed.
== Frequently Asked Questions ==
= Does the plugin work with all Wordpress forms? =
Maspik currently supports:

<ul>
<li>Elementor forms</li>
<li>Contact form 7</li>
<li>NinjaForms</li>
<li>Formidable forms</li>
<li>Forminator forms</li>
<li>Fluentforms</li>
<li>Bricksbuilder forms</li>
<li>Wpforms (Maspik Pro license required)</li>
<li>Gravityforms (Maspik Pro license required)</li>
<li>Wordpress comments</li>
<li>Wordpress registration form</li>
<li>Woocommerce registration form (Maspik Pro license required)</li>
<li>Woocommerce review (Maspik Pro license required)</li>
</ul>

More forms will be supported in future releases.
Looking for specific plugin support? Let us know at https://wpmaspik.com/#contact

= Where do I set this up? =

In the WordPress dashboard menu, look for the 'Maspik spam" item and click on it.

= Will MASPIK slow down my site? =
No.
I developed this plugin using high-quality server-side code and avoided using CSS/JS to ensure optimal website performance as CSS/JS running in the front-end can slow down websites.

= How can I report security bugs? =
You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/contact-forms-anti-spam)

== Screenshots ==

1. Setting page - Text field blacklist
1. Setting page
1. Setting page
1. Setting page
1. Setting page
1. Setting page


== Changelog ==

= 2.1.2 - 26/07/2024 =
* Bug Fix - Fix error when Max links set on 0 

= 2.1.1 - 24/07/2024 =
* Improvement - Improve Honeypot check for Elementor + CF7 + Comments + Woocommerce Review + Registration 
* Bug Fix - Fix css glitch on some casese with new Honeypots

= 2.1.0 - 22/07/2024 =
* New Feature - Time check (If spent less then 5 secund in site - Spam)
* New Feature - Honeypot field (If not ampty- Spam )
* New Feature - Advance Honeypot field (If Js Year is diffrent from server year - Spam)

= 2.0.6 - 19/07/2024 =
* Improvement - Improve spam check performance 
* Bug Fix - Fix error appearing in Country check in some environments

= 2.0.5 - 16/07/2024 =
* Bug Fix - Fix phone check

= 2.0.4 - 16/07/2024 =
* New Feature - Download Spam-log as CSV
* Bug Fix - Fix languages check
* Bug Fix - Fix error appearing when spam is caught in some environments
* Improvement - Add option to insert question mark (?) in wildcard pattern in phone field


= 2.0.3 - 12/07/2024 =
* Improvement - Improve Dashboard(Maspik API) - add more fields in WpMaspik site for Pro users  
* Bug fix - Fix languages chack   
* Bug fix - Fix toggel option for some condition 

= 2.0.2 - 11/07/2024 =
* Bug fix - fixed some toggel options (Allow usage tracking, Disable comments, Add country name to emails) that weren't stable on some aviroment. 
* Change plugin footer text

= 2.0.1 - 10/07/2024 =
* Bug fix - removed disable comments option as it is not stable on some aviroment. 

= 2.0.0 - 10/07/2024 =
* Major Update - New user experience with a fresh & clear design.
* Code Upgrade - 80% of the plugin code has been revamped.
* Database Enhancement - Plugin settings are now saved in a separate table.
* New Feature - Added support for NinjaForms.
* New Feature - Added the ability to limit the number of characters in various fields.
* Spam Log Upgrade - Improved spam log functionality (note: previous spam log content will be deleted).
* Enhancement - Import/Export option become a free option (Not pro).

= 1.0.5 -  15/05/2024 =
* Bug fix - Fix Woocommerce spam check

= 1.0.4 -  30/04/2024 =
* Bug fix - Fix comment spam check

= 1.0.3 -  07/04/2024 =
* Improvement - Add maximum number of characters in Text Area fields
* Improvement - Improve Playground form
* Improvement - Improve code performance 
* Improvement - Improve spam check in Text Area fields

= 1.0.2 -  22/03/2024 =
* Improvement - Improve check in Proxycheck API.
* Improvement - Add language name to spam log when required/forbidden language triggers spam.
* Improvement - Maspik API language options integration.

= 1.0.1 -  14/03/2024 =
* Bug fix - Remove unnecessary css file in admin area
* Bug fix - Disable cron checker log if no key is set

= 1.0.0 -  12/03/2024 =
* Enhancement - New License Manager (Please delete your current license and re-activate it if it does not work)
* Enhancement - Block based on country/language becomes a PRO feature. see <a target="_blank" href="https://wpmaspik.com/announcement-changes-to-maspik-plugin/?1.0-inplugin-readme">article</a> for detail
* Improvement - Improve code performance
* New Feature - Options to create custom validation error for more options
* Bug fix - Fix in Playground form  

= 0.13.0 -  01/03/2024 =
* Improvement - code performance improvement throughout the plugin code
* Improvement - Minor UI/UX improvements to the settings page
* New Feature - Options to create custom validation error for few options
* Bug fix - Fix Brick forms compatibility  
* Bug fix - Fix Phone field in Playground form

= 0.12.4 -  24/02/2024 =
* New Feature - Export Import (For Pro users only) 
* Bug fix - Fixed bug in the GravityForms - confirm email field.

= 0.12.3 -  18/02/2024 =
* Improvement  -  Improve code performance Thanks to @pluginvulnerabilities.com

= 0.12.2 -  09/02/2024 =
* Improvement - Add support to Bricksbuilder forms!
* Improvement - Add support to Fluentforms!

= 0.12.1 -  04/02/2024 =
* Bug fix - Fix error in old php versions 

= 0.12.0 -  01/02/2024 =
* New Feature - New Playground - test your entries to see if they will be blocked 
* Improvement - Add support to Forminator forms!
* Improvement - New style for the setting page
* Performance - Improve code performance

= 0.11.0 -  27/01/2024 =
* Improvement - Add support to Formidable forms!
* Performance - Improve code performance

= 0.10.7 -  21/01/2024 =
* Performance  -  Improve code performance Thanks to @pluginvulnerabilities.com
* Disabling of Maspik human verification check - because not stable yet.

= 0.10.6 -  11/01/2024 =
* Tweak - option to add country name at the bottom of email content - to identify and block countries that send spam.
* Improvement - better organization of the Maspik dashboard menu.

= 0.10.5 -  19/12/2023 =
* Improvement  - shering non-sensitive information for helping block more spam - under Maspik "More option" page (Most down option - please select V) 

= 0.10.4 -  08/12/2023 =
* Changed the way visitor IP is checked in IP/country blocking (for patchstack.com)

= 0.10.3 -  27/11/2023 =
* Performance  -  Improve code performance
* Bug fix - Fix spam check in some situation


= 0.10.2 -  26/11/2023 =
* Performance  -  Improve code performance
* Bug fix - Fix Textarea spam check, Thanks to @tauri77

= 0.10.1 -  23/11/2023 =
* Performance  -  Improve code performance
* Tweek - Introduce Maspik human verification - New Bot capture - BETA 

= 0.10.0 -  23/11/2023 =
* Performance  -  Improve code performance
* Bug fix - fix vulnerability problem
* Tweek - Adding languages to the block/allow list

= 0.9.3 -  22/11/2023 =
* Bug fix - fix vulnerability problem, thanks to patchstack.com

= 0.9.2 -  17/11/2023 =
* Improvement - Support multisite wordpress setup 
* Performance  -  Improve code performance.

= 0.9.1 -  16/10/2023 =
* Bug fix - Tel field incorrectly checked for spam  

= 0.9.0 -  14/10/2023 =

* New Feature - Wildcard pattern accepted in text-field/email-field/ Tel-field!
* Improvement - Improve and add more in the API options (https://wpmaspik.com/).
* Performance  -  Improve code performance.

= 0.8.3 -  04/09/2023 =
* New Feature - Automatically adding spam phrases from the MASPIK API (PRO)(BETA)	
* Bug fix - Ip detect wrong in some servers  
* Improvement - Improve and add more in the API options (https://wpmaspik.com/).

= 0.8.2 -  29/07/2023 =
* Improvement - Add Russian translation thanks to Andrey

= 0.8.1 -  10/07/2023 =
* Bug fix - Fix ip detect wrong in some cases  

= 0.8 -  12/05/2023 =
* New Feature - Add option to Completely Disable Comments in WordPress.
* Performance  -  Improve code performance.
* Improvement - Improve and add more in the API options (https://wpmaspik.com/).

= 0.7.11 -  11/05/2023 =
* Performance  -  Improve code performance
* Improvement - Improve API options 

= 0.7.10 -  07/04/2023 =
* Adaptation to WP version 6.2

= 0.7.9 -  25/02/2023 =
* Bug fix (Please update ASAP!)

= 0.7.8 -  07/10/2022 =
* Adaptation to WP version 6.1

= 0.7.7 -  07/10/2022 =
* Bug fix - Fix Presserror problem    

= 0.7.6 -  29/09/2022 =
* Bug fix - Fix Name field check is is Array in Wpforms & GravityForms  

= 0.7.5 -  12/08/2022 =
* Bug fix - Fix Block Domain Email (Ex: xyz.com) in some servers  

= 0.7.4 -  01/08/2022 =
* New Feature - Connection to Proxycheck.io API (Thanks to josephcy95).
* New Feature - Connection to AbuseIPDB.com API (Thanks to josephcy95).
* Improvement - Add possibility to filter entire CIDR range such as 134.209.0.0/16 in IP blocklist field. (Thanks to josephcy95).
* Improvement - Improve layout of Spam log.

= 0.7.3 -  29/07/2022 =
* Bug fix - Prevents letters from becoming lowercase in regex format on tel field.
* New Offer - Want to get a free Pro license? Write an article about Maspik on your relevant blog, and get a link from the plugin page and a professional license for free. Email me for more details (Yonifre AT gmail).

= 0.7.2 -  19/07/2022 =
*  Bug fix - Error message not displaying in some servers  
*  Bug fix - Spam log not showing in some servers
*  Improvement - Add the ability to disable spam log in the Option page  

= 0.7.1 -  12/07/2022 =
*  Improvement - Add Option page  

= 0.7.0 -  12/07/2022 =
*  Improvement - Add support in Wordpress comments 
*  Improvement - Add support in Wordpress registration 
*  Improvement - Add support in Woocommerce review (pro) 
*  Improvement - Add support in Woocommerce registration (pro)

= 0.6.6 -  04/07/2022 =
* Performance  -  Improving code performance, Error log loud only when needed (thanks to Marius) 

= 0.6.5 -  03/06/2022 =
*  Bug fix - fix Block empty source URL option

= 0.6.4 -  03/06/2022 =
*  Bug fix - Disable Spampixel 

= 0.6.3 -  31/05/2022 =
*  Bug fix - Fix lower/uppercase letter in Country field 
*  New Url to API site 

= 0.6.1 -  30/05/2022 =

*  Improvement - Add Spam log counter to Spam log menu title
*  Bug fix - Allow only specific country option in CF7

= 0.6.0 -  27/05/2022 =
*  Improvement - Smart bot capture (spampixel)
*  Improvement - Add Gravityforms support - pro feature
*  Improvement - Add Wpforms support - pro feature   

= 0.5.8 -  27/04/2022 =
*  Tweek - Add Editor access to Spam-log 


= 0.5.7 -  20/04/2022 =
*  Tweek - Improve Text field spam lockup 
*  Bug fix - Fix Allow only specific country option

= 0.5.6 -  09/04/2022 =
* Improvement - Add option to put an end of Email in email field, like: @test.com 

= 0.5.5 -  06/04/2022 =
*  Thanks to @Fiona_Fars, Improve explanation content in the setting pages.


= 0.5.4 -  24/01/2022 =
* Improvement - Load API php file only if mark it or already use it
* Improvement - Ready for Wordpress 5.9

= 0.5.3 -  25/11/2021 =
* Improvement - Limit spam log to max 100 entrees (To prevent DB overload)

= 0.5.2 =
* Add a minimum requires version for php (7)

= 0.5.1 =
* Bug fix - Language api

= 0.5 =
* Performance  -  Improving code performance
* Tweek added  -  Change country drop down list
* Tweek added  -  Show your API list in the main setting page

= 0.4.3 =
* Add translation   - Add Hebrew translation

= 0.4.2 =
* Bug fix  - fix Block empty source URL option 

= 0.4.1 =
* Bug fix  - fix PHP error in 0.4.0v 

= 0.4.0 =
upgrade_notice: 'You may need to activate the plugin again after the update, as the plugin name changed'
* Tweek added  -  Connect your site to Spam API 
* Tweek added  -  Variety of new options
* Performance  -  Improving code performance

= 0.3.0 =
upgrade_notice: 'You may need to activate the plugin again after the update, as the plugin name changed'
* Tweek added  -  Allow only specific country 
* Tweek added  -  Block text-field with more than X characters 
* Tweek added  -  Ability to block textarea-field with any Russian character 
* Tweek added  -  Email Blacklist field support Regex 
* Performance  -  Improving code performance

= 0.2.2 =
upgrade_notice: 'You may need to activate the plugin again after the update, as the plugin name changed'
* Bug fix  - Fix phone filed & affect Language check only on textarea. 


= 0.2.1 =
* Bug fix  - Fix phone filed in CF7 always blocked. 

= 0.2.0 =
* Tweek added  - Spam log 
* Bug fix  - Not blocking Email field. 

= 0.1.0 =
*Tweek added  - Add plugin support to- Contact form 7 
*Tweek added  - Custom phone validation (Add your format) 

= 0.0.5 =
*Bug fix  - Fix php error. 

= 0.0.4 =
*Bug fix  - Fix bug in textarea field. 

= 0.0.3 =
*Tweek added  - Add Spam block counter

= 0.0.2 =
*Tweek added  - Block sending if not contains one character from main site language (Hebrew only for now) 

= 0.0.1 =
First release

== Upgrade Notice ==

= 2.0.0 =
Major Update - New user experience with a fresh & clear design. (note: previous spam log content will be deleted)