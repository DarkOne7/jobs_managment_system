<?php
/**
 * Template handler for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Templates {
    
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_job_pages'));
    }
    
    /**
     * Handle job page requests
     */
    public function handle_job_pages() {
        global $jms_data;
        
        $jms_page = get_query_var('jms_page');
        
        if ($jms_page) {
            switch ($jms_page) {
                case 'departments':
                    $this->load_departments_page();
                    break;
                case 'department_jobs':
                    $this->load_department_jobs_page();
                    break;
                case 'location_jobs':
                    $this->load_location_jobs_page();
                    break;
                case 'single_job':
                    $this->load_single_job_page();
                    break;
                case 'application_success':
                    $this->load_application_success_page();
                    break;
            }
        }
    }
    
    /**
     * Load departments archive page
     */
    private function load_departments_page() {
        global $jms_data;
        
        $departments = JMS_DB_Helper::get_departments();
        
        // Add job counts to departments
        foreach ($departments as $department) {
            $department->jobs_count = JMS_DB_Helper::get_jobs(array(
                'department_id' => $department->id,
                'status' => 'active'
            ));
            $department->jobs_count = count($department->jobs_count);
        }
        
        $jms_data = array(
            'departments' => $departments,
            'page_title' => 'Job Departments',
            'page_description' => 'Explore job opportunities by department'
        );
    }
    
    /**
     * Load department jobs page
     */
    private function load_department_jobs_page() {
        global $jms_data;
        
        $department_slug = get_query_var('department_slug');
        
        if (!$department_slug) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $department = JMS_Department::get_by_slug($department_slug);
        if (!$department) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $jobs = JMS_DB_Helper::get_jobs(array(
            'department_id' => $department->id,
            'status' => 'active'
        ));
        
        $jms_data = array(
            'department' => $department,
            'jobs' => $jobs,
            'page_title' => $department->name . ' Jobs',
            'page_description' => 'Job opportunities in ' . $department->name
        );
    }
    
    /**
     * Load location jobs page
     */
    private function load_location_jobs_page() {
        global $jms_data;
        
        $location_slug = get_query_var('location_slug');
        
        if (!$location_slug) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $location = JMS_Location::get_by_slug($location_slug);
        if (!$location) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $jobs = JMS_DB_Helper::get_jobs(array(
            'location_id' => $location->id,
            'status' => 'active'
        ));
        
        $jms_data = array(
            'location' => $location,
            'jobs' => $jobs,
            'page_title' => $location->name . ' Jobs',
            'page_description' => 'Job opportunities in ' . $location->name
        );
    }
    
    /**
     * Load single job page
     */
    private function load_single_job_page() {
        global $jms_data;
        
        $job_slug = get_query_var('job_slug');
        
        if (!$job_slug) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $job = JMS_Job::get_by_slug($job_slug);
        if (!$job) {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        // Check if job is still active
        if ($job->status !== 'active') {
            wp_redirect(home_url('/jobs/'));
            exit;
        }
        
        $jms_data = array(
            'job' => $job,
            'page_title' => $job->name,
            'page_description' => wp_trim_words(strip_tags($job->description), 25)
        );
    }
    
    /**
     * Load application success page
     */
    private function load_application_success_page() {
        global $jms_data;
        
        $jms_data = array(
            'page_title' => 'Application Submitted',
            'page_description' => 'Your job application has been submitted successfully'
        );
    }
    
    /**
     * Helper functions for templates
     */
    
    /**
     * Format work type display
     */
    public static function format_work_type($work_type) {
        $types = array(
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship'
        );
        
        return isset($types[$work_type]) ? $types[$work_type] : ucfirst($work_type);
    }
    
    /**
     * Format salary display
     */
    public static function format_salary($salary) {
        if (empty($salary)) {
            return 'Competitive';
        }
        
        // If it's numeric, add currency
        if (is_numeric($salary)) {
            return '$' . number_format($salary);
        }
        
        return $salary;
    }
    
    /**
     * Get time ago format
     */
    public static function time_ago($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        if ($time < 31536000) return floor($time/2592000) . ' months ago';
        
        return floor($time/31536000) . ' years ago';
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $ending = '...') {
        if (strlen($text) > $length) {
            return substr($text, 0, $length - strlen($ending)) . $ending;
        }
        return $text;
    }
    
    /**
     * Get department icon with fallback
     */
    public static function get_department_icon($icon) {
        return !empty($icon) ? $icon : 'fas fa-briefcase';
    }
    
    /**
     * Get department color with fallback
     */
    public static function get_department_color($color) {
        return !empty($color) ? $color : '#007cba';
    }
    
    /**
     * Generate breadcrumb navigation
     */
    public static function get_breadcrumbs() {
        $jms_page = get_query_var('jms_page');
        $breadcrumbs = array();
        
        // Always start with jobs home
        $breadcrumbs[] = array(
            'title' => 'Jobs',
            'url' => home_url('/jobs/')
        );
        
        switch ($jms_page) {
            case 'department_jobs':
                $department_slug = get_query_var('department_slug');
                if ($department_slug) {
                    $department = JMS_Department::get_by_slug($department_slug);
                    if ($department) {
                        $breadcrumbs[] = array(
                            'title' => $department->name,
                            'url' => ''
                        );
                    }
                }
                break;
                
            case 'location_jobs':
                $location_slug = get_query_var('location_slug');
                if ($location_slug) {
                    $location = JMS_Location::get_by_slug($location_slug);
                    if ($location) {
                        $breadcrumbs[] = array(
                            'title' => $location->name,
                            'url' => ''
                        );
                    }
                }
                break;
                
            case 'single_job':
                $job_slug = get_query_var('job_slug');
                if ($job_slug) {
                    $job = JMS_Job::get_by_slug($job_slug);
                    if ($job) {
                        // Add department breadcrumb
                        if ($job->department_name) {
                            $department = JMS_DB_Helper::get_department_by_slug($job_slug); // This needs to be fixed
                            $breadcrumbs[] = array(
                                'title' => $job->department_name,
                                'url' => home_url('/jobs/department/' . $department->slug)
                            );
                        }
                        
                        // Add job breadcrumb
                        $breadcrumbs[] = array(
                            'title' => $job->name,
                            'url' => ''
                        );
                    }
                }
                break;
        }
        
        return $breadcrumbs;
    }
}

// Initialize templates handler
new JMS_Templates();