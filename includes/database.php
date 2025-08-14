<?php
/**
 * Database management for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

class JMS_Database {
    
    /**
     * Create all required database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create departments table
        $table_departments = $wpdb->prefix . 'jms_departments';
        $sql_departments = "CREATE TABLE $table_departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL UNIQUE,
            color varchar(7) DEFAULT '#007cba',
            icon varchar(255) DEFAULT 'fas fa-briefcase',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP
        );";
        
        // Create locations table
        $table_locations = $wpdb->prefix . 'jms_locations';
        $sql_locations = "CREATE TABLE $table_locations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL UNIQUE,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP
        );";
        
        // Create jobs table
        $table_jobs = $wpdb->prefix . 'jms_jobs';
        $sql_jobs = "CREATE TABLE $table_jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL UNIQUE,
            department_id INTEGER NOT NULL,
            location_id INTEGER NOT NULL,
            work_type varchar(20) DEFAULT 'full-time' CHECK(work_type IN ('full-time','part-time','contract','freelance','internship')),
            salary varchar(100),
            application_deadline date,
            description text,
            requirements text,
            benefits text,
            status varchar(20) DEFAULT 'active' CHECK(status IN ('active','inactive','closed')),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES $table_departments(id) ON DELETE CASCADE,
            FOREIGN KEY (location_id) REFERENCES $table_locations(id) ON DELETE CASCADE
        );";
        
        // Create applications table
        $table_applications = $wpdb->prefix . 'jms_applications';
        $sql_applications = "CREATE TABLE $table_applications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            job_id INTEGER NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50),
            cv_file varchar(255),
            cover_letter text,
            status varchar(20) DEFAULT 'pending' CHECK(status IN ('pending','approved','rejected','shortlisted')),
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (job_id) REFERENCES $table_jobs(id) ON DELETE CASCADE
        );";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_departments);
        dbDelta($sql_locations);
        dbDelta($sql_jobs);
        dbDelta($sql_applications);
        
        // Insert sample data if tables are empty
        self::insert_sample_data();
    }
    
    /**
     * Insert sample data for testing
     */
    private static function insert_sample_data() {
        global $wpdb;
        
        $table_departments = $wpdb->prefix . 'jms_departments';
        $table_locations = $wpdb->prefix . 'jms_locations';
        
        // Check if we have any departments
        $dept_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_departments");
        
        if ($dept_count == 0) {
            // Insert sample departments
            $sample_departments = array(
                array(
                    'name' => 'Information Technology',
                    'slug' => 'information-technology',
                    'color' => '#007cba',
                    'icon' => 'fas fa-laptop-code',
                    'description' => 'Technology and software development positions'
                ),
                array(
                    'name' => 'Human Resources',
                    'slug' => 'human-resources',
                    'color' => '#28a745',
                    'icon' => 'fas fa-users',
                    'description' => 'HR and recruitment related positions'
                ),
                array(
                    'name' => 'Marketing',
                    'slug' => 'marketing',
                    'color' => '#ffc107',
                    'icon' => 'fas fa-bullhorn',
                    'description' => 'Marketing and promotional activities'
                ),
                array(
                    'name' => 'Sales',
                    'slug' => 'sales',
                    'color' => '#dc3545',
                    'icon' => 'fas fa-chart-line',
                    'description' => 'Sales and business development roles'
                )
            );
            
            foreach ($sample_departments as $dept) {
                $wpdb->insert($table_departments, $dept);
            }
        }
        
        // Check if we have any locations
        $loc_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_locations");
        
        if ($loc_count == 0) {
            // Insert sample locations
            $sample_locations = array(
                array(
                    'name' => 'New York, NY',
                    'slug' => 'new-york-ny',
                    'description' => 'New York City office location'
                ),
                array(
                    'name' => 'San Francisco, CA',
                    'slug' => 'san-francisco-ca',
                    'description' => 'San Francisco office location'
                ),
                array(
                    'name' => 'Remote',
                    'slug' => 'remote',
                    'description' => 'Work from anywhere position'
                ),
                array(
                    'name' => 'London, UK',
                    'slug' => 'london-uk',
                    'description' => 'London office location'
                )
            );
            
            foreach ($sample_locations as $loc) {
                $wpdb->insert($table_locations, $loc);
            }
        }
    }
    
    /**
     * Drop all plugin tables (used in uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'jms_applications',
            $wpdb->prefix . 'jms_jobs',
            $wpdb->prefix . 'jms_locations',
            $wpdb->prefix . 'jms_departments'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Get table name with prefix
     */
    public static function get_table($table_name) {
        global $wpdb;
        return $wpdb->prefix . 'jms_' . $table_name;
    }
}

/**
 * Helper functions for database operations
 */
class JMS_DB_Helper {
    
    /**
     * Get all departments
     */
    public static function get_departments($args = array()) {
        // Check if using options fallback
        if (get_option('jms_use_options')) {
            $departments = get_option('jms_departments', []);
            $result = [];
            foreach ($departments as $id => $dept) {
                $obj = new stdClass();
                $obj->id = $id;
                $obj->name = $dept['name'];
                $obj->slug = $dept['slug'];
                $obj->color = $dept['color'];
                $obj->icon = $dept['icon'];
                $obj->description = $dept['description'];
                $obj->created_at = date('Y-m-d H:i:s');
                $result[] = $obj;
            }
            return $result;
        }
        
        // Use database if available
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        
        $defaults = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => 0
        );
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT * FROM $table ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']}";
        }
        
        try {
            return $wpdb->get_results($sql);
        } catch (Exception $e) {
            // Fallback to empty array if database error
            return [];
        }
    }
    
    /**
     * Get department by slug
     */
    public static function get_department_by_slug($slug) {
        // Check if using options fallback
        if (get_option('jms_use_options')) {
            $departments = get_option('jms_departments', []);
            foreach ($departments as $id => $dept) {
                if ($dept['slug'] === $slug) {
                    $obj = new stdClass();
                    $obj->id = $id;
                    $obj->name = $dept['name'];
                    $obj->slug = $dept['slug'];
                    $obj->color = $dept['color'];
                    $obj->icon = $dept['icon'];
                    $obj->description = $dept['description'];
                    $obj->created_at = date('Y-m-d H:i:s');
                    return $obj;
                }
            }
            return null;
        }
        
        // Use database if available
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        
        try {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE slug = %s",
                $slug
            ));
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all locations
     */
    public static function get_locations($args = array()) {
        // Check if using options fallback
        if (get_option('jms_use_options')) {
            $locations = get_option('jms_locations', []);
            $result = [];
            foreach ($locations as $id => $loc) {
                $obj = new stdClass();
                $obj->id = $id;
                $obj->name = $loc['name'];
                $obj->slug = $loc['slug'];
                $obj->description = $loc['description'];
                $obj->created_at = date('Y-m-d H:i:s');
                $result[] = $obj;
            }
            return $result;
        }
        
        // Use database if available
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        
        $defaults = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => 0
        );
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT * FROM $table ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']}";
        }
        
        try {
            return $wpdb->get_results($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get location by slug
     */
    public static function get_location_by_slug($slug) {
        // Check if using options fallback
        if (get_option('jms_use_options')) {
            $locations = get_option('jms_locations', []);
            foreach ($locations as $id => $loc) {
                if ($loc['slug'] === $slug) {
                    $obj = new stdClass();
                    $obj->id = $id;
                    $obj->name = $loc['name'];
                    $obj->slug = $loc['slug'];
                    $obj->description = $loc['description'];
                    $obj->created_at = date('Y-m-d H:i:s');
                    return $obj;
                }
            }
            return null;
        }
        
        // Use database if available
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        
        try {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE slug = %s",
                $slug
            ));
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get jobs with department and location info
     */
    public static function get_jobs($args = array()) {
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        $loc_table = JMS_Database::get_table('locations');
        
        // Auto-update expired jobs
        self::update_expired_jobs();
        
        $defaults = array(
            'department_id' => 0,
            'location_id' => 0,
            'status' => 'active',
            'orderby' => 'j.created_at',
            'order' => 'DESC',
            'limit' => 0
        );
        $args = wp_parse_args($args, $defaults);
        
        $where = array("1=1");
        $values = array();
        
        // Only filter by status if a specific status is provided
        if (!empty($args['status'])) {
            $where[] = "j.status = %s";
            $values[] = $args['status'];
        }
        
        if ($args['department_id'] > 0) {
            $where[] = "j.department_id = %d";
            $values[] = $args['department_id'];
        }
        
        if ($args['location_id'] > 0) {
            $where[] = "j.location_id = %d";
            $values[] = $args['location_id'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT j.*, d.name as department_name, d.color as department_color, d.icon as department_icon, 
                       l.name as location_name
                FROM $jobs_table j 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                LEFT JOIN $loc_table l ON j.location_id = l.id 
                WHERE $where_clause 
                ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']}";
        }
        
        if (!empty($values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    /**
     * Get job by ID
     */
    public static function get_job($id) {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Get job by slug
     */
    public static function get_job_by_slug($slug) {
        global $wpdb;
        $jobs_table = JMS_Database::get_table('jobs');
        $dept_table = JMS_Database::get_table('departments');
        $loc_table = JMS_Database::get_table('locations');
        
        $sql = "SELECT j.*, d.name as department_name, d.color as department_color, d.icon as department_icon, 
                       l.name as location_name
                FROM $jobs_table j 
                LEFT JOIN $dept_table d ON j.department_id = d.id 
                LEFT JOIN $loc_table l ON j.location_id = l.id 
                WHERE j.slug = %s AND j.status = 'active'";
        
        return $wpdb->get_row($wpdb->prepare($sql, $slug));
    }
    
    /**
     * Get applications with job info
     */
    public static function get_applications($args = array()) {
        global $wpdb;
        $app_table = JMS_Database::get_table('applications');
        $jobs_table = JMS_Database::get_table('jobs');
        
        $defaults = array(
            'job_id' => 0,
            'status' => '',
            'orderby' => 'a.created_at',
            'order' => 'DESC',
            'limit' => 0
        );
        $args = wp_parse_args($args, $defaults);
        
        $where = array("1=1");
        $values = array();
        
        if ($args['job_id'] > 0) {
            $where[] = "a.job_id = %d";
            $values[] = $args['job_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = "a.status = %s";
            $values[] = $args['status'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT a.*, j.name as job_name 
                FROM $app_table a 
                LEFT JOIN $jobs_table j ON a.job_id = j.id 
                WHERE $where_clause 
                ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT {$args['limit']}";
        }
        
        if (!empty($values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $values));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    /**
     * Auto-update expired jobs status to 'closed'
     */
    public static function update_expired_jobs() {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        try {
            $wpdb->query($wpdb->prepare(
                "UPDATE $table 
                 SET status = 'closed', updated_at = %s 
                 WHERE status = 'active' 
                 AND application_deadline IS NOT NULL 
                 AND application_deadline != '' 
                 AND application_deadline < %s",
                current_time('mysql'),
                current_time('Y-m-d')
            ));
        } catch (Exception $e) {
            error_log("JMS: Failed to update expired jobs: " . $e->getMessage());
        }
    }
}
