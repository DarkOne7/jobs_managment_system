<?php
/**
 * Frontend functionality for Job System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Job_System_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_head', array($this, 'add_custom_styles'));
        add_filter('the_content', array($this, 'add_apply_button_to_jobs'));
        add_action('wp_footer', array($this, 'add_application_modal'));
        add_action('wp_footer', array($this, 'check_custom_pages_modal'));
        add_shortcode('job_list', array($this, 'job_list_shortcode'));
        add_shortcode('job_departments', array($this, 'job_departments_shortcode'));
    }
    

    /**
     * Add custom styles to head
     */
    public function add_custom_styles() {
        $job_system_page = get_query_var('job_system_page');
        $should_load_styles = (
            is_singular('job') || 
            (isset($_POST['post_content']) && is_string($_POST['post_content']) && (has_shortcode($_POST['post_content'], 'job_list') || has_shortcode($_POST['post_content'], 'job_departments'))) ||
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            (strpos($_SERVER['REQUEST_URI'], '/jobs') !== false) ||
            (strpos($_SERVER['REQUEST_URI'], '/locations') !== false)
        );
        
        if ($should_load_styles) {
            ?>
            <style>
                /* Job Header Styles */
                .job-header {
                    font-family: "Poppins", sans-serif;
                    background: black;
                    color: white;
                    padding: 25px 20px;
                    text-align: left;
                    border-bottom: solid 3px #14A26A;
                    margin: 0 -20px 20px -20px; /* Full width - extends beyond container */
                    width: 100vw;
                    position: relative;
                    left: 50%;
                    right: 50%;
                    margin-left: -50vw;
                    margin-right: -50vw;
                    box-sizing: border-box;
                }
                
                /* Hide WordPress default title */
                .single-job .entry-title,
                .single-job .page-title,
                .post-type-job .entry-title,
                .post-type-job .page-title {
                    display: none !important;
                }
                
                .job-header h1 {
                    margin: 0 0 10px 0;
                    font-size: 2rem;
                    color: white;
                    max-width: 1200px;
                    margin-left: auto;
                    margin-right: auto;
                    padding: 0 0;
                }
                
                .job-header-content {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 0 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    gap: 20px;
                }
                
                .job-header-left {
                    flex: 1;
                }
                
                .job-header-right {
                    flex-shrink: 0;
                    align-self: center;
                }
                
                .job-header-apply-btn {
                    background: transparent;
                    color: white;
                    border: 2px solid white;
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 16px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-decoration: none;
                    display: inline-block;
                    font-family: "Poppins", sans-serif;
                }
                
                .job-header-apply-btn:hover {
                    background: white;
                    color: black;
                    text-decoration: none;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
                }
                
                /* Simple Apply Button */
                .job-apply-button.large {
                    background: #007cba;
                    color: white;
                    border: none;
                    padding: 15px 40px;
                    font-size: 18px;
                    font-weight: 600;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-family: "Poppins", sans-serif;
                    margin: 40px 0;
                }
                
                .job-apply-button.large:hover {
                    background: #005a87;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 124, 186, 0.3);
                }
                
                .job-apply-button.large:disabled {
                    background: #ccc;
                    color: #666;
                    cursor: not-allowed;
                    transform: none;
                    box-shadow: none;
                }
                
                /* Content spacing improvements */
                .single-job .entry-content,
                .post-type-job .entry-content {
                    line-height: 1.8;
                    font-size: 16px;
                    color: #333;
                    margin-bottom: 0;
                }
                
                .single-job .entry-content h2,
                .single-job .entry-content h3,
                .post-type-job .entry-content h2,
                .post-type-job .entry-content h3 {
                    color: #2c3e50;
                    margin-top: 2rem;
                    margin-bottom: 1rem;
                }
                
                .single-job .entry-content ul,
                .single-job .entry-content ol,
                .post-type-job .entry-content ul,
                .post-type-job .entry-content ol {
                    margin: 1.5rem 0;
                    padding-left: 2rem;
                }
                
                .single-job .entry-content li,
                .post-type-job .entry-content li {
                    margin-bottom: 0.5rem;
                }
                
                .job-header__subtitle {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 30px; /* Gap between location and work type sections */
                    margin-top: 10px;
                    font-size: 16px;
                    max-width: none; /* Remove max-width constraint */
                    margin-left: 0;
                    margin-right: 0;
                    padding: 0;
                }
                
                .location-section,
                .work-type-section {
                    display: flex;
                    align-items: center;
                }
                
                .location-section i,
                .work-type-section i {
                    margin-right: 8px;
                }
                
                /* Responsive Design for Header */
                @media (max-width: 768px) {
                    .job-header {
                        padding: 20px 15px;
                    }
                    
                    .job-header-content {
                        flex-direction: column;
                        align-items: stretch;
                        gap: 15px;
                        padding: 0 15px;
                    }
                    
                    .job-header-right {
                        align-self: stretch;
                    }
                    
                    .job-header-apply-btn {
                        width: 100%;
                        text-align: center;
                    }
                    
                    .job-header h1 {
                        font-size: 1.8rem;
                        margin-bottom: 8px;
                        padding: 0;
                    }
                    
                    .job-header__subtitle {
                        flex-direction: column;
                        gap: 15px;
                        font-size: 14px;
                        padding: 0;
                    }
                    
                    .job-apply-button.large {
                        width: 100%;
                        padding: 12px 30px;
                        font-size: 16px;
                        margin: 30px 0;
                    }
                }
                
                @media (max-width: 480px) {
                    .job-header {
                        padding: 15px 10px;
                    }
                    
                    .job-header-content {
                        padding: 0 10px;
                        gap: 12px;
                    }
                    
                    .job-header h1 {
                        font-size: 1.5rem;
                        padding: 0;
                    }
                    
                    .job-header__subtitle {
                        gap: 12px;
                        font-size: 13px;
                        padding: 0;
                    }
                    
                    .job-header-apply-btn {
                        padding: 10px 20px;
                        font-size: 14px;
                    }
                    
                    .job-apply-button.large {
                        padding: 10px 25px;
                        font-size: 15px;
                        margin: 20px 0;
                        width: 100%;
                    }
                }

                .job-apply-button {
                    background-color: #00a0d2;
                    color: white;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                    margin: 20px 0;
                    display: inline-block;
                    text-decoration: none;
                }
                
                .job-apply-button:hover {
                    background-color: #005177;
                    color: white;
                    text-decoration: none;
                }
                
                .job-apply-button.loading {
                    opacity: 0.7;
                    pointer-events: none;
                }
                
                /* Form Messages */
                .form-message {
                    padding: 12px;
                    border-radius: 4px;
                    margin-bottom: 15px;
                    font-weight: 500;
                }
                
                .form-message.message-success {
                    background-color: #d1edff;
                    color: #0c5460;
                    border-left: 4px solid #10b981;
                }
                
                .form-message.message-error {
                    background-color: #fef2f2;
                    color: #991b1b;
                    border-left: 4px solid #dc2626;
                }
                
                .form-message.message-info {
                    background-color: #eff6ff;
                    color: #1e40af;
                    border-left: 4px solid #3b82f6;
                }
                
                /* Form Input Errors */
                .job-application-form input.error,
                .job-application-form textarea.error {
                    border-color: #dc2626;
                    box-shadow: 0 0 0 1px #dc2626;
                }
                
                .job-modal {
                    display: none;
                    position: fixed;
                    z-index: 9999;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0,0,0,0.5);
                }
                
                .job-modal-content {
                    background-color: #fefefe;
                    margin: 5% auto;
                    padding: 0;
                    border-radius: 8px;
                    width: 90%;
                    max-width: 870px;
                    position: relative;
                    max-height: 90vh;
                    overflow-y: auto;
                }
                
                .job-modal-header {
                    padding: 20px 24px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: #ffffff;
                    border-radius: 12px 12px 0 0;
                }
                
                .job-modal-header h2 {
                    font-size: 18px;
                    font-weight: 600;
                    color: #1f2937;
                    margin: 0;
                    font-family: "Poppins", sans-serif;
                }
                
                .job-title-separator {
                    color: #9ca3af;
                    margin: 0 8px;
                }
                
                .job-modal-body {
                    padding: 24px;
                    background: #ffffff;
                    border-radius: 0 0 12px 12px;
                }
                
                .job-modal-close {
                    color: #6b7280;
                    font-size: 24px;
                    font-weight: bold;
                    cursor: pointer;
                    background: none;
                    border: none;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                }
                
                .job-modal-close:hover {
                    background: white;
                    color: #1f2937;
                }
                
                /* Modern Form Styles */
                .job-application-form {
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                }
                
                .form-row {
                    display: flex;
                    gap: 16px;
                }
                
                .form-group {
                    display: flex;
                    flex-direction: column;
                }
                
                .form-group.half-width {
                    flex: 1;
                }
                
                .job-application-form label {
                    font-weight: 500;
                    margin-bottom: 8px;
                    color: #374151;
                    font-size: 14px;
                    font-family: "Poppins", sans-serif;
                }
                
                .job-application-form input {
                    padding: 12px 16px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    font-size: 14px;
                    color: #374151;
                    transition: all 0.2s ease;
                    font-family: "Poppins", sans-serif;
                }
                
                .job-application-form input:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                }
                
                .job-application-form input::placeholder {
                    color: #9ca3af;
                }
                
                /* Modern File Upload */
                .file-upload-modern {
                    border: 2px dashed #92E3A9;
                    border-radius: 12px;
                    padding: 24px;
                    background: #F3FAF8;
                    display: flex;
                    align-items: flex-start;
                    gap: 16px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    text-align: left;
                }
                
                .file-upload-modern:hover {
                    border-color: #10b981;
                    background: #f0fdf4;
                }
                
                .file-upload-icon {
                    flex-shrink: 0;
                    width: 48px;
                    height: 48px;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .file-upload-content {
                    flex: 1;
                    text-align: left;
                }
                
                .file-upload-text {
                    text-align: left;
                }
                
                .file-upload-text p {
                    margin: 0 0 8px 0;
                    font-size: 13px;
                    line-height: 1.4;
                    color: #4b5563;
                    text-align: left;
                }
                
                .file-upload-text p:first-child {
                    font-weight: 500;
                    color: #374151;
                    font-size: 14px;
                    margin-bottom: 12px;
                }
                
                .file-upload-text p:last-child {
                    margin-bottom: 0;
                }
                
                /* Modern Submit Button */
                .submit-button-modern {
                    background: #000000;
                    color: white;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 16px;
                    font-weight: 600;
                    font-family: "Poppins", sans-serif;
                    transition: all 0.2s ease;
                    width: 100%;
                    margin-top: 24px;
                }
                
                .submit-button-modern:hover {
                    background: #1f2937;
                    transform: translateY(-1px);
                }
                
                /* Mobile Responsive */
                @media (max-width: 768px) {
                    .job-modal-content {
                        margin: 20px;
                        max-width: none;
                        width: calc(100% - 40px);
                    }
                    
                    .job-modal-header {
                        padding: 16px 20px;
                    }
                    
                    .job-modal-header h2 {
                        font-size: 16px;
                    }
                    
                    .job-modal-body {
                        padding: 20px;
                    }
                    
                    .form-row {
                        flex-direction: column;
                        gap: 20px;
                    }
                    
                    .form-group.half-width {
                        flex: none;
                    }
                    
                    .file-upload-modern {
                        flex-direction: column;
                        text-align: left;
                        gap: 12px;
                        padding: 20px;
                    }
                    
                    .file-upload-icon {
                        margin: 0;
                        align-self: flex-start;
                    }
                    
                    .file-upload-content {
                        text-align: left;
                    }
                    
                    .file-upload-text {
                        text-align: left;
                    }
                    
                    .file-upload-text p {
                        font-size: 12px;
                        text-align: left;
                    }
                }
                
                .file-upload-info {
                    font-size: 12px;
                    color: #666;
                    margin-top: 10px;
                }
                
                .job-departments-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                
                .job-department-card {
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    background: white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .job-department-card h3 {
                    margin: 0 0 10px 0;
                    color: white;
                    padding: 10px;
                    border-radius: 4px;
                }
                
                .job-department-card .job-count {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 15px;
                }
                
                .job-department-card .view-jobs-btn {
                    background: transparent;
                    border: 2px solid #00a0d2;
                    color: #00a0d2;
                    padding: 8px 16px;
                    border-radius: 4px;
                    text-decoration: none;
                    display: inline-block;
                }

                .job-header-apply-btn:focus {
                    background-color: white !important;
                    color: black !important;
                }

                .job-department-card .view-jobs-btn:hover {
                    background: #00a0d2;
                    color: white;
                    text-decoration: none;
                }
                
                .job-list-container {
                    margin: 20px 0;
                }
                
                .job-item {
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    margin-bottom: 20px;
                    background: white;
                }
                
                .job-item h3 {
                    margin: 0 0 10px 0;
                    color: #333;
                }
                
                .job-item .job-meta {
                    display: flex;
                    gap: 15px;
                    margin-bottom: 15px;
                    flex-wrap: wrap;
                }
                
                .job-item .job-meta span {
                    font-size: 14px;
                    color: #666;
                }
                
                .job-item .job-department-tag {
                    padding: 4px 8px;
                    border-radius: 3px;
                    color: white;
                    font-size: 12px;
                    font-weight: bold;
                }
                
                .job-item .job-excerpt {
                    margin: 15px 0;
                    color: #666;
                }
                
                @media (max-width: 768px) {
                    .job-modal-content {
                        width: 95%;
                        margin: 10% auto;
                    }
                    
                    .job-departments-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .job-item .job-meta {
                        flex-direction: column;
                        gap: 5px;
                    }
                }
            </style>
            <?php
        }
    }
    
    /**
     * Add apply button to job posts
     */
    public function add_apply_button_to_jobs($content) {
        if (is_singular('job')) {
            $job_id = get_the_ID();
            $deadline = get_post_meta($job_id, '_application_deadline', true);
            $is_expired = false;
            
            if ($deadline) {
                $deadline_timestamp = strtotime($deadline);
                $is_expired = time() > $deadline_timestamp;
            }
            
            // Get job details for header
            $job_title = get_the_title($job_id);
            $departments = get_the_terms($job_id, 'job_department');
            $locations = get_the_terms($job_id, 'job_location');
            $job_location = get_post_meta($job_id, '_job_location', true);
            $work_type = get_post_meta($job_id, '_work_type', true);
            
            // Build header with Apply button
            $header = '<div class="job-header">';
            $header .= '<div class="job-header-content">';
            $header .= '<div class="job-header-left">';
            $header .= '<h1>' . esc_html($job_title) . '</h1>';
            $header .= '<div class="job-header__subtitle">';
            
            // Location section
            $header .= '<div class="location-section">';
            $header .= '<i class="fa-light fa-location-dot"></i>';
            $header .= '<span>Office location: ';
            if (!empty($locations) && !is_wp_error($locations)) {
                $header .= esc_html($locations[0]->name);
            } elseif ($job_location) {
                $header .= esc_html($job_location);
            } else {
                $header .= 'Not specified';
            }
            $header .= '</span>';
            $header .= '</div>';
            
            // Work type section
            $header .= '<div class="work-type-section">';
            $header .= '<i class="fa-light fa-file-lines"></i>';
            $header .= '<span>Work type: ' . ($work_type ? esc_html(ucfirst($work_type)) : 'Not specified') . '</span>';
            $header .= '</div>';
            
            $header .= '</div>'; // Close subtitle
            $header .= '</div>'; // Close left
            
            // Apply button section
            $header .= '<div class="job-header-right">';
            if (!$is_expired) {
                $header .= '<button class="job-header-apply-btn" onclick="openJobModal(' . $job_id . ')">Apply Now</button>';
            } else {
                $header .= '<button class="job-header-apply-btn" disabled style="opacity: 0.5; cursor: not-allowed;">Application Closed</button>';
            }
            $header .= '</div>'; // Close right
            
            $header .= '</div>'; // Close content
            $header .= '</div>'; // Close header
            
            // Add simple apply button after content
            $apply_button_section = '<div style="text-align: left; margin: 40px 0;">';
            if (!$is_expired) {
                $apply_button_section .= '<button class="job-apply-button large" onclick="openJobModal(' . $job_id . ')" style="background: #007cba; color: white; border: none; padding: 15px 40px; font-size: 18px; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; font-family: \'Poppins\', sans-serif;">Apply Now</button>';
            } else {
                $apply_button_section .= '<button class="job-apply-button large" disabled style="background: #ccc; color: #666; border: none; padding: 15px 40px; font-size: 18px; font-weight: 600; border-radius: 8px; cursor: not-allowed; font-family: \'Poppins\', sans-serif;">Application Closed</button>';
            }
            $apply_button_section .= '</div>';
            
            // Return header + content + apply section
            $content = $header . $content . $apply_button_section;
        }
        
        return $content;
    }
    
    /**
     * Add application modal to footer
     */
    public function add_application_modal() {
    $current_post = get_post();
    $has_job_list_shortcode = false;
    if ($current_post && isset($current_post->post_content) && is_string($current_post->post_content)) {
        $has_job_list_shortcode = has_shortcode($current_post->post_content, 'job_list');
    }
    if (is_singular('job') || $has_job_list_shortcode) {
            ?>
            <div id="jobApplicationModal" class="job-modal">
                <div class="job-modal-content">
                    <div class="job-modal-header">
                        <h2><?php _e('Apply now', 'job-system'); ?> <span class="job-title-separator">---></span> <span id="modal-job-title"></span></h2>
                        <button class="job-modal-close" onclick="closeJobModal()">&times;</button>
                    </div>
                    <div class="job-modal-body">
                        <form id="jobApplicationForm" class="job-application-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="applicant_name"><?php _e('Full Name', 'job-system'); ?></label>
                                <input type="text" id="applicant_name" name="applicant_name" required 
                                       placeholder="Enter your full name" />
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group half-width">
                                    <label for="applicant_email"><?php _e('Email', 'job-system'); ?></label>
                                    <input type="email" id="applicant_email" name="applicant_email" required 
                                           placeholder="Enter Your Email" />
                                </div>
                                
                                <div class="form-group half-width">
                                    <label for="applicant_phone"><?php _e('Phone Number', 'job-system'); ?></label>
                                    <input type="tel" id="applicant_phone" name="applicant_phone" 
                                           placeholder="Enter Your Phone Number" />
                                </div>
                            </div>
                            
                            <div class="form-group" style="font-family: 'Poppins', sans-serif;">
                                <label for="cv_file"><?php _e('Upload your Resume', 'job-system'); ?></label>
                                <div class="file-upload-modern">
                                    <div class="file-upload-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M14 2V8H20" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 13H8" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M16 17H8" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M10 9H9H8" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;" />
                                    <div class="file-upload-content">
                                        <div class="file-upload-text">
                                            <p><strong>Upload your Resume</strong></p>
                                            <p>• You can only upload <strong>1 file</strong>, Max file size is <strong>20 MB</strong></p>
                                            <p>• Ensure that your receipt is clear and legible, with no blurry or unclear sections.</p>
                                            <p>• You can only upload these file formats: .JPG, .PNG, .PDF, .JPEG.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="job_id" name="job_id" />
                        </form>
                        
                        <button type="submit" form="jobApplicationForm" class="submit-button-modern"><?php _e('Submit', 'job-system'); ?></button>
                    </div>
                </div>
            </div>
            
            <script>
            function openJobModal(jobId) {
                document.getElementById('job_id').value = jobId;
                
                // Get job title and set it in modal
                var jobTitle = document.querySelector('.job-header h1').textContent || 
                              document.querySelector('.entry-title').textContent || 
                              'Job Position';
                document.getElementById('modal-job-title').textContent = jobTitle;
                
                // Smooth scroll to top when modal opens
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                document.getElementById('jobApplicationModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                // Focus on first input for better UX
                setTimeout(function() {
                    document.getElementById('applicant_name').focus();
                }, 300);
            }
            
            function closeJobModal() {
                document.getElementById('jobApplicationModal').style.display = 'none';
                document.body.style.overflow = 'auto';
                document.getElementById('jobApplicationForm').reset();
                // Reset file upload text
                resetFileUploadText();
            }
            
            function resetFileUploadText() {
                var fileUploadText = document.querySelector('.file-upload-text p:first-child');
                if (fileUploadText) {
                    fileUploadText.innerHTML = 'Select CV or Resume file';
                }
            }
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                var modal = document.getElementById('jobApplicationModal');
                if (event.target == modal) {
                    closeJobModal();
                }
            }
            
            // Hide WordPress default title on single job pages
            document.addEventListener('DOMContentLoaded', function() {
                if (document.body.classList.contains('single-job') || 
                    document.body.classList.contains('post-type-job')) {
                    var titles = document.querySelectorAll('.entry-title, .page-title, h1.entry-title');
                    titles.forEach(function(title) {
                        if (title.textContent !== '' && !title.closest('.job-header')) {
                            title.style.display = 'none';
                        }
                    });
                }
                
                // Add smooth hover effects to apply buttons
                var applyButtons = document.querySelectorAll('.job-apply-button, .job-header-apply-btn');
                applyButtons.forEach(function(button) {
                    button.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });
                    button.addEventListener('mouseleave', function() {
                        if (!this.disabled) {
                            this.style.transform = 'translateY(0)';
                        }
                    });
                });
                
                // File upload area click handler
                var fileUploadArea = document.querySelector('.file-upload-modern');
                if (fileUploadArea) {
                    fileUploadArea.addEventListener('click', function() {
                        document.getElementById('cv_file').click();
                    });
                }
                
                // File input change handler
                var fileInput = document.getElementById('cv_file');
                if (fileInput) {
                    fileInput.addEventListener('change', function() {
                        var fileName = this.files[0]?.name;
                        var fileUploadText = document.querySelector('.file-upload-text p:first-child');
                        if (fileName && fileUploadText) {
                            fileUploadText.innerHTML = 'Selected: <strong>' + fileName + '</strong>';
                        }
                    });
                }
                
                // Form submission with AJAX
                var form = document.getElementById('jobApplicationForm');
                var submitBtn = document.querySelector('.submit-button-modern');
                
                if (form && submitBtn) {
                    submitBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Basic validation
                        var name = document.getElementById('applicant_name').value.trim();
                        var email = document.getElementById('applicant_email').value.trim();
                        var jobId = document.getElementById('job_id').value;
                        
                        if (!name || !email || !jobId) {
                            alert('Please fill in all required fields');
                            return;
                        }
                        
                        var formData = new FormData(form);
                        formData.append('action', 'submit_job_application');
                        formData.append('nonce', '<?php echo wp_create_nonce("job_application_nonce"); ?>');
                        
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Submitting...';
                        
                        console.log('Submitting application for job ID:', jobId);
                        
                        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.text();
                        })
                        .then(text => {
                            console.log('Raw response:', text);
                            try {
                                const data = JSON.parse(text);
                                console.log('Parsed response:', data);
                                if (data.success) {
                                    alert('Your request has been submitted successfully!');
                                    closeJobModal();
                                } else {
                                    alert('Error: ' + (data.data?.message || data.data || 'Send to Developer This Error'));
                                }
                            } catch (e) {
                                console.error('JSON parse error:', e);
                                console.log('Response text:', text);
                                alert('Response processing error: ' + text.substring(0, 100));
                            }
                        })
                        .catch(error => {
                            console.error('Network error:', error);
                            alert('An error occurred. Please try again.');
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Submit';
                        });
                    });
                }
            });
            </script>
            <?php
        }
    }
    
    /**
     * Render job header
     */
    public function render_job_header($job_id, $custom_title = null) {
        $job_title = $custom_title ? $custom_title : get_the_title($job_id);
        $departments = get_the_terms($job_id, 'job_department');
        $locations = get_the_terms($job_id, 'job_location');
        $job_location = get_post_meta($job_id, '_job_location', true);
        $work_type = get_post_meta($job_id, '_work_type', true);
        
        // Check if application deadline has passed
        $deadline = get_post_meta($job_id, '_application_deadline', true);
        $is_expired = false;
        if ($deadline) {
            $is_expired = time() > strtotime($deadline);
        }
        
        ob_start();
        ?>
        <div class="job-header">
            <div class="job-header-content">
                <div class="job-header-left">
                    <h1><?php echo esc_html($job_title); ?></h1>
                    <div class="job-header__subtitle">
                        <div class="location-section">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>Office location: 
                                <?php 
                                if (!empty($locations) && !is_wp_error($locations)) {
                                    echo esc_html($locations[0]->name);
                                } elseif ($job_location) {
                                    echo esc_html($job_location);
                                } else {
                                    echo 'Not specified';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="work-type-section">
                            <i class="fa-solid fa-briefcase"></i>
                            <span>Work type: <?php echo $work_type ? esc_html(ucfirst($work_type)) : 'Not specified'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="job-header-right">
                    <?php if (!$is_expired): ?>
                        <button class="job-header-apply-btn" onclick="openJobModal(<?php echo $job_id; ?>)">
                            Apply Now
                        </button>
                    <?php else: ?>
                        <button class="job-header-apply-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                            Application Closed
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Job list shortcode
     */
    public function job_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'department' => '',
            'limit' => -1,
            'show_excerpt' => 'true'
        ), $atts);
        
        $args = array(
            'post_type' => 'job',
            'posts_per_page' => intval($atts['limit']),
            'post_status' => 'publish'
        );
        
        if (!empty($atts['department'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'job_department',
                    'field' => 'slug',
                    'terms' => $atts['department']
                )
            );
        }
        
        $jobs = get_posts($args);
        
        if (empty($jobs)) {
            return '<p>' . __('No jobs found.', 'job-system') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="job-list-container">
            <?php foreach ($jobs as $job): ?>
                <?php
                $departments = get_the_terms($job->ID, 'job_department');
                $location = get_post_meta($job->ID, '_job_location', true);
                $work_type = get_post_meta($job->ID, '_work_type', true);
                $deadline = get_post_meta($job->ID, '_application_deadline', true);
                $is_expired = $deadline && time() > strtotime($deadline);
                ?>
                <div class="job-item">
                    <h3>
                        <a href="<?php echo get_permalink($job->ID); ?>"><?php echo esc_html($job->post_title); ?></a>
                    </h3>
                    
                    <div class="job-meta">
                        <?php if ($location): ?>
                            <span><strong><?php _e('Location:', 'job-system'); ?></strong> <?php echo esc_html($location); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($work_type): ?>
                            <span><strong><?php _e('Type:', 'job-system'); ?></strong> <?php echo esc_html(ucfirst($work_type)); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($departments && !is_wp_error($departments)): ?>
                            <span>
                                <?php foreach ($departments as $department): ?>
                                    <?php
                                    $color = get_term_meta($department->term_id, 'department_color', true);
                                    $style = $color ? 'background-color: ' . esc_attr($color) . ';' : 'background-color: #00a0d2;';
                                    ?>
                                    <span class="job-department-tag" style="<?php echo $style; ?>">
                                        <?php echo esc_html($department->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($atts['show_excerpt'] === 'true'): ?>
                        <div class="job-excerpt">
                            <?php echo wp_trim_words(get_the_excerpt($job->ID), 30); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="job-actions">
                        <?php if (!$is_expired): ?>
                            <button class="job-apply-button" onclick="openJobModal(<?php echo $job->ID; ?>)">
                                <?php _e('Apply Now', 'job-system'); ?>
                            </button>
                        <?php else: ?>
                            <span style="color: red;"><?php _e('Application Closed', 'job-system'); ?></span>
                        <?php endif; ?>
                        
                        <a href="<?php echo get_permalink($job->ID); ?>" class="view-details-btn">
                            <?php _e('View Details', 'job-system'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Job departments shortcode
     */
    public function job_departments_shortcode($atts) {
        // Check if we're showing a specific department's jobs
        if (isset($_GET['department'])) {
            return $this->render_department_jobs();
        }
        
        // Include the departments page template
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/departments-page.php';
        return ob_get_clean();
    }
    
    /**
     * Render jobs for a specific department
     */
    private function render_department_jobs() {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/department-jobs.php';
        return ob_get_clean();
    }
    
    /**
     * Check if we need to show modal on custom pages
     */
    public function check_custom_pages_modal() {
        $job_system_page = get_query_var('job_system_page');
        if ($job_system_page === 'departments' || $job_system_page === 'department_jobs' || 
            $job_system_page === 'locations' || $job_system_page === 'location_jobs' || 
            strpos($_SERVER['REQUEST_URI'], '/jobs') !== false || strpos($_SERVER['REQUEST_URI'], '/locations') !== false) {
            $this->add_application_modal();
        }
    }
}
