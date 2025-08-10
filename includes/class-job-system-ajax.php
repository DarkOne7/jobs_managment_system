<?php
/**
 * AJAX functionality for Job System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Job_System_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_submit_job_application', array($this, 'submit_job_application'));
        add_action('wp_ajax_nopriv_submit_job_application', array($this, 'submit_job_application'));
        add_action('wp_ajax_update_application_status', array($this, 'update_application_status'));
    }
    
    /**
     * Submit job application
     */
    public function submit_job_application() {
        // Log the start of the function for debugging
        error_log('Job Application Submission Started');
        error_log('POST data: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'job_application_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error(array('message' => __('Security check failed', 'job-system')));
            return;
        }
        
        // Sanitize input data
        $job_id = intval($_POST['job_id']);
        $name = sanitize_text_field($_POST['applicant_name']);
        $email = sanitize_email($_POST['applicant_email']);
        $phone = sanitize_text_field($_POST['applicant_phone']);
        
        error_log("Received data - Job ID: $job_id, Name: $name, Email: $email, Phone: $phone");
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($job_id)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'job-system')));
        }
        
        // Check if job exists and applications are still open
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'job') {
            wp_send_json_error(array('message' => __('Invalid job.', 'job-system')));
        }
        
        $deadline = get_post_meta($job_id, '_application_deadline', true);
        if ($deadline && time() > strtotime($deadline)) {
            wp_send_json_error(array('message' => __('Application deadline has passed.', 'job-system')));
        }
        
        // Handle file upload
        $cv_file_path = '';
        if (!empty($_FILES['cv_file']['name'])) {
            $uploaded_file = $this->handle_cv_upload($_FILES['cv_file']);
            if (is_wp_error($uploaded_file)) {
                wp_send_json_error(array('message' => $uploaded_file->get_error_message()));
            }
            $cv_file_path = $uploaded_file['url'];
        }
        
        // Insert application into database with better error handling
        global $wpdb;
        
        // First, check if the applications table exists and is properly set up
        $table_name = $wpdb->prefix . 'job_applications';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            wp_send_json_error(array('message' => __('Application system is not properly configured. Please contact support.', 'job-system')));
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'job_id' => $job_id,
                'applicant_name' => $name,
                'applicant_email' => $email,
                'applicant_phone' => $phone,
                'cv_file_path' => $cv_file_path,
                'application_date' => current_time('mysql'),
                'status' => 'pending'
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Log the specific error for debugging
            error_log('Job Application Insert Error: ' . $wpdb->last_error);
            error_log('Attempted Job ID: ' . $job_id);
            error_log('Job exists: ' . ($job ? 'Yes' : 'No'));
            
            // Check if this is a foreign key constraint error
            if (strpos($wpdb->last_error, 'foreign key constraint') !== false) {
                // Try to fix the constraint issue and retry
                $wpdb->query("SET foreign_key_checks = 0");
                
                $retry_result = $wpdb->insert(
                    $table_name,
                    array(
                        'job_id' => $job_id,
                        'applicant_name' => $name,
                        'applicant_email' => $email,
                        'applicant_phone' => $phone,
                        'cv_file_path' => $cv_file_path,
                        'application_date' => current_time('mysql'),
                        'status' => 'pending'
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                
                $wpdb->query("SET foreign_key_checks = 1");
                
                if ($retry_result !== false) {
                    $result = $retry_result;
                }
            }
            
            if ($result === false) {
                wp_send_json_error(array('message' => __('Failed to submit application. Please try again.', 'job-system')));
            }
        }
        
        // Send emails
        $this->send_thank_you_email($job_id, $name, $email);
        $this->send_admin_notification($job_id, $name, $email, $phone);
        
        wp_send_json_success(array('message' => __('Application submitted successfully!', 'job-system')));
    }
    
    /**
     * Handle CV file upload
     */
    private function handle_cv_upload($file) {
        // Check file size (20MB limit)
        $max_size = 20 * 1024 * 1024; // 20MB in bytes
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', __('File size exceeds 20MB limit.', 'job-system'));
        }
        
        // Check file type
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            return new WP_Error('invalid_file_type', __('Invalid file type. Allowed types: PDF, DOC, DOCX, JPG, JPEG, PNG.', 'job-system'));
        }
        
        // Setup upload directory
        $upload_dir = wp_upload_dir();
        $job_uploads_dir = $upload_dir['basedir'] . '/job-applications/';
        
        if (!file_exists($job_uploads_dir)) {
            wp_mkdir_p($job_uploads_dir);
        }
        
        // Generate unique filename
        $filename = time() . '_' . sanitize_file_name($file['name']);
        $file_path = $job_uploads_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return array(
                'path' => $file_path,
                'url' => $upload_dir['baseurl'] . '/job-applications/' . $filename
            );
        } else {
            return new WP_Error('upload_failed', __('Failed to upload file.', 'job-system'));
        }
    }
    
    /**
     * Send thank you email to applicant
     */
    private function send_thank_you_email($job_id, $applicant_name, $applicant_email) {
        $job_title = get_the_title($job_id);
        $company_name = get_bloginfo('name');
        
        $subject = sprintf(__('Thank you for applying to %s', 'job-system'), $job_title);
        
        $message = sprintf(
            __("Dear %s,\n\nThank you for applying to the %s position at %s.\n\nWe have successfully received your application and it will be reviewed by our HR team.\n\nWe will contact you soon if we are interested in your application.\n\nBest regards,\nHR Team\n%s", 'job-system'),
            $applicant_name,
            $job_title,
            $company_name,
            $company_name
        );
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // HTML version of the email
        $html_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Thank You for Applying!</h2>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>Dear <strong>{$applicant_name}</strong>,</p>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    Thank you for applying to the <strong style='color: #007cba;'>{$job_title}</strong> position at <strong>{$company_name}</strong>.
                </p>
                
                <div style='background-color: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007cba;'>
                    <p style='margin: 0; font-size: 16px; color: #333;'>
                        ✅ We have successfully received your application and it will be reviewed by our HR team.
                    </p>
                </div>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    We will contact you soon if we are interested in your application.
                </p>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 14px; color: #777; text-align: center;'>
                    Best regards,<br>
                    <strong>HR Team</strong><br>
                    {$company_name}
                </p>
            </div>
        </div>";
        
        wp_mail($applicant_email, $subject, $html_message, $headers);
    }
    
    /**
     * Send notification email to admin about new application
     */
    private function send_admin_notification($job_id, $applicant_name, $applicant_email, $applicant_phone) {
        $job_title = get_the_title($job_id);
        $admin_email = get_option('admin_email');
        $company_name = get_bloginfo('name');
        $application_time = current_time('Y-m-d H:i:s');
        
        $subject = sprintf(__('New Job Application: %s', 'job-system'), $job_title);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // HTML email for admin
        $html_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px; background: linear-gradient(45deg, #007cba, #00a0d2); color: white; padding: 15px; border-radius: 8px;'>
                    🔔 New Job Application Received
                </h2>
                
                <div style='background-color: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #007cba;'>
                    <h3 style='color: #007cba; margin-top: 0;'>Job Details:</h3>
                    <p style='margin: 5px 0; font-size: 16px;'><strong>Position:</strong> {$job_title}</p>
                    <p style='margin: 5px 0; font-size: 14px; color: #666;'><strong>Application Date:</strong> {$application_time}</p>
                </div>
                
                <div style='background-color: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #333; margin-top: 0;'>Applicant Information:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; width: 30%;'>Name:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>{$applicant_name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Email:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'><a href='mailto:{$applicant_email}' style='color: #007cba;'>{$applicant_email}</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Phone:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>{$applicant_phone}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . admin_url('edit.php?post_type=job&page=job-applications') . "' 
                       style='display: inline-block; background-color: #007cba; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                        View All Applications
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 12px; color: #777; text-align: center;'>
                    This is an automated message from the Job Management System at {$company_name}
                </p>
            </div>
        </div>";
        
        wp_mail($admin_email, $subject, $html_message, $headers);
    }
    
    /**
     * Send application notification email (Legacy function - keeping for compatibility)
     */
    private function send_application_notification($job_id, $applicant_name, $applicant_email) {
        // This function is kept for backward compatibility
        // The new functions above are used instead
        return true;
    }
    
    /**
     * Update application status
     */
    public function update_application_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'job_system_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'job-system')));
        }
        
        $application_id = intval($_POST['application_id']);
        $status = sanitize_text_field($_POST['status']);
        
        // Validate status
        $allowed_statuses = array('pending', 'reviewed', 'accepted', 'rejected');
        if (!in_array($status, $allowed_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'job-system')));
        }
        
        // Update status in database
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'job_applications',
            array('status' => $status),
            array('id' => $application_id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to update status.', 'job-system')));
        }
        
        wp_send_json_success(array('message' => __('Status updated successfully.', 'job-system')));
    }
}
