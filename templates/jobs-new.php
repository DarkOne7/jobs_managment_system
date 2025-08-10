<?php
/**
 * Template: Jobs New (brand-new page)
 * Renders a simple hero + list reused from departments for demo
 */

if (!defined('ABSPATH')) {
    exit;
}
wp_head();
// Reuse the departments data for a quick demo
$departments = get_terms(array(
    'taxonomy' => 'job_department',
    'hide_empty' => true,
));

$total_jobs = wp_count_posts('job');
$total_published_jobs = isset($total_jobs->publish) ? (int)$total_jobs->publish : 0;
?>

<div class="job-new-hero" style="background: linear-gradient(90deg,#E0F7FA 0%, #E8F5E9 100%); border-bottom: 3px solid #14A26A; padding: 40px 0;">
  <div class="container" style="max-width:1140px;margin:0 auto;padding:0 20px;">
    <h1 style="font-family:Poppins, sans-serif; margin:0; font-weight:700;">Explore Our Open Roles</h1>
    <p style="margin:10px 0 0 0; color:#333;">We have <strong><?php echo esc_html($total_published_jobs); ?></strong> open positions</p>
  </div>
</div>

<div class="job-new-content" style="max-width:1140px;margin:30px auto;padding:0 20px;font-family:Poppins, sans-serif;">
  <?php if (!empty($departments) && !is_wp_error($departments)): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;">
      <?php foreach ($departments as $department): ?>
        <?php
          $color = get_term_meta($department->term_id, 'department_color', true) ?: '#10b981';
          $job_count = (int)$department->count;
          $department_link = home_url('/jobs/department?id=' . $department->term_id);
        ?>
        <a href="<?php echo esc_url($department_link); ?>" style="text-decoration:none;color:inherit;">
          <div style="border:1px solid #e5e7eb;border-radius:12px;padding:18px;display:flex;flex-direction:column;gap:10px;background:#fff;">
            <div style="width:5px;height:40px;background:<?php echo esc_attr($color); ?>;border-radius:3px;"></div>
            <strong style="font-size:18px;"><?php echo esc_html($department->name); ?></strong>
            <span style="color:#555; font-size:14px;">
              <?php printf(_n('%d Opportunity', '%d Opportunities', $job_count, 'job-system'), $job_count); ?>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p><?php esc_html_e('No departments found.', 'job-system'); ?></p>
  <?php endif; ?>
</div>
