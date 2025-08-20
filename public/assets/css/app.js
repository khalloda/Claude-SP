// Spare Parts Application JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Language switcher functionality
    const langLinks = document.querySelectorAll('.lang-link');
    langLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('data-lang');
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        });
    });

    // Form validation helpers
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    };

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Initialize tooltips and other interactive elements
    initializeInteractiveElements();
});

function initializeInteractiveElements() {
    // Add loading states to buttons
    const submitButtons = document.querySelectorAll('button[type="submit"], input[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = this.getAttribute('data-loading-text') || 'Loading...';
            
            setTimeout(() => {
                this.disabled = false;
                this.textContent = originalText;
            }, 2000);
        });
    });
}

// Utility functions
window.SPApp = {
    // Show success message
    showSuccess: function(message) {
        this.showAlert(message, 'success');
    },
    
    // Show error message
    showError: function(message) {
        this.showAlert(message, 'danger');
    },
    
    // Show alert
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.main-content .container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }
    },
    
    // Format currency
    formatCurrency: function(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Confirm action
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
};
