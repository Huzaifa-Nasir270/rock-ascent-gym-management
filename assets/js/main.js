/**
 * Main JavaScript File
 * Common functions and utilities
 */

// Show success message
function showSuccess(message) {
    showAlert(message, 'success');
}

// Show error message
function showError(message) {
    showAlert(message, 'danger');
}

// Show alert message
function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`;
    
    // Insert at top of container
    const container = document.querySelector('.main-content') || document.querySelector('body');
    const firstElement = container.firstChild;
    const alertElement = document.createElement('div');
    alertElement.innerHTML = alertHTML;
    container.insertBefore(alertElement.firstChild, firstElement);
}

// Confirm delete action
function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

// Format currency
function formatCurrency(value) {
    return '$' + parseFloat(value).toFixed(2);
}

// Format date
function formatDate(date) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
}

// Validate email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate phone
function isValidPhone(phone) {
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(phone);
}

// Toggle loading state on buttons
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.getAttribute('data-original-text') || 'Submit';
    }
}

// Clear form
function clearForm(formId) {
    document.getElementById(formId).reset();
}

// Get query parameter
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Initialize tooltips
function initTooltips() {
    document.querySelectorAll('[data-toggle="tooltip"]').forEach(element => {
        new bootstrap.Tooltip(element);
    });
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('Copied to clipboard!');
    });
}

// Search filter
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    input.addEventListener('keyup', function() {
        const searchTerm = input.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });

    // Premium UI: Animate Counters
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const originalText = counter.innerText.trim();
            const isCurrency = originalText.startsWith('$');
            const target = parseInt(originalText.replace(/[^0-9]/g, ''));
            if (isNaN(target)) return;
            
            let count = 0;
            const speed = Math.max(1, 2000 / (target > 100 ? 100 : target)); // Cap steps for large numbers
            
            const updateCount = () => {
                if (count < target) {
                    const increment = Math.ceil(target / 50);
                    count = Math.min(target, count + increment);
                    counter.innerText = (isCurrency ? '$' : '') + count.toLocaleString();
                    setTimeout(updateCount, 40);
                } else {
                    counter.innerText = (isCurrency ? '$' : '') + target.toLocaleString();
                }
            };
            updateCount();
        });
    };

    // Premium UI: Scroll Reveal
    const revealOnScroll = () => {
        const reveals = document.querySelectorAll('.card, .stat-card, .fade-in-up');
        const windowHeight = window.innerHeight;
        reveals.forEach(reveal => {
            const revealTop = reveal.getBoundingClientRect().top;
            if (revealTop < windowHeight - 50) {
                reveal.style.opacity = '1';
                reveal.style.transform = 'translateY(0)';
            }
        });
    };

    // Initial run
    setTimeout(animateCounters, 500);
    
    // Set initial styles for reveal
    document.querySelectorAll('.card, .stat-card, .fade-in-up').forEach(el => {
        if (!el.classList.contains('no-animate')) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.8s cubic-bezier(0.23, 1, 0.32, 1)';
        }
    });

    window.addEventListener('scroll', revealOnScroll);
    setTimeout(revealOnScroll, 100); // Trigger initial reveal
});
