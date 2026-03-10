<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add Meta Box for Missing Person Details
function mpr_add_missing_person_meta_box() {
    add_meta_box(
        'mpr_details_meta_box', // ID of the meta box
        __('Missing Person Details', 'your-plugin-textdomain'), // Title of the meta box
        'mpr_display_missing_person_meta_box', // Callback function to display the fields
        'missing_person', // Custom post type to which the meta box is added
        'normal', // Context (where on the screen)
        'high' // Priority
    );

    add_meta_box(
        'mpr_contact_meta_box', // ID for contact info
        __('Contact Information', 'your-plugin-textdomain'), // Title for contact info
        'mpr_display_contact_meta_box', // Callback for contact info
        'missing_person',
        'normal',
        'high'
    );

    add_meta_box(
        'mpr_police_meta_box', // ID for police info
        __('Police Information', 'your-plugin-textdomain'), // Title for police info
        'mpr_display_police_meta_box', // Callback for police info
        'missing_person',
        'normal',
        'high'
    );

    add_meta_box(
        'mpr_other_images_meta_box', // ID for other images
        __('Other Images', 'your-plugin-textdomain'), // Title for other images
        'mpr_display_other_images_meta_box', // Callback for other images
        'missing_person',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mpr_add_missing_person_meta_box');

// Callback function to display Missing Person Details fields
function mpr_display_missing_person_meta_box($post) {
    // Add a nonce field for security
    wp_nonce_field('mpr_save_missing_person_data', 'mpr_meta_box_nonce');

    // Get current meta values
    $full_name = get_post_meta($post->ID, 'mpr_full_name', true);
    $nickname = get_post_meta($post->ID, 'mpr_nickname', true);
    $age = get_post_meta($post->ID, 'mpr_age', true);
    $dob = get_post_meta($post->ID, 'mpr_dob', true);
    $gender = get_post_meta($post->ID, 'mpr_gender', true);
    $height = get_post_meta($post->ID, 'mpr_height', true);
    $body_type = get_post_meta($post->ID, 'mpr_body_type', true);
    $weight = get_post_meta($post->ID, 'mpr_weight', true);
    $hair_color = get_post_meta($post->ID, 'mpr_hair_color', true);
    $hair_style = get_post_meta($post->ID, 'mpr_hair_style', true);
    $eye_color = get_post_meta($post->ID, 'mpr_eye_color', true);
    $distinguishing_features = get_post_meta($post->ID, 'mpr_distinguishing_features', true);
    $piercings = get_post_meta($post->ID, 'mpr_piercings', true);
    $tattoos = get_post_meta($post->ID, 'mpr_tattoos', true);
    $date_last_seen = get_post_meta($post->ID, 'mpr_date_last_seen', true);
    $last_seen_location = get_post_meta($post->ID, 'mpr_last_seen_location', true);
    $what_they_were_wearing = get_post_meta($post->ID, 'mpr_what_they_were_wearing', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mpr_full_name">Full Name</label></th>
            <td><input type="text" id="mpr_full_name" name="mpr_full_name" value="<?php echo esc_attr($full_name); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_nickname">Nickname</label></th>
            <td><input type="text" id="mpr_nickname" name="mpr_nickname" value="<?php echo esc_attr($nickname); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_age">Age</label></th>
            <td><input type="number" id="mpr_age" name="mpr_age" value="<?php echo esc_attr($age); ?>" class="small-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_dob">Date of Birth</label></th>
            <td><input type="date" id="mpr_dob" name="mpr_dob" value="<?php echo esc_attr($dob); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_gender">Gender</label></th>
            <td>
                <select name="mpr_gender" id="mpr_gender">
                    <option value="">Select...</option>
                    <option value="Male" <?php selected($gender, 'Male'); ?>>Male</option>
                    <option value="Female" <?php selected($gender, 'Female'); ?>>Female</option>
                    <option value="Other" <?php selected($gender, 'Other'); ?>>Other</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="mpr_height">Height</label></th>
            <td><input type="text" id="mpr_height" name="mpr_height" value="<?php echo esc_attr($height); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_body_type">Body Type</label></th>
            <td><input type="text" id="mpr_body_type" name="mpr_body_type" value="<?php echo esc_attr($body_type); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_weight">Weight</label></th>
            <td><input type="text" id="mpr_weight" name="mpr_weight" value="<?php echo esc_attr($weight); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_hair_color">Hair Color</label></th>
            <td><input type="text" id="mpr_hair_color" name="mpr_hair_color" value="<?php echo esc_attr($hair_color); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_hair_style">Hair Style</label></th>
            <td><input type="text" id="mpr_hair_style" name="mpr_hair_style" value="<?php echo esc_attr($hair_style); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_eye_color">Eye Color</label></th>
            <td><input type="text" id="mpr_eye_color" name="mpr_eye_color" value="<?php echo esc_attr($eye_color); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_distinguishing_features">Distinguishing Features</label></th>
            <td><textarea id="mpr_distinguishing_features" name="mpr_distinguishing_features" rows="3" class="large-text"><?php echo esc_textarea($distinguishing_features); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="mpr_piercings">Piercings</label></th>
            <td><input type="text" id="mpr_piercings" name="mpr_piercings" value="<?php echo esc_attr($piercings); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_tattoos">Tattoos</label></th>
            <td><input type="text" id="mpr_tattoos" name="mpr_tattoos" value="<?php echo esc_attr($tattoos); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_date_last_seen">Date Last Seen</label></th>
            <td><input type="date" id="mpr_date_last_seen" name="mpr_date_last_seen" value="<?php echo esc_attr($date_last_seen); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_last_seen_location">Last Seen Location</label></th>
            <td><input type="text" id="mpr_last_seen_location" name="mpr_last_seen_location" value="<?php echo esc_attr($last_seen_location); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_what_they_were_wearing">What they were wearing</label></th>
            <td><textarea id="mpr_what_they_were_wearing" name="mpr_what_they_were_wearing" rows="3" class="large-text"><?php echo esc_textarea($what_they_were_wearing); ?></textarea></td>
        </tr>
    </table>
    <?php
}

// Callback function to display Contact Information fields
function mpr_display_contact_meta_box($post) {
    $contact_person = get_post_meta($post->ID, 'mpr_contact_person', true);
    $contact_email = get_post_meta($post->ID, 'mpr_contact_email', true); // NEW FIELD
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mpr_contact_person">Contact Person Name</label></th>
            <td><input type="text" id="mpr_contact_person" name="mpr_contact_person" value="<?php echo esc_attr($contact_person); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_contact_email">Contact Person Email</label></th>
            <td><input type="email" id="mpr_contact_email" name="mpr_contact_email" value="<?php echo esc_attr($contact_email); ?>" class="large-text"></td>
        </tr>
    </table>
    <?php
}

// Callback function to display Police Information fields
function mpr_display_police_meta_box($post) {
    $police_station = get_post_meta($post->ID, 'mpr_police_station', true);
    $ob_number = get_post_meta($post->ID, 'mpr_ob_number', true);
    $police_phone = get_post_meta($post->ID, 'mpr_police_phone', true);
    $police_email = get_post_meta($post->ID, 'mpr_police_email', true); // NEW FIELD
    $investigating_officer = get_post_meta($post->ID, 'mpr_investigating_officer', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="mpr_police_station">Police Station Reported to</label></th>
            <td><input type="text" id="mpr_police_station" name="mpr_police_station" value="<?php echo esc_attr($police_station); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_ob_number">Police OB Number</label></th>
            <td><input type="text" id="mpr_ob_number" name="mpr_ob_number" value="<?php echo esc_attr($ob_number); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_police_phone">Police Station Telephone</label></th>
            <td><input type="text" id="mpr_police_phone" name="mpr_police_phone" value="<?php echo esc_attr($police_phone); ?>" class="medium-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_police_email">Police Station Email</label></th>
            <td><input type="email" id="mpr_police_email" name="mpr_police_email" value="<?php echo esc_attr($police_email); ?>" class="large-text"></td>
        </tr>
        <tr>
            <th><label for="mpr_investigating_officer">Investigating Officer</label></th>
            <td><input type="text" id="mpr_investigating_officer" name="mpr_investigating_officer" value="<?php echo esc_attr($investigating_officer); ?>" class="large-text"></td>
        </tr>
    </table>
    <?php
}

// Callback function to display Other Images (no changes needed)
function mpr_display_other_images_meta_box($post) {
    $other_image_ids = get_post_meta($post->ID, 'mpr_other_images', true);
    $image_ids_array = array_filter(array_map('intval', explode(',', $other_image_ids)));

    if (!empty($image_ids_array)) {
        echo '<div class="mpr-admin-other-images-gallery" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 10px;">';
        foreach ($image_ids_array as $image_id) {
            echo '<div style="text-align: center; border: 1px solid #eee; padding: 5px; border-radius: 4px;">';
            echo wp_get_attachment_image($image_id, 'thumbnail'); // Display thumbnail
            // Optionally add a link to edit/remove image directly
            echo '<br><a href="' . get_edit_post_link($image_id) . '" target="_blank">Edit</a>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No additional images uploaded.</p>';
    }
    echo '<p class="description">These images are collected from the public submission form (if any) or can be added/managed directly via the Media Library by attaching them to this post.</p>';
}

// Save Meta Box Data
function mpr_save_missing_person_meta_data($post_id) {
    // Check if our nonce is set and verify it.
    if (!isset($_POST['mpr_meta_box_nonce']) || !wp_verify_nonce($_POST['mpr_meta_box_nonce'], 'mpr_save_missing_person_data')) {
        return $post_id;
    }

    // Check if current user has permission to edit the post.
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check if this is our custom post type
    if (isset($_POST['post_type']) && 'missing_person' == $_POST['post_type']) {
        // Sanitize and save all fields
        $fields = [
            'mpr_full_name', 'mpr_nickname', 'mpr_age', 'mpr_dob', 'mpr_gender',
            'mpr_height', 'mpr_body_type', 'mpr_weight', 'mpr_hair_color', 'mpr_hair_style',
            'mpr_eye_color', 'mpr_distinguishing_features', 'mpr_piercings', 'mpr_tattoos',
            'mpr_date_last_seen', 'mpr_last_seen_location', 'mpr_what_they_were_wearing',
            'mpr_police_station', 'mpr_ob_number', 'mpr_police_phone', 'mpr_investigating_officer',
            'mpr_contact_person'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            } else {
                delete_post_meta($post_id, $field); // Delete if not set (e.g., checkbox not checked)
            }
        }
        
        // Sanitize and save email fields specifically
        if (isset($_POST['mpr_police_email'])) {
            update_post_meta($post_id, 'mpr_police_email', sanitize_email($_POST['mpr_police_email']));
        } else {
            delete_post_meta($post_id, 'mpr_police_email');
        }

        if (isset($_POST['mpr_contact_email'])) {
            update_post_meta($post_id, 'mpr_contact_email', sanitize_email($_POST['mpr_contact_email']));
        } else {
            delete_post_meta($post_id, 'mpr_contact_email');
        }

        // Note: The main post_content (description) is saved by WordPress itself.
    }
}
add_action('save_post', 'mpr_save_missing_person_meta_data');