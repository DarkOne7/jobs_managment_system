# Job System Custom Pages Guide

## New Update: Custom Pages Instead of Shortcodes

The job system has been updated to work with custom pages instead of shortcodes.

## New URLs:

### 1. Main Departments Page:
```
https://yoursite.com/jobs
```
- Displays all departments in a grid layout
- Allows searching through departments
- Clicking on a department navigates to the jobs page

### 2. Department Jobs Page:
```
https://yoursite.com/jobs/department?id=1
```
- `id=1` is the department term ID
- Displays all jobs in the specified department
- Contains "Apply Now" buttons for each job

## How It Works:

### 1. System Setup:
- Activating the plugin will automatically set up custom URLs
- Rewrite rules will be flushed upon activation

### 2. Navigation:
- User goes to `/jobs` to see departments
- Clicking on a department navigates to `/jobs/department?id=X`
- Where X is the department term ID (term_id)

### 3. Available Features:
- Search through departments
- Display jobs with detailed information
- Application form with CV upload
- Return to main page

## Updated Files:

### 1. class-job-system.php:
- Added `add_rewrite_rules()` and `handle_custom_pages()` functions
- Set up routing for custom pages
- Handle page display

### 2. templates/departments-page.php:
- Updated JavaScript to navigate to `/jobs/department?id=X`
- Added `data-department-id` to cards

### 3. templates/department-jobs.php:
- Updated to work with department ID instead of slug
- Updated return links

### 4. frontend.js:
- Updated click handlers to navigate to new URLs
- Full support for modal and interaction

## Testing the System:

### 1. Create Departments:
```
Jobs > Job Departments > Add New Department
```

### 2. Create Jobs:
```
Jobs > Add New Job
```

### 3. Visit Pages:
```
yoursite.com/jobs
yoursite.com/jobs/department?id=1
```

## Important Notes:

1. **Reactivate Plugin**: If URLs don't work, deactivate and reactivate the plugin

2. **Permalink Settings**: Make sure WordPress Permalinks are enabled (not Default)

3. **404 Errors**: If you get 404 pages, go to Settings > Permalinks and press Save

4. **Browser Cache**: You may need to clear browser cache to see updates

## New Benefits:

1. **Better URLs**: `/jobs` is clearer than shortcodes
2. **Improved SEO**: Search engines understand the structure better
3. **Better User Experience**: Smooth navigation between pages
4. **Easy Sharing**: Direct links to departments

## Troubleshooting:

### If Pages Don't Work:
1. Check if plugin is activated
2. Go to Settings > Permalinks and press Save
3. Make sure departments and jobs exist
4. Check browser console for errors

### If Application Doesn't Work:
1. Check if JavaScript is enabled
2. Make sure AJAX settings are correct
3. Check file upload permissions

## Technical Implementation:

### URL Structure:
- `/jobs/` - Main departments page
- `/jobs/department?id=X` - Department jobs page
- `/jobs-new/` - New jobs page (alternative)
- `/jobs-test/` - Test page

### Database Integration:
- Uses WordPress taxonomies for departments and locations
- Custom post type 'job' for job listings
- Custom post type 'job_application' for applications

### Frontend Features:
- Responsive design with Poppins font
- AJAX application submission
- File upload support (PDF, DOC, DOCX, JPG, PNG)
- Modal application forms

### Admin Features:
- Application management dashboard
- Status tracking (pending, approved, rejected)
- CSV export functionality
- Email testing tools

## Customization Options:

### Styling:
- Modify `assets/css/frontend.css` for custom styles
- Update templates in `templates/` directory
- Add custom CSS to your theme

### Functionality:
- Extend shortcodes: `[job_list]` and `[job_departments]`
- Customize application form fields
- Modify email templates

## Security Considerations:

1. **File Upload**: Restricted to specific file types
2. **Form Validation**: Server-side and client-side validation
3. **Nonce Verification**: CSRF protection for forms
4. **Input Sanitization**: All user inputs are sanitized

## Performance Optimization:

1. **Caching**: Compatible with WordPress caching plugins
2. **Database Queries**: Optimized queries for job listings
3. **Asset Loading**: CSS and JS loaded only when needed
4. **Image Optimization**: Responsive images for better loading

---

**For support and updates, check the main README.md file.**
