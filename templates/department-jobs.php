<?php
/**
 * Template for displaying jobs by department (Desktop - 8.jpg style)
 * This template shows jobs for a specific department
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get department from query var (set by our custom routing)
$department = get_query_var('current_department');

if (!$department) {
    // Fallback: try to get from URL parameter
    $department_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($department_id) {
        $department = get_term($department_id, 'job_department');
    }
}

if (!$department || is_wp_error($department)) {
    wp_redirect(home_url('/jobs'));
    exit;
}

// Debug: Show what department we're working with
if (isset($_GET['debug'])) {
    echo "<h3>Debug Info:</h3>";
    echo "Department ID: " . $department->term_id . "<br>";
    echo "Department Name: " . $department->name . "<br>";
    echo "Department Slug: " . $department->slug . "<br>";
}

$department_color = get_term_meta($department->term_id, 'department_color', true);
if (!$department_color) {
    $department_color = '#10b981';
}

// Get jobs for this department - simplified query first
$jobs_query = new WP_Query(array(
    'post_type' => 'job',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'job_department',
            'field' => 'term_id',
            'terms' => $department->term_id
        )
    )
));

// Debug: Show query results
if (isset($_GET['debug'])) {
    echo "Jobs found: " . $jobs_query->found_posts . "<br>";
    echo "SQL Query: " . $jobs_query->request . "<br><br>";
}
?>

<div class="Header">
        <h1><?php echo esc_html($department->name); ?></h1>
    </div>

<div class="department-jobs-page">
    <!-- Header Section -->

<!--     <div class="department-header" style="background: linear-gradient(135deg, <?php echo esc_attr($department_color); ?>15 0%, <?php echo esc_attr($department_color); ?>05 100%);">
        <div class="header-content">
            <div class="back-navigation">
                <a href="<?php echo esc_url( home_url('/jobs') ); ?>" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php _e('Back to all departments', 'job-system'); ?>
                </a>
            </div>
            
            <div class="department-info">
                <div class="logo-placeholder" style="background-color: <?php echo esc_attr($department_color); ?>;">
                    <?php echo esc_html(strtoupper(substr($department->name, 0, 2))); ?>
                </div>
                <h1 class="department-title"><?php echo esc_html($department->name); ?></h1>
            </div>
        </div>
    </div> -->

    <!-- Jobs Grid -->
    <div class="jobs-container">
        <?php if ($jobs_query->have_posts()): ?>
            <div class="jobs-grid">
                <?php while ($jobs_query->have_posts()): $jobs_query->the_post(); ?>
                    <?php
                    $job_id = get_the_ID();
                    $location = get_post_meta($job_id, '_job_location', true);
                    $work_type = get_post_meta($job_id, '_work_type', true);
                    $deadline = get_post_meta($job_id, '_application_deadline', true);
                    $is_expired = $deadline && time() > strtotime($deadline);
                    ?>
                    
                    <div class="card" data-job-id="<?php echo esc_attr($job_id); ?>">
                        <div class="card-top">
                            <div class="vertical-bar" style="background-color: <?php echo esc_attr($department_color); ?>;"></div>
                            <div class="text-content">
                                <p class="team-label"><?php echo esc_html($department->name); ?></p>
                                <h2 class="product-title"><?php echo get_the_title($job_id); ?></h2>
                            </div>
                        </div>

                        <div class="card-bottom">
                            <p class="opportunities-count"><?php echo wp_trim_words(get_the_excerpt($job_id), 15, '...'); ?></p>
                            <?php if (!$is_expired): ?>
                                <a href="<?php echo get_permalink($job_id); ?>" class="see-all-btn">Know more</a>
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
                    <p><?php printf(__('There are currently no open positions in %s department.', 'job-system'), esc_html($department->name)); ?></p>
                    <a href="<?php echo esc_url( home_url('/jobs') ); ?>" class="back-to-departments">
                        <?php _e('View all departments', 'job-system'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
.Header {
    font-family: "Poppins";
    background: black;
    color: white;
    padding: 48px 104px;
    text-align: center;
    border-bottom: solid 3px #14A26A;
    margin-bottom: 30px;
}
/* Department Jobs Page Styles */
.department-jobs-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.jobs-container {
    padding: 20px 0;
}

/* .department-header {
    
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

.department-info {
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

.department-title {
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
 */

/* Jobs Grid Layout */
.jobs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 4 columns on desktop */
    gap: 24px;
    align-items: stretch;
    padding: 20px 0;
}

/* --- Card Styling --- */
.card {
    background-color: #ffffff;
    border-radius: 16px; /* More rounded corners */
    border: solid 1px #e5e7eb;
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
    color: #10b981; /* Green color for departments */
    border: 1.5px solid #10b981; /* Green border */
    padding: 12px 0;
    border-radius: 12px; /* More rounded button corners */
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

.job-department-tag {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    color: white;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    align-self: flex-start;
}

.job-title {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.3;
}

.job-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
}

.job-location,
.job-type {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    font-size: 14px;
}

.job-excerpt {
    color: #6b7280;
    line-height: 1.6;
    font-size: 14px;
}

.apply-button {
    background: #1a1a1a;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-family: inherit;
}

.apply-button:hover {
    background: #374151;
    transform: translateY(-1px);
}

.see-all-btn {
    background: transparent;
    color: #10b981;
    border: 1px solid #10b981;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-block;
    font-family: inherit;
}

.see-all-btn:hover {
    background: #10b981;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
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
    color: #1a1a1a;
    margin: 0 0 12px 0;
}

.no-jobs-content p {
    color: #6b7280;
    margin-bottom: 24px;
    line-height: 1.6;
}

.back-to-departments {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-to-departments:hover {
    background: #059669;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
    /* Tablet - 2 columns */
    .jobs-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

@media (max-width: 768px) {
    /* Mobile - 1 column (stacked) */
    .jobs-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .department-header {
        padding: 24px 20px;
    }
    
    .department-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .department-title {
        font-size: 2rem;
    }
    
    .logo-placeholder {
        width: 60px;
        height: 60px;
        font-size: 18px;
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

@media (max-width: 480px) {
    /* Extra small phones */
    .jobs-grid {
        gap: 12px;
        padding: 10px 0;
    }
    
    .card {
        padding: 16px;
        min-height: 160px;
    }
    
    .card-top {
        gap: 12px;
    }
    
    .card-bottom {
        gap: 16px;
    }
    
    .product-title {
        font-size: 20px;
    }
    
    .opportunities-count {
        font-size: 14px;
    }
    
    .see-all-btn {
        padding: 10px 0;
        font-size: 14px;
    }
}
</style>
