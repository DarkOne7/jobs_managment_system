<?php
/**
 * Job System Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Shortcodes {
    
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_assets'));
        add_action('wp_ajax_jms_search_jobs', array($this, 'ajax_search_jobs'));
        add_action('wp_ajax_nopriv_jms_search_jobs', array($this, 'ajax_search_jobs'));
        add_action('wp_ajax_jms_get_initial_jobs', array($this, 'ajax_get_initial_jobs'));
        add_action('wp_ajax_nopriv_jms_get_initial_jobs', array($this, 'ajax_get_initial_jobs'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('job_application_form', array($this, 'job_application_form_shortcode'));
    }
    
    /**
     * Enqueue assets for shortcodes
     */
    public function enqueue_shortcode_assets() {
        // Only enqueue if shortcode is used on the page
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'job_application_form')) {
            // Only enqueue jQuery as we have inline CSS and JS in the shortcode
            wp_enqueue_script('jquery');
        }
    }
    
    /**
     * Job Application Form Shortcode
     */
    public function job_application_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => 'Apply for a Job',
            'subtitle' => 'Find your perfect opportunity',
            'show_jobs' => 'true'
        ), $atts);
        
        $active_jobs = JMS_DB_Helper::get_jobs(array('status' => 'active'));
        
        ob_start();
        ?>
            <div class="jms-shortcode-container">
                <div class="jms-form-wrapper">
                
                <div class="jms-form-body">
                        <div id="jms-success-message" class="jms-success-message">
                            <i class="fas fa-check-circle"></i> Your application has been submitted successfully! We will contact you soon.
                        </div>
                        <form id="jms-shortcode-application-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="jms_submit_application">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('jms_nonce'); ?>">
                            
                            <div class="jms-form-row">
                                <div class="jms-form-group">
                                    <label for="jms_applicant_name">Name</label>
                                    <input type="text" id="jms_applicant_name" name="name" placeholder="Enter your name" required>
                                </div>
                                <div class="jms-form-group">
                                    <label for="jms_applicant_email">Email</label>
                                    <input type="email" id="jms_applicant_email" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="jms-form-row">
                                <div class="jms-form-group">
                                    <label for="jms_applicant_phone">Phone Number</label>
                                    <input type="tel" id="jms_applicant_phone" name="phone" placeholder="Enter your phone number" required>
                                </div>
                                <div class="jms-form-group">
                                    <label for="jms_job_role">Job Role</label>
                                    <div class="jms-job-search-wrapper">
                                        <input type="text" 
                                               id="jms_job_role" 
                                               name="job_search" 
                                               placeholder="Search for jobs..." 
                                               autocomplete="off"
                                               required>
                                        <input type="hidden" id="jms_selected_job_id" name="job_id">
                                        <div class="jms-job-dropdown" id="jms_job_dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="jms-form-group">
                                <div class="jms-upload-box">
                                    <input class="jms-upload-input" type="file" id="jms_cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                                    <div class="jms-upload-inner">
                                        <div class="jms-upload-icon">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        <div class="jms-upload-content">
                                            <div class="jms-upload-title">Upload your Resume</div>
                                            <ul class="jms-upload-guidelines">
                                                <li>You can only upload <strong>1 file</strong>, Max file size is <strong>20 MB</strong></li>
                                                <li>Ensure that your receipt is clear and legible, with no blurry or unclear sections.</li>
                                                <li>You can only upload these file formats: JPG, PNG, PDF, JPEG.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="jms-submit-btn" id="jms-shortcode-submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .jms-page-section {
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .jms-hero-section {
            padding: 60px 20px 40px;
            text-align: left;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .jms-hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 16px 0;
            line-height: 1.2;
        }
        
        .jms-highlight {
            color: #10b981;
        }
        
        .jms-hero-content p {
            font-size: 1.1rem;
            color: #374151;
            margin: 0;
            font-weight: 400;
        }
        
        .jms-shortcode-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .jms-form-wrapper {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 40px;
            margin-top: 40px;
        }
        
        .jms-form-body {
            padding: 0;
        }
        
        .jms-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .jms-form-group {
            margin-bottom: 24px;
        }
        
        .jms-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .jms-form-group input,
        .jms-form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            background: #fff;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .jms-form-group input:focus,
        .jms-form-group select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .jms-job-search-wrapper {
            position: relative;
        }
        
        .jms-job-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #d1d5db;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 280px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .jms-job-option {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
            transition: background-color 0.15s ease;
        }
        
        .jms-job-option:hover {
            background: #f9fafb;
            color: #10b981;
        }
        
        .jms-job-option:last-child {
            border-bottom: none;
        }
        
        .jms-job-option.selected {
            background: #ecfdf5;
            color: #10b981;
            font-weight: 500;
        }
        
        .jms-upload-box {
            position: relative;
            border: 2px dashed #10b981;
            background: #14A26A0D;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .jms-upload-box:hover {
            border-color: #059669;
            background:rgba(17, 135, 88, 0.05);
        }   
        
        .jms-upload-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .jms-upload-inner {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        
        .jms-upload-icon {
            width: 48px;
            height: 48px;
            background: #10b981;
            border-radius: 8px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .jms-upload-content {
            flex: 1;
        }
        
        .jms-upload-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .jms-upload-guidelines {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 14px;
            color: #10b981;
            line-height: 1.5;
        }
        
        .jms-upload-guidelines li {
            margin: 4px 0;
            position: relative;
            padding-left: 8px;
        }
        
        .jms-upload-guidelines li:before {
            content: "•";
            position: absolute;
            left: 0;
        }
        
        .jms-submit-btn {
            width: 100%;
            margin: 32px auto 0;
            display: block;
            background: #000;
            color: #fff;
            padding: 16px 24px;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .jms-submit-btn:hover {
            background: #1f2937;
            transform: translateY(-1px);
        }
        
        .jms-submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .jms-success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: none;
        }
        
        .jms-success-message.show {
            display: block;
        }
        
        @media (max-width: 768px) {
            .jms-hero-content h1 {
                font-size: 2rem;
            }
            
            .jms-hero-section {
                padding: 40px 20px 30px;
                text-align: center;
            }
            
            .jms-form-row {
                grid-template-columns: 1fr;
            }
            
            .jms-form-wrapper {
                padding: 30px 20px;
            }
            
            .jms-shortcode-container {
                padding: 0 15px 40px;
            }
        }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jms-shortcode-application-form');
            const submitBtn = document.getElementById('jms-shortcode-submit');
            const jobInput = document.getElementById('jms_job_role');
            const jobDropdown = document.getElementById('jms_job_dropdown');
            const selectedJobId = document.getElementById('jms_selected_job_id');
            const cvFileInput = document.getElementById('jms_cv_file');
            const successMessage = document.getElementById('jms-success-message');
            
            let searchTimeout;
            let jobs = [];
            
            // Job search functionality
            if (jobInput) {
                // Load initial jobs on focus
                jobInput.addEventListener('focus', function() {
                    if (jobs.length === 0) {
                        // Load initial 8 jobs
                        loadInitialJobs();
                    } else {
                        if (this.value.length === 0 || this.value.length >= 2) {
                            jobDropdown.style.display = 'block';
                        }
                    }
                });
                
                jobInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length === 0) {
                        // Show initial jobs when empty
                        if (jobs.length > 0) {
                            displayJobOptions(jobs);
                        }
                        return;
                    }
                    
                    if (query.length < 2) {
                        jobDropdown.style.display = 'none';
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        searchJobs(query);
                    }, 300);
                });
                
                document.addEventListener('click', function(e) {
                    if (!jobInput.contains(e.target) && !jobDropdown.contains(e.target)) {
                        jobDropdown.style.display = 'none';
                    }
                });
            }
            
            function loadInitialJobs() {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=jms_get_initial_jobs&nonce=' + encodeURIComponent('<?php echo wp_create_nonce('jms_shortcode_nonce'); ?>')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        jobs = data.data;
                        displayJobOptions(jobs);
                    }
                })
                .catch(error => {
                    console.error('Load initial jobs error:', error);
                });
            }
            
            function searchJobs(query) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=jms_search_jobs&query=' + encodeURIComponent(query) + '&nonce=' + encodeURIComponent('<?php echo wp_create_nonce('jms_shortcode_nonce'); ?>')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        jobs = data.data;
                        displayJobOptions(jobs);
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
            }
            
            function displayJobOptions(jobsList) {
                jobDropdown.innerHTML = '';
                
                if (jobsList.length === 0) {
                    jobDropdown.innerHTML = '<div class="jms-job-option">No jobs found</div>';
                } else {
                    jobsList.forEach(function(job) {
                        const option = document.createElement('div');
                        option.className = 'jms-job-option';
                        option.textContent = job.name;
                        option.addEventListener('click', function() {
                            selectJob(job);
                        });
                        jobDropdown.appendChild(option);
                    });
                }
                
                jobDropdown.style.display = 'block';
            }
            
            function selectJob(job) {
                jobInput.value = job.name;
                selectedJobId.value = job.id;
                jobDropdown.style.display = 'none';
            }
            
            // Handle file upload title change
            if (cvFileInput) {
                cvFileInput.addEventListener('change', function() {
                    const uploadTitle = document.querySelector('.jms-upload-title');
                    if (this.files && this.files[0] && uploadTitle) {
                        uploadTitle.textContent = 'File Name : ' + this.files[0].name;
                    } else if (uploadTitle) {
                        uploadTitle.textContent = 'Upload your Resume';
                    }
                });
            }
            
            // Handle form submission
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    if (!selectedJobId.value) {
                        alert('Please select a job from the dropdown.');
                        return;
                    }
                    
                    let wasSuccessful = false;
                    const originalHtml = submitBtn.innerHTML;
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                    
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json().catch(() => null);
                        
                        if (response.ok && data && (data.success || data.status === 'success')) {
                            wasSuccessful = true;
                            
                            // Show success message
                            if (successMessage) {
                                successMessage.classList.add('show');
                                form.style.display = 'none';
                            }
                            
                            // Reset form after 5 seconds
                            setTimeout(function() {
                                if (successMessage) {
                                    successMessage.classList.remove('show');
                                    form.style.display = 'block';
                                }
                                form.reset();
                                selectedJobId.value = '';
                                if (document.querySelector('.jms-upload-title')) {
                                    document.querySelector('.jms-upload-title').textContent = 'Upload your Resume';
                                }
                                submitBtn.innerHTML = 'Submit Application';
                                submitBtn.disabled = false;
                            }, 5000);
                        } else {
                            alert((data && (data.message || data.error)) || 'Failed to submit. Please try again.');
                        }
                    } catch (error) {
                        alert('Network error. Please try again.');
                    } finally {
                        if (!wasSuccessful) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHtml;
                        }
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for job search
     */
    public function ajax_search_jobs() {
        if (!wp_verify_nonce($_POST['nonce'], 'jms_shortcode_nonce')) {
            wp_die('Security check failed');
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        if (strlen($query) < 2) {
            wp_send_json_error('Query too short');
        }
        
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        
        $sql = "SELECT id, name FROM $jobs_table 
                WHERE status = 'active' 
                AND (name LIKE %s OR description LIKE %s)
                ORDER BY name ASC
                LIMIT 10";
        
        $search_term = '%' . $wpdb->esc_like($query) . '%';
        $jobs = $wpdb->get_results($wpdb->prepare($sql, $search_term, $search_term));
        
        wp_send_json_success($jobs);
    }
    
    /**
     * AJAX handler for getting initial jobs (first 8)
     */
    public function ajax_get_initial_jobs() {
        if (!wp_verify_nonce($_POST['nonce'], 'jms_shortcode_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        
        $sql = "SELECT id, name FROM $jobs_table 
                WHERE status = 'active' 
                ORDER BY created_at DESC
                LIMIT 8";
        
        $jobs = $wpdb->get_results($sql);
        
        wp_send_json_success($jobs);
    }
}

// Initialize shortcodes
new JMS_Shortcodes();
