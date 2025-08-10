/**
 * Frontend JavaScript for Job System
 */

jQuery(document).ready(function($) {
    
    // Global variables
    var isSubmitting = false;
    
    /**
     * Handle department card clicks for navigation to /jobs/department?id=X
     */
    $(document).on('click', '.department-card', function(e) {
        e.preventDefault();
        var departmentId = $(this).data('department-id');
        
        if (departmentId) {
            window.location.href = '/jobs/department?id=' + departmentId;
        }
    });
    
    /**
     * Handle search functionality for departments
     */
    $(document).on('input', '.search-input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var $departmentCards = $('.department-card');
        
        $departmentCards.each(function() {
            var departmentName = $(this).find('.department-title, .department-name').text().toLowerCase();
            var departmentDesc = $(this).find('.department-description').text().toLowerCase();
            
            if (departmentName.includes(searchTerm) || departmentDesc.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show "no results" message if no cards are visible
        var visibleCards = $departmentCards.filter(':visible').length;
        var $noResults = $('.no-results-message');
        
        if (visibleCards === 0 && searchTerm.length > 0) {
            if ($noResults.length === 0) {
                $('.departments-grid').after('<div class="no-results-message" style="text-align: center; padding: 40px; color: #6b7280;"><h3>No departments found</h3><p>Try a different search term</p></div>');
            }
        } else {
            $noResults.remove();
        }
    });

    /**
     * Open job application modal
     */
    window.openJobModal = function(jobId) {
        $('#job_id').val(jobId);
        
        // Get job title from different possible sources
        var jobTitle = $('[data-job-id="' + jobId + '"] .job-title').text() || 
                      $('.entry-title').text() || 
                      $('.job-item h3 a').first().text() || 
                      'Job Application';
        $('#modal-job-title').text(jobTitle);
        
        $('#jobApplicationModal').fadeIn(300);
        $('body').addClass('modal-open').css('overflow', 'hidden');
        
        // Focus on first input
        setTimeout(function() {
            $('#applicant_name').focus();
        }, 300);
    };
    
    /**
     * Close job application modal
     */
    window.closeJobModal = function() {
        $('#jobApplicationModal').fadeOut(300);
        $('body').removeClass('modal-open').css('overflow', 'auto');
        $('#jobApplicationForm')[0].reset();
        clearMessages();
    };
    
    /**
     * Handle modal close on outside click
     */
    $(document).on('click', '#jobApplicationModal', function(e) {
        if (e.target === this) {
            closeJobModal();
        }
    });
    
    /**
     * Handle escape key to close modal
     */
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $('#jobApplicationModal').is(':visible')) {
            closeJobModal();
        }
    });
    
    /**
     * Handle file upload preview
     */
    $(document).on('change', '#cv_file', function() {
        var file = this.files[0];
        var $fileInfo = $('.file-upload-info');
        
        if (file) {
            var fileName = file.name;
            var fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
            var allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            var fileExtension = fileName.split('.').pop().toLowerCase();
            
            // Check file type
            if (!allowedTypes.includes(fileExtension)) {
                showMessage('Please upload a valid file format: PDF, DOC, DOCX, JPG, JPEG, PNG', 'error');
                $(this).val('');
                return;
            }
            
            // Check file size (20MB limit)
            if (fileSize > 20) {
                showMessage('File size must be less than 20MB', 'error');
                $(this).val('');
                return;
            }
            
            $fileInfo.html('<p style="color: green;">✓ File selected: ' + fileName + ' (' + fileSize + 'MB)</p>');
        }
    });
    
    /**
     * Handle form validation
     */
    function validateForm() {
        var isValid = true;
        var requiredFields = ['applicant_name', 'applicant_email'];
        
        requiredFields.forEach(function(fieldId) {
            var $field = $('#' + fieldId);
            var value = $field.val().trim();
            
            if (!value) {
                $field.addClass('error');
                isValid = false;
            } else {
                $field.removeClass('error');
            }
        });
        
        // Email validation
        var email = $('#applicant_email').val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#applicant_email').addClass('error');
            showMessage('Please enter a valid email address', 'error');
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Show message to user
     */
    function showMessage(message, type) {
        type = type || 'info';
        var className = 'message-' + type;
        
        // Remove existing messages
        $('.form-message').remove();
        
        var messageHtml = '<div class="form-message ' + className + '">' + message + '</div>';
        $('#jobApplicationForm').prepend(messageHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.form-message').fadeOut();
        }, 5000);
    }
    
    /**
     * Clear messages
     */
    function clearMessages() {
        $('.form-message').remove();
        $('.error').removeClass('error');
    }
    
    /**
     * Handle form submission
     */
    $(document).on('submit', '#jobApplicationForm', function(e) {
        e.preventDefault();
        
        if (isSubmitting) {
            return;
        }
        
        if (!validateForm()) {
            showMessage('Please fill in all required fields correctly', 'error');
            return;
        }
        
        isSubmitting = true;
        var formData = new FormData(this);
        formData.append('action', 'submit_job_application');
        formData.append('nonce', job_system_ajax.nonce);
        
        var $submitBtn = $(this).find('.submit-button');
        var originalText = $submitBtn.text();
        $submitBtn.text('Submitting...').prop('disabled', true);
        
        $.ajax({
            url: job_system_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage('Application submitted successfully!', 'success');
                    setTimeout(function() {
                        closeJobModal();
                    }, 2000);
                } else {
                    showMessage('Error: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                showMessage('An error occurred. Please try again.', 'error');
                console.error('AJAX Error:', status, error);
            },
            complete: function() {
                $submitBtn.text(originalText).prop('disabled', false);
                isSubmitting = false;
            }
        });
    });
    
    /**
     * Handle input focus/blur for better UX
     */
    $(document).on('focus', '.job-application-form input, .job-application-form textarea', function() {
        $(this).removeClass('error');
    });
    
    /**
     * Add loading states and animations
     */
    $(document).on('click', '.apply-button', function() {
        $(this).addClass('loading');
    });
    
    /**
     * Handle responsive navigation
     */
    $(window).on('resize', function() {
        // Adjust modal positioning on window resize
        if ($('#jobApplicationModal').is(':visible')) {
            var $modal = $('.job-modal-content');
            $modal.css({
                'margin-top': Math.max(0, ($(window).height() - $modal.outerHeight()) / 2) + 'px'
            });
        }
    });
});
