/**
 * JavaScript خاص بصفحة الوظيفة الواحدة
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        initSingleJobFeatures();
    });
    
    function initSingleJobFeatures() {
        // تهيئة نموذج التقديم
        initJobApplicationForm();
        
        // تحسين التجربة البصرية
        initVisualEnhancements();
    }
    
    /**
     * نموذج التقديم للوظيفة
     */
    function initJobApplicationForm() {
        const form = $('#job-application-form');
        
        if (form.length === 0) return;
        
        form.on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.find('.btn-apply');
            const originalText = submitBtn.html();
            
            // تغيير نص الزر أثناء الإرسال
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
            submitBtn.prop('disabled', true);
            
            // هنا يمكنك إضافة كود AJAX لإرسال البيانات
            // مؤقتاً سنعرض رسالة تأكيد
            setTimeout(function() {
                // عرض رسالة نجاح                
                // إعادة تعيين الزر
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }, 2000);
        });
    }
    
    /**
     * عرض إشعار خاص بالوظيفة
     */
    
    /**
     * الحصول على أيقونة الإشعار
     */
    function getJobNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    /**
     * إخفاء الإشعار
     */
    window.hideJobNotification = function(notificationId) {
        const $notification = $('#' + notificationId);
        $notification.removeClass('show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    };
    
    /**
     * تحسينات بصرية
     */
    function initVisualEnhancements() {
        // تأثير تمرير سلس للروابط الداخلية
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
        
        // تأثير الظهور للبطاقات
        $('.info-card, .apply-card, .share-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)',
                'transition': 'all 0.6s ease'
            });
            
            setTimeout(() => {
                $(this).css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }, index * 200);
        });
        
        // تأثير الضغط على الأزرار
        $('.btn-apply').on('mousedown', function() {
            $(this).css('transform', 'scale(0.95)');
        }).on('mouseup', function() {
            $(this).css('transform', 'scale(1)');
        });
    }
    
})(jQuery);

