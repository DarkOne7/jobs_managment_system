# Job System - WordPress Plugin

A complete job management system for WordPress with departments, job listings, and application forms. Built with modern UI/UX and comprehensive admin functionality.

![Job System Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)
![Version](https://img.shields.io/badge/Version-1.0.0-green.svg)
![License](https://img.shields.io/badge/License-GPL%20v2-orange.svg)

## 🚀 Features

### Core Functionality
- **Custom Job Post Type** - Manage job listings with custom fields
- **Department Management** - Organize jobs by departments with color coding
- **Location Management** - Categorize jobs by location
- **Application System** - Built-in job application forms with file upload
- **Admin Dashboard** - Comprehensive admin interface for managing applications

### Frontend Features
- **Modern UI/UX** - Clean, responsive design with Poppins font
- **Job Headers** - Custom job page headers with apply buttons
- **Application Modal** - Popup application forms with file upload
- **Shortcodes** - Easy integration with `[job_list]` and `[job_departments]`
- **Custom Pages** - Automatic `/jobs/`, `/jobs-new/` page generation

### Admin Features
- **Application Management** - View, edit, and manage job applications
- **Status Tracking** - Track application status (pending, approved, rejected)
- **CSV Export** - Export applications to CSV format
- **Email Testing** - Built-in email testing functionality
- **Quick Edit** - Quick edit applications from admin panel

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 🛠️ Installation

### Method 1: Manual Installation
1. Download the plugin files
2. Upload the `job-system` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **Jobs > Departments** to create your first department
5. Go to **Jobs > Add New** to create your first job posting

### Method 2: Git Clone
```bash
cd wp-content/plugins/
git clone https://github.com/yourusername/job-system.git
```

## 🎯 Quick Start

### 1. Create Departments
1. Go to **Jobs > Departments**
2. Add departments with custom colors:
   - Product (#4CAF50)
   - Engineering (#2196F3)
   - Marketing (#FF5722)
   - Sales (#FFC107)
   - HR & Operations (#9C27B0)

### 2. Create Locations
1. Go to **Jobs > Locations**
2. Add locations like:
   - Cairo, Egypt
   - Remote
   - Dubai, UAE

### 3. Create Job Posts
1. Go to **Jobs > Add New**
2. Fill in job details:
   - Title and description
   - Select department and location
   - Set work type (onsite/remote)
   - Set application deadline

### 4. Display Jobs on Your Site
Use shortcodes on any page:
```
[job_departments]  // Shows all departments
[job_list]         // Shows all jobs
[job_list department="product"]  // Shows jobs from specific department
```

## 📁 File Structure

```
job-system/
├── job-system.php                 # Main plugin file
├── README.md                      # This file
├── includes/
│   ├── class-job-system.php       # Main plugin class
│   ├── class-job-system-admin.php # Admin functionality
│   ├── class-job-system-frontend.php # Frontend functionality
│   └── class-job-system-ajax.php  # AJAX handlers
├── templates/
│   ├── departments-page.php       # Departments listing page
│   ├── department-jobs.php        # Department jobs page
│   ├── locations-page.php         # Locations listing page
│   ├── location-jobs.php          # Location jobs page
│   └── jobs-new.php              # New jobs page
├── assets/
│   ├── css/
│   │   ├── admin.css             # Admin styles
│   │   └── frontend.css          # Frontend styles
│   ├── js/
│   │   ├── admin.js              # Admin scripts
│   │   └── frontend.js           # Frontend scripts
│   └── images/
│       └── file.png              # File upload icon
└── sample-content.php            # Sample data creation
```

## 🎨 Customization

### Styling
The plugin uses modern CSS with Poppins font. You can customize styles by:
1. Adding custom CSS to your theme
2. Modifying the styles in `assets/css/frontend.css`

### Templates
Customize job display by editing templates in the `templates/` directory:
- `departments-page.php` - Departments grid layout
- `department-jobs.php` - Jobs listing within departments
- `jobs-new.php` - New jobs page layout

### Shortcodes
Available shortcodes for easy integration:
- `[job_departments]` - Display departments grid
- `[job_list]` - Display job listings
- `[job_list department="department-slug"]` - Filter by department

## 🔧 Configuration

### Custom Fields
Jobs include these custom fields:
- **Department** - Taxonomy-based department selection
- **Location** - Taxonomy-based location selection
- **Work Type** - Onsite/Remote options
- **Application Deadline** - Date picker for application cutoff

### Admin Settings
- **Applications Management** - View and manage all applications
- **Status Updates** - Change application status
- **CSV Export** - Export applications data
- **Email Testing** - Test email functionality

## 🚀 Advanced Features

### AJAX Application Submission
- Real-time form submission without page reload
- File upload support (PDF, DOC, DOCX, JPG, PNG)
- Form validation and error handling
- Success/error messaging

### Responsive Design
- Mobile-friendly application forms
- Responsive job listings
- Touch-friendly interface

### SEO Friendly
- Custom post types with proper URLs
- Meta data support
- Clean URL structure

## 🐛 Troubleshooting

### Common Issues

**Plugin won't activate:**
- Check PHP version (requires 7.4+)
- Ensure all files are uploaded correctly
- Check WordPress debug log for errors

**Jobs not displaying:**
- Verify departments and locations are created
- Check if jobs are published (not drafts)
- Ensure shortcodes are used correctly

**Application form not working:**
- Check if AJAX is enabled
- Verify file upload permissions
- Check browser console for JavaScript errors

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📝 Changelog

### Version 1.0.0
- Initial release
- Custom job post type
- Department and location management
- Application system with file upload
- Admin dashboard
- Responsive design
- Shortcode support

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/DarkOne7/jobs_managment_system/issues)
- **Documentation**: [Wiki](https://github.com/DarkOne7/jobs_managment_system/wiki)
- **Email**: your-adhamshawki@outlook.com

## 🙏 Acknowledgments

- Built for WordPress community
- Uses modern web standards
- Responsive design principles
- Accessibility considerations

---

**Made with ❤️ for the WordPress community**

