/**
 * PaySheetPro - Leave Management JavaScript
 */

(function() {
    'use strict';

    // Calculate leave days
    function calculateLeaveDays() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const daysDisplay = document.getElementById('days_display');

        if (!startDate || !endDate || !daysDisplay) return;

        function updateDays() {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);

            if (startDate.value && endDate.value) {
                if (end >= start) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    daysDisplay.value = diffDays + ' day(s)';
                    daysDisplay.style.color = '#10b981';
                } else {
                    daysDisplay.value = 'Invalid date range';
                    daysDisplay.style.color = '#ef4444';
                }
            } else {
                daysDisplay.value = '';
            }
        }

        startDate.addEventListener('change', updateDays);
        endDate.addEventListener('change', updateDays);
    }

    // Check leave balance before submission
    function checkLeaveBalance() {
        const leaveForm = document.getElementById('leaveRequestForm');
        if (!leaveForm) return;

        leaveForm.addEventListener('submit', function(e) {
            const leaveType = document.getElementById('leave_type_id');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            if (!leaveType.value || !startDate.value || !endDate.value) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
                return false;
            }

            const start = new Date(startDate.value);
            const end = new Date(endDate.value);

            if (end < start) {
                e.preventDefault();
                showToast('End date cannot be before start date', 'error');
                return false;
            }

            // Additional validation can be added here
            return true;
        });
    }

    // Filter leave requests
    function initLeaveFilters() {
        const statusFilter = document.getElementById('statusFilter');
        const leaveTypeFilter = document.getElementById('leaveTypeFilter');

        if (statusFilter) {
            statusFilter.addEventListener('change', filterLeaveRequests);
        }

        if (leaveTypeFilter) {
            leaveTypeFilter.addEventListener('change', filterLeaveRequests);
        }
    }

    // Filter leave requests function
    function filterLeaveRequests() {
        const statusFilter = document.getElementById('statusFilter')?.value.toLowerCase() || '';
        const leaveTypeFilter = document.getElementById('leaveTypeFilter')?.value.toLowerCase() || '';
        const leaveRows = document.querySelectorAll('.leave-request-card, .leave-item');

        leaveRows.forEach(row => {
            const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';
            const leaveType = row.querySelector('.leave-type-badge')?.textContent.toLowerCase() || '';

            const statusMatch = statusFilter === '' || status.includes(statusFilter);
            const typeMatch = leaveTypeFilter === '' || leaveType.includes(leaveTypeFilter);

            if (statusMatch && typeMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateFilterResults();
    }

    // Update filter results count
    function updateFilterResults() {
        const visibleItems = document.querySelectorAll('.leave-request-card:not([style*="display: none"]), .leave-item:not([style*="display: none"])');
        const resultCount = document.getElementById('filterResultCount');
        
        if (resultCount) {
            resultCount.textContent = visibleItems.length + ' result(s)';
        }
    }

    // Approve/Reject confirmation
    function initLeaveActions() {
        const approveButtons = document.querySelectorAll('.btn-approve');
        const rejectButtons = document.querySelectorAll('.btn-reject');

        approveButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const employeeName = this.dataset.employee || 'this employee';
                const leaveDays = this.dataset.days || '';
                
                if (!confirm(`Approve leave request for ${employeeName} (${leaveDays} days)?`)) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        rejectButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const employeeName = this.dataset.employee || 'this employee';
                
                if (!confirm(`Reject leave request for ${employeeName}?`)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    // Highlight insufficient balance
    function highlightInsufficientBalance() {
        const leaveCards = document.querySelectorAll('.leave-request-card');

        leaveCards.forEach(card => {
            const daysCount = parseInt(card.dataset.days) || 0;
            const remainingDays = parseInt(card.dataset.remaining) || 0;

            if (daysCount > remainingDays) {
                card.classList.add('insufficient-balance');
                
                // Add warning badge if not exists
                if (!card.querySelector('.warning-badge')) {
                    const warningBadge = document.createElement('div');
                    warningBadge.className = 'warning-badge';
                    warningBadge.innerHTML = '⚠️ Insufficient Balance';
                    card.querySelector('.leave-request-header')?.appendChild(warningBadge);
                }
            }
        });
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        calculateLeaveDays();
        checkLeaveBalance();
        initLeaveFilters();
        initLeaveActions();
        highlightInsufficientBalance();
    });

    // Export functions to window object
    window.LeaveManager = {
        calculateLeaveDays: calculateLeaveDays,
        filterLeaveRequests: filterLeaveRequests
    };

})();