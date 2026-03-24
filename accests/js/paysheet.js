/**
 * PaySheetPro - Paysheet Management JavaScript
 */

(function() {
    'use strict';

    // Calculate paysheet preview
    function calculatePaysheetPreview() {
        const basicSalary = parseFloat(document.getElementById('basic_salary')?.value.replace(/,/g, '')) || 0;
        const otHours = parseFloat(document.getElementById('ot_hours')?.value) || 0;
        const travelAllowance = parseFloat(document.getElementById('travel_allowance')?.value.replace(/,/g, '')) || 0;
        const foodAllowance = parseFloat(document.getElementById('food_allowance')?.value.replace(/,/g, '')) || 0;

        if (basicSalary > 0) {
            // Calculate OT
            const hourlyRate = basicSalary / 240;
            const otRate = hourlyRate * 1.5;
            const otPayment = otHours * otRate;

            // Total Earnings
            const totalEarnings = basicSalary + otPayment + travelAllowance + foodAllowance;

            // Deductions
            const epf = basicSalary * 0.12;
            const etf = basicSalary * 0.03;
            const apit = calculateAPIT(basicSalary);

            const totalDeductions = epf + etf + apit;

            // Net Salary
            const netSalary = totalEarnings - totalDeductions;

            // Update preview
            updatePaysheetPreview({
                basicSalary,
                otPayment,
                travelAllowance,
                foodAllowance,
                totalEarnings,
                epf,
                etf,
                apit,
                totalDeductions,
                netSalary
            });
        }
    }

    // Update paysheet preview display
    function updatePaysheetPreview(data) {
        const previewSection = document.getElementById('paysheetPreview');
        if (!previewSection) return;

        previewSection.innerHTML = `
            <h4 class="preview-title">Paysheet Preview</h4>
            <div class="preview-grid">
                <div class="preview-section earnings">
                    <h5>Earnings</h5>
                    <div class="preview-item">
                        <span>Basic Salary:</span>
                        <span>${formatCurrency(data.basicSalary)}</span>
                    </div>
                    <div class="preview-item">
                        <span>OT Payment:</span>
                        <span>${formatCurrency(data.otPayment)}</span>
                    </div>
                    <div class="preview-item">
                        <span>Travel Allowance:</span>
                        <span>${formatCurrency(data.travelAllowance)}</span>
                    </div>
                    <div class="preview-item">
                        <span>Food Allowance:</span>
                        <span>${formatCurrency(data.foodAllowance)}</span>
                    </div>
                    <div class="preview-total">
                        <span>Total Earnings:</span>
                        <span>${formatCurrency(data.totalEarnings)}</span>
                    </div>
                </div>
                <div class="preview-section deductions">
                    <h5>Deductions</h5>
                    <div class="preview-item">
                        <span>EPF (12%):</span>
                        <span>${formatCurrency(data.epf)}</span>
                    </div>
                    <div class="preview-item">
                        <span>ETF (3%):</span>
                        <span>${formatCurrency(data.etf)}</span>
                    </div>
                    <div class="preview-item">
                        <span>APIT Tax:</span>
                        <span>${formatCurrency(data.apit)}</span>
                    </div>
                    <div class="preview-total">
                        <span>Total Deductions:</span>
                        <span>${formatCurrency(data.totalDeductions)}</span>
                    </div>
                </div>
            </div>
            <div class="preview-net">
                <span>Net Salary:</span>
                <span>${formatCurrency(data.netSalary)}</span>
            </div>
        `;

        previewSection.style.display = 'block';
    }

    // Calculate APIT Tax
    function calculateAPIT(monthlyIncome) {
        if (monthlyIncome <= 100000) {
            return 0;
        }

        let taxable = monthlyIncome - 100000;
        let tax = 0;

        const slabs = [
            { limit: 41667, rate: 0.06 },
            { limit: 41667, rate: 0.12 },
            { limit: 41667, rate: 0.18 },
            { limit: 41667, rate: 0.24 },
            { limit: 41667, rate: 0.30 },
            { limit: Infinity, rate: 0.36 }
        ];

        for (let slab of slabs) {
            if (taxable > 0) {
                const taxableInSlab = Math.min(taxable, slab.limit);
                tax += taxableInSlab * slab.rate;
                taxable -= taxableInSlab;
            } else {
                break;
            }
        }

        return tax;
    }

    // Format currency
    function formatCurrency(amount) {
        return 'LKR ' + parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Initialize paysheet inputs
    function initPaysheetInputs() {
        const inputs = [
            'basic_salary',
            'ot_hours',
            'travel_allowance',
            'food_allowance'
        ];

        inputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', debounce(calculatePaysheetPreview, 300));
            }
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

    // Filter paysheets
    function initPaysheetFilters() {
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        const employeeFilter = document.getElementById('employeeFilter');

        if (monthFilter) monthFilter.addEventListener('change', filterPaysheets);
        if (yearFilter) yearFilter.addEventListener('change', filterPaysheets);
        if (employeeFilter) employeeFilter.addEventListener('change', filterPaysheets);
    }

    // Filter paysheets function
    function filterPaysheets() {
        const month = document.getElementById('monthFilter')?.value || '';
        const year = document.getElementById('yearFilter')?.value || '';
        const employee = document.getElementById('employeeFilter')?.value || '';

        const paysheetRows = document.querySelectorAll('.paysheet-table tbody tr, .employee-paysheet-card');

        paysheetRows.forEach(row => {
            const rowMonth = row.dataset.month || '';
            const rowYear = row.dataset.year || '';
            const rowEmployee = row.dataset.employee || '';

            const monthMatch = month === '' || rowMonth === month;
            const yearMatch = year === '' || rowYear === year;
            const employeeMatch = employee === '' || rowEmployee === employee;

            if (monthMatch && yearMatch && employeeMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Print paysheet
    function printPaysheet() {
        window.print();
    }

    // Download paysheet as PDF
    function downloadPaysheetPDF(paysheetId) {
        if (!paysheetId) {
            alert('Paysheet ID is required');
            return;
        }

        window.open(`generate_pdf.php?id=${paysheetId}`, '_blank');
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initPaysheetInputs();
        initPaysheetFilters();
    });

    // Export functions to window object
    window.PaysheetManager = {
        calculatePaysheetPreview: calculatePaysheetPreview,
        calculateAPIT: calculateAPIT,
        formatCurrency: formatCurrency,
        printPaysheet: printPaysheet,
        downloadPaysheetPDF: downloadPaysheetPDF
    };

})();