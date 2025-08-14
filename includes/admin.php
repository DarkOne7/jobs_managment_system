<?php
/**
 * Admin interface for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'Job Management',
            'Job Management',
            'manage_options',
            'jms_dashboard',
            array($this, 'dashboard_page'),
            'dashicons-businessman',
            25
        );
        
        // Dashboard
        add_submenu_page(
            'jms_dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'jms_dashboard',
            array($this, 'dashboard_page')
        );
        
        // Departments
        add_submenu_page(
            'jms_dashboard',
            'Departments',
            'Departments',
            'manage_options',
            'jms_departments',
            array($this, 'departments_page')
        );
        
        // Locations
        add_submenu_page(
            'jms_dashboard',
            'Locations',
            'Locations',
            'manage_options',
            'jms_locations',
            array($this, 'locations_page')
        );
        
        // Jobs
        add_submenu_page(
            'jms_dashboard',
            'Jobs',
            'Jobs',
            'manage_options',
            'jms_jobs',
            array($this, 'jobs_page')
        );
        
        // Applications
        add_submenu_page(
            'jms_dashboard',
            'Applications',
            'Applications',
            'manage_options',
            'jms_applications',
            array($this, 'applications_page')
        );
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle export action early to avoid WordPress output
        if (isset($_GET['action']) && isset($_GET['page']) && $_GET['page'] === 'jms_applications') {
            if ($_GET['action'] === 'export') {
                $this->export_applications();
                return;
            } elseif ($_GET['action'] === 'export_xlsx') {
                $this->export_applications_xlsx();
                return;
            }
        }
        
        // Handle form submissions here
        if (isset($_POST['jms_action'])) {
            switch ($_POST['jms_action']) {
                case 'save_department':
                    $this->save_department();
                    break;
                case 'save_location':
                    $this->save_location();
                    break;
                            case 'save_job':
                $this->save_job();
                break;
            case 'update_application':
                $this->update_application();
                break;
            }
        }
        
        // Handle delete actions
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'delete_department':
                    if (isset($_GET['id'])) {
                        $this->delete_department(intval($_GET['id']));
                    }
                    break;
                case 'delete_location':
                    if (isset($_GET['id'])) {
                        $this->delete_location(intval($_GET['id']));
                    }
                    break;
                case 'delete_job':
                    if (isset($_GET['id'])) {
                        $this->delete_job(intval($_GET['id']));
                    }
                    break;
            }
        }
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        global $wpdb;
        
        // Get statistics
        $stats = array(
            'departments' => $wpdb->get_var("SELECT COUNT(*) FROM " . JMS_Database::get_table('departments')),
            'locations' => $wpdb->get_var("SELECT COUNT(*) FROM " . JMS_Database::get_table('locations')),
            'jobs' => $wpdb->get_var("SELECT COUNT(*) FROM " . JMS_Database::get_table('jobs')),
            'applications' => $wpdb->get_var("SELECT COUNT(*) FROM " . JMS_Database::get_table('applications')),
            'pending_applications' => $wpdb->get_var("SELECT COUNT(*) FROM " . JMS_Database::get_table('applications') . " WHERE status = 'pending'")
        );
        
        // Get recent applications
        $recent_applications = JMS_DB_Helper::get_applications(array('limit' => 5));
        
        include JMS_PLUGIN_PATH . 'admin/dashboard.php';
    }
    
    /**
     * Departments page
     */
    public function departments_page() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $department_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'delete' && $department_id > 0) {
            $this->delete_department($department_id);
            wp_redirect(admin_url('admin.php?page=jms_departments&message=' . urlencode('Department deleted successfully!')));
            exit;
        } elseif ($action === 'edit' && $department_id > 0) {
            $department = $this->get_department($department_id);
            include JMS_PLUGIN_PATH . 'admin/department-form.php';
        } elseif ($action === 'add') {
            $department = null;
            include JMS_PLUGIN_PATH . 'admin/department-form.php';
        } else {
            $departments = JMS_DB_Helper::get_departments();
            include JMS_PLUGIN_PATH . 'admin/departments-list.php';
        }
    }
    
    /**
     * Locations page
     */
    public function locations_page() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $location_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'delete' && $location_id > 0) {
            $this->delete_location($location_id);
            wp_redirect(admin_url('admin.php?page=jms_locations&message=' . urlencode('Location deleted successfully!')));
            exit;
        } elseif ($action === 'edit' && $location_id > 0) {
            $location = $this->get_location($location_id);
            include JMS_PLUGIN_PATH . 'admin/location-form.php';
        } elseif ($action === 'add') {
            $location = null;
            include JMS_PLUGIN_PATH . 'admin/location-form.php';
        } else {
            $locations = JMS_DB_Helper::get_locations();
            include JMS_PLUGIN_PATH . 'admin/locations-list.php';
        }
    }
    
    /**
     * Jobs page
     */
    public function jobs_page() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'delete' && $job_id > 0) {
            $this->delete_job($job_id);
            wp_redirect(admin_url('admin.php?page=jms_jobs&message=' . urlencode('Job deleted successfully!')));
            exit;
        } elseif ($action === 'edit' && $job_id > 0) {
            $job = $this->get_job($job_id);
            $departments = JMS_DB_Helper::get_departments();
            $locations = JMS_DB_Helper::get_locations();
            include JMS_PLUGIN_PATH . 'admin/job-form.php';
        } elseif ($action === 'add') {
            $job = null;
            $departments = JMS_DB_Helper::get_departments();
            $locations = JMS_DB_Helper::get_locations();
            include JMS_PLUGIN_PATH . 'admin/job-form.php';
        } else {
            $jobs = JMS_DB_Helper::get_jobs(array('status' => ''));
            include JMS_PLUGIN_PATH . 'admin/jobs-list.php';
        }
    }
    
    /**
     * Applications page
     */
    public function applications_page() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'export') {
            $this->export_applications();
            return;
        } elseif ($action === 'delete' && $application_id > 0) {
            $this->delete_application($application_id);
            wp_redirect(admin_url('admin.php?page=jms_applications&message=' . urlencode('Application deleted successfully!')));
            exit;
        } elseif ($action === 'view' && $application_id > 0) {
            $application = $this->get_application($application_id);
            include JMS_PLUGIN_PATH . 'admin/application-view.php';
        } else {
            $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
            $job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
            
            $filter_args = array();
            if (!empty($status_filter)) {
                $filter_args['status'] = $status_filter;
            }
            if ($job_filter > 0) {
                $filter_args['job_id'] = $job_filter;
            }
            
            $applications = JMS_DB_Helper::get_applications($filter_args);
            include JMS_PLUGIN_PATH . 'admin/applications-list.php';
        }
    }
    
    // Database operations
    
    private function save_department() {
        if (!wp_verify_nonce($_POST['jms_nonce'], 'jms_department_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['slug'] ?: $_POST['name']),
            'color' => sanitize_hex_color($_POST['color']),
            'icon' => sanitize_text_field($_POST['icon']),
            'description' => sanitize_textarea_field($_POST['description'])
        );
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id > 0) {
            $wpdb->update($table, $data, array('id' => $id));
            $message = 'Department updated successfully!';
        } else {
            $wpdb->insert($table, $data);
            $message = 'Department created successfully!';
        }
        
        wp_redirect(add_query_arg(array('page' => 'jms_departments', 'message' => urlencode($message)), admin_url('admin.php')));
        exit;
    }
    
    private function save_location() {
        if (!wp_verify_nonce($_POST['jms_nonce'], 'jms_location_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['slug'] ?: $_POST['name']),
            'description' => sanitize_textarea_field($_POST['description'])
        );
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id > 0) {
            $wpdb->update($table, $data, array('id' => $id));
            $message = 'Location updated successfully!';
        } else {
            $wpdb->insert($table, $data);
            $message = 'Location created successfully!';
        }
        
        wp_redirect(add_query_arg(array('page' => 'jms_locations', 'message' => urlencode($message)), admin_url('admin.php')));
        exit;
    }
    
    private function save_job() {
        if (!wp_verify_nonce($_POST['jms_nonce'], 'jms_job_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'slug' => sanitize_title($_POST['slug'] ?: $_POST['name']),
            'department_id' => intval($_POST['department_id']),
            'location_id' => intval($_POST['location_id']),
            'work_type' => sanitize_text_field($_POST['work_type']),
            'application_deadline' => sanitize_text_field($_POST['application_deadline']),
            'description' => wp_kses_post($_POST['description']),
            'status' => 'active'  // Always set status to active
        );
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id > 0) {
            $wpdb->update($table, $data, array('id' => $id));
            $message = 'Job updated successfully!';
        } else {
            $wpdb->insert($table, $data);
            $message = 'Job created successfully!';
        }
        
        wp_redirect(add_query_arg(array('page' => 'jms_jobs', 'message' => urlencode($message)), admin_url('admin.php')));
        exit;
    }
    
    private function update_application() {
        if (!wp_verify_nonce($_POST['jms_nonce'], 'jms_application_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $table = JMS_Database::get_table('applications');
        
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['status']);
        $admin_notes = sanitize_textarea_field(isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '');
        
        // Debug log
        error_log("JMS: Updating application ID: $application_id, Status: $new_status");
        
        // Get current application data
        $application = $this->get_application($application_id);
        if (!$application) {
            wp_die('Application not found');
        }
        
        $old_status = $application->status;
        
        // Update application
        $data = array(
            'status' => $new_status,
            'admin_notes' => $admin_notes,
            'updated_at' => current_time('mysql')
        );
        
        $result = $wpdb->update($table, $data, array('id' => $application_id));
        
        if ($result !== false) {
            error_log("JMS: Application updated successfully. Old status: $old_status, New status: $new_status");
            
            // Send email notification if status changed
            if ($old_status !== $new_status && !empty($application->email)) {
                $email_result = $this->send_status_change_email($application_id, $old_status, $new_status);
                error_log("JMS: Email notification sent: " . ($email_result ? 'Success' : 'Failed'));
            }
            
            $message = 'Application updated successfully!';
        } else {
            error_log("JMS: Failed to update application. WPDB Error: " . $wpdb->last_error);
            $message = 'Error updating application: ' . $wpdb->last_error;
        }
        
        wp_redirect(admin_url('admin.php?page=jms_applications&action=view&id=' . $application_id . '&message=' . urlencode($message)));
        exit;
    }
    
    private function send_status_change_email($application_id, $old_status, $new_status) {
        // Get updated application data
        $application = $this->get_application($application_id);
        if (!$application) {
            return false;
        }
        
        // Initialize email handler
        if (!class_exists('JMS_Email_Handler')) {
            require_once JMS_PLUGIN_PATH . 'includes/email-handler.php';
        }
        
        $email_handler = new JMS_Email_Handler();
        
        // Send appropriate notification based on status
        switch ($new_status) {
            case 'approved':
                return $email_handler->send_approval_notification($application);
            case 'rejected':
                return $email_handler->send_rejection_notification($application);
            case 'shortlisted':
                return $email_handler->send_shortlist_notification($application);
            default:
                return $email_handler->send_status_update_notification($application, $old_status, $new_status);
        }
    }
    
    // Helper functions to get individual records
    
    private function delete_department($id) {
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        $wpdb->delete($table, array('id' => $id));
    }
    
    private function delete_location($id) {
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        $wpdb->delete($table, array('id' => $id));
    }
    
    private function delete_job($id) {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        $wpdb->delete($table, array('id' => $id));
    }
    
    private function delete_application($id) {
        global $wpdb;
        $table = JMS_Database::get_table('applications');
        $wpdb->delete($table, array('id' => $id));
    }
    
    private function get_department($id) {
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    private function get_location($id) {
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    private function get_job($id) {
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        $loc_table = JMS_Database::get_table('locations');
        
        $sql = "SELECT j.*, d.name as department_name, l.name as location_name
                FROM $jobs_table j 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                LEFT JOIN $loc_table l ON j.location_id = l.id 
                WHERE j.id = %d";
        
        return $wpdb->get_row($wpdb->prepare($sql, $id));
    }
    
    private function get_application($id) {
        global $wpdb;
        $app_table = JMS_Database::get_table('applications');
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        
        $sql = "SELECT a.*, j.name as job_name, d.name as department_name 
                FROM $app_table a 
                LEFT JOIN $jobs_table j ON a.job_id = j.id 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                WHERE a.id = %d";
        
        return $wpdb->get_row($wpdb->prepare($sql, $id));
    }
    
    /**
     * Export applications to XLSX
     */
    private function export_applications_xlsx() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Include XLSX writer
        require_once JMS_PLUGIN_PATH . 'includes/xlsx-writer.php';
        
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        
        $filter_args = array();
        if (!empty($status_filter)) {
            $filter_args['status'] = $status_filter;
        }
        if ($job_filter > 0) {
            $filter_args['job_id'] = $job_filter;
        }
        
        $applications = JMS_DB_Helper::get_applications($filter_args);
        
        // Set filename
        $filename_parts = array('job-applications');
        if ($job_filter > 0) {
            $job = JMS_DB_Helper::get_job($job_filter);
            if ($job) {
                $filename_parts[] = sanitize_title($job->name);
            }
        }
        if ($status_filter) {
            $filename_parts[] = $status_filter;
        }
        $filename_parts[] = date('Y-m-d-H-i-s');
        $filename = implode('-', $filename_parts) . '.xlsx';
        
        // Create XLSX writer
        $xlsx = new JMS_XLSX_Writer($filename);
        
        // Set status filter for display
        if ($status_filter) {
            $xlsx->setStatusFilter($status_filter);
        }
        
        // Set headers (without ID column)
        $headers = array(
            'Applicant Name', 
            'Email',
            'Phone',
            'Job Title',
            'Application Date',
            'Status',
            'CV Download',
            'Admin Notes'
        );
        $xlsx->setHeaders($headers);
        
        // Add data rows (without ID column)
        foreach ($applications as $app) {
            $row = array(
                $app->name ?? '',
                $app->email ?? '',
                $app->phone ?? '',
                $app->job_name ?? '',
                date('n/j/Y G:i', strtotime($app->created_at ?? '')), // Format like 8/8/2025 14:25
                ucfirst($app->status ?? ''),
                $app->cv_file ? 'Yes' : 'No',
                $app->admin_notes ?? ''
            );
            
            // Generate CV download link if file exists
            $cv_link = '';
            if ($app->cv_file) {
                $cv_link = JMS_UPLOAD_URL . $app->cv_file;
            }
            
            $xlsx->addRow($row, $cv_link);
        }
        
        // Download file
        $xlsx->download();
    }
    
    /**
     * Export applications to Excel (CSV)
     */
    private function export_applications() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Clear any output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        
        $filter_args = array();
        if (!empty($status_filter)) {
            $filter_args['status'] = $status_filter;
        }
        if ($job_filter > 0) {
            $filter_args['job_id'] = $job_filter;
        }
        
        $applications = JMS_DB_Helper::get_applications($filter_args);
        
        // Set headers for file download
        $filename_parts = array('job-applications');
        if ($job_filter > 0) {
            $job = JMS_DB_Helper::get_job($job_filter);
            if ($job) {
                $filename_parts[] = sanitize_title($job->name);
            }
        }
        if ($status_filter) {
            $filename_parts[] = $status_filter;
        }
        $filename_parts[] = date('Y-m-d-H-i-s');
        $filename = implode('-', $filename_parts) . '.csv';
        
        // Prevent any WordPress output
        nocache_headers();
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create file pointer connected to output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add column headers
        $headers = array(
            'ID',
            'Applicant Name', 
            'Email',
            'Phone',
            'Job Title',
            'Department',
            'Status',
            'Applied Date',
            'CV File',
            'Admin Notes'
        );
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($applications as $app) {
            $row = array(
                $app->id,
                $app->name ?? '',
                $app->email ?? '',
                $app->phone ?? '',
                $app->job_name ?? '',
                $app->department_name ?? '',
                ucfirst($app->status ?? ''),
                date('Y-m-d H:i:s', strtotime($app->created_at ?? '')),
                $app->cv_file ? 'Yes' : 'No',
                $app->admin_notes ?? ''
            );
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}

// Initialize admin
new JMS_Admin();
