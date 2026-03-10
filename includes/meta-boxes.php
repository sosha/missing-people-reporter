<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// 1. Add the Meta Box to the 'missing_person' CPT
function mpr_add_meta_boxes()
{
    add_meta_box(
        'mpr_details_meta_box', // ID
        'Missing Person Details', // Title
        'mpr_render_meta_box_fields', // Callback function to render HTML
        'missing_person', // Post Type
        'normal', // Context (normal, side)
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'mpr_add_meta_boxes');

// 2. Render the HTML fields for the Meta Box (COMPLETE VERSION)
function mpr_render_meta_box_fields($post)
{
    // Add a nonce field for security
    wp_nonce_field('mpr_save_meta_box_data', 'mpr_meta_box_nonce');

    // Retrieve existing values from the database
    $meta = get_post_meta($post->ID);

    // Helper function to get meta value
    $get_meta = function ($key) use ($meta) {
        return $meta[$key][0] ?? '';
    };

?>
    <style>
        .mpr-meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .mpr-meta-grid .full-width { grid-column: 1 / -1; }
        .mpr-meta-grid p { margin-top: 0; }
        .mpr-meta-grid label { font-weight: bold; display: block; margin-bottom: 5px;}
        .mpr-meta-grid input[type="text"],
        .mpr-meta-grid input[type="email"],
        .mpr-meta-grid input[type="number"],
        .mpr-meta-grid input[type="date"],
        .mpr-meta-grid select { width: 100%; padding: 5px; }
        .mpr-other-images-wrapper img { max-width: 100px; height: auto; margin: 5px; border: 1px solid #ddd; }
    </style>

    <div class="mpr-meta-grid">
        <p>
            <label for="mpr_case_status">Case Status:</label>
            <select id="mpr_case_status" name="mpr_case_status">
                <option value="Missing" <?php selected($get_meta('mpr_case_status'), 'Missing'); ?>>Missing</option>
                <option value="Found - Safe" <?php selected($get_meta('mpr_case_status'), 'Found - Safe'); ?>>Found - Safe</option>
                <option value="Found - Deceased" <?php selected($get_meta('mpr_case_status'), 'Found - Deceased'); ?>>Found - Deceased</option>
                <option value="Cold Case" <?php selected($get_meta('mpr_case_status'), 'Cold Case'); ?>>Cold Case</option>
            </select>
        </p>
        <p>
            <label for="mpr_nickname">Nickname:</label>
            <input type="text" id="mpr_nickname" name="mpr_nickname" value="<?php echo esc_attr($get_meta('mpr_nickname')); ?>">
        </p>
        <p>
            <label for="mpr_age">Age:</label>
            <input type="number" id="mpr_age" name="mpr_age" value="<?php echo esc_attr($get_meta('mpr_age')); ?>">
        </p>
        <p>
            <label for="mpr_dob">Date of Birth:</label>
            <input type="date" id="mpr_dob" name="mpr_dob" value="<?php echo esc_attr($get_meta('mpr_dob')); ?>">
        </p>
        <p>
            <label for="mpr_gender">Gender:</label>
            <select id="mpr_gender" name="mpr_gender">
                <option value="">Select Gender</option>
                <option value="Male" <?php selected($get_meta('mpr_gender'), 'Male'); ?>>Male</option>
                <option value="Female" <?php selected($get_meta('mpr_gender'), 'Female'); ?>>Female</option>
                <option value="Other" <?php selected($get_meta('mpr_gender'), 'Other'); ?>>Other</option>
            </select>
        </p>
        <p>
            <label for="mpr_height">Height:</label>
            <input type="text" id="mpr_height" name="mpr_height" value="<?php echo esc_attr($get_meta('mpr_height')); ?>" placeholder="e.g., 5' 10&quot;">
        </p>
        <p>
            <label for="mpr_body_type">Body Type:</label>
            <input type="text" id="mpr_body_type" name="mpr_body_type" value="<?php echo esc_attr($get_meta('mpr_body_type')); ?>" placeholder="e.g., Slim, Athletic">
        </p>
        <p>
            <label for="mpr_weight">Weight:</label>
            <input type="text" id="mpr_weight" name="mpr_weight" value="<?php echo esc_attr($get_meta('mpr_weight')); ?>" placeholder="e.g., 150 lbs or 68 kg">
        </p>
        <p>
            <label for="mpr_hair_color">Hair Color:</label>
            <input type="text" id="mpr_hair_color" name="mpr_hair_color" value="<?php echo esc_attr($get_meta('mpr_hair_color')); ?>">
        </p>
        <p>
            <label for="mpr_hair_style">Hair Style:</label>
            <input type="text" id="mpr_hair_style" name="mpr_hair_style" value="<?php echo esc_attr($get_meta('mpr_hair_style')); ?>">
        </p>
        <p>
            <label for="mpr_eye_color">Eye Color:</label>
            <input type="text" id="mpr_eye_color" name="mpr_eye_color" value="<?php echo esc_attr($get_meta('mpr_eye_color')); ?>">
        </p>
        <div class="full-width">
            <label for="mpr_distinguishing_features">Distinguishing Features:</label>
            <?php wp_editor($get_meta('mpr_distinguishing_features'), 'mpr_distinguishing_features', ['textarea_rows' => 5, 'media_buttons' => false]); ?>
        </div>
        <p>
            <label for="mpr_piercings">Piercings:</label>
            <input type="text" id="mpr_piercings" name="mpr_piercings" value="<?php echo esc_attr($get_meta('mpr_piercings')); ?>">
        </p>
        <p>
            <label for="mpr_tattoos">Tattoos:</label>
            <input type="text" id="mpr_tattoos" name="mpr_tattoos" value="<?php echo esc_attr($get_meta('mpr_tattoos')); ?>">
        </p>
        <hr class="full-width">
        <p>
            <label for="mpr_date_last_seen">Date Last Seen:</label>
            <input type="date" id="mpr_date_last_seen" name="mpr_date_last_seen" value="<?php echo esc_attr($get_meta('mpr_date_last_seen')); ?>">
        </p>
        <p>
            <label for="mpr_last_seen_location">Last Seen Location:</label>
            <input type="text" id="mpr_last_seen_location" name="mpr_last_seen_location" value="<?php echo esc_attr($get_meta('mpr_last_seen_location')); ?>">
        </p>
        <p class="full-width">
            <label for="mpr_what_they_were_wearing">What they were wearing:</label>
            <input type="text" id="mpr_what_they_were_wearing" name="mpr_what_they_were_wearing" value="<?php echo esc_attr($get_meta('mpr_what_they_were_wearing')); ?>">
        </p>
        <hr class="full-width">
        <p>
            <label for="mpr_police_station">Police Station Reported to:</label>
            <input type="text" id="mpr_police_station" name="mpr_police_station" value="<?php echo esc_attr($get_meta('mpr_police_station')); ?>">
        </p>
        <p>
            <label for="mpr_ob_number">Occurrence Book (OB) Number:</label>
            <input type="text" id="mpr_ob_number" name="mpr_ob_number" value="<?php echo esc_attr($get_meta('mpr_ob_number')); ?>">
        </p>
        <p>
            <label for="mpr_police_phone">Police Station Telephone:</label>
            <input type="text" id="mpr_police_phone" name="mpr_police_phone" value="<?php echo esc_attr($get_meta('mpr_police_phone')); ?>">
        </p>
        <p>
            <label for="mpr_police_email">Police Email:</label>
            <input type="email" id="mpr_police_email" name="mpr_police_email" value="<?php echo esc_attr($get_meta('mpr_police_email')); ?>">
        </p>
        <p>
            <label for="mpr_investigating_officer">Investigating Officer:</label>
            <input type="text" id="mpr_investigating_officer" name="mpr_investigating_officer" value="<?php echo esc_attr($get_meta('mpr_investigating_officer')); ?>">
        </p>
         <p>
            <label for="mpr_contact_person">Family/Public Contact Person:</label>
            <input type="text" id="mpr_contact_person" name="mpr_contact_person" value="<?php echo esc_attr($get_meta('mpr_contact_person')); ?>">
        </p>
         <p>
            <label for="mpr_contact_person_email">Family/Public Contact Email:</label>
            <input type="email" id="mpr_contact_person_email" name="mpr_contact_person_email" value="<?php echo esc_attr($get_meta('mpr_contact_person_email')); ?>">
        </p>
        <div class="full-width">
            <h4>Other Images</h4>
            <div class="other-images-wrapper"></div>
            <input type="hidden" id="mpr_other_images" name="mpr_other_images" value="<?php echo esc_attr($get_meta('mpr_other_images')); ?>">
            <button type="button" class="button" id="mpr_upload_other_images_button">Upload / Manage Other Images</button>
        </div>
    </div>
    <?php
}

// 3. Save the custom field data (COMPLETE VERSION)
function mpr_save_meta_box_data($post_id)
{
    if (!isset($_POST['mpr_meta_box_nonce']) || !wp_verify_nonce($_POST['mpr_meta_box_nonce'], 'mpr_save_meta_box_data'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;
    if (wp_is_post_revision($post_id))
        return;

    // Define all the meta keys to loop through
    $meta_keys = [
        'mpr_nickname', 'mpr_age', 'mpr_dob', 'mpr_gender', 'mpr_height', 'mpr_body_type', 'mpr_weight',
        'mpr_hair_color', 'mpr_hair_style', 'mpr_eye_color', 'mpr_piercings', 'mpr_tattoos',
        'mpr_date_last_seen', 'mpr_last_seen_location', 'mpr_what_they_were_wearing',
        'mpr_police_station', 'mpr_ob_number', 'mpr_police_phone', 'mpr_police_email', 'mpr_investigating_officer',
        'mpr_contact_person', 'mpr_contact_person_email', 'mpr_other_images', 'mpr_case_status'
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

    // Special handling for rich text editor field
    if (isset($_POST['mpr_distinguishing_features'])) {
        update_post_meta($post_id, 'mpr_distinguishing_features', wp_kses_post($_POST['mpr_distinguishing_features']));
    }
}
add_action('save_post_missing_person', 'mpr_save_meta_box_data');