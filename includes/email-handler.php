<?php
/**
 * Email handler for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Email_Handler {
    
    /**
     * Send application notification
     */
    public function send_application_notification($application, $type = 'submitted') {
        // Check if email notifications are enabled
        if (!get_option('jms_email_notifications', 1)) {
            return false;
        }
        
        switch ($type) {
            case 'submitted':
                return $this->send_submission_confirmation($application);
            case 'approved':
                return $this->send_approval_notification($application);
            case 'rejected':
                return $this->send_rejection_notification($application);
            case 'shortlisted':
                return $this->send_shortlist_notification($application);
            default:
                return false;
        }
    }
    
    /**
     * Send submission confirmation to applicant
     */
    private function send_submission_confirmation($application) {
        $to = $application->email;
        $subject = 'Application Received - ' . $application->job_name;
        
        $message = $this->get_email_template('submission_confirmation', array(
            'applicant_name' => $application->name ?? 'Applicant',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'application_date' => $application->created_at ? date('F j, Y', strtotime($application->created_at)) : date('F j, Y')
        ));
        
        // Send to applicant
        $sent_to_applicant = $this->send_email($to, $subject, $message);
        
        // Send notification to admin
        $this->send_admin_notification($application);
        
        return $sent_to_applicant;
    }

    /**
     * Send notification to admin
     */
    private function send_admin_notification($application) {
        $admin_email = get_option('jms_notification_email', get_option('admin_email'));
        $subject = 'New Job Application - ' . $application->job_name;
        
        $message = $this->get_email_template('admin_notification', array(
            'applicant_name' => $application->name ?? 'Unknown Applicant',
            'applicant_email' => $application->email ?? 'No email provided',
            'applicant_phone' => $application->phone ?? 'No phone provided',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'application_date' => $application->created_at ? date('F j, Y g:i A', strtotime($application->created_at)) : date('F j, Y g:i A'),
            'admin_url' => admin_url('admin.php?page=jms_applications&action=view&id=' . $application->id)
        ));
        
        return $this->send_email($admin_email, $subject, $message);
    }

    /**
     * Get email template
     */
    private function get_email_template($template_name, $variables = array()) {
        $templates = array(
            'submission_confirmation' => '
                <h2>Application Received Successfully</h2>
                <p>Dear {applicant_name},</p>
                <p>Thank you for your interest in the <strong>{job_name}</strong> position in our {department_name} department.</p>
                <p>We have received your application submitted on {application_date} and our hiring team will review it carefully.</p>
                <p>We will contact you if your qualifications match our requirements.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'application_approved' => '
                <h2>Congratulations! Your Application Has Been Approved</h2>
                <p>Dear {applicant_name},</p>
                <p>We are pleased to inform you that your application for the <strong>{job_name}</strong> position has been approved!</p>
                <p>Our HR team will contact you soon to discuss the next steps in the hiring process.</p>
                {admin_notes_section}
                <p>Congratulations and welcome to our team!</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'application_rejected' => '
                <h2>Update on Your Application</h2>
                <p>Dear {applicant_name},</p>
                <p>Thank you for your interest in the <strong>{job_name}</strong> position in our {department_name} department.</p>
                <p>After careful consideration, we have decided to move forward with other candidates whose qualifications more closely match our current needs.</p>
                {admin_notes_section}
                <p>We encourage you to apply for future positions that match your skills and experience.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'application_shortlisted' => '
                <h2>You\'ve Been Shortlisted!</h2>
                <p>Dear {applicant_name},</p>
                <p>Great news! Your application for the <strong>{job_name}</strong> position has been shortlisted.</p>
                <p>Our hiring team was impressed with your qualifications and would like to move forward with the next stage of the interview process.</p>
                {admin_notes_section}
                <p>We will contact you soon with further details.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'admin_notification' => '
                <h2>New Job Application Received</h2>
                <p>A new application has been submitted for the <strong>{job_name}</strong> position.</p>
                <h3>Applicant Details:</h3>
                <ul>
                    <li><strong>Name:</strong> {applicant_name}</li>
                    <li><strong>Email:</strong> {applicant_email}</li>
                    <li><strong>Phone:</strong> {applicant_phone}</li>
                    <li><strong>Position:</strong> {job_name}</li>
                    <li><strong>Department:</strong> {department_name}</li>
                    <li><strong>Applied on:</strong> {application_date}</li>
                </ul>
                <p><a href="{admin_url}" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Application</a></p>
            ',
            
            'approval_notification' => '
                <h2>Congratulations! Your Application Has Been Approved</h2>
                <p>Dear {applicant_name},</p>
                <p>We are pleased to inform you that your application for the <strong>{job_name}</strong> position in our {department_name} department has been approved!</p>
                <p>Our HR team will contact you soon to discuss the next steps in the hiring process.</p>
                {admin_notes_section}
                <p>Congratulations and welcome to our team!</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'rejection_notification' => '
                <h2>Update on Your Application</h2>
                <p>Dear {applicant_name},</p>
                <p>Thank you for your interest in the <strong>{job_name}</strong> position in our {department_name} department.</p>
                <p>After careful consideration, we have decided to move forward with other candidates whose qualifications more closely match our current needs.</p>
                {admin_notes_section}
                <p>We encourage you to apply for future positions that match your skills and experience.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'shortlist_notification' => '
                <h2>Great News! You\'ve Been Shortlisted</h2>
                <p>Dear {applicant_name},</p>
                <p>We are pleased to inform you that your application for the <strong>{job_name}</strong> position in our {department_name} department has been shortlisted!</p>
                <p>You are among our top candidates and we would like to proceed to the next stage of our selection process.</p>
                {admin_notes_section}
                <p>Our HR team will contact you soon with details about the next steps.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            ',
            
            'status_update_notification' => '
                <h2>Application Status Update</h2>
                <p>Dear {applicant_name},</p>
                <p>Your application status for the <strong>{job_name}</strong> position in our {department_name} department has been updated.</p>
                <p><strong>Previous Status:</strong> {old_status}<br>
                <strong>Current Status:</strong> {new_status}</p>
                {admin_notes_section}
                <p>If you have any questions, please don\'t hesitate to contact us.</p>
                <p>Best regards,<br>
                The Hiring Team</p>
            '
        );
        
        if (!isset($templates[$template_name])) {
            return '';
        }
        
        $template = $templates[$template_name];
        
        // Replace variables
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value ?? '', $template);
        }
        
        // Handle admin notes section
        if (isset($variables['admin_notes']) && !empty($variables['admin_notes'])) {
            $notes_section = '<div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0;">
                <strong>Additional Notes:</strong><br>
                ' . nl2br(esc_html($variables['admin_notes'])) . '
            </div>';
            $template = str_replace('{admin_notes_section}', $notes_section, $template);
        } else {
            $template = str_replace('{admin_notes_section}', '', $template);
        }
        
        return $this->wrap_email_template($template);
    }
    
    /**
     * Wrap email content with HTML template
     */
    private function wrap_email_template($content) {
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Job Application Notification</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: #007cba; color: white; padding: 20px; text-align: center; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 24px;">' . esc_html($site_name) . '</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Job Management System</p>
            </div>
            
            <div style="background: white; padding: 30px; border: 1px solid #ddd; border-radius: 5px;">
                ' . $content . '
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <p style="margin: 0; font-size: 14px; color: #666;">
                    This email was sent by <a href="' . esc_url($site_url) . '" style="color: #007cba;">' . esc_html($site_name) . '</a>
                </p>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Send approval notification
     */
    public function send_approval_notification($application) {
        $to = $application->email;
        $subject = 'Application Approved - ' . ($application->job_name ?? 'Job Position');
        
        $message = $this->get_email_template('approval_notification', array(
            'applicant_name' => $application->name ?? 'Applicant',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'admin_notes' => $application->admin_notes ?? ''
        ));
        
        return $this->send_email($to, $subject, $message);
    }
    
    /**
     * Send rejection notification
     */
    public function send_rejection_notification($application) {
        $to = $application->email;
        $subject = 'Application Update - ' . ($application->job_name ?? 'Job Position');
        
        $message = $this->get_email_template('rejection_notification', array(
            'applicant_name' => $application->name ?? 'Applicant',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'admin_notes' => $application->admin_notes ?? ''
        ));
        
        return $this->send_email($to, $subject, $message);
    }
    
    /**
     * Send shortlist notification
     */
    public function send_shortlist_notification($application) {
        $to = $application->email;
        $subject = 'You\'ve been shortlisted - ' . ($application->job_name ?? 'Job Position');
        
        $message = $this->get_email_template('shortlist_notification', array(
            'applicant_name' => $application->name ?? 'Applicant',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'admin_notes' => $application->admin_notes ?? ''
        ));
        
        return $this->send_email($to, $subject, $message);
    }
    
    /**
     * Send general status update notification
     */
    public function send_status_update_notification($application, $old_status, $new_status) {
        $to = $application->email;
        $subject = 'Application Status Update - ' . ($application->job_name ?? 'Job Position');
        
        $message = $this->get_email_template('status_update_notification', array(
            'applicant_name' => $application->name ?? 'Applicant',
            'job_name' => $application->job_name ?? 'Unknown Position',
            'department_name' => $application->department_name ?? 'Unknown Department',
            'old_status' => ucfirst($old_status),
            'new_status' => ucfirst($new_status),
            'admin_notes' => $application->admin_notes ?? ''
        ));
        
        return $this->send_email($to, $subject, $message);
    }
    
    /**
     * Send email using WordPress wp_mail function
     */
    private function send_email($to, $subject, $message) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }
}

