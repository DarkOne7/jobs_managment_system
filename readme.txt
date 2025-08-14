=== Job Management System ===
Contributors: yourname
Tags: jobs, employment, careers, departments, hr, applications, recruitment
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive job management system with departments, locations, jobs, and applications management using custom database entities.

== Description ==

Job Management System is a complete WordPress plugin for managing job postings, applications, and recruitment processes. Built with custom database tables for optimal performance and flexibility.

### Key Features:

* **Department Management**: Create and manage job departments with custom colors and icons
* **Location Management**: Organize jobs by location with SEO-friendly slugs
* **Job Posting**: Comprehensive job posting system with rich descriptions, requirements, and benefits
* **Application System**: Complete application handling with CV uploads and status tracking
* **Email Notifications**: Automated email system for applicants and administrators
* **Admin Dashboard**: Intuitive admin interface for managing all aspects of the job system
* **Responsive Design**: Mobile-friendly templates that work on all devices
* **File Upload**: Secure CV/resume upload system with file validation
* **Application Status**: Track applications through pending, approved, rejected, and shortlisted states

### Page Structure:

1. `/jobs/` - All job departments overview
2. `/jobs/department/{department-slug}/` - Jobs in specific department
3. `/jobs/location/{location-slug}/` - Jobs in specific location
4. `/jobs/{job-slug}/` - Individual job details with application form
5. `/jobs/application/success/` - Application confirmation page

### Entities:

**Departments:**
- Name, Slug, Color, Icon, Description

**Locations:**
- Name, Slug, Description

**Jobs:**
- Name, Slug, Department, Location, Work Type, Salary, Application Deadline, Description, Requirements, Benefits, Status

**Applications:**
- Name, Email, Phone, Job, CV File, Cover Letter, Status, Admin Notes, Timestamps

### Admin Features:

* Dashboard with statistics and recent applications
* Full CRUD operations for all entities
* Application review and approval system
* Email notification settings
* File upload settings and management
* Bulk operations and filtering

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/job-system/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Job Management' in your admin menu
4. Configure settings and start adding departments and locations
5. Create jobs and start receiving applications

== Frequently Asked Questions ==

= How do I add a new department? =

1. Go to Job Management > Departments
2. Click "Add New Department"
3. Fill in the name, description, choose a color and icon
4. Save the department

= How do I manage job applications? =

1. Go to Job Management > Applications
2. View all applications with their status
3. Click on any application to view details
4. Update status and add notes
5. Email notifications are sent automatically

= Can I customize the email templates? =

Currently, email templates are built-in but you can modify them by editing the email-handler.php file. Custom template support will be added in future versions.

= What file types are supported for CV uploads? =

By default: PDF, DOC, and DOCX files up to 5MB. You can modify these settings in Job Management > Settings.

= How do I customize the appearance? =

The plugin includes comprehensive CSS that respects your theme. You can override styles by adding custom CSS to your theme or using the WordPress Customizer.

== Screenshots ==

1. Job departments overview page
2. Jobs listing in a department
3. Individual job posting with application form
4. Admin dashboard with statistics
5. Application management interface
6. Department management screen

== Changelog ==

= 2.0.0 =
* Complete rewrite using custom database tables
* Added comprehensive application management system
* Implemented email notification system
* Added file upload functionality for CVs
* Created responsive admin interface
* Added application status tracking
* Implemented department and location management
* Added job filtering and search capabilities
* Converted all text to English
* Added mobile-responsive design
* Implemented AJAX form submissions
* Added social sharing functionality

= 1.0.0 =
* Initial release with basic functionality

== Upgrade Notice ==

= 2.0.0 =
Major update with complete rewrite. Please backup your data before upgrading. This version uses custom database tables instead of post types for better performance and flexibility.

== Technical Details ==

### Database Tables:
* `wp_jms_departments` - Job departments
* `wp_jms_locations` - Job locations  
* `wp_jms_jobs` - Job postings
* `wp_jms_applications` - Job applications

### File Structure:
```
job-system/
├── job-system.php              # Main plugin file
├── includes/
│   ├── database.php           # Database management
│   ├── admin.php              # Admin interface
│   ├── entities.php           # Entity classes
│   ├── templates.php          # Template handler
│   ├── ajax-handlers.php      # AJAX processing
│   └── email-handler.php      # Email notifications
├── templates/
│   ├── departments-archive.php
│   ├── department-jobs.php
│   ├── single-job.php
│   └── application-success.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── admin/                     # Admin templates (future)
├── readme.txt
└── uninstall.php
```

### Custom URLs:
The plugin creates custom URL structure for better SEO and user experience. All URLs are automatically generated based on slugs.

### Security Features:
* Nonce verification for all forms
* File upload validation
* SQL injection prevention
* XSS protection
* Capability checks for admin functions

### Performance:
* Custom database tables for optimal queries
* Minimal dependencies
* Efficient caching
* Mobile-optimized assets

== Support ==

For support, feature requests, or bug reports, please visit our support page or contact us directly.

== Privacy ==

This plugin stores job application data including names, email addresses, phone numbers, and uploaded CV files. This data is used solely for recruitment purposes. The plugin includes options to delete applications and associated files. Please ensure compliance with local privacy laws (GDPR, CCPA, etc.) when using this plugin.