<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Track user's recent views.
 */
function mpr_track_view($post_id)
{
    if (!is_user_logged_in() || !is_singular('missing_person')) {
        return;
    }

    $user_id = get_current_user_id();
    $viewed_posts = get_user_meta($user_id, 'mpr_recent_views', true);
    if (!is_array($viewed_posts)) {
        $viewed_posts = [];
    }

    // Add post to the beginning of the array
    array_unshift($viewed_posts, $post_id);
    // Remove duplicates
    $viewed_posts = array_unique($viewed_posts);
    // Keep only the last 10 views
    $viewed_posts = array_slice($viewed_posts, 0, 10);

    update_user_meta($user_id, 'mpr_recent_views', $viewed_posts);
}

/**
 * [mpr_user_profile] Shortcode
 */
function mpr_user_profile_shortcode()
{
    if (!is_user_logged_in()) {
        return sprintf(
            __('Please <a href="%s">log in</a> to view your profile.', 'mpr'),
            wp_login_url(get_permalink())
        );
    }

    $user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    ob_start();
?>
    <div class="mpr-user-profile">
        <h2><?php printf(__("%s's Profile", 'mpr'), esc_html($current_user->display_name)); ?></h2>

        <div class="profile-section">
            <h3><?php _e('Cases You Are Following', 'mpr'); ?></h3>
            <?php
    $followed_cases = get_user_meta($user_id, 'mpr_followed_cases', true);
    if (!empty($followed_cases)) {
        $args = ['post_type' => 'missing_person', 'post__in' => $followed_cases, 'posts_per_page' => -1];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
            wp_reset_postdata();
        }
    }
    else {
        echo '<p>' . __('You are not following any cases yet.', 'mpr') . '</p>';
    }
?>
        </div>

        <div class="profile-section">
            <h3><?php _e('Recently Viewed Cases', 'mpr'); ?></h3>
             <?php
    $recent_views = get_user_meta($user_id, 'mpr_recent_views', true);
    if (!empty($recent_views)) {
        $args = ['post_type' => 'missing_person', 'post__in' => $recent_views, 'orderby' => 'post__in'];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
            wp_reset_postdata();
        }
    }
    else {
        echo '<p>' . __('You have not viewed any cases recently.', 'mpr') . '</p>';
    }
?>
        </div>

        <div class="profile-section">
            <h3><?php _e('Your Comment History', 'mpr'); ?></h3>
            <?php
    $comments = get_comments(['user_id' => $user_id, 'number' => 10, 'status' => 'approve']);
    if (!empty($comments)) {
        echo '<ul>';
        foreach ($comments as $comment) {
            echo sprintf(
                __('<li>On <a href="%s"> %s</a>: <em>"%s"</em></li>', 'mpr'),
                get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID,
                get_the_title($comment->comment_post_ID),
                wp_trim_words($comment->comment_content, 15, '...')
            );
        }
        echo '</ul>';
    }
    else {
        echo '<p>' . __('You have not made any comments.', 'mpr') . '</p>';
    }
?>
        </div>
        
        <div class="profile-section">
            <h3><?php _e('Account', 'mpr'); ?></h3>
            <ul>
                <li><a href="<?php echo get_edit_user_link(); ?>"><?php _e('Manage Your Profile', 'mpr'); ?></a></li>
                <li><a href="/donate"><?php _e('Donate to Support Us', 'mpr'); ?></a></li> 
                <li><a href="mailto:<?php echo get_option('admin_email'); ?>"><?php _e('Contact an Admin', 'mpr'); ?></a></li>
            </ul>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('mpr_user_profile', 'mpr_user_profile_shortcode');