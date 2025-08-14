<?php
/**
 * Custom Post Types Management File
 */

if (!defined('ABSPATH')) {
    exit;
}

class JobSystemPostTypes {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_filter('manage_job_posts_columns', array($this, 'add_job_columns'));
        add_action('manage_job_posts_custom_column', array($this, 'job_custom_column'), 10, 2);
        add_filter('manage_job_department_posts_columns', array($this, 'add_department_columns'));
        add_action('manage_job_department_posts_custom_column', array($this, 'department_custom_column'), 10, 2);
    }
    
    public function register_post_types() {
        // Register post type for departments with more details
        register_post_type('job_department', array(
            'labels' => array(
                'name' => __('Job Departments', 'job-system'),
                'singular_name' => __('Job Department', 'job-system'),
                'add_new' => __('Add New Department', 'job-system'),
                'add_new_item' => __('Add New Job Department', 'job-system'),
                'edit_item' => __('Edit Job Department', 'job-system'),
                'new_item' => __('New Job Department', 'job-system'),
                'all_items' => __('All Job Departments', 'job-system'),
                'view_item' => __('View Job Department', 'job-system'),
                'search_items' => __('Search Job Departments', 'job-system'),
                'not_found' => __('No job departments found', 'job-system'),
                'not_found_in_trash' => __('No job departments found in trash', 'job-system'),
                'menu_name' => __('Job Departments', 'job-system')
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-department'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-building',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
            'show_in_rest' => true,
        ));
        
        // Register post type for jobs with more details
        register_post_type('job', array(
            'labels' => array(
                'name' => __('Jobs', 'job-system'),
                'singular_name' => __('Job', 'job-system'),
                'add_new' => __('Add New Job', 'job-system'),
                'add_new_item' => __('Add New Job', 'job-system'),
                'edit_item' => __('Edit Job', 'job-system'),
                'new_item' => __('New Job', 'job-system'),
                'all_items' => __('All Jobs', 'job-system'),
                'view_item' => __('View Job', 'job-system'),
                'search_items' => __('Search Jobs', 'job-system'),
                'not_found' => __('No jobs found', 'job-system'),
                'not_found_in_trash' => __('No jobs found in trash', 'job-system'),
                'menu_name' => __('Jobs', 'job-system')
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 26,
            'menu_icon' => 'dashicons-businessman',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));
    }
    
    // Add custom columns to jobs table in admin
    public function add_job_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['job_department'] = __('Department', 'job-system');
        $new_columns['job_location'] = __('Location', 'job-system');
        $new_columns['job_type'] = __('Job Type', 'job-system');
        $new_columns['job_salary'] = __('Salary', 'job-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    // Display custom column content for jobs
    public function job_custom_column($column, $post_id) {
        switch ($column) {
            case 'job_department':
                $department_id = get_post_meta($post_id, '_job_department', true);
                if ($department_id) {
                    $department = get_post($department_id);
                    if ($department) {
                        echo '<a href="' . admin_url('edit.php?post_type=job_department&post=' . $department_id . '&action=edit') . '">';
                        echo esc_html($department->post_title);
                        echo '</a>';
                    }
                } else {
                    echo '<span style="color: #999;">Not specified</span>';
                }
                break;
            case 'job_location':
                $location = get_post_meta($post_id, '_job_location', true);
                echo $location ? esc_html($location) : '<span style="color: #999;">Not specified</span>';
                break;
            case 'job_type':
                $type = get_post_meta($post_id, '_job_type', true);
                $types = array(
                    'full-time' => 'Full Time',
                    'part-time' => 'Part Time',
                    'contract' => 'Contract',
                    'freelance' => 'Freelance'
                );
                echo isset($types[$type]) ? $types[$type] : '<span style="color: #999;">Not specified</span>';
                break;
            case 'job_salary':
                $salary = get_post_meta($post_id, '_job_salary', true);
                echo $salary ? esc_html($salary) : '<span style="color: #999;">Not specified</span>';
                break;
        }
    }
    
    // Add custom columns to departments table in admin
    public function add_department_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['department_icon'] = __('Icon', 'job-system');
        $new_columns['department_color'] = __('Color', 'job-system');
        $new_columns['jobs_count'] = __('Jobs Count', 'job-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    // Display custom column content for departments
    public function department_custom_column($column, $post_id) {
        switch ($column) {
            case 'department_icon':
                $icon = get_post_meta($post_id, '_department_icon', true);
                if ($icon) {
                    echo '<i class="' . esc_attr($icon) . '" style="font-size: 20px;"></i> ' . esc_html($icon);
                } else {
                    echo '<span style="color: #999;">Not specified</span>';
                }
                break;
            case 'department_color':
                $color = get_post_meta($post_id, '_department_color', true);
                if ($color) {
                    echo '<div style="width: 30px; height: 20px; background-color: ' . esc_attr($color) . '; border: 1px solid #ddd; display: inline-block; margin-right: 10px;"></div>';
                    echo esc_html($color);
                } else {
                    echo '<span style="color: #999;">Not specified</span>';
                }
                break;
            case 'jobs_count':
                $jobs = get_posts(array(
                    'post_type' => 'job',
                    'meta_query' => array(
                        array(
                            'key' => '_job_department',
                            'value' => $post_id,
                            'compare' => '='
                        )
                    ),
                    'numberposts' => -1
                ));
                $count = count($jobs);
                echo '<strong>' . $count . '</strong> jobs';
                break;
        }
    }
    
    // Helper function to get all jobs in a specific department
    public static function get_jobs_by_department($department_id) {
        return get_posts(array(
            'post_type' => 'job',
            'meta_query' => array(
                array(
                    'key' => '_job_department',
                    'value' => $department_id,
                    'compare' => '='
                )
            ),
            'numberposts' => -1,
            'post_status' => 'publish'
        ));
    }
    
    // Helper function to get all departments
    public static function get_all_departments() {
        return get_posts(array(
            'post_type' => 'job_department',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order title',
            'order' => 'ASC'
        ));
    }
}

// Initialize Post Types class
new JobSystemPostTypes();
