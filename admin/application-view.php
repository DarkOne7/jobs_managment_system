<?php
/**
 * Application View Template
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$application) {
    echo '<div class="wrap"><h1>Application Not Found</h1><p>The requested application could not be found.</p></div>';
    return;
}

$statuses = [
    'pending' => 'Pending Review',
    'approved' => 'Approved',
    'rejected' => 'Rejected', 
    'shortlisted' => 'Shortlisted'
];
?>

<div class="wrap">
    <h1>Application Details</h1>
    
    <?php
    // Handle messages
    $message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
    if ($message): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="jms-application-view">
        <div class="application-header">
            <div class="applicant-info">
                <h2><?php echo esc_html($application->name); ?></h2>
                <p class="applicant-meta">
                    Applied for: <strong><?php echo esc_html($application->job_name ?? 'Unknown Job'); ?></strong><br>
                    Department: <strong><?php echo esc_html($application->department_name ?? 'Unknown'); ?></strong><br>
                    Applied on: <strong><?php echo $application->created_at ? date('F j, Y \a\t g:i A', strtotime($application->created_at)) : 'Unknown Date'; ?></strong>
                </p>
            </div>
            <div class="application-status">
                <div class="current-status">
                    <span class="jms-status jms-status-<?php echo esc_attr($application->status); ?>">
                        <?php echo esc_html($statuses[$application->status] ?? ucfirst($application->status)); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="application-content">
            <div class="application-main">
                <div class="info-section">
                    <h3>Contact Information</h3>
                    <table class="form-table">
                        <tr>
                            <th>Email:</th>
                            <td>
                                <a href="mailto:<?php echo esc_attr($application->email); ?>">
                                    <?php echo esc_html($application->email); ?>
                                </a>
                            </td>
                        </tr>
                        <?php if ($application->phone): ?>
                        <tr>
                            <th>Phone:</th>
                            <td>
                                <a href="tel:<?php echo esc_attr($application->phone); ?>">
                                    <?php echo esc_html($application->phone); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <?php if (!empty($application->cv_file)): ?>
                <div class="info-section">
                    <h3>CV/Resume</h3>
                    <div class="cv-section">
                        <a href="<?php echo esc_url(JMS_UPLOAD_URL . $application->cv_file); ?>" 
                           target="_blank" 
                           class="cv-download">
                            <i class="fas fa-file-pdf"></i>
                            <span>Download CV/Resume</span>
                            <small>(<?php echo esc_html($application->cv_file); ?>)</small>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($application->admin_notes)): ?>
                <div class="info-section">
                    <h3>Admin Notes</h3>
                    <div class="admin-notes">
                        <?php echo nl2br(esc_html($application->admin_notes)); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="application-sidebar">
                <div class="action-section">
                    <h3>Actions</h3>
                    
                    <form method="post" action="<?php echo admin_url('admin.php?page=jms_applications'); ?>" class="status-form">
                        <input type="hidden" name="jms_action" value="update_application">
                        <input type="hidden" name="application_id" value="<?php echo esc_attr($application->id); ?>">
                        <?php wp_nonce_field('jms_application_nonce', 'jms_nonce'); ?>
                        
                        <div class="form-group">
                            <label for="new_status">Update Status:</label>
                            <select id="new_status" name="status" class="form-control">
                                <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($application->status, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_notes">Admin Notes:</label>
                            <textarea id="admin_notes" 
                                      name="admin_notes" 
                                      rows="4" 
                                      class="form-control"
                                      placeholder="Add notes about this application..."><?php echo esc_textarea($application->admin_notes); ?></textarea>
                            <small class="form-text">These notes will be included in status change emails to the applicant.</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="button button-primary">
                                Update Application
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="quick-action-buttons">
                        <?php if ($application->status === 'pending'): ?>
                        <button class="button button-approve" onclick="quickUpdateStatus('approved')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="button button-reject" onclick="quickUpdateStatus('rejected')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <button class="button button-shortlist" onclick="quickUpdateStatus('shortlisted')">
                            <i class="fas fa-star"></i> Shortlist
                        </button>
                        <?php endif; ?>
                        
                        <a href="mailto:<?php echo esc_attr($application->email); ?>?subject=Re: Your application for <?php echo esc_attr($application->job_name); ?>" 
                           class="button button-secondary">
                            <i class="fas fa-envelope"></i> Send Email
                        </a>
                    </div>
                </div>
                
                <div class="application-meta">
                    <h3>Application Timeline</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo $application->created_at ? date('M j, Y g:i A', strtotime($application->created_at)) : 'Unknown Date'; ?>
                            </div>
                            <div class="timeline-content">
                                Application submitted
                            </div>
                        </div>
                        
                        <?php if (!empty($application->updated_at) && $application->updated_at !== $application->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('M j, Y g:i A', strtotime($application->updated_at)); ?>
                            </div>
                            <div class="timeline-content">
                                Status updated to: <?php echo esc_html($statuses[$application->status] ?? ucfirst($application->status ?? 'Unknown')); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="application-footer">
            <a href="<?php echo admin_url('admin.php?page=jms_applications'); ?>" class="button">
                ← Back to Applications
            </a>
            <a href="<?php echo admin_url('admin.php?page=jms_applications&action=delete&id=' . $application->id); ?>" 
               class="button button-link-delete" 
               onclick="return confirm('Are you sure you want to delete this application?');">
                Delete Application
            </a>
        </div>
    </div>
</div>

<style>
.jms-application-view {
    max-width: 1200px;
}

.application-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.applicant-info h2 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
}

.applicant-meta {
    color: #666;
    margin: 0;
    line-height: 1.6;
}

.application-status {
    text-align: right;
}

.jms-status {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.jms-status-pending { background: #fff3cd; color: #856404; }
.jms-status-approved { background: #d4edda; color: #155724; }
.jms-status-rejected { background: #f8d7da; color: #721c24; }
.jms-status-shortlisted { background: #d1ecf1; color: #0c5460; }

.application-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.application-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.info-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
}

.cv-download {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    text-decoration: none;
    color: #007cba;
    transition: all 0.3s ease;
}

.cv-download:hover {
    background: #e9ecef;
    border-color: #007cba;
    text-decoration: none;
}

.cv-download i {
    font-size: 1.5rem;
    color: #dc3545;
}

.cv-download small {
    color: #666;
    font-style: italic;
}

.cover-letter {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #007cba;
    line-height: 1.6;
    white-space: pre-wrap;
}

.admin-notes {
    background: #fff3cd;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #ffc107;
    line-height: 1.6;
}

.application-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.action-section,
.quick-actions,
.application-meta {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-section h3,
.quick-actions h3,
.application-meta h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-text {
    font-size: 0.8rem;
    color: #666;
    margin-top: 5px;
}

.quick-action-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
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

.button-shortlist {
    background: #ffc107;
    color: #333;
    border-color: #ffc107;
}

.button-shortlist:hover {
    background: #e0a800;
    color: #333;
}

.timeline {
    border-left: 2px solid #e9ecef;
    padding-left: 0;
}

.timeline-item {
    position: relative;
    padding-left: 20px;
    margin-bottom: 15px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -6px;
    top: 5px;
    width: 10px;
    height: 10px;
    background: #007cba;
    border-radius: 50%;
}

.timeline-date {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 3px;
}

.timeline-content {
    font-size: 0.9rem;
    color: #333;
}

.application-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .application-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .application-content {
        grid-template-columns: 1fr;
    }
    
    .application-footer {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .quick-action-buttons .button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function quickUpdateStatus(status) {
    if (confirm('Update application status to "' + status + '"?')) {
        document.getElementById('new_status').value = status;
        document.querySelector('.status-form').submit();
    }
}
</script>

