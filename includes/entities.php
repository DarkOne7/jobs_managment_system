<?php
/**
 * Entity classes for Job Management System
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Department Entity
 */
class JMS_Department {
    
    public $id;
    public $name;
    public $slug;
    public $color;
    public $icon;
    public $description;
    public $created_at;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    /**
     * Save department to database
     */
    public function save() {
        global $wpdb;
        $table = JMS_Database::get_table('departments');
        
        $data = array(
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'icon' => $this->icon,
            'description' => $this->description
        );
        
        if ($this->id) {
            $wpdb->update($table, $data, array('id' => $this->id));
        } else {
            $wpdb->insert($table, $data);
            $this->id = $wpdb->insert_id;
        }
        
        return $this->id;
    }
    
    /**
     * Get jobs count for this department
     */
    public function get_jobs_count() {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE department_id = %d AND status = 'active'",
            $this->id
        ));
    }
    
    /**
     * Get department by slug
     */
    public static function get_by_slug($slug) {
        $dept = JMS_DB_Helper::get_department_by_slug($slug);
        return $dept ? new self((array)$dept) : null;
    }
}

/**
 * Location Entity
 */
class JMS_Location {
    
    public $id;
    public $name;
    public $slug;
    public $description;
    public $created_at;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    /**
     * Save location to database
     */
    public function save() {
        global $wpdb;
        $table = JMS_Database::get_table('locations');
        
        $data = array(
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description
        );
        
        if ($this->id) {
            $wpdb->update($table, $data, array('id' => $this->id));
        } else {
            $wpdb->insert($table, $data);
            $this->id = $wpdb->insert_id;
        }
        
        return $this->id;
    }
    
    /**
     * Get jobs count for this location
     */
    public function get_jobs_count() {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE location_id = %d AND status = 'active'",
            $this->id
        ));
    }
    
    /**
     * Get location by slug
     */
    public static function get_by_slug($slug) {
        $location = JMS_DB_Helper::get_location_by_slug($slug);
        return $location ? new self((array)$location) : null;
    }
}

/**
 * Job Entity
 */
class JMS_Job {
    
    public $id;
    public $name;
    public $slug;
    public $department_id;
    public $location_id;
    public $work_type;
    public $salary;
    public $application_deadline;
    public $description;
    public $requirements;
    public $benefits;
    public $status;
    public $created_at;
    public $updated_at;
    
    // Related data
    public $department_name;
    public $department_color;
    public $department_icon;
    public $location_name;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    /**
     * Save job to database
     */
    public function save() {
        global $wpdb;
        $table = JMS_Database::get_table('jobs');
        
        $data = array(
            'name' => $this->name,
            'slug' => $this->slug,
            'department_id' => $this->department_id,
            'location_id' => $this->location_id,
            'work_type' => $this->work_type,
            'salary' => $this->salary,
            'application_deadline' => $this->application_deadline,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'status' => $this->status
        );
        
        if ($this->id) {
            $wpdb->update($table, $data, array('id' => $this->id));
        } else {
            $wpdb->insert($table, $data);
            $this->id = $wpdb->insert_id;
        }
        
        return $this->id;
    }
    
    /**
     * Get applications count for this job
     */
    public function get_applications_count() {
        global $wpdb;
        $table = JMS_Database::get_table('applications');
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE job_id = %d",
            $this->id
        ));
    }
    
    /**
     * Check if application deadline has passed
     */
    public function is_deadline_passed() {
        if (empty($this->application_deadline)) {
            return false;
        }
        
        return strtotime($this->application_deadline) < time();
    }
    
    /**
     * Get work type display name
     */
    public function get_work_type_display() {
        $types = array(
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship'
        );
        
        return isset($types[$this->work_type]) ? $types[$this->work_type] : ucfirst($this->work_type);
    }
    
    /**
     * Get job by slug
     */
    public static function get_by_slug($slug) {
        $job = JMS_DB_Helper::get_job_by_slug($slug);
        return $job ? new self((array)$job) : null;
    }
}

/**
 * Application Entity
 */
class JMS_Application {
    
    public $id;
    public $job_id;
    public $name;
    public $email;
    public $phone;
    public $cv_file;
    public $cover_letter;
    public $status;
    public $admin_notes;
    public $created_at;
    public $updated_at;
    
    // Related data
    public $job_name;
    public $department_name;
    
    public function __construct($data = array()) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    /**
     * Save application to database
     */
    public function save() {
        global $wpdb;
        $table = JMS_Database::get_table('applications');
        
        $data = array(
            'job_id' => $this->job_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cv_file' => $this->cv_file,
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes
        );
        
        if ($this->id) {
            $wpdb->update($table, $data, array('id' => $this->id));
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $data);
            $this->id = $wpdb->insert_id;
        }
        
        return $this->id;
    }
    
    /**
     * Get status display name
     */
    public function get_status_display() {
        $statuses = array(
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'shortlisted' => 'Shortlisted'
        );
        
        return isset($statuses[$this->status]) ? $statuses[$this->status] : ucfirst($this->status);
    }
    
    /**
     * Get status color class
     */
    public function get_status_color() {
        $colors = array(
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'shortlisted' => 'info'
        );
        
        return isset($colors[$this->status]) ? $colors[$this->status] : 'secondary';
    }
    
    /**
     * Get CV file URL
     */
    public function get_cv_url() {
        if (empty($this->cv_file)) {
            return '';
        }
        
        return JMS_UPLOAD_URL . $this->cv_file;
    }
    
    /**
     * Delete CV file
     */
    public function delete_cv_file() {
        if (empty($this->cv_file)) {
            return true;
        }
        
        $file_path = JMS_UPLOAD_DIR . $this->cv_file;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }
    
    /**
     * Send notification email
     */
    public function send_notification($type = 'submitted') {
        $email_handler = new JMS_Email_Handler();
        return $email_handler->send_application_notification($this, $type);
    }
}

