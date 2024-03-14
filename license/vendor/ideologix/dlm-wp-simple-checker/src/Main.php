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

use Automattic\WooCommerce\Blocks\BlockTypes\Checkout;
use IdeoLogix\DigitalLicenseManagerClient\Service;
use IdeoLogix\DigitalLicenseManagerSimpleChecker\Abstracts\AbstractActivator;
use IdeoLogix\DigitalLicenseManagerSimpleChecker\Abstracts\AbstractChecker;

class Main {

	public $configuration;
	public $activator;
	public $checker;
	public $license;

	/**
	 * Constructor
	 *
	 * @param $config
	 *
	 * @throws \Exception
	 */
	public function __construct( $config ) {
		$this->configuration = new Configuration( $config );
		if ( isset( $config['activator'] ) && $config['activator'] instanceof AbstractActivator ) {
			$this->activator = $config['activator'];
		} else {
			$this->activator = new Activator( $this->configuration );
		}
		if ( isset( $config['checker'] ) && $config['checker'] instanceof AbstractChecker ) {
			$this->checker = $config['checker'];
		} else {
			$this->checker = new Checker( $this->configuration );
		}
		$this->license = new License($this->configuration);
	}

	/**
	 * Returns the license key data
	 * @return License
	 * @throws \Exception
	 */
	public function license() {
		return $this->license;
	}

	/**
	 * Returns the license checker data
	 * @return AbstractChecker|Checker
	 */
	public function checker() {
		return $this->checker;
	}
}