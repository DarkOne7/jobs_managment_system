<?php
/**
 * Plugin Name: Job System
 * Plugin URI: https://example.com/job-system
 * Description: Complete job management system with departments, jobs, and application forms.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: job-system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JOB_SYSTEM_VERSION', '1.0.0');
define('JOB_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JOB_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once JOB_SYSTEM_PLUGIN_DIR . 'includes/class-job-system.php';
require_once JOB_SYSTEM_PLUGIN_DIR . 'includes/class-job-system-admin.php';
require_once JOB_SYSTEM_PLUGIN_DIR . 'includes/class-job-system-frontend.php';
require_once JOB_SYSTEM_PLUGIN_DIR . 'includes/class-job-system-ajax.php';

// Initialize the plugin
function job_system_init() {
    $job_system = new Job_System();
    $job_system->init();
}
add_action('plugins_loaded', 'job_system_init');

// Activation hook
function job_system_activate_plugin() {
    // Create database tables first
    if (class_exists('Job_System')) {
        Job_System::activate();
    }
    
    // Force clear the rewrite rules flag
    delete_option('job_system_rewrite_rules_flushed');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Clear any caching
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}
register_activation_hook(__FILE__, 'job_system_activate_plugin');

// Deactivation hook
function job_system_deactivate_plugin() {
    Job_System::deactivate();
}
register_deactivation_hook(__FILE__, 'job_system_deactivate_plugin');
