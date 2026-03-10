<?php
/**
 * Admin Dashboard for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add the dashboard page to the admin menu.
 */
function mpr_add_admin_dashboard_page()
{
    add_menu_page(
        __('MPR Dashboard', 'mpr'),
        __('MPR Dashboard', 'mpr'),
        'manage_options',
        'mpr-dashboard',
        'mpr_render_admin_dashboard',
        'dashicons-dashboard',
        6
    );
}
add_action('admin_menu', 'mpr_add_admin_dashboard_page');

/**
 * Render the Admin Dashboard.
 */
function mpr_render_admin_dashboard()
{
    // Get Statistics using optimized helper
    $stats_data = mpr_get_stats_counts();
    $total_cases = $stats_data['total'];
    $status_counts = $stats_data['status'];
    $risk_counts = $stats_data['risk'];

    // Recent Reports
    $recent_reports = new WP_Query(array(
        'post_type' => 'missing_person',
        'posts_per_page' => 5,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ));

?>
    <style>
        .mpr-dashboard-wrap { margin: 20px 20px 0 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
        .mpr-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .mpr-stat-card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; border-left: 5px solid #2271b1; }
        .mpr-stat-card.total { border-left-color: #2271b1; }
        .mpr-stat-card.missing { border-left-color: #d63638; }
        .mpr-stat-card.found { border-left-color: #00a32a; }
        .mpr-stat-card.high-risk { border-left-color: #d63638; animation: pulse-border 2s infinite; }
        @keyframes pulse-border { 0% { border-left-color: #d63638; } 50% { border-left-color: #ffb900; } 100% { border-left-color: #d63638; } }
        .mpr-stat-card h2 { margin: 0; font-size: 2.5rem; color: #1d2327; line-height: 1; }
        .mpr-stat-card p { margin: 10px 0 0; color: #646970; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.8rem; }
        
        .mpr-dashboard-content { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .mpr-content-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .mpr-content-box h3 { margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #f0f0f1; display: flex; align-items: center; }
        .mpr-content-box h3 .dashicons { margin-right: 10px; color: #2271b1; }
        
        .mpr-recent-table { width: 100%; border-collapse: collapse; }
        .mpr-recent-table th, .mpr-recent-table td { text-align: left; padding: 12px 15px; border-bottom: 1px solid #f0f0f1; }
        .mpr-recent-table tr:last-child td { border-bottom: none; }
        .mpr-recent-table th { font-weight: 600; color: #1d2327; background: #f6f7f7; }
        
        .mpr-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-high { background: #fbe9e9; color: #d63638; }
        .badge-medium { background: #fcf0d8; color: #854d0e; }
        .badge-low { background: #e7f5ea; color: #00a32a; }
        
        .mpr-quick-actions ul { list-style: none; padding: 0; margin: 0; }
        .mpr-quick-actions li { margin-bottom: 15px; }
        .mpr-quick-actions a { display: block; padding: 12px 15px; background: #f6f7f7; border-radius: 6px; color: #2271b1; text-decoration: none; font-weight: 600; transition: all 0.2s; border: 1px solid #dcdcde; }
        .mpr-quick-actions a:hover { background: #2271b1; color: #fff; border-color: #2271b1; transform: translateY(-2px); }
        .mpr-quick-actions td img { border-radius: 4px; }
    </style>

    <div class="wrap mpr-dashboard-wrap">
        <h1><?php _e('Missing People Reporter Dashboard', 'mpr'); ?></h1>
        
        <div class="mpr-stats-grid">
            <div class="mpr-stat-card total">
                <h2><?php echo number_format_i18n($total_cases); ?></h2>
                <p><?php _e('Total Cases', 'mpr'); ?></p>
            </div>
            <div class="mpr-stat-card missing">
                <h2><?php echo number_format_i18n($status_counts['Missing'] ?? 0); ?></h2>
                <p><?php _e('Currently Missing', 'mpr'); ?></p>
            </div>
            <div class="mpr-stat-card found">
                <h2><?php echo number_format_i18n($status_counts['Found - Safe'] ?? 0); ?></h2>
                <p><?php _e('Found Safely', 'mpr'); ?></p>
            </div>
            <div class="mpr-stat-card high-risk">
                <h2><?php echo number_format_i18n($risk_counts['High'] ?? 0); ?></h2>
                <p><?php _e('High Risk Cases', 'mpr'); ?></p>
            </div>
        </div>

        <div class="mpr-dashboard-content">
            <div class="mpr-content-box">
                <h3><span class="dashicons dashicons-list-view"></span> <?php _e('Recent Reports', 'mpr'); ?></h3>
                <?php if ($recent_reports->have_posts()): ?>
                    <table class="mpr-recent-table">
                        <thead>
                            <tr>
                                <th><?php _e('Photo', 'mpr'); ?></th>
                                <th><?php _e('Name', 'mpr'); ?></th>
                                <th><?php _e('Status', 'mpr'); ?></th>
                                <th><?php _e('Risk', 'mpr'); ?></th>
                                <th><?php _e('Date', 'mpr'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($recent_reports->have_posts()):
            $recent_reports->the_post();
            $status = get_post_meta(get_the_ID(), 'mpr_case_status', true) ?: __('Missing', 'mpr');
            $risk = get_post_meta(get_the_ID(), 'mpr_risk_level', true) ?: __('Low', 'mpr');
            $thumb = mpr_get_case_image_url(get_the_ID(), array(40, 40));
?>
                                <tr>
                                    <td><img src="<?php echo esc_url($thumb); ?>" width="35" height="35" style="object-fit:cover;"></td>
                                    <td><strong><a href="<?php echo get_edit_post_link(get_the_ID()); ?>"><?php the_title(); ?></a></strong></td>
                                    <td><?php echo esc_html($status); ?></td>
                                    <td><span class="mpr-badge badge-<?php echo strtolower($risk); ?>"><?php echo esc_html($risk); ?></span></td>
                                    <td><?php echo get_the_date(); ?></td>
                                </tr>
                            <?php
        endwhile;
        wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                <?php
    else: ?>
                    <p><?php _e('No reports found yet.', 'mpr'); ?></p>
                <?php
    endif; ?>
            </div>

            <div class="mpr-quick-actions">
                <div class="mpr-content-box">
                    <h3><span class="dashicons dashicons-admin-links"></span> <?php _e('Quick Actions', 'mpr'); ?></h3>
                    <ul>
                        <li><a href="<?php echo admin_url('post-new.php?post_type=missing_person'); ?>"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Create New Case', 'mpr'); ?></a></li>
                        <li><a href="<?php echo admin_url('edit.php?post_type=missing_person&page=mpr-agency-settings'); ?>"><span class="dashicons dashicons-admin-generic"></span> <?php _e('Agency Settings', 'mpr'); ?></a></li>
                        <li><a href="<?php echo admin_url('edit.php?post_type=missing_person'); ?>"><span class="dashicons dashicons-groups"></span> <?php _e('View All Cases', 'mpr'); ?></a></li>
                    </ul>
                    
                    <div style="margin-top:25px; padding-top:20px; border-top:1px solid #f0f0f1;">
                        <p style="font-size:0.85rem; color:#646970;"><strong><?php _e('Agency Info:', 'mpr'); ?></strong><br>
                        <?php echo esc_html(get_option('mpr_agency_name', __('Not Set', 'mpr'))); ?><br>
                        <?php echo esc_html(get_option('mpr_agency_phone', __('Not Set', 'mpr'))); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
