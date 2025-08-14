<?php
/**
 * Template for application success page
 * URL: /jobs/application/success/
 */

global $jms_data;
$page_title = $jms_data['page_title'] ?? 'Application Submitted';
$page_description = $jms_data['page_description'] ?? 'Your job application has been submitted successfully';

get_header(); ?>

<div class="jms-container">
    <div class="jms-header">
        <div class="container">
            <h1 class="page-title"><?php echo esc_html($page_title); ?></h1>
            <p class="page-description"><?php echo esc_html($page_description); ?></p>
        </div>
    </div>

    <div class="container">
        <div class="success-content">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h2>Thank You for Your Application!</h2>
                
                <div class="success-message">
                    <p>Your job application has been successfully submitted and received by our hiring team.</p>
                    
                    <div class="next-steps">
                        <h3>What happens next?</h3>
                        <div class="steps-grid">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Application Review</h4>
                                    <p>Our hiring team will carefully review your application and qualifications.</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Initial Screening</h4>
                                    <p>Qualified candidates will be contacted for initial screening or interview.</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Further Process</h4>
                                    <p>Shortlisted candidates will proceed to the next stage of our hiring process.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-info">
                        <h3>Timeline</h3>
                        <p>We typically respond to applications within <strong>5-7 business days</strong>. You will receive an email notification regarding the status of your application.</p>
                    </div>
                    
                    <div class="contact-info">
                        <h3>Questions?</h3>
                        <p>If you have any questions about your application or the position, please don't hesitate to contact our HR team.</p>
                    </div>
                </div>
                
                <div class="success-actions">
                    <a href="<?php echo esc_url(home_url('/jobs/')); ?>" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Browse More Jobs
                    </a>
                    
                    <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-outline">
                        <i class="fas fa-home"></i>
                        Return to Homepage
                    </a>
                </div>
            </div>
            
            <div class="additional-info">
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Confirmation</h4>
                        <p>You should receive a confirmation email shortly. Please check your spam folder if you don't see it in your inbox.</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Join Our Community</h4>
                        <p>Follow us on social media to stay updated on new job opportunities and company news.</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h4>Job Alerts</h4>
                        <p>Sign up for job alerts to be notified when new positions matching your interests become available.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-content {
    padding: 60px 0;
    max-width: 800px;
    margin: 0 auto;
}

.success-card {
    background: white;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 40px;
}

.success-icon {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 30px;
}

.success-card h2 {
    color: #333;
    margin-bottom: 30px;
    font-size: 2rem;
}

.success-message {
    text-align: left;
    margin-bottom: 30px;
}

.success-message > p {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 30px;
}

.next-steps h3,
.timeline-info h3,
.contact-info h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.3rem;
}

.steps-grid {
    display: grid;
    gap: 20px;
    margin-bottom: 30px;
}

.step-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.step-number {
    background: #007cba;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    margin-bottom: 5px;
    color: #333;
}

.step-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.timeline-info,
.contact-info {
    padding: 20px;
    background: #e8f4f8;
    border-radius: 8px;
    margin-bottom: 20px;
}

.timeline-info p,
.contact-info p {
    margin: 0;
    color: #666;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.info-icon {
    font-size: 2rem;
    color: #007cba;
    margin-bottom: 15px;
}

.info-card h4 {
    color: #333;
    margin-bottom: 10px;
}

.info-card p {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

@media (max-width: 768px) {
    .success-card {
        padding: 20px;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .success-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .step-item {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        align-self: center;
    }
}
</style>

<?php get_footer(); ?>

