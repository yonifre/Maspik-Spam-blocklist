<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

?>
<div class="wrap">
    <h1><?php echo esc_html__('Maspik Import/Export Settings - Pro only', 'contact-forms-anti-spam'); ?></h1>

    <div class="maspik-message">
        <p>
            <?php echo esc_html__('Please note that importing/exporting settings will affect most of the Maspik configuration.', 'contact-forms-anti-spam'); ?><br>
            <?php echo esc_html__('Imported settings will override existing ones, and there is no option to undo the imported settings.', 'contact-forms-anti-spam'); ?><br>
            <?php echo esc_html__('Use this feature with caution.', 'contact-forms-anti-spam'); ?>
        </p>

        <?php 
        if( !cfes_is_supporting() ){
            echo "<h2 style='border: 1px solid #333;padding: 10px;width: auto;display: inline-block;background: #e4e4e4;'>".__("Your license is NOT active, so you can’t use the import/export Maspik settings. Please activate your license first.", 'contact-forms-anti-spam' )."</h2>"; ?>
            <p><?php 
            echo sprintf(
                esc_html__('You can purchase the PRO license on our website at %s.', 'contact-forms-anti-spam'), 
                '<a href="https://WPMaspik.com?import-export" target="_blank">WPMaspik.com</a></p>'
            ); 
            
        } ?>
    </div>
    <hr>
    <!-- Export form -->
    <h2><?php echo esc_html__('Export Settings', 'contact-forms-anti-spam'); ?></h2>
    <form id="export-settings-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        
        <input type="hidden" name="action" value="Maspik_export_settings">
        
        <?php wp_nonce_field('Maspik_export_settings_nonce', 'Maspik_export_settings_nonce_field'); ?>
        
        <?php submit_button(__('Export Settings', 'contact-forms-anti-spam')); ?>
    </form>
    <hr>

    <!-- Import form -->
    <h2><?php echo esc_html__('Import Settings', 'contact-forms-anti-spam'); ?></h2>
    <form id="import-settings-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="Maspik_import_settings">
        <?php wp_nonce_field('Maspik_import_settings_nonce', 'Maspik_import_settings_nonce_field'); ?>
        <input type="file" name="maspik-settings">
        <?php submit_button(__('Import Settings', 'contact-forms-anti-spam')); ?>
    </form>
</div>

<?php