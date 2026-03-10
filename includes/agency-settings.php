<?php
/**
 * Agency Information Settings for Missing People Reporter
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add the settings page to the admin menu.
 */
function mpr_add_agency_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=missing_person',
        __('Agency Information', 'mpr'),
        __('Agency Information', 'mpr'),
        'manage_options',
        'mpr-agency-settings',
        'mpr_render_agency_settings_page'
    );
}
add_action('admin_menu', 'mpr_add_agency_settings_page');

/**
 * Initialize settings, sections, and fields.
 */
function mpr_initialize_agency_settings()
{
    register_setting('mpr_agency_settings_group', 'mpr_agency_name');
    register_setting('mpr_agency_settings_group', 'mpr_agency_email');
    register_setting('mpr_agency_settings_group', 'mpr_agency_phone');
    register_setting('mpr_agency_settings_group', 'mpr_agency_address');
    register_setting('mpr_agency_settings_group', 'mpr_agency_website');

    add_settings_section(
        'mpr_agency_main_section',
        __('Basic Agency Information', 'mpr'),
        'mpr_agency_section_callback',
        'mpr-agency-settings'
    );

    add_settings_field('mpr_agency_name', __('Agency Name', 'mpr'), 'mpr_agency_name_render', 'mpr-agency-settings', 'mpr_agency_main_section');
    add_settings_field('mpr_agency_email', __('Contact Email', 'mpr'), 'mpr_agency_email_render', 'mpr-agency-settings', 'mpr_agency_main_section');
    add_settings_field('mpr_agency_phone', __('Contact Phone', 'mpr'), 'mpr_agency_phone_render', 'mpr-agency-settings', 'mpr_agency_main_section');
    add_settings_field('mpr_agency_address', __('Physical Address', 'mpr'), 'mpr_agency_address_render', 'mpr-agency-settings', 'mpr_agency_main_section');
    add_settings_field('mpr_agency_website', __('Website URL', 'mpr'), 'mpr_agency_website_render', 'mpr-agency-settings', 'mpr_agency_main_section');
}
add_action('admin_init', 'mpr_initialize_agency_settings');

function mpr_agency_section_callback()
{
    echo '<p>' . __('Enter the basic contact information for your agency. This information will be used on posters and contact sections.', 'mpr') . '</p>';
}

function mpr_agency_name_render()
{
    $val = get_option('mpr_agency_name');
    echo '<input type="text" name="mpr_agency_name" value="' . esc_attr($val) . '" class="regular-text">';
}

function mpr_agency_email_render()
{
    $val = get_option('mpr_agency_email');
    echo '<input type="email" name="mpr_agency_email" value="' . esc_attr($val) . '" class="regular-text">';
}

function mpr_agency_phone_render()
{
    $val = get_option('mpr_agency_phone');
    echo '<input type="text" name="mpr_agency_phone" value="' . esc_attr($val) . '" class="regular-text">';
}

function mpr_agency_address_render()
{
    $val = get_option('mpr_agency_address');
    echo '<textarea name="mpr_agency_address" rows="3" class="large-text">' . esc_textarea($val) . '</textarea>';
}

function mpr_agency_website_render()
{
    $val = get_option('mpr_agency_website');
    echo '<input type="url" name="mpr_agency_website" value="' . esc_attr($val) . '" class="regular-text">';
}

/**
 * Render the settings page.
 */
function mpr_render_agency_settings_page()
{
?>
    <div class="wrap mpr-admin-wrap">
        <h1><?php echo esc_html__('Agency Information Settings', 'mpr'); ?></h1>
        <form action="options.php" method="post">
            <?php
    settings_fields('mpr_agency_settings_group');
    do_settings_sections('mpr-agency-settings');
    submit_button();
?>
        </form>
    </div>
    <?php
}
