<?php
/**
 * Job System Plugin Test File
 * Upload this file to the main site directory to test the plugin
 */

// Check the path
if (!file_exists('wp-config.php')) {
    die('Please upload this file in the same directory as wp-config.php');
}

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Handle requests
if (isset($_POST['flush_rules'])) {
    flush_rewrite_rules();
    echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
    echo '<strong>Rewrite Rules have been updated!</strong><br>';
    echo '</div>';
}

if (isset($_POST['force_refresh'])) {
    // Reactivate the plugin
    if (is_plugin_active('job-system/job-system.php')) {
        deactivate_plugins('job-system/job-system.php');
        activate_plugin('job-system/job-system.php');
        echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<strong>Plugin has been reactivated!</strong><br>';
        echo '</div>';
    }
}

echo '<h2>Job System Plugin Test</h2>';

// Check if the plugin exists
$plugin_file = 'wp-content/plugins/job-system/job-system.php';
if (file_exists($plugin_file)) {
    echo '<p style="color: green;">✅ Plugin file exists</p>';
} else {
    echo '<p style="color: red;">❌ Plugin file does not exist</p>';
    exit;
}

// Check if the plugin is active
if (is_plugin_active('job-system/job-system.php')) {
    echo '<p style="color: green;">✅ Plugin is active</p>';
} else {
    echo '<p style="color: orange;">⚠️ Plugin is not active</p>';
}

// Check Post Types
$post_types = get_post_types();
if (isset($post_types['job']) && isset($post_types['job_department'])) {
    echo '<p style="color: green;">✅ Post Types registered successfully</p>';
} else {
    echo '<p style="color: red;">❌ Post Types not registered</p>';
}

// Check templates
$templates = array(
    'departments-archive.php',
    'department-jobs.php', 
    'single-job.php'
);

echo '<h3>Template Files:</h3>';
foreach ($templates as $template) {
    $template_path = 'wp-content/plugins/job-system/templates/' . $template;
    if (file_exists($template_path)) {
        echo '<p style="color: green;">✅ ' . $template . ' exists</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $template . ' does not exist</p>';
    }
}

// Test rewrite rules
echo '<h3>Link Test:</h3>';
echo '<ul>';
echo '<li><a href="/jobs/" target="_blank">Departments Page (/jobs/)</a></li>';
echo '<li><a href="/jobs/department?id=1" target="_blank">Department Jobs (if exists)</a></li>';
echo '</ul>';

// Test rewrite rules
$rules = get_option('rewrite_rules');
$job_rules_found = false;
if (is_array($rules)) {
    foreach ($rules as $pattern => $rule) {
        if (strpos($pattern, 'jobs') !== false || strpos($rule, 'job_system_page') !== false) {
            $job_rules_found = true;
            echo '<p style="color: green;">✅ Rewrite rule found: ' . $pattern . ' => ' . $rule . '</p>';
        }
    }
}

if (!$job_rules_found) {
    echo '<p style="color: red;">❌ No rewrite rules found for jobs</p>';
    echo '<form method="post">';
    echo '<input type="hidden" name="flush_rules" value="1">';
    echo '<button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Update Rewrite Rules</button>';
    echo '</form>';
}

// Statistics
$departments = get_posts(array('post_type' => 'job_department', 'numberposts' => -1));
$jobs = get_posts(array('post_type' => 'job', 'numberposts' => -1));

echo '<h3>Statistics:</h3>';
echo '<p>Number of Departments: <strong>' . count($departments) . '</strong></p>';
echo '<p>Number of Jobs: <strong>' . count($jobs) . '</strong></p>';

// Control buttons
echo '<h3>Repair Tools:</h3>';
echo '<div style="display: flex; gap: 10px; margin-bottom: 20px;">';
echo '<form method="post" style="display: inline;">';
echo '<input type="hidden" name="force_refresh" value="1">';
echo '<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Reactivate Plugin</button>';
echo '</form>';

echo '<form method="post" style="display: inline;">';
echo '<input type="hidden" name="flush_rules" value="1">';
echo '<button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Update Rules</button>';
echo '</form>';
echo '</div>';

// Delete file button
echo '<h3>Cleanup:</h3>';
echo '<form method="post">';
echo '<input type="hidden" name="delete_test_file" value="1">';
echo '<button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Delete Test File</button>';
echo '</form>';

if (isset($_POST['delete_test_file'])) {
    unlink(__FILE__);
    echo '<p style="color: green;">Test file has been deleted!</p>';
    echo '<script>setTimeout(function(){ window.location.href = "/wp-admin/"; }, 2000);</script>';
}
?>
