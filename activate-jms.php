<?php
/**
 * Manual plugin activation and table creation script
 * Use this if the automatic activation doesn't work properly
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    die('You do not have permission to run this script');
}

echo '<h2>Job Management System - Manual Activation</h2>';

// Include necessary files
require_once('includes/database.php');

if (isset($_POST['activate_plugin'])) {
    echo '<h3>Activating Plugin...</h3>';
    
    try {
        // Step 1: Activate the plugin
        if (!is_plugin_active('job-system/job-system.php')) {
            activate_plugin('job-system/job-system.php');
            echo '<p>✅ Plugin activated</p>';
        } else {
            echo '<p>✅ Plugin already active</p>';
        }
        
        // Step 2: Create database tables
        echo '<p>Creating database tables...</p>';
        JMS_Database::create_tables();
        echo '<p>✅ Database tables created</p>';
        
        // Step 3: Set options
        update_option('jms_email_notifications', 1);
        update_option('jms_max_file_size', 5);
        update_option('jms_allowed_file_types', array('pdf', 'doc', 'docx'));
        echo '<p>✅ Default options set</p>';
        
        // Step 4: Create upload directory
        $upload_dir = wp_upload_dir()['basedir'] . '/job-system/';
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
            file_put_contents($upload_dir . 'index.php', '<?php // Silence is golden');
            echo '<p>✅ Upload directory created</p>';
        } else {
            echo '<p>✅ Upload directory already exists</p>';
        }
        
        // Step 5: Flush rewrite rules
        flush_rewrite_rules();
        echo '<p>✅ Rewrite rules flushed</p>';
        
        echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>✅ Activation Complete!</h3>';
        echo '<p>The Job Management System has been successfully activated.</p>';
        echo '<p><a href="/wp-admin/admin.php?page=jms_dashboard" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Job Management Dashboard</a></p>';
        echo '</div>';
        
        // Verify everything is working
        echo '<h3>System Verification:</h3>';
        
        // Check plugin status
        if (is_plugin_active('job-system/job-system.php')) {
            echo '<p style="color: green;">✅ Plugin is active</p>';
        } else {
            echo '<p style="color: red;">❌ Plugin activation failed</p>';
        }
        
        // Check tables
        global $wpdb;
        $tables = array(
            'jms_departments' => 'Departments',
            'jms_locations' => 'Locations', 
            'jms_jobs' => 'Jobs',
            'jms_applications' => 'Applications'
        );
        
        foreach ($tables as $table => $name) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
                echo "<p style='color: green;'>✅ $name table created ($count sample records)</p>";
            } else {
                echo "<p style='color: red;'>❌ $name table creation failed</p>";
            }
        }
        
        // Check upload directory
        if (is_dir($upload_dir) && is_writable($upload_dir)) {
            echo '<p style="color: green;">✅ Upload directory is ready</p>';
        } else {
            echo '<p style="color: red;">❌ Upload directory issue</p>';
        }
        
    } catch (Exception $e) {
        echo '<div style="background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>❌ Activation Error!</h3>';
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        echo '</div>';
    }
} else {
    // Show current status
    echo '<h3>Current Status:</h3>';
    
    // Check plugin status
    if (is_plugin_active('job-system/job-system.php')) {
        echo '<p style="color: green;">✅ Plugin is currently active</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Plugin is not active</p>';
    }
    
    // Check database tables
    global $wpdb;
    $tables = array(
        'jms_departments' => 'Departments',
        'jms_locations' => 'Locations', 
        'jms_jobs' => 'Jobs',
        'jms_applications' => 'Applications'
    );
    
    $missing_tables = 0;
    foreach ($tables as $table => $name) {
        $full_table_name = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
        
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
            echo "<p style='color: green;'>✅ $name table exists ($count records)</p>";
        } else {
            echo "<p style='color: red;'>❌ $name table missing</p>";
            $missing_tables++;
        }
    }
    
    // Check upload directory
    $upload_dir = wp_upload_dir()['basedir'] . '/job-system/';
    if (is_dir($upload_dir)) {
        echo '<p style="color: green;">✅ Upload directory exists</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Upload directory missing</p>';
    }
    
    // Show activation button
    echo '<div style="background: #e7f3ff; padding: 20px; margin: 20px 0; border-radius: 5px;">';
    echo '<h3>Manual Activation</h3>';
    echo '<p>Click the button below to manually activate the plugin and create all necessary database tables.</p>';
    echo '<form method="post">';
    echo '<input type="hidden" name="activate_plugin" value="1">';
    echo '<button type="submit" style="background: #007cba; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;">Activate Job Management System</button>';
    echo '</form>';
    echo '</div>';
    
    // Show links
    if (is_plugin_active('job-system/job-system.php') && $missing_tables == 0) {
        echo '<div style="background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>✅ System Ready</h3>';
        echo '<p>Everything looks good! You can now use the Job Management System.</p>';
        echo '<p>';
        echo '<a href="/wp-admin/admin.php?page=jms_dashboard" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Dashboard</a>';
        echo '<a href="/jobs/" target="_blank" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Jobs Page</a>';
        echo '</p>';
        echo '</div>';
    }
}
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin: 40px;
    background: #f1f1f1;
    line-height: 1.6;
}

h2, h3 {
    color: #333;
}

p {
    margin-bottom: 10px;
}

button {
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

code {
    background: #f1f1f1;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>

