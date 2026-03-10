<?php
/*
Plugin Name: Missing People Reporter
Plugin URI: https://missing.ke
Description: A comprehensive plugin to create, manage, and display reports for missing people.
Version: 1.0.0
Author: Mentaltude / Gemini
Author URI: https://missing.ke
License: GPLv2 or later
Text Domain: mpr
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

// Plugin Activation Hook
register_activation_hook(__FILE__, 'mpr_activate_plugin');

function mpr_activate_plugin() {
    // The CPT is registered via 'init' hook in includes/post-type.php.
    // We only need to flush rewrite rules here for activation to ensure permalinks work immediately.
    flush_rewrite_rules();

    // Register custom meta fields for 'missing_person' post type
    // These need to be registered at activation or init if they are not defined in includes/post-type.php directly
    // This duplication is often seen in plugins for robustness, but ideally, meta box registration handles update/save.
    // However, for REST API exposure or specific behaviors, direct registration here is common.
    register_post_meta('missing_person', 'mpr_full_name', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_nickname', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_age', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_dob', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_gender', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_height', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_body_type', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_weight', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_hair_color', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_hair_style', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_eye_color', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_distinguishing_features', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_piercings', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_tattoos', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_date_last_seen', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_last_seen_location', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_what_they_were_wearing', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_police_station', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_ob_number', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_police_phone', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_police_email', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_investigating_officer', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_contact_person', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
    register_post_meta('missing_person', 'mpr_contact_email', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'auth_callback'     => '__return_true',
    ));
    // Important: The mpr_other_images meta field should store a comma-separated string of attachment IDs.
    register_post_meta('missing_person', 'mpr_other_images', array(
        'show_in_rest'      => true, // Exposed to REST API if needed
        'single'            => true, // Stored as a single value (comma-separated string)
        'type'              => 'string', // Type is string
        'sanitize_callback' => 'sanitize_text_field', // Sanitize as text field
        'auth_callback'     => '__return_true', // Allow all users to read/write for now, refine as needed
    ));
    // Case Status field
    register_post_meta('missing_person', 'mpr_case_status', array(
        'show_in_rest'      => true,
        'single'            => true,
        'type'              => 'string',
        'default'           => 'Missing',
        'sanitize_callback' => 'sanitize_text_field',
        'auth_callback'     => '__return_true',
    ));
}

// Plugin Deactivation Hook (optional, but good practice)
register_deactivation_hook(__FILE__, 'mpr_deactivate_plugin');

function mpr_deactivate_plugin() {
    flush_rewrite_rules();
}

// Enqueue scripts and styles for frontend
function mpr_enqueue_assets() {
    // Frontend styles
    wp_enqueue_style(
        'mpr-frontend-css',
        MPR_PLUGIN_URL . 'assets/css/frontend.css',
        [],
        '1.0.0'
    );

    // Follow system AJAX script - only load on single missing_person posts
    if (is_singular('missing_person')) {
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
            'nonce'    => wp_create_nonce('mpr_follow_nonce')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mpr_enqueue_assets');

// Enqueue admin scripts for media uploader
function mpr_enqueue_admin_assets($hook) {
    global $post; // Make the $post global available

    // Check if we are on a post edit screen ('post.php') or new post screen ('post-new.php')
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        // For 'post-new.php', check the 'post_type' query parameter
        // For 'post.php', check the $post global object's post_type
        $current_post_type = isset($_GET['post_type']) ? $_GET['post_type'] : ($post->post_type ?? '');

        if ('missing_person' === $current_post_type) {
            wp_enqueue_media(); // Enqueue the WordPress media scripts
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
                'nonce'    => wp_create_nonce('mpr_media_nonce'), // Add a nonce for the AJAX call
            ]);
        }
    }
}
add_action('admin_enqueue_scripts', 'mpr_enqueue_admin_assets');

/**
 * AJAX handler to get thumbnail URL for the admin media uploader.
 * This is called from assets/js/media-uploader.js to display existing additional images.
 */
function mpr_get_attachment_thumbnail_url_ajax() {
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
    } else {
        wp_send_json_error('Could not get attachment URL.');
    }
}
add_action('wp_ajax_get_attachment_thumbnail_url', 'mpr_get_attachment_thumbnail_url_ajax');
add_action('wp_ajax_nopriv_get_attachment_thumbnail_url', 'mpr_get_attachment_thumbnail_url_ajax'); // If non-logged in users need this (unlikely for admin function)