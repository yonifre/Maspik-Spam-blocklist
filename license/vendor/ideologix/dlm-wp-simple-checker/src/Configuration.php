<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-2024  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManagerSimpleChecker;


class Configuration {
	public $name;
	public $logo;
	public $prefix;
	public $context;
	public $public_path;
	public $public_url;
	public $consumer_key;
	public $consumer_secret;
	public $api_url;

	public $menu = [];
	public $cron = [];

	/**
	 * Constructor
	 * @param  array  $config
	 *
	 * @throws \Exception
	 */
	public function __construct( array $config ) {

		$this->name            = $this->getOrFail( $config, 'name' );
		$this->prefix          = $this->getOrFail( $config, 'prefix' );
		$this->public_path     = $this->getOrFail( $config, 'public_path' );
		$this->public_url      = $this->getOrFail( $config, 'public_url' );
		$this->consumer_key    = $this->getOrFail( $config, 'consumer_key' );
		$this->consumer_secret = $this->getOrFail( $config, 'consumer_secret' );
		$this->api_url         = $this->getOrFail( $config, 'api_url' );
		$this->menu            = $this->getOrFail( $config, 'menu' );

		if ( ! empty( $config['cron'] ) ) {
			$this->cron = $config['cron'];
		}

		if ( ! empty( $config['logo'] ) ) {
			$this->logo = $config['logo'];
		}

		if ( empty( $config['context'] ) || ! in_array( $config['context'], [ 'theme', 'plugin' ] ) ) {
			$this->context = 'plugin';
		}
	}

	/**
	 * Extracts parameter or throws missing exception.
	 * @param $config
	 * @param $key
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	private function getOrFail( $config, $key ) {
		if ( empty( $config[ $key ] ) ) {
			throw new \Exception( sprintf( 'DLM Simple Checker: %s is missing.', $key ) );
		}
		return $config[ $key ];
	}

	/**
	 * The activation page url
	 * @return string
	 */
	public function getActivationPageUrl() {
		return add_query_arg( [ 'page' => $this->getActivationPageSlug() ], admin_url( 'admin.php' ) );
	}

	/**
	 * Te activation page slug
	 * @return string
	 */
	public function getActivationPageSlug() {
		return $this->prefix . 'activator';
	}

	/**
	 * Is the activation page
	 * @return bool
	 */
	public function isActivationPage() {
		return !empty( $_GET['page'] ) && $_GET['page'] === $this->getActivationPageSlug();

	}
}