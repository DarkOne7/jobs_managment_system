<?php
/**
 * ملف إدارة Custom Post Types
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
        // تسجيل post type للأقسام مع المزيد من التفاصيل
        register_post_type('job_department', array(
            'labels' => array(
                'name' => __('أقسام الوظائف', 'job-system'),
                'singular_name' => __('قسم وظائف', 'job-system'),
                'add_new' => __('إضافة قسم جديد', 'job-system'),
                'add_new_item' => __('إضافة قسم وظائف جديد', 'job-system'),
                'edit_item' => __('تعديل قسم الوظائف', 'job-system'),
                'new_item' => __('قسم وظائف جديد', 'job-system'),
                'all_items' => __('جميع أقسام الوظائف', 'job-system'),
                'view_item' => __('عرض قسم الوظائف', 'job-system'),
                'search_items' => __('البحث في أقسام الوظائف', 'job-system'),
                'not_found' => __('لم يتم العثور على أقسام وظائف', 'job-system'),
                'not_found_in_trash' => __('لم يتم العثور على أقسام وظائف في المهملات', 'job-system'),
                'menu_name' => __('أقسام الوظائف', 'job-system')
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
        
        // تسجيل post type للوظائف مع المزيد من التفاصيل
        register_post_type('job', array(
            'labels' => array(
                'name' => __('الوظائف', 'job-system'),
                'singular_name' => __('وظيفة', 'job-system'),
                'add_new' => __('إضافة وظيفة جديدة', 'job-system'),
                'add_new_item' => __('إضافة وظيفة جديدة', 'job-system'),
                'edit_item' => __('تعديل الوظيفة', 'job-system'),
                'new_item' => __('وظيفة جديدة', 'job-system'),
                'all_items' => __('جميع الوظائف', 'job-system'),
                'view_item' => __('عرض الوظيفة', 'job-system'),
                'search_items' => __('البحث في الوظائف', 'job-system'),
                'not_found' => __('لم يتم العثور على وظائف', 'job-system'),
                'not_found_in_trash' => __('لم يتم العثور على وظائف في المهملات', 'job-system'),
                'menu_name' => __('الوظائف', 'job-system')
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
    
    // إضافة أعمدة مخصصة لجدول الوظائف في الـ admin
    public function add_job_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['job_department'] = __('القسم', 'job-system');
        $new_columns['job_location'] = __('الموقع', 'job-system');
        $new_columns['job_type'] = __('نوع الوظيفة', 'job-system');
        $new_columns['job_salary'] = __('الراتب', 'job-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    // عرض محتوى الأعمدة المخصصة للوظائف
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
                    echo '<span style="color: #999;">غير محدد</span>';
                }
                break;
            case 'job_location':
                $location = get_post_meta($post_id, '_job_location', true);
                echo $location ? esc_html($location) : '<span style="color: #999;">غير محدد</span>';
                break;
            case 'job_type':
                $type = get_post_meta($post_id, '_job_type', true);
                $types = array(
                    'full-time' => 'دوام كامل',
                    'part-time' => 'دوام جزئي',
                    'contract' => 'عقد',
                    'freelance' => 'عمل حر'
                );
                echo isset($types[$type]) ? $types[$type] : '<span style="color: #999;">غير محدد</span>';
                break;
            case 'job_salary':
                $salary = get_post_meta($post_id, '_job_salary', true);
                echo $salary ? esc_html($salary) : '<span style="color: #999;">غير محدد</span>';
                break;
        }
    }
    
    // إضافة أعمدة مخصصة لجدول الأقسام في الـ admin
    public function add_department_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['department_icon'] = __('الأيقونة', 'job-system');
        $new_columns['department_color'] = __('اللون', 'job-system');
        $new_columns['jobs_count'] = __('عدد الوظائف', 'job-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    // عرض محتوى الأعمدة المخصصة للأقسام
    public function department_custom_column($column, $post_id) {
        switch ($column) {
            case 'department_icon':
                $icon = get_post_meta($post_id, '_department_icon', true);
                if ($icon) {
                    echo '<i class="' . esc_attr($icon) . '" style="font-size: 20px;"></i> ' . esc_html($icon);
                } else {
                    echo '<span style="color: #999;">غير محدد</span>';
                }
                break;
            case 'department_color':
                $color = get_post_meta($post_id, '_department_color', true);
                if ($color) {
                    echo '<div style="width: 30px; height: 20px; background-color: ' . esc_attr($color) . '; border: 1px solid #ddd; display: inline-block; margin-right: 10px;"></div>';
                    echo esc_html($color);
                } else {
                    echo '<span style="color: #999;">غير محدد</span>';
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
                echo '<strong>' . $count . '</strong> وظيفة';
                break;
        }
    }
    
    // دالة مساعدة للحصول على جميع الوظائف في قسم معين
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
    
    // دالة مساعدة للحصول على جميع الأقسام
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

// تشغيل كلاس Post Types
new JobSystemPostTypes();
