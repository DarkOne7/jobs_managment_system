/**
 * Admin JavaScript for Job System
 */

jQuery(document).ready(function($) {
    
    /**
     * Initialize color picker for department colors
     */
    if ($('.color-picker').length) {
        $('.color-picker').wpColorPicker({
            defaultColor: '#00a0d2',
            change: function(event, ui) {
                // Handle color change if needed
                console.log('Color changed to:', ui.color.toString());
            },
            clear: function() {
                // Handle color clear if needed
                console.log('Color cleared');
            }
        });
    }
    
    /**
     * Department selection enhancement
     */
    if ($('#job_department').length) {
        initDepartmentSelection();
    }
    
    /**
     * Initialize department selection
     */
    function initDepartmentSelection() {
        var $departmentSelect = $('#job_department');
        
        // Add visual feedback for department selection
        $departmentSelect.on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var departmentName = selectedOption.text();
            
            if (departmentName && departmentName !== 'Select Department') {
                showNotice('Department "' + departmentName + '" selected', 'info');
            }
        });
        
        // Highlight if no department is selected
        if (!$departmentSelect.val()) {
            $departmentSelect.addClass('no-department-selected');
        }
        
        $departmentSelect.on('change', function() {
            if ($(this).val()) {
                $(this).removeClass('no-department-selected');
            } else {
                $(this).addClass('no-department-selected');
            }
        });
    }
    
    /**
     * Job applications table enhancements
     */
    if ($('.job-applications-table').length) {
        initApplicationsTable();
    }
    
    /**
     * Initialize applications table functionality
     */
    function initApplicationsTable() {
        // Job filter functionality
        $('#job-filter').on('change', function() {
            var selectedJob = $(this).val();
            var $rows = $('tbody tr[data-job-id]');
            
            if (selectedJob) {
                $rows.hide();
                $rows.filter('[data-job-id="' + selectedJob + '"]').show();
            } else {
                $rows.show();
            }
            
            // Update visible count
            updateVisibleCount();
        });
        
        // Status update functionality
        $('.application-status').on('change', function() {
            var $select = $(this);
            var applicationId = $select.data('id');
            var status = $select.val();
            var originalStatus = $select.data('original-status') || $select.find('option:selected').data('original');
            
            // Store original status
            if (!$select.data('original-status')) {
                $select.data('original-status', originalStatus);
            }
            
            // Show loading state
            $select.prop('disabled', true);
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_application_status',
                    application_id: applicationId,
                    status: status,
                    nonce: $('#job-system-nonce').val() || $('meta[name="job-system-nonce"]').attr('content')
                },
                success: function(response) {
                    $select.prop('disabled', false);
                    
                    if (response.success) {
                        // Update original status
                        $select.data('original-status', status);
                        
                        // Show success feedback
                        showNotice('Status updated successfully', 'success');
                        
                        // Update row styling based on status
                        updateRowStyling($select.closest('tr'), status);
                    } else {
                        // Revert to original status
                        $select.val(originalStatus);
                        showNotice(response.data.message || 'Failed to update status', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $select.prop('disabled', false);
                    $select.val(originalStatus);
                    showNotice('An error occurred while updating status', 'error');
                    console.error('AJAX Error:', error);
                }
            });
        });
        
        // Add search functionality for applications
        addApplicationSearch();
        
        // Add export functionality
        addExportFunctionality();
    }
    
    /**
     * Update row styling based on status
     */
    function updateRowStyling($row, status) {
        // Remove existing status classes
        $row.removeClass('status-pending status-reviewed status-accepted status-rejected');
        
        // Add new status class
        $row.addClass('status-' + status);
        
        // Update status colors
        var colors = {
            'pending': '#fff3cd',
            'reviewed': '#d1ecf1', 
            'accepted': '#d4edda',
            'rejected': '#f8d7da'
        };
        
        if (colors[status]) {
            $row.css('background-color', colors[status]);
        }
    }
    
    /**
     * Add search functionality for applications
     */
    function addApplicationSearch() {
        if (!$('#application-search').length) {
            var searchHtml = '<input type="text" id="application-search" placeholder="Search applications..." style="margin-bottom: 10px; padding: 8px; width: 300px;">';
            $('.job-application-search').append(searchHtml);
        }
        
        var searchTimeout;
        $('#application-search').on('input', function() {
            clearTimeout(searchTimeout);
            var query = $(this).val().toLowerCase();
            
            searchTimeout = setTimeout(function() {
                filterApplications(query);
            }, 300);
        });
    }
    
    /**
     * Filter applications based on search query
     */
    function filterApplications(query) {
        $('tbody tr[data-job-id]').each(function() {
            var $row = $(this);
            var applicantName = $row.find('td:nth-child(1)').text().toLowerCase();
            var applicantEmail = $row.find('td:nth-child(2)').text().toLowerCase();
            var jobTitle = $row.find('td:nth-child(4)').text().toLowerCase();
            
            var matches = applicantName.includes(query) || 
                         applicantEmail.includes(query) || 
                         jobTitle.includes(query);
            
            if (matches || query === '') {
                $row.show();
            } else {
                $row.hide();
            }
        });
        
        updateVisibleCount();
    }
    
    /**
     * Update visible applications count
     */
    function updateVisibleCount() {
        var total = $('tbody tr[data-job-id]').length;
        var visible = $('tbody tr[data-job-id]:visible').length;
        
        if (!$('.applications-count').length) {
            $('.job-application-search').append('<div class="applications-count"></div>');
        }
        
        $('.applications-count').text('Showing ' + visible + ' of ' + total + ' applications');
    }
    
    /**
     * Add export functionality
     */
    function addExportFunctionality() {
        if (!$('#export-applications').length) {
            var exportButton = '<button id="export-applications" class="button" style="margin-left: 10px;">Export CSV</button>';
            $('.job-application-search').append(exportButton);
        }
        
        $('#export-applications').on('click', function() {
            exportApplicationsToCSV();
        });
    }
    
    /**
     * Export applications to CSV
     */
    function exportApplicationsToCSV() {
        var csv = [];
        var headers = ['Applicant Name', 'Email', 'Phone', 'Job', 'Application Date', 'Status'];
        csv.push(headers.join(','));
        
        $('tbody tr[data-job-id]:visible').each(function() {
            var row = [];
            $(this).find('td').each(function(index) {
                if (index < 6) { // Skip CV column for CSV
                    var text = $(this).text().trim();
                    // Escape commas and quotes
                    text = text.replace(/"/g, '""');
                    if (text.includes(',')) {
                        text = '"' + text + '"';
                    }
                    row.push(text);
                }
            });
            csv.push(row.join(','));
        });
        
        // Download CSV
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'job-applications-' + new Date().toISOString().split('T')[0] + '.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        var noticeClass = 'notice notice-' + type + ' is-dismissible';
        var noticeHtml = '<div class="' + noticeClass + '"><p>' + message + '</p></div>';
        
        // Remove existing notices
        $('.notice').remove();
        
        // Add new notice
        $('.wrap h1').after(noticeHtml);
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $('.notice').fadeOut();
        }, 3000);
    }
    
    /**
     * Job meta box enhancements
     */
    if ($('#job-details').length) {
        initJobMetaBox();
    }
    
    /**
     * Initialize job meta box functionality
     */
    function initJobMetaBox() {
        // Date/time picker for application deadline
        if ($('#application_deadline').length && typeof flatpickr !== 'undefined') {
            flatpickr('#application_deadline', {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                minDate: 'today'
            });
        }
        
        // Location autocomplete (if Google Places API is available)
        if ($('#job_location').length && typeof google !== 'undefined') {
            var autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('job_location'),
                { types: ['(cities)'] }
            );
        }
    }
    
    /**
     * Dashboard widget functionality
     */
    if ($('.job-system-dashboard-widget').length) {
        initDashboardWidget();
    }
    
    /**
     * Initialize dashboard widget
     */
    function initDashboardWidget() {
        // Refresh stats periodically
        setInterval(function() {
            refreshJobStats();
        }, 300000); // 5 minutes
    }
    
    /**
     * Refresh job statistics
     */
    function refreshJobStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_job_stats',
                nonce: $('#job-system-nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to refresh stats:', error);
            }
        });
    }
    
    /**
     * Update statistics display
     */
    function updateStatsDisplay(stats) {
        $('.job-system-stat').each(function() {
            var $stat = $(this);
            var statType = $stat.data('stat-type');
            
            if (stats[statType] !== undefined) {
                $stat.find('.number').text(stats[statType]);
            }
        });
    }
    
    /**
     * Bulk actions for applications
     */
    if ($('.bulkactions').length) {
        addBulkActions();
    }
    
    /**
     * Add bulk actions functionality
     */
    function addBulkActions() {
        // Add bulk action dropdown
        var bulkActionsHtml = '<select id="bulk-action">' +
            '<option value="">Bulk Actions</option>' +
            '<option value="mark-reviewed">Mark as Reviewed</option>' +
            '<option value="mark-accepted">Mark as Accepted</option>' +
            '<option value="mark-rejected">Mark as Rejected</option>' +
            '<option value="delete">Delete</option>' +
            '</select>' +
            '<button id="apply-bulk-action" class="button">Apply</button>';
        
        $('.tablenav.top').prepend('<div class="alignleft actions">' + bulkActionsHtml + '</div>');
        
        // Handle bulk action application
        $('#apply-bulk-action').on('click', function() {
            var action = $('#bulk-action').val();
            var checkedBoxes = $('input[name="application[]"]:checked');
            
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            if (checkedBoxes.length === 0) {
                alert('Please select applications to modify.');
                return;
            }
            
            if (confirm('Are you sure you want to apply this action to ' + checkedBoxes.length + ' applications?')) {
                applyBulkAction(action, checkedBoxes);
            }
        });
    }
    
    /**
     * Apply bulk action to selected applications
     */
    function applyBulkAction(action, checkedBoxes) {
        var applicationIds = [];
        checkedBoxes.each(function() {
            applicationIds.push($(this).val());
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bulk_update_applications',
                bulk_action: action,
                application_ids: applicationIds,
                nonce: $('#job-system-nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    location.reload(); // Refresh page to show changes
                } else {
                    showNotice(response.data.message || 'Bulk action failed', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice('An error occurred during bulk action', 'error');
                console.error('Bulk Action Error:', error);
            }
        });
    }
    
    /**
     * Initialize tooltips
     */
    if ($('[title]').length && typeof $.fn.tooltip !== 'undefined') {
        $('[title]').tooltip();
    }
    
    /**
     * Initialize sortable tables
     */
    if ($('.wp-list-table').length && typeof $.fn.sortable !== 'undefined') {
        // Add sortable functionality if needed
    }
    
    // Initialize everything when document is ready
    $(document).ready(function() {
        updateVisibleCount();
        
        // Add keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                e.preventDefault();
                $('form').submit();
            }
        });
    });
});
