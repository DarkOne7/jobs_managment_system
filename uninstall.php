<?php
/**
 * Job Management System Uninstall Script
 * Runs when the plugin is deleted through WordPress admin
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up all plugin data
 */
function jms_uninstall_cleanup() {
    global $wpdb;
    
    // Define upload directory
    $upload_dir = wp_upload_dir()['basedir'] . '/job-system/';
    
    // Delete all CV files
    if (is_dir($upload_dir)) {
        $files = glob($upload_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($upload_dir);
    }
    
    // Drop database tables (in correct order due to foreign keys)
    $tables_to_drop = array(
        $wpdb->prefix . 'jms_applications',
        $wpdb->prefix . 'jms_jobs',
        $wpdb->prefix . 'jms_locations',
        $wpdb->prefix . 'jms_departments'
    );
    
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    // Delete plugin options
    $options_to_delete = array(
        'jms_email_notifications',
        'jms_max_file_size',
        'jms_allowed_file_types',
        'jms_notification_email',
        'jms_needs_flush'
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
    
    // Clear any cached data
    wp_cache_flush();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Run cleanup
jms_uninstall_cleanup();