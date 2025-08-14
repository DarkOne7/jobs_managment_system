<?php
/**
 * Departments List Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle messages
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Departments</h1>
    <a href="<?php echo admin_url('admin.php?page=jms_departments&action=add'); ?>" class="page-title-action">Add New Department</a>
    
    <?php if ($message): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="jms-departments-list">
        <?php if (!empty($departments)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="column-icon">Icon</th>
                    <th scope="col" class="column-name">Name</th>
                    <th scope="col" class="column-slug">Slug</th>
                    <th scope="col" class="column-color">Color</th>
                    <th scope="col" class="column-jobs">Jobs</th>
                    <th scope="col" class="column-date">Created</th>
                    <th scope="col" class="column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $department): 
                    $jobs_count = JMS_DB_Helper::get_jobs(array('department_id' => $department->id, 'status' => 'active'));
                    $jobs_count = count($jobs_count);
                ?>
                <tr>
                    <td class="column-icon">
                        <i class="<?php echo esc_attr($department->icon ?: 'fas fa-briefcase'); ?>" 
                           style="color: <?php echo esc_attr($department->color ?: '#007cba'); ?>; font-size: 1.2rem;"></i>
                    </td>
                    <td class="column-name">
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=jms_departments&action=edit&id=' . $department->id); ?>">
                                <?php echo esc_html($department->name); ?>
                            </a>
                        </strong>
                        <?php if ($department->description): ?>
                        <br><small class="description"><?php echo esc_html(wp_trim_words($department->description, 10)); ?></small>
                        <?php endif; ?>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo admin_url('admin.php?page=jms_departments&action=edit&id=' . $department->id); ?>">Edit</a> |
                            </span>
                            <span class="view">
                                <a href="<?php echo home_url('/jobs/department/' . $department->slug); ?>" target="_blank">View</a> |
                            </span>
                            <span class="delete">
                                <a href="<?php echo admin_url('admin.php?page=jms_departments&action=delete&id=' . $department->id); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this department? This will also delete all associated jobs.');" 
                                   class="submitdelete">Delete</a>
                            </span>
                        </div>
                    </td>
                    <td class="column-slug">
                        <code><?php echo esc_html($department->slug); ?></code>
                    </td>
                    <td class="column-color">
                        <div class="color-preview" style="background-color: <?php echo esc_attr($department->color ?: '#007cba'); ?>"></div>
                        <code><?php echo esc_html($department->color ?: '#007cba'); ?></code>
                    </td>
                    <td class="column-jobs">
                        <?php if ($jobs_count > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=jms_jobs&department=' . $department->id); ?>">
                            <?php echo $jobs_count; ?> job<?php echo $jobs_count !== 1 ? 's' : ''; ?>
                        </a>
                        <?php else: ?>
                        <span class="description">No jobs</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-date">
                        <?php echo date('M j, Y', strtotime($department->created_at)); ?>
                    </td>
                    <td class="column-actions">
                        <a href="<?php echo admin_url('admin.php?page=jms_departments&action=edit&id=' . $department->id); ?>" 
                           class="button button-small">Edit</a>
                        <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=add&department=' . $department->id); ?>" 
                           class="button button-small">Add Job</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="jms-empty-state">
            <div class="jms-empty-icon">
                <i class="fas fa-building fa-3x"></i>
            </div>
            <h3>No Departments Found</h3>
            <p>Start by creating your first department to organize your job postings.</p>
            <a href="<?php echo admin_url('admin.php?page=jms_departments&action=add'); ?>" class="button button-primary">
                Add Your First Department
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.column-icon {
    width: 60px;
    text-align: center;
}

.column-color {
    width: 100px;
}

.column-jobs {
    width: 80px;
}

.column-date {
    width: 100px;
}

.column-actions {
    width: 120px;
}

.color-preview {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
    border: 1px solid #ddd;
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
</style>

