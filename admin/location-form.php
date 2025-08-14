<?php
/**
 * Location Form Template (Add/Edit)
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($location);
$page_title = $is_edit ? 'Edit Location' : 'Add New Location';
$button_text = $is_edit ? 'Update Location' : 'Add Location';

// Default values
$name = $is_edit ? $location->name : '';
$slug = $is_edit ? $location->slug : '';
$description = $is_edit ? $location->description : '';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=jms_locations'); ?>" class="jms-form">
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($location->id); ?>">
        <?php endif; ?>
        
        <input type="hidden" name="jms_action" value="save_location">
        <?php wp_nonce_field('jms_location_nonce', 'jms_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="location_name">Location Name *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="location_name" 
                               name="name" 
                               value="<?php echo esc_attr($name); ?>" 
                               class="regular-text" 
                               required
                               maxlength="255">
                        <p class="description">Enter the location name (e.g., New York, NY | San Francisco, CA | Remote)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="location_slug">Slug *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="location_slug" 
                               name="slug" 
                               value="<?php echo esc_attr($slug); ?>" 
                               class="regular-text" 
                               required
                               pattern="[a-z0-9-]+"
                               maxlength="255">
                        <p class="description">URL-friendly version (lowercase, hyphens only). Auto-generated from name if left empty.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="location_description">Description</label>
                    </th>
                    <td>
                        <textarea id="location_description" 
                                  name="description" 
                                  rows="4" 
                                  class="large-text"
                                  maxlength="1000"><?php echo esc_textarea($description); ?></textarea>
                        <p class="description">Additional information about this location (office address, remote work details, etc.)</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html($button_text); ?></button>
            <a href="<?php echo admin_url('admin.php?page=jms_locations'); ?>" class="button">Cancel</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from name
    $('#location_name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim('-');
        
        if (!$('#location_slug').val() || $('#location_slug').data('auto-generated')) {
            $('#location_slug').val(slug).data('auto-generated', true);
        }
    });
    
    // Mark slug as manually edited
    $('#location_slug').on('input', function() {
        $(this).data('auto-generated', false);
    });
});
</script>

<style>
.jms-form .form-table {
    max-width: 800px;
}
</style>

