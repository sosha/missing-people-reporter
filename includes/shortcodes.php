<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// [missing_people_summary] Shortcode
function mpr_summary_shortcode($atts)
{
    $atts = shortcode_atts([
        'layout' => 'grid',
        'posts_per_page' => 12,
        'status' => '',
        'risk' => '',
    ], $atts, 'missing_people_summary');

    // Capture filter parameters, prioritizing URL params over shortcode atts if present
    $search = isset($_GET['mpr_search']) ? sanitize_text_field($_GET['mpr_search']) : '';
    $status = isset($_GET['mpr_status']) ? sanitize_text_field($_GET['mpr_status']) : ($atts['status'] ?: '');
    $risk = isset($_GET['mpr_risk']) ? sanitize_text_field($_GET['mpr_risk']) : ($atts['risk'] ?: '');
    $loc = isset($_GET['mpr_loc']) ? sanitize_text_field($_GET['mpr_loc']) : '';

    $args = [
        'post_type' => 'missing_person',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['posts_per_page']),
        's' => $search,
        'meta_query' => ['relation' => 'AND'],
    ];

    if ($status) {
        $args['meta_query'][] = [
            'key' => 'mpr_case_status',
            'value' => $status,
            'compare' => '=',
        ];
    }

    if ($risk) {
        $args['meta_query'][] = [
            'key' => 'mpr_risk_level',
            'value' => $risk,
            'compare' => '=',
        ];
    }

    if ($loc) {
        $args['meta_query'][] = [
            'key' => 'mpr_last_seen_location',
            'value' => $loc,
            'compare' => 'LIKE',
        ];
    }

    $query = new WP_Query($args);

    $output = '';

    // --- Filter Bar UI ---
    $output .= '<div class="mpr-filter-bar">';
    $output .= '<form action="' . esc_url(get_permalink()) . '" method="GET" class="mpr-filter-form">';

    // Search
    $output .= '<div class="mpr-filter-field mpr-filter-search">';
    $output .= '<input type="text" name="mpr_search" value="' . esc_attr($search) . '" placeholder="' . esc_attr__('Search by name...', 'mpr') . '">';
    $output .= '</div>';

    // Status
    $output .= '<div class="mpr-filter-field">';
    $output .= '<select name="mpr_status">';
    $output .= '<option value="">' . __('All Statuses', 'mpr') . '</option>';
    $output .= '<option value="Missing" ' . selected($status, 'Missing', false) . '>' . __('Missing', 'mpr') . '</option>';
    $output .= '<option value="Found - Safe" ' . selected($status, 'Found - Safe', false) . '>' . __('Found - Safe', 'mpr') . '</option>';
    $output .= '<option value="Found - Deceased" ' . selected($status, 'Found - Deceased', false) . '>' . __('Found - Deceased', 'mpr') . '</option>';
    $output .= '<option value="Cold Case" ' . selected($status, 'Cold Case', false) . '>' . __('Cold Case', 'mpr') . '</option>';
    $output .= '</select>';
    $output .= '</div>';

    // Risk
    $output .= '<div class="mpr-filter-field">';
    $output .= '<select name="mpr_risk">';
    $output .= '<option value="">' . __('Any Risk', 'mpr') . '</option>';
    $output .= '<option value="Low" ' . selected($risk, 'Low', false) . '>' . __('Low Risk', 'mpr') . '</option>';
    $output .= '<option value="Medium" ' . selected($risk, 'Medium', false) . '>' . __('Medium Risk', 'mpr') . '</option>';
    $output .= '<option value="High" ' . selected($risk, 'High', false) . '>' . __('High Risk', 'mpr') . '</option>';
    $output .= '</select>';
    $output .= '</div>';

    // Location
    $output .= '<div class="mpr-filter-field">';
    $output .= '<input type="text" name="mpr_loc" value="' . esc_attr($loc) . '" placeholder="' . esc_attr__('Location...', 'mpr') . '">';
    $output .= '</div>';

    $output .= '<div class="mpr-filter-submit">';
    $output .= '<button type="submit" class="mpr-btn-filter">' . __('Filter', 'mpr') . '</button>';
    if ($search || $status || $risk || $loc) {
        $output .= '<a href="' . esc_url(get_permalink()) . '" class="mpr-btn-reset">' . __('Reset', 'mpr') . '</a>';
    }
    $output .= '</div>';

    $output .= '</form>';
    $output .= '</div>';

    if (!$query->have_posts()) {
        return $output . '<p>' . __('No missing people reports found matching your criteria.', 'mpr') . '</p>';
    }

    $output .= '<div class="mpr-summary-container mpr-' . esc_attr($atts['layout']) . '">';
    while ($query->have_posts()) {
        $query->the_post();
        $age = get_post_meta(get_the_ID(), 'mpr_age', true);
        $location = get_post_meta(get_the_ID(), 'mpr_last_seen_location', true);

        // Use centralized image helper
        $first_image_src = mpr_get_case_image_url(get_the_ID(), 'medium');
        $image_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) ?: get_the_title();

        $output .= '<div class="mpr-summary-item">';
        $output .= '<a href="' . get_permalink() . '">';

        // Display the found image or placeholder
        if ($first_image_src) {
            $output .= '<img src="' . esc_url($first_image_src) . '" alt="' . esc_attr($image_alt) . '">';
        }
        else {
            // Fallback to the original placeholder if no image found by any method
            $output .= '<img src="' . MPR_PLUGIN_URL . 'assets/images/placeholder.svg" alt="Placeholder Image">';
        }

        $output .= '</a>';
        $output .= '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        if ($age) {
            $output .= '<p><strong>' . __('Age:', 'mpr') . '</strong> ' . esc_html($age) . '</p>';
        }
        if ($location) {
            $output .= '<p><strong>' . __('Last Seen:', 'mpr') . '</strong> ' . esc_html($location) . '</p>';
        }

        $st = get_post_meta(get_the_ID(), 'mpr_case_status', true);
        if (!$st)
            $st = 'Missing';
        $st_class = 'mpr-status-' . sanitize_title($st);
        $output .= '<div class="mpr-status-badges-container">';
        $output .= '<div class="mpr-status-badge ' . esc_attr($st_class) . '">' . esc_html($st) . '</div>';

        $rl = get_post_meta(get_the_ID(), 'mpr_risk_level', true);
        if ($rl && 'Low' !== $rl) {
            $rl_class = 'mpr-risk-' . sanitize_title($rl);
            $output .= '<div class="mpr-risk-badge ' . esc_attr($rl_class) . '">' . sprintf(__('%s Risk', 'mpr'), esc_html($rl)) . '</div>';
        }
        $output .= '</div>';

        $output .= '</div>';
    }
    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('missing_people_summary', 'mpr_summary_shortcode');


// [mpr_public_report_form] Shortcode
function mpr_public_report_form_shortcode()
{
    ob_start();
?>
    <div id="mpr-public-form-container">
        <form id="mpr-public-form" class="mpr-multi-step-form" action="" method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field('mpr_public_submission', 'mpr_public_nonce'); ?>
            
            <div class="mpr-form-header">
                <h2><?php _e('Report a Missing Person', 'mpr'); ?></h2>
                <div class="mpr-steps-indicator">
                    <div class="mpr-step-node active" title="<?php esc_attr_e('Personal Details', 'mpr'); ?>">1</div>
                    <div class="mpr-step-node" title="<?php esc_attr_e('Appearance', 'mpr'); ?>">2</div>
                    <div class="mpr-step-node" title="<?php esc_attr_e('Case Info', 'mpr'); ?>">3</div>
                    <div class="mpr-step-node" title="<?php esc_attr_e('Authorities', 'mpr'); ?>">4</div>
                    <div class="mpr-step-node" title="<?php esc_attr_e('Reporter Contact', 'mpr'); ?>">5</div>
                </div>
            </div>

            <!-- Step 1: Personal Details -->
            <div class="mpr-form-step active" data-step="1">
                <h3><?php _e('1. Personal Details', 'mpr'); ?></h3>
                <div class="mpr-field-group">
                    <label for="mpr_full_name"><?php _e('Full Name *', 'mpr'); ?></label>
                    <input type="text" id="mpr_full_name" name="mpr_full_name" required placeholder="e.g. John Doe">
                </div>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_nickname"><?php _e('Nickname', 'mpr'); ?></label>
                        <input type="text" id="mpr_nickname" name="mpr_nickname" placeholder="e.g. Johnny">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_ethnicity"><?php _e('Ethnicity', 'mpr'); ?></label>
                        <input type="text" id="mpr_ethnicity" name="mpr_ethnicity" placeholder="e.g. Caucasian">
                    </div>
                </div>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_age"><?php _e('Age at Disappearance *', 'mpr'); ?></label>
                        <input type="number" id="mpr_age" name="mpr_age" required min="0" max="150" placeholder="e.g. 25">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_dob"><?php _e('Date of Birth', 'mpr'); ?></label>
                        <input type="date" id="mpr_dob" name="mpr_dob">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_gender"><?php _e('Gender *', 'mpr'); ?></label>
                        <select id="mpr_gender" name="mpr_gender" required>
                            <option value=""><?php _e('Select...', 'mpr'); ?></option>
                            <option value="Male"><?php _e('Male', 'mpr'); ?></option>
                            <option value="Female"><?php _e('Female', 'mpr'); ?></option>
                            <option value="Other"><?php _e('Other', 'mpr'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_main_image"><?php _e('Clear Photo of Missing Person *', 'mpr'); ?></label>
                    <input type="file" id="mpr_main_image" name="mpr_main_image" accept="image/png, image/jpeg" required>
                    <small><?php _e('This will be the main display image. High resolution preferred.', 'mpr'); ?></small>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_medical_conditions"><?php _e('Medical Conditions / Critical Vulnerabilities', 'mpr'); ?></label>
                    <textarea id="mpr_medical_conditions" name="mpr_medical_conditions" rows="3" placeholder="<?php esc_attr_e('e.g. Diabetic, Requires medication, Dementia, etc.', 'mpr'); ?>"></textarea>
                </div>
                <div class="mpr-form-nav">
                    <button type="button" class="mpr-btn-nav mpr-btn-next"><?php _e('Next: Appearance', 'mpr'); ?></button>
                </div>
            </div>

            <!-- Step 2: Appearance -->
            <div class="mpr-form-step" data-step="2">
                <h3><?php _e('2. Detailed Appearance', 'mpr'); ?></h3>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_height"><?php _e('Height', 'mpr'); ?></label>
                        <input type="text" id="mpr_height" name="mpr_height" placeholder="e.g. 5'8\" or 175cm">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_weight"><?php _e('Weight', 'mpr'); ?></label>
                        <input type="text" id="mpr_weight" name="mpr_weight" placeholder="e.g. 70kg">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_body_type"><?php _e('Body Type', 'mpr'); ?></label>
                        <input type="text" id="mpr_body_type" name="mpr_body_type" placeholder="e.g. Athletic, Slim, Large">
                    </div>
                </div>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_hair_color"><?php _e('Hair Color', 'mpr'); ?></label>
                        <input type="text" id="mpr_hair_color" name="mpr_hair_color">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_hair_style"><?php _e('Hair Style', 'mpr'); ?></label>
                        <input type="text" id="mpr_hair_style" name="mpr_hair_style">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_eye_color"><?php _e('Eye Color', 'mpr'); ?></label>
                        <input type="text" id="mpr_eye_color" name="mpr_eye_color">
                    </div>
                </div>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_piercings"><?php _e('Piercings', 'mpr'); ?></label>
                        <input type="text" id="mpr_piercings" name="mpr_piercings">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_tattoos"><?php _e('Tattoos', 'mpr'); ?></label>
                        <input type="text" id="mpr_tattoos" name="mpr_tattoos">
                    </div>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_distinguishing_features"><?php _e('Distinguishing Features', 'mpr'); ?></label>
                    <textarea id="mpr_distinguishing_features" name="mpr_distinguishing_features" rows="4" placeholder="<?php esc_attr_e('e.g. Scar on left cheek, Birthmark on right arm...', 'mpr'); ?>"></textarea>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_what_they_were_wearing"><?php _e('Last Known Clothing', 'mpr'); ?></label>
                    <textarea id="mpr_what_they_were_wearing" name="mpr_what_they_were_wearing" rows="3" placeholder="<?php esc_attr_e('e.g. Blue jeans, White polo shirt...', 'mpr'); ?>"></textarea>
                </div>
                <div class="mpr-form-nav">
                    <button type="button" class="mpr-btn-nav mpr-btn-prev"><?php _e('Previous', 'mpr'); ?></button>
                    <button type="button" class="mpr-btn-nav mpr-btn-next"><?php _e('Next: Case Info', 'mpr'); ?></button>
                </div>
            </div>

            <!-- Step 3: Case Info -->
            <div class="mpr-form-step" data-step="3">
                <h3><?php _e('3. Circumstances of Disappearance', 'mpr'); ?></h3>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_date_last_seen"><?php _e('Date Last Seen *', 'mpr'); ?></label>
                        <input type="date" id="mpr_date_last_seen" name="mpr_date_last_seen" required>
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_last_seen_location"><?php _e('Last Known Location *', 'mpr'); ?></label>
                        <input type="text" id="mpr_last_seen_location" name="mpr_last_seen_location" required placeholder="<?php esc_attr_e('e.g. Near CBD Mall, Street Name...', 'mpr'); ?>">
                    </div>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_description"><?php _e('Detailed Description of Events *', 'mpr'); ?></label>
                    <textarea id="mpr_description" name="mpr_description" rows="6" required placeholder="<?php esc_attr_e('Please provide as much detail as possible about the disappearance...', 'mpr'); ?>"></textarea>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_other_images"><?php _e('Additional Images', 'mpr'); ?></label>
                    <input type="file" id="mpr_other_images" name="mpr_other_images[]" multiple accept="image/png, image/jpeg">
                    <small><?php _e('Upload more photos that might help with identification.', 'mpr'); ?></small>
                </div>
                <div class="mpr-form-nav">
                    <button type="button" class="mpr-btn-nav mpr-btn-prev"><?php _e('Previous', 'mpr'); ?></button>
                    <button type="button" class="mpr-btn-nav mpr-btn-next"><?php _e('Next: Authorities', 'mpr'); ?></button>
                </div>
            </div>

            <!-- Step 4: Authorities -->
            <div class="mpr-form-step" data-step="4">
                <h3><?php _e('4. Police & Authorities', 'mpr'); ?></h3>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_police_station"><?php _e('Police Station Reported To', 'mpr'); ?></label>
                        <input type="text" id="mpr_police_station" name="mpr_police_station">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_ob_number"><?php _e('Police OB Number', 'mpr'); ?></label>
                        <input type="text" id="mpr_ob_number" name="mpr_ob_number" placeholder="e.g. OB 12/34/5678">
                    </div>
                </div>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_investigating_officer"><?php _e('Investigating Officer Name', 'mpr'); ?></label>
                        <input type="text" id="mpr_investigating_officer" name="mpr_investigating_officer">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_police_phone"><?php _e('Officer/Station Telephone', 'mpr'); ?></label>
                        <input type="text" id="mpr_police_phone" name="mpr_police_phone">
                    </div>
                </div>
                <div class="mpr-field-group">
                    <label for="mpr_police_email"><?php _e('Official Police Email', 'mpr'); ?></label>
                    <input type="email" id="mpr_police_email" name="mpr_police_email">
                </div>
                <div class="mpr-form-nav">
                    <button type="button" class="mpr-btn-nav mpr-btn-prev"><?php _e('Previous', 'mpr'); ?></button>
                    <button type="button" class="mpr-btn-nav mpr-btn-next"><?php _e('Next: Your Contact', 'mpr'); ?></button>
                </div>
            </div>

            <!-- Step 5: Reporter Contact -->
            <div class="mpr-form-step" data-step="5">
                <h3><?php _e('5. Your Contact Information', 'mpr'); ?></h3>
                <p><?php _e('This information is used for internal verification and will NOT be public unless you provide consent later.', 'mpr'); ?></p>
                <div class="mpr-grid">
                    <div class="mpr-field-group">
                        <label for="mpr_contact_person"><?php _e('Reporter Name *', 'mpr'); ?></label>
                        <input type="text" id="mpr_contact_person" name="mpr_contact_person" required placeholder="<?php esc_attr_e('Your full name', 'mpr'); ?>">
                    </div>
                    <div class="mpr-field-group">
                        <label for="mpr_contact_person_email"><?php _e('Reporter Email *', 'mpr'); ?></label>
                        <input type="email" id="mpr_contact_person_email" name="mpr_contact_person_email" required placeholder="<?php esc_attr_e('yourname@example.com', 'mpr'); ?>">
                    </div>
                </div>
                
                <div class="mpr-notice-final">
                    <p><?php _e('By submitting, you agree that all information provided is accurate to the best of your knowledge.', 'mpr'); ?></p>
                </div>

                <div class="mpr-form-nav">
                    <button type="button" class="mpr-btn-nav mpr-btn-prev"><?php _e('Previous', 'mpr'); ?></button>
                    <button type="submit" name="mpr_submit_public_report" class="mpr-btn-nav mpr-btn-submit"><?php _e('Submit Report for Review', 'mpr'); ?></button>
                </div>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mpr_public_report_form', 'mpr_public_report_form_shortcode');
