<?php
/**
 * Notification API Settings for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add the notification settings page to the admin menu.
 */
function mpr_add_notification_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=missing_person',
        __('Notification APIs', 'mpr'),
        __('Notification APIs', 'mpr'),
        'manage_options',
        'mpr-notification-settings',
        'mpr_render_notification_settings_page'
    );
}
add_action('admin_menu', 'mpr_add_notification_settings_page');

/**
 * Initialize notification settings.
 */
function mpr_initialize_notification_settings()
{
    // Web Push (VAPID) Settings
    register_setting('mpr_notification_settings_group', 'mpr_vapid_public_key');
    register_setting('mpr_notification_settings_group', 'mpr_vapid_private_key');

    // SMS Settings
    register_setting('mpr_notification_settings_group', 'mpr_sms_provider');
    register_setting('mpr_notification_settings_group', 'mpr_sms_api_id');
    register_setting('mpr_notification_settings_group', 'mpr_sms_api_secret');
    register_setting('mpr_notification_settings_group', 'mpr_sms_from_number');

    // Web Push Section
    add_settings_section(
        'mpr_push_api_section',
        __('Web Push (VAPID) Configuration', 'mpr'),
        'mpr_push_api_section_callback',
        'mpr-notification-settings'
    );

    add_settings_field('mpr_vapid_public_key', __('VAPID Public Key', 'mpr'), 'mpr_vapid_public_render', 'mpr-notification-settings', 'mpr_push_api_section');
    add_settings_field('mpr_vapid_private_key', __('VAPID Private Key', 'mpr'), 'mpr_vapid_private_render', 'mpr-notification-settings', 'mpr_push_api_section');

    // SMS Section
    add_settings_section(
        'mpr_sms_api_section',
        __('SMS Gateway Configuration', 'mpr'),
        'mpr_sms_api_section_callback',
        'mpr-notification-settings'
    );

    add_settings_field('mpr_sms_provider', __('SMS Provider', 'mpr'), 'mpr_sms_provider_render', 'mpr-notification-settings', 'mpr_sms_api_section');
    add_settings_field('mpr_sms_api_id', __('Account SID / API ID', 'mpr'), 'mpr_sms_api_id_render', 'mpr-notification-settings', 'mpr_sms_api_section');
    add_settings_field('mpr_sms_api_secret', __('Auth Token / API Secret', 'mpr'), 'mpr_sms_api_secret_render', 'mpr-notification-settings', 'mpr_sms_api_section');
    add_settings_field('mpr_sms_from_number', __('From Number / Sender ID', 'mpr'), 'mpr_sms_from_render', 'mpr-notification-settings', 'mpr_sms_api_section');
}
add_action('admin_init', 'mpr_initialize_notification_settings');

function mpr_push_api_section_callback()
{
    echo '<p>' . sprintf(__('Enter your VAPID keys for Web Push notifications. You can generate these using tools like <a href="%s" target="_blank">this codelab</a>.', 'mpr'), 'https://web-push-codelab.glitch.me/') . '</p>';
}

function mpr_sms_api_section_callback()
{
    echo '<p>' . __('Configure your SMS gateway to send mobile alerts. Currently supports Twilio and Africa\'s Talking.', 'mpr') . '</p>';
}

function mpr_vapid_public_render()
{
    $val = get_option('mpr_vapid_public_key');
    echo '<input type="text" name="mpr_vapid_public_key" value="' . esc_attr($val) . '" class="regular-text" placeholder="' . esc_attr__('Paste Public Key here', 'mpr') . '">';
}

function mpr_vapid_private_render()
{
    $val = get_option('mpr_vapid_private_key');
    echo '<input type="password" name="mpr_vapid_private_key" value="' . esc_attr($val) . '" class="regular-text" placeholder="' . esc_attr__('Paste Private Key here', 'mpr') . '">';
}

function mpr_sms_provider_render()
{
    $val = get_option('mpr_sms_provider');
?>
    <select name="mpr_sms_provider">
        <option value="none" <?php selected($val, 'none'); ?>><?php _e('None (Disabled)', 'mpr'); ?></option>
        <option value="twilio" <?php selected($val, 'twilio'); ?>><?php _e('Twilio', 'mpr'); ?></option>
        <option value="africastalking" <?php selected($val, 'africastalking'); ?>><?php _e('Africa\'s Talking', 'mpr'); ?></option>
    </select>
    <?php
}

function mpr_sms_api_id_render()
{
    $val = get_option('mpr_sms_api_id');
    echo '<input type="text" name="mpr_sms_api_id" value="' . esc_attr($val) . '" class="regular-text">';
}

function mpr_sms_api_secret_render()
{
    $val = get_option('mpr_sms_api_secret');
    echo '<input type="password" name="mpr_sms_api_secret" value="' . esc_attr($val) . '" class="regular-text">';
}

function mpr_sms_from_render()
{
    $val = get_option('mpr_sms_from_number');
    echo '<input type="text" name="mpr_sms_from_number" value="' . esc_attr($val) . '" class="regular-text" placeholder="' . esc_attr__('+1234567890 or SENDER_ID', 'mpr') . '">';
}

function mpr_render_notification_settings_page()
{
?>
    <div class="wrap mpr-admin-wrap">
        <h1><?php _e('Notification API Settings', 'mpr'); ?></h1>
        <form action="options.php" method="post">
            <?php
    settings_fields('mpr_notification_settings_group');
    do_settings_sections('mpr-notification-settings');
    submit_button();
?>
        </form>

        <div class="card" style="max-width: 100%; margin-top: 20px;">
            <h2><?php _e('Test Configuration', 'mpr'); ?></h2>
            <p><?php _e('Use the button below to send a test push notification to all active subscriptions. This helps verify your VAPID keys and library integration.', 'mpr'); ?></p>
            <button type="button" id="mpr-test-push-btn" class="button button-secondary">
                <?php _e('Send Test Push', 'mpr'); ?>
            </button>
            <span id="mpr-test-push-status" style="margin-left: 10px;"></span>
        </div>
        
        <div class="card" style="max-width: 100%; margin-top: 20px;">
            <h2><?php _e('Important Note', 'mpr'); ?></h2>
            <p><?php _e('SMS notifications involve third-party costs from providers. Most Web Push notifications are free but require VAPID keys to work correctly in all browsers.', 'mpr'); ?></p>
        </div>
    </div>
    <?php
}
