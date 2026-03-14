<?php
/**
 * Plugin Name: Missing People Reporter
 * Plugin URI:  https://www.missingpeople.co.ke
 * Description: A comprehensive plugin to create, manage, and display reports for missing people.
 * Version:     0.1.0
 * Author:      Mentaltude
 * Author URI:  https://www.missingpeople.co.ke
 * License:     GPLv2 or later
 * Text Domain: mpr
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('MPR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MPR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include plugin files
include(MPR_PLUGIN_PATH . 'includes/post-type.php');
include(MPR_PLUGIN_PATH . 'includes/meta-boxes.php');
include(MPR_PLUGIN_PATH . 'includes/shortcodes.php');
include(MPR_PLUGIN_PATH . 'includes/form-handler.php');
include(MPR_PLUGIN_PATH . 'includes/user-profile.php');
include(MPR_PLUGIN_PATH . 'includes/follow-system.php');
include(MPR_PLUGIN_PATH . 'includes/template-loader.php');
include(MPR_PLUGIN_PATH . 'includes/agency-settings.php');
include(MPR_PLUGIN_PATH . 'includes/admin-dashboard.php');
include(MPR_PLUGIN_PATH . 'includes/notifications-core.php');
include(MPR_PLUGIN_PATH . 'includes/notifications-push.php');
include(MPR_PLUGIN_PATH . 'includes/digest-system.php');
include(MPR_PLUGIN_PATH . 'includes/notification-settings.php');
include(MPR_PLUGIN_PATH . 'includes/leads-system.php');
include(MPR_PLUGIN_PATH . 'includes/comments-integration.php');

/**
 * Load plugin text domain for localization.
 */
function mpr_load_textdomain()
{
    load_plugin_textdomain('mpr', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mpr_load_textdomain');

/**
 * Helper to get all custom meta keys for 'missing_person' CPT.
 * Centralizing this ensures consistency across admin, frontend, and API.
 */
function mpr_get_meta_keys()
{
    return [
        'mpr_full_name' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_nickname' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_age' => ['type' => 'integer', 'sanitize' => 'absint'],
        'mpr_dob' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_gender' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_height' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_body_type' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_weight' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_hair_color' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_hair_style' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_eye_color' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_distinguishing_features' => ['type' => 'string', 'sanitize' => 'wp_kses_post'],
        'mpr_piercings' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_tattoos' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_date_last_seen' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_last_seen_location' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_what_they_were_wearing' => ['type' => 'string', 'sanitize' => 'sanitize_textarea_field'],
        'mpr_police_station' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_ob_number' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_police_phone' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_police_email' => ['type' => 'string', 'sanitize' => 'sanitize_email'],
        'mpr_investigating_officer' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_contact_person' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_contact_person_email' => ['type' => 'string', 'sanitize' => 'sanitize_email'], // Fixed key name consistency
        'mpr_other_images' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_case_status' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'Missing'],
        'mpr_medical_conditions' => ['type' => 'string', 'sanitize' => 'sanitize_textarea_field'],
        'mpr_ethnicity' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_risk_level' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'Low'],
        'mpr_latitude' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'mpr_longitude' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
    ];
}

// Plugin Activation Hook
register_activation_hook(__FILE__, 'mpr_activate_plugin');

function mpr_activate_plugin()
{
    // The CPT is registered via 'init' hook in includes/post-type.php.
    // We only need to flush rewrite rules here for activation to ensure permalinks work immediately.
    mpr_register_missing_person_post_type();
    mpr_create_push_subscriptions_table();
    mpr_create_leads_table();
    flush_rewrite_rules();

    // Register custom meta fields for 'missing_person' post type
    $meta_keys = mpr_get_meta_keys();
    foreach ($meta_keys as $key => $args) {
        register_post_meta('missing_person', $key, array(
            'show_in_rest' => true,
            'single' => true,
            'type' => $args['type'],
            'default' => $args['default'] ?? '',
            'sanitize_callback' => $args['sanitize'],
            'auth_callback' => function () {
            return current_user_can('edit_posts');
        },
        ));
    }
}

// Plugin Deactivation Hook (optional, but good practice)
register_deactivation_hook(__FILE__, 'mpr_deactivate_plugin');

function mpr_deactivate_plugin()
{
    flush_rewrite_rules();
}

// Enqueue scripts and styles for frontend
function mpr_enqueue_assets()
{
    // Frontend styles
    wp_enqueue_style('mpr-frontend-css', MPR_PLUGIN_URL . 'assets/css/frontend.css', array(), '0.1.0');

    // Enqueue Push Notifications Script
    wp_enqueue_script('mpr-push-notifications', MPR_PLUGIN_URL . 'assets/js/notifications.js', array(), '0.1.0', true);
    wp_localize_script('mpr-push-notifications', 'mpr_push_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mpr_push_nonce'),
        'sw_url' => MPR_PLUGIN_URL . 'assets/js/sw.js',
        'vapid_public_key' => get_option('mpr_vapid_public_key', ''),
        'registering_msg' => __('Registering for notifications...', 'mpr'),
        'success_msg' => __('Subscribed successfully!', 'mpr'),
        'error_msg' => __('Subscription failed. Please try again.', 'mpr')
    ));

    wp_enqueue_style('mpr-print-css', MPR_PLUGIN_URL . 'assets/css/print.css', [], '0.1.0', 'print');

    // New Multi-step Form Assets
    wp_enqueue_style('mpr-form-styles', MPR_PLUGIN_URL . 'assets/css/form-styles.css', array(), '0.1.0');
    wp_enqueue_script('mpr-form-steps', MPR_PLUGIN_URL . 'assets/js/form-steps.js', array('jquery'), '0.1.0', true);

    // Follow system AJAX script - only load on single missing_person posts
    if (is_singular('missing_person')) {
        // Enqueue Leaflet for Frontend
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);

        wp_enqueue_script(
            'mpr-frontend-map-js',
            MPR_PLUGIN_URL . 'assets/js/frontend-map.js',
        ['leaflet-js'],
            '1.0.0',
            true
        );

        wp_localize_script('mpr-frontend-map-js', 'mpr_map_data', [
            'lat' => get_post_meta(get_the_ID(), 'mpr_latitude', true),
            'lng' => get_post_meta(get_the_ID(), 'mpr_longitude', true),
            'name' => get_the_title()
        ]);

        wp_enqueue_script(
            'mpr-follow-js',
            MPR_PLUGIN_URL . 'assets/js/follow.js',
        ['jquery'],
            '1.0.0',
            true
        );
        // Pass data to the script, like the AJAX URL and a nonce for security
        wp_localize_script('mpr-follow-js', 'mpr_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mpr_follow_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mpr_enqueue_assets');

// Enqueue admin scripts for media uploader
function mpr_enqueue_admin_assets($hook)
{
    if ($hook === 'missing_person_page_mpr-notification-settings') {
        wp_enqueue_script('mpr-admin-notifications', MPR_PLUGIN_URL . 'assets/js/admin-notifications.js', array('jquery'), '1.0.0', true);
        wp_localize_script('mpr-admin-notifications', 'mpr_admin_notif_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mpr_notification_test_nonce'),
            'sending_msg' => __('Sending test push...', 'mpr'),
            'success_msg' => __('Success!', 'mpr'),
            'error_msg' => __('Error: ', 'mpr'),
        ));
    }

    global $post; // Make the $post global available

    // Check if we are on a post edit screen ('post.php') or new post screen ('post-new.php')
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        // For 'post-new.php', check the 'post_type' query parameter
        // For 'post.php', check the $post global object's post_type
        $current_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : ($post->post_type ?? '');

        if ('missing_person' === $current_post_type) {
            wp_enqueue_media(); // Enqueue the WordPress media scripts

            // Enqueue Leaflet for Admin
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);

            wp_enqueue_script(
                'mpr-admin-map-js',
                MPR_PLUGIN_URL . 'assets/js/admin-map.js',
            ['leaflet-js', 'jquery'],
                '1.0.0',
                true
            );

            wp_localize_script('mpr-admin-map-js', 'mpr_admin_map_vars', [
                'lat' => get_post_meta($post->ID, 'mpr_latitude', true),
                'lng' => get_post_meta($post->ID, 'mpr_longitude', true),
            ]);

            wp_enqueue_script(
                'mpr-media-uploader-js',
                MPR_PLUGIN_URL . 'assets/js/media-uploader.js',
            ['jquery'],
                '1.0.0',
                true
            );
            // Localize script to pass ajaxurl, which is needed in your media-uploader.js
            wp_localize_script('mpr-media-uploader-js', 'mpr_admin_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mpr_media_nonce'), // Add a nonce for the AJAX call
            ]);
        }
    }
}
add_action('admin_enqueue_scripts', 'mpr_enqueue_admin_assets');

/**
 * AJAX handler to get thumbnail URL for the admin media uploader.
 * This is called from assets/js/media-uploader.js to display existing additional images.
 */
function mpr_get_attachment_thumbnail_url_ajax()
{
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mpr_media_nonce')) {
        wp_send_json_error('Nonce verification failed.');
    }

    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
    if ($attachment_id === 0) {
        wp_send_json_error('Invalid attachment ID.');
    }

    $url = wp_get_attachment_image_url($attachment_id, 'thumbnail'); // Using 'thumbnail' size
    if ($url) {
        wp_send_json_success(['url' => $url]);
    }
    else {
        wp_send_json_error('Could not get attachment URL.');
    }
}
add_action('wp_ajax_get_attachment_thumbnail_url', 'mpr_get_attachment_thumbnail_url_ajax');
// Removing nopriv hook for security as this is an admin uploader helper
// add_action('wp_ajax_nopriv_get_attachment_thumbnail_url', 'mpr_get_attachment_thumbnail_url_ajax');

/**
 * Modern Helper to get the best image for a missing person case.
 * Logic: Featured Image -> First Attachment -> Regex from Content -> Placeholder.
 */
function mpr_get_case_image_url($post_id, $size = 'medium')
{
    // 1. Featured Image
    if (has_post_thumbnail($post_id)) {
        $img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);
        if ($img)
            return $img[0];
    }

    // 2. Attached Images
    $attachments = get_children([
        'post_parent' => $post_id,
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'numberposts' => 1,
        'orderby' => 'menu_order ID',
        'order' => 'ASC',
    ]);
    if ($attachments) {
        $attachment = reset($attachments);
        $img = wp_get_attachment_image_src($attachment->ID, $size);
        if ($img)
            return $img[0];
    }

    // 3. Regex from Content
    $post = get_post($post_id);
    if ($post) {
        preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
        if (!empty($matches[1]))
            return $matches[1];
    }

    // 4. Placeholder
    return MPR_PLUGIN_URL . 'assets/images/placeholder.svg';
}

/**
 * High-performance helper to get case statistics using direct SQL.
 */
function mpr_get_stats_counts()
{
    global $wpdb;
    $table = $wpdb->postmeta;
    $posts_table = $wpdb->posts;

    $results = $wpdb->get_results("
        SELECT meta_value as label, COUNT(*) as count 
        FROM $table pm
        JOIN $posts_table p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'mpr_case_status' 
        AND p.post_status = 'publish'
        GROUP BY meta_value
    ");

    $stats = [
        'Missing' => 0,
        'Found - Safe' => 0,
        'Found - Deceased' => 0,
        'Cold Case' => 0,
    ];

    foreach ($results as $row) {
        $stats[$row->label] = (int)$row->count;
    }

    // Risk levels
    $risk_results = $wpdb->get_results("
        SELECT meta_value as label, COUNT(*) as count 
        FROM $table pm
        JOIN $posts_table p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'mpr_risk_level' 
        AND p.post_status = 'publish'
        GROUP BY meta_value
    ");

    $risks = ['High' => 0, 'Medium' => 0, 'Low' => 0];
    foreach ($risk_results as $row) {
        $risks[$row->label] = (int)$row->count;
    }

    return [
        'status' => $stats,
        'risk' => $risks,
        'total' => (int)$wpdb->get_var("SELECT COUNT(*) FROM $posts_table WHERE post_type = 'missing_person' AND post_status = 'publish'")
    ];
}
