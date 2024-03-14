## Digital License Manager WordPress Simple Update Checker

WordPress simple license checker that utilizes the Digital License Manager's [REST API](https://docs.codeverve.com/digital-license-manager/rest-api/).

This package is inspired by [TheWebSolver/tws-license-manager-client](https://github.com/TheWebSolver/tws-license-manager-client).

*Note*: This license checker is standalone and shouldn't be used together with [ideologix/dlm-wp-updater](https://github.com/ideologix/dlm-wp-updater) which is for more advanced usage.

## Requirements

1. WordPress 4.0+
2. Digital License Manager, PRO is optional but recommended.

## Installation

The PHP package can be imported either with Composer:

```shell
composer require ideologix/dlm-wp-simple-checker
```

## Integration

The following example explains how to use the library within your PRO/Premium plugin.

1. First, install the package using `composer` in the ROOT of your plugin.
2. Create `init.php` (or name it as you wish) file in the ROOT of your plugin as follows:

```php
if ( ! defined( 'ABSPATH' ) ) {
	die; // Prevent direct access.
}

/**
 * Require Digital License Manager simple license activation API.
 */
require_once dirname( __FILE__ ) . '/path/to/vendor/autoload.php';
if ( ! class_exists( 'YourPrefix_License_Checker' ) ) {
	class YourPrefix_License_Checker extends \IdeoLogix\DigitalLicenseManagerSimpleChecker\Main {
	}
}


/**
 * Returns the DLMs simple license checker instance
 * @return YourPrefix_License_Checker
 */
if ( ! function_exists( 'yourprefix_license_checker' ) ) {
	function yourprefix_license_checker() {
		static $checker;

		if ( ! $checker ) {
			$checker = new \Maspik_License_Checker( [
				'name'            => 'YourPrefix - Plugin',
				'logo'            => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/logo.png',
				'prefix'          => 'yourprefix_',
				'context'         => 'plugin',
				'public_path'     => trailingslashit( dirname( __FILE__ ) ) . 'vendor/ideologix/dlm-wp-simple-checker/public/', // You can override this and set your own path if you want to customzie the views and the assets.
				'public_url'      => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'vendor/ideologix/dlm-wp-simple-checker/public/', // You can override this and set your own path if you want to customzie the views and the assets.
				'consumer_key'    => 'ck_3fc0620008eb219e510b42d7a1164c7e0d28b2f1',
				'consumer_secret' => 'cs_1eef46aeae9ef30571491672fd14b9cfcaf50856',
				'api_url'         => 'http://digital-license-manager-enabled-site.com/wp-json/dlm/v1/',
				'menu'            => [
					'page_title' => 'License Activation',
					'menu_title' => 'License Activation',
					'parent_slug' => 'your-plugin-settings',
					'capaibility' => 'manage_options',
				]
			] );
		}

		return $checker;
	}
}


/**
 * Initialize it!
 */
yourprefix_license_checker();


/**
 *  @NOTES
 * 
 * --- ADVANCED USAGE ---
 * 
 *  1. To check if license is active
 *     if(yourprefix_license_checker()->license()->isLicenseValid())...
 *  2. To activate the current license
 *     yourprefix_license_checker()->license()->activate('License-key-goes-here')
 *  3. To deactivate the current license
 *     yourprefix_license_checker()->license()->deactivate()
 *  4. To remove the license completely (disables if not disabled)
 *     yourprefix_license_checker()->license()->deleteData()
 */

```


## License 

```
  This is part of the "Digital License Manager" WordPress plugin.
  https://darkog.com/p/digital-license-manager/
 
  Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
  Copyright (C) 2020-2024  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 
  Digital License Manager is free software; you can redistribute it
  and/or modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.
 
  Digital License Manager program is distributed in the hope that it
  will be useful,but WITHOUT ANY WARRANTY; without even the implied
  warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
  See the GNU General Public License for more details.
 
  You should have received a copy of the GNU General Public License v3
  along with this program;
 
  If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 
  Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
```
