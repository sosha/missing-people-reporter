<?php
/**
 * The template for displaying all single Missing Person posts.
 *
 * Adapted from a Fullwidth Template to ensure consistent look and feel.
 *
 * @package MissingPersonsReports
 */

// Function to track views. Call this at the top of the single template.
if (function_exists('mpr_track_view')) {
    mpr_track_view(get_the_ID());
}

get_header();

$layout_class = 'content-area';
$post_class = 'mpr-single-wrapper';
?>
<div class="<?php echo esc_attr($layout_class); ?>">
    <div class="<?php echo esc_attr($post_class); ?>">
        <div class="container">

            <?php while (have_posts()):
    the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('mpr-article'); ?>>

                    <?php
    // --- Start of Original Missing Person Content ---

    // Use centralized image helper
    $first_image_src = mpr_get_case_image_url(get_the_ID(), 'large');
    $image_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) ?: get_the_title();
?>

                    <div class="mpr-single-grid">
                        <div class="mpr-single-left">
                            <div class="mpr-main-image">
                                <?php if ($first_image_src): ?>
                                    <img src="<?php echo esc_url($first_image_src); ?>" alt="<?php echo esc_attr($image_alt); ?>">
                                <?php
    else: ?>
                                    <img src="<?php echo MPR_PLUGIN_URL . 'assets/images/placeholder.svg'; ?>" alt="Placeholder Image">
                                <?php
    endif; ?>
                            </div>

                            <?php if (function_exists('mpr_display_follow_button')) {
        mpr_display_follow_button(get_the_ID());
    }?>

                            <div class="mpr-social-share">
                                <strong><?php _e('Share this case:', 'mpr'); ?></strong>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank"><?php _e('Facebook', 'mpr'); ?></a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(sprintf(__('Missing: %s', 'mpr'), get_the_title())); ?>" target="_blank"><?php _e('X', 'mpr'); ?></a>
                                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink()); ?>&title=<?php echo urlencode(sprintf(__('Missing: %s', 'mpr'), get_the_title())); ?>" target="_blank"><?php _e('LinkedIn', 'mpr'); ?></a>
                            </div>

                            <div class="mpr-print-action" style="margin-top: 20px;">
                                <button onclick="window.print();" class="mpr-btn-print" style="width: 100%; background: #2c3e50; color: #fff; border: none; padding: 12px; border-radius: 5px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                    <span class="dashicons dashicons-printer"></span> <?php _e('Print Missing Poster', 'mpr'); ?>
                                </button>
                            </div>
                        </div>

                        <div class="mpr-single-right">
                            <?php // The main title will be generated.
    // You might want to remove this inner title if it's redundant.
?>
                            <header class="entry-header">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                <div class="mpr-header-badges">
                                    <?php
    $status = get_post_meta(get_the_ID(), 'mpr_case_status', true);
    if (!$status)
        $status = __('Missing', 'mpr');
    $status_class = 'mpr-status-' . sanitize_title($status);
    echo '<div class="mpr-status-badge ' . esc_attr($status_class) . '">' . esc_html($status) . '</div>';

    $risk_level = get_post_meta(get_the_ID(), 'mpr_risk_level', true);
    if ($risk_level && 'Low' !== $risk_level) {
        $risk_class = 'mpr-risk-' . sanitize_title($risk_level);
        echo '<div class="mpr-status-badge ' . esc_attr($risk_class) . '">' . sprintf(__('%s Risk', 'mpr'), esc_html($risk_level)) . '</div>';
    }
?>
                                </div>
                            </header>

                            <h3><?php _e('Personal Information', 'mpr'); ?></h3>
                            <ul class="mpr-details-list">
                                <?php $meta = get_post_meta(get_the_ID()); ?>
                                <li><strong><?php _e('Nickname:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_nickname'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Age:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_age'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Ethnicity:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_ethnicity'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Date of Birth:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_dob'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Gender:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_gender'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Medical Conditions:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_medical_conditions'][0] ?? __('N/A', 'mpr')); ?></li>
                            </ul>

                            <h3><?php _e('Physical Description', 'mpr'); ?></h3>
                            <ul class="mpr-details-list">
                                 <li><strong><?php _e('Height:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_height'][0] ?? __('N/A', 'mpr')); ?></li>
                                 <li><strong><?php _e('Weight:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_weight'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Hair Color:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_hair_color'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Eye Color:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_eye_color'][0] ?? __('N/A', 'mpr')); ?></li>
                                <li><strong><?php _e('Distinguishing Features:', 'mpr'); ?></strong> <?php echo wp_kses_post($meta['mpr_distinguishing_features'][0] ?? __('N/A', 'mpr')); ?></li>
                            </ul>

                            <h3><?php _e('Circumstances of Disappearance', 'mpr'); ?></h3>
                             <ul class="mpr-details-list">
                                  <li><strong><?php _e('Date Last Seen:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_date_last_seen'][0] ?? __('N/A', 'mpr')); ?></li>
                                 <li><strong><?php _e('Last Seen Location:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_last_seen_location'][0] ?? __('N/A', 'mpr')); ?></li>
                                 <li><strong><?php _e('What they were wearing:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_what_they_were_wearing'][0] ?? __('N/A', 'mpr')); ?></li>
                            </ul>

                            <h3><?php _e('Police Report Information', 'mpr'); ?></h3>
                             <ul class="mpr-details-list">
                                  <li><strong><?php _e('Police Station:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_police_station'][0] ?? __('N/A', 'mpr')); ?></li>
                                  <li><strong><?php _e('OB Number:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_ob_number'][0] ?? __('N/A', 'mpr')); ?></li>
                                  <li><strong><?php _e('Contact Number:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_police_phone'][0] ?? __('N/A', 'mpr')); ?></li>
                                  <li><strong><?php _e('Police Email:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_police_email'][0] ?? __('N/A', 'mpr')); ?></li>
                                  <li><strong><?php _e('Investigating Officer:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_investigating_officer'][0] ?? __('N/A', 'mpr')); ?></li>
                            </ul>

                            <h3><?php _e('Family/Public Contact', 'mpr'); ?></h3>
                             <ul class="mpr-details-list">
                                  <li><strong><?php _e('Contact Person:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_contact_person'][0] ?? __('N/A', 'mpr')); ?></li>
                                  <li><strong><?php _e('Contact Email:', 'mpr'); ?></strong> <?php echo esc_html($meta['mpr_contact_person_email'][0] ?? __('N/A', 'mpr')); ?></li>
                            </ul>

                            <?php
    $agency_name = get_option('mpr_agency_name');
    $agency_phone = get_option('mpr_agency_phone');
    $agency_email = get_option('mpr_agency_email');
    if ($agency_name):
?>
                            <h3><?php _e('Agency / Organization Contact', 'mpr'); ?></h3>
                             <ul class="mpr-details-list">
                                  <li><strong><?php _e('Agency:', 'mpr'); ?></strong> <?php echo esc_html($agency_name); ?></li>
                                  <?php if ($agency_phone): ?><li><strong><?php _e('Phone:', 'mpr'); ?></strong> <?php echo esc_html($agency_phone); ?></li><?php
        endif; ?>
                                  <?php if ($agency_email): ?><li><strong><?php _e('Email:', 'mpr'); ?></strong> <?php echo esc_html($agency_email); ?></li><?php
        endif; ?>
                            </ul>
                            <?php
    endif; ?>

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
                            <p>Generated by <?php echo esc_html(get_option('mpr_agency_name', 'Missing People Reporter')); ?> - [<?php echo esc_html(get_option('mpr_agency_website', 'www.missing.ke')); ?>]</p>
                        </div>
                    </div>
                </article>
            <?php
endwhile; ?>

        </div> </div> </div> <?php get_footer(); ?>
