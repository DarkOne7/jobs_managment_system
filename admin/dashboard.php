<?php
/**
 * Admin Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Job Management Dashboard</h1>
    
    <div class="jms-dashboard">
        <!-- Stats Cards -->
        <div class="jms-stats-grid">
            <div class="jms-stat-card">
                <div class="jms-stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="jms-stat-content">
                    <h3><?php echo $stats['departments']; ?></h3>
                    <p>Departments</p>
                    <a href="<?php echo admin_url('admin.php?page=jms_departments'); ?>" class="jms-stat-link">Manage</a>
                </div>
            </div>
            
            <div class="jms-stat-card">
                <div class="jms-stat-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="jms-stat-content">
                    <h3><?php echo $stats['locations']; ?></h3>
                    <p>Locations</p>
                    <a href="<?php echo admin_url('admin.php?page=jms_locations'); ?>" class="jms-stat-link">Manage</a>
                </div>
            </div>
            
            <div class="jms-stat-card">
                <div class="jms-stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="jms-stat-content">
                    <h3><?php echo $stats['jobs']; ?></h3>
                    <p>Jobs</p>
                    <a href="<?php echo admin_url('admin.php?page=jms_jobs'); ?>" class="jms-stat-link">Manage</a>
                </div>
            </div>
            
            <div class="jms-stat-card">
                <div class="jms-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="jms-stat-content">
                    <h3><?php echo $stats['applications']; ?></h3>
                    <p>Applications</p>
                    <a href="<?php echo admin_url('admin.php?page=jms_applications'); ?>" class="jms-stat-link">Manage</a>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="jms-quick-actions">
            <h2>Quick Actions</h2>
            <div class="jms-actions-grid">
                <a href="<?php echo admin_url('admin.php?page=jms_departments&action=add'); ?>" class="jms-action-btn">
                    <i class="fas fa-plus"></i>
                    Add Department
                </a>
                <a href="<?php echo admin_url('admin.php?page=jms_locations&action=add'); ?>" class="jms-action-btn">
                    <i class="fas fa-plus"></i>
                    Add Location
                </a>
                <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=add'); ?>" class="jms-action-btn">
                    <i class="fas fa-plus"></i>
                    Add Job
                </a>
                <a href="<?php echo home_url('/jobs/'); ?>" class="jms-action-btn" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    View Jobs Page
                </a>
            </div>
        </div>
        
        <!-- Recent Applications -->
        <?php if (!empty($recent_applications)): ?>
        <div class="jms-recent-applications">
            <h2>Recent Applications</h2>
            <div class="jms-applications-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Job</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_applications as $application): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($application->name); ?></strong><br>
                                <small><?php echo esc_html($application->email); ?></small>
                            </td>
                            <td><?php echo esc_html($application->job_name); ?></td>
                            <td>
                                <span class="jms-status jms-status-<?php echo esc_attr($application->status); ?>">
                                    <?php echo ucfirst($application->status); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($application->created_at)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=jms_applications&action=view&id=' . $application->id); ?>" class="button button-small">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="jms-view-all">
                    <a href="<?php echo admin_url('admin.php?page=jms_applications'); ?>">View All Applications →</a>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Pending Applications Alert -->
        <?php if ($stats['pending_applications'] > 0): ?>
        <div class="notice notice-warning">
            <p>
                <strong>You have <?php echo $stats['pending_applications']; ?> pending application(s) that need review.</strong>
                <a href="<?php echo admin_url('admin.php?page=jms_applications&status=pending'); ?>">Review Now</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.jms-dashboard {
    max-width: 1200px;
}

.jms-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.jms-stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.jms-stat-icon {
    font-size: 2rem;
    color: #007cba;
    opacity: 0.8;
}

.jms-stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #333;
}

.jms-stat-content p {
    margin: 5px 0;
    color: #666;
}

.jms-stat-link {
    color: #007cba;
    text-decoration: none;
    font-size: 0.9rem;
}

.jms-stat-link:hover {
    text-decoration: underline;
}

.jms-quick-actions {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.jms-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.jms-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 15px;
    background: #007cba;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.jms-action-btn:hover {
    background: #005a87;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.jms-recent-applications {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.jms-view-all {
    text-align: right;
    margin-top: 15px;
}

.jms-view-all a {
    color: #007cba;
    text-decoration: none;
    font-weight: 500;
}

.jms-view-all a:hover {
    text-decoration: underline;
}
</style>

