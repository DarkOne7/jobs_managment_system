/**
 * Job Management System - Main JavaScript
 */

(function($) {
    'use strict';

    // Ensure global notification function exists to avoid runtime errors from cached scripts
    if (typeof window.showNotification !== 'function') {
        window.showNotification = function(message, type) {
            // Minimal no-op/fallback
            if (type === 'error') {
                console.error(message || 'An error occurred');
            } else if (message) {
                console.log(message);
            }
        };
    }

    $(document).ready(function() {
        initJobSystem();
    });

    /**
     * Initialize Job Management System
     */
    function initJobSystem() {
        // Initialize animations
        initAnimations();
        
        // Initialize job application form
        initJobApplicationForm();
        
        // Initialize social sharing
        initSocialSharing();
        
        // Initialize UI enhancements
        initUIEnhancements();
    }

    /**
     * Initialize animations and effects
     */
    function initAnimations() {
        // Fade in cards on scroll
        $('.department-card, .job-card').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(30px)',
                'transition': 'all 0.6s ease'
            });
            
            setTimeout(() => {
                $(this).css({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                });
            }, index * 100);
        });

        // Smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
            }
        });
    }

    /**
     * Initialize job application form
     */
    function initJobApplicationForm() {
        const $form = $('#job-application-form');
        
        if ($form.length === 0) return;

        // Mark as bound to avoid duplicate listeners from inline scripts
        $form.data('jms-bound', true);
        $form.attr('data-jms-bound', '1');

        // Form validation
        $form.on('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm($form)) {
                return;
            }
            
            submitApplication($form);
        });

        // File upload: show selected filename and validate
        $('#cv_file').on('change', function() {
            if (this.files && this.files[0]) {
                updateUploadTitle(this);
            } else {
                resetUploadTitle(this);
            }
            validateFile(this);
        });

        // Real-time validation
        $form.find('.form-control').on('blur', function() {
            validateField($(this));
        });
    }

    /**
     * Validate form fields
     */
    function validateForm($form) {
        let isValid = true;
        
        // Validate required fields
        $form.find('[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        // Validate file upload (required)
        const fileInput = $form.find('#cv_file')[0];
        if (!fileInput || fileInput.files.length === 0) {
            isValid = false;
            showFieldError($('#cv_file'), 'Please upload your CV');
        } else if (!validateFile(fileInput)) {
            isValid = false;
        }

        return isValid;
    }

    /**
     * Validate individual field
     */
    function validateField($field) {
        const value = $field.val().trim();
        const fieldType = $field.attr('type') || 'text';
        const isRequired = $field.prop('required');
        
        let isValid = true;
        let errorMessage = '';

        // Clear previous errors
        clearFieldError($field);

        // Check required fields
        if (isRequired && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        // Validate email
        else if (fieldType === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        // Validate phone
        else if ($field.attr('name') === 'phone' && value) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
        }

        // Show error if invalid
        if (!isValid) {
            showFieldError($field, errorMessage);
        }

        return isValid;
    }

    /**
     * Validate file upload
     */
    function validateFile(fileInput) {
        const file = fileInput.files[0];
        if (!file) return true;

        let isValid = true;
        let errorMessage = '';

        // Check file size (20MB)
        const maxSize = 20 * 1024 * 1024;
        if (file.size > maxSize) {
            isValid = false;
            errorMessage = jmsAjax.strings.file_too_large || 'File size is too large. Maximum size is 20MB.';
        }

        // Check file type
        const allowedTypes = ['pdf', 'doc', 'docx'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) {
            isValid = false;
            errorMessage = jmsAjax.strings.invalid_file_type || 'Invalid file type. Please upload a PDF or DOC file.';
        }

        const $field = $(fileInput);
        clearFieldError($field);

        if (!isValid) {
            showFieldError($field, errorMessage);
        }

        return isValid;
    }

    /**
     * Update upload title to show selected file name
     */
    function updateUploadTitle(fileInput) {
        const file = fileInput.files && fileInput.files[0];
        const $box = $(fileInput).closest('.upload-box');
        const $title = $box.find('.upload-title');
        if (file && $title.length) {
            $title.text('File Name : ' + file.name);
        }
    }

    /**
     * Reset upload title to default text
     */
    function resetUploadTitle(fileInput) {
        const $box = $(fileInput).closest('.upload-box');
        const $title = $box.find('.upload-title');
        if ($title.length) {
            $title.text('Upload your CV');
        }
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        $field.addClass('is-invalid');
        
        let $error = $field.siblings('.field-error');
        if ($error.length === 0) {
            $error = $('<div class="field-error"></div>');
            $field.after($error);
        }
        
        $error.text(message).show();
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        $field.removeClass('is-invalid');
        $field.siblings('.field-error').hide();
    }

    /**
     * Submit job application
     */
    function submitApplication($form) {
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        let wasSuccessful = false;
        
        // Show loading state
        $submitBtn.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

        // Prepare form data
        const formData = new FormData($form[0]);

        // Submit via AJAX
        $.ajax({
            url: jmsAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    wasSuccessful = true;
                    showNotification(response.data && response.data.message ? response.data.message : 'Success', 'success');
                    // Optionally reset the form fields while keeping the modal open until it auto-closes
                    if ($form.length && $form[0]) { $form[0].reset(); }
                } else {
                    showNotification(response.data || 'An error occurred. Please try again.', 'error');
                }
            },
            error: function() {
                showNotification('Network error. Please check your connection and try again.', 'error');
            },
            complete: function() {
                // If not successful, restore the original button state
                if (!wasSuccessful) {
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            }
        });
    }

    /**
     * Initialize social sharing
     */
    function initSocialSharing() {
        // Share job function
        window.shareJob = function(platform) {
            const jobTitle = $('.page-title').text() || document.title;
            const jobUrl = window.location.href;
            const jobDescription = $('.page-description').text() || '';
            
            let shareUrl = '';
            
            switch (platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(jobUrl)}`;
                    break;
                    
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(jobTitle)}&url=${encodeURIComponent(jobUrl)}`;
                    break;
                    
                case 'linkedin':
                    shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(jobUrl)}`;
                    break;
                    
                default:
                    return;
            }
            
            // Open share window
            const shareWindow = window.open(
                shareUrl,
                `share-${platform}`,
                'width=600,height=400,scrollbars=yes,resizable=yes'
            );
            
            if (shareWindow) {
                shareWindow.focus();
            }
        };

        // Copy job link function
        window.copyJobLink = function() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(window.location.href).then(function() {
                    showNotification('Job link copied to clipboard!', 'success');
                }).catch(function() {
                    fallbackCopyTextToClipboard(window.location.href);
                });
            } else {
                fallbackCopyTextToClipboard(window.location.href);
            }
        };
    }

    /**
     * Fallback copy to clipboard
     */
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showNotification('Job link copied!', 'success');
            } else {
                showNotification('Failed to copy link', 'error');
            }
        } catch (err) {
            showNotification('Copy not supported', 'error');
        }
        
        document.body.removeChild(textArea);
    }

    /**
     * Initialize UI enhancements
     */
    function initUIEnhancements() {
        // Enhance buttons on hover
        $('.btn').on('mouseenter', function() {
            $(this).addClass('btn-hover');
        }).on('mouseleave', function() {
            $(this).removeClass('btn-hover');
        });

        // Card hover effects
        $('.department-card, .job-card').on('mouseenter', function() {
            $(this).addClass('card-hover');
        }).on('mouseleave', function() {
            $(this).removeClass('card-hover');
        });

        // Initialize tooltips if available
        if (typeof $.fn.tooltip === 'function') {
            $('[data-toggle="tooltip"]').tooltip();
        }

        // Auto-hide notifications after 5 seconds
        setTimeout(function() {
            $('.notification').fadeOut();
        }, 5000);
    }

    // Lightweight notification handler tailored for application success UX
    function showNotification(message, type = 'info') {
        // On success of job application, switch button state to a check and close modal after 3 seconds
        if (type === 'success') {
            const $submitBtn = $('#job-application-form button[type="submit"]');
            if ($submitBtn.length && $submitBtn.html().indexOf('fa-spinner') !== -1) {
                $submitBtn.prop('disabled', true)
                          .html('<i class="fas fa-check"></i> Your Request Submited Sucessfully');

                const $modal = $('#apply-modal-backdrop');
                if ($modal.length) {
                    setTimeout(function() {
                        // Trigger fade-out if used, then hide
                        $modal.removeClass('show');
                        setTimeout(function() { $modal.css('display', 'none'); }, 300);
                    }, 3000);
                }
                return;
            }
        }
        // Fallback: log or simple alert on errors
        if (type === 'error') {
            // Avoid blocking UX with alerts if not desired; log instead
            console.error(message || 'An error occurred');
        } else {
            if (message) { console.log(message); }
        }
    }

    // Expose globally for other scripts that may call it
    window.showNotification = showNotification;

    /**
     * Initialize mobile optimizations
     */
    function initMobileOptimizations() {
        // Touch feedback for mobile
        $('.btn, .department-card, .job-card').on('touchstart', function() {
            $(this).addClass('touch-active');
        }).on('touchend', function() {
            const $this = $(this);
            setTimeout(function() {
                $this.removeClass('touch-active');
            }, 150);
        });
    }

    // Initialize mobile optimizations on mobile devices
    if (window.innerWidth <= 768) {
        initMobileOptimizations();
    }

    // Re-initialize on window resize
    $(window).on('resize', function() {
        if (window.innerWidth <= 768) {
            initMobileOptimizations();
        }
    });

})(jQuery);