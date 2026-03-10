<?php
/**
 * Secure Lead Submission System for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create the Leads Database Table.
 */
function mpr_create_leads_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_leads';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        case_id bigint(20) NOT NULL,
        submitter_name varchar(100) NOT NULL,
        submitter_email varchar(100) NOT NULL,
        submitter_phone varchar(20) DEFAULT NULL,
        lead_content text NOT NULL,
        status varchar(20) DEFAULT 'unverified' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Handle Lead Submission via AJAX.
 */
function mpr_handle_lead_submission()
{
    check_ajax_referer('mpr_lead_nonce', 'nonce');

    $case_id = isset($_POST['case_id']) ? intval($_POST['case_id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $lead = isset($_POST['lead']) ? sanitize_textarea_field($_POST['lead']) : '';

    if (!$case_id || !$name || !$email || !$lead) {
        wp_send_json_error(array('message' => __('Please fill in all required fields.', 'mpr')));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_leads';

    $result = $wpdb->insert(
        $table_name,
        array(
        'case_id' => $case_id,
        'submitter_name' => $name,
        'submitter_email' => $email,
        'submitter_phone' => $phone,
        'lead_content' => $lead,
        'status' => 'unverified'
    ),
        array('%d', '%s', '%s', '%s', '%s', '%s')
    );

    if ($result) {
        // Trigger notification to agency
        do_action('mpr_after_lead_submitted', $wpdb->insert_id, $case_id);
        wp_send_json_success(array('message' => __('Your lead has been submitted securely. Thank you.', 'mpr')));
    }
    else {
        wp_send_json_error(array('message' => __('Failed to submit lead. Please try again.', 'mpr')));
    }
}
add_action('wp_ajax_mpr_submit_lead', 'mpr_handle_lead_submission');
add_action('wp_ajax_nopriv_mpr_submit_lead', 'mpr_handle_lead_submission');

/**
 * Add Leads menu to admin.
 */
function mpr_add_leads_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=missing_person',
        __('Leads & Information', 'mpr'),
        __('Leads', 'mpr'),
        'manage_options',
        'mpr-leads',
        'mpr_render_leads_admin_page'
    );
}
add_action('admin_menu', 'mpr_add_leads_admin_menu');

/**
 * Render Leads Admin Page.
 */
function mpr_render_leads_admin_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mpr_leads';
    $leads = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
?>
    <div class="wrap mpr-admin-wrap">
        <h1><?php _e('Submitted Leads', 'mpr'); ?></h1>
        <p><?php _e('This information is confidential and should be handled securely.', 'mpr'); ?></p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Date', 'mpr'); ?></th>
                    <th><?php _e('Case', 'mpr'); ?></th>
                    <th><?php _e('Submitter', 'mpr'); ?></th>
                    <th><?php _e('Contact', 'mpr'); ?></th>
                    <th><?php _e('Lead', 'mpr'); ?></th>
                    <th><?php _e('Status', 'mpr'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leads): ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?php echo esc_html($lead->created_at); ?></td>
                            <td><a href="<?php echo get_edit_post_link($lead->case_id); ?>"><?php echo get_the_title($lead->case_id); ?></a></td>
                            <td><?php echo esc_html($lead->submitter_name); ?></td>
                            <td>
                                <?php echo esc_html($lead->submitter_email); ?><br>
                                <?php echo esc_html($lead->submitter_phone); ?>
                            </td>
                            <td><?php echo nl2br(esc_html($lead->lead_content)); ?></td>
                            <td>
                                <span class="status-tag <?php echo esc_attr($lead->status); ?>">
                                    <?php echo esc_html(ucfirst(__($lead->status, 'mpr'))); ?>
                                </span>
                            </td>
                        </tr>
                    <?php
        endforeach; ?>
                <?php
    else: ?>
                    <tr><td colspan="6"><?php _e('No leads submitted yet.', 'mpr'); ?></td></tr>
                <?php
    endif; ?>
            </tbody>
        </table>
    </div>
    <style>
        .status-tag { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-tag.unverified { background: #eee; color: #666; }
        .status-tag.verified { background: #d4edda; color: #155724; }
    </style>
    <?php
}
