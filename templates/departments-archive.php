<?php
/**
 * Template for displaying all job departments
 * URL: /jobs/
 */

global $jms_data;
$departments = $jms_data['departments'] ?? array();
$page_title = $jms_data['page_title'] ?? 'Job Departments';
$page_description = $jms_data['page_description'] ?? 'Explore job opportunities by department';

get_header(); ?>

<style>
/* Departments Archive Page Styles */

.jms-header{
    background: linear-gradient(135deg, #D3F8DFBF 0%, #D3F8DF40 100%);
    color: white;
    border-bottom: solid #14A26A 3px;
    padding: 60px 0;
    text-align: left;
}

.job-system-container {
    padding: 0;
    margin: 0;
}

.job-system-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}

.job-system-header .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 39px;
}

.page-title {
    color: #000000;
    font-size: 3rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.page-description {
    font-weight: 500;
    color: #4E4E4E;
    font-size: 18px;
    opacity: 0.9;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    padding: 60px 0;
    align-items: stretch;
}

.department-card {
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
    min-height: 200px;
}

.department-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.department-card-color {
    border-left: 4px solid #0284c7;
    border-radius: 4px;
    padding-left: 18px;
    text-align: left;
    margin-bottom: 16px;
}

.department-label {
    color: #868686;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
}

.department-card-title {
    font-size: 28px;
    font-weight: 600;
    color: #000000;
    margin: 0;
    line-height: 1.2;
}

.department-opportunities {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 24px;
    font-weight: 500;
}

.department-see-all-btn {
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

.department-see-all-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #374151;
    text-decoration: none;
}

.department-see-all-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
}

.department-see-all-btn:active {
    transform: translateY(1px);
}

.no-departments {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
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
    .page-title {
        font-size: 2rem;
    }
    
    .departments-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 40px 0;
    }
    
    .department-card {
        max-width: 100%;
        margin: 0 auto;
    }
    
    .department-card-title {
        font-size: 24px;
    }
    
    .department-see-all-btn {
        padding: 14px 16px;
        font-size: 16px;
    }
}
</style>

<div class="jms-container">
    <div class="jms-header">
        <div class="container">
            <h1 class="page-title">Join our <span style="color: #14A26A;">Roster of Experts!</span></h1>
            <p class="page-description">EXACT BUSINESS is a Cairo-based consultancy helping startups and EU-funded projects</p>
        </div>
    </div>

    <div class="container">
        <div class="departments-grid">
            <?php if (!empty($departments)): ?>
                <?php foreach ($departments as $department): 
                    $department_color = JMS_Templates::get_department_color($department->color);
                    $department_icon = JMS_Templates::get_department_icon($department->icon);
                    $jobs_count = $department->jobs_count ?? 0;
                ?>
                    <div class="department-card">
                        <div class="department-card-color" style="border-left-color: <?php echo esc_attr($department_color); ?> !important;">
                            <div class="department-label">Team</div>
                            <h2 class="department-card-title"><?php echo esc_html($department->name); ?></h2>
                        </div>
                        <p class="department-opportunities"><?php echo $jobs_count; ?> <?php echo $jobs_count === 1 ? 'Opportunity' : 'Opportunities'; ?></p>
                        <a href="<?php echo esc_url(home_url('/jobs/department/' . $department->slug)); ?>" class="department-see-all-btn">
                            View all
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-departments">
                    <div class="no-content-message">
                        <i class="fas fa-building fa-3x"></i>
                        <h3>No Departments Available</h3>
                        <p>Job departments will be added soon</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>