/**
 * PaySheetPro - Loan Management JavaScript
 */

(function() {
    'use strict';

    // Calculate loan details
    function calculateLoan() {
        const loanAmount = parseFloat(document.getElementById('loan_amount')?.value.replace(/,/g, '')) || 0;
        const monthlyInstallment = parseFloat(document.getElementById('monthly_installment')?.value.replace(/,/g, '')) || 0;

        if (loanAmount > 0 && monthlyInstallment > 0) {
            const estimatedMonths = Math.ceil(loanAmount / monthlyInstallment);

            // Update estimated months
            const estimatedMonthsInput = document.getElementById('estimated_months');
            if (estimatedMonthsInput) {
                estimatedMonthsInput.value = estimatedMonths + ' months';
            }

            // Show preview
            showLoanPreview(loanAmount, monthlyInstallment, estimatedMonths);
        } else {
            hideLoanPreview();
        }
    }

    // Show loan preview
    function showLoanPreview(loanAmount, monthlyInstallment, estimatedMonths) {
        const preview = document.getElementById('loanPreview');
        if (!preview) return;

        document.getElementById('previewAmount').textContent = formatCurrency(loanAmount);
        document.getElementById('previewInstallment').textContent = formatCurrency(monthlyInstallment);
        document.getElementById('previewMonths').textContent = estimatedMonths + ' months';

        preview.style.display = 'block';
    }

    // Hide loan preview
    function hideLoanPreview() {
        const preview = document.getElementById('loanPreview');
        if (preview) {
            preview.style.display = 'none';
        }
    }

    // Format currency
    function formatCurrency(amount) {
        return 'LKR ' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Initialize loan inputs
    function initLoanInputs() {
        const loanAmountInput = document.getElementById('loan_amount');
        const monthlyInstallmentInput = document.getElementById('monthly_installment');

        if (loanAmountInput) {
            loanAmountInput.addEventListener('input', debounce(calculateLoan, 300));
        }

        if (monthlyInstallmentInput) {
            monthlyInstallmentInput.addEventListener('input', debounce(calculateLoan, 300));
        }
    }

    // Calculate salary advance
    function calculateAdvance() {
        const employeeSelect = document.getElementById('employee_id');
        const advanceAmountInput = document.getElementById('advance_amount');
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');

        if (!employeeSelect || !advanceAmountInput) return;

        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        const salary = parseFloat(selectedOption.dataset.salary) || 0;
        const advanceAmount = parseFloat(advanceAmountInput.value.replace(/,/g, '')) || 0;

        if (salary > 0 && advanceAmount > 0) {
            const maxAdvance = salary * 0.5;
            const percentage = (advanceAmount / salary) * 100;

            // Update max advance hint
            const maxAdvanceHint = document.getElementById('max_advance_hint');
            if (maxAdvanceHint) {
                maxAdvanceHint.textContent = `Maximum 50% of basic salary (${formatCurrency(maxAdvance)})`;
            }

            // Show preview
            showAdvancePreview(advanceAmount, percentage, monthSelect, yearSelect);

            // Highlight if over 50%
            if (percentage > 50) {
                advanceAmountInput.style.borderColor = '#ef4444';
            } else {
                advanceAmountInput.style.borderColor = '#10b981';
            }
        }
    }

    // Show advance preview
    function showAdvancePreview(advanceAmount, percentage, monthSelect, yearSelect) {
        const preview = document.getElementById('advancePreview');
        if (!preview) return;

        const monthName = monthSelect ? monthSelect.options[monthSelect.selectedIndex].text : '';
        const year = yearSelect ? yearSelect.value : '';

        document.getElementById('previewAmount').textContent = formatCurrency(advanceAmount);
        document.getElementById('previewDate').textContent = monthName + ' ' + year;
        
        const percentageElement = document.getElementById('previewPercentage');
        percentageElement.textContent = percentage.toFixed(1) + '%';
        
        if (percentage > 50) {
            percentageElement.style.color = '#ef4444';
        } else {
            percentageElement.style.color = '#10b981';
        }

        preview.style.display = 'block';
    }

    // Update salary info when employee is selected
    function updateSalaryInfo() {
        const employeeSelect = document.getElementById('employee_id');
        if (!employeeSelect) return;

        employeeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const salary = parseFloat(selectedOption.dataset.salary) || 0;

            if (salary > 0) {
                const maxAdvance = salary * 0.5;
                const employeeSalaryInput = document.getElementById('employee_salary');
                const maxAdvanceHint = document.getElementById('max_advance_hint');

                if (employeeSalaryInput) {
                    employeeSalaryInput.value = formatCurrency(salary);
                }

                if (maxAdvanceHint) {
                    maxAdvanceHint.textContent = `Maximum 50% of basic salary (${formatCurrency(maxAdvance)})`;
                }

                calculateAdvance();
            }
        });
    }

    // Initialize advance inputs
    function initAdvanceInputs() {
        const advanceAmountInput = document.getElementById('advance_amount');
        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');

        if (advanceAmountInput) {
            advanceAmountInput.addEventListener('input', debounce(calculateAdvance, 300));
        }

        if (monthSelect) {
            monthSelect.addEventListener('change', calculateAdvance);
        }

        if (yearSelect) {
            yearSelect.addEventListener('change', calculateAdvance);
        }

        updateSalaryInfo();
    }

    // Filter loans by status
    function initLoanFilters() {
        const statusFilter = document.getElementById('loanStatusFilter');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const status = this.value.toLowerCase();
                const loanRows = document.querySelectorAll('.loan-table tbody tr, .loan-card');

                loanRows.forEach(row => {
                    const rowStatus = row.dataset.status?.toLowerCase() || '';

                    if (status === '' || rowStatus === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
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

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initLoanInputs();
        initAdvanceInputs();
        initLoanFilters();
    });

    // Export functions to window object
    window.LoanManager = {
        calculateLoan: calculateLoan,
        calculateAdvance: calculateAdvance,
        formatCurrency: formatCurrency
    };

})();