<?php
/**
 * Template for displaying job departments (Desktop - 7.jpg style)
 * This template shows all departments with job counts and search functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get search query if any
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$location_filter = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';

// Get all departments
$departments = get_terms(array(
    'taxonomy' => 'job_department',
    'hide_empty' => true,
));

// Get total job count
$total_jobs = wp_count_posts('job');
$total_published_jobs = $total_jobs->publish;
?>


<!-- Outer background element -->

<div class="header-wrapper">
  <!-- Inner content element (container) -->
  <div class="header-container">
    <h1 style="font-weight: 600;">We have <span class="highlight"><?php echo esc_html($total_published_jobs); ?></span> open positions</h1>
  </div>
</div>

<div class="job-opportunities-page">
    <!-- Header Section -->

    <!-- Search and Filter Section -->
    <div class="opportunities-filters" style="margin-top: 40px;">
        <div class="filter-container">
            <div class="location-filter">
                <div class="filter-icon">
                    <i class="fa-regular fa-location-dot"></i>
                </div>
                <select id="location-filter" name="location">
                    <option value=""><?php _e('Choose location', 'job-system'); ?></option>
                    <?php
                    // Get locations from job_location taxonomy
                    $location_terms = get_terms(array(
                        'taxonomy' => 'job_location',
                        'hide_empty' => true,
                    ));
                    
                    if (!empty($location_terms) && !is_wp_error($location_terms)) {
                        foreach ($location_terms as $location_term) {
                            $selected = ($location_filter === $location_term->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($location_term->slug) . '" ' . $selected . '>' . esc_html($location_term->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="search-filter">
                <div class="search-icon">
                    <i class="fa-regular fa-magnifying-glass"></i>
                </div>
                <input type="text" id="search-jobs" placeholder="<?php echo esc_attr(sprintf(__('Search from %d open positions', 'job-system'), $total_published_jobs)); ?>" value="<?php echo esc_attr($search_query); ?>">
            </div>
        </div>
    </div>

    <!-- Departments Grid -->
    <div class="departments-grid">
        <?php if (!empty($departments) && !is_wp_error($departments)): ?>
            <?php foreach ($departments as $department): ?>
                <?php
                $color = get_term_meta($department->term_id, 'department_color', true);
                if (!$color) {
                    $color = '#00a0d2';
                }
                $job_count = $department->count;
                $department_link = home_url('/jobs/department?id=' . $department->term_id);
                ?>
                <div class="department-card" 
                     data-department="<?php echo esc_attr($department->slug); ?>"
                     data-department-id="<?php echo esc_attr($department->term_id); ?>">
                    <div class="card-top">
                        <div class="vertical-bar" style="background-color: <?php echo esc_attr($color); ?>;"></div>
                        <div class="text-content">
                            <p class="team-label"><?php _e('Team', 'job-system'); ?></p>
                            <h2 class="department-title"><?php echo esc_html($department->name); ?></h2>
                        </div>
                    </div>
                    
                    <div class="card-bottom">
                        <p class="opportunities-count">
                            <?php printf(_n('%d Opportunity', '%d Opportunities', $job_count, 'job-system'), $job_count); ?>
                        </p>
                        <a href="<?php echo esc_url($department_link); ?>" class="see-all-btn">
                            <?php _e('See all', 'job-system'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-departments">
                <p><?php _e('No departments found.', 'job-system'); ?></p>
                <?php if (current_user_can('manage_options')): ?>
                    <p>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_department&post_type=job'); ?>">
                            <?php _e('Add departments in admin panel', 'job-system'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

/* 1. Outer element (background) */
.header-wrapper {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(90deg, #D3F8DFBF 25%, #D3F8DF40 75%);
    border-bottom: solid 3px #14A26A;
    padding-top: 48px;    /* Top margin only */
    padding-bottom: 48px; /* Bottom margin only */
}

/* 2. Inner element (container) */
.header-container {
    max-width: 1140px; /* Example: same max width as the rest of your site content */
    margin-left: auto;  /* To center the container */
    margin-right: auto; /* To center the container */
    padding-left: 0px; /* Safety margin on small screens */
    padding-right: 15px;/* Safety margin on small screens */
    text-align: left;
    color: black;
}
@media (max-width: 1167px) {
    .header-container {
        padding-left: 21px;
    }
}
/* No changes here */
.highlight {
    color: #14A26A;
    font-weight: bold;
}


/* Opportunities Page Styles */
.job-opportunities-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.opportunities-header {
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%);
    padding: 60px 40px;
    border-radius: 16px;
    margin-bottom: 40px;
    text-align: center;
}

.opportunities-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.2;
}

.highlight-number {
    color: #10b981;
    font-weight: 700;
}

.opportunities-filters {
    margin-bottom: 40px;
}

/* .filter-container {
    display: flex;
    gap: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.location-filter,
.search-filter {
    position: relative;
    flex: 1;
}

.filter-icon,
.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    z-index: 2;
}

.location-filter select,
.search-filter input {
    width: 100%;
    padding: 16px 16px 16px 50px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 16px;
    background: white;
    transition: all 0.3s ease;
}

.location-filter select:focus,
.search-filter input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
} */

.opportunities-filters {
    margin-bottom: 40px;
}

.filter-container {
    display: flex;
    gap: 20px; /* Distance between fields */
    /* Note: I removed max-width and margin: auto from here
       because the main container .job-opportunities-page already handles this */
}

.location-filter {
    position: relative;
    flex: 1; /* Make the location field take one part of the space */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%238A94A6' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E" );
    background-repeat: no-repeat;
    background-position: right 16px center; /* Places the arrow 16px from the right and vertically centered */
}

.search-filter {
    position: relative;
    flex: 2; /* Make the search field take two parts of the space (wider) */
}

.filter-icon,
.search-icon {
    position: absolute;
    left: 18px;
    top: 49%;
    transform: translateY(-50%);
    color: #868686; /* Light gray icon color as shown in the image */
    z-index: 2;
}

.location-filter select,
.search-filter input {
    width: 100%;
    padding: 14px 16px 14px 50px; /* Adjust padding to fit the design */
    border: 1px solid #e5e7eb; /* Light gray border 1px thick */
    border-radius: 8px; /* Less sharp rounded corners */
    font-size: 16px;
    background: #ffffff; /* White background */
    transition: border-color 0.2s ease; /* Smooth transition on focus */
    -webkit-appearance: none; /* Remove default select field appearance */
    -moz-appearance: none;
    appearance: none;
}

/* Modify dropdown arrow to fit the design */
.location-filter {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%238A94A6' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E" );
    background-repeat: no-repeat;
    background-position: right 16px center;
}

.location-filter select {
    width: 100%;
    padding: 14px 40px 14px 50px; /* Increase right padding to make room for the arrow */
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    background-color: transparent; /* Make select background transparent to show parent background */

    /* --- This is the part for removing the default arrow --- */
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* When focusing on any field */
.location-filter select:focus,
.search-filter input:focus {
    outline: none; /* Remove default blue outline */
    border-color: #e5e7eb; /* Change border color to slightly darker gray */
    box-shadow: none; /* Remove shadow completely */
}

.departments-grid {
    display: grid;
    gap: 24px;
    margin-top: 40px;
    align-items: stretch; /* Good for maintaining equal height */
    
    /* Default layout (for mobile screens) - one column */
    grid-template-columns: 1fr;
}

/* Tablet screens (larger than 768px) - two columns */
@media (min-width: 768px) {
    .departments-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop screens (larger than 1200px) - three columns (this is your main request) */
@media (min-width: 1200px) {
    .departments-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.department-card {
    background-color: #ffffff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    padding: 24px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 200px;
    box-sizing: border-box;
}

.department-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.15);
}

.card-top {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.vertical-bar {
    width: 5px;
    height: 50px;
    border-top-left-radius: 3px;
    border-bottom-left-radius: 3px;
}

.text-content {
    display: flex;
    flex-direction: column;
}

.team-label {
    color: #8A94A6;
    font-size: 15px;
    font-weight: 500;
    margin: 0;
    line-height: 1.2;
}

.department-title {
    color: #000000;
    font-size: 26px;
    font-weight: 600;
    margin: 0;
    line-height: 1.4;
}

.card-bottom {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.opportunities-count {
    color: #333;
    font-size: 16px;
    font-weight: 500;
    margin: 11px 0 0 0;
}

.department-action {
    margin-top: auto; /* This ensures the action stays at the bottom */
}

.see-all-btn {
    display: block;
    text-align: center;
    background-color: #ffffff;
    color: #10b981;
    border: 1.5px solid #10b981;
    padding: 12px 0;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    transition: background-color 0.2s, color 0.2s;
}

.see-all-btn:hover {
    background-color: #10b981;
    color: #ffffff;
    text-decoration: none;
}

.no-departments {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.no-departments p {
    font-size: 18px;
    margin-bottom: 16px;
}

.no-departments a {
    color: #10b981;
    text-decoration: none;
    font-weight: 600;
}

.no-departments a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .opportunities-title {
        font-size: 2rem;
    }
    
    .filter-container {
        flex-direction: column;
        gap: 16px;
    }
    
    .departments-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .opportunities-header {
        padding: 40px 20px;
    }
}

@media (max-width: 480px) {
    .department-card {
        padding: 20px;
    }
    
    .department-name {
        font-size: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-jobs');
    const locationFilter = document.getElementById('location-filter');
    const departmentCards = document.querySelectorAll('.department-card');
    
    function filterDepartments() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedLocation = locationFilter.value;
        
        departmentCards.forEach(card => {
            const departmentName = card.querySelector('.department-title').textContent.toLowerCase();
            let shouldShow = departmentName.includes(searchTerm);
            
            // If a location is selected, we would need additional filtering logic here
            // For now, we'll just show all departments as location filtering 
            // would require backend support
            
            if (shouldShow) {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.3s ease';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterDepartments);
    }
    
    if (locationFilter) {
        locationFilter.addEventListener('change', filterDepartments);
    }
    
    // Handle department card clicks
    departmentCards.forEach(card => {
        card.addEventListener('click', function() {
            const departmentId = this.getAttribute('data-department-id');
            if (departmentId) {
                window.location.href = '/jobs/department?id=' + departmentId;
            }
        });
        
        // Add pointer cursor
        card.style.cursor = 'pointer';
    });
    
    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});
</script>
