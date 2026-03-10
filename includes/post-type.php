<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the 'Missing Person' custom post type.
 */
function mpr_register_missing_person_post_type() { // Renamed function for clarity and consistency
    $labels = [
        'name'                  => _x('Missing People', 'post type general name', 'mpr'),
        'singular_name'         => _x('Missing Person', 'post type singular name', 'mpr'),
        'menu_name'             => _x('Missing People', 'admin menu', 'mpr'),
        'name_admin_bar'        => _x('Missing Person', 'add new on admin bar', 'mpr'),
        'add_new'               => _x('Add New', 'missing person', 'mpr'),
        'add_new_item'          => __('Add New Missing Person', 'mpr'),
        'new_item'              => __('New Missing Person', 'mpr'),
        'edit_item'             => __('Edit Missing Person', 'mpr'),
        'view_item'             => __('View Missing Person', 'mpr'),
        'all_items'             => __('All Missing People', 'mpr'),
        'search_items'          => __('Search Missing People', 'mpr'),
        'not_found'             => __('No missing people found.', 'mpr'),
        'not_found_in_trash'    => __('No missing people found in Trash.', 'mpr'),
        'featured_image'        => __('Main Image', 'mpr'),
        'set_featured_image'    => __('Set Main Image', 'mpr'),
        'remove_featured_image' => __('Remove Main Image', 'mpr'),
    ];

    $args = [
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => ['slug' => 'missing-person'],
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-groups',
        'supports'              => ['title', 'editor', 'thumbnail', 'comments', 'custom-fields'], // Added 'custom-fields' support
        'show_in_rest'          => true, // Essential for Gutenberg editor and REST API integration
    ];

    register_post_type('missing_person', $args);
}
add_action('init', 'mpr_register_missing_person_post_type'); // Hook the renamed function to 'init'