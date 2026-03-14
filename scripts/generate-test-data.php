<?php
/**
 * Test Data Generator for Missing People Reporter
 * 
 * Instructions:
 * Ensure this file is placed in your WordPress root or loaded in an environment where WordPress functions are available.
 * e.g., run via WP-CLI: `wp eval-file wp-content/plugins/missing-people-reporter/scripts/generate-test-data.php`
 * Or place in the root directory temporarily and access via browser: `http://yoursite.local/generate-test-data.php`
 * 
 * DO NOT LEAVE THIS SCRIPT IN A PRODUCTION ENVIRONMENT.
 */

// Attempt to load WordPress Core
$wp_load_paths = [
    dirname(__FILE__, 5) . '/wp-load.php', // Assuming wp-content/plugins/plugin-name/scripts/
    dirname(__FILE__, 4) . '/wp-load.php',
    dirname(__FILE__, 3) . '/wp-load.php',
    dirname(__FILE__, 2) . '/wp-load.php',
    dirname(__FILE__, 1) . '/wp-load.php',
];

$loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    die("Could not find wp-load.php. Please copy this script to your WordPress root directory to run it.");
}

// Proceed only if admin or cli
if (php_sapi_name() !== 'cli' && (!function_exists('current_user_can') || !current_user_can('manage_options'))) {
    die('Unauthorized access. You must be an administrator to run this script from the browser.');
}

if (!function_exists('wp_insert_post')) {
    die("WordPress core functions not available. Are you running this correctly?");
}

echo "Starting Test Data Generation...\n";
echo "=====================================\n";

// --- 1. Generate Missing Persons ---
$names = ['John Doe', 'Jane Smith', 'David Kipkorir', 'Amina Onyango', 'Michael Wanjiku', 'Sarah Ndungu', 'Peter Ochieng', 'Mary Moraa', 'Kevin Kimani', 'Gladys Njeri'];
$statuses = ['Missing', 'Found - Safe', 'Found - Deceased', 'Cold Case'];
$risks = ['Low', 'Medium', 'High'];
$locations = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika'];

$created_posts = [];

echo "Creating 10 Missing Person Cases...\n";
for ($i = 0; $i < 10; $i++) {
    $name = $names[$i];
    $status = $statuses[array_rand($statuses)];
    $risk = $risks[array_rand($risks)];
    $location = $locations[array_rand($locations)];

    $post_data = array(
        'post_title' => $name,
        'post_content' => "This is an auto-generated test case. Details: Last seen wearing a blue jacket near the {$location} town center.",
        'post_status' => 'publish',
        'post_author' => 1, // Assumes Admin is ID 1
        'post_type' => 'missing_person'
    );

    $post_id = wp_insert_post($post_data);

    if (!is_wp_error($post_id)) {
        // Add Meta Data
        update_post_meta($post_id, 'mpr_case_status', $status);
        update_post_meta($post_id, 'mpr_risk_level', $risk);
        update_post_meta($post_id, 'mpr_nickname', explode(' ', $name)[0] . 'ie');
        update_post_meta($post_id, 'mpr_age', rand(5, 75));
        update_post_meta($post_id, 'mpr_gender', (rand(0, 1) ? 'Male' : 'Female'));
        update_post_meta($post_id, 'mpr_height', rand(120, 190) . ' cm');
        update_post_meta($post_id, 'mpr_date_last_seen', date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')));
        update_post_meta($post_id, 'mpr_last_seen_location', $location);
        update_post_meta($post_id, 'mpr_police_station', $location . ' Central');
        update_post_meta($post_id, 'mpr_ob_number', 'OB/' . rand(100, 999) . '/' . date('Y'));

        $created_posts[] = $post_id;
        echo "- Created: {$name} [{$status} / {$risk} Risk] (ID: {$post_id})\n";
    }
    else {
        echo "- Failed to create: {$name}\n";
    }
}

// --- 2. Generate Leads (Comments) ---
echo "\nGenerating Leads (Comments) for Missing Cases...\n";
foreach ($created_posts as $post_id) {
    if (get_post_meta($post_id, 'mpr_case_status', true) === 'Missing') {
        $num_leads = rand(0, 3);
        if ($num_leads > 0) {
            for ($j = 0; $j < $num_leads; $j++) {
                $commentdata = array(
                    'comment_post_ID' => $post_id,
                    'comment_author' => 'Anonymous Witness',
                    'comment_author_email' => 'witness' . rand(1, 100) . '@example.com',
                    'comment_content' => "I think I saw someone matching this description near the main bus park on " . date('l, M jS', strtotime('-' . rand(1, 10) . ' days')) . ".",
                    'comment_type' => '',
                    'comment_parent' => 0,
                    'user_id' => 0,
                    'comment_approved' => 1, // Automatically approve for testing
                );
                wp_insert_comment($commentdata);
            }
            echo "- Added {$num_leads} lead(s) to Case ID: {$post_id}\n";
        }
    }
}

// --- 3. Generate Subscriptions ---
echo "\nGenerating Test Dummy Subscriptions...\n";
global $wpdb;
$table_name = $wpdb->prefix . 'mpr_push_subscriptions';

// Check if table exists before trying to insert
if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
    for ($k = 1; $k <= 5; $k++) {
        $wpdb->insert(
            $table_name,
            array(
            'case_id' => $created_posts[array_rand($created_posts)],
            'user_email' => "subscriber{$k}@example.com",
            'phone_number' => '+25470000000' . $k,
            'status' => 'active',
            'created_at' => current_time('mysql')
        ),
            array('%d', '%s', '%s', '%s', '%s')
        );
    }
    echo "- Added 5 test subscriptions to the database.\n";
}
else {
    echo "- Skipping subscriptions: Custom table `{$table_name}` does not exist yet. Did you activate the plugin?\n";
}

echo "\n=====================================\n";
echo "Test Data Generation Complete!\n";

function is_cli()
{
    return (php_sapi_name() === 'cli' || defined('STDIN'));
}
