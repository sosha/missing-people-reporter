<?php
/**
 * The template for displaying all single Missing Person posts.
 *
 * Adapted from Hestia's Fullwidth Template to ensure consistent look and feel.
 *
 * @package MissingPersonsReports
 */

// Function to track views. Call this at the top of the single template.
if (function_exists('mpr_track_view')) {
    mpr_track_view(get_the_ID());
}

get_header(); // This pulls in Hestia's main site header (logo, menu, etc.).

// This action hook is crucial for displaying Hestia's page header (title, breadcrumbs)
// and setting up the main wrapper for the content.
do_action('hestia_before_single_page_wrapper'); //

?>
<div class="<?php echo hestia_layout(); ?>">
    <div class="blog-post"> <?php // This is a key wrapper in Hestia's page templates ?>
        <div class="container"> <?php // Hestia's main content container ?>

            <?php while (have_posts()):
    the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('hestia-blog-post'); ?>>

                    <?php
    // --- Start of Original Missing Person Content (within Hestia's primary content area) ---

    // Image fetching logic
    $first_image_src = '';
    $image_alt = get_the_title();

    if (has_post_thumbnail()) {
        $image_id = get_post_thumbnail_id();
        $image_array = wp_get_attachment_image_src($image_id, 'large');
        if ($image_array) {
            $first_image_src = $image_array[0];
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            if (empty($image_alt)) {
                $image_alt = get_the_title();
            }
        }
    }

    if (empty($first_image_src)) {
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_parent' => get_the_ID(),
            'numberposts' => 1,
            'order' => 'ASC',
            'orderby' => 'menu_order ID',
        );
        $attachments = get_children($args);

        if ($attachments) {
            foreach ($attachments as $attachment) {
                $image_array = wp_get_attachment_image_src($attachment->ID, 'large');
                if ($image_array) {
                    $first_image_src = $image_array[0];
                    $image_alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
                    if (empty($image_alt)) {
                        $image_alt = $attachment->post_title;
                    }
                    if (empty($image_alt)) {
                        $image_alt = get_the_title();
                    }
                    break;
                }
            }
        }
    }

    if (empty($first_image_src)) {
        $content_post = get_the_content();
        preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content_post, $matches);
        if (!empty($matches[1])) {
            $first_image_src = $matches[1];
            preg_match('/<img.+alt=[\'"]([^\'"]+)[\'"].*>/i', $content_post, $alt_matches);
            $image_alt = !empty($alt_matches[1]) ? $alt_matches[1] : get_the_title();
        }
    }
?>

                    <div class="mpr-single-grid">
                        <div class="mpr-single-left">
                            <div class="mpr-main-image">
                                <?php if ($first_image_src): ?>
                                    <img src="<?php echo esc_url($first_image_src); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                                <?php
    else: ?>
                                    <img src="<?php echo MPR_PLUGIN_URL . 'assets/images/placeholder.png'; ?>" alt="Placeholder Image">
                                <?php
    endif; ?>
                            </div>

                            <?php if (function_exists('mpr_display_follow_button')) {
        mpr_display_follow_button(get_the_ID());
    }?>

                            <div class="mpr-social-share">
                                <strong>Share this case:</strong>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank">Facebook</a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode('Missing: ' . get_the_title()); ?>" target="_blank">X</a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode('Missing: ' . get_the_title()); ?>" target="_blank">LinkedIn</a>
                            </div>

                            <div class="mpr-print-action" style="margin-top: 20px;">
                                <button onclick="window.print();" class="mpr-btn-print" style="width: 100%; background: #2c3e50; color: #fff; border: none; padding: 12px; border-radius: 5px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <span class="dashicons dashicons-printer"></span> Print Missing Poster
                                </button>
                            </div>
                        </div>

                        <div class="mpr-single-right">
                            <?php // The main title will be generated by hestia_before_single_page_wrapper.
    // You might want to remove this inner title if it's redundant.
?>
                            <header class="entry-header">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                <div class="mpr-header-badges">
                                    <?php
    $status = get_post_meta(get_the_ID(), 'mpr_case_status', true);
    if (!$status)
        $status = 'Missing';
    $status_class = 'mpr-status-' . sanitize_title($status);
    echo '<div class="mpr-status-badge ' . esc_attr($status_class) . '">' . esc_html($status) . '</div>';

    $risk_level = get_post_meta(get_the_ID(), 'mpr_risk_level', true);
    if ($risk_level && 'Low' !== $risk_level) {
        $risk_class = 'mpr-risk-' . sanitize_title($risk_level);
        echo '<div class="mpr-risk-badge ' . esc_attr($risk_class) . '">' . esc_html($risk_level) . ' Risk</div>';
    }
?>
                                </div>
                            </header>

                            <h3>Personal Information</h3>
                            <ul class="mpr-details-list">
                                <?php $meta = get_post_meta(get_the_ID()); ?>
                                <li><strong>Nickname:</strong> <?php echo esc_html($meta['mpr_nickname'][0] ?? 'N/A'); ?></li>
                                <li><strong>Age:</strong> <?php echo esc_html($meta['mpr_age'][0] ?? 'N/A'); ?></li>
                                <li><strong>Ethnicity:</strong> <?php echo esc_html($meta['mpr_ethnicity'][0] ?? 'N/A'); ?></li>
                                <li><strong>Date of Birth:</strong> <?php echo esc_html($meta['mpr_dob'][0] ?? 'N/A'); ?></li>
                                <li><strong>Gender:</strong> <?php echo esc_html($meta['mpr_gender'][0] ?? 'N/A'); ?></li>
                                <li><strong>Medical Conditions:</strong> <?php echo esc_html($meta['mpr_medical_conditions'][0] ?? 'N/A'); ?></li>
                            </ul>

                            <h3>Physical Description</h3>
                            <ul class="mpr-details-list">
                                 <li><strong>Height:</strong> <?php echo esc_html($meta['mpr_height'][0] ?? 'N/A'); ?></li>
                                 <li><strong>Weight:</strong> <?php echo esc_html($meta['mpr_weight'][0] ?? 'N/A'); ?></li>
                                <li><strong>Hair Color:</strong> <?php echo esc_html($meta['mpr_hair_color'][0] ?? 'N/A'); ?></li>
                                <li><strong>Eye Color:</strong> <?php echo esc_html($meta['mpr_eye_color'][0] ?? 'N/A'); ?></li>
                                <li><strong>Distinguishing Features:</strong> <?php echo wp_kses_post($meta['mpr_distinguishing_features'][0] ?? 'N/A'); ?></li>
                            </ul>

                            <h3>Circumstances of Disappearance</h3>
                             <ul class="mpr-details-list">
                                  <li><strong>Date Last Seen:</strong> <?php echo esc_html($meta['mpr_date_last_seen'][0] ?? 'N/A'); ?></li>
                                 <li><strong>Last Seen Location:</strong> <?php echo esc_html($meta['mpr_last_seen_location'][0] ?? 'N/A'); ?></li>
                                 <li><strong>What they were wearing:</strong> <?php echo esc_html($meta['mpr_what_they_were_wearing'][0] ?? 'N/A'); ?></li>
                            </ul>

                            <h3>Police Report Information</h3>
                             <ul class="mpr-details-list">
                                  <li><strong>Police Station:</strong> <?php echo esc_html($meta['mpr_police_station'][0] ?? 'N/A'); ?></li>
                                  <li><strong>OB Number:</strong> <?php echo esc_html($meta['mpr_ob_number'][0] ?? 'N/A'); ?></li>
                                  <li><strong>Contact Number:</strong> <?php echo esc_html($meta['mpr_police_phone'][0] ?? 'N/A'); ?></li>
                                  <li><strong>Police Email:</strong> <?php echo esc_html($meta['mpr_police_email'][0] ?? 'N/A'); ?></li>
                                  <li><strong>Investigating Officer:</strong> <?php echo esc_html($meta['mpr_investigating_officer'][0] ?? 'N/A'); ?></li>
                            </ul>

                            <h3>Family/Public Contact</h3>
                             <ul class="mpr-details-list">
                                  <li><strong>Contact Person:</strong> <?php echo esc_html($meta['mpr_contact_person'][0] ?? 'N/A'); ?></li>
                                  <li><strong>Contact Email:</strong> <?php echo esc_html($meta['mpr_contact_person_email'][0] ?? 'N/A'); ?></li>
                            </ul>

                            <?php
    $lat = get_post_meta(get_the_ID(), 'mpr_latitude', true);
    $lng = get_post_meta(get_the_ID(), 'mpr_longitude', true);
    if ($lat && $lng):
?>
                             <div class="mpr-map-container" style="margin-bottom: 25px;">
                                 <h3>Last Seen Location Map</h3>
                                 <div id="mpr-single-map" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #eee; margin-top:10px;"></div>
                                 <p class="description" style="font-size: 0.85em; color: #666; margin-top: 5px;">Approximate location where the person was last seen.</p>
                             </div>
                             <?php
    endif; ?>

                             <h3>Main Description</h3>
                            <div class="mpr-description">
                                <?php the_content(); ?>
                            </div>

                            <?php
    $other_images_ids = get_post_meta(get_the_ID(), 'mpr_other_images', true);
    if ($other_images_ids) {
        echo '<h3>Other Images</h3>';
        $ids_array = explode(',', $other_images_ids);
        echo '<div class="mpr-other-images-gallery">';
        foreach ($ids_array as $id) {
            echo '<a href="' . wp_get_attachment_url($id) . '" target="_blank">' . wp_get_attachment_image($id, 'medium') . '</a>';
        }
        echo '</div>';
    }
?>
                        </div>
                    </div>
                    <?php // --- End of Original Missing Person Content --- ?>

                    <?php
    // Display comments if enabled
    if (comments_open() || get_comments_number()):
        comments_template();
    endif;
?>

                    <!-- MPR Poster Template (Hidden on web, shown in print) -->
                    <div id="mpr-poster-template" class="mpr-poster-wrapper">
                        <div class="mpr-poster-header">
                            <h1>MISSING</h1>
                        </div>

                        <div class="mpr-poster-image">
                            <?php if ($first_image_src): ?>
                                <img src="<?php echo esc_url($first_image_src); ?>" alt="Missing Person Photo">
                            <?php
    endif; ?>
                        </div>

                        <div class="mpr-poster-content">
                            <h2 class="mpr-poster-name"><?php the_title(); ?></h2>
                            
                            <div class="mpr-poster-risk">
                                <span class="mpr-poster-risk-badge"><?php echo esc_html($risk_level); ?> RISK</span>
                                <span class="mpr-poster-risk-badge"><?php echo esc_html($status); ?></span>
                            </div>

                            <div class="mpr-poster-grid">
                                <div class="mpr-poster-section">
                                    <h3>Personal Details</h3>
                                    <p><strong>Age:</strong> <?php echo esc_html($meta['mpr_age'][0] ?? 'N/A'); ?></p>
                                    <p><strong>Height:</strong> <?php echo esc_html($meta['mpr_height'][0] ?? 'N/A'); ?></p>
                                    <p><strong>Gender:</strong> <?php echo esc_html($meta['mpr_gender'][0] ?? 'N/A'); ?></p>
                                    <p><strong>Last Seen:</strong> <?php echo esc_html($meta['mpr_date_last_seen'][0] ?? 'N/A'); ?></p>
                                </div>
                                <div class="mpr-poster-section">
                                    <h3>Last Known Location</h3>
                                    <p><?php echo esc_html($meta['mpr_last_seen_location'][0] ?? 'N/A'); ?></p>
                                    <p><strong>Wearing:</strong> <?php echo esc_html($meta['mpr_what_they_were_wearing'][0] ?? 'N/A'); ?></p>
                                </div>
                            </div>

                            <div class="mpr-poster-description">
                                <h3>Case Summary</h3>
                                <p><?php echo wp_trim_words(get_the_content(), 50); ?></p>
                            </div>

                            <div class="mpr-poster-contact">
                                <h2>HAVE YOU SEEN THEM?</h2>
                                <p>Report to: <?php echo esc_html($meta['mpr_police_station'][0] ?? 'Local Police'); ?></p>
                                <p>Police Phone: <?php echo esc_html($meta['mpr_police_phone'][0] ?? '999'); ?></p>
                                <p>OB Number: <?php echo esc_html($meta['mpr_ob_number'][0] ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="mpr-poster-footer">
                            <p>Generated by Missing People Reporter - [www.missing.ke]</p>
                        </div>
                    </div>
                </article>
            <?php
endwhile; ?>

        </div> </div> </div> <?php get_footer(); ?>