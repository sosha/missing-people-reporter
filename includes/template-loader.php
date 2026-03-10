<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load a template from the plugin's templates directory.
 * This function checks if the current query is for our CPT and
 * loads the template from our plugin, otherwise it returns the theme's template.
 */
function mpr_template_include($template) {
    // For the main list of missing people (the archive page)
    if (is_post_type_archive('missing_person')) {
        $new_template = MPR_PLUGIN_PATH . 'templates/archive-missing_person.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }

    // For the single missing person detail page
    if (is_singular('missing_person')) {
        $new_template = MPR_PLUGIN_PATH . 'templates/single-missing_person.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }

    return $template; // Return the original template
}
add_filter('template_include', 'mpr_template_include', 99);