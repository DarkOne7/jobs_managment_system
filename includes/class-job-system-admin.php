<?php
/**
 * Admin functionality for Job System
 */

if (!defined('ABSPATH')) {
    exit;
}

class Job_System_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('add_meta_boxes', array($this, 'remove_default_taxonomy_meta_boxes'));
        add_action('save_post', array($this, 'save_job_meta'));
        add_action('job_department_add_form_fields', array($this, 'add_department_color_field'));
        add_action('job_department_edit_form_fields', array($this, 'edit_department_color_field'));
        add_action('created_job_department', array($this, 'save_department_color'));
        add_action('edited_job_department', array($this, 'save_department_color'));
        
        // Location taxonomy actions
        add_action('job_location_add_form_fields', array($this, 'add_location_color_field'));
        add_action('job_location_edit_form_fields', array($this, 'edit_location_color_field'));
        add_action('created_job_location', array($this, 'save_location_color'));
        add_action('edited_job_location', array($this, 'save_location_color'));
        add_filter('manage_job_posts_columns', array($this, 'job_columns'));
        add_action('manage_job_posts_custom_column', array($this, 'job_custom_column'), 10, 2);
        add_filter('manage_job_application_posts_columns', array($this, 'application_columns'));
        add_action('manage_job_application_posts_custom_column', array($this, 'application_custom_column'), 10, 2);
        
        // Add admin notices for missing data
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Handle CSV export
        add_action('admin_init', array($this, 'handle_export_applications'));
        
        // Handle application editing
        add_action('admin_init', array($this, 'handle_edit_application'));
        add_action('wp_ajax_update_application_status', array($this, 'ajax_update_application_status'));
        
        // Handle test email
        add_action('admin_init', array($this, 'handle_test_email'));
        add_action('admin_init', array($this, 'handle_quick_mail_test'));
        
        // AJAX handlers for quick edit
        add_action('wp_ajax_quick_edit_application', array($this, 'handle_quick_edit'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=job',
            __('Job Applications', 'job-system'),
            __('Applications', 'job-system'),
            'manage_options',
            'job-applications',
            array($this, 'applications_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=job',
            __('Test Email', 'job-system'),
            __('Test Email', 'job-system'),
            'manage_options',
            'job-test-email',
            array($this, 'test_email_page')
        );
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'job-details',
            __('Job Details', 'job-system'),
            array($this, 'job_details_meta_box'),
            'job',
            'normal',
            'high'
        );
    }
    
    /**
     * Remove default taxonomy meta boxes
     */
    public function remove_default_taxonomy_meta_boxes() {
        remove_meta_box('job_departmentdiv', 'job', 'side');
        remove_meta_box('tagsdiv-job_department', 'job', 'side');
        remove_meta_box('job_locationdiv', 'job', 'side');
        remove_meta_box('tagsdiv-job_location', 'job', 'side');
    }
    
    /**
     * Job details meta box
     */
    public function job_details_meta_box($post) {
        wp_nonce_field('save_job_meta', 'job_meta_nonce');
        
        $application_deadline = get_post_meta($post->ID, '_application_deadline', true);
        $location = get_post_meta($post->ID, '_job_location', true);
        $work_type = get_post_meta($post->ID, '_work_type', true);
        
        // Get current departments assigned to this job
        $current_departments = wp_get_object_terms($post->ID, 'job_department', array('fields' => 'ids'));
        $current_department = !empty($current_departments) ? $current_departments[0] : '';
        
        // Get current locations assigned to this job
        $current_locations = wp_get_object_terms($post->ID, 'job_location', array('fields' => 'ids'));
        $current_location = !empty($current_locations) ? $current_locations[0] : '';
        
        // Get all available departments
        $departments = get_terms(array(
            'taxonomy' => 'job_department',
            'hide_empty' => false,
        ));
        
        // Get all available locations
        $locations = get_terms(array(
            'taxonomy' => 'job_location',
            'hide_empty' => false,
        ));
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="job_department"><?php _e('Department', 'job-system'); ?></label></th>
                <td>
                    <select id="job_department" name="job_department" class="regular-text">
                        <option value=""><?php _e('Select Department', 'job-system'); ?></option>
                        <?php if (!empty($departments) && !is_wp_error($departments)): ?>
                            <?php foreach ($departments as $department): ?>
                                <?php
                                $color = get_term_meta($department->term_id, 'department_color', true);
                                $style = $color ? 'style="background-color: ' . esc_attr($color) . '; color: white; padding: 2px 8px; border-radius: 3px;"' : '';
                                ?>
                                <option value="<?php echo esc_attr($department->term_id); ?>" 
                                        <?php selected($current_department, $department->term_id); ?>>
                                    <?php echo esc_html($department->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled><?php _e('No departments found. Please create departments first.', 'job-system'); ?></option>
                        <?php endif; ?>
                    </select>
                    <p class="description">
                        <?php _e('Select the department this job belongs to.', 'job-system'); ?>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_department&post_type=job'); ?>" target="_blank">
                            <?php _e('Manage Departments', 'job-system'); ?>
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="application_deadline"><?php _e('Application Deadline', 'job-system'); ?></label></th>
                <td>
                    <input type="datetime-local" id="application_deadline" name="application_deadline" 
                           value="<?php echo esc_attr($application_deadline); ?>" class="regular-text" />
                    <p class="description"><?php _e('Applications will automatically close after this date.', 'job-system'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="job_location_taxonomy"><?php _e('Location', 'job-system'); ?></label></th>
                <td>
                    <select id="job_location_taxonomy" name="job_location_taxonomy" class="regular-text">
                        <option value=""><?php _e('Select Location', 'job-system'); ?></option>
                        <?php if (!empty($locations) && !is_wp_error($locations)): ?>
                            <?php foreach ($locations as $location_term): ?>
                                <?php
                                $color = get_term_meta($location_term->term_id, 'location_color', true);
                                ?>
                                <option value="<?php echo esc_attr($location_term->term_id); ?>" 
                                        <?php selected($current_location, $location_term->term_id); ?>>
                                    <?php echo esc_html($location_term->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled><?php _e('No locations found. Please create locations first.', 'job-system'); ?></option>
                        <?php endif; ?>
                    </select>
                    <p class="description">
                        <?php _e('Select the location for this job.', 'job-system'); ?>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_location&post_type=job'); ?>" target="_blank">
                            <?php _e('Manage Locations', 'job-system'); ?>
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="job_location"><?php _e('Specific Address (Optional)', 'job-system'); ?></label></th>
                <td>
                    <input type="text" id="job_location" name="job_location" 
                           value="<?php echo esc_attr($location); ?>" class="regular-text" />
                    <p class="description"><?php _e('Specific address or additional location details.', 'job-system'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="work_type"><?php _e('Work Type', 'job-system'); ?></label></th>
                <td>
                    <select id="work_type" name="work_type">
                        <option value="remote" <?php selected($work_type, 'remote'); ?>><?php _e('Remote', 'job-system'); ?></option>
                        <option value="onsite" <?php selected($work_type, 'onsite'); ?>><?php _e('On-site', 'job-system'); ?></option>
                        <option value="hybrid" <?php selected($work_type, 'hybrid'); ?>><?php _e('Hybrid', 'job-system'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save job meta
     */
    public function save_job_meta($post_id) {
        if (!isset($_POST['job_meta_nonce']) || !wp_verify_nonce($_POST['job_meta_nonce'], 'save_job_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save department selection
        if (isset($_POST['job_department'])) {
            $department_id = intval($_POST['job_department']);
            if ($department_id > 0) {
                wp_set_object_terms($post_id, $department_id, 'job_department');
            } else {
                // Remove department if none selected
                wp_set_object_terms($post_id, array(), 'job_department');
            }
        }
        
        // Save location selection
        if (isset($_POST['job_location_taxonomy'])) {
            $location_id = intval($_POST['job_location_taxonomy']);
            if ($location_id > 0) {
                wp_set_object_terms($post_id, $location_id, 'job_location');
            } else {
                // Remove location if none selected
                wp_set_object_terms($post_id, array(), 'job_location');
            }
        }
        
        if (isset($_POST['application_deadline'])) {
            update_post_meta($post_id, '_application_deadline', sanitize_text_field($_POST['application_deadline']));
        }
        
        if (isset($_POST['job_location'])) {
            update_post_meta($post_id, '_job_location', sanitize_text_field($_POST['job_location']));
        }
        
        if (isset($_POST['work_type'])) {
            update_post_meta($post_id, '_work_type', sanitize_text_field($_POST['work_type']));
        }
    }
    
    /**
     * Add department color field
     */
    public function add_department_color_field() {
        ?>
        <div class="form-field">
            <label for="department_color"><?php _e('Department Color', 'job-system'); ?></label>
            <input type="text" id="department_color" name="department_color" value="#00a0d2" class="color-picker" />
            <p><?php _e('Choose a color for this department.', 'job-system'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit department color field
     */
    public function edit_department_color_field($term) {
        $color = get_term_meta($term->term_id, 'department_color', true);
        if (!$color) {
            $color = '#00a0d2';
        }
        ?>
        <tr class="form-field">
            <th scope="row"><label for="department_color"><?php _e('Department Color', 'job-system'); ?></label></th>
            <td>
                <input type="text" id="department_color" name="department_color" value="<?php echo esc_attr($color); ?>" class="color-picker" />
                <p class="description"><?php _e('Choose a color for this department.', 'job-system'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save department color
     */
    public function save_department_color($term_id) {
        if (isset($_POST['department_color'])) {
            update_term_meta($term_id, 'department_color', sanitize_hex_color($_POST['department_color']));
        }
    }
    
    /**
     * Add location color field
     */
    public function add_location_color_field() {
        ?>
        <div class="form-field">
            <label for="location_color"><?php _e('Location Color', 'job-system'); ?></label>
            <input type="text" id="location_color" name="location_color" value="#f97316" class="color-picker" />
            <p><?php _e('Choose a color for this location.', 'job-system'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Edit location color field
     */
    public function edit_location_color_field($term) {
        $color = get_term_meta($term->term_id, 'location_color', true);
        if (!$color) {
            $color = '#f97316';
        }
        ?>
        <tr class="form-field">
            <th scope="row"><label for="location_color"><?php _e('Location Color', 'job-system'); ?></label></th>
            <td>
                <input type="text" id="location_color" name="location_color" value="<?php echo esc_attr($color); ?>" class="color-picker" />
                <p class="description"><?php _e('Choose a color for this location.', 'job-system'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save location color
     */
    public function save_location_color($term_id) {
        if (isset($_POST['location_color'])) {
            update_term_meta($term_id, 'location_color', sanitize_hex_color($_POST['location_color']));
        }
    }
    
    /**
     * Job columns
     */
    public function job_columns($columns) {
        $columns['department'] = __('Department', 'job-system');
        $columns['location'] = __('Location', 'job-system');
        $columns['deadline'] = __('Application Deadline', 'job-system');
        $columns['applications'] = __('Applications', 'job-system');
        return $columns;
    }
    
    /**
     * Job custom column content
     */
    public function job_custom_column($column, $post_id) {
        switch ($column) {
            case 'department':
                $departments = get_the_terms($post_id, 'job_department');
                if ($departments && !is_wp_error($departments)) {
                    $department_names = array();
                    foreach ($departments as $department) {
                        $color = get_term_meta($department->term_id, 'department_color', true);
                        $style = $color ? 'style="background-color: ' . esc_attr($color) . '; color: white; padding: 2px 8px; border-radius: 3px; display: inline-block; margin: 2px;"' : '';
                        $department_names[] = '<span ' . $style . '>' . esc_html($department->name) . '</span>';
                    }
                    echo implode(' ', $department_names);
                } else {
                    echo '—';
                }
                break;
            case 'location':
                // Show location taxonomy
                $locations = get_the_terms($post_id, 'job_location');
                if ($locations && !is_wp_error($locations)) {
                    $location_names = array();
                    foreach ($locations as $location_term) {
                        $color = get_term_meta($location_term->term_id, 'location_color', true);
                        $style = $color ? 'style="background-color: ' . esc_attr($color) . '; color: white; padding: 2px 8px; border-radius: 3px; display: inline-block; margin: 2px;"' : '';
                        $location_names[] = '<span ' . $style . '>' . esc_html($location_term->name) . '</span>';
                    }
                    echo implode(' ', $location_names);
                    
                    // Show specific address if available
                    $specific_location = get_post_meta($post_id, '_job_location', true);
                    if ($specific_location) {
                        echo '<br><small style="color: #666;">' . esc_html($specific_location) . '</small>';
                    }
                } else {
                    // Fallback to old location meta field
                    $location = get_post_meta($post_id, '_job_location', true);
                    echo $location ? esc_html($location) : '—';
                }
                break;
            case 'deadline':
                $deadline = get_post_meta($post_id, '_application_deadline', true);
                if ($deadline) {
                    echo esc_html(date('Y-m-d H:i', strtotime($deadline)));
                } else {
                    echo '—';
                }
                break;
            case 'applications':
                global $wpdb;
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}job_applications WHERE job_id = %d",
                    $post_id
                ));
                echo intval($count);
                break;
        }
    }
    
    /**
     * Application columns
     */
    public function application_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => __('Application', 'job-system'),
            'job' => __('Job', 'job-system'),
            'applicant' => __('Applicant', 'job-system'),
            'date' => __('Date', 'job-system'),
        );
    }
    
    /**
     * Application custom column content
     */
    public function application_custom_column($column, $post_id) {
        switch ($column) {
            case 'job':
                $job_id = get_post_meta($post_id, '_job_id', true);
                if ($job_id) {
                    $job_title = get_the_title($job_id);
                    echo '<a href="' . get_edit_post_link($job_id) . '">' . esc_html($job_title) . '</a>';
                } else {
                    echo '—';
                }
                break;
            case 'applicant':
                $name = get_post_meta($post_id, '_applicant_name', true);
                $email = get_post_meta($post_id, '_applicant_email', true);
                echo esc_html($name) . '<br><small>' . esc_html($email) . '</small>';
                break;
        }
    }
    
    /**
     * Applications page
     */
    public function applications_page() {
        global $wpdb;
        
        // Check if we're editing an application
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $this->edit_application_form();
            return;
        }
        
        $applications = $wpdb->get_results(
            "SELECT a.*, j.post_title as job_title 
             FROM {$wpdb->prefix}job_applications a 
             LEFT JOIN {$wpdb->posts} j ON a.job_id = j.ID 
             ORDER BY a.application_date DESC"
        );
        
        ?>
        <div class="wrap">
            <h1><?php _e('Job Applications', 'job-system'); ?></h1>
            
            <div class="job-applications-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="job-application-search">
                    <select id="job-filter">
                        <option value=""><?php _e('All Jobs', 'job-system'); ?></option>
                        <?php
                        $jobs = get_posts(array(
                            'post_type' => 'job',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        ));
                        foreach ($jobs as $job) {
                            echo '<option value="' . esc_attr($job->ID) . '">' . esc_html($job->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="export-actions">
                    <a href="#" 
                       id="export-csv-btn"
                       class="button button-primary" 
                       style="background: #00a32a; border-color: #00a32a;">
                        <span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 5px;"></span>
                        <?php _e('Export to CSV', 'job-system'); ?>
                    </a>
                    <a href="#" 
                       id="export-excel-btn"
                       class="button button-secondary" 
                       style="margin-left: 10px;">
                        <span class="dashicons dashicons-media-spreadsheet" style="vertical-align: middle; margin-right: 5px;"></span>
                        <?php _e('Export to Excel', 'job-system'); ?>
                    </a>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Applicant Name', 'job-system'); ?></th>
                        <th><?php _e('Email', 'job-system'); ?></th>
                        <th><?php _e('Phone', 'job-system'); ?></th>
                        <th><?php _e('Job', 'job-system'); ?></th>
                        <th><?php _e('CV', 'job-system'); ?></th>
                        <th><?php _e('Application Date', 'job-system'); ?></th>
                        <th><?php _e('Status', 'job-system'); ?></th>
                        <th><?php _e('Actions', 'job-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($applications): ?>
                        <?php foreach ($applications as $application): ?>
                            <tr data-job-id="<?php echo esc_attr($application->job_id); ?>">
                                <td><?php echo esc_html($application->applicant_name); ?></td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr($application->applicant_email); ?>" 
                                       style="color: #0073aa; text-decoration: none;"
                                       title="<?php echo esc_attr('Send email to ' . $application->applicant_name); ?>">
                                        <?php echo esc_html($application->applicant_email); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($application->applicant_phone); ?></td>
                                <td><?php echo esc_html($application->job_title); ?></td>
                                <td>
                                    <?php if ($application->cv_file_path): ?>
                                        <a href="<?php echo esc_url($application->cv_file_path); ?>" 
                                           target="_blank" 
                                           class="button button-small" 
                                           style="background: #007cba; color: white; border-color: #007cba; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; margin: 0;"
                                           title="<?php echo esc_attr('Download CV: ' . basename($application->cv_file_path)); ?>">
                                            <span class="dashicons dashicons-download" style="font-size: 14px; line-height: 1;"></span>
                                            <?php _e('Download Now', 'job-system'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;"><?php _e('No CV uploaded', 'job-system'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($application->application_date))); ?></td>
                                <td>
                                    <select class="application-status" data-id="<?php echo esc_attr($application->id); ?>" data-email="<?php echo esc_attr($application->applicant_email); ?>" data-name="<?php echo esc_attr($application->applicant_name); ?>" data-job="<?php echo esc_attr($application->job_title); ?>">
                                        <option value="pending" <?php selected($application->status, 'pending'); ?>><?php _e('Pending', 'job-system'); ?></option>
                                        <option value="reviewed" <?php selected($application->status, 'reviewed'); ?>><?php _e('Reviewed', 'job-system'); ?></option>
                                        <option value="accepted" <?php selected($application->status, 'accepted'); ?>><?php _e('Accepted', 'job-system'); ?></option>
                                        <option value="rejected" <?php selected($application->status, 'rejected'); ?>><?php _e('Rejected', 'job-system'); ?></option>
                                    </select>
                                    <div class="status-update-loader" data-id="<?php echo esc_attr($application->id); ?>" style="display: none;">
                                        <span class="spinner is-active" style="float: none; margin: 0;"></span>
                                    </div>
                                </td>
                                <td>
                                    <button class="button button-small quick-edit-btn" 
                                            data-id="<?php echo esc_attr($application->id); ?>"
                                            data-name="<?php echo esc_attr($application->applicant_name); ?>"
                                            data-email="<?php echo esc_attr($application->applicant_email); ?>"
                                            data-phone="<?php echo esc_attr($application->applicant_phone); ?>"
                                            data-notes="<?php echo esc_attr($application->admin_notes ?? ''); ?>"
                                            title="<?php _e('Quick Edit', 'job-system'); ?>">
                                        <span class="dashicons dashicons-edit" style="font-size: 14px; line-height: 1;"></span>
                                        <?php _e('Quick Edit', 'job-system'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8"><?php _e('No applications found.', 'job-system'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Quick Edit Modal -->
        <div id="quick-edit-modal" style="display: none;">
            <div class="quick-edit-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999;">
                <div class="quick-edit-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 500px; max-width: 90%; box-shadow: 0 5px 25px rgba(0,0,0,0.3);">
                    <h3 style="margin-top: 0;"><?php _e('Quick Edit Application', 'job-system'); ?></h3>
                    <form id="quick-edit-form">
                        <table class="form-table">
                            <tr>
                                <th><label for="edit-name"><?php _e('Applicant Name', 'job-system'); ?></label></th>
                                <td><input type="text" id="edit-name" name="name" class="regular-text" readonly style="background: #f1f1f1;"></td>
                            </tr>
                            <tr>
                                <th><label for="edit-email"><?php _e('Email', 'job-system'); ?></label></th>
                                <td><input type="email" id="edit-email" name="email" class="regular-text" readonly style="background: #f1f1f1;"></td>
                            </tr>
                            <tr>
                                <th><label for="edit-phone"><?php _e('Phone', 'job-system'); ?></label></th>
                                <td><input type="text" id="edit-phone" name="phone" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th><label for="edit-notes"><?php _e('Admin Notes', 'job-system'); ?></label></th>
                                <td><textarea id="edit-notes" name="notes" rows="4" class="large-text"></textarea></td>
                            </tr>
                        </table>
                        <input type="hidden" id="edit-id" name="id">
                        <div style="margin-top: 20px; text-align: right;">
                            <button type="button" class="button" onclick="closeQuickEdit()"><?php _e('Cancel', 'job-system'); ?></button>
                            <button type="submit" class="button-primary" style="margin-left: 10px;"><?php _e('Update', 'job-system'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Update export button URLs
            function updateExportUrls() {
                var selectedJob = $('#job-filter').val();
                var baseUrl = '<?php echo admin_url('edit.php?post_type=job&page=job-applications'); ?>';
                
                var csvUrl = baseUrl + '&export=csv';
                var excelUrl = baseUrl + '&export=excel';
                
                if (selectedJob) {
                    csvUrl += '&job_id=' + selectedJob;
                    excelUrl += '&job_id=' + selectedJob;
                }
                
                $('#export-csv-btn').attr('href', csvUrl);
                $('#export-excel-btn').attr('href', excelUrl);
            }
            
            // Initialize export URLs
            updateExportUrls();
            
            // Job filter
            $('#job-filter').on('change', function() {
                var selectedJob = $(this).val();
                
                // Filter table rows
                if (selectedJob) {
                    $('tbody tr').hide();
                    $('tbody tr[data-job-id="' + selectedJob + '"]').show();
                } else {
                    $('tbody tr').show();
                }
                
                // Update export button URLs
                updateExportUrls();
                
                // Update export button text to show filter status
                if (selectedJob) {
                    var jobText = $('#job-filter option:selected').text();
                    $('#export-csv-btn').find('span').next().text('Export "' + jobText + '" to CSV');
                    $('#export-excel-btn').find('span').next().text('Export "' + jobText + '" to Excel');
                } else {
                    $('#export-csv-btn').find('span').next().text('<?php _e('Export to CSV', 'job-system'); ?>');
                    $('#export-excel-btn').find('span').next().text('<?php _e('Export to Excel', 'job-system'); ?>');
                }
            });
            
            // Status update with email notification
            $('.application-status').on('change', function() {
                var $select = $(this);
                var applicationId = $select.data('id');
                var newStatus = $select.val();
                var oldStatus = $select.find('option:selected').siblings().filter(function() {
                    return $(this).prop('selected');
                }).val() || 'pending';
                var applicantEmail = $select.data('email');
                var applicantName = $select.data('name');
                var jobTitle = $select.data('job');
                
                // Show loader
                $('.status-update-loader[data-id="' + applicationId + '"]').show();
                $select.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_application_status',
                        application_id: applicationId,
                        status: newStatus,
                        old_status: oldStatus,
                        applicant_email: applicantEmail,
                        applicant_name: applicantName,
                        job_title: jobTitle,
                        nonce: '<?php echo wp_create_nonce('job_system_nonce'); ?>'
                    },
                    success: function(response) {
                        $('.status-update-loader[data-id="' + applicationId + '"]').hide();
                        $select.prop('disabled', false);
                        
                        if (response.success) {
                            // Show success message
                            $('<div class="notice notice-success is-dismissible" style="margin: 10px 0;"><p>' + response.data.message + '</p></div>')
                                .insertAfter('.wrap h1').delay(3000).fadeOut();
                        } else {
                            alert('Error updating status: ' + (response.data.message || 'Unknown error'));
                            // Revert selection
                            $select.val(oldStatus);
                        }
                    },
                    error: function() {
                        $('.status-update-loader[data-id="' + applicationId + '"]').hide();
                        $select.prop('disabled', false);
                        alert('Error updating status. Please try again.');
                        $select.val(oldStatus);
                    }
                });
            });
            
            // Quick edit
            $('.quick-edit-btn').on('click', function() {
                var data = $(this).data();
                $('#edit-id').val(data.id);
                $('#edit-name').val(data.name);
                $('#edit-email').val(data.email);
                $('#edit-phone').val(data.phone);
                $('#edit-notes').val(data.notes);
                $('#quick-edit-modal').show();
            });
            
            // Quick edit form submission
            $('#quick-edit-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    action: 'quick_edit_application',
                    id: $('#edit-id').val(),
                    phone: $('#edit-phone').val(),
                    notes: $('#edit-notes').val(),
                    nonce: '<?php echo wp_create_nonce('job_system_nonce'); ?>'
                };
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Refresh to show updated data
                        } else {
                            alert('Error updating application: ' + (response.data.message || 'Unknown error'));
                        }
                    },
                    error: function() {
                        alert('Error updating application. Please try again.');
                    }
                });
            });
        });
        
        function closeQuickEdit() {
            jQuery('#quick-edit-modal').hide();
        }
        
        // Close modal when clicking overlay
        jQuery(document).on('click', '.quick-edit-overlay', function(e) {
            if (e.target === this) {
                closeQuickEdit();
            }
        });
        </script>
        
        <style>
        .status-update-loader {
            display: inline-block;
            margin-left: 10px;
        }
        .quick-edit-btn {
            white-space: nowrap;
        }
        .application-status:disabled {
            opacity: 0.6;
        }
        </style>
        <?php
    }
    
    /**
     * Show admin notices for missing data
     */
    public function admin_notices() {
        $screen = get_current_screen();
        
        // Only show on job-related pages
        if (!$screen || (strpos($screen->id, 'job') === false && $screen->id !== 'dashboard')) {
            return;
        }
        
        // Check for missing locations
        $locations = get_terms(array(
            'taxonomy' => 'job_location',
            'hide_empty' => false,
            'number' => 1
        ));
        
        if (empty($locations) || is_wp_error($locations)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Job System:', 'job-system'); ?></strong>
                    <?php _e('No job locations found. ', 'job-system'); ?>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_location&post_type=job'); ?>">
                        <?php _e('Create locations now', 'job-system'); ?>
                    </a>
                    <?php _e(' or default locations will be created automatically.', 'job-system'); ?>
                </p>
            </div>
            <?php
        }
        
        // Check for missing departments
        $departments = get_terms(array(
            'taxonomy' => 'job_department',
            'hide_empty' => false,
            'number' => 1
        ));
        
        if (empty($departments) || is_wp_error($departments)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Job System:', 'job-system'); ?></strong>
                    <?php _e('No job departments found. ', 'job-system'); ?>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_department&post_type=job'); ?>">
                        <?php _e('Create departments now', 'job-system'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Handle CSV/Excel export for applications
     */
    public function handle_export_applications() {
        if (!isset($_GET['export']) || !in_array($_GET['export'], array('csv', 'excel'))) {
            return;
        }
        
        if (!isset($_GET['page']) || $_GET['page'] !== 'job-applications') {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        global $wpdb;
        
        // Check if job filter is applied
        $job_filter = isset($_GET['job_id']) && !empty($_GET['job_id']) ? intval($_GET['job_id']) : null;
        
        // Build query based on filter
        if ($job_filter) {
            $applications = $wpdb->get_results($wpdb->prepare(
                "SELECT a.*, j.post_title as job_title 
                 FROM {$wpdb->prefix}job_applications a 
                 LEFT JOIN {$wpdb->posts} j ON a.job_id = j.ID 
                 WHERE a.job_id = %d
                 ORDER BY a.application_date DESC",
                $job_filter
            ));
        } else {
            $applications = $wpdb->get_results(
                "SELECT a.*, j.post_title as job_title 
                 FROM {$wpdb->prefix}job_applications a 
                 LEFT JOIN {$wpdb->posts} j ON a.job_id = j.ID 
                 ORDER BY a.application_date DESC"
            );
        }
        
        $export_type = $_GET['export'];
        
        // Create filename with job filter info
        if ($job_filter) {
            $job_title = $applications[0]->job_title ?? 'job-' . $job_filter;
            $job_slug = sanitize_title($job_title);
            $filename = 'job-applications-' . $job_slug . '-' . date('Y-m-d-H-i-s');
        } else {
            $filename = 'job-applications-all-' . date('Y-m-d-H-i-s');
        }
        
        if ($export_type === 'csv') {
            $this->export_csv($applications, $filename, $job_filter);
        } else {
            $this->export_excel($applications, $filename, $job_filter);
        }
    }
    
    /**
     * Export applications as CSV
     */
    private function export_csv($applications, $filename, $job_filter = null) {
        $filename .= '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add export info header if filtered
        if ($job_filter && !empty($applications)) {
            fputcsv($output, array('Job Applications Export'));
            fputcsv($output, array('Job: ' . $applications[0]->job_title));
            fputcsv($output, array('Export Date: ' . date('Y-m-d H:i:s')));
            fputcsv($output, array('Total Applications: ' . count($applications)));
            fputcsv($output, array('')); // Empty row
        }
        
        // CSV Headers
        $headers = array(
            'Applicant Name',
            'Email',
            'Phone',
            'Job Title',
            'Application Date',
            'Status',
            'CV Download Link',
            'Admin Notes'
        );
        
        fputcsv($output, $headers);
        
        // CSV Data
        foreach ($applications as $application) {
            // Create proper hyperlink for CV
            $cv_link = '';
            if ($application->cv_file_path) {
                $cv_link = $application->cv_file_path;
            }
            
            $row = array(
                $application->applicant_name,
                $application->applicant_email,
                $application->applicant_phone,
                $application->job_title,
                date('Y-m-d H:i:s', strtotime($application->application_date)),
                ucfirst($application->status),
                $cv_link,
                $application->admin_notes ?? ''
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export applications as Excel-compatible CSV
     */
    private function export_excel($applications, $filename, $job_filter = null) {
        $filename .= '.xls';
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        
        // Add export info if filtered
        if ($job_filter && !empty($applications)) {
            echo '<h2>Job Applications Export</h2>';
            echo '<p><strong>Job:</strong> ' . esc_html($applications[0]->job_title) . '</p>';
            echo '<p><strong>Export Date:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            echo '<p><strong>Total Applications:</strong> ' . count($applications) . '</p>';
            echo '<br>';
        }
        
        // Excel HTML Table
        echo '<table border="1">';
        
        // Headers
        echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
        echo '<td>Applicant Name</td>';
        echo '<td>Email</td>';
        echo '<td>Phone</td>';
        echo '<td>Job Title</td>';
        echo '<td>Application Date</td>';
        echo '<td>Status</td>';
        echo '<td>CV Download</td>';
        echo '<td>Admin Notes</td>';
        echo '</tr>';
        
        // Data rows
        foreach ($applications as $application) {
            echo '<tr>';
            echo '<td>' . esc_html($application->applicant_name) . '</td>';
            echo '<td>' . esc_html($application->applicant_email) . '</td>';
            echo '<td>' . esc_html($application->applicant_phone) . '</td>';
            echo '<td>' . esc_html($application->job_title) . '</td>';
            echo '<td>' . esc_html(date('Y-m-d H:i:s', strtotime($application->application_date))) . '</td>';
            echo '<td>' . esc_html(ucfirst($application->status)) . '</td>';
            
            // Create hyperlink for CV file
            if ($application->cv_file_path) {
                echo '<td><a href="' . esc_url($application->cv_file_path) . '" target="_blank">Download Now</a></td>';
            } else {
                echo '<td>No CV uploaded</td>';
            }
            
            echo '<td>' . esc_html($application->admin_notes ?? '') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    /**
     * Display edit application form
     */
    public function edit_application_form() {
        global $wpdb;
        
        $application_id = intval($_GET['id']);
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, j.post_title as job_title 
             FROM {$wpdb->prefix}job_applications a 
             LEFT JOIN {$wpdb->posts} j ON a.job_id = j.ID 
             WHERE a.id = %d", 
            $application_id
        ));
        
        if (!$application) {
            wp_die(__('Application not found.', 'job-system'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Edit Job Application', 'job-system'); ?></h1>
            
            <a href="<?php echo admin_url('edit.php?post_type=job&page=job-applications'); ?>" class="button">
                ← <?php _e('Back to Applications', 'job-system'); ?>
            </a>
            
            <form method="post" action="" style="margin-top: 20px;">
                <?php wp_nonce_field('edit_application_' . $application_id, 'edit_application_nonce'); ?>
                <input type="hidden" name="application_id" value="<?php echo esc_attr($application_id); ?>">
                <input type="hidden" name="action" value="update_application">
                
                <table class="form-table" style="background: white; padding: 20px; border: 1px solid #ddd; margin-top: 20px;">
                    <tr>
                        <th scope="row"><?php _e('Applicant Name', 'job-system'); ?></th>
                        <td>
                            <input type="text" name="applicant_name" value="<?php echo esc_attr($application->applicant_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email', 'job-system'); ?></th>
                        <td>
                            <input type="email" name="applicant_email" value="<?php echo esc_attr($application->applicant_email); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Phone', 'job-system'); ?></th>
                        <td>
                            <input type="text" name="applicant_phone" value="<?php echo esc_attr($application->applicant_phone); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Job', 'job-system'); ?></th>
                        <td>
                            <strong><?php echo esc_html($application->job_title); ?></strong>
                            <input type="hidden" name="job_id" value="<?php echo esc_attr($application->job_id); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('CV File', 'job-system'); ?></th>
                        <td>
                            <?php if ($application->cv_file_path): ?>
                                <a href="<?php echo esc_url($application->cv_file_path); ?>" target="_blank" class="button">
                                    <span class="dashicons dashicons-media-document"></span>
                                    <?php _e('Download CV', 'job-system'); ?>
                                </a>
                            <?php else: ?>
                                <em><?php _e('No CV uploaded', 'job-system'); ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Application Date', 'job-system'); ?></th>
                        <td>
                            <strong><?php echo esc_html(date('Y-m-d H:i:s', strtotime($application->application_date))); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Status', 'job-system'); ?></th>
                        <td>
                            <select name="status" class="regular-text">
                                <option value="pending" <?php selected($application->status, 'pending'); ?>><?php _e('Pending', 'job-system'); ?></option>
                                <option value="reviewed" <?php selected($application->status, 'reviewed'); ?>><?php _e('Reviewed', 'job-system'); ?></option>
                                <option value="accepted" <?php selected($application->status, 'accepted'); ?>><?php _e('Accepted', 'job-system'); ?></option>
                                <option value="rejected" <?php selected($application->status, 'rejected'); ?>><?php _e('Rejected', 'job-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Admin Notes', 'job-system'); ?></th>
                        <td>
                            <textarea name="admin_notes" rows="5" cols="50" class="large-text" placeholder="<?php _e('Add notes about this application...', 'job-system'); ?>"><?php echo esc_textarea($application->admin_notes ?? ''); ?></textarea>
                            <p class="description"><?php _e('Internal notes visible only to administrators.', 'job-system'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="<?php _e('Update Application', 'job-system'); ?>">
                    <a href="<?php echo admin_url('edit.php?post_type=job&page=job-applications'); ?>" class="button"><?php _e('Cancel', 'job-system'); ?></a>
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Handle application editing
     */
    public function handle_edit_application() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'update_application') {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }
        
        $application_id = intval($_POST['application_id']);
        
        if (!wp_verify_nonce($_POST['edit_application_nonce'], 'edit_application_' . $application_id)) {
            wp_die(__('Security check failed.'));
        }
        
        global $wpdb;
        
        // First check if admin_notes column exists, if not add it
        $table_name = $wpdb->prefix . 'job_applications';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'admin_notes'");
        
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN admin_notes TEXT");
        }
        
        $result = $wpdb->update(
            $table_name,
            array(
                'applicant_name' => sanitize_text_field($_POST['applicant_name']),
                'applicant_email' => sanitize_email($_POST['applicant_email']),
                'applicant_phone' => sanitize_text_field($_POST['applicant_phone']),
                'status' => sanitize_text_field($_POST['status']),
                'admin_notes' => sanitize_textarea_field($_POST['admin_notes'])
            ),
            array('id' => $application_id),
            array('%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Send email notification to applicant about status change
            $this->send_status_update_email($application_id, sanitize_text_field($_POST['status']));
            
            wp_redirect(admin_url('edit.php?post_type=job&page=job-applications&updated=1'));
            exit;
        } else {
            wp_die(__('Failed to update application.'));
        }
    }
    
    /**
     * AJAX handler for updating application status
     */
    public function ajax_update_application_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'job_system_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $application_id = intval($_POST['application_id']);
        $status = sanitize_text_field($_POST['status']);
        
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'job_applications',
            array('status' => $status),
            array('id' => $application_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // Send email notification to applicant about status change
            $this->send_status_update_email($application_id, $status);
            
            wp_send_json_success(array('message' => 'Status updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update status'));
        }
    }
    
    /**
     * Send status update email to applicant
     */
    private function send_status_update_email($application_id, $new_status) {
        global $wpdb;
        
        // Get application details
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, j.post_title as job_title 
             FROM {$wpdb->prefix}job_applications a 
             LEFT JOIN {$wpdb->posts} j ON a.job_id = j.ID 
             WHERE a.id = %d", 
            $application_id
        ));
        
        if (!$application) {
            return false;
        }
        
        $company_name = get_bloginfo('name');
        $applicant_name = $application->applicant_name;
        $applicant_email = $application->applicant_email;
        $job_title = $application->job_title;
        
        // Determine email content based on status
        switch ($new_status) {
            case 'reviewed':
                $subject = sprintf(__('Application Update: %s - Under Review', 'job-system'), $job_title);
                $status_message = __('Your application is currently under review by our HR team.', 'job-system');
                $status_color = '#007cba';
                $status_icon = '🔍';
                break;
                
            case 'accepted':
                $subject = sprintf(__('Congratulations! Application Accepted: %s', 'job-system'), $job_title);
                $status_message = __('Congratulations! We are pleased to inform you that your application has been accepted.', 'job-system');
                $status_color = '#00a32a';
                $status_icon = '🎉';
                break;
                
            case 'rejected':
                $subject = sprintf(__('Application Update: %s', 'job-system'), $job_title);
                $status_message = __('Thank you for your interest. After careful consideration, we have decided to move forward with other candidates.', 'job-system');
                $status_color = '#d63638';
                $status_icon = '📋';
                break;
                
            case 'pending':
            default:
                $subject = sprintf(__('Application Update: %s - Received', 'job-system'), $job_title);
                $status_message = __('Your application has been received and is pending review.', 'job-system');
                $status_color = '#dba617';
                $status_icon = '⏳';
                break;
        }
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // HTML email template
        $html_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Application Status Update</h2>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>Dear <strong>{$applicant_name}</strong>,</p>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    We wanted to update you on the status of your application for the <strong style='color: #007cba;'>{$job_title}</strong> position at <strong>{$company_name}</strong>.
                </p>
                
                <div style='background-color: #f0f8ff; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid {$status_color}; text-align: center;'>
                    <h3 style='margin: 0 0 10px 0; font-size: 20px; color: {$status_color};'>
                        {$status_icon} " . ucfirst($new_status) . "
                    </h3>
                    <p style='margin: 0; font-size: 16px; color: #333; line-height: 1.5;'>
                        {$status_message}
                    </p>
                </div>";
        
        // Add specific content based on status
        if ($new_status === 'accepted') {
            $html_message .= "
                <div style='background-color: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #00a32a;'>
                    <h4 style='color: #00a32a; margin-top: 0;'>Next Steps:</h4>
                    <p style='margin: 5px 0; color: #333;'>
                        Our HR team will be in touch with you shortly to discuss the next steps in the hiring process.
                    </p>
                    <p style='margin: 5px 0; color: #333;'>
                        Please keep an eye on your email and phone for further communication.
                    </p>
                </div>";
        } elseif ($new_status === 'rejected') {
            $html_message .= "
                <div style='background-color: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #d63638;'>
                    <p style='margin: 5px 0; color: #333;'>
                        We appreciate the time and effort you put into your application. We encourage you to apply for future opportunities that match your skills and experience.
                    </p>
                    <p style='margin: 5px 0; color: #333;'>
                        We wish you the best of luck in your job search.
                    </p>
                </div>";
        } else {
            $html_message .= "
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    We will keep you updated as your application progresses through our review process.
                </p>";
        }
        
        $html_message .= "
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 14px; color: #777; text-align: center;'>
                    Best regards,<br>
                    <strong>HR Team</strong><br>
                    {$company_name}
                </p>
                
                <p style='font-size: 12px; color: #999; text-align: center; margin-top: 20px;'>
                    This is an automated message regarding your job application.
                </p>
            </div>
        </div>";
        
        // Send the email
        return wp_mail($applicant_email, $subject, $html_message, $headers);
    }
    
    /**
     * Test email page
     */
    public function test_email_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Test Email System', 'job-system'); ?></h1>
            
            <?php if (isset($_GET['sent'])): ?>
                <div class="notice notice-success">
                    <p><strong><?php _e('Test email sent successfully!', 'job-system'); ?></strong> <?php _e('Check your inbox.', 'job-system'); ?></p>
                    <?php if (isset($_GET['msg'])): ?>
                        <p><strong>Results:</strong> <?php echo esc_html(urldecode($_GET['msg'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="notice notice-error">
                    <p><strong><?php _e('Failed to send test email.', 'job-system'); ?></strong> <?php echo esc_html($_GET['error']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2><?php _e('Email Configuration Test', 'job-system'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <p><?php _e('Use this tool to test if your WordPress site can send emails properly.', 'job-system'); ?></p>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('test_email_nonce', 'test_email_nonce'); ?>
                        <input type="hidden" name="action" value="send_test_email">
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Test Email Type', 'job-system'); ?></th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="radio" name="test_type" value="simple" checked>
                                            <?php _e('Simple Test Email', 'job-system'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="test_type" value="application_received">
                                            <?php _e('Application Received Email', 'job-system'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="test_type" value="status_update">
                                            <?php _e('Status Update Email', 'job-system'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="test_type" value="admin_notification">
                                            <?php _e('Admin Notification Email', 'job-system'); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Send To', 'job-system'); ?></th>
                                <td>
                                    <input type="email" name="test_email" value="<?php echo esc_attr(get_option('admin_email')); ?>" class="regular-text" required>
                                    <p class="description"><?php _e('Email address to send the test to (defaults to admin email).', 'job-system'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="submit" class="button-primary" value="<?php _e('Send Test Email', 'job-system'); ?>">
                        </p>
                    </form>
                    
                    <!-- Quick PHP mail test -->
                    <hr style="margin: 30px 0;">
                    <h3><?php _e('Quick Server Test', 'job-system'); ?></h3>
                    <p><?php _e('Test basic server mail functionality:', 'job-system'); ?></p>
                    <form method="post" action="" style="display: inline;">
                        <?php wp_nonce_field('quick_mail_test', 'quick_mail_nonce'); ?>
                        <input type="hidden" name="action" value="quick_mail_test">
                        <input type="email" name="quick_email" value="<?php echo esc_attr(get_option('admin_email')); ?>" style="width: 250px;">
                        <input type="submit" class="button" value="<?php _e('Send Quick Test', 'job-system'); ?>">
                    </form>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2><?php _e('Email Configuration Info', 'job-system'); ?></h2>
                </div>
                <div class="inside" style="padding: 20px;">
                    <table class="widefat">
                        <tr>
                            <td><strong><?php _e('WordPress Mail Function:', 'job-system'); ?></strong></td>
                            <td><?php echo function_exists('wp_mail') ? '<span style="color: green;">✓ Available</span>' : '<span style="color: red;">✗ Not Available</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('PHP Mail Function:', 'job-system'); ?></strong></td>
                            <td><?php echo function_exists('mail') ? '<span style="color: green;">✓ Available</span>' : '<span style="color: red;">✗ Not Available</span>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Admin Email:', 'job-system'); ?></strong></td>
                            <td><?php echo esc_html(get_option('admin_email')); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Site URL:', 'job-system'); ?></strong></td>
                            <td><?php echo esc_html(get_site_url()); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('From Email:', 'job-system'); ?></strong></td>
                            <td><?php echo esc_html('wordpress@' . parse_url(get_site_url(), PHP_URL_HOST)); ?></td>
                        </tr>
                    </table>
                    
                    <h4 style="margin-top: 20px;"><?php _e('Troubleshooting Tips:', 'job-system'); ?></h4>
                    <ul style="margin-left: 20px;">
                        <li><?php _e('Check your spam/junk folder for test emails', 'job-system'); ?></li>
                        <li><?php _e('Verify your hosting provider allows email sending', 'job-system'); ?></li>
                        <li><?php _e('Consider using an SMTP plugin for better email delivery', 'job-system'); ?></li>
                        <li><?php _e('Check server error logs for email-related errors', 'job-system'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle test email sending
     */
    public function handle_test_email() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'send_test_email') {
            return;
        }
        
        if (!wp_verify_nonce($_POST['test_email_nonce'], 'test_email_nonce')) {
            wp_die(__('Security check failed.'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }
        
        $test_email = sanitize_email($_POST['test_email']);
        $test_type = sanitize_text_field($_POST['test_type']);
        
        if (!is_email($test_email)) {
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&error=' . urlencode('Invalid email address')));
            exit;
        }
        
        $result = false;
        $error_message = '';
        
        // Enable WordPress debug for email
        add_action('wp_mail_failed', function($wp_error) use (&$error_message) {
            $error_message = $wp_error->get_error_message();
        });
        
        switch ($test_type) {
            case 'simple':
                $result = $this->send_simple_test_email($test_email);
                break;
            case 'application_received':
                $result = $this->send_test_application_received_email($test_email);
                break;
            case 'status_update':
                $result = $this->send_test_status_update_email($test_email);
                break;
            case 'admin_notification':
                $result = $this->send_test_admin_notification_email($test_email);
                break;
        }
        
        if ($result) {
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&sent=1'));
        } else {
            $final_error = $error_message ? $error_message : 'wp_mail() returned false - check server configuration';
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&error=' . urlencode($final_error)));
        }
        exit;
    }
    
    /**
     * Handle quick mail test
     */
    public function handle_quick_mail_test() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'quick_mail_test') {
            return;
        }
        
        if (!wp_verify_nonce($_POST['quick_mail_nonce'], 'quick_mail_test')) {
            wp_die('Security check failed');
        }
        
        $test_email = sanitize_email($_POST['quick_email']);
        
        if (!is_email($test_email)) {
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&error=' . urlencode('Invalid email address')));
            exit;
        }
        
        // Try both wp_mail and PHP mail
        $wp_result = wp_mail($test_email, 'Quick WP Mail Test', 'This is a quick test using wp_mail() function.');
        
        $php_result = false;
        if (function_exists('mail')) {
            $php_result = mail($test_email, 'Quick PHP Mail Test', 'This is a quick test using PHP mail() function.');
        }
        
        $message = 'WP Mail: ' . ($wp_result ? 'SUCCESS' : 'FAILED');
        $message .= ' | PHP Mail: ' . ($php_result ? 'SUCCESS' : 'FAILED');
        
        if ($wp_result || $php_result) {
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&sent=1&msg=' . urlencode($message)));
        } else {
            wp_redirect(admin_url('edit.php?post_type=job&page=job-test-email&error=' . urlencode('Both methods failed: ' . $message)));
        }
        exit;
    }
    
    /**
     * Handle status update via AJAX
     */
    public function handle_status_update() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'job_system_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['status']);
        $old_status = sanitize_text_field($_POST['old_status']);
        $applicant_email = sanitize_email($_POST['applicant_email']);
        $applicant_name = sanitize_text_field($_POST['applicant_name']);
        $job_title = sanitize_text_field($_POST['job_title']);
        
        // Update status in database
        $updated = $wpdb->update(
            $wpdb->prefix . 'job_applications',
            array('status' => $new_status),
            array('id' => $application_id),
            array('%s'),
            array('%d')
        );
        
        if ($updated === false) {
            wp_send_json_error(array('message' => 'Failed to update status in database'));
            return;
        }
        
        // Send email notification if status changed
        if ($old_status !== $new_status && $applicant_email) {
            $email_sent = $this->send_status_change_email($applicant_email, $applicant_name, $job_title, $new_status);
            
            if ($email_sent) {
                wp_send_json_success(array('message' => 'Status updated successfully and email sent to applicant!'));
            } else {
                wp_send_json_success(array('message' => 'Status updated successfully but failed to send email notification.'));
            }
        } else {
            wp_send_json_success(array('message' => 'Status updated successfully!'));
        }
    }
    
    /**
     * Handle quick edit via AJAX
     */
    public function handle_quick_edit() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'job_system_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        
        $application_id = intval($_POST['id']);
        $phone = sanitize_text_field($_POST['phone']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        // Update application in database
        $updated = $wpdb->update(
            $wpdb->prefix . 'job_applications',
            array(
                'applicant_phone' => $phone,
                'admin_notes' => $notes
            ),
            array('id' => $application_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($updated === false) {
            wp_send_json_error(array('message' => 'Failed to update application'));
            return;
        }
        
        wp_send_json_success(array('message' => 'Application updated successfully!'));
    }
    
    /**
     * Send status change email notification
     */
    private function send_status_change_email($email, $name, $job_title, $status) {
        $company_name = get_bloginfo('name');
        $subject = '';
        $status_message = '';
        $status_class = '';
        
        switch ($status) {
            case 'reviewed':
                $subject = "Application Update: {$job_title} - Under Review";
                $status_message = "Your application is currently being reviewed by our team.";
                $status_class = 'reviewed';
                break;
            case 'accepted':
                $subject = "Great News! Application Accepted: {$job_title}";
                $status_message = "🎉 Congratulations! We are pleased to inform you that your application has been accepted.";
                $status_class = 'accepted';
                break;
            case 'rejected':
                $subject = "Application Update: {$job_title}";
                $status_message = "Thank you for your interest. After careful consideration, we have decided to move forward with other candidates.";
                $status_class = 'rejected';
                break;
            default:
                return false; // Don't send email for 'pending' or unknown status
        }
        
        $html_message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Application Status Update</h2>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    Dear <strong>{$name}</strong>,
                </p>
                
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    We wanted to update you on the status of your application for the <strong style='color: #007cba;'>{$job_title}</strong> position at <strong>{$company_name}</strong>.
                </p>
                
                <div style='background-color: " . ($status_class === 'accepted' ? '#f0fdf4' : ($status_class === 'rejected' ? '#fef2f2' : '#f0f8ff')) . "; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid " . ($status_class === 'accepted' ? '#00a32a' : ($status_class === 'rejected' ? '#d63638' : '#007cba')) . "; text-align: center;'>
                    <h3 style='margin: 0 0 10px 0; font-size: 20px; color: " . ($status_class === 'accepted' ? '#00a32a' : ($status_class === 'rejected' ? '#d63638' : '#007cba')) . ";'>" . ucfirst($status) . "</h3>
                    <p style='margin: 0; font-size: 16px; color: #333; line-height: 1.5;'>
                        {$status_message}
                    </p>
                </div>";
        
        // Add specific content based on status
        if ($status === 'accepted') {
            $html_message .= "
                <div style='background-color: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #00a32a;'>
                    <h4 style='color: #00a32a; margin-top: 0;'>Next Steps:</h4>
                    <p style='margin: 5px 0; color: #333;'>
                        Our HR team will be in touch with you shortly to discuss the next steps in the hiring process.
                    </p>
                    <p style='margin: 5px 0; color: #333;'>
                        Please keep an eye on your email and phone for further communication.
                    </p>
                </div>";
        } elseif ($status === 'rejected') {
            $html_message .= "
                <div style='background-color: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #d63638;'>
                    <p style='margin: 5px 0; color: #333;'>
                        We appreciate the time and effort you put into your application. We encourage you to apply for future opportunities that match your skills and experience.
                    </p>
                    <p style='margin: 5px 0; color: #333;'>
                        We wish you the best of luck in your job search.
                    </p>
                </div>";
        } else {
            $html_message .= "
                <p style='font-size: 16px; line-height: 1.6; color: #555;'>
                    We will keep you updated as your application progresses through our review process.
                </p>";
        }
        
        $html_message .= "
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                
                <p style='font-size: 14px; color: #777; text-align: center;'>
                    Best regards,<br>
                    <strong>HR Team</strong><br>
                    {$company_name}
                </p>
            </div>
        </div>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($email, $subject, $html_message, $headers);
    }
    
    /**
     * Send simple test email
     */
    private function send_simple_test_email($email) {
        $subject = 'WordPress Email Test - ' . get_bloginfo('name');
        $message = "
        <h2>Email Test Successful!</h2>
        <p>This is a test email from your WordPress site: <strong>" . get_bloginfo('name') . "</strong></p>
        <p>If you received this email, your WordPress mail system is working correctly.</p>
        <p>Sent at: " . current_time('Y-m-d H:i:s') . "</p>
        <p>From: " . get_option('admin_email') . "</p>
        <p>To: " . $email . "</p>
        ";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Add debug logging
        error_log('Job System: Attempting to send test email to ' . $email);
        $result = wp_mail($email, $subject, $message, $headers);
        error_log('Job System: wp_mail result: ' . ($result ? 'true' : 'false'));
        
        return $result;
    }
    
    /**
     * Send test application received email
     */
    private function send_test_application_received_email($email) {
        $subject = 'Thank you for applying to Test Position';
        $company_name = get_bloginfo('name');
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Thank You for Applying!</h2>
                <p>Dear <strong>Test Applicant</strong>,</p>
                <p>Thank you for applying to the <strong style='color: #007cba;'>Test Position</strong> at <strong>{$company_name}</strong>.</p>
                <div style='background-color: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007cba;'>
                    <p style='margin: 0; font-size: 16px; color: #333;'>
                        ✅ We have successfully received your application and it will be reviewed by our HR team.
                    </p>
                </div>
                <p>This is a <strong>TEST EMAIL</strong> from the Job System plugin.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                <p style='font-size: 14px; color: #777; text-align: center;'>
                    Best regards,<br><strong>HR Team</strong><br>{$company_name}
                </p>
            </div>
        </div>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Send test status update email
     */
    private function send_test_status_update_email($email) {
        $subject = 'Application Update: Test Position - Accepted';
        $company_name = get_bloginfo('name');
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Application Status Update</h2>
                <p>Dear <strong>Test Applicant</strong>,</p>
                <p>We wanted to update you on the status of your application for the <strong style='color: #007cba;'>Test Position</strong> at <strong>{$company_name}</strong>.</p>
                <div style='background-color: #f0f8ff; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #00a32a; text-align: center;'>
                    <h3 style='margin: 0 0 10px 0; font-size: 20px; color: #00a32a;'>🎉 Accepted</h3>
                    <p style='margin: 0; font-size: 16px; color: #333;'>Congratulations! We are pleased to inform you that your application has been accepted.</p>
                </div>
                <p>This is a <strong>TEST EMAIL</strong> from the Job System plugin.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                <p style='font-size: 14px; color: #777; text-align: center;'>
                    Best regards,<br><strong>HR Team</strong><br>{$company_name}
                </p>
            </div>
        </div>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Send test admin notification email
     */
    private function send_test_admin_notification_email($email) {
        $subject = 'New Job Application: Test Position';
        $company_name = get_bloginfo('name');
        $application_time = current_time('Y-m-d H:i:s');
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
            <div style='background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #333; text-align: center; margin-bottom: 30px; background: linear-gradient(45deg, #007cba, #00a0d2); color: white; padding: 15px; border-radius: 8px;'>
                    🔔 New Job Application Received
                </h2>
                <div style='background-color: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #007cba;'>
                    <h3 style='color: #007cba; margin-top: 0;'>Job Details:</h3>
                    <p><strong>Position:</strong> Test Position</p>
                    <p><strong>Application Date:</strong> {$application_time}</p>
                </div>
                <div style='background-color: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #333; margin-top: 0;'>Applicant Information:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Name:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>Test Applicant</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Email:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>test@example.com</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Phone:</td>
                            <td style='padding: 8px; border-bottom: 1px solid #eee;'>+1234567890</td>
                        </tr>
                    </table>
                </div>
                <p>This is a <strong>TEST EMAIL</strong> from the Job System plugin.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
                <p style='font-size: 12px; color: #777; text-align: center;'>
                    This is an automated message from the Job Management System at {$company_name}
                </p>
            </div>
        </div>";
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($email, $subject, $message, $headers);
    }
}
