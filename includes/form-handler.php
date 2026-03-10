<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// in includes/form-handler.php

function mpr_handle_public_submission()
{
    if (isset($_POST['mpr_submit_public_report']) && isset($_POST['mpr_public_nonce'])) {
        if (!wp_verify_nonce($_POST['mpr_public_nonce'], 'mpr_public_submission')) {
            wp_die('Security check failed!');
        }

        // Require necessary WordPress files for media handling
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $post_data = [
            'post_title' => sanitize_text_field($_POST['mpr_full_name']),
            'post_content' => wp_kses_post($_POST['mpr_description']),
            'post_status' => 'pending',
            'post_type' => 'missing_person',
        ];
        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Handle main featured image upload
            if (isset($_FILES['mpr_main_image']) && $_FILES['mpr_main_image']['error'] == 0) {
                $attachment_id = media_handle_upload('mpr_main_image', $post_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id); // Set as featured image
                }
            }

            // Handle "Other Images" gallery upload
            if (isset($_FILES['mpr_other_images']) && !empty($_FILES['mpr_other_images']['name'][0])) {
                $other_image_ids = [];
                $files = $_FILES['mpr_other_images'];

                // Loop through each file in the array
                foreach ($files['name'] as $key => $value) {
                    if ($files['name'][$key]) {
                        $file = array(
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key]
                        );

                        // Temporarily re-assign to a new key in $_FILES for media_handle_upload to work
                        $_FILES['temp_gallery_file'] = $file;
                        $attachment_id = media_handle_upload('temp_gallery_file', $post_id);

                        if (!is_wp_error($attachment_id)) {
                            $other_image_ids[] = $attachment_id;
                        }
                    }
                }

                // Clean up the temporary key
                unset($_FILES['temp_gallery_file']);

                // If images were uploaded, save the IDs as a comma-separated string
                if (!empty($other_image_ids)) {
                    update_post_meta($post_id, 'mpr_other_images', implode(',', $other_image_ids));
                }
            }

            // Use centralized meta keys for standard fields
            $meta_keys = mpr_get_meta_keys();

            foreach ($meta_keys as $key => $args) {
                if (isset($_POST[$key])) {
                    $value = $_POST[$key];

                    // Use specialized sanitization if defined
                    if (isset($args['sanitize']) && function_exists($args['sanitize'])) {
                        $value = call_user_func($args['sanitize'], $value);
                    }
                    else {
                        $value = sanitize_text_field($value);
                    }

                    update_post_meta($post_id, $key, $value);
                }
            }

            // Admin Notification Email
            $admin_email = get_option('admin_email');
            $subject = sprintf(__('New Missing Person Report Submitted: %s', 'mpr'), sanitize_text_field($_POST['mpr_full_name']));
            $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
            $message = __("A new missing person report has been submitted on your website.\n\n", 'mpr');
            $message .= sprintf(__('Name: %s', 'mpr'), sanitize_text_field($_POST['mpr_full_name'])) . "\n";
            $message .= sprintf(__('Please review and publish it here: %s', 'mpr'), $edit_link) . "\n";

            wp_mail($admin_email, $subject, $message);


            $redirect_url = home_url('/thank-you'); // Create a "Thank You" page in WordPress
            wp_redirect($redirect_url);
            exit;
        }
    }
}
add_action('init', 'mpr_handle_public_submission');