<?php
/**
 * Template for displaying job locations
 * This template shows all locations with job counts and search functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get search query if any
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';

// Get all locations
$locations = get_terms(array(
    'taxonomy' => 'job_location',
    'hide_empty' => false, // Show all locations, even empty ones
));

// If no locations exist, show a message
if (empty($locations) || is_wp_error($locations)) {
    $no_locations = true;
    
    // Trigger default location creation
    do_action('job_system_ensure_locations');
    
    // Re-check for locations after potential creation
    $locations = get_terms(array(
        'taxonomy' => 'job_location',
        'hide_empty' => false,
    ));
    
    if (!empty($locations) && !is_wp_error($locations)) {
        $no_locations = false;
    }
} else {
    $no_locations = false;
}

// Get total job count
$total_jobs = wp_count_posts('job');
$total_published_jobs = $total_jobs->publish;
?>

<div class="job-opportunities-page">
    <!-- Header Section -->
    <div class="opportunities-header">
        <div class="header-content">
            <h1 class="opportunities-title">
                We have <span class="highlight-number"><?php echo esc_html($total_published_jobs); ?></span> open position<?php echo $total_published_jobs != 1 ? 's' : ''; ?> in <?php echo count($locations); ?> location<?php echo count($locations) != 1 ? 's' : ''; ?>
            </h1>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="opportunities-filters">
        <div class="filter-container">
            <div class="department-filter">
                <div class="filter-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/>
                    </svg>
                </div>
                <select id="department-filter" name="department">
                    <option value=""><?php _e('Choose department', 'job-system'); ?></option>
                    <?php
                    // Get departments for filter
                    $departments = get_terms(array(
                        'taxonomy' => 'job_department',
                        'hide_empty' => true,
                    ));
                    if (!empty($departments) && !is_wp_error($departments)) {
                        foreach ($departments as $dept) {
                            $selected = ($department_filter === $dept->slug) ? 'selected' : '';
                            echo '<option value="' . esc_attr($dept->slug) . '" ' . $selected . '>' . esc_html($dept->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="search-filter">
                <div class="search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </div>
                <input type="text" id="search-jobs" placeholder="<?php esc_attr_e('Search from 350 open positions', 'job-system'); ?>" value="<?php echo esc_attr($search_query); ?>">
            </div>
        </div>
    </div>

    <!-- Locations Grid -->
    <div class="locations-grid">
        <?php if (!$no_locations): ?>
            <?php foreach ($locations as $location): ?>
                <?php
                $color = get_term_meta($location->term_id, 'location_color', true);
                if (!$color) {
                    $color = '#f97316'; // Orange default for locations
                }
                $job_count = $location->count;
                $location_link = home_url('/jobs/location?id=' . $location->term_id);
                ?>
                <div class="location-card" 
                     data-location="<?php echo esc_attr($location->slug); ?>"
                     data-location-id="<?php echo esc_attr($location->term_id); ?>">
                    <div class="card-top">
                        <div class="vertical-bar" style="background-color: <?php echo esc_attr($color); ?>;"></div>
                        <div class="text-content">
                            <p class="team-label"><?php _e('Location', 'job-system'); ?></p>
                            <h2 class="location-title"><?php echo esc_html($location->name); ?></h2>
                        </div>
                    </div>
                    
                    <div class="card-bottom">
                        <p class="opportunities-count">
                            <?php printf(_n('%d Opportunity', '%d Opportunities', $job_count, 'job-system'), $job_count); ?>
                        </p>
                        <a href="<?php echo esc_url($location_link); ?>" class="see-all-btn">
                            <?php _e('See all', 'job-system'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-locations">
                <div class="no-locations-content">
                    <h3><?php _e('No locations available yet', 'job-system'); ?></h3>
                    <p><?php _e('Locations will be created automatically, or you can create them manually.', 'job-system'); ?></p>
                    <?php if (current_user_can('manage_options')): ?>
                        <p>
                            <a href="<?php echo admin_url('edit-tags.php?taxonomy=job_location&post_type=job'); ?>" class="btn">
                                <?php _e('Create Locations', 'job-system'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Navigation to departments -->
    <div class="navigation-section">
    <a href="<?php echo esc_url( home_url('/jobs') ); ?>" class="nav-link">
            <?php _e('View jobs by department', 'job-system'); ?>
        </a>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

/* Location Page Styles - similar to departments but with location-specific colors */
.job-opportunities-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.opportunities-header {
    text-align: center;
    padding: 60px 40px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    border-radius: 16px;
    margin-bottom: 40px;
    color: white;
}

.opportunities-title {
    font-size: 3rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
}

.highlight-number {
    color: #fbbf24;
    font-weight: 800;
}

.opportunities-filters {
    margin-bottom: 40px;
}

.filter-container {
    display: flex;
    gap: 20px;
    max-width: 600px;
    margin: 0 auto;
}

.department-filter,
.search-filter {
    flex: 1;
    position: relative;
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

.filter-container select,
.filter-container input {
    width: 100%;
    padding: 16px 16px 16px 48px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 16px;
    background: white;
    transition: all 0.3s ease;
}

.filter-container select:focus,
.filter-container input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-top: 40px;
    align-items: stretch;
}

.location-card {
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
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

.location-card:hover {
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

.location-title {
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

.location-action {
    margin-top: auto;
}

.see-all-btn {
    display: block;
    text-align: center;
    background-color: #ffffff;
    color: #f97316;
    border: 1.5px solid #f97316;
    padding: 12px 0;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    transition: background-color 0.2s, color 0.2s;
}

.see-all-btn:hover {
    background-color: #f97316;
    color: #ffffff;
    text-decoration: none;
}

.no-locations {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.no-locations p {
    font-size: 18px;
    margin-bottom: 16px;
}

.no-locations a {
    color: #f97316;
    text-decoration: none;
    font-weight: 600;
}

.no-locations a:hover {
    text-decoration: underline;
}

.navigation-section {
    text-align: center;
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid #e5e7eb;
}

.nav-link {
    display: inline-flex;
    align-items: center;
    padding: 16px 32px;
    background: #f8fafc;
    color: #374151;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: #f97316;
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .opportunities-title {
        font-size: 2rem;
    }
    
    .filter-container {
        flex-direction: column;
        gap: 16px;
    }
    
    .locations-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle location card clicks
    document.querySelectorAll('.location-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.see-all-btn')) return;
            
            const locationId = this.dataset.locationId;
            if (locationId) {
                window.location.href = `/locations/location?id=${locationId}`;
            }
        });
        
        card.style.cursor = 'pointer';
    });
    
    // Handle search functionality
    const searchInput = document.getElementById('search-jobs');
    const departmentFilter = document.getElementById('department-filter');
    
    function filterLocations() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDepartment = departmentFilter.value;
        
        document.querySelectorAll('.location-card').forEach(card => {
            const locationName = card.querySelector('.location-name').textContent.toLowerCase();
            const locationSlug = card.dataset.location;
            
            const matchesSearch = !searchTerm || locationName.includes(searchTerm);
            const matchesDepartment = !selectedDepartment; // For now, show all when no department filter
            
            if (matchesSearch && matchesDepartment) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', filterLocations);
    }
    
    if (departmentFilter) {
        departmentFilter.addEventListener('change', filterLocations);
    }
});
</script>
