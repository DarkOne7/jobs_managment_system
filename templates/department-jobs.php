<?php
/**
 * Template for displaying jobs in a specific department
 * URL: /jobs/department/{department_slug}
 */

global $jms_data;
$department = $jms_data['department'] ?? null;
$jobs = $jms_data['jobs'] ?? array();
$page_title = $jms_data['page_title'] ?? 'Jobs';
$page_description = $jms_data['page_description'] ?? '';

if (!$department) {
    wp_redirect(home_url('/jobs/'));
    exit;
}

$department_color = JMS_Templates::get_department_color($department->color);
$department_icon = JMS_Templates::get_department_icon($department->icon);

get_header(); ?>

<style>
/* Department Jobs Page Styles */
.job-system-container {
    padding: 0;
    margin: 0;
}

.jms-header {
    color: white;
    padding: 100px 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}





.separator {
    margin: 0 10px;
}

.department-header-content {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.page-title {
    color: white;
    font-size: 3.5rem;
    margin-bottom: 20px;
    font-weight: 700;
    text-align: center;
}

.department-description {
    font-size: 18px;
    margin-bottom: 25px;
    opacity: 0.9;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    line-height: 1.6;
}

.jobs-count-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
}

.jobs-listing {
    padding: 0;
}

.jobs-filter {
    margin-bottom: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.filter-options label {
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.filter-options select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 16px;
    background: white;
    width: 200px;
    max-width: 100%;
}

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    padding: 60px 0;
    align-items: stretch;
}

.job-card {
    font-family: "Poppins", -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: white;
    border-radius: 12px;
    padding: 24px;
    width: 100%;
    border: 1px solid #DFDFDF;
    position: relative;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    min-height: 250px;
}

.job-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.job-card-color {
    border-left: 4px solid #0284c7;
    border-radius: 4px;
    padding-left: 18px;
    text-align: left;
    margin-bottom: 16px;
}

.job-label {
    color: #868686;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
}

.job-card-title {
    font-size: 28px;
    font-weight: 600;
    color: #000000;
    margin: 0;
    line-height: 1.2;
}

.job-card-title a {
    color: #000000;
    text-decoration: none;
    transition: color 0.3s ease;
}

.job-card-title a:hover {
    color: #007cba;
}

.job-location-info {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 24px;
    font-weight: 500;
}

.job-see-all-btn {
    width: 100%;
    background: transparent;
    border: 2px solid #14A26A;
    color: #14A26A;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    font-family: "Poppins", -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    text-align: center;
    display: block;
    margin-top: auto;
}

.job-see-all-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
    text-decoration: none;
}

.job-see-all-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}

.job-see-all-btn:active {
    transform: translateY(1px);
}

.job-department-badge {
    background-color: #007cba;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    white-space: nowrap;
}

.job-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 25px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.meta-item i {
    color: #007cba;
    width: 16px;
}

.job-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: #007cba;
    color: white;
}

.btn-primary:hover {
    background-color: #005a87;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #007cba;
    border: 2px solid #007cba;
}

.btn-outline:hover {
    background: #007cba;
    color: white;
}

.job-date {
    color: #999;
    font-size: 0.9rem;
}

.no-jobs {
    text-align: center;
    padding: 80px 20px;
}

.no-content-message {
    color: #666;
}

.no-content-message i {
    color: #ccc;
    margin-bottom: 20px;
}

.no-content-message h3 {
    margin-bottom: 10px;
    color: #333;
}

@media (max-width: 768px) {
    .jms-header {
        padding: 60px 0;
    }
    
    .page-title {
        font-size: 2.5rem;
    }
    
    .department-description {
        font-size: 1rem;
        padding: 0 10px;
    }
    
    .jobs-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 40px 0;
    }
}

@media (max-width: 1200px) {
    .jobs-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .jobs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .job-card {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .job-card-title {
        font-size: 24px;
    }
    
    .job-see-all-btn {
        padding: 14px 16px;
        font-size: 16px;
    }
}
</style>

<div class="jms-container">
    <div class="jms-header" style="background: black;border-bottom: solid #14A26A 3px;">
        <div class="container">
           
            <div class="department-header-content">
                <h1 class="page-title"><?php echo esc_html($department->name); ?></h1>
                
                
            </div>
        </div>
    </div>

    <div class="container">
        <div class="jobs-listing">
            <?php if (!empty($jobs)): ?>
                
                
                <div class="jobs-grid">
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card" data-work-type="<?php echo esc_attr($job->work_type); ?>">
                            <div class="job-card-color" style="border-left-color: <?php echo esc_attr($department_color); ?> !important;">
                                <div class="job-label">Position</div>
                                <h2 class="job-card-title">
                                    <a href="<?php echo esc_url(home_url('/jobs/' . $job->slug)); ?>">
                                        <?php echo esc_html($job->name); ?>
                                    </a>
                                </h2>
                            </div>
                            <p class="job-location-info">
                                <?php echo esc_html($job->location_name); ?>
                                <?php if (!empty($job->work_type)): ?>
                                    • <?php echo esc_html(JMS_Templates::format_work_type($job->work_type)); ?>
                                <?php endif; ?>
                            </p>
                            <a href="<?php echo esc_url(home_url('/jobs/' . $job->slug)); ?>" class="job-see-all-btn">
                                Know more
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-jobs">
                    <div class="no-content-message">
                        <i class="fas fa-briefcase fa-3x"></i>
                        <h3>No Jobs Available</h3>
                        <p>There are currently no open positions in the <?php echo esc_html($department->name); ?> department.</p>
                        <a href="<?php echo esc_url(home_url('/jobs/')); ?>" class="btn btn-outline">
                            Browse Other Departments
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Simple filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filter = document.getElementById('work-type-filter');
    const jobCards = document.querySelectorAll('.job-card');
    
    if (filter) {
        filter.addEventListener('change', function() {
            const selectedType = this.value;
            
            jobCards.forEach(function(card) {
                if (selectedType === '' || card.dataset.workType === selectedType) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php get_footer(); ?>