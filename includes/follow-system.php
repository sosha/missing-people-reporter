<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Displays the follow/unfollow button.
 */
function mpr_display_follow_button($post_id) {
    if (!is_user_logged_in()) {
        echo '<p><a href="' . wp_login_url(get_permalink()) . '">Log in to follow this case</a></p>';
        return;
    }

    $user_id = get_current_user_id();
    $followed_cases = get_user_meta($user_id, 'mpr_followed_cases', true);

    if (!is_array($followed_cases)) {
        $followed_cases = [];
    }

    $is_following = in_array($post_id, $followed_cases);
    $button_text = $is_following ? 'Unfollow Case' : 'Follow Case';
    $button_class = $is_following ? 'mpr-unfollow-btn' : 'mpr-follow-btn';

    echo '<button data-post-id="' . esc_attr($post_id) . '" data-user-id="' . esc_attr($user_id) . '" class="button ' . esc_attr($button_class) . '">' . esc_html($button_text) . '</button>';
}

/**
 * AJAX handler to toggle follow status.
 */
function mpr_handle_follow_ajax() {
    // Security check
    check_ajax_referer('mpr_follow_nonce', 'nonce');

    if (!is_user_logged_in() || !isset($_POST['post_id'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $user_id = get_current_user_id();
    $followed_cases = get_user_meta($user_id, 'mpr_followed_cases', true);
    if (!is_array($followed_cases)) {
        $followed_cases = [];
    }
    
    // Check if the user is already following
    if (in_array($post_id, $followed_cases)) {
        // Unfollow: remove the post_id from the array
        $followed_cases = array_diff($followed_cases, [$post_id]);
        $action = 'unfollowed';
    } else {
        // Follow: add the post_id to the array
        $followed_cases[] = $post_id;
        $action = 'followed';
    }

    // Update the user meta
    update_user_meta($user_id, 'mpr_followed_cases', $followed_cases);
    
    wp_send_json_success(['action' => $action]);
}
add_action('wp_ajax_mpr_toggle_follow', 'mpr_handle_follow_ajax');