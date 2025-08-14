<?php
/**
 * Department Form Template (Add/Edit)
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($department);
$page_title = $is_edit ? 'Edit Department' : 'Add New Department';
$button_text = $is_edit ? 'Update Department' : 'Add Department';

// Default values
$name = $is_edit ? $department->name : '';
$slug = $is_edit ? $department->slug : '';
$color = $is_edit ? $department->color : '#007cba';
$icon = $is_edit ? $department->icon : 'fas fa-briefcase';
$description = $is_edit ? $department->description : '';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=jms_departments'); ?>" class="jms-form">
        <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo esc_attr($department->id); ?>">
        <?php endif; ?>
        
        <input type="hidden" name="jms_action" value="save_department">
        <?php wp_nonce_field('jms_department_nonce', 'jms_nonce'); ?>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="department_name">Department Name *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="department_name" 
                               name="name" 
                               value="<?php echo esc_attr($name); ?>" 
                               class="regular-text" 
                               required
                               maxlength="255">
                        <p class="description">Enter the department name (e.g., Information Technology, Human Resources)</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="department_slug">Slug *</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="department_slug" 
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
                        <label for="department_color">Department Color</label>
                    </th>
                    <td>
                        <input type="color" 
                               id="department_color" 
                               name="color" 
                               value="<?php echo esc_attr($color); ?>" 
                               class="color-picker">
                        <p class="description">Choose a color theme for this department. Used in job listings and cards.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="department_icon">Icon</label>
                    </th>
                    <td>
                        <div class="icon-selector">
                            <input type="text" 
                                   id="department_icon" 
                                   name="icon" 
                                   value="<?php echo esc_attr($icon); ?>" 
                                   class="regular-text"
                                   placeholder="fas fa-briefcase">
                            <div class="icon-preview">
                                <i class="<?php echo esc_attr($icon); ?>" style="color: <?php echo esc_attr($color); ?>"></i>
                            </div>
                        </div>
                        <p class="description">
                            Font Awesome icon class (e.g., fas fa-laptop-code, fas fa-users). 
                            <a href="https://fontawesome.com/icons" target="_blank">Browse icons</a>
                        </p>
                        
                        <div class="icon-suggestions">
                            <p><strong>Popular department icons:</strong></p>
                            <div class="icon-grid">
                                <button type="button" class="icon-option" data-icon="fas fa-laptop-code" title="Technology">
                                    <i class="fas fa-laptop-code"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-users" title="Human Resources">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-chart-line" title="Sales">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-bullhorn" title="Marketing">
                                    <i class="fas fa-bullhorn"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-calculator" title="Finance">
                                    <i class="fas fa-calculator"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-cogs" title="Operations">
                                    <i class="fas fa-cogs"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-handshake" title="Customer Service">
                                    <i class="fas fa-handshake"></i>
                                </button>
                                <button type="button" class="icon-option" data-icon="fas fa-palette" title="Design">
                                    <i class="fas fa-palette"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="department_description">Description</label>
                    </th>
                    <td>
                        <textarea id="department_description" 
                                  name="description" 
                                  rows="4" 
                                  class="large-text"
                                  maxlength="1000"><?php echo esc_textarea($description); ?></textarea>
                        <p class="description">Brief description of this department and the types of roles it includes.</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html($button_text); ?></button>
            <a href="<?php echo admin_url('admin.php?page=jms_departments'); ?>" class="button">Cancel</a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from name
    $('#department_name').on('input', function() {
        const name = $(this).val();
        const slug = name.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim('-');
        
        if (!$('#department_slug').val() || $('#department_slug').data('auto-generated')) {
            $('#department_slug').val(slug).data('auto-generated', true);
        }
    });
    
    // Mark slug as manually edited
    $('#department_slug').on('input', function() {
        $(this).data('auto-generated', false);
    });
    
    // Update icon preview
    function updateIconPreview() {
        const iconClass = $('#department_icon').val() || 'fas fa-briefcase';
        const color = $('#department_color').val() || '#007cba';
        $('.icon-preview i').attr('class', iconClass).css('color', color);
    }
    
    $('#department_icon, #department_color').on('change input', updateIconPreview);
    
    // Icon selection
    $('.icon-option').on('click', function(e) {
        e.preventDefault();
        const icon = $(this).data('icon');
        $('#department_icon').val(icon);
        updateIconPreview();
        
        // Highlight selected
        $('.icon-option').removeClass('selected');
        $(this).addClass('selected');
    });
    
    // Highlight current icon if it matches a suggestion
    const currentIcon = $('#department_icon').val();
    $('.icon-option[data-icon="' + currentIcon + '"]').addClass('selected');
});
</script>

<style>
.jms-form .form-table {
    max-width: 800px;
}

.color-picker {
    width: 60px;
    height: 40px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.icon-selector {
    display: flex;
    align-items: center;
    gap: 15px;
}

.icon-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

.icon-preview i {
    font-size: 1.5rem;
}

.icon-suggestions {
    margin-top: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}

.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 8px;
    max-width: 400px;
}

.icon-option {
    width: 40px;
    height: 40px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.icon-option:hover {
    border-color: #007cba;
    background: #f0f8ff;
}

.icon-option.selected {
    border-color: #007cba;
    background: #007cba;
    color: white;
}

.icon-option i {
    font-size: 1.2rem;
}

.icon-option.selected i {
    color: white;
}
</style>

