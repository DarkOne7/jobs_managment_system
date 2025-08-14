<?php
/**
 * Applications List Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle messages
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$current_job = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Get all jobs for filter dropdown
$all_jobs = JMS_DB_Helper::get_jobs(array('status' => ''));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Job Applications</h1>
    <div class="export-buttons">
        <?php 
        $export_params = array();
        if ($current_status) $export_params['status'] = $current_status;
        if ($current_job) $export_params['job_id'] = $current_job;
        $export_query = http_build_query($export_params);
        ?>
        <a href="<?php echo admin_url('admin.php?page=jms_applications&action=export_xlsx&' . $export_query); ?>" 
           class="page-title-action">
            <i class="fas fa-file-excel"></i> Export to XLSX
        </a>
        <a href="<?php echo admin_url('admin.php?page=jms_applications&action=export&' . $export_query); ?>" 
           class="page-title-action">
            <i class="fas fa-file-csv"></i> Export to CSV
        </a>
    </div>
    
    <?php if ($message): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Filter Form -->
    <div class="jms-filter-form">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="jms_applications">
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="job_filter">Filter by Job:</label>
                    <select name="job_id" id="job_filter">
                        <option value="">All Jobs</option>
                        <?php foreach ($all_jobs as $job): ?>
                        <option value="<?php echo esc_attr($job->id); ?>" 
                                <?php selected($current_job, $job->id); ?>>
                            <?php echo esc_html($job->name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status_filter">Filter by Status:</label>
                    <select name="status" id="status_filter">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php selected($current_status, 'pending'); ?>>Pending</option>
                        <option value="approved" <?php selected($current_status, 'approved'); ?>>Approved</option>
                        <option value="rejected" <?php selected($current_status, 'rejected'); ?>>Rejected</option>
                        <option value="shortlisted" <?php selected($current_status, 'shortlisted'); ?>>Shortlisted</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="button">Filter</button>
                    <a href="<?php echo admin_url('admin.php?page=jms_applications'); ?>" class="button">Clear</a>
                </div>
            </div>
        </form>
    </div>
    
    <?php if ($current_job > 0): 
        $selected_job = JMS_DB_Helper::get_job($current_job);
    ?>
    <div class="jms-current-filters">
        <h3>Showing applications for: <strong><?php echo esc_html($selected_job ? $selected_job->name : 'Selected Job'); ?></strong></h3>
    </div>
    <?php endif; ?>
    
    <div class="jms-applications-filter">
        <div class="subsubsub">
            <?php 
            $job_param = $current_job ? '&job_id=' . $current_job : '';
            $base_filter_args = $current_job ? ['job_id' => $current_job] : [];
            ?>
            <a href="<?php echo admin_url('admin.php?page=jms_applications' . $job_param); ?>" 
               class="<?php echo empty($current_status) ? 'current' : ''; ?>">
                All <span class="count">(<?php echo count(JMS_DB_Helper::get_applications($base_filter_args)); ?>)</span>
            </a> |
            <a href="<?php echo admin_url('admin.php?page=jms_applications&status=pending' . $job_param); ?>" 
               class="<?php echo $current_status === 'pending' ? 'current' : ''; ?>">
                Pending <span class="count">(<?php echo count(JMS_DB_Helper::get_applications(array_merge($base_filter_args, ['status' => 'pending']))); ?>)</span>
            </a> |
            <a href="<?php echo admin_url('admin.php?page=jms_applications&status=approved' . $job_param); ?>" 
               class="<?php echo $current_status === 'approved' ? 'current' : ''; ?>">
                Approved <span class="count">(<?php echo count(JMS_DB_Helper::get_applications(array_merge($base_filter_args, ['status' => 'approved']))); ?>)</span>
            </a> |
            <a href="<?php echo admin_url('admin.php?page=jms_applications&status=rejected' . $job_param); ?>" 
               class="<?php echo $current_status === 'rejected' ? 'current' : ''; ?>">
                Rejected <span class="count">(<?php echo count(JMS_DB_Helper::get_applications(array_merge($base_filter_args, ['status' => 'rejected']))); ?>)</span>
            </a> |
            <a href="<?php echo admin_url('admin.php?page=jms_applications&status=shortlisted' . $job_param); ?>" 
               class="<?php echo $current_status === 'shortlisted' ? 'current' : ''; ?>">
                Shortlisted <span class="count">(<?php echo count(JMS_DB_Helper::get_applications(array_merge($base_filter_args, ['status' => 'shortlisted']))); ?>)</span>
            </a>
        </div>
    </div>
    
    <div class="jms-applications-list">
        <?php if (!empty($applications)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="column-applicant">Applicant</th>
                    <th scope="col" class="column-job">Job</th>
                    <th scope="col" class="column-contact">Contact</th>
                    <th scope="col" class="column-cv">CV</th>
                    <th scope="col" class="column-status">Status</th>
                    <th scope="col" class="column-date">Applied</th>
                    <th scope="col" class="column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application): ?>
                <tr>
                    <td class="column-applicant">
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=jms_applications&action=view&id=' . $application->id); ?>">
                                <?php echo esc_html($application->name); ?>
                            </a>
                        </strong>
                        <?php if ($application->cover_letter): ?>
                        <br><small class="description">Has cover letter</small>
                        <?php endif; ?>
                        <div class="row-actions">
                            <span class="view">
                                <a href="<?php echo admin_url('admin.php?page=jms_applications&action=view&id=' . $application->id); ?>">View Details</a> |
                            </span>
                            <span class="delete">
                                <a href="<?php echo admin_url('admin.php?page=jms_applications&action=delete&id=' . $application->id); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this application?');" 
                                   class="submitdelete">Delete</a>
                            </span>
                        </div>
                    </td>
                    <td class="column-job">
                        <?php if (!empty($application->job_name)): ?>
                            <a href="<?php echo home_url('/jobs/' . sanitize_title($application->job_name)); ?>" target="_blank">
                                <?php echo esc_html($application->job_name); ?>
                            </a>
                        <?php else: ?>
                            <span class="description">Job not found</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-contact">
                        <div class="contact-info">
                            <div class="email">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo esc_attr($application->email); ?>">
                                    <?php echo esc_html($application->email); ?>
                                </a>
                            </div>
                            <?php if ($application->phone): ?>
                            <div class="phone">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo esc_attr($application->phone); ?>">
                                    <?php echo esc_html($application->phone); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="column-cv">
                        <?php if ($application->cv_file): ?>
                            <a href="<?php echo esc_url(JMS_UPLOAD_URL . $application->cv_file); ?>" 
                               target="_blank" 
                               class="cv-link">
                                <i class="fas fa-file-pdf"></i>
                                Download CV
                            </a>
                        <?php else: ?>
                            <span class="description">No CV</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-status">
                        <span class="jms-status jms-status-<?php echo esc_attr($application->status); ?>">
                            <?php 
                            $statuses = [
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'shortlisted' => 'Shortlisted'
                            ];
                            echo esc_html($statuses[$application->status] ?? ucfirst($application->status));
                            ?>
                        </span>
                    </td>
                    <td class="column-date">
                        <?php echo date('M j, Y', strtotime($application->created_at)); ?><br>
                        <small class="description"><?php echo date('g:i A', strtotime($application->created_at)); ?></small>
                    </td>
                    <td class="column-actions">
                        <div class="application-actions">
                            <a href="<?php echo admin_url('admin.php?page=jms_applications&action=view&id=' . $application->id); ?>" 
                               class="button button-small">View</a>
                            
                            <?php if ($application->status === 'pending'): ?>
                            <div class="quick-actions">
                                <button class="button button-small button-approve" 
                                        data-id="<?php echo $application->id; ?>" 
                                        title="Approve Application">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="button button-small button-reject" 
                                        data-id="<?php echo $application->id; ?>" 
                                        title="Reject Application">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="jms-empty-state">
            <div class="jms-empty-icon">
                <i class="fas fa-users fa-3x"></i>
            </div>
            <h3>No Applications Found</h3>
            <?php if ($current_status): ?>
            <p>No applications with status "<?php echo esc_html(ucfirst($current_status)); ?>" found.</p>
            <a href="<?php echo admin_url('admin.php?page=jms_applications'); ?>" class="button">View All Applications</a>
            <?php else: ?>
            <p>No job applications have been submitted yet. Once people start applying for jobs, they will appear here.</p>
            <a href="<?php echo admin_url('admin.php?page=jms_jobs'); ?>" class="button button-primary">Manage Jobs</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.jms-filter-form {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.filter-row {
    display: flex;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    font-size: 13px;
    color: #23282d;
}

.filter-group select {
    min-width: 200px;
    height: 32px;
    padding: 4px 8px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
}

.filter-group .button {
    height: 32px;
    margin-right: 5px;
}

.jms-current-filters {
    background: #e7f3ff;
    border-left: 4px solid #0073aa;
    padding: 10px 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.jms-current-filters h3 {
    margin: 0;
    font-size: 14px;
    color: #0073aa;
}

.jms-applications-filter {
    margin-bottom: 20px;
}

.column-applicant {
    width: 180px;
}

.column-job {
    width: 200px;
}

.column-contact {
    width: 200px;
}

.column-cv {
    width: 100px;
}

.column-status {
    width: 120px;
}

.column-date {
    width: 100px;
}

.column-actions {
    width: 120px;
}

.contact-info {
    font-size: 0.9rem;
}

.contact-info div {
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.contact-info i {
    width: 12px;
    color: #666;
}

.cv-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #007cba;
    text-decoration: none;
    font-size: 0.9rem;
}

.cv-link:hover {
    text-decoration: underline;
}

.jms-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.jms-status-pending {
    background: #fff3cd;
    color: #856404;
}

.jms-status-approved {
    background: #d4edda;
    color: #155724;
}

.jms-status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.jms-status-shortlisted {
    background: #d1ecf1;
    color: #0c5460;
}

.application-actions {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.quick-actions {
    display: flex;
    gap: 3px;
}

.button-approve {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.button-approve:hover {
    background: #218838;
    color: white;
}

.button-reject {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.button-reject:hover {
    background: #c82333;
    color: white;
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

.export-buttons {
    display: inline-block;
    margin-left: 10px;
}

.export-buttons .page-title-action {
    margin-left: 5px;
}

.export-buttons .page-title-action i {
    margin-right: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Quick approve/reject actions
    $('.button-approve').on('click', function() {
        if (confirm('Approve this application?')) {
            // Quick approve action would go here
            alert('Quick approve functionality to be implemented');
        }
    });
    
    $('.button-reject').on('click', function() {
        if (confirm('Reject this application?')) {
            // Quick reject action would go here
            alert('Quick reject functionality to be implemented');
        }
    });
});
</script>

