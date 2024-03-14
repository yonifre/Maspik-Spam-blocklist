<?php
/* @var \IdeoLogix\DigitalLicenseManagerSimpleChecker\Configuration $configuration */
/* @var \IdeoLogix\DigitalLicenseManagerSimpleChecker\License $license */

$statusCode = $license->getStatus();
$statusText = $statusCode === $license::STATUS_MISSING_LICENSE_KEY ? __( 'Not Active' ) : ucwords( str_replace( [ '_', '-' ], ' ', $statusCode ) );
?>

<div id="dlm_license_form" class="2">
    <div class="dlm_license_form_head">
        <div id="dlm_license_branding">
			<?php if ( ! empty( $configuration->logo ) ): ?>
                <div id="logo"><img alt="logo" src="<?php echo esc_url( $configuration->logo ); ?>"></div>
			<?php endif; ?>
            <h2 id="tagline"><?php echo esc_html( $configuration->name ); ?></h2>
        </div>
        <div id="dlm_license_status">
            <span class="label"><?php _e( 'License Status' ); ?></span>
            <span class="value <?php echo esc_attr( $statusCode ); ?>"><?php echo esc_html( $statusText ); ?></span>
        </div>
    </div>
    <div class="dlm_license_form_content">
		<?php if ( $license::STATUS_MISSING_TOKEN === $statusCode ): ?><?php
			$licenseData = $license->queryValidateLicenseExpiration();
			?>
            <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <fieldset id="dlm_activate_plugin">
                    <div class="dlm_license_key ">
                        <p class="label"><?php _e( 'License Key' ); ?></p>
                        <p class="field"><input id="license_key" name="license_key" readonly type="password" value="<?php echo esc_attr( $license->getLicenseKey() ); ?>"></p>
                    </div>
                    <div class="dlm_license_email ">
                        <p class="label"><?php _e( 'Select activation token' ); ?></p>
                        <p class="field">
                            <select name="activation_token" id="activation_token">
                                <option value="new"><?php _e( 'Create new activation token' ); ?></option>
								<?php if ( ! empty( $licenseData['activations'] ) ): ?>

									<?php foreach ( $licenseData['activations'] as $activation ): ?>

										<?php if ( !empty($activation['deactivated_at']) ) {
											continue;
										}; ?>
                                        <option value="<?php echo esc_attr( $activation['token'] ); ?>"><?php echo !empty($activation['label']) ? sprintf('%s -> %s', $activation['label'], $activation['token']) : esc_html( $activation['token'] ); ?></option>
									<?php endforeach; ?><?php endif; ?>
                            </select>
                        </p>
                    </div>
                </fieldset>
                <div id="dlm_license_actions">
                    <fieldset class="dlm_license_links"></fieldset>
                    <fieldset class="dlm_license_button">
                        <input type="hidden" name="action" value="<?php echo esc_attr( $this->configuration->prefix . 'activator' ); ?>"/>
                        <input type="hidden" name="type" value="update_token"/>
						<?php echo wp_nonce_field( 'activate_nonce' ); ?>
                        <input type="submit" class="dlm_btn__prim dlm_lmac_btn" name="save" value="<?php _e( 'Save' ); ?>">
                        <input type="submit" class="dlm_btn__prim dlm_lmdac_btn" name="delete" onclick="return confirm('<?php _e( "Are you sure? This action cannot be reverted." ); ?>')" value="<?php _e( 'Delete' ); ?>">
                    </fieldset>
                </div>
            </form>
		<?php elseif ( $license::STATUS_EXPIRED === $statusCode ): ?>

            <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <fieldset id="dlm_activate_plugin">
                    <div class="dlm_license_key ">
                        <p class="label"><?php _e( 'License Key' ); ?></p>
                        <p class="field"><input id="license_key" name="license_key" readonly type="password" value="<?php echo esc_attr( $license->getLicenseKey() ); ?>"></p>
                    </div>
                </fieldset>
                <div id="dlm_license_actions">
                    <fieldset class="dlm_license_links"></fieldset>
                    <fieldset class="dlm_license_button">
                        <input type="hidden" name="action" value="<?php echo esc_attr( $this->configuration->prefix . 'activator' ); ?>"/>
						<?php echo wp_nonce_field( 'activate_nonce' ); ?>
                        <input type="submit" class="dlm_btn__prim dlm_lmdac_btn" name="delete" onclick="return confirm('<?php _e( "Are you sure? This action cannot be reverted." ); ?>')" value="<?php _e( 'Delete' ); ?>">
                    </fieldset>
                </div>
            </form>


		<?php elseif ( $license::STATUS_DISABLED === $statusCode ): ?>

            <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <fieldset id="dlm_activate_plugin">
                    <div class="dlm_license_key ">
                        <p class="label"><?php _e( 'License Key' ); ?></p>
                        <p class="field"><input id="license_key" name="license_key" readonly type="password" value="<?php echo esc_attr( $license->getLicenseKey() ); ?>"></p>
                    </div>
                </fieldset>
                <div id="dlm_license_actions">
                    <fieldset class="dlm_license_links"></fieldset>
                    <fieldset class="dlm_license_button">
                        <input type="hidden" name="action" value="<?php echo esc_attr( $this->configuration->prefix . 'activator' ); ?>"/>
						<?php echo wp_nonce_field( 'activate_nonce' ); ?>
                        <input type="submit" class="dlm_btn__prim dlm_lmac_btn" name="reactivate" value="<?php _e( 'Reactivate' ); ?>">
                        <input type="submit" class="dlm_btn__prim dlm_lmdac_btn" name="delete" onclick="return confirm('<?php _e( "Are you sure? This action cannot be reverted." ); ?>')" value="<?php _e( 'Delete' ); ?>">
                    </fieldset>
                </div>
            </form>

		<?php elseif ( $license::STATUS_ACTIVE === $statusCode ): ?>

            <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <fieldset id="dlm_activate_plugin">
                    <div class="dlm_license_key ">
                        <p class="label"><?php _e( 'License Key' ); ?></p>
                        <p class="field"><input id="license_key" name="license_key" readonly type="password" value="<?php echo esc_attr( $license->getLicenseKey() ); ?>"></p>
                    </div>
                </fieldset>
                <div id="dlm_license_actions">
                    <fieldset class="dlm_license_links"></fieldset>
                    <fieldset class="dlm_license_button">
                        <input type="hidden" name="action" value="<?php echo esc_attr( $this->configuration->prefix . 'activator' ); ?>"/>
						<?php echo wp_nonce_field( 'activate_nonce' ); ?>
                        <input type="submit" class="dlm_btn__prim dlm_lmac_btn" name="deactivate" value="<?php _e( 'Deactivate' ); ?>">
                        <input type="submit" class="dlm_btn__prim dlm_lmdac_btn" name="delete" onclick="return confirm('<?php _e( "Are you sure? This action cannot be reverted." ); ?>')" value="<?php _e( 'Delete' ); ?>">
                    </fieldset>
                </div>
            </form>

		<?php elseif ( $license::STATUS_MISSING_LICENSE_KEY === $statusCode ): ?>

            <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <fieldset id="dlm_activate_plugin">
                    <div class="dlm_license_key ">
                        <p class="label"><?php _e( 'License Key' ); ?></p>
                        <p class="field"><input id="license_key" name="license_key" type="text" value=""></p>
                    </div>
                </fieldset>
                <div id="dlm_license_actions">
                    <fieldset class="dlm_license_links"></fieldset>
                    <fieldset class="dlm_license_button">
                        <input type="hidden" name="action" value="<?php echo esc_attr( $this->configuration->prefix . 'activator' ); ?>"/>
						<?php echo wp_nonce_field( 'activate_nonce' ); ?>
                        <input type="submit" class="dlm_btn__prim dlm_lmac_btn" name="activate" value="<?php _e( 'Activate' ); ?>">
                    </fieldset>
                </div>
            </form>

		<?php endif; ?>
    </div>
</div>


