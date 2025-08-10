<?php
/**
 * Sample content and setup for Job System Plugin
 * This file contains example data and instructions for setting up the plugin
 */

// This file should be run after plugin activation to create sample content

function job_system_create_sample_content() {
    // Create sample departments
    $departments = array(
        array(
            'name' => 'Product',
            'slug' => 'product',
            'color' => '#4CAF50'
        ),
        array(
            'name' => 'Commercial',
            'slug' => 'commercial', 
            'color' => '#FF5722'
        ),
        array(
            'name' => 'Finance Operations',
            'slug' => 'finance-operations',
            'color' => '#FFC107'
        ),
        array(
            'name' => 'Supply Chain',
            'slug' => 'supply-chain',
            'color' => '#00BCD4'
        ),
        array(
            'name' => 'People & Culture',
            'slug' => 'people-culture',
            'color' => '#9C27B0'
        ),
        array(
            'name' => 'Customer Experience',
            'slug' => 'customer-experience',
            'color' => '#2196F3'
        )
    );
    
    foreach ($departments as $dept) {
        $term = wp_insert_term($dept['name'], 'job_department', array(
            'slug' => $dept['slug']
        ));
        
        if (!is_wp_error($term)) {
            update_term_meta($term['term_id'], 'department_color', $dept['color']);
        }
    }
    
    // Create sample locations
    $locations = array(
        array(
            'name' => 'Cairo, Egypt',
            'slug' => 'cairo-egypt',
            'color' => '#f97316'
        ),
        array(
            'name' => 'Alexandria, Egypt',
            'slug' => 'alexandria-egypt',
            'color' => '#3b82f6'
        ),
        array(
            'name' => 'Remote - MENA',
            'slug' => 'remote-mena',
            'color' => '#10b981'
        ),
        array(
            'name' => 'Dubai, UAE',
            'slug' => 'dubai-uae',
            'color' => '#8b5cf6'
        ),
        array(
            'name' => 'Riyadh, Saudi Arabia',
            'slug' => 'riyadh-saudi',
            'color' => '#f59e0b'
        ),
        array(
            'name' => 'Remote - Global',
            'slug' => 'remote-global',
            'color' => '#ef4444'
        )
    );
    
    foreach ($locations as $loc) {
        $term = wp_insert_term($loc['name'], 'job_location', array(
            'slug' => $loc['slug']
        ));
        
        if (!is_wp_error($term)) {
            update_term_meta($term['term_id'], 'location_color', $loc['color']);
        }
    }
    
    // Create sample jobs
    $jobs = array(
        array(
            'title' => 'Senior Software Engineer',
            'content' => 'As a Software Testing Engineer II, I write, execute, and maintain test cases, report bugs, and estimate and prioritize testing activities. I track and analyze technical/client bugs, test business requirements, detect UI bugs, and collaborate closely with the team in an Agile environment. I stay updated on new testing tools and strategies.',
            'department' => 'product',
            'location' => 'Dokki, Cairo, Egypt',
            'location_taxonomy' => 'cairo-egypt',
            'work_type' => 'remote',
            'deadline' => date('Y-m-d H:i', strtotime('+30 days'))
        ),
        array(
            'title' => 'Senior Product Designer',
            'content' => 'We are looking for a talented Product Designer to join our team and help create amazing user experiences.',
            'department' => 'product',
            'location' => 'Remote',
            'location_taxonomy' => 'remote-mena',
            'work_type' => 'remote',
            'deadline' => date('Y-m-d H:i', strtotime('+25 days'))
        ),
        array(
            'title' => 'Product Manager Team Leader',
            'content' => 'Lead a team of product managers and drive product strategy across multiple initiatives.',
            'department' => 'product',
            'location' => 'Cairo, Egypt',
            'location_taxonomy' => 'cairo-egypt',
            'work_type' => 'hybrid',
            'deadline' => date('Y-m-d H:i', strtotime('+20 days'))
        ),
        array(
            'title' => 'Head of Product',
            'content' => 'Strategic leadership role overseeing the entire product organization.',
            'department' => 'product',
            'location' => 'Cairo, Egypt',
            'location_taxonomy' => 'cairo-egypt',
            'work_type' => 'onsite',
            'deadline' => date('Y-m-d H:i', strtotime('+45 days'))
        ),
        array(
            'title' => 'Senior Product Analyst',
            'content' => 'Analyze product performance and provide insights to drive decision making.',
            'department' => 'product',
            'location' => 'Remote',
            'location_taxonomy' => 'remote-global',
            'work_type' => 'remote',
            'deadline' => date('Y-m-d H:i', strtotime('+35 days'))
        )
    );
    
    foreach ($jobs as $job) {
        $post_id = wp_insert_post(array(
            'post_title' => $job['title'],
            'post_content' => $job['content'],
            'post_status' => 'publish',
            'post_type' => 'job'
        ));
        
        if ($post_id) {
            // Set department
            wp_set_object_terms($post_id, $job['department'], 'job_department');
            
            // Set location taxonomy
            if (isset($job['location_taxonomy'])) {
                wp_set_object_terms($post_id, $job['location_taxonomy'], 'job_location');
            }
            
            // Set meta fields
            update_post_meta($post_id, '_application_deadline', $job['deadline']);
            update_post_meta($post_id, '_job_location', $job['location']);
            update_post_meta($post_id, '_work_type', $job['work_type']);
        }
    }
    
    // Create sample pages
    create_sample_pages();
}

function create_sample_pages() {
    // Jobs page
    $jobs_page = array(
        'post_title' => 'Jobs',
        'post_content' => '<h2>We have <span style="color: #00a0d2;">350</span> open position</h2>

[job_departments]

<h3>All Open Positions</h3>
[job_list]',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    
    wp_insert_post($jobs_page);
    
    // Opportunities page
    $opportunities_page = array(
        'post_title' => 'Opportunities',
        'post_content' => '<h2>Join Our Team</h2>
<p>Discover exciting career opportunities across various departments.</p>

[job_departments]',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    
    wp_insert_post($opportunities_page);
    
    // Product jobs page
    $product_page = array(
        'post_title' => 'Product Team Jobs',
        'post_content' => '<h2>Product Team Opportunities</h2>
<p>Join our product team and help build amazing user experiences.</p>

[job_list department="product"]',
        'post_status' => 'publish',
        'post_type' => 'page'
    );
    
    wp_insert_post($product_page);
}

// Instructions for manual setup
?>

<!-- 
SETUP INSTRUCTIONS:

1. After activating the plugin, you can run the sample content creation by adding this to your functions.php temporarily:

add_action('init', 'job_system_create_sample_content');

2. Or manually create:

DEPARTMENTS:
- Go to Jobs > Departments
- Add these departments with colors:
  * Product (#4CAF50)
  * Commercial (#FF5722)
  * Finance Operations (#FFC107)
  * Supply Chain (#00BCD4)
  * People & Culture (#9C27B0)
  * Customer Experience (#2196F3)

PAGES:
- Create a page called "Jobs" with content: [job_departments] and [job_list]
- Create a page called "Opportunities" with content: [job_departments]
- Create individual job posts in Jobs > Add New

SHORTCODES:
- [job_departments] - Shows all departments in a grid
- [job_list] - Shows all jobs
- [job_list department="product"] - Shows jobs from specific department
- [job_list limit="5"] - Limit number of jobs shown

The plugin will automatically:
- Add "Apply Now" buttons to job pages
- Show application modal when clicked
- Handle file uploads and form submissions
- Send email notifications to admin
- Provide admin interface for managing applications

-->
