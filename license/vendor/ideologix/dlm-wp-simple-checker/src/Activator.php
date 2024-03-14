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

use IdeoLogix\DigitalLicenseManagerSimpleChecker\Abstracts\AbstractActivator;

class Activator extends AbstractActivator {

	/**
	 * The configuration
	 * @var Configuration
	 */
	public $configuration;

	/**
	 * Activaton form
	 *
	 * @param  Configuration  $configuration
	 *
	 * @throws \Exception
	 */
	public function __construct( $configuration ) {
		$this->configuration = $configuration;
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
		add_action( 'admin_post_' . $this->configuration->prefix . 'activator', [ $this, 'handleActivation' ] );
		add_action( 'admin_notices', [ $this, 'adminNotices' ] );
		//add_action('admin_body_class', [$this, 'adminBodyClass']);
	}

	/**
	 * Enqueues the required scripts
	 * @return void
	 */
	public function enqueueScripts() {

		if ( $this->configuration->isActivationPage() ) {
			//return; // yoni comment this
		}
        
        if( isset($_GET['page']) && $_GET['page'] === $this->configuration->prefix .'activator'){ // yoni add this check

            wp_enqueue_style(
                $this->configuration->prefix . 'activator',
                trailingslashit( $this->configuration->public_url ) . 'assets/style.css',
                [],
                filemtime( trailingslashit( $this->configuration->public_path ) . 'assets/style.css' ),
                'all'
            );
            wp_enqueue_style(
                $this->configuration->prefix . 'activator',
                trailingslashit( $this->configuration->public_path ) . 'assets/script.js',
                [],
                filemtime( trailingslashit( $this->configuration->public_path ) . 'assets/script.js' ),
                'all'
            );
        }// yoni add this end
	}

	/**
	 * Registers the menu
	 * @return void
	 */
	public function adminMenu() {

		if ( empty( $this->configuration->menu['parent_slug'] ) ) {
			add_menu_page(
				! empty( $this->configuration->menu['page_title'] ) ? $this->configuration->menu['page_title'] : esc_html__( 'License Activation' ),
				! empty( $this->configuration->menu['menu_title'] ) ? $this->configuration->menu['menu_title'] : esc_html__( 'License Activation' ),
				! empty( $this->configuration->menu['capability'] ) ? $this->configuration->menu['capability'] : 'manage_woocommerce',
				! empty( $this->configuration->menu['menu_slug'] ) ? ! empty( $this->configuration->menu['menu_slug'] ) : $this->configuration->prefix . 'activator',
				[ $this, 'renderPage' ],
				! empty( $this->configuration->menu['icon_url'] ) ? ! empty( $this->configuration->menu['icon_url'] ) : 'dashicons-lock',
				! empty( $this->configuration->menu['position'] ) ? ! empty( $this->configuration->menu['position'] ) : 5
			);
		} else {
			add_submenu_page(
				! empty( $this->configuration->menu['parent_slug'] ) ? $this->configuration->menu['parent_slug'] : 'options-general.php',
				! empty( $this->configuration->menu['page_title'] ) ? $this->configuration->menu['page_title'] : esc_html__( 'License Activation' ),
				! empty( $this->configuration->menu['menu_title'] ) ? $this->configuration->menu['menu_title'] : esc_html__( 'License Activation' ),
				! empty( $this->configuration->menu['capability'] ) ? $this->configuration->menu['capability'] : 'manage_options',
				! empty( $this->configuration->menu['menu_slug'] ) ? ! empty( $this->configuration->menu['menu_slug'] ) : $this->configuration->prefix . 'activator',
				[ $this, 'renderPage' ]
			);
		}
	}


	/**
	 * Show the admin notices
	 * @return void
	 */
	public function adminNotices() {

		if ( ! $this->configuration->isActivationPage() ) {
			//return;
		}

		$result = $this->getFlashedMessage( true );
		if ( ! empty( $result ) ) {
			$class   = ! empty( $result['code'] ) && $result['code'] === 'error' ? 'notice notice-error' : 'notice notice-success';
			$message = ! empty( $result['message'] ) ? $result['message'] : __( 'Irks! An error has occurred.' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

			return;
		}

	}

	/**
	 * Renders the activation page
	 * @return void
	 */
	public function renderPage() {

		$configuration = $this->configuration;
		try {
			$license = new License( $configuration );
			include( $configuration->public_path . 'views/page.php' );
		} catch ( \Exception $e ) {
			echo '<h1>Error.</h1>';
			echo '<p>Unable to load license activation page.</p>';
			echo '<p><strong>Error</strong>:' . $e->getMessage() . '</p>';
		}
	}

	/**
	 * Handles the activation
	 * @return void
	 */
	public function handleActivation() {

		if ( empty( $_POST ) || ! check_admin_referer( 'activate_nonce', '_wpnonce' ) ) {
			return;
		}

		$capability = isset( $this->configuration->menu['capability'] ) ? $this->configuration->menu['capability'] : 'manage_options';

		if ( ! current_user_can( $capability ) ) {
			wp_die( 'Access denied.' );
		}

		try {
			$license = new License( $this->configuration );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}

		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';

		if ( ! empty( $_POST['save'] ) ) {
			if ( empty( $_POST['activation_token'] ) ) {
				$this->flashMessage( [ 'code' => 'error', 'message' => __( 'Unablle to save the selected token.' ) ] );
				$this->redirectBack( 'error' );
			} else {
				if('new' === $_POST['activation_token']) {
					$licenseKey = $license->getLicenseKey();
					if ( empty( $licenseKey ) ) {
						$this->flashMessage( [ 'code' => 'success', 'message' => __( 'Missing license key!' ) ] );
						$this->redirectBack( 'error' );
					} else {
						$resposne   = $license->activate( $licenseKey );
						if ( is_wp_error( $resposne ) ) {
							$this->redirectBack( 'error' );
						} else {
							$this->flashMessage( [ 'code' => 'success', 'message' => __( 'The license has been activated successfully' ) ] );
							$this->redirectBack( 'success' );
						}
					}
				} else {
					$license->updateData( [ 'token' => sanitize_text_field( $_POST['activation_token'] ) ] );
					$this->flashMessage( [ 'code' => 'success', 'message' => __( 'The token has been saved successfully.' ) ] );
					$this->redirectBack( 'success' );
				}
			}
			exit;
		} elseif ( ! empty( $_POST['delete'] ) ) {
			if ( ! $license->deactivate() ) {
				$this->flashMessage( [ 'code' => 'error', 'message' => __( 'Unable to delete and deactivate licnese.' ) ] );
				$this->redirectBack( 'error' );
			} else {
				$license->deleteData();
				$this->flashMessage( [ 'code' => 'deleted', 'message' => __( 'The license has been deleted and deactivated from this site.' ) ] );
				$this->redirectBack( 'success' );
			}
			$license->deleteData();
			exit;
		} elseif ( ! empty( $_POST['activate'] ) ) {
			if ( empty( $_POST['license_key'] ) ) {
				$this->flashMessage( [ 'code' => 'success', 'message' => __( 'Missing license key!' ) ] );
				$this->redirectBack( 'error' );
			} else {
				$licenseKey = sanitize_text_field( $_POST['license_key'] );
				$resposne   = $license->activate( $licenseKey );
				if ( is_wp_error( $resposne ) ) {
					$this->redirectBack( 'error' );
				} else {
					$this->flashMessage( [ 'code' => 'success', 'message' => __( 'The license has been activated successfully' ) ] );
					$this->redirectBack( 'success' );
				}
			}
			exit;
		} elseif ( ! empty( $_POST['reactivate'] ) ) {
			$licenseKey = $license->getLicenseKey();
			if ( empty( $licenseKey ) ) {
				$this->flashMessage( [ 'code' => 'error', 'message' => __( 'The license key is missing.' ) ] );
				$this->redirectBack( 'error' );
			} else {
				$license->activate( $licenseKey );
				$this->flashMessage( [ 'code' => 'success', 'message' => __( 'The license has been reactivated successfully' ) ] );
				$this->redirectBack( 'success' );
			}
			exit;
		} elseif ( $_POST['deactivate'] ) {
			if ( ! $license->deactivate() ) {
				$this->flashMessage( [ 'code' => 'error', 'message' => __( 'Unable to deactivate licnese.' ) ] );
				$this->redirectBack( 'error' );
			} else {
				$this->flashMessage( [ 'code' => 'success', 'message' => __( 'The license has been deactivated, however the license data is still saved and can be reactivated at anytime.' ) ] );
				$this->redirectBack( 'success' );
			}
			exit;
		} else {
			wp_die( 'Unknown action.' );
		}
	}

	/**
	 * Flashes a message
	 *
	 * @param $message
	 *
	 * @return void
	 */
	protected function flashMessage( $message ) {
		set_transient( $this->configuration->prefix . 'flash', $message, 60 * 10 );
	}

	/**
	 * Redirects back
	 *
	 * @param $status
	 *
	 * @return void
	 */
	protected function redirectBack( $status ) {
		wp_redirect( add_query_arg( [ 'status' => $status ], $this->configuration->getActivationPageUrl() ) );
	}

	/**
	 * Returns the flashed message and purges it from the database.
	 *
	 * @param $purge
	 *
	 * @return mixed
	 */
	protected function getFlashedMessage( $purge = true ) {
		$message = get_transient( $this->configuration->prefix . 'flash' );
		if ( $purge ) {
			delete_transient( $this->configuration->prefix . 'flash' );
		}

		return $message;
	}

}