/**
 * Job Management System - Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initAdmin();
    });

    /**
     * Initialize admin functionality
     */
    function initAdmin() {
        // Initialize department form
        initDepartmentForm();
        
        // Initialize icon picker
        initIconPicker();
        
        // Initialize color picker
        initColorPicker();
        
        // Initialize tooltips
        initTooltips();
        
        // Initialize delete confirmations
        initDeleteConfirmations();

        // Initialize application quick approve/reject actions
        initApplicationQuickActions();
    }

    /**
     * Initialize department form functionality
     */
    function initDepartmentForm() {
        // Auto-generate slug from name
        $('#department_name').on('input', function() {
            const name = $(this).val();
            const slug = name.toLowerCase()
                            .replace(/[^a-z0-9\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-')
                            .trim('-');
            
            const $slugField = $('#department_slug');
            if (!$slugField.val() || $slugField.data('auto-generated')) {
                $slugField.val(slug).data('auto-generated', true);
            }
        });
        
        // Mark slug as manually edited
        $('#department_slug').on('input', function() {
            $(this).data('auto-generated', false);
        });
    }

    /**
     * Initialize quick approve/reject for applications list
     */
    function initApplicationQuickActions() {
        const STATUS_TEXT = {
            approved: 'APPROVED',
            rejected: 'REJECTED',
            shortlisted: 'SHORTLISTED',
            pending: 'PENDING REVIEW'
        };

        function updateRowUI($btn, newStatus) {
            const $row = $btn.closest('tr');
            const $status = $row.find('.jms-status');
            // Reset status classes
            $status.removeClass('jms-status-pending jms-status-approved jms-status-rejected jms-status-shortlisted')
                   .addClass('jms-status-' + newStatus)
                   .text(STATUS_TEXT[newStatus] || newStatus.toUpperCase());
            // Remove quick actions for non-pending
            if (newStatus !== 'pending') {
                $row.find('.quick-actions').remove();
            }
        }

        function quickUpdate($btn, applicationId, newStatus) {
            if (!applicationId || !newStatus) return;
            const originalHtml = $btn.html();
            showLoading($btn);

            $.ajax({
                url: jmsAdmin.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'jms_approve_application',
                    nonce: jmsAdmin.nonce,
                    application_id: applicationId,
                    status: newStatus,
                    admin_notes: ''
                },
                success: function(resp) {
                    if (resp && resp.success) {
                        updateRowUI($btn, newStatus);
                        showNotice(resp.data && resp.data.message ? resp.data.message : 'Application updated', 'success');
                    } else {
                        showNotice((resp && resp.data) || 'Failed to update application', 'error');
                    }
                },
                error: function() {
                    showNotice('Network error occurred', 'error');
                },
                complete: function() {
                    $btn.html(originalHtml);
                    hideLoading($btn);
                }
            });
        }

        $(document).on('click', '.button-approve', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');
            if (confirm('Approve this application?')) {
                quickUpdate($btn, id, 'approved');
            }
        });

        $(document).on('click', '.button-reject', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');
            if (confirm('Reject this application?')) {
                quickUpdate($btn, id, 'rejected');
            }
        });
    }

    /**
     * Initialize icon picker functionality
     */
    function initIconPicker() {
        // Update icon preview
        function updateIconPreview() {
            const iconClass = $('#department_icon').val() || 'fas fa-briefcase';
            const color = $('#department_color').val() || '#007cba';
            $('.icon-preview i').attr('class', iconClass).css('color', color);
        }
        
        $('#department_icon, #department_color').on('change input', updateIconPreview);
        
        // Icon selection from suggestions
        $('.icon-option').on('click', function(e) {
            e.preventDefault();
            const icon = $(this).data('icon');
            $('#department_icon').val(icon);
            updateIconPreview();
            
            // Highlight selected
            $('.icon-option').removeClass('selected');
            $(this).addClass('selected');
        });
        
        // Highlight current icon if it matches a suggestion
        const currentIcon = $('#department_icon').val();
        $('.icon-option[data-icon="' + currentIcon + '"]').addClass('selected');
        
        // Initial preview update
        updateIconPreview();
    }

    /**
     * Initialize color picker functionality
     */
    function initColorPicker() {
        // Enhanced color picker if available
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.color-picker').wpColorPicker({
                change: function(event, ui) {
                    updateIconPreview();
                }
            });
        }
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        // Add tooltips to various elements
        $('[title]').each(function() {
            $(this).attr('data-toggle', 'tooltip');
        });
        
        // Initialize Bootstrap tooltips if available
        if (typeof $.fn.tooltip !== 'undefined') {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    /**
     * Initialize delete confirmations
     */
    function initDeleteConfirmations() {
        $('.submitdelete').on('click', function(e) {
            const confirmed = confirm(jmsAdmin.strings.confirm_delete || 'Are you sure you want to delete this item?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        });
    }

    /**
     * Show loading state
     */
    function showLoading($element) {
        const originalText = $element.text();
        $element.data('original-text', originalText)
                .prop('disabled', true)
                .html('<span class="jms-loading"></span> ' + ((window.jmsAdmin && window.jmsAdmin.strings && window.jmsAdmin.strings.loading) ? window.jmsAdmin.strings.loading : 'Loading...'));
    }

    /**
     * Hide loading state
     */
    function hideLoading($element) {
        const originalText = $element.data('original-text');
        $element.prop('disabled', false).text(originalText);
    }

    /**
     * Show admin notice
     */
    function showNotice(message, type = 'success') {
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap > h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    }

    /**
     * AJAX form handler
     */
    function handleAjaxForm($form, successCallback) {
        $form.on('submit', function(e) {
            e.preventDefault();
            
            const $submitBtn = $form.find('button[type="submit"]');
            showLoading($submitBtn);
            
            $.ajax({
                url: jmsAdmin.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=jms_admin_action&nonce=' + jmsAdmin.nonce,
                success: function(response) {
                    if (response.success) {
                        showNotice(response.data.message, 'success');
                        if (successCallback) {
                            successCallback(response.data);
                        }
                    } else {
                        showNotice(response.data || 'An error occurred', 'error');
                    }
                },
                error: function() {
                    showNotice('Network error occurred', 'error');
                },
                complete: function() {
                    hideLoading($submitBtn);
                }
            });
        });
    }

    /**
     * Initialize data tables if available
     */
    function initDataTables() {
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.wp-list-table').DataTable({
                pageLength: 25,
                responsive: true,
                order: [[1, 'asc']], // Sort by name column
                columnDefs: [
                    { orderable: false, targets: [-1] } // Disable sorting on actions column
                ]
            });
        }
    }

    /**
     * Initialize charts if available
     */
    function initCharts() {
        // Dashboard charts could be added here
        if (typeof Chart !== 'undefined') {
            // Chart.js integration for dashboard statistics
        }
    }

    /**
     * Global functions
     */
    window.jmsAdmin = Object.assign({}, window.jmsAdmin || {}, {
        showNotice: showNotice,
        showLoading: showLoading,
        hideLoading: hideLoading,
        handleAjaxForm: handleAjaxForm
    });

})(jQuery);

