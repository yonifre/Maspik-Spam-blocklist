=== Maspik - Spam Blacklist ===
Contributors: yonifre
Donate link: paypal.me/yonifre
Tags: spam , Blacklist, Validation, CAPTCHA, Anti spam, 
Requires at least: 4.3
Tested up to: 6.4
Stable tag: 0.13.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Say goodbye to annoying spam - with Maspik you'll make unwanted messages a rarity and spam a thing of the past!

== Description ==

Maspik uses a highly efficient "blacklist" method that surpasses traditional CAPTCHA services like Google's in both efficiency and accuracy, with a success rate of over 95%.

Maspik gives you the power to define what is considered spam by either adding phrases to your blacklist or using the list provided in the MASPIK API (PRO). With fast and precise blocking of spam submissions, setup takes as little as 2 minutes.

<h3>How it works:</h3>
Maspik allows you to specify words, email addresses, phone formats, IP addresses and more. If they contain links, originate from certain countries or are in specified languages, Maspik flags them as spam and keeps them out of your inbox.

Say goodbye to annoying spam - with Maspik you'll make unwanted messages a rarity and spam a thing of the past!

<h3>Main Features:</h3>

<ul>
<li>Blacklisting by field type:</li>
<li>- Text fields (often used as Name/Subject)</li>
<li>- Email fields (supports regex/ wildcard pattern)</li>
<li>- Text area fields</li>
<li>- phone number verification with regex/wildcard format</li>
<li>Controlling the maximum number of characters in text fields</li>
<li>Limit the number of links allowed in the text area (ideally 1)</li>
<li>Block specific IP addresses</li>
<li>Block spam submissions in WordPress comments and subscription forms</li>
<li>Access the spam log to review blocked submissions</li>
<li>Block Elementor form submissions without source URLs</li>
<li>Integration with Proxycheck.io API</li>
<li>Integration with AbuseIPDB.com API</li>
<li>Blocking based on the presence or absence of characters from specific languages (e.g. block submissions if Russian/Chinese/Arabic/English...  are found in the content).</li>
<li>Blocking or allowing submissions from specific countries only (e.g. block submissions if the country of origin is USA/China/Russia...).</li>

</ul>

<h3>Pro Version Features:</h3>

The Pro version of the Maspik plugin offers advanced functionality:
<ul>
<li>Integration with the Maspik Spam API</li>
<li>Create your own SPAM API on the WpMaspik web site and use it in all of your web sites. </li>
<li>Support for Gravity Forms, WPForms, WooCommerce registration and WooCommerce reviews.</li>
<li>Import/Export Settings.</li>
</ul>

<h3>Supported forms:</h3>

Maspik integrates seamlessly with a wide range of popular contact forms, ensuring compatibility across different site settings:

<ul>
<li>Elementor forms</li>
<li>Contact form 7</li>
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


<h4>Maspik Pro license And API Site: <a href="https://wpmaspik.com/?readme-file">WpMaspik.com</a></h4>
Note: Be careful when selecting words to blacklist as each website has different needs. For example, if you're a digital marketing agency and blacklist the word "SEO," you may lose some valid leads.


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

1. Setting page
2. Error after submitting form with spam words


== Changelog ==

= 0.13.0 -  01/03/2024 =
* New Feature - Import/Export Settings (Pro features)
* Improvement - code performance improvement throughout the plugin code
* Improvement - Minor UI/UX improvements to the settings page
* New Feature - Options to create custom validation error for few options
* Bug fix - Fix Brick forms compatibility  
* Bug fix - Fix Phone field in Playground forms
* New version 1.0.0 coming soon, see <a target="_blank" href="https://wpmaspik.com/announcement-changes-to-maspik-plugin/?Announcement-inplugin-readme">article</a> for details


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

= 1.0.0 =
Bug fixed - become more stable.