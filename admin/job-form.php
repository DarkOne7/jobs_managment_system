<?php
/**
 * Job Form Template (Add/Edit)
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($job);
$page_title = $is_edit ? 'Edit Job' : 'Add New Job';
$button_text = $is_edit ? 'Update Job' : 'Add Job';

// Default values
$name = $is_edit ? $job->name : '';
$slug = $is_edit ? $job->slug : '';
$department_id = $is_edit ? $job->department_id : '';
$location_id = $is_edit ? $job->location_id : '';
$work_type = $is_edit ? $job->work_type : 'full-time';
$application_deadline = $is_edit ? $job->application_deadline : '';
$description = $is_edit ? $job->description : '';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=jms_jobs'); ?>" class="jms-form">
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($job->id); ?>">
        <?php endif; ?>
        
        <input type="hidden" name="jms_action" value="save_job">
        <input type="hidden" name="status" value="active">
        <?php wp_nonce_field('jms_job_nonce', 'jms_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="job_name">Job Title *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="job_name" 
                               name="name" 
                               value="<?php echo esc_attr($name); ?>" 
                               class="regular-text" 
                               required
                               maxlength="255">
                        <p class="description">Enter the job title (e.g., Senior Software Developer, Marketing Manager)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="job_slug">Slug *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="job_slug" 
                               name="slug" 
                               value="<?php echo esc_attr($slug); ?>" 
                               class="regular-text" 
                               required
                               pattern="[a-z0-9-]+"
                               maxlength="255">
                        <p class="description">URL-friendly version (lowercase, hyphens only). Auto-generated from title if left empty.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="department_id">Department *</label>
                    </th>
                    <td>
                        <select id="department_id" name="department_id" class="regular-text" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo esc_attr($dept->id); ?>" <?php selected($department_id, $dept->id); ?>>
                                <?php echo esc_html($dept->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Choose the department this job belongs to. <a href="<?php echo admin_url('admin.php?page=jms_departments&action=add'); ?>" target="_blank">Add new department</a></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="location_id">Location *</label>
                    </th>
                    <td>
                        <select id="location_id" name="location_id" class="regular-text" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo esc_attr($loc->id); ?>" <?php selected($location_id, $loc->id); ?>>
                                <?php echo esc_html($loc->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Choose the location for this job. <a href="<?php echo admin_url('admin.php?page=jms_locations&action=add'); ?>" target="_blank">Add new location</a></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="work_type">Work Type</label>
                    </th>
                    <td>
                        <select id="work_type" name="work_type" class="regular-text">
                            <option value="full-time" <?php selected($work_type, 'full-time'); ?>>Full Time</option>
                            <option value="part-time" <?php selected($work_type, 'part-time'); ?>>Part Time</option>
                            <option value="contract" <?php selected($work_type, 'contract'); ?>>Contract</option>
                            <option value="freelance" <?php selected($work_type, 'freelance'); ?>>Freelance</option>
                            <option value="internship" <?php selected($work_type, 'internship'); ?>>Internship</option>
                        </select>
                        <p class="description">Type of employment for this position.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="application_deadline">Application Deadline</label>
                    </th>
                    <td>
                        <input type="date" 
                               id="application_deadline" 
                               name="application_deadline" 
                               value="<?php echo esc_attr($application_deadline); ?>" 
                               class="regular-text">
                        <p class="description">Last date to accept applications (optional).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="job_description">Job Description</label>
                    </th>
                    <td>
                        <?php 
                        wp_editor($description, 'job_description', array(
                            'textarea_name' => 'description',
                            'media_buttons' => false,
                            'textarea_rows' => 8,
                            'teeny' => true
                        ));
                        ?>
                        <p class="description">Detailed description of the job role and responsibilities.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html($button_text); ?></button>
            <a href="<?php echo admin_url('admin.php?page=jms_jobs'); ?>" class="button">Cancel</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from job title
    $('#job_name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim('-');
        
        if (!$('#job_slug').val() || $('#job_slug').data('auto-generated')) {
            $('#job_slug').val(slug).data('auto-generated', true);
        }
    });
    
    // Mark slug as manually edited
    $('#job_slug').on('input', function() {
        $(this).data('auto-generated', false);
    });
});
</script>

<style>
.jms-form .form-table {
    max-width: 900px;
}

.jms-form .wp-editor-container {
    border: 1px solid #ddd;
    border-radius: 4px;
}

select.regular-text {
    height: auto;
    padding: 6px 8px;
}
</style>

