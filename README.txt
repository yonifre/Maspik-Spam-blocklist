=== Maspik - Spam blacklist ===
Contributors: yonifre
Donate link: paypal.me/yonifre
Tags: elementor forms, spam,Blacklist,elementor,Maspik ,anti,lenix,anti spam,contact form,eric,blacklist, forms,cf7,Contact form 7,phone validation, validation,form validation,Eric Jones
Requires at least: 4.3
Tested up to: 6.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Eliminate spam. Block specific words, IP, country, languages, and more!

== Description ==

Are you getting spam emails through your contact forms?
MASPIK will help you stop spam coming through your Elementor + CF7 forms + Wordpress Comments & Registration (plus Gravityforms + Wpforms + Woocommerce reviews & Registration if you active pro license).
Block/blacklist specific words, IP, country, languages, and more – MASPIK blocks the spam that ReCaptcha doesn’t block.

If you look through your spam emails, you can often find the same words repeated. Sometimes they are in a foreign language, and other times they are from the same country or even IP.
One example of common words in spam email is “Eric Jones”, “SEO Ranking”, “Automatically submit your website” , "Get free".

MASPIK can block form spam emails from being delivered to your inbox!


<h4>Features:</h4>
<ul>
<li>Support on Elementor forms + CF7 (More in the pro version)</li>
<li>Support built-in Wordpress Comments & Registration</li>
<li>Blacklist words in a text field</li>
<li>Blacklist words in the Email field (regex accepted)</li>
<li>Blacklist words in the textarea field</li>
<li>Blacklist a specific IP address</li>
<li>Blacklist a specific country or countries</li>
<li>Allow only a specific country</li>
<li>Block text fields that have more than X characters</li>
<li>Block spam if the textarea contains more than X links</li>
<li>Block or allow the email if it contains (or doesn’t contain) one character from the main site language.</li>
<li>Custom phone validation (Add your regex format)</li>
<li>Spam log, which you can clear at any time</li>
<li>Block forms devoid of source URL (Elementor forms only)</li>
</ul>
If a bot or spammer fills out your form with any of the above, the form will NOT be sent, and they will receive a validation error.

<h4>Pro version features:</h4>
<ul>
<li>Connect your site to private Spam API </li>
<li>Gravityforms support</li>
<li>Wpforms support</li>
<li>Woocommerce registration</li>
<li>Woocommerce review</li>
</ul>

<h4>API site: <a href="https://wpmaspik.com/">WpMaspik.com</a></h4>

Note:
The plugin allows you to manually blacklist the words you select.
Each website has different needs, so be careful with the words you choose to blacklist. For example, if you are a digital marketing agency and block the word SEO, you will likely lose some valid leads.

Love each author, it will make the world better ❤

== Installation ==

1. Search in wp Plugin repository through the 'Plugins' menu in WordPress for "Maspik - Spam blacklist".
2. Install and Activate the plugin.
3. In the WordPress dashboard menu you will find the setting page - Maspik - Spam blacklist.
4. Add Spam words as you wish.

== Frequently Asked Questions ==

= The plugin stop spam for any different kind of contact form plugins? =

MASPIK support freely at Elementor and Contact Form 7.
And in pro features, Gravityforms + Wpforms as well.

We plan on supporting more forms in the future.

= Where do I set this up? =

In the WordPress dashboard menu, look for the MASPIK plugin and click on “settings”.

= Will MASPIK slow down my site? =
No.
I wrote this plugin in high-quality code and not in CSS/JS. (CSS/JS running in the front end is what slows down websites.)

== Screenshots ==

1. Setting page
2. Error after submitting form with spam words


== Changelog ==

= 0.7.3 -  29/07/2022 =
* Bug fix - Prevents letters from becoming lowercase in regex format on tel field
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