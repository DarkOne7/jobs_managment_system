<?php
/**
 * Jobs List Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle messages
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Jobs</h1>
    <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=add'); ?>" class="page-title-action">Add New Job</a>
    
    <?php if ($message): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="jms-jobs-list">
        <?php if (!empty($jobs)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="column-title">Job Title</th>
                    <th scope="col" class="column-department">Department</th>
                    <th scope="col" class="column-location">Location</th>
                    <th scope="col" class="column-type">Type</th>
                    <th scope="col" class="column-status">Status</th>
                    <th scope="col" class="column-deadline">Deadline</th>
                    <th scope="col" class="column-applications">Applications</th>
                    <th scope="col" class="column-date">Posted</th>
                    <th scope="col" class="column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): 
                    $applications_count = JMS_DB_Helper::get_applications(array('job_id' => $job->id));
                    $applications_count = count($applications_count);
                    $is_deadline_passed = !empty($job->application_deadline) && strtotime($job->application_deadline) < time();
                ?>
                <tr class="<?php echo $job->status !== 'active' ? 'inactive' : ''; ?>">
                    <td class="column-title">
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=edit&id=' . $job->id); ?>">
                                <?php echo esc_html($job->name); ?>
                            </a>
                        </strong>
                        <?php if ($job->description): ?>
                        <br><small class="description"><?php echo esc_html(wp_trim_words(strip_tags($job->description), 15)); ?></small>
                        <?php endif; ?>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=edit&id=' . $job->id); ?>">Edit</a> |
                            </span>
                            <span class="view">
                                <a href="<?php echo home_url('/jobs/' . $job->slug); ?>" target="_blank">View</a> |
                            </span>
                            <span class="delete">
                                <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=delete&id=' . $job->id); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this job? This will also delete all applications.');" 
                                   class="submitdelete">Delete</a>
                            </span>
                        </div>
                    </td>
                    <td class="column-department">
                        <?php if (!empty($job->department_name)): ?>
                            <span class="department-badge" style="color: <?php echo esc_attr($job->department_color ?: '#007cba'); ?>">
                                <i class="<?php echo esc_attr($job->department_icon ?: 'fas fa-briefcase'); ?>"></i>
                                <?php echo esc_html($job->department_name); ?>
                            </span>
                        <?php else: ?>
                            <span class="description">No department</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-location">
                        <?php echo esc_html($job->location_name ?: 'No location'); ?>
                    </td>
                    <td class="column-type">
                        <span class="work-type-badge badge-<?php echo esc_attr($job->work_type); ?>">
                            <?php 
                            $types = [
                                'full-time' => 'Full Time',
                                'part-time' => 'Part Time', 
                                'contract' => 'Contract',
                                'freelance' => 'Freelance',
                                'internship' => 'Internship'
                            ];
                            echo esc_html($types[$job->work_type] ?? ucfirst($job->work_type));
                            ?>
                        </span>
                    </td>
                    <td class="column-status">
                        <span class="status-badge status-<?php echo esc_attr($job->status); ?>">
                            <?php echo esc_html(ucfirst($job->status)); ?>
                        </span>
                    </td>
                    <td class="column-deadline">
                        <?php if ($job->application_deadline): ?>
                            <span class="deadline-date <?php echo $is_deadline_passed ? 'deadline-passed' : ''; ?>">
                                <?php echo date('M j, Y', strtotime($job->application_deadline)); ?>
                                <?php if ($is_deadline_passed): ?>
                                    <br><small style="color: #d63384;">Expired</small>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="description">No deadline</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-applications">
                        <?php if ($applications_count > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=jms_applications&job=' . $job->id); ?>">
                            <?php echo $applications_count; ?> application<?php echo $applications_count !== 1 ? 's' : ''; ?>
                        </a>
                        <?php else: ?>
                        <span class="description">No applications</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-date">
                        <?php echo date('M j, Y', strtotime($job->created_at)); ?>
                    </td>
                    <td class="column-actions">
                        <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=edit&id=' . $job->id); ?>" 
                           class="button button-small">Edit</a>
                        <?php if ($applications_count > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=jms_applications&job=' . $job->id); ?>" 
                           class="button button-small">Applications</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="jms-empty-state">
            <div class="jms-empty-icon">
                <i class="fas fa-briefcase fa-3x"></i>
            </div>
            <h3>No Jobs Found</h3>
            <p>Start by creating your first job posting. Make sure you have departments and locations set up first.</p>
            <div class="empty-state-actions">
                <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=add'); ?>" class="button button-primary">
                    Add Your First Job
                </a>
                <a href="<?php echo admin_url('admin.php?page=jms_departments'); ?>" class="button">
                    Manage Departments
                </a>
                <a href="<?php echo admin_url('admin.php?page=jms_locations'); ?>" class="button">
                    Manage Locations
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.column-department {
    width: 150px;
}

.column-location {
    width: 120px;
}

.column-type {
    width: 100px;
}

.column-status {
    width: 80px;
}

.column-deadline {
    width: 100px;
}

.column-applications {
    width: 100px;
}

.column-date {
    width: 80px;
}

.column-actions {
    width: 120px;
}

.department-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.work-type-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    color: white;
}

.badge-full-time { background: #28a745; }
.badge-part-time { background: #17a2b8; }
.badge-contract { background: #ffc107; color: #333; }
.badge-freelance { background: #6c757d; }
.badge-internship { background: #007bff; }

.status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-active { background: #d4edda; color: #155724; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-closed { background: #d1ecf1; color: #0c5460; }

.deadline-passed {
    color: #d63384;
}

.description {
    color: #666;
    font-style: italic;
}

.jms-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.jms-empty-icon {
    color: #ccc;
    margin-bottom: 20px;
}

.jms-empty-state h3 {
    margin-bottom: 10px;
    color: #333;
}

.jms-empty-state p {
    color: #666;
    margin-bottom: 20px;
}

.empty-state-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

tr.inactive {
    opacity: 0.6;
}
</style>

