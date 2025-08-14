<?php
/**
 * AJAX handlers for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Ajax_Handler {
    
    /**
     * Handle job application submission
     */
    public function handle_application_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jms_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Validate required fields
        $required_fields = array('name', 'email', 'job_id');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error('Please fill in all required fields');
            }
        }
        
        // Sanitize input
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $job_id = intval($_POST['job_id']);
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error('Please enter a valid email address');
        }
        
        // Check if job exists and is active
        $job = $this->get_job_by_id($job_id);
        if (!$job || $job->status !== 'active') {
            wp_send_json_error('Job not found or no longer active');
        }
        
        // Check if application deadline has passed
        if (!empty($job->application_deadline) && strtotime($job->application_deadline) < time()) {
            wp_send_json_error('Application deadline has passed');
        }
        
        // Check for duplicate application
        if ($this->has_duplicate_application($email, $job_id)) {
            wp_send_json_error('You have already applied for this position');
        }
        
        // Handle CV file upload
        $cv_filename = '';
        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
            $cv_filename = $this->handle_cv_upload($_FILES['cv_file'], $email, $job_id);
            if (!$cv_filename) {
                wp_send_json_error('Failed to upload CV file');
            }
        }
        
        // Create application entity
        $application = new JMS_Application(array(
            'job_id' => $job_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'cv_file' => $cv_filename,
            'status' => 'pending'
        ));
        
        // Save application
        $application_id = $application->save();
        
        if ($application_id) {
            // Send notification email
            $application->send_notification('submitted');
            
            wp_send_json_success(array(
                'message' => 'Application submitted successfully!'
            ));
        } else {
            wp_send_json_error('Failed to submit application. Please try again.');
        }
    }
    
    /**
     * Handle application approval/rejection
     */
    public function handle_application_approval() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'jms_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['status']);
        $admin_notes = sanitize_textarea_field($_POST['admin_notes']);
        
        // Validate status
        $valid_statuses = array('pending', 'approved', 'rejected', 'shortlisted');
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error('Invalid status');
        }
        
        // Get application
        $application = $this->get_application_by_id($application_id);
        if (!$application) {
            wp_send_json_error('Application not found');
        }
        
        // Update application
        $application->status = $new_status;
        $application->admin_notes = $admin_notes;
        
        if ($application->save()) {
            // Send notification email to applicant
            $application->send_notification($new_status);
            
            wp_send_json_success(array(
                'message' => 'Application status updated successfully!',
                'new_status' => $application->get_status_display(),
                'status_color' => $application->get_status_color()
            ));
        } else {
            wp_send_json_error('Failed to update application status');
        }
    }
    
    /**
     * Handle CV file upload
     */
    private function handle_cv_upload($file, $email, $job_id) {
        // Check file size (5MB max)
        $max_size = get_option('jms_max_file_size', 5) * 1024 * 1024; // Convert MB to bytes
        if ($file['size'] > $max_size) {
            return false;
        }
        
        // Check file type
        $allowed_types = get_option('jms_allowed_file_types', array('pdf', 'doc', 'docx'));
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            return false;
        }
        
        // Generate unique filename
        $sanitized_email = sanitize_file_name($email);
        $timestamp = time();
        $filename = "cv_{$sanitized_email}_{$job_id}_{$timestamp}.{$file_ext}";
        
        // Upload file
        $upload_path = JMS_UPLOAD_DIR . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $filename;
        }
        
        return false;
    }
    
    /**
     * Check for duplicate application
     */
    private function has_duplicate_application($email, $job_id) {
        global $wpdb;
        $table = JMS_Database::get_table('applications');
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE email = %s AND job_id = %d",
            $email,
            $job_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Get job by ID
     */
    private function get_job_by_id($job_id) {
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        $loc_table = JMS_Database::get_table('locations');
        
        $sql = "SELECT j.*, d.name as department_name, l.name as location_name
                FROM $jobs_table j 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                LEFT JOIN $loc_table l ON j.location_id = l.id 
                WHERE j.id = %d";
        
        $job_data = $wpdb->get_row($wpdb->prepare($sql, $job_id));
        
        return $job_data ? new JMS_Job((array)$job_data) : null;
    }
    
    /**
     * Get application by ID
     */
    private function get_application_by_id($application_id) {
        global $wpdb;
        $app_table = JMS_Database::get_table('applications');
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        
        $sql = "SELECT a.*, j.name as job_name, d.name as department_name 
                FROM $app_table a 
                LEFT JOIN $jobs_table j ON a.job_id = j.id 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                WHERE a.id = %d";
        
        $app_data = $wpdb->get_row($wpdb->prepare($sql, $application_id));
        
        return $app_data ? new JMS_Application((array)$app_data) : null;
    }
}

