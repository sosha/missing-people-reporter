<?php
/**
 * Weekly Digest System for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Schedule the weekly digest event.
 */
function mpr_schedule_weekly_digest()
{
    if (!wp_next_scheduled('mpr_weekly_digest_event')) {
        wp_schedule_event(time(), 'weekly', 'mpr_weekly_digest_event');
    }
}
add_action('wp', 'mpr_schedule_weekly_digest');

/**
 * Handle the weekly digest event.
 */
function mpr_handle_weekly_digest()
{
    global $wpdb;

    // 1. Get resolved cases in the last 7 days
    $args = array(
        'post_type' => 'missing_person',
        'posts_per_page' => -1,
        'date_query' => array(
                array(
                'after' => '1 week ago',
            ),
        ),
        'meta_query' => array(
                array(
                'key' => 'mpr_case_status',
                'value' => array('Found', 'Resolved'),
                'compare' => 'IN'
            )
        )
    );

    $query = new WP_Query($args);
    $resolved_count = $query->found_posts;

    if ($resolved_count === 0) {
        return; // Nothing to report this week
    }

    $case_list = "";
    while ($query->have_posts()) {
        $query->the_post();
        $case_list .= "- " . get_the_title() . " (" . get_permalink() . ")\n";
    }
    wp_reset_postdata();

    // 2. Prepare the message
    $agency_name = get_option('mpr_agency_name', __('Missing People Reporter', 'mpr'));
    $subject = sprintf(__('Weekly Good News Digest: %d People Found!', 'mpr'), $resolved_count);
    $body = sprintf(
        __("Hello,\n\nThis week, we have some good news. %d people have been found or their cases resolved safely!\n\nResolved Cases:\n%s\n\nThank you for being part of our community.\n- %s", 'mpr'),
        $resolved_count,
        $case_list,
        $agency_name
    );

    // 3. Send to Weekly Digest subscribers
    $table_name = $wpdb->prefix . 'mpr_push_subscriptions';
    $subscribers = $wpdb->get_results($wpdb->prepare(
        "SELECT subscription_data FROM $table_name WHERE subscription_type = %s",
        'weekly_digest'
    ));

    foreach ($subscribers as $sub) {
        // Here we would trigger the push notification for each subscriber
        // And if we had email subscriptions stored in the same table, we'd email them.
        error_log("Sending Weekly Digest Push to: " . $sub->subscription_data);
    }

    // Optional: Email all registered agency users the digest summary
    $admin_email = get_option('admin_email');
    $agency_email = get_option('mpr_agency_email', $admin_email);
    wp_mail($agency_email, $subject, $body);
}
add_action('mpr_weekly_digest_event', 'mpr_handle_weekly_digest');
