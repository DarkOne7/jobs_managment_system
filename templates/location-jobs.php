<?php
/**
 * Template for displaying jobs by location
 * This template shows jobs for a specific location
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get location from query var (set by our custom routing)
$location = get_query_var('current_location');

if (!$location) {
    // Fallback: try to get from URL parameter
    $location_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($location_id) {
        $location = get_term($location_id, 'job_location');
    }
}

if (!$location || is_wp_error($location)) {
    wp_redirect(home_url('/locations'));
    exit;
}

// Debug: Show what location we're working with
if (isset($_GET['debug'])) {
    echo "<h3>Debug Info:</h3>";
    echo "Location ID: " . $location->term_id . "<br>";
    echo "Location Name: " . $location->name . "<br>";
    echo "Location Slug: " . $location->slug . "<br>";
}

$location_color = get_term_meta($location->term_id, 'location_color', true);
if (!$location_color) {
    $location_color = '#f97316'; // Orange default for locations
}

// Get jobs for this location - simplified query first
$jobs_query = new WP_Query(array(
    'post_type' => 'job',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'job_location',
            'field' => 'term_id',
            'terms' => $location->term_id
        )
    )
));

// Debug: Show query results
if (isset($_GET['debug'])) {
    echo "Jobs found: " . $jobs_query->found_posts . "<br>";
    echo "SQL Query: " . $jobs_query->request . "<br><br>";
}
?>
<div class="location-jobs-page">
    <!-- Header Section -->
    <div class="location-header" style="background: linear-gradient(135deg, <?php echo esc_attr($location_color); ?>15 0%, <?php echo esc_attr($location_color); ?>05 100%);">
        <div class="header-content">
            <div class="back-navigation">
                <a href="/locations" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php _e('Back to all locations', 'job-system'); ?>
                </a>
            </div>
            
            <div class="location-info">
                <div class="logo-placeholder" style="background-color: <?php echo esc_attr($location_color); ?>;">
                    <?php echo esc_html(strtoupper(substr($location->name, 0, 2))); ?>
                </div>
                <h1 class="location-title"><?php echo esc_html($location->name); ?></h1>
            </div>
        </div>
    </div>

    <!-- Jobs Grid -->
    <div class="jobs-container">
        <?php if ($jobs_query->have_posts()): ?>
            <div class="jobs-grid">
                <?php while ($jobs_query->have_posts()): $jobs_query->the_post(); ?>
                    <?php
                    $job_id = get_the_ID();
                    $job_location = get_post_meta($job_id, '_job_location', true);
                    $work_type = get_post_meta($job_id, '_work_type', true);
                    $deadline = get_post_meta($job_id, '_application_deadline', true);
                    $is_expired = $deadline && time() > strtotime($deadline);
                    
                    // Get job departments
                    $job_departments = wp_get_post_terms($job_id, 'job_department');
                    ?>
                    
                    <div class="card" data-job-id="<?php echo esc_attr($job_id); ?>">
                        <div class="card-top">
                            <div class="vertical-bar" style="background-color: <?php echo esc_attr($location_color); ?>;"></div>
                            <div class="text-content">
                                <?php if (!empty($job_departments)): ?>
                                    <p class="team-label"><?php echo esc_html($job_departments[0]->name); ?></p>
                                <?php else: ?>
                                    <p class="team-label">Position</p>
                                <?php endif; ?>
                                <h2 class="product-title"><?php echo get_the_title($job_id); ?></h2>
                            </div>
                        </div>

                        <div class="card-bottom">
                            <p class="opportunities-count"><?php echo wp_trim_words(get_the_excerpt($job_id), 15, '...'); ?></p>
                            <?php if (!$is_expired): ?>
                                <a href="<?php echo get_permalink($job_id); ?>" class="see-all-btn">Apply Now</a>
                            <?php else: ?>
                                <span class="expired-label">Application Closed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php else: ?>
            <div class="no-jobs">
                <div class="no-jobs-content">
                    <div class="no-jobs-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6h-2V4c0-1.11-.89-2-2-2H8c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zM8 4h8v2H8V4z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3><?php _e('No open positions', 'job-system'); ?></h3>
                    <p><?php printf(__('There are currently no open positions in %s location.', 'job-system'), esc_html($location->name)); ?></p>
                    <a href="/locations" class="back-to-locations">
                        <?php _e('View all locations', 'job-system'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

/* Location Jobs Page Styles */
.location-jobs-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.location-header {
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 40px;
}

.back-navigation {
    margin-bottom: 24px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #374151;
    text-decoration: none;
}

.location-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.logo-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 700;
    flex-shrink: 0;
}

.location-title {
    font-size: 3rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.1;
}

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    align-items: stretch;
}

/* --- Card Styling --- */
.card {
    background-color: #ffffff;
    border-radius: 16px; /* More rounded corners */
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07); /* Softer, more spread out shadow */
    padding: 24px;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Pushes content and button apart */
    min-height: 200px; /* Gives the card a fixed height */
    box-sizing: border-box; /* Ensures padding is included in the width/height */
}

.card-top {
    display: flex;
    align-items: flex-start; /* Align items to the top */
    gap: 16px; /* Space between the green bar and the text */
}

.vertical-bar {
    width: 5px;
    height: 50px; /* Height of the bar */
    border-top-left-radius: 3px;
    border-bottom-left-radius: 3px;
}

.text-content {
    display: flex;
    flex-direction: column;
}

.team-label {
    color: #8A94A6; /* Gray color for "Team" */
    font-size: 15px;
    font-weight: 500;
    margin: 0;
    line-height: 1.2;
}

.product-title {
    color: #000000; /* Black color for "Product" */
    font-size: 26px;
    font-weight: 600; /* Bolder */
    margin: 0;
    line-height: 1.4;
}

.card-bottom {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Space between opportunities text and button */
}

.opportunities-count {
    color: #333;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

/* --- Button Styling --- */
.see-all-btn {
    display: block;
    text-align: center;
    background-color: #ffffff;
    color: #f97316; /* Orange color for locations */
    border: 1.5px solid #f97316; /* Orange border */
    padding: 12px 0;
    border-radius: 12px; /* More rounded button corners */
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

.expired-label {
    color: #dc2626;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    padding: 12px 0;
    border-radius: 12px;
    border: 1.5px solid #dc2626;
    background-color: #ffffff;
}

.no-jobs {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.no-jobs-content {
    text-align: center;
    max-width: 400px;
}

.no-jobs-icon {
    color: #d1d5db;
    margin-bottom: 24px;
    display: flex;
    justify-content: center;
}

.no-jobs-content h3 {
    font-size: 24px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 16px;
}

.no-jobs-content p {
    color: #6b7280;
    margin-bottom: 32px;
    line-height: 1.6;
}

.back-to-locations {
    display: inline-flex;
    align-items: center;
    padding: 12px 24px;
    background: #f97316;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-to-locations:hover {
    background: #ea580c;
    text-decoration: none;
}

@media (max-width: 768px) {
    .location-title {
        font-size: 2rem;
    }
    
    .location-info {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
    
    .jobs-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .card {
        padding: 20px;
        min-height: 180px;
    }
    
    .product-title {
        font-size: 22px;
    }
    
    .opportunities-count {
        font-size: 14px;
    }
}
</style>

<script>
function openJobModal(jobId) {
    // This function should be implemented to open the job application modal
    console.log('Opening job modal for job ID:', jobId);
    // You can implement the actual modal functionality here
}
</script>
