<?php
/**
 * Locations List Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle messages
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Locations</h1>
    <a href="<?php echo admin_url('admin.php?page=jms_locations&action=add'); ?>" class="page-title-action">Add New Location</a>
    
    <?php if ($message): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="jms-locations-list">
        <?php if (!empty($locations)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="column-name">Name</th>
                    <th scope="col" class="column-slug">Slug</th>
                    <th scope="col" class="column-jobs">Jobs</th>
                    <th scope="col" class="column-date">Created</th>
                    <th scope="col" class="column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($locations as $location): 
                    $jobs_count = JMS_DB_Helper::get_jobs(array('location_id' => $location->id, 'status' => 'active'));
                    $jobs_count = count($jobs_count);
                ?>
                <tr>
                    <td class="column-name">
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=jms_locations&action=edit&id=' . $location->id); ?>">
                                <?php echo esc_html($location->name); ?>
                            </a>
                        </strong>
                        <?php if ($location->description): ?>
                        <br><small class="description"><?php echo esc_html(wp_trim_words($location->description, 15)); ?></small>
                        <?php endif; ?>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo admin_url('admin.php?page=jms_locations&action=edit&id=' . $location->id); ?>">Edit</a> |
                            </span>
                            <span class="view">
                                <a href="<?php echo home_url('/jobs/location/' . $location->slug); ?>" target="_blank">View</a> |
                            </span>
                            <span class="delete">
                                <a href="<?php echo admin_url('admin.php?page=jms_locations&action=delete&id=' . $location->id); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this location? This will also affect associated jobs.');" 
                                   class="submitdelete">Delete</a>
                            </span>
                        </div>
                    </td>
                    <td class="column-slug">
                        <code><?php echo esc_html($location->slug); ?></code>
                    </td>
                    <td class="column-jobs">
                        <?php if ($jobs_count > 0): ?>
                        <a href="<?php echo admin_url('admin.php?page=jms_jobs&location=' . $location->id); ?>">
                            <?php echo $jobs_count; ?> job<?php echo $jobs_count !== 1 ? 's' : ''; ?>
                        </a>
                        <?php else: ?>
                        <span class="description">No jobs</span>
                        <?php endif; ?>
                    </td>
                    <td class="column-date">
                        <?php echo date('M j, Y', strtotime($location->created_at)); ?>
                    </td>
                    <td class="column-actions">
                        <a href="<?php echo admin_url('admin.php?page=jms_locations&action=edit&id=' . $location->id); ?>" 
                           class="button button-small">Edit</a>
                        <a href="<?php echo admin_url('admin.php?page=jms_jobs&action=add&location=' . $location->id); ?>" 
                           class="button button-small">Add Job</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="jms-empty-state">
            <div class="jms-empty-icon">
                <i class="fas fa-map-marker-alt fa-3x"></i>
            </div>
            <h3>No Locations Found</h3>
            <p>Start by creating your first location to organize job postings by place.</p>
            <a href="<?php echo admin_url('admin.php?page=jms_locations&action=add'); ?>" class="button button-primary">
                Add Your First Location
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.column-jobs {
    width: 80px;
}

.column-date {
    width: 100px;
}

.column-actions {
    width: 120px;
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

