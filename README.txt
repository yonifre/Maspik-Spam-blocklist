=== Maspik - Spam Blacklist ===
Contributors: yonifre
Donate link: paypal.me/yonifre
Tags: elementor forms, spam, blacklist, elementor, Maspik, anti spam, contact form, Eric Jones, forms, CF7, Contact form 7, phone validation, validation, Spam API, Lenix, Wordpress
Requires at least: 4.3
Tested up to: 6.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Maspik is an anti-spam plugin that blocks spam submissions through your Elementor, CF7, and Wordpress contact forms, comments, and registration pages. With Maspik, you can blacklist specific words, IPs, countries, and languages to prevent spam emails from being delivered to your inbox.

If you're receiving spam emails, take a look at the common words or phrases they contain, such as "Eric Jones," "SEO Ranking," "Automatically submit your website," or "Get free." Maspik can block these and other spam words that ReCaptcha may miss.

<h4>Features:</h4>
<ul>
<li>Supports Elementor forms and CF7 (with more supported forms available in the Pro version)</li>
<li>Blocks spam submissions in Wordpress comments and registration forms</li>
<li>Allows you to blacklist words in a text field, email field (regex accepted), or textarea field</li>
<li>Enables you to blacklist specific IPs or countries, or only allow submissions from a specific country</li>
<li>Blocks text fields with more than X characters, or the textarea field if it contains more than X links</li>
<li>Blocks or allows the email field if it contains (or doesn't contain) one character from the main site language</li>
<li>Enables you to add custom phone validation with your regex format</li>
<li>Includes a spam log that you can clear at any time</li>
<li>Blocks Elementor forms devoid of source URLs</li>
<li>Connects to Proxycheck.io API and AbuseIPDB.com API</li>
</ul>
If a bot or spammer fills out your form with any of the above, the form will not be sent, and they will receive a validation error.

<h4>Pro Version Features:</h4>
<ul>
<li>Connects your site to a private spam API</li>
<li>Connects your site to a public spam API</li>
<li>Supports Gravityforms, Wpforms, Woocommerce registration, and Woocommerce reviews</li>
</ul>
<h4>API Site: <a href="https://wpmaspik.com/">WpMaspik.com</a></h4>
Note: Be careful when selecting words to blacklist as each website has different needs. For example, if you're a digital marketing agency and blacklist the word "SEO," you may lose some valid leads.

Let's spread love! ❤️

== Installation ==

Search for "Maspik - Spam Blacklist" in the Wordpress Plugin repository through the 'Plugins' menu in Wordpress.
Install and activate the plugin.
In the Wordpress dashboard menu, find the Maspik - Spam Blacklist setting page.
Add spam words as needed.
== Frequently Asked Questions ==
= Does the plugin work with other contact form plugins besides Elementor and CF7? =
Maspik supports Elementor and Contact Form 7, and it supports Gravityforms and Wpforms in the Pro version. We plan on supporting more forms in the future.
= Where do I set this up? =

In the WordPress dashboard menu, look for the 'Anti spam" item and click on it.

= Will MASPIK slow down my site? =
No.
I developed this plugin using high-quality server-side code and avoided using CSS/JS to ensure optimal website performance as CSS/JS running in the front-end can slow down websites.

= How can I report security bugs? =
You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/contact-forms-anti-spam)

== Screenshots ==

1. Setting page
2. Error after submitting form with spam words


== Changelog ==

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