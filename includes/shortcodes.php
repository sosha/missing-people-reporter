<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// [missing_people_summary] Shortcode
function mpr_summary_shortcode($atts)
{
    $atts = shortcode_atts(['layout' => 'grid'], $atts, 'missing_people_summary');

    // Capture filter parameters
    $search = isset($_GET['mpr_search']) ? sanitize_text_field($_GET['mpr_search']) : '';
    $status = isset($_GET['mpr_status']) ? sanitize_text_field($_GET['mpr_status']) : '';
    $risk = isset($_GET['mpr_risk']) ? sanitize_text_field($_GET['mpr_risk']) : '';
    $loc = isset($_GET['mpr_loc']) ? sanitize_text_field($_GET['mpr_loc']) : '';

    $args = [
        'post_type' => 'missing_person',
        'post_status' => 'publish',
        'posts_per_page' => 12,
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
    $output .= '<input type="text" name="mpr_search" value="' . esc_attr($search) . '" placeholder="Search by name...">';
    $output .= '</div>';

    // Status
    $output .= '<div class="mpr-filter-field">';
    $output .= '<select name="mpr_status">';
    $output .= '<option value="">All Statuses</option>';
    $output .= '<option value="Missing" ' . selected($status, 'Missing', false) . '>Missing</option>';
    $output .= '<option value="Found - Safe" ' . selected($status, 'Found - Safe', false) . '>Found - Safe</option>';
    $output .= '<option value="Found - Deceased" ' . selected($status, 'Found - Deceased', false) . '>Found - Deceased</option>';
    $output .= '<option value="Cold Case" ' . selected($status, 'Cold Case', false) . '>Cold Case</option>';
    $output .= '</select>';
    $output .= '</div>';

    // Risk
    $output .= '<div class="mpr-filter-field">';
    $output .= '<select name="mpr_risk">';
    $output .= '<option value="">Any Risk</option>';
    $output .= '<option value="Low" ' . selected($risk, 'Low', false) . '>Low Risk</option>';
    $output .= '<option value="Medium" ' . selected($risk, 'Medium', false) . '>Medium Risk</option>';
    $output .= '<option value="High" ' . selected($risk, 'High', false) . '>High Risk</option>';
    $output .= '</select>';
    $output .= '</div>';

    // Location
    $output .= '<div class="mpr-filter-field">';
    $output .= '<input type="text" name="mpr_loc" value="' . esc_attr($loc) . '" placeholder="Location...">';
    $output .= '</div>';

    $output .= '<div class="mpr-filter-submit">';
    $output .= '<button type="submit" class="mpr-btn-filter">Filter</button>';
    if ($search || $status || $risk || $loc) {
        $output .= '<a href="' . esc_url(get_permalink()) . '" class="mpr-btn-reset">Reset</a>';
    }
    $output .= '</div>';

    $output .= '</form>';
    $output .= '</div>';

    if (!$query->have_posts()) {
        return $output . '<p>No missing people reports found matching your criteria.</p>';
    }

    $output .= '<div class="mpr-summary-container mpr-' . esc_attr($atts['layout']) . '">';
    while ($query->have_posts()) {
        $query->the_post();
        $age = get_post_meta(get_the_ID(), 'mpr_age', true);
        $location = get_post_meta(get_the_ID(), 'mpr_last_seen_location', true);

        // --- Start Image Logic for Summary Page ---
        $first_image_src = '';
        $image_alt = get_the_title(); // Default alt text

        if (has_post_thumbnail()) {
            // If a featured image exists, use it
            $image_id = get_post_thumbnail_id();
            $image_array = wp_get_attachment_image_src($image_id, 'medium'); // Use 'medium' size as in your original summary code
            if ($image_array) {
                $first_image_src = $image_array[0];
                $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                if (!empty($alt_text)) {
                    $image_alt = $alt_text;
                }
            }
        }

        // If no featured image or failed to get its URL, try to get attached images
        if (empty($first_image_src)) {
            $args_attach = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_parent' => get_the_ID(),
                'numberposts' => 1, // We only need the first one
                'order' => 'ASC',
                'orderby' => 'menu_order ID',
            );
            $attachments = get_children($args_attach);

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $image_array = wp_get_attachment_image_src($attachment->ID, 'medium'); // Use 'medium' size
                    if ($image_array) {
                        $first_image_src = $image_array[0];
                        $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                        if (!empty($alt_text)) {
                            $image_alt = $alt_text;
                        }
                        elseif (!empty($attachment->post_title)) { // Fallback to attachment title
                            $image_alt = $attachment->post_title;
                        }
                        break; // Found the first attached image, exit loop
                    }
                }
            }
        }

        // If still no image (e.g., hotlinked image in content, not attached), try regex from content
        if (empty($first_image_src)) {
            $content = get_the_content();
            preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
            if (!empty($matches[1])) {
                $first_image_src = $matches[1];
                // Try to get alt text from the matched img tag if possible, otherwise use post title
                preg_match('/<img.+alt=[\'"]([^\'"]+)[\'"].*>/i', $content, $alt_matches);
                $image_alt = !empty($alt_matches[1]) ? $alt_matches[1] : get_the_title();
            }
        }
        // --- End Image Logic for Summary Page ---

        $output .= '<div class="mpr-summary-item">';
        $output .= '<a href="' . get_permalink() . '">';

        // Display the found image or placeholder
        if ($first_image_src) {
            $output .= '<img src="' . esc_url($first_image_src) . '" alt="' . esc_attr($image_alt) . '">';
        }
        else {
            // Fallback to the original placeholder if no image found by any method
            $output .= '<img src="' . MPR_PLUGIN_URL . 'assets/images/placeholder.png" alt="Placeholder Image">';
        }

        $output .= '</a>';
        $output .= '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
        if ($age) {
            $output .= '<p><strong>Age:</strong> ' . esc_html($age) . '</p>';
        }
        if ($location) {
            $output .= '<p><strong>Last Seen:</strong> ' . esc_html($location) . '</p>';
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
            $output .= '<div class="mpr-risk-badge ' . esc_attr($rl_class) . '">' . esc_html($rl) . ' Risk</div>';
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
    <form id="mpr-public-form" action="" method="POST" enctype="multipart/form-data">
        <?php wp_nonce_field('mpr_public_submission', 'mpr_public_nonce'); ?>
        
        <h2>Report a Missing Person</h2>
        <p>Your report will be submitted for review by an administrator before being published.</p>

        <p><label for="mpr_full_name">Full Name*</label><input type="text" name="mpr_full_name" required></p>
        <p><label for="mpr_nickname">Nickname</label><input type="text" name="mpr_nickname"></p>
        <p><label for="mpr_ethnicity">Ethnicity</label><input type="text" name="mpr_ethnicity"></p>
        
        <p><label for="mpr_main_image">Main Image of Missing Person (Featured)*</label><input type="file" id="mpr_main_image" name="mpr_main_image" accept="image/png, image/jpeg" required></p>
        <p><label for="mpr_other_images">Other Images (select multiple)</label><input type="file" id="mpr_other_images" name="mpr_other_images[]" multiple accept="image/png, image/jpeg"></p>
        
        <p><label for="mpr_age">Age*</label><input type="number" name="mpr_age" required></p>
        <p><label for="mpr_dob">Date of Birth</label><input type="date" name="mpr_dob"></p>
        <p><label for="mpr_gender">Gender*</label><select name="mpr_gender" required><option value="">Select...</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></p>
        <p><label for="mpr_height">Height</label><input type="text" name="mpr_height"></p>
        <p><label for="mpr_body_type">Body Type</label><input type="text" name="mpr_body_type"></p>
        <p><label for="mpr_weight">Weight</label><input type="text" name="mpr_weight"></p>
        <p><label for="mpr_hair_color">Hair Color</label><input type="text" name="mpr_hair_color"></p>
        <p><label for="mpr_hair_style">Hair Style</label><input type="text" name="mpr_hair_style"></p>
        <p><label for="mpr_eye_color">Eye Color</label><input type="text" name="mpr_eye_color"></p>
        <p><label for="mpr_distinguishing_features">Distinguishing Features</label><textarea name="mpr_distinguishing_features"></textarea></p>
        <p><label for="mpr_medical_conditions">Medical Conditions / Vulnerability</label><textarea name="mpr_medical_conditions" rows="3"></textarea></p>
        <p><label for="mpr_piercings">Piercings</label><input type="text" name="mpr_piercings"></p>
        <p><label for="mpr_tattoos">Tattoos</label><input type="text" name="mpr_tattoos"></p>
        <p><label for="mpr_date_last_seen">Date Last Seen*</label><input type="date" name="mpr_date_last_seen" required></p>
        <p><label for="mpr_last_seen_location">Last Seen Location*</label><input type="text" name="mpr_last_seen_location" required></p>
        <p><label for="mpr_what_they_were_wearing">What they were wearing</label><textarea name="mpr_what_they_were_wearing"></textarea></p>
        <p><label for="mpr_police_station">Police Station Reported to</label><input type="text" name="mpr_police_station"></p>
        <p><label for="mpr_ob_number">Police OB Number</label><input type="text" name="mpr_ob_number"></p>
        <p><label for="mpr_police_phone">Police Station Telephone</label><input type="text" name="mpr_police_phone"></p>
        <p><label for="mpr_police_email">Police Email</label><input type="email" name="mpr_police_email"></p>
        <p><label for="mpr_investigating_officer">Investigating Officer</label><input type="text" name="mpr_investigating_officer"></p>
        <p><label for="mpr_contact_person">Family/Public Contact Person*</label><input type="text" name="mpr_contact_person" required></p>
        <p><label for="mpr_contact_person_email">Family/Public Contact Email*</label><input type="email" name="mpr_contact_person_email" required></p>
        <p><label for="mpr_description">Description / Other Details*</label><textarea name="mpr_description" rows="5" required></textarea></p>
        
        <input type="submit" name="mpr_submit_public_report" value="Submit for Review">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('mpr_public_report_form', 'mpr_public_report_form_shortcode');