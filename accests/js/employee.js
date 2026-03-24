/**
 * PaySheetPro - Employee Management JavaScript
 */

(function() {
    'use strict';

    // Format number inputs with commas
    function formatNumberInputs() {
        const numberInputs = document.querySelectorAll('.format-number');
        
        numberInputs.forEach(input => {
            input.addEventListener('blur', function() {
                let value = this.value.replace(/,/g, '');
                if (value && !isNaN(value)) {
                    this.value = parseFloat(value).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            });

            input.addEventListener('focus', function() {
                this.value = this.value.replace(/,/g, '');
            });
        });
    }

    // Search functionality for employee table
    function initEmployeeSearch() {
        const searchInput = document.getElementById('employeeSearch');
        if (!searchInput) return;

        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const employeeRows = document.querySelectorAll('.employee-table tbody tr');

            employeeRows.forEach(row => {
                const name = row.querySelector('.employee-name-table')?.textContent.toLowerCase() || '';
                const nic = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const email = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';

                if (name.includes(searchTerm) || nic.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchResults();
        }, 300));
    }

    // Update search results count
    function updateSearchResults() {
        const visibleRows = document.querySelectorAll('.employee-table tbody tr:not([style*="display: none"])');
        const resultCount = document.getElementById('resultCount');
        
        if (resultCount) {
            resultCount.textContent = visibleRows.length;
        }
    }

    // Delete confirmation for employees
    function initDeleteConfirmation() {
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const employeeName = this.dataset.name || 'this employee';
                const deleteUrl = this.href;

                if (confirm(`Are you sure you want to delete ${employeeName}? This action cannot be undone.`)) {
                    window.location.href = deleteUrl;
                }
            });
        });
    }

    // Calculate salary breakdown preview
    function initSalaryPreview() {
        const salaryInput = document.getElementById('basic_salary');
        if (!salaryInput) return;

        salaryInput.addEventListener('input', debounce(function() {
            const salary = parseFloat(this.value.replace(/,/g, '')) || 0;
            
            if (salary > 0) {
                const epf = salary * 0.12;
                const etf = salary * 0.03;
                const hourlyRate = salary / 240;
                const otRate = hourlyRate * 1.5;

                // Update preview if elements exist
                updatePreviewElement('epfPreview', epf);
                updatePreviewElement('etfPreview', etf);
                updatePreviewElement('hourlyRatePreview', hourlyRate);
                updatePreviewElement('otRatePreview', otRate);

                showElement('salaryBreakdownPreview');
            } else {
                hideElement('salaryBreakdownPreview');
            }
        }, 300));
    }

    // Update preview element
    function updatePreviewElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = 'LKR ' + formatNumber(value.toFixed(2));
        }
    }

    // Show element
    function showElement(id) {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'block';
        }
    }

    // Hide element
    function hideElement(id) {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    }

    // Format number with commas
    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Department filter
    function initDepartmentFilter() {
        const deptFilter = document.getElementById('departmentFilter');
        if (!deptFilter) return;

        deptFilter.addEventListener('change', function() {
            const selectedDept = this.value.toLowerCase();
            const employeeRows = document.querySelectorAll('.employee-table tbody tr');

            employeeRows.forEach(row => {
                const dept = row.querySelector('.department-badge')?.textContent.toLowerCase() || '';

                if (selectedDept === '' || dept.includes(selectedDept)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchResults();
        });
    }

    // Status filter
    function initStatusFilter() {
        const statusFilter = document.getElementById('statusFilter');
        if (!statusFilter) return;

        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            const employeeRows = document.querySelectorAll('.employee-table tbody tr');

            employeeRows.forEach(row => {
                const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';

                if (selectedStatus === '' || status.includes(selectedStatus)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            updateSearchResults();
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        formatNumberInputs();
        initEmployeeSearch();
        initDeleteConfirmation();
        initSalaryPreview();
        initDepartmentFilter();
        initStatusFilter();
    });

    // Export functions to window object
    window.EmployeeManager = {
        formatNumber: formatNumber,
        debounce: debounce
    };

})();

