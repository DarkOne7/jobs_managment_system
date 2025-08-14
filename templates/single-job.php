<?php
/**
 * Template for displaying a single job with application form
 * URL: /jobs/{job_slug}
 */

global $jms_data;
$job = $jms_data['job'] ?? null;
$page_title = $jms_data['page_title'] ?? 'Job Details';

if (!$job) {
    wp_redirect(home_url('/jobs/'));
    exit;
}

$department_color = JMS_Templates::get_department_color($job->department_color ?? '');
$department_icon = JMS_Templates::get_department_icon($job->department_icon ?? '');
$is_deadline_passed = !empty($job->application_deadline) && strtotime($job->application_deadline) < time();

get_header(); ?>

<style>
/* Single Job Page - header like department-jobs with Apply Now modal */
body { font-family: 'Poppins', sans-serif; }
.job-system-container { padding: 0; margin: 0; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.jms-header { background: #000; color: #fff; padding: 60px 0; border-bottom: 3px solid #14A26A; }
.job-header-bar { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.job-header-left { display: flex; flex-direction: column; gap: 8px; }
.page-title { color: #fff; font-size: 3rem; margin: 0; font-weight: 700; }
.job-header-meta { display: flex; align-items: center; gap: 18px; color: #cfcfcf; font-weight: 500; }
.job-header-meta .meta { display: inline-flex; align-items: center; gap: 8px; font-weight: 400; }
.job-header-meta i { color: #cfcfcf; }
.apply-now-btn-header { background: #14A26A; color: #fff; border: 2px solid #14A26A; padding: 10px 18px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all .2s ease; }
.apply-now-btn-header:hover { background: transparent; color: #14A26A; border-color: #14A26A; }
.apply-now-btn-header:disabled { opacity: .5; cursor: not-allowed; }

.apply-now-btn-inline { background: #14A26A; color: #fff; border: 2px solid #14A26A; padding: 10px 75px 10px 75px; border-radius: 12px; font-weight: 500; cursor: pointer; transition: all .2s ease; }
.apply-now-btn-inline:hover { background: transparent; color: #14A26A; border-color: #14A26A; }
.apply-now-btn-inline:disabled { opacity: .5; cursor: not-allowed; }
.apply-now-btn-inline:focus { background-color: #14A26A; }
.apply-now-btn:disabled { opacity: .5; cursor: not-allowed; }

.job-content { padding: 60px 0; }
.job-details-grid { display: grid; grid-template-columns: 1fr; gap: 40px; }
.content-section { padding: 30px; border-bottom: 1px solid #eee; }
.content-section:last-child { border-bottom: none; }
.section-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; }
.job-description { color: #666; line-height: 1.7; font-size: 1.1rem; }

/* Modal */
.apply-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 9999; opacity: 0; }
.apply-modal-backdrop.show { display: flex; animation: jmsFadeIn .25s ease forwards; }
.apply-modal { background: #fff; width: min(1080px, 92vw); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.35); overflow: hidden; transform: translateY(16px) scale(.98); opacity: 0; }
.apply-modal-backdrop.show .apply-modal { animation: jmsSlideUp .25s ease forwards; }
@keyframes jmsFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes jmsSlideUp { from { opacity: 0; transform: translateY(24px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
.apply-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 22px 26px;  }
.apply-modal-title { font-size: 1.5rem; font-weight: 700; }
.apply-modal-subtitle { margin-left: 6px; margin-top: 6px; color: #666; font-weight: 600; }
.apply-modal-close { background: transparent; border: 0; font-size: 22px; line-height: 1; cursor: pointer; color:black;}
.apply-modal-close:hover { background: transparent; border: 0; font-size: 22px; line-height: 1; cursor: pointer; color:black;}

.apply-modal-body { padding: 26px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
.form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
.upload-box { position: relative; border: 2px dashed rgba(20,162,106,0.5); background: #F5FBF8; border-radius: 12px; padding: 6px; cursor: pointer; }
.upload-box:hover { border-color: #14A26A; }
.upload-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-inner { display: grid; grid-template-columns: 56px 1fr; align-items: center; gap: 16px; padding: 14px; }
.upload-icon { width: 56px; height: 56px; border: 1px dashed rgba(20,162,106,0.45); background: #EAF7F1; border-radius: 10px; color: #14A26A; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.upload-title { font-weight: 500; color: #111827; margin-bottom: 6px; }
.upload-guidelines { margin: 0; padding-left: 18px; font-size: 13px; color: #0E8B63; line-height: 1.6; font-weight: 400; }
.upload-guidelines li { margin: 2px 0; }
.submit-btn { width: -webkit-fill-available; margin: 10px auto 0; display: block; background: #000; color: #fff; padding: 14px 18px; border-radius: 10px;border: solid 2px black; font-weight: 700; cursor: pointer; transition: transform .15s ease; }
.submit-btn:hover { background-color: transparent; color: black; border: 2px solid black; }
.submit-btn:disabled { background: #bbb; cursor: not-allowed; }
.button-apply-1:hover {background-color: white !important; color: black !important;}
@media (max-width: 768px) {
  .jms-header { padding: 40px 0; }
  .page-title { font-size: 2rem; }
  .job-header-bar { flex-direction: column; align-items: flex-start; gap: 12px; }
  .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="jms-container">
    <div class="jms-header">
        <div class="container">
            <div class="job-header-bar">
                <div class="job-header-left">
                    <h1 class="page-title"><?php echo esc_html($job->name); ?></h1>
                    <div class="job-header-meta">
                        <span class="meta"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($job->location_name ?? ''); ?></span>
                        <span class="meta"><i class="fas fa-briefcase"></i> <?php echo esc_html(JMS_Templates::format_work_type($job->work_type ?? '')); ?></span>
                    </div>
                </div>
                <div class="job-header-actions">
                    <?php if (!$is_deadline_passed): ?>
                        <button class="apply-now-btn-header open-apply-modal button-apply-1" type="button" style="
                        font-weight: 500;
                        padding: 10px 50px 10px 50px;
                        background-color: transparent;
                        border: solid 2px white !important;
                        ">Apply Now</button>
                    <?php else: ?>
                        <button class="apply-now-btn-header" disabled>Application Closed</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="job-content">
            <div class="job-details-grid">
                    <?php if (!empty($job->description)): ?>
                        <div>
                            <?php echo wp_kses_post($job->description); ?>
                        </div>
                        <?php if (!$is_deadline_passed): ?>
                            <div style="margin-top:24px;">
                                <button class="apply-now-btn-inline open-apply-modal" type="button">Apply Now</button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$is_deadline_passed): ?>
    <div id="apply-modal-backdrop" class="apply-modal-backdrop" aria-hidden="true">
        <div class="apply-modal" role="dialog" aria-modal="true" aria-labelledby="apply-modal-title">
            <div class="apply-modal-header">
            <div style="
    display: flex;
    align-items: center;
    align-content: center;
    flex-direction: row;
">
                    <div class="apply-modal-title" id="apply-modal-title">Apply now</div>
                    <div class="apply-modal-subtitle">—> <?php echo esc_html($job->name); ?></div>
                </div>
                <button class="apply-modal-close" id="apply-modal-close" aria-label="Close">×</button>
            </div>
            <div class="apply-modal-body">
                <form id="job-application-form" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo esc_attr($job->id); ?>">
                    <input type="hidden" name="action" value="jms_submit_application">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('jms_nonce'); ?>">
                    <div class="form-group">
                        <label for="applicant_name">Name</label>
                        <input type="text" id="applicant_name" name="name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="applicant_email">Email</label>
                            <input type="email" id="applicant_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="applicant_phone">Phone Number</label>
                            <input type="tel" id="applicant_phone" name="phone" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="upload-box">
                            <input class="upload-input" type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                            <div class="upload-inner">
                                <div class="upload-icon"><img src="<?php echo plugin_dir_url(__DIR__) . 'assets/images/Icon.png'; ?>" alt="Upload Resume"></div>
                                <div>
                                    <div class="upload-title">Upload your CV</div>
                                    <ul class="upload-guidelines">
                                        <li>You can only upload <strong>1 file</strong>, Max file size is <strong>20 MB</strong></li>
                                        <li>Ensure your file is clear and legible, with no blurry or unclear sections.</li>
                                        <li>Allowed formats: PDF, DOC, DOCX.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn" id="apply-submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const openBtns = document.querySelectorAll('.open-apply-modal');
  const backdrop = document.getElementById('apply-modal-backdrop');
  const closeBtn = document.getElementById('apply-modal-close');
  const form = document.getElementById('job-application-form');
  const submitBtn = document.getElementById('apply-submit');
  const isJmsBound = form && form.getAttribute('data-jms-bound') === '1';

  if (openBtns && backdrop) {
    openBtns.forEach(function(btn){
      btn.addEventListener('click', function () {
        // ensure inline display doesn't block the CSS class
        backdrop.style.display = 'flex';
        // trigger fade-in
        backdrop.classList.add('show');
        backdrop.setAttribute('aria-hidden', 'false');
      });
    });
  }

  if (closeBtn && backdrop) {
    function hideModal() {
      backdrop.classList.remove('show');
      backdrop.setAttribute('aria-hidden', 'true');
      setTimeout(function(){ backdrop.style.display = 'none'; }, 250);
    }
    closeBtn.addEventListener('click', hideModal);
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) hideModal();
    });
  }

  // Handle file upload title change
  const cvFileInput = document.getElementById('cv_file');
  if (cvFileInput) {
    cvFileInput.addEventListener('change', function() {
      const uploadTitle = document.querySelector('.upload-title');
      if (this.files && this.files[0] && uploadTitle) {
        uploadTitle.textContent = 'File Name : ' + this.files[0].name;
      } else if (uploadTitle) {
        uploadTitle.textContent = 'Upload your CV';
      }
    });
  }

  if (form && !isJmsBound) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      let wasSuccessful = false;
      const originalHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
      }
      const formData = new FormData(form);
      try {
        const res = await fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: formData });
        const data = await res.json().catch(() => null);
        if (res.ok && data && (data.success || data.status === 'success')) {
          wasSuccessful = true;
          if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Your Request Submited Sucessfully';
            submitBtn.disabled = true;
          }
          // optionally disable inputs to prevent re-submit
          if (form) Array.from(form.elements).forEach(el => { if (el && el.tagName !== 'BUTTON') el.disabled = true; });
          // auto-close modal after 3 seconds
          if (backdrop) {
            setTimeout(function(){
              backdrop.classList.remove('show');
              backdrop.setAttribute('aria-hidden', 'true');
              setTimeout(function(){ backdrop.style.display = 'none'; }, 250);
            }, 3000);
          }
        } else {
          alert((data && (data.message || data.error)) || 'Failed to submit. Please try again.');
        }
      } catch (err) {
        alert('Network error. Please try again.');
      } finally {
        if (!wasSuccessful && submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHtml;
        }
      }
    });
  }
});
</script>

<?php get_footer(); ?>