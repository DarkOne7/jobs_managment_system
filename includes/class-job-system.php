<?php
/**
 * Main Job System Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Job_System {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor logic here if needed
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        add_action('init', array($this, 'create_post_types'));
        add_action('init', array($this, 'create_taxonomies'));
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Super early check for job pages
        add_action('init', array($this, 'check_job_pages_early'), 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add safety filter to prevent null post access
        add_filter('post_link', array($this, 'safe_post_link'), 10, 2);
        
        // Add safety filter to prevent post_type warnings
        add_filter('get_post_metadata', array($this, 'safe_post_metadata'), 10, 4);
        add_filter('page_link', array($this, 'safe_post_link'), 10, 2);
        
        // Custom routing hooks
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'handle_parse_request'), 1);
        add_action('template_redirect', array($this, 'handle_custom_pages'), 1);
        add_filter('template_include', array($this, 'custom_template_include'), 99);
        
        // Early interception for job pages
        add_action('parse_request', array($this, 'early_parse_request'), 0);
        
        // Prevent canonical redirects for our custom pages
        add_filter('redirect_canonical', array($this, 'prevent_canonical_redirect'), 10, 2);
        
        // Add Elementor config for custom pages
        add_action('wp_head', array($this, 'add_elementor_config'), 1);
        
        // Initialize admin and frontend classes
        if (is_admin()) {
            new Job_System_Admin();
        } else {
            new Job_System_Frontend();
        }
        
        // Initialize AJAX handler (always needed for both admin and frontend)
        new Job_System_Ajax();
        
        // Check if default locations and departments exist, if not create them
        add_action('wp_loaded', array($this, 'ensure_default_locations'));
        add_action('wp_loaded', array($this, 'ensure_default_departments'));
        add_action('job_system_ensure_locations', array($this, 'ensure_default_locations'));
        add_action('job_system_ensure_departments', array($this, 'ensure_default_departments'));
        
        // Let Elementor work naturally - no interference needed
        $this->integrate_with_elementor();
        
        // Add debug functionality
        add_action('wp_footer', array($this, 'debug_elementor_integration'));
    }
    
    /**
     * Get a valid existing page ID to use as context for virtual pages
     */
    private function get_fallback_page_id() {
        $front_id = (int) get_option('page_on_front');
        if ($front_id) {
            return $front_id;
        }
        $ids = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        if (!empty($ids) && isset($ids[0])) {
            return (int) $ids[0];
        }
        return 0;
    }
    

    /**
     * Create custom post types
     */
    public function create_post_types() {
        // Register Job post type
        register_post_type('job', array(
            'labels' => array(
                'name' => __('Jobs', 'job-system'),
                'singular_name' => __('Job', 'job-system'),
                'add_new' => __('Add New Job', 'job-system'),
                'add_new_item' => __('Add New Job', 'job-system'),
                'edit_item' => __('Edit Job', 'job-system'),
                'new_item' => __('New Job', 'job-system'),
                'view_item' => __('View Job', 'job-system'),
                'search_items' => __('Search Jobs', 'job-system'),
                'not_found' => __('No jobs found', 'job-system'),
                'not_found_in_trash' => __('No jobs found in trash', 'job-system'),
            ),
            'public' => true,
            'has_archive' => false, // Disable default archive
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-businessman',
            'rewrite' => array('slug' => 'job-posts'), // Change slug to avoid conflict
        ));
        
        // Register Job Application post type
        register_post_type('job_application', array(
            'labels' => array(
                'name' => __('Job Applications', 'job-system'),
                'singular_name' => __('Job Application', 'job-system'),
                'add_new' => __('Add New Application', 'job-system'),
                'add_new_item' => __('Add New Application', 'job-system'),
                'edit_item' => __('Edit Application', 'job-system'),
                'new_item' => __('New Application', 'job-system'),
                'view_item' => __('View Application', 'job-system'),
                'search_items' => __('Search Applications', 'job-system'),
                'not_found' => __('No applications found', 'job-system'),
                'not_found_in_trash' => __('No applications found in trash', 'job-system'),
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-feedback',
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => false,
            ),
            'map_meta_cap' => true,
        ));
    }
    
    /**
     * Create custom taxonomies
     */
    public function create_taxonomies() {
        // Register Department taxonomy
        register_taxonomy('job_department', 'job', array(
            'labels' => array(
                'name' => __('Departments', 'job-system'),
                'singular_name' => __('Department', 'job-system'),
                'add_new_item' => __('Add New Department', 'job-system'),
                'edit_item' => __('Edit Department', 'job-system'),
                'update_item' => __('Update Department', 'job-system'),
                'view_item' => __('View Department', 'job-system'),
                'separate_items_with_commas' => __('Separate departments with commas', 'job-system'),
                'add_or_remove_items' => __('Add or remove departments', 'job-system'),
                'choose_from_most_used' => __('Choose from the most used', 'job-system'),
                'popular_items' => __('Popular Departments', 'job-system'),
                'search_items' => __('Search Departments', 'job-system'),
                'not_found' => __('Not Found', 'job-system'),
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
        ));
        
        // Register Location taxonomy
        register_taxonomy('job_location', 'job', array(
            'labels' => array(
                'name' => __('Locations', 'job-system'),
                'singular_name' => __('Location', 'job-system'),
                'add_new_item' => __('Add New Location', 'job-system'),
                'edit_item' => __('Edit Location', 'job-system'),
                'update_item' => __('Update Location', 'job-system'),
                'view_item' => __('View Location', 'job-system'),
                'separate_items_with_commas' => __('Separate locations with commas', 'job-system'),
                'add_or_remove_items' => __('Add or remove locations', 'job-system'),
                'choose_from_most_used' => __('Choose from the most used', 'job-system'),
                'popular_items' => __('Popular Locations', 'job-system'),
                'search_items' => __('Search Locations', 'job-system'),
                'not_found' => __('Not Found', 'job-system'),
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('job-system-frontend', JOB_SYSTEM_PLUGIN_URL . 'assets/css/frontend.css', array(), JOB_SYSTEM_VERSION);
        wp_enqueue_script('job-system-frontend', JOB_SYSTEM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), JOB_SYSTEM_VERSION, true);
        
        // Enqueue FontAwesome for job system pages
        $this->enqueue_fontawesome_if_needed();
        
        // Localize script for AJAX
        wp_localize_script('job-system-frontend', 'job_system_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('job_system_nonce'),
        ));
    }
    
    /**
     * Enqueue FontAwesome styles if needed for job system pages
     */
    private function enqueue_fontawesome_if_needed() {
        $job_system_page = get_query_var('job_system_page');
        $should_load_fontawesome = (
            is_singular('job') || 
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            (strpos($_SERVER['REQUEST_URI'], '/jobs') !== false) ||
            (strpos($_SERVER['REQUEST_URI'], '/locations') !== false) ||
            ((get_post() && is_string(get_post()->post_content) && (has_shortcode(get_post()->post_content, 'job_list') || has_shortcode(get_post()->post_content, 'job_departments'))))
        );
        
        if ($should_load_fontawesome) {
            wp_enqueue_style('fontawesome-all', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/all.css', array(), '6.7.2');
            wp_enqueue_style('fontawesome-sharp-solid', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/sharp-solid.css', array(), '6.7.2');
            wp_enqueue_style('fontawesome-sharp-regular', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/sharp-regular.css', array(), '6.7.2');
            wp_enqueue_style('fontawesome-sharp-light', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/sharp-light.css', array(), '6.7.2');
            wp_enqueue_style('fontawesome-duotone', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/duotone.css', array(), '6.7.2');
            wp_enqueue_style('fontawesome-brands', 'https://site-assets.fontawesome.com/releases/v6.7.2/css/brands.css', array(), '6.7.2');
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style('job-system-admin', JOB_SYSTEM_PLUGIN_URL . 'assets/css/admin.css', array(), JOB_SYSTEM_VERSION);
        wp_enqueue_script('job-system-admin', JOB_SYSTEM_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), JOB_SYSTEM_VERSION, true);
        wp_enqueue_style('wp-color-picker');
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database tables
        self::create_database_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database tables
     */
    private static function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create job applications table for better data management
        $table_name = $wpdb->prefix . 'job_applications';
        
        // Check if table already exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                job_id bigint(20) NOT NULL,
                applicant_name varchar(255) NOT NULL,
                applicant_email varchar(255) NOT NULL,
                applicant_phone varchar(50),
                cv_file_path varchar(255),
                application_date datetime DEFAULT CURRENT_TIMESTAMP,
                status varchar(50) DEFAULT 'pending',
                admin_notes text,
                PRIMARY KEY (id),
                KEY idx_job_id (job_id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    /**
     * Add custom rewrite rules for job pages
     */
    public function add_rewrite_rules() {
        // Add rewrite rule for /jobs page with specific query var
        add_rewrite_rule('^jobs/?$', 'index.php?job_system_page=departments', 'top');
        // Add rewrite rule for /jobs-test page (testing departments render)
        add_rewrite_rule('^jobs-test/?$', 'index.php?job_system_page=jobs_test', 'top');
        // Add rewrite rule for /jobs-new page (brand-new page)
        add_rewrite_rule('^jobs-new/?$', 'index.php?job_system_page=jobs_new', 'top');
        
        // Add rewrite rule for /jobs/department?id=X
        add_rewrite_rule('^jobs/department/?$', 'index.php?job_system_page=department_jobs', 'top');
        
        // Add rewrite rule for /locations page
        add_rewrite_rule('^locations/?$', 'index.php?job_system_page=locations', 'top');
        
        // Add rewrite rule for /locations/location?id=X
        add_rewrite_rule('^locations/location/?$', 'index.php?job_system_page=location_jobs', 'top');
        
        // Add query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Add template redirect hook
        add_action('template_redirect', array($this, 'handle_custom_pages'), 1); // Highest priority
        
        // Flush rewrite rules if needed (only on activation)
        if (get_option('job_system_rewrite_rules_flushed') !== '1') {
            flush_rewrite_rules();
            update_option('job_system_rewrite_rules_flushed', '1');
        }
    }
    
    /**
     * Early parse request to intercept job pages before WordPress redirects
     */
    public function early_parse_request($wp) {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // Check for exact /jobs URL
        if (preg_match('#^/jobs/?(\?.*)?$#', $request_uri)) {
            // Set the query var manually
            $wp->query_vars['job_system_page'] = 'departments';
            // Prevent WordPress from thinking this is a 404
            $wp->matched_rule = '^jobs/?$';
            $wp->matched_query = 'job_system_page=departments';
            $wp->request = 'jobs';
            return;
        }

        // Check for exact /jobs-test URL
        if (preg_match('#^/jobs-test/?(\?.*)?$#', $request_uri)) {
            $wp->query_vars['job_system_page'] = 'jobs_test';
            $wp->matched_rule = '^jobs-test/?$';
            $wp->matched_query = 'job_system_page=jobs_test';
            $wp->request = 'jobs-test';
            return;
        }

        // Check for exact /jobs-new URL
        if (preg_match('#^/jobs-new/?(\?.*)?$#', $request_uri)) {
            $wp->query_vars['job_system_page'] = 'jobs_new';
            $wp->matched_rule = '^jobs-new/?$';
            $wp->matched_query = 'job_system_page=jobs_new';
            $wp->request = 'jobs-new';
            return;
        }
        
        // Check for /jobs/department URLs
        if (preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri)) {
            $wp->query_vars['job_system_page'] = 'department_jobs';
            $wp->matched_rule = '^jobs/department/?$';
            $wp->matched_query = 'job_system_page=department_jobs';
            $wp->request = 'jobs/department';
            return;
        }
        
        // Check for /locations URLs
        if (preg_match('#^/locations/?(\?.*)?$#', $request_uri)) {
            $wp->query_vars['job_system_page'] = 'locations';
            $wp->matched_rule = '^locations/?$';
            $wp->matched_query = 'job_system_page=locations';
            $wp->request = 'locations';
            return;
        }
        
        // Check for /locations/location URLs
        if (preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)) {
            $wp->query_vars['job_system_page'] = 'location_jobs';
            $wp->matched_rule = '^locations/location/?$';
            $wp->matched_query = 'job_system_page=location_jobs';
            $wp->request = 'locations/location';
            return;
        }
    }
    
    /**
     * Prevent canonical redirects for our custom pages
     */
    public function prevent_canonical_redirect($redirect_url, $requested_url) {
        // Check if this is one of our custom job system pages
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (preg_match('#^/jobs/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs-test/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs-new/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)) {
            // Don't redirect our custom pages
            return false;
        }
        
        return $redirect_url;
    }
    
    /**
     * Super early check for job pages to prevent redirects
     */
    public function check_job_pages_early() {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // If this is /jobs, immediately set up for handling
        if (preg_match('#^/jobs/?(\?.*)?$#', $request_uri)) {
            // Force WordPress to recognize this as a valid page
            global $wp_rewrite;
            if (!$wp_rewrite->using_permalinks()) {
                // If not using permalinks, redirect to ?job_system_page=departments
                if (!isset($_GET['job_system_page'])) {
                    wp_redirect(home_url('/?job_system_page=departments'));
                    exit;
                }
            }
        }
    }
    
    /**
     * Add Elementor config to prevent JavaScript errors
     */
    public function add_elementor_config() {
        // Only add on our custom job pages
        $job_system_page = get_query_var('job_system_page');
        $request_uri = $_SERVER['REQUEST_URI'];
        
        $is_job_page = (
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            preg_match('#^/jobs/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)
        );
        
        if ($is_job_page && class_exists('\Elementor\Plugin')) {
            // Let Elementor handle its own configuration naturally
            // We just ensure it's available if needed
            ?>
            <script>
            // Ensure Elementor config exists for our custom pages
            (function() {
                // Wait for Elementor to initialize its config
                function ensureElementorConfig() {
                    if (typeof elementorFrontendConfig === 'undefined') {
                        // Create a minimal config if Elementor hasn't loaded yet
                        window.elementorFrontendConfig = {
                            environmentMode: {
                                edit: false,
                                wpPreview: false,
                                isScriptDebug: false
                            },
                            i18n: {
                                shareOnFacebook: "Share on Facebook",
                                shareOnTwitter: "Share on Twitter",
                                pinIt: "Pin it",
                                download: "Download",
                                downloadImage: "Download image",
                                fullscreen: "Fullscreen",
                                zoom: "Zoom",
                                share: "Share",
                                playVideo: "Play Video",
                                previous: "Previous",
                                next: "Next",
                                close: "Close"
                            },
                            is_rtl: <?php echo is_rtl() ? 'true' : 'false'; ?>,
                            breakpoints: { xs: 0, sm: 480, md: 768, lg: 1025, xl: 1440, xxl: 1600 },
                            responsive: {
                                breakpoints: {
                                    mobile: { label: "Mobile", value: 767, default_value: 767, direction: "max", is_enabled: true },
                                    mobile_extra: { label: "Mobile Extra", value: 880, default_value: 880, direction: "max", is_enabled: false },
                                    tablet: { label: "Tablet", value: 1024, default_value: 1024, direction: "max", is_enabled: true },
                                    tablet_extra: { label: "Tablet Extra", value: 1200, default_value: 1200, direction: "max", is_enabled: false },
                                    laptop: { label: "Laptop", value: 1366, default_value: 1366, direction: "max", is_enabled: false },
                                    widescreen: { label: "Widescreen", value: 2400, default_value: 2400, direction: "min", is_enabled: false }
                                }
                            },
                            version: "<?php echo defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.31.1'; ?>",
                            is_static: false,
                            experimentalFeatures: {
                                e_dom_optimization: true,
                                e_optimized_assets_loading: true,
                                e_optimized_css_loading: true,
                                additional_custom_breakpoints: true,
                                e_swiper_latest: true,
                                container: true,
                                theme_builder_v2: true,
                                hello_theme_header_footer: true,
                                landing_pages: true,
                                page_transitions: true,
                                notes: true,
                                loop: true,
                                form_submissions: true,
                                e_scroll_snap: true
                            },
                            urls: { 
                                assets: "<?php echo defined('ELEMENTOR_ASSETS_URL') ? ELEMENTOR_ASSETS_URL : ''; ?>",
                                ajax: "<?php echo admin_url('admin-ajax.php'); ?>"
                            },
                            nonces: {
                                floatingButtonsClickTracking: "<?php echo wp_create_nonce('floating_buttons_click_tracking'); ?>"
                            },
                            swiperClass: "swiper",
                            settings: { 
                                page: [], 
                                editorPreferences: [] 
                            },
                            kit: {
                                active_breakpoints: ["viewport_mobile", "viewport_tablet"],
                                global_image_lightbox: "yes",
                                lightbox_enable_counter: "yes",
                                lightbox_enable_fullscreen: "yes",
                                lightbox_enable_zoom: "yes",
                                lightbox_enable_share: "yes",
                                lightbox_title_src: "title",
                                lightbox_description_src: "description",
                                hello_header_logo_type: "logo",
                                hello_header_menu_layout: "horizontal",
                                hello_footer_logo_type: "logo"
                            },
                            post: { 
                                id: <?php echo get_the_ID() ?: 0; ?>, 
                                title: "<?php echo esc_js(get_the_title()); ?>", 
                                excerpt: "<?php echo esc_js(get_the_excerpt()); ?>", 
                                featuredImage: false 
                            }
                        };
                    }
                }
                
                // Try to ensure config is available immediately
                ensureElementorConfig();
                
                // Also ensure it's available after DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', ensureElementorConfig);
                } else {
                    ensureElementorConfig();
                }
                
                // Fallback: ensure config is available after a short delay
                setTimeout(ensureElementorConfig, 100);
            })();
            </script>
            <?php
        }
    }
    
    /**
     * Proper Elementor integration - reads actual Elementor variables
     */
    public function integrate_with_elementor() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        // Hook into Elementor's frontend init to get real variables
        add_action('elementor/frontend/init', array($this, 'setup_elementor_integration'));
        
        // Ensure Elementor scripts are loaded on our custom pages
        add_action('wp_enqueue_scripts', array($this, 'ensure_elementor_scripts'), 20);
    }
    
    /**
     * Setup proper Elementor integration
     */
    public function setup_elementor_integration() {
        $job_system_page = get_query_var('job_system_page');
        $request_uri = $_SERVER['REQUEST_URI'];
        
        $is_job_page = (
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            preg_match('#^/jobs/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)
        );
        
        if ($is_job_page) {
            // Get Elementor's actual configuration
            $elementor = \Elementor\Plugin::instance();
            $frontend = $elementor->frontend;
            
            // Ensure Elementor's frontend scripts are loaded
            if (method_exists($frontend, 'enqueue_scripts')) {
                $frontend->enqueue_scripts();
            }
            
            // Add our custom integration script
            add_action('wp_footer', array($this, 'add_elementor_integration_script'), 999);
        }
    }
    
    /**
     * Ensure Elementor scripts are loaded on job pages
     */
    public function ensure_elementor_scripts() {
        $job_system_page = get_query_var('job_system_page');
        $request_uri = $_SERVER['REQUEST_URI'];
        
        $is_job_page = (
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            preg_match('#^/jobs/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)
        );
        
        if ($is_job_page && class_exists('\Elementor\Plugin')) {
            // Force Elementor to load its frontend scripts
            wp_enqueue_script('elementor-frontend');
            wp_enqueue_style('elementor-frontend');
            
            // Also load Elementor's common scripts
            wp_enqueue_script('elementor-common');
            wp_enqueue_style('elementor-common');
        }
    }
    
    /**
     * Add Elementor integration script that reads actual variables
     */
    public function add_elementor_integration_script() {
        $job_system_page = get_query_var('job_system_page');
        $request_uri = $_SERVER['REQUEST_URI'];
        
        $is_job_page = (
            $job_system_page === 'departments' ||
            $job_system_page === 'department_jobs' ||
            $job_system_page === 'locations' ||
            $job_system_page === 'location_jobs' ||
            preg_match('#^/jobs/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/jobs/department/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/?(\?.*)?$#', $request_uri) ||
            preg_match('#^/locations/location/?(\?.*)?$#', $request_uri)
        );
        
        if ($is_job_page && class_exists('\Elementor\Plugin')) {
            // Get actual Elementor configuration from database
            $elementor_config = $this->get_elementor_actual_config();
            
            // Get detected Elementor variables from current page
            $elementor_variables = $this->detect_elementor_variables();
            
            ?>
            <script>
            (function() {
                // Actual Elementor configuration from database
                var actualElementorConfig = <?php echo json_encode($elementor_config); ?>;
                
                // Detected Elementor variables from current page
                var elementorVariables = <?php echo json_encode($elementor_variables); ?>;
                
                // Function to get Elementor's actual configuration
                function getElementorConfig() {
                    // Try to get the actual Elementor config
                    if (typeof elementorFrontendConfig !== 'undefined') {
                        return elementorFrontendConfig;
                    }
                    
                    // If Elementor hasn't loaded yet, try to get it from the page
                    var scripts = document.querySelectorAll('script');
                    for (var i = 0; i < scripts.length; i++) {
                        var script = scripts[i];
                        if (script.textContent && script.textContent.includes('elementorFrontendConfig')) {
                            try {
                                // Extract the config from the script
                                var match = script.textContent.match(/elementorFrontendConfig\s*=\s*({[\s\S]*?});/);
                                if (match) {
                                    return JSON.parse(match[1]);
                                }
                            } catch (e) {
                                console.log('Could not parse Elementor config:', e);
                            }
                        }
                    }
                    
                    return null;
                }
                
                // Function to merge our config with Elementor's actual config
                function mergeElementorConfig() {
                    var elementorConfig = getElementorConfig();
                    
                    if (elementorConfig) {
                        // Elementor config exists, merge with our actual config
                        console.log('Using Elementor\'s actual configuration and merging with database config');
                        return Object.assign({}, actualElementorConfig, elementorConfig);
                    } else {
                        // Use our actual config from database
                        console.log('Using actual Elementor configuration from database');
                        return Object.assign({}, {
                            environmentMode: {
                                edit: false,
                                wpPreview: false,
                                isScriptDebug: false
                            },
                            is_rtl: <?php echo is_rtl() ? 'true' : 'false'; ?>,
                            breakpoints: { xs: 0, sm: 480, md: 768, lg: 1025, xl: 1440, xxl: 1600 },
                            version: "<?php echo defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.31.1'; ?>",
                            is_static: false,
                            urls: { 
                                assets: "<?php echo defined('ELEMENTOR_ASSETS_URL') ? ELEMENTOR_ASSETS_URL : ''; ?>",
                                ajax: "<?php echo admin_url('admin-ajax.php'); ?>"
                            },
                            post: { 
                                id: <?php echo get_the_ID() ?: 0; ?>, 
                                title: "<?php echo esc_js(get_the_title()); ?>", 
                                excerpt: "<?php echo esc_js(get_the_excerpt()); ?>", 
                                featuredImage: false 
                            }
                        }, actualElementorConfig);
                    }
                }
                
                // Initialize Elementor integration
                function initElementorIntegration() {
                    // Wait for Elementor to be available
                    if (typeof elementorFrontend === 'undefined') {
                        // Try again after a short delay
                        setTimeout(initElementorIntegration, 100);
                        return;
                    }
                    
                    // Get the actual Elementor configuration
                    var config = mergeElementorConfig();
                    
                    // Add detected variables to the config
                    config.elementorVariables = elementorVariables;
                    
                    // Ensure the config is available globally
                    if (typeof elementorFrontendConfig === 'undefined') {
                        window.elementorFrontendConfig = config;
                    } else {
                        // Merge with existing config
                        window.elementorFrontendConfig = Object.assign({}, window.elementorFrontendConfig, config);
                    }
                    
                    // Initialize any Elementor-dependent functionality
                    if (typeof elementorFrontend !== 'undefined' && elementorFrontend.init) {
                        try {
                            elementorFrontend.init();
                        } catch (e) {
                            console.log('Elementor frontend init error:', e);
                        }
                    }
                    
                    console.log('Job System: Elementor integration complete with actual variables');
                    console.log('Elementor config:', config);
                    console.log('Elementor variables:', elementorVariables);
                }
                
                // Start the integration process
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initElementorIntegration);
                } else {
                    initElementorIntegration();
                }
                
                // Also try after a longer delay to catch late-loading Elementor
                setTimeout(initElementorIntegration, 500);
                setTimeout(initElementorIntegration, 1000);
            })();
            </script>
            <?php
        }
    }
    
    /**
     * Get actual Elementor configuration from database and theme
     */
    private function get_elementor_actual_config() {
        if (!class_exists('\Elementor\Plugin')) {
            return array();
        }
        
        $config = array();
        
        try {
            $elementor = \Elementor\Plugin::instance();
            
            // Get Elementor's actual breakpoints
            $breakpoints = $elementor->breakpoints->get_breakpoints_config();
            if ($breakpoints) {
                $config['breakpoints'] = $breakpoints;
            }
            
            // Get Elementor's kit settings
            $kit_id = get_option('elementor_active_kit');
            if ($kit_id) {
                $kit = get_post($kit_id);
                if ($kit && $kit->post_type === 'elementor_library') {
                    $kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
                    if ($kit_settings) {
                        $config['kit'] = $kit_settings;
                    }
                }
            }
            
            // Get Elementor's global settings
            $global_settings = get_option('elementor_settings');
            if ($global_settings) {
                $config['settings'] = $global_settings;
            }
            
            // Get Elementor's experimental features
            $experimental_features = get_option('elementor_experiment-*');
            if ($experimental_features) {
                $config['experimentalFeatures'] = $experimental_features;
            }
            
            // Get Elementor's custom CSS
            $custom_css = get_option('elementor_custom_css');
            if ($custom_css) {
                $config['customCSS'] = $custom_css;
            }
            
            // Get Elementor's version
            $config['version'] = defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : '3.31.1';
            
            // Get Elementor's assets URL
            $config['urls'] = array(
                'assets' => defined('ELEMENTOR_ASSETS_URL') ? ELEMENTOR_ASSETS_URL : '',
                'ajax' => admin_url('admin-ajax.php')
            );
            
            // Get current post/page settings
            $post_id = get_the_ID();
            if ($post_id) {
                $elementor_data = get_post_meta($post_id, '_elementor_data', true);
                if ($elementor_data) {
                    $config['post'] = array(
                        'id' => $post_id,
                        'title' => get_the_title($post_id),
                        'excerpt' => get_the_excerpt($post_id),
                        'featuredImage' => has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'full') : false,
                        'elementorData' => $elementor_data
                    );
                }
            }
            
            // Get Elementor's responsive settings
            $responsive_settings = array();
            $breakpoint_devices = array('mobile', 'tablet', 'desktop');
            foreach ($breakpoint_devices as $device) {
                $setting = get_option("elementor_viewport_{$device}");
                if ($setting) {
                    $responsive_settings[$device] = $setting;
                }
            }
            if (!empty($responsive_settings)) {
                $config['responsive'] = $responsive_settings;
            }
            
            // Get Elementor's lightbox settings
            $lightbox_settings = get_option('elementor_lightbox_enable_counter');
            if ($lightbox_settings) {
                $config['lightbox'] = array(
                    'enable_counter' => $lightbox_settings,
                    'enable_fullscreen' => get_option('elementor_lightbox_enable_fullscreen', 'yes'),
                    'enable_zoom' => get_option('elementor_lightbox_enable_zoom', 'yes'),
                    'enable_share' => get_option('elementor_lightbox_enable_share', 'yes')
                );
            }
            
        } catch (Exception $e) {
            error_log('Job System: Error getting Elementor config: ' . $e->getMessage());
        }
        
        return $config;
    }
    
    /**
     * Detect Elementor variables from current page and theme
     */
    private function detect_elementor_variables() {
        $variables = array();
        
        // Check if current page is built with Elementor
        $post_id = get_the_ID();
        if ($post_id) {
            $elementor_data = get_post_meta($post_id, '_elementor_data', true);
            if ($elementor_data) {
                $variables['isElementorPage'] = true;
                $variables['elementorData'] = $elementor_data;
                
                // Parse Elementor data to extract variables
                $parsed_data = json_decode($elementor_data, true);
                if ($parsed_data) {
                    $variables['elementorElements'] = $this->extract_elementor_elements($parsed_data);
                }
            } else {
                $variables['isElementorPage'] = false;
            }
        }
        
        // Check if theme has Elementor support
        $theme_support = get_theme_support('elementor');
        if ($theme_support) {
            $variables['themeElementorSupport'] = $theme_support;
        }
        
        // Check Elementor widgets
        if (class_exists('\Elementor\Plugin')) {
            $elementor = \Elementor\Plugin::instance();
            $widgets_manager = $elementor->widgets_manager;
            if ($widgets_manager) {
                $registered_widgets = $widgets_manager->get_widget_types();
                $variables['registeredWidgets'] = array_keys($registered_widgets);
            }
        }
        
        // Check Elementor templates
        $templates = get_posts(array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        if ($templates) {
            $variables['elementorTemplates'] = array();
            foreach ($templates as $template) {
                $variables['elementorTemplates'][] = array(
                    'id' => $template->ID,
                    'title' => $template->post_title,
                    'type' => get_post_meta($template->ID, '_elementor_template_type', true)
                );
            }
        }
        
        return $variables;
    }
    
    /**
     * Extract Elementor elements and their settings
     */
    private function extract_elementor_elements($data) {
        $elements = array();
        
        if (is_array($data)) {
            foreach ($data as $element) {
                if (isset($element['widgetType'])) {
                    $elements[] = array(
                        'type' => $element['widgetType'],
                        'id' => isset($element['id']) ? $element['id'] : '',
                        'settings' => isset($element['settings']) ? $element['settings'] : array()
                    );
                }
                
                // Recursively check for nested elements
                if (isset($element['elements']) && is_array($element['elements'])) {
                    $elements = array_merge($elements, $this->extract_elementor_elements($element['elements']));
                }
            }
        }
        
        return $elements;
    }
    
    /**
     * Debug Elementor integration (for testing)
     */
    public function debug_elementor_integration() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['debug_elementor']) && $_GET['debug_elementor'] === '1') {
            echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
            echo '<h3>Elementor Integration Debug</h3>';
            
            // Check if Elementor is active
            echo '<p><strong>Elementor Active:</strong> ' . (class_exists('\Elementor\Plugin') ? 'Yes' : 'No') . '</p>';
            
            if (class_exists('\Elementor\Plugin')) {
                $elementor = \Elementor\Plugin::instance();
                echo '<p><strong>Elementor Version:</strong> ' . (defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'Unknown') . '</p>';
                
                // Get actual config
                $config = $this->get_elementor_actual_config();
                echo '<p><strong>Elementor Config:</strong></p>';
                echo '<pre>' . print_r($config, true) . '</pre>';
                
                // Get detected variables
                $variables = $this->detect_elementor_variables();
                echo '<p><strong>Elementor Variables:</strong></p>';
                echo '<pre>' . print_r($variables, true) . '</pre>';
                
                // Check current page
                $post_id = get_the_ID();
                if ($post_id) {
                    $elementor_data = get_post_meta($post_id, '_elementor_data', true);
                    echo '<p><strong>Current Page Elementor Data:</strong> ' . ($elementor_data ? 'Yes' : 'No') . '</p>';
                }
            }
            
            echo '</div>';
        }
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'job_system_page';
        $vars[] = 'department_id';
        $vars[] = 'location_id';
        return $vars;
    }
    
    /**
     * Handle parse request - intercept early
     */
    public function handle_parse_request($wp) {
        // Check if this is our custom route
        if (isset($wp->query_vars['job_system_page'])) {
            $job_page = $wp->query_vars['job_system_page'];
            
            if ($job_page === 'departments') {
                $wp->query_vars['pagename'] = 'jobs';
            } elseif ($job_page === 'department_jobs') {
                $wp->query_vars['pagename'] = 'jobs-department';
            }
        }
    }

    /**
     * Handle custom pages routing
     */
    public function handle_custom_pages() {
        global $wp_query;
        
        $job_page = get_query_var('job_system_page');
        
        // Debug mode (commented out)
        /*
        if (isset($_GET['debug_jobs'])) {
            echo "<h3>Job System Debug:</h3>";
            echo "Job System Page: " . $job_page . "<br>";
            echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
            echo "Query vars: ";
            print_r($wp_query->query_vars);
            echo "<br>";
        }
        */
        
        if ($job_page === 'departments') {
            // Prevent WordPress from treating this as 404
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_jobs_page();
            exit;
        } elseif ($job_page === 'jobs_test') {
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_jobs_test_page();
            exit;
        } elseif ($job_page === 'jobs_new') {
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_jobs_new_page();
            exit;
        } elseif ($job_page === 'department_jobs') {
            // Prevent WordPress from treating this as 404
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_department_jobs_page();
            exit;
        } elseif ($job_page === 'locations') {
            // Prevent WordPress from treating this as 404
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_locations_page();
            exit;
        } elseif ($job_page === 'location_jobs') {
            // Prevent WordPress from treating this as 404
            status_header(200);
            $wp_query->is_404 = false;
            $wp_query->is_home = false;
            $wp_query->is_page = true;
            
            $this->display_location_jobs_page();
            exit;
        }
    }
    
    /**
     * Display jobs main page
     */
    private function display_jobs_page() {
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object to make WordPress think this is a regular page
        $post = new WP_Post((object) array(
            'ID' => 999999,
            'post_title' => 'Careers',
            'post_name' => 'jobs',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/jobs/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
        $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $this->get_fallback_page_id();
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_attachment = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        $wp_query->is_tag = false;
        $wp_query->is_tax = false;
        $wp_query->is_author = false;
        $wp_query->is_date = false;
        $wp_query->is_year = false;
        $wp_query->is_month = false;
        $wp_query->is_day = false;
        $wp_query->is_time = false;
        $wp_query->is_search = false;
        $wp_query->is_feed = false;
        $wp_query->is_comment_feed = false;
        $wp_query->is_trackback = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        $wp_query->is_comments_popup = false;
        $wp_query->is_paged = false;
        $wp_query->is_admin = false;
        $wp_query->is_preview = false;
        $wp_query->is_robots = false;
        $wp_query->is_posts_page = false;
        $wp_query->is_post_type_archive = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Get header
        get_header();
        // Fallback: if theme header.php didn't call wp_head(), fire it now
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include our template
        include plugin_dir_path(__FILE__) . '../templates/departments-page.php';
        
        // Get footer
        get_footer();
        // Fallback: if theme footer.php didn't call wp_footer(), fire it now (last resort)
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data to avoid conflicts
        wp_reset_postdata();
    }

    /**
     * Display jobs test page (same as departments at /jobs-test)
     */
    private function display_jobs_test_page() {
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object to make WordPress think this is a regular page
        $post = new WP_Post((object) array(
            'ID' => 999995,
            'post_title' => 'Careers Test',
            'post_name' => 'jobs-test',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/jobs-test/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
        $wp_query->queried_object = $post;
        // Avoid core lookups on non-existent DB post IDs
        $wp_query->queried_object_id = 0;
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_attachment = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        $wp_query->is_tag = false;
        $wp_query->is_tax = false;
        $wp_query->is_author = false;
        $wp_query->is_date = false;
        $wp_query->is_year = false;
        $wp_query->is_month = false;
        $wp_query->is_day = false;
        $wp_query->is_time = false;
        $wp_query->is_search = false;
        $wp_query->is_feed = false;
        $wp_query->is_comment_feed = false;
        $wp_query->is_trackback = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        $wp_query->is_comments_popup = false;
        $wp_query->is_paged = false;
        $wp_query->is_admin = false;
        $wp_query->is_preview = false;
        $wp_query->is_robots = false;
        $wp_query->is_posts_page = false;
        $wp_query->is_post_type_archive = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Get header
        get_header();
        // Fallback: if theme header.php didn't call wp_head(), fire it now
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include our template (reuse departments)
        include plugin_dir_path(__FILE__) . '../templates/departments-page.php';
        
        // Get footer
        get_footer();
        // Fallback: if theme footer.php didn't call wp_footer(), fire it now (last resort)
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data to avoid conflicts
        wp_reset_postdata();
    }

    /**
     * Display jobs new page at /jobs-new (brand-new content)
     */
    private function display_jobs_new_page() {
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object for the new page
        $post = new WP_Post((object) array(
            'ID' => 999994,
            'post_title' => 'Careers New',
            'post_name' => 'jobs-new',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/jobs-new/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
        $wp_query->queried_object = $post;
        $wp_query->queried_object_id = 0; // avoid core lookups
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Get header and ensure wp_head fired
        get_header();
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include the new template
        include plugin_dir_path(__FILE__) . '../templates/jobs-new.php';
        
        // Get footer and ensure wp_footer fired
        get_footer();
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data
        wp_reset_postdata();
    }
    
    /**
     * Display department jobs page
     */
    private function display_department_jobs_page() {
        // Get department ID from URL parameter
        $department_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$department_id) {
            wp_redirect(home_url('/jobs'));
            exit;
        }
        
        // Verify department exists
        $department = get_term($department_id, 'job_department');
        if (!$department || is_wp_error($department)) {
            wp_redirect(home_url('/jobs'));
            exit;
        }
        
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object
        $post = new WP_Post((object) array(
            'ID' => 999998,
            'post_title' => $department->name . ' Jobs',
            'post_name' => 'department-jobs',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/jobs/department/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $this->get_fallback_page_id();
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Set department data for template
        set_query_var('current_department', $department);
        
        // Get header
        get_header();
        // Fallback: if theme header.php didn't call wp_head(), fire it now
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include our template
        include plugin_dir_path(__FILE__) . '../templates/department-jobs.php';
        
        // Get footer
        get_footer();
        // Fallback: if theme footer.php didn't call wp_footer(), fire it now (last resort)
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data to avoid conflicts
        wp_reset_postdata();
    }
    
    /**
     * Display locations main page
     */
    private function display_locations_page() {
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object
        $post = new WP_Post((object) array(
            'ID' => 999997,
            'post_title' => 'Locations',
            'post_name' => 'locations',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/locations/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $this->get_fallback_page_id();
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Get header
        get_header();
        // Fallback: if theme header.php didn't call wp_head(), fire it now
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include our template
        include plugin_dir_path(__FILE__) . '../templates/locations-page.php';
        
        // Get footer
        get_footer();
        // Fallback: if theme footer.php didn't call wp_footer(), fire it now (last resort)
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data to avoid conflicts
        wp_reset_postdata();
    }
    
    /**
     * Display location jobs page
     */
    private function display_location_jobs_page() {
        // Get location ID from URL parameter
        $location_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$location_id) {
            wp_redirect(home_url('/locations'));
            exit;
        }
        
        // Verify location exists
        $location = get_term($location_id, 'job_location');
        if (!$location || is_wp_error($location)) {
            wp_redirect(home_url('/locations'));
            exit;
        }
        
        // Set up page data for WordPress
        global $wp_query, $post;
        
        // Create a fake post object
        $post = new WP_Post((object) array(
            'ID' => 999996,
            'post_title' => $location->name . ' Jobs',
            'post_name' => 'location-jobs',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_content' => '',
            'post_excerpt' => '',
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', 1),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1),
            'post_parent' => 0,
            'post_password' => '',
            'post_mime_type' => '',
            'guid' => home_url('/locations/location/'),
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'comment_count' => 0,
            'menu_order' => 0,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'filter' => 'raw'
        ));
        
        // Set up query vars
        $wp_query->post = $post;
        $wp_query->posts = array($post);
    $wp_query->queried_object = $post;
    $wp_query->queried_object_id = $this->get_fallback_page_id();
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_home = false;
        $wp_query->is_404 = false;
        
        // Set global $post variable
        $GLOBALS['post'] = $post;
        setup_postdata($post);
        
        // Set location data for template
        set_query_var('current_location', $location);
        
        // Get header
        get_header();
        // Fallback: if theme header.php didn't call wp_head(), fire it now
        if (!did_action('wp_head')) {
            do_action('wp_head');
        }
        
        // Include our template
        include plugin_dir_path(__FILE__) . '../templates/location-jobs.php';
        
        // Get footer
        get_footer();
        // Fallback: if theme footer.php didn't call wp_footer(), fire it now (last resort)
        if (!did_action('wp_footer')) {
            do_action('wp_footer');
        }
        
        // Reset post data to avoid conflicts
        wp_reset_postdata();
    }

    /**
     * Custom template include for backup routing
     */
    public function custom_template_include($template) {
    // Check if we're on a jobs-related page
        
        if (preg_match('/\/jobs\/department/', $_SERVER['REQUEST_URI']) && !is_admin()) {
            ob_start();
            $this->display_department_jobs_page();
            $content = ob_get_clean();
            echo $content;
            exit;
        }
        
        return $template;
    }
    
    /**
     * Safety filter to prevent null post access in permalink functions
     */
    public function safe_post_link($link, $post) {
        // If post is null or doesn't have required properties, return a fallback
        if (!$post || !isset($post->post_type) || is_null($post->post_type)) {
            // For custom job system pages, return appropriate URLs
            if (strpos($_SERVER['REQUEST_URI'], '/jobs') !== false) {
                return home_url('/jobs/');
            }
            return home_url('/');
        }
        return $link;
    }
    
    /**
     * Safe post metadata to prevent warnings
     */
    public function safe_post_metadata($metadata, $object_id, $meta_key, $single) {
        // If trying to access metadata for non-existent post, return empty
        if (!$object_id || !get_post($object_id)) {
            return $single ? '' : array();
        }
        return $metadata;
    }
    
    /**
     * Ensure default locations exist
     */
    public function ensure_default_locations() {
        // Check if we already have locations
        $existing_locations = get_terms(array(
            'taxonomy' => 'job_location',
            'hide_empty' => false,
            'number' => 1
        ));
        
        // If no locations exist, create default ones
        if (empty($existing_locations) || is_wp_error($existing_locations)) {
            $this->create_default_locations();
        }
    }
    
    /**
     * Create default locations
     */
    private function create_default_locations() {
        $default_locations = array(
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
                'name' => 'Remote - Global',
                'slug' => 'remote-global',
                'color' => '#ef4444'
            )
        );
        
        foreach ($default_locations as $location) {
            $term = wp_insert_term($location['name'], 'job_location', array(
                'slug' => $location['slug']
            ));
            
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'location_color', $location['color']);
            }
        }
    }
    
    /**
     * Ensure default departments exist
     */
    public function ensure_default_departments() {
        // Check if we already have departments
        $existing_departments = get_terms(array(
            'taxonomy' => 'job_department',
            'hide_empty' => false,
            'number' => 1
        ));
        
        // If no departments exist, create default ones
        if (empty($existing_departments) || is_wp_error($existing_departments)) {
            $this->create_default_departments();
        }
    }
    
    /**
     * Create default departments
     */
    private function create_default_departments() {
        $default_departments = array(
            array(
                'name' => 'Product',
                'slug' => 'product',
                'color' => '#4CAF50'
            ),
            array(
                'name' => 'Engineering',
                'slug' => 'engineering', 
                'color' => '#2196F3'
            ),
            array(
                'name' => 'Marketing',
                'slug' => 'marketing',
                'color' => '#FF5722'
            ),
            array(
                'name' => 'Sales',
                'slug' => 'sales',
                'color' => '#FFC107'
            ),
            array(
                'name' => 'HR & Operations',
                'slug' => 'hr-operations',
                'color' => '#9C27B0'
            ),
            array(
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'color' => '#00BCD4'
            )
        );
        
        foreach ($default_departments as $department) {
            $term = wp_insert_term($department['name'], 'job_department', array(
                'slug' => $department['slug']
            ));
            
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'department_color', $department['color']);
            }
        }
    }
}
