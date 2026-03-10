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
        __('Missing Person Details', 'mpr'), // Title
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
            <label for="mpr_full_name"><?php _e('Full Name:', 'mpr'); ?></label>
            <input type="text" id="mpr_full_name" name="mpr_full_name" value="<?php echo esc_attr($get_meta('mpr_full_name')); ?>">
        </p>
        <p>
            <label for="mpr_case_status"><?php _e('Case Status:', 'mpr'); ?></label>
            <select id="mpr_case_status" name="mpr_case_status">
                <option value="Missing" <?php selected($get_meta('mpr_case_status'), 'Missing'); ?>><?php _e('Missing', 'mpr'); ?></option>
                <option value="Found - Safe" <?php selected($get_meta('mpr_case_status'), 'Found - Safe'); ?>><?php _e('Found - Safe', 'mpr'); ?></option>
                <option value="Found - Deceased" <?php selected($get_meta('mpr_case_status'), 'Found - Deceased'); ?>><?php _e('Found - Deceased', 'mpr'); ?></option>
                <option value="Cold Case" <?php selected($get_meta('mpr_case_status'), 'Cold Case'); ?>><?php _e('Cold Case', 'mpr'); ?></option>
            </select>
        </p>
        <p>
            <label for="mpr_risk_level"><?php _e('Risk Level:', 'mpr'); ?></label>
            <select id="mpr_risk_level" name="mpr_risk_level">
                <option value="Low" <?php selected($get_meta('mpr_risk_level'), 'Low'); ?>><?php _e('Low', 'mpr'); ?></option>
                <option value="Medium" <?php selected($get_meta('mpr_risk_level'), 'Medium'); ?>><?php _e('Medium', 'mpr'); ?></option>
                <option value="High" <?php selected($get_meta('mpr_risk_level'), 'High'); ?>><?php _e('High', 'mpr'); ?></option>
            </select>
        </p>
        <p>
            <label for="mpr_ethnicity"><?php _e('Ethnicity:', 'mpr'); ?></label>
            <input type="text" id="mpr_ethnicity" name="mpr_ethnicity" value="<?php echo esc_attr($get_meta('mpr_ethnicity')); ?>">
        </p>
        <p>
            <label for="mpr_nickname"><?php _e('Nickname:', 'mpr'); ?></label>
            <input type="text" id="mpr_nickname" name="mpr_nickname" value="<?php echo esc_attr($get_meta('mpr_nickname')); ?>">
        </p>
        <p>
            <label for="mpr_age"><?php _e('Age:', 'mpr'); ?></label>
            <input type="number" id="mpr_age" name="mpr_age" value="<?php echo esc_attr($get_meta('mpr_age')); ?>">
        </p>
        <p>
            <label for="mpr_dob"><?php _e('Date of Birth:', 'mpr'); ?></label>
            <input type="date" id="mpr_dob" name="mpr_dob" value="<?php echo esc_attr($get_meta('mpr_dob')); ?>">
        </p>
        <p>
            <label for="mpr_gender"><?php _e('Gender:', 'mpr'); ?></label>
            <select id="mpr_gender" name="mpr_gender">
                <option value=""><?php _e('Select Gender', 'mpr'); ?></option>
                <option value="Male" <?php selected($get_meta('mpr_gender'), 'Male'); ?>><?php _e('Male', 'mpr'); ?></option>
                <option value="Female" <?php selected($get_meta('mpr_gender'), 'Female'); ?>><?php _e('Female', 'mpr'); ?></option>
                <option value="Other" <?php selected($get_meta('mpr_gender'), 'Other'); ?>><?php _e('Other', 'mpr'); ?></option>
            </select>
        </p>
        <p>
            <label for="mpr_height"><?php _e('Height:', 'mpr'); ?></label>
            <input type="text" id="mpr_height" name="mpr_height" value="<?php echo esc_attr($get_meta('mpr_height')); ?>" placeholder="<?php esc_attr_e('e.g., 5\' 10"', 'mpr'); ?>">
        </p>
        <p>
            <label for="mpr_body_type"><?php _e('Body Type:', 'mpr'); ?></label>
            <input type="text" id="mpr_body_type" name="mpr_body_type" value="<?php echo esc_attr($get_meta('mpr_body_type')); ?>" placeholder="<?php esc_attr_e('e.g., Slim, Athletic', 'mpr'); ?>">
        </p>
        <p>
            <label for="mpr_weight"><?php _e('Weight:', 'mpr'); ?></label>
            <input type="text" id="mpr_weight" name="mpr_weight" value="<?php echo esc_attr($get_meta('mpr_weight')); ?>" placeholder="<?php esc_attr_e('e.g., 150 lbs or 68 kg', 'mpr'); ?>">
        </p>
        <p>
            <label for="mpr_hair_color"><?php _e('Hair Color:', 'mpr'); ?></label>
            <input type="text" id="mpr_hair_color" name="mpr_hair_color" value="<?php echo esc_attr($get_meta('mpr_hair_color')); ?>">
        </p>
        <p>
            <label for="mpr_hair_style"><?php _e('Hair Style:', 'mpr'); ?></label>
            <input type="text" id="mpr_hair_style" name="mpr_hair_style" value="<?php echo esc_attr($get_meta('mpr_hair_style')); ?>">
        </p>
        <p>
            <label for="mpr_eye_color"><?php _e('Eye Color:', 'mpr'); ?></label>
            <input type="text" id="mpr_eye_color" name="mpr_eye_color" value="<?php echo esc_attr($get_meta('mpr_eye_color')); ?>">
        </p>
        <div class="full-width">
            <label for="mpr_distinguishing_features"><?php _e('Distinguishing Features:', 'mpr'); ?></label>
            <?php wp_editor($get_meta('mpr_distinguishing_features'), 'mpr_distinguishing_features', ['textarea_rows' => 5, 'media_buttons' => false]); ?>
        </div>
        <div class="full-width">
            <label for="mpr_medical_conditions"><?php _e('Medical Conditions / Vulnerability:', 'mpr'); ?></label>
            <textarea id="mpr_medical_conditions" name="mpr_medical_conditions" rows="3" style="width:100%;"><?php echo esc_textarea($get_meta('mpr_medical_conditions')); ?></textarea>
        </div>
        <p>
            <label for="mpr_piercings"><?php _e('Piercings:', 'mpr'); ?></label>
            <input type="text" id="mpr_piercings" name="mpr_piercings" value="<?php echo esc_attr($get_meta('mpr_piercings')); ?>">
        </p>
        <p>
            <label for="mpr_tattoos"><?php _e('Tattoos:', 'mpr'); ?></label>
            <input type="text" id="mpr_tattoos" name="mpr_tattoos" value="<?php echo esc_attr($get_meta('mpr_tattoos')); ?>">
        </p>
        <hr class="full-width">
        <p>
            <label for="mpr_date_last_seen"><?php _e('Date Last Seen:', 'mpr'); ?></label>
            <input type="date" id="mpr_date_last_seen" name="mpr_date_last_seen" value="<?php echo esc_attr($get_meta('mpr_date_last_seen')); ?>">
        </p>
        <p>
            <label for="mpr_last_seen_location"><?php _e('Last Seen Location:', 'mpr'); ?></label>
            <input type="text" id="mpr_last_seen_location" name="mpr_last_seen_location" value="<?php echo esc_attr($get_meta('mpr_last_seen_location')); ?>">
            <button type="button" id="mpr_geocode_button" class="button" style="margin-top:5px;"><?php _e('Find on Map', 'mpr'); ?></button>
        </p>
        <div class="full-width">
            <label><?php _e('Physical Location (Pin on Map):', 'mpr'); ?></label>
            <div id="mpr-admin-map" style="height: 300px; width: 100%; border: 1px solid #ccc; margin-bottom: 10px;"></div>
            <div style="display: flex; gap: 10px;">
                <p style="flex: 1;">
                    <label for="mpr_latitude"><?php _e('Latitude:', 'mpr'); ?></label>
                    <input type="text" id="mpr_latitude" name="mpr_latitude" value="<?php echo esc_attr($get_meta('mpr_latitude')); ?>" readonly>
                </p>
                <p style="flex: 1;">
                    <label for="mpr_longitude"><?php _e('Longitude:', 'mpr'); ?></label>
                    <input type="text" id="mpr_longitude" name="mpr_longitude" value="<?php echo esc_attr($get_meta('mpr_longitude')); ?>" readonly>
                </p>
            </div>
            <p class="description"><?php _e('Click the map to set the exact coordinates, or use the "Find on Map" button above to search based on the text location.', 'mpr'); ?></p>
        </div>
        <p class="full-width">
            <label for="mpr_what_they_were_wearing"><?php _e('What they were wearing:', 'mpr'); ?></label>
            <input type="text" id="mpr_what_they_were_wearing" name="mpr_what_they_were_wearing" value="<?php echo esc_attr($get_meta('mpr_what_they_were_wearing')); ?>">
        </p>
        <hr class="full-width">
        <p>
            <label for="mpr_police_station"><?php _e('Police Station Reported to:', 'mpr'); ?></label>
            <input type="text" id="mpr_police_station" name="mpr_police_station" value="<?php echo esc_attr($get_meta('mpr_police_station')); ?>">
        </p>
        <p>
            <label for="mpr_ob_number"><?php _e('Occurrence Book (OB) Number:', 'mpr'); ?></label>
            <input type="text" id="mpr_ob_number" name="mpr_ob_number" value="<?php echo esc_attr($get_meta('mpr_ob_number')); ?>">
        </p>
        <p>
            <label for="mpr_police_phone"><?php _e('Police Station Telephone:', 'mpr'); ?></label>
            <input type="text" id="mpr_police_phone" name="mpr_police_phone" value="<?php echo esc_attr($get_meta('mpr_police_phone')); ?>">
        </p>
        <p>
            <label for="mpr_police_email"><?php _e('Police Email:', 'mpr'); ?></label>
            <input type="email" id="mpr_police_email" name="mpr_police_email" value="<?php echo esc_attr($get_meta('mpr_police_email')); ?>">
        </p>
        <p>
            <label for="mpr_investigating_officer"><?php _e('Investigating Officer:', 'mpr'); ?></label>
            <input type="text" id="mpr_investigating_officer" name="mpr_investigating_officer" value="<?php echo esc_attr($get_meta('mpr_investigating_officer')); ?>">
        </p>
         <p>
            <label for="mpr_contact_person"><?php _e('Family/Public Contact Person:', 'mpr'); ?></label>
            <input type="text" id="mpr_contact_person" name="mpr_contact_person" value="<?php echo esc_attr($get_meta('mpr_contact_person')); ?>">
        </p>
         <p>
            <label for="mpr_contact_person_email"><?php _e('Family/Public Contact Email:', 'mpr'); ?></label>
            <input type="email" id="mpr_contact_person_email" name="mpr_contact_person_email" value="<?php echo esc_attr($get_meta('mpr_contact_person_email')); ?>">
        </p>
        <div class="full-width">
            <h4><?php _e('Other Images', 'mpr'); ?></h4>
            <div class="other-images-wrapper"></div>
            <input type="hidden" id="mpr_other_images" name="mpr_other_images" value="<?php echo esc_attr($get_meta('mpr_other_images')); ?>">
            <button type="button" class="button" id="mpr_upload_other_images_button"><?php _e('Upload / Manage Other Images', 'mpr'); ?></button>
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

    // Use centralized meta keys
    $meta_keys = mpr_get_meta_keys();

    foreach ($meta_keys as $key => $args) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];

            // Use specialized sanitization if defined, otherwise default to sanitize_text_field
            if (isset($args['sanitize']) && function_exists($args['sanitize'])) {
                $value = call_user_func($args['sanitize'], $value);
            }
            else {
                $value = sanitize_text_field($value);
            }

            update_post_meta($post_id, $key, $value);
        }
    }
}
add_action('save_post_missing_person', 'mpr_save_meta_box_data');