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

use IdeoLogix\DigitalLicenseManagerSimpleChecker\Abstracts\AbstractChecker;

class Checker extends AbstractChecker {

	/**
	 * The configuration instance
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * The checker class
	 *
	 * @param  Configuration  $configuration
	 */
	public function __construct( $configuration ) {
		$this->configuration = $configuration;
		add_action( 'init', [ $this, 'schedule' ] );
		add_action( $this->getHookName(), [ $this, 'execute' ] );
	}

	/**
	 * Executes specific cron hook
	 * @return void
	 * @throws \Exception
	 */
	public function execute() {

		$licenseAPI = new License( $this->configuration );

		if ( ! $licenseAPI->isLicenseKeySet() ) {
			return;
		}

		$licenseObj = $licenseAPI->queryValidateLicenseExpiration();
		if ( is_wp_error( $licenseObj ) ) {
			error_log( sprintf( '%s - Cron Checker: %s (%s)', $this->configuration->name, $licenseObj->get_error_message(), $licenseObj->get_error_code() ) );

			return;
		} else {
			$licenseData = [
				'expires_at' => $licenseObj['expires_at'],
				'checked_at' => gmdate( 'Y-m-d H:i:s' ),
			];
			$licenseAPI->updateData( $licenseData );
			// prefix_dlm_license_check_license_data
			do_action( sprintf( '%s_%s', $this->getHookName(), 'check_success' ), $licenseData );
		}

		if ( ! $licenseAPI->isActivationTokenSet() ) {
			error_log( sprintf( '%s - Cron Checker: %s', $this->configuration->name, __( 'Activation token not found.' ) ) );

			return;
		}

		$tokenObj = $licenseAPI->queryValidateActivationToken();
		if ( is_wp_error( $tokenObj ) ) {
			error_log( sprintf( '%s - Cron Checker: %s (%s)', $this->configuration->name, $tokenObj->get_error_message(), $tokenObj->get_error_code() ) );

			return;
		} else {
			$tokenData = [
				'token'          => $tokenObj['token'],
				'checked_at'     => gmdate( 'Y-m-d H:i:s' ),
				'deactivated_at' => is_null( $tokenObj['deactivated_at'] ) ? null : $tokenObj['deactivated_at'],
			];
			$licenseAPI->updateData( $tokenData );
			// prefix_dlm_license_check_token_data
			do_action( sprintf( '%s_%s', $this->getHookName(), 'token_data_success' ), $tokenData );
		}
	}

	/**
	 * Schedules a cron task on specific interval
	 * @return void
	 */
	public function schedule() {
		$hook     = $this->getHookName();
		$interval = ! empty( $this->configuration->cron['interval'] ) ? $this->configuration->cron['interval'] : 'hourly';
		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time(), $interval, $hook );
		}
	}


	/**
	 * Returns the schedule id
	 * @return string
	 */
	public function getHookName() {
		return $this->configuration->prefix . 'dlm_license_check';
	}

}