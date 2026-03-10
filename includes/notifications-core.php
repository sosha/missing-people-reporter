<?php
/**
 * Notifications Core for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle status change and trigger alerts.
 */
function mpr_handle_status_change($meta_id, $object_id, $meta_key, $meta_value)
{
    if ($meta_key !== 'mpr_case_status') {
        return;
    }

    $case_id = $object_id;
    $new_status = $meta_value;
    $old_status = get_post_meta($case_id, 'mpr_case_status', true);

    if ($old_status === $new_status) {
        return;
    }

    // Trigger alerts
    mpr_send_status_change_notifications($case_id, $new_status, $old_status);
}
add_action('updated_post_meta', 'mpr_handle_status_change', 10, 4);

/**
 * Trigger alerts for New Cases.
 */
function mpr_notify_new_case($post_id, $post, $update)
{
    if ($update || $post->post_type !== 'missing_person' || $post->post_status !== 'publish') {
        return;
    }

    $agency_name = get_option('mpr_agency_name', 'Missing People Reporter');
    $title = sprintf(__('New Case Reported: %s', 'mpr'), get_the_title($post_id));
    $body = __('A new missing person case has been reported. Help us spread the word.', 'mpr');
    $url = get_permalink($post_id);

    // Notify 'new_cases' and 'all_updates' subscribers
    mpr_dispatch_categorized_push($post_id, $title, $body, array('new_cases', 'all_updates'));

    // Email Admin/Agency
    $admin_email = get_option('admin_email');
    $agency_email = get_option('mpr_agency_email', $admin_email);
    wp_mail($agency_email, $title, $body . "\n\n" . $url . "\n- " . $agency_name);
}
add_action('wp_insert_post', 'mpr_notify_new_case', 10, 3);

/**
 * Send notifications to all available channels with filtering.
 */
function mpr_send_status_change_notifications($case_id, $new_status, $old_status)
{
    $case_title = get_the_title($case_id);
    $case_url = get_permalink($case_id);
    $agency_name = get_option('mpr_agency_name', 'Missing People Reporter');
    $location = get_post_meta($case_id, 'mpr_last_seen_location', true);
    $risk = get_post_meta($case_id, 'mpr_risk_level', true);

    $title = sprintf(__('Case Status Update: %s', 'mpr'), $case_title);
    $body = sprintf(__('Status updated from %s to %s.', 'mpr'), $old_status ?: __('Initial', 'mpr'), $new_status);
    $message = $title . "\n" . $body . "\n" . sprintf(__('View case: %s', 'mpr'), $case_url) . "\n- " . $agency_name;

    // 1. Email to Admin/Agency
    $admin_email = get_option('admin_email');
    $agency_email = get_option('mpr_agency_email', $admin_email);
    wp_mail($agency_email, $title, $message);

    // 2. Web Push Notifications (Filtered)
    if (function_exists('mpr_dispatch_categorized_push')) {
        mpr_dispatch_categorized_push($case_id, $title, $body, array('all_updates', 'single'), $location, $risk);
    }

    // 3. SMS Alert (Hook for Third-Party Plugins)
    do_action('mpr_after_status_change_notification', $case_id, $new_status, $message);
}

/**
 * Helper to dispatch push notifications based on subscription categories.
 */
function mpr_dispatch_categorized_push($case_id, $title, $body, $types = array(), $location = '', $risk = '')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_push_subscriptions';

    // Build the query to find matching subscribers
    $query = "SELECT subscription_data FROM $table_name WHERE 1=1 AND (";
    $conditions = array();

    if (in_array('all_updates', $types))
        $conditions[] = "subscription_type = 'all_updates'";
    if (in_array('new_cases', $types))
        $conditions[] = "subscription_type = 'new_cases'";
    if (in_array('single', $types))
        $conditions[] = $wpdb->prepare(" (subscription_type = 'single' AND case_id = %d) ", $case_id);

    if ($location) {
        $conditions[] = $wpdb->prepare(" (subscription_type = 'location' AND filter_value = %s) ", $location);
    }
    if ($risk) {
        $conditions[] = $wpdb->prepare(" (subscription_type = 'risk' AND filter_value = %s) ", $risk);
    }

    if (empty($conditions))
        return;

    $query .= implode(' OR ', $conditions) . ")";
    $results = $wpdb->get_results($query);

    if (empty($results))
        return;

    foreach ($results as $row) {
        // Here we call the actual push delivery function in notifications-push.php
        if (function_exists('mpr_send_push_notification')) {
            mpr_send_push_notification($case_id, $title, $body);
        }
    }
}

/**
 * SMS integration using configured provider.
 */
function mpr_sms_notification_provider($case_id, $status, $message)
{
    $provider = get_option('mpr_sms_provider', 'none');
    if ($provider === 'none')
        return;

    $api_id = get_option('mpr_sms_api_id');
    $api_secret = get_option('mpr_sms_api_secret');
    $from = get_option('mpr_sms_from_number');
    $to = get_option('mpr_agency_phone'); // Default to agency phone for alerts

    if (!$api_id || !$api_secret || !$to)
        return;

    if ($provider === 'twilio') {
        $url = "https://api.twilio.com/2010-04-01/Accounts/$api_id/Messages.json";
        $auth = base64_encode("$api_id:$api_secret");

        wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth,
            ),
            'body' => array(
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ),
        ));
    }
    elseif ($provider === 'africastalking') {
        $url = "https://api.africastalking.com/version1/messaging";

        wp_remote_post($url, array(
            'headers' => array(
                'Accept' => 'application/json',
                'apikey' => $api_secret,
            ),
            'body' => array(
                'username' => $api_id,
                'to' => $to,
                'message' => $message,
                'from' => $from,
            ),
        ));
    }
}
add_action('mpr_after_status_change_notification', 'mpr_sms_notification_provider', 10, 3);

/**
 * Notify Agency when a new lead is submitted.
 */
function mpr_notify_agency_on_lead($lead_id, $case_id)
{
    global $wpdb;
    $lead = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mpr_leads WHERE id = %d", $lead_id));

    if (!$lead)
        return;

    $case_title = get_the_title($case_id);
    $admin_email = get_option('admin_email');
    $agency_email = get_option('mpr_agency_email', $admin_email);

    $subject = sprintf(__('🚨 NEW LEAD: %s (Case #%d)', 'mpr'), $case_title, $case_id);
    $message = sprintf(__('A new lead has been submitted for %s.', 'mpr'), $case_title) . "\n\n";
    $message .= sprintf(__('Submitter: %s (%s)', 'mpr'), $lead->submitter_name, $lead->submitter_email) . "\n";
    $message .= sprintf(__('Phone: %s', 'mpr'), $lead->submitter_phone) . "\n\n";
    $message .= __('Lead Details:', 'mpr') . "\n{$lead->lead_content}\n\n";
    $message .= __('View all leads in dashboard:', 'mpr') . " " . admin_url('edit.php?post_type=missing_person&page=mpr-leads');

    wp_mail($agency_email, $subject, $message);

    // Also trigger SMS alert to agency if enabled
    do_action('mpr_after_status_change_notification', $case_id, __('Lead Submitted', 'mpr'), $message);
}
add_action('mpr_after_lead_submitted', 'mpr_notify_agency_on_lead', 10, 2);
