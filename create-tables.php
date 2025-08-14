<?php
/**
 * Manual database table creation script
 * Run this if tables weren't created during plugin activation
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    die('You do not have permission to run this script');
}

// Include our database class
require_once('includes/database.php');

echo '<h2>Job Management System - Database Table Creation</h2>';

if (isset($_POST['create_tables'])) {
    echo '<h3>Creating Database Tables...</h3>';
    
    try {
        // Create tables
        JMS_Database::create_tables();
        
        echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>✅ Success!</h3>';
        echo '<p>All database tables have been created successfully!</p>';
        echo '</div>';
        
        // Verify tables exist
        global $wpdb;
        $tables = array(
            'jms_departments',
            'jms_locations', 
            'jms_jobs',
            'jms_applications'
        );
        
        echo '<h3>Table Verification:</h3>';
        foreach ($tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") == $full_table_name;
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
                echo "<p style='color: green;'>✅ $full_table_name - Created ($count records)</p>";
            } else {
                echo "<p style='color: red;'>❌ $full_table_name - Failed to create</p>";
            }
        }
        
        echo '<p><a href="/wp-admin/admin.php?page=jms_dashboard" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Job Management Dashboard</a></p>';
        
    } catch (Exception $e) {
        echo '<div style="background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>❌ Error!</h3>';
        echo '<p>Failed to create tables: ' . $e->getMessage() . '</p>';
        echo '</div>';
    }
} else {
    // Show current status
    global $wpdb;
    
    $tables = array(
        'jms_departments' => 'Departments',
        'jms_locations' => 'Locations', 
        'jms_jobs' => 'Jobs',
        'jms_applications' => 'Applications'
    );
    
    echo '<h3>Current Database Status:</h3>';
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
    
    if ($missing_tables > 0) {
        echo '<div style="background: #fff3cd; padding: 20px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>⚠️ Missing Database Tables</h3>';
        echo '<p>Some database tables are missing. Click the button below to create them.</p>';
        echo '<form method="post">';
        echo '<input type="hidden" name="create_tables" value="1">';
        echo '<button type="submit" style="background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px;">Create Database Tables</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<div style="background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 5px;">';
        echo '<h3>✅ All Tables Exist</h3>';
        echo '<p>All required database tables are present.</p>';
        echo '<a href="/wp-admin/admin.php?page=jms_dashboard" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Job Management Dashboard</a>';
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
</style>

