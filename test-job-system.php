<?php
/**
 * ملف اختبار Job System Plugin
 * ارفعه لمجلد الموقع الرئيسي لاختبار الـ plugin
 */

// التأكد من المسار
if (!file_exists('wp-config.php')) {
    die('يرجى رفع هذا الملف في نفس مجلد wp-config.php');
}

// تحميل WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// معالجة الطلبات
if (isset($_POST['flush_rules'])) {
    flush_rewrite_rules();
    echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
    echo '<strong>تم إعادة تحديث Rewrite Rules!</strong><br>';
    echo '</div>';
}

if (isset($_POST['force_refresh'])) {
    // إعادة تفعيل الـ plugin
    if (is_plugin_active('job-system/job-system.php')) {
        deactivate_plugins('job-system/job-system.php');
        activate_plugin('job-system/job-system.php');
        echo '<div style="background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;">';
        echo '<strong>تم إعادة تفعيل Plugin!</strong><br>';
        echo '</div>';
    }
}

echo '<h2>اختبار Job System Plugin</h2>';

// التحقق من وجود الـ plugin
$plugin_file = 'wp-content/plugins/job-system/job-system.php';
if (file_exists($plugin_file)) {
    echo '<p style="color: green;">✅ ملف الـ plugin موجود</p>';
} else {
    echo '<p style="color: red;">❌ ملف الـ plugin غير موجود</p>';
    exit;
}

// التحقق من تفعيل الـ plugin
if (is_plugin_active('job-system/job-system.php')) {
    echo '<p style="color: green;">✅ الـ plugin مفعّل</p>';
} else {
    echo '<p style="color: orange;">⚠️ الـ plugin غير مفعّل</p>';
}

// التحقق من Post Types
$post_types = get_post_types();
if (isset($post_types['job']) && isset($post_types['job_department'])) {
    echo '<p style="color: green;">✅ Post Types مسجلة بنجاح</p>';
} else {
    echo '<p style="color: red;">❌ Post Types غير مسجلة</p>';
}

// التحقق من الـ templates
$templates = array(
    'departments-archive.php',
    'department-jobs.php', 
    'single-job.php'
);

echo '<h3>ملفات Templates:</h3>';
foreach ($templates as $template) {
    $template_path = 'wp-content/plugins/job-system/templates/' . $template;
    if (file_exists($template_path)) {
        echo '<p style="color: green;">✅ ' . $template . ' موجود</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $template . ' غير موجود</p>';
    }
}

// اختبار الـ rewrite rules
echo '<h3>اختبار الروابط:</h3>';
echo '<ul>';
echo '<li><a href="/jobs/" target="_blank">صفحة الأقسام (/jobs/)</a></li>';
echo '<li><a href="/jobs/department?id=1" target="_blank">وظائف قسم (إذا كان موجود)</a></li>';
echo '</ul>';

// اختبار rewrite rules
$rules = get_option('rewrite_rules');
$job_rules_found = false;
if (is_array($rules)) {
    foreach ($rules as $pattern => $rule) {
        if (strpos($pattern, 'jobs') !== false || strpos($rule, 'job_system_page') !== false) {
            $job_rules_found = true;
            echo '<p style="color: green;">✅ Rewrite rule found: ' . $pattern . ' => ' . $rule . '</p>';
        }
    }
}

if (!$job_rules_found) {
    echo '<p style="color: red;">❌ لم يتم العثور على rewrite rules للوظائف</p>';
    echo '<form method="post">';
    echo '<input type="hidden" name="flush_rules" value="1">';
    echo '<button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px;">إعادة تحديث Rewrite Rules</button>';
    echo '</form>';
}

// إحصائيات
$departments = get_posts(array('post_type' => 'job_department', 'numberposts' => -1));
$jobs = get_posts(array('post_type' => 'job', 'numberposts' => -1));

echo '<h3>الإحصائيات:</h3>';
echo '<p>عدد الأقسام: <strong>' . count($departments) . '</strong></p>';
echo '<p>عدد الوظائف: <strong>' . count($jobs) . '</strong></p>';

// أزرار التحكم
echo '<h3>أدوات الإصلاح:</h3>';
echo '<div style="display: flex; gap: 10px; margin-bottom: 20px;">';
echo '<form method="post" style="display: inline;">';
echo '<input type="hidden" name="force_refresh" value="1">';
echo '<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">إعادة تفعيل Plugin</button>';
echo '</form>';

echo '<form method="post" style="display: inline;">';
echo '<input type="hidden" name="flush_rules" value="1">';
echo '<button type="submit" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">إعادة تحديث Rules</button>';
echo '</form>';
echo '</div>';

// زر حذف الملف
echo '<h3>تنظيف:</h3>';
echo '<form method="post">';
echo '<input type="hidden" name="delete_test_file" value="1">';
echo '<button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">حذف ملف الاختبار</button>';
echo '</form>';

if (isset($_POST['delete_test_file'])) {
    unlink(__FILE__);
    echo '<p style="color: green;">تم حذف ملف الاختبار!</p>';
    echo '<script>setTimeout(function(){ window.location.href = "/wp-admin/"; }, 2000);</script>';
}
?>
