<?php
/**
 * Plugin Name: Job Management System
 * Plugin URI: https://yourwebsite.com
 * Description: A comprehensive job management system with departments, locations, jobs, and applications management using custom database entities.
 * Version: 2.0.0
 * Author: Your Name
 * License: GPL2
 * Text Domain: job-management-system
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JMS_VERSION', '2.0.0');
define('JMS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JMS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('JMS_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/job-system/');
define('JMS_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/job-system/');

/**
 * Main Job Management System Class
 */
class JobManagementSystem {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('template_redirect', array($this, 'template_redirect'));
        
        // Load required files
        $this->load_includes();
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Create upload directory
        if (!file_exists(JMS_UPLOAD_DIR)) {
            wp_mkdir_p(JMS_UPLOAD_DIR);
        }
        
        // Add rewrite rules
        $this->add_rewrite_rules();
        
        // Add query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Handle template includes
        add_filter('template_include', array($this, 'template_include'));
        
        // Auto-flush rewrite rules if needed
        if (get_option('jms_needs_flush')) {
            flush_rewrite_rules();
            delete_option('jms_needs_flush');
        }
        
        // Handle AJAX requests
        add_action('wp_ajax_jms_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_nopriv_jms_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_jms_approve_application', array($this, 'handle_application_approval'));
    }
    
    private function load_includes() {
        require_once JMS_PLUGIN_PATH . 'includes/database.php';
        require_once JMS_PLUGIN_PATH . 'includes/admin.php';
        require_once JMS_PLUGIN_PATH . 'includes/entities.php';
        require_once JMS_PLUGIN_PATH . 'includes/templates.php';
        require_once JMS_PLUGIN_PATH . 'includes/ajax-handlers.php';
        require_once JMS_PLUGIN_PATH . 'includes/email-handler.php';
        require_once JMS_PLUGIN_PATH . 'includes/shortcodes.php';
    }
    
    public function add_rewrite_rules() {
        // Main jobs page
        add_rewrite_rule('^jobs/?$', 'index.php?jms_page=departments', 'top');
        
        // Jobs by department
        add_rewrite_rule('^jobs/department/([^/]+)/?$', 'index.php?jms_page=department_jobs&department_slug=$matches[1]', 'top');
        
        // Jobs by location
        add_rewrite_rule('^jobs/location/([^/]+)/?$', 'index.php?jms_page=location_jobs&location_slug=$matches[1]', 'top');
        
        // Single job
        add_rewrite_rule('^jobs/([^/]+)/?$', 'index.php?jms_page=single_job&job_slug=$matches[1]', 'top');
        
        // Application success page
        add_rewrite_rule('^jobs/application/success/?$', 'index.php?jms_page=application_success', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'jms_page';
        $vars[] = 'department_slug';
        $vars[] = 'location_slug';
        $vars[] = 'job_slug';
        return $vars;
    }
    
    public function template_redirect() {
        $jms_page = get_query_var('jms_page');
        
        if ($jms_page) {
            // Enqueue job system styles and scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_job_scripts'));
        }
    }
    
    public function template_include($template) {
        $jms_page = get_query_var('jms_page');
        
        if ($jms_page) {
            switch ($jms_page) {
                case 'departments':
                    $new_template = JMS_PLUGIN_PATH . 'templates/departments-archive.php';
                    break;
                case 'department_jobs':
                    $new_template = JMS_PLUGIN_PATH . 'templates/department-jobs.php';
                    break;
                case 'location_jobs':
                    $new_template = JMS_PLUGIN_PATH . 'templates/location-jobs.php';
                    break;
                case 'single_job':
                    $new_template = JMS_PLUGIN_PATH . 'templates/single-job.php';
                    break;
                case 'application_success':
                    $new_template = JMS_PLUGIN_PATH . 'templates/application-success.php';
                    break;
                default:
                    return $template;
            }
            
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        
        return $template;
    }
    
    public function enqueue_scripts() {
        // Only enqueue on admin pages for job system
        if (is_admin() && (isset($_GET['page']) && strpos($_GET['page'], 'jms_') !== false)) {
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
        }
    }
    
    public function admin_enqueue_scripts() {
        // Admin styles and scripts for job system pages
        if (isset($_GET['page']) && strpos($_GET['page'], 'jms_') !== false) {
            wp_enqueue_style('jms-admin-style', JMS_PLUGIN_URL . 'assets/css/admin.css', array(), JMS_VERSION);
            wp_enqueue_script('jms-admin-script', JMS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), JMS_VERSION, true);
            
            wp_localize_script('jms-admin-script', 'jmsAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('jms_admin_nonce'),
                'strings' => array(
                    'confirm_delete' => 'Are you sure you want to delete this item?',
                    'loading' => 'Loading...',
                    'error' => 'An error occurred. Please try again.',
                    'success' => 'Operation completed successfully!'
                )
            ));
        }
    }
    
    public function enqueue_job_scripts() {
        $jms_page = get_query_var('jms_page');
        
        // Only load essential external resources
        // Font Awesome for icons
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');
        
        // Google Fonts for Poppins
        wp_enqueue_style('google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null);
        
        // Load JavaScript files only for pages that need them
        if ($jms_page === 'single_job') {
            wp_enqueue_script('jms-single-job', JMS_PLUGIN_URL . 'assets/js/single-job.js', array('jquery'), JMS_VERSION, true);
        }
        
        // Main JavaScript
        wp_enqueue_script('jms-script', JMS_PLUGIN_URL . 'assets/js/script.js', array('jquery'), JMS_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('jms-script', 'jmsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jms_nonce'),
            'upload_url' => JMS_UPLOAD_URL,
            'strings' => array(
                'loading' => 'Loading...',
                'error' => 'An error occurred. Please try again.',
                'success' => 'Application submitted successfully!',
                'file_too_large' => 'File size is too large. Maximum size is 5MB.',
                'invalid_file_type' => 'Invalid file type. Please upload a PDF or DOC file.',
                'required_fields' => 'Please fill in all required fields.'
            )
        ));
    }
    
    public function handle_application_submission() {
        check_ajax_referer('jms_nonce', 'nonce');
        
        // Include the AJAX handler
        $ajax_handler = new JMS_Ajax_Handler();
        $ajax_handler->handle_application_submission();
    }
    
    public function handle_application_approval() {
        check_ajax_referer('jms_admin_nonce', 'nonce');
        
        // Include the AJAX handler
        $ajax_handler = new JMS_Ajax_Handler();
        $ajax_handler->handle_application_approval();
    }
    
    public function activate() {
        // Create database tables
        JMS_Database::create_tables();
        
        // Create upload directory
        if (!file_exists(JMS_UPLOAD_DIR)) {
            wp_mkdir_p(JMS_UPLOAD_DIR);
            
            // Add index.php to prevent directory browsing
            file_put_contents(JMS_UPLOAD_DIR . 'index.php', '<?php // Silence is golden');
        }
        
        // Add rewrite rules
        $this->add_rewrite_rules();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set flag for future flushes
        update_option('jms_needs_flush', 1);
        
        // Set default options
        add_option('jms_email_notifications', 1);
        add_option('jms_max_file_size', 5); // 5MB
        add_option('jms_allowed_file_types', array('pdf', 'doc', 'docx'));
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
JobManagementSystem::getInstance();