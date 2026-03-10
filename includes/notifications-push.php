<?php
/**
 * Web Push Notification System for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create the Push Subscriptions Table.
 */
function mpr_create_push_subscriptions_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_push_subscriptions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT NULL,
        case_id bigint(20) DEFAULT NULL,
        subscription_type varchar(50) DEFAULT 'single' NOT NULL,
        filter_value varchar(255) DEFAULT NULL,
        subscription_data text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Register Push Subscription.
 */
function mpr_register_push_subscription()
{
    check_ajax_referer('mpr_push_nonce', 'nonce');

    $subscription = isset($_POST['subscription']) ? sanitize_textarea_field($_POST['subscription']) : '';
    $case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
    $type = isset($_POST['subscription_type']) ? sanitize_text_field($_POST['subscription_type']) : 'single';
    $filter = isset($_POST['filter_value']) ? sanitize_text_field($_POST['filter_value']) : '';

    if (empty($subscription)) {
        wp_send_json_error(array('message' => __('Invalid subscription data.', 'mpr')));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_push_subscriptions';

    $wpdb->insert(
        $table_name,
        array(
        'user_id' => get_current_user_id(),
        'case_id' => $case_id,
        'subscription_type' => $type,
        'filter_value' => $filter,
        'subscription_data' => $subscription
    ),
        array('%d', '%d', '%s', '%s', '%s')
    );

    wp_send_json_success(array('message' => __('Subscribed successfully.', 'mpr')));
}
add_action('wp_ajax_mpr_register_push_subscription', 'mpr_register_push_subscription');
add_action('wp_ajax_nopriv_mpr_register_push_subscription', 'mpr_register_push_subscription');

/**
 * Handle Test Push Notification via AJAX.
 */
function mpr_handle_test_push_ajax()
{
    check_ajax_referer('mpr_notification_test_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permissions check failed.', 'mpr')));
    }

    $title = __('Test Notification', 'mpr');
    $body = __('This is a test notification from Missing People Reporter.', 'mpr');
    $url = admin_url('edit.php?post_type=missing_person&page=mpr-notification-settings');

    $result = mpr_send_push_notification($title, $body, $url);

    if ($result) {
        wp_send_json_success(array('message' => __('Test push notification dispatched successfully!', 'mpr')));
    }
    else {
        wp_send_json_error(array('message' => __('Failed to dispatch test notification. Check if you have active subscriptions or if your VAPID keys are valid.', 'mpr')));
    }
}
add_action('wp_ajax_mpr_test_push', 'mpr_handle_test_push_ajax');

/**
 * Log notification activity.
 */
function mpr_log_notification($message)
{
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[MPR Notification] ' . $message);
    }
}

/**
 * Send Web Push Notification.
 */
function mpr_send_push_notification($case_id, $title, $body)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_push_subscriptions';

    // Get all subscriptions for this case (or global if case_id=0)
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT subscription_data FROM $table_name WHERE case_id = %d OR case_id = 0",
        $case_id
    ));

    if (empty($results)) {
        return;
    }

    $vapid_public = get_option('mpr_vapid_public_key');
    $vapid_private = get_option('mpr_vapid_private_key');

    foreach ($results as $row) {
        $subscription = json_decode($row->subscription_data, true);

        // Final Placeholder: Mentioning common library hook
        // To implement full push, the user should install 'minishlink/web-push'
        // and hook into 'mpr_do_push_delivery'.
        do_action('mpr_do_push_delivery', $subscription, $title, $body, get_permalink($case_id));

        error_log(sprintf(__('Sending Push to subscription: %s', 'mpr'), $row->subscription_data));
    }
}
