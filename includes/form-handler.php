<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// in includes/form-handler.php

function mpr_handle_public_submission() {
    if (isset($_POST['mpr_submit_public_report']) && isset($_POST['mpr_public_nonce'])) {
        if (!wp_verify_nonce($_POST['mpr_public_nonce'], 'mpr_public_submission')) {
            wp_die('Security check failed!');
        }

        // Require necessary WordPress files for media handling
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $post_data = [
            'post_title'   => sanitize_text_field($_POST['mpr_full_name']),
            'post_content' => wp_kses_post($_POST['mpr_description']),
            'post_status'  => 'pending',
            'post_type'    => 'missing_person',
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
                            'name'     => $files['name'][$key],
                            'type'     => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error'    => $files['error'][$key],
                            'size'     => $files['size'][$key]
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

            $meta_keys = [
                'mpr_nickname', 'mpr_age', 'mpr_dob', 'mpr_gender', 'mpr_height', 'mpr_body_type', 'mpr_weight',
                'mpr_hair_color', 'mpr_hair_style', 'mpr_eye_color', 'mpr_piercings', 'mpr_tattoos',
                'mpr_date_last_seen', 'mpr_last_seen_location', 'mpr_what_they_were_wearing',
                'mpr_police_station', 'mpr_ob_number', 'mpr_police_phone', 'mpr_police_email', 'mpr_investigating_officer', 
                'mpr_contact_person', 'mpr_contact_person_email'
            ];

            foreach ($meta_keys as $key) {
                if (isset($_POST[$key])) {
                     $value = sanitize_text_field($_POST[$key]);
                     if (substr($key, -5) === '_email') { // Use sanitize_email for email fields
                         $value = sanitize_email($_POST[$key]);
                     }
                    update_post_meta($post_id, $key, $value);
                }
            }
            if (isset($_POST['mpr_distinguishing_features'])) {
                 update_post_meta($post_id, 'mpr_distinguishing_features', sanitize_textarea_field($_POST['mpr_distinguishing_features']));
            }

            // Admin Notification Email
            $admin_email = get_option('admin_email');
            $subject = 'New Missing Person Report Submitted: ' . sanitize_text_field($_POST['mpr_full_name']);
            $edit_link = admin_url('post.php?post=' . $post_id . '&action=edit');
            $message = "A new missing person report has been submitted on your website.\n\n";
            $message .= "Name: " . sanitize_text_field($_POST['mpr_full_name']) . "\n";
            $message .= "Please review and publish it here: " . $edit_link . "\n";
            
            wp_mail($admin_email, $subject, $message);


            $redirect_url = home_url('/thank-you'); // Create a "Thank You" page in WordPress
            wp_redirect($redirect_url);
            exit;
        }
    }
}
add_action('init', 'mpr_handle_public_submission');