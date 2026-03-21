/**
 * PaySheetPro - Form Validation
 * Client-side form validation
 */

// ===== REGISTRATION FORM VALIDATION =====
document.addEventListener('DOMContentLoaded', function() {
    
    // Register form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
        
        // Real-time password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.setCustomValidity('Passwords do not match');
                    this.style.borderColor = '#ef4444';
                } else {
                    this.setCustomValidity('');
                    this.style.borderColor = '#10b981';
                }
            });
        }
        
        // Phone number formatting
        const phoneInput = document.getElementById('company_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    }
    
    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }
    
    // Employee form validation
    const employeeForm = document.getElementById('employeeForm');
    if (employeeForm) {
        employeeForm.addEventListener('submit', validateEmployeeForm);
        
        // NIC validation on input
        const nicInput = document.getElementById('employee_nic');
        if (nicInput) {
            nicInput.addEventListener('blur', function() {
                validateNIC(this.value, this);
            });
        }
        
        // Salary formatting
        const salaryInput = document.getElementById('basic_salary');
        if (salaryInput) {
            salaryInput.addEventListener('blur', function() {
                const value = parseFloat(this.value.replace(/,/g, ''));
                if (!isNaN(value)) {
                    this.value = formatNumber(value);
                }
            });
            
            salaryInput.addEventListener('focus', function() {
                this.value = this.value.replace(/,/g, '');
            });
        }
    }
    
    // Add input validation indicators
    addInputValidationIndicators();
});

// ===== VALIDATE REGISTRATION FORM =====
function validateRegisterForm(e) {
    e.preventDefault();
    
    const companyName = document.getElementById('company_name').value.trim();
    const companyAddress = document.getElementById('company_address').value.trim();
    const companyPhone = document.getElementById('company_phone').value.trim();
    const companyEmail = document.getElementById('company_email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    let errors = [];
    
    // Company name validation
    if (companyName.length < 3) {
        errors.push('Company name must be at least 3 characters');
        highlightError('company_name');
    }
    
    // Company address validation
    if (companyAddress.length < 10) {
        errors.push('Please enter a complete address');
        highlightError('company_address');
    }
    
    // Phone validation
    if (!validatePhone(companyPhone)) {
        errors.push('Invalid phone number. Format: 0XXXXXXXXX');
        highlightError('company_phone');
    }
    
    // Email validation
    if (!validateEmail(companyEmail)) {
        errors.push('Invalid email address');
        highlightError('company_email');
    }
    
    // Username validation
    if (username.length < 4) {
        errors.push('Username must be at least 4 characters');
        highlightError('username');
    }
    
    // Password validation
    if (password.length < 6) {
        errors.push('Password must be at least 6 characters');
        highlightError('password');
    }
    
    // Password strength check
    if (!validatePasswordStrength(password)) {
        errors.push('Password should contain letters and numbers');
        highlightError('password');
    }
    
    // Confirm password validation
    if (password !== confirmPassword) {
        errors.push('Passwords do not match');
        highlightError('confirm_password');
    }
    
    if (errors.length > 0) {
        showValidationErrors(errors);
        return false;
    }
    
    // Show loading if available
    if (typeof showLoading === 'function') {
        showLoading();
    }
    
    // Submit form
    e.target.submit();
}

// ===== VALIDATE LOGIN FORM =====
function validateLoginForm(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    let errors = [];
    
    if (username === '') {
        errors.push('Username is required');
        highlightError('username');
    }
    
    if (password === '') {
        errors.push('Password is required');
        highlightError('password');
    }
    
    if (errors.length > 0) {
        showValidationErrors(errors);
        return false;
    }
    
    if (typeof showLoading === 'function') {
        showLoading();
    }
    e.target.submit();
}

// ===== VALIDATE EMPLOYEE FORM =====
function validateEmployeeForm(e) {
    e.preventDefault();
    
    const employeeName = document.getElementById('employee_name').value.trim();
    const employeeNIC = document.getElementById('employee_nic').value.trim();
    const employeeEmail = document.getElementById('employee_email').value.trim();
    const basicSalary = document.getElementById('basic_salary').value.replace(/,/g, '');
    
    let errors = [];
    
    // Name validation
    if (employeeName.length < 3) {
        errors.push('Employee name must be at least 3 characters');
        highlightError('employee_name');
    }
    
    // NIC validation
    if (!validateNIC(employeeNIC)) {
        errors.push('Invalid NIC number');
        highlightError('employee_nic');
    }
    
    // Email validation
    if (!validateEmail(employeeEmail)) {
        errors.push('Invalid email address');
        highlightError('employee_email');
    }
    
    // Salary validation
    if (isNaN(basicSalary) || parseFloat(basicSalary) < 0) {
        errors.push('Invalid salary amount');
        highlightError('basic_salary');
    }
    
    if (errors.length > 0) {
        showValidationErrors(errors);
        return false;
    }
    
    if (typeof showLoading === 'function') {
        showLoading();
    }
    e.target.submit();
}

// ===== VALIDATION HELPER FUNCTIONS =====

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone number (Sri Lankan format)
 */
function validatePhone(phone) {
    const re = /^0[0-9]{9}$/;
    return re.test(phone);
}

/**
 * Validate NIC (Sri Lankan format)
 */
function validateNIC(nic, inputElement) {
    // Old format: 9 digits + V/X (e.g., 123456789V)
    // New format: 12 digits (e.g., 199012345678)
    const oldFormat = /^[0-9]{9}[VvXx]$/;
    const newFormat = /^[0-9]{12}$/;
    
    const isValid = oldFormat.test(nic) || newFormat.test(nic);
    
    if (inputElement) {
        if (isValid) {
            inputElement.style.borderColor = '#10b981';
        } else {
            inputElement.style.borderColor = '#ef4444';
        }
    }
    
    return isValid;
}

/**
 * Validate password strength
 */
function validatePasswordStrength(password) {
    // At least one letter and one number
    const hasLetter = /[a-zA-Z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    return hasLetter && hasNumber;
}

/**
 * Highlight error field
 */
function highlightError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.style.borderColor = '#ef4444';
        field.classList.add('error-shake');
        
        setTimeout(() => {
            field.classList.remove('error-shake');
        }, 500);
    }
}

// Add shake animation
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .error-shake {
        animation: shake 0.5s;
    }
`;
document.head.appendChild(shakeStyle);

/**
 * Show validation errors
 */
function showValidationErrors(errors) {
    // Remove existing error alerts
    const existingAlerts = document.querySelectorAll('.validation-errors');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create error alert
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error validation-errors';
    errorDiv.innerHTML = '<strong>Please fix the following errors:</strong><ul style="margin: 8px 0 0 20px;">' +
        errors.map(error => `<li>${error}</li>`).join('') +
        '</ul>';
    
    // Insert at top of form
    const form = document.querySelector('.auth-form') || document.querySelector('form');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

/**
 * Add real-time validation indicators
 */
function addInputValidationIndicators() {
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        if (input.hasAttribute('required')) {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.style.borderColor = '#ef4444';
                } else {
                    this.style.borderColor = '#10b981';
                }
            });
            
            input.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
            });
        }
    });
}

/**
 * Password strength indicator
 */
function showPasswordStrength(password, targetElement) {
    const strength = calculatePasswordStrength(password);
    const colors = {
        weak: '#ef4444',
        medium: '#f59e0b',
        strong: '#10b981'
    };
    
    const strengthText = {
        weak: 'Weak',
        medium: 'Medium',
        strong: 'Strong'
    };
    
    if (targetElement) {
        targetElement.innerHTML = `
            <div style="margin-top: 8px;">
                <div style="height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden;">
                    <div style="height: 100%; width: ${strength.percentage}%; background: ${colors[strength.level]}; transition: width 0.3s;"></div>
                </div>
                <span style="font-size: 12px; color: ${colors[strength.level]}; margin-top: 4px; display: block;">
                    Password Strength: ${strengthText[strength.level]}
                </span>
            </div>
        `;
    }
}

/**
 * Calculate password strength
 */
function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 6) score += 25;
    if (password.length >= 8) score += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 15;
    if (/[^a-zA-Z0-9]/.test(password)) score += 10;
    
    let level = 'weak';
    if (score >= 50 && score < 75) level = 'medium';
    if (score >= 75) level = 'strong';
    
    return { percentage: score, level: level };
}

// Add password strength indicator to password fields
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    if (passwordField) {
        const strengthContainer = document.createElement('div');
        strengthContainer.id = 'password-strength';
        passwordField.parentElement.appendChild(strengthContainer);
        
        passwordField.addEventListener('input', function() {
            showPasswordStrength(this.value, strengthContainer);
        });
    }
});

/**
 * Format number input
 */
function formatNumberInput(input) {
    input.addEventListener('input', function() {
        // Remove non-numeric characters
        let value = this.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        this.value = value;
    });
}

/**
 * Validate date range
 */
function validateDateRange(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (end < start) {
        return {
            valid: false,
            message: 'End date cannot be before start date'
        };
    }
    
    return {
        valid: true,
        message: 'Valid date range'
    };
}
