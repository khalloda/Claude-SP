// Spare Parts Management System - Safe JavaScript (No form interference)

document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Spare Parts Management System loaded');
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Language switcher (safe)
    const langLinks = document.querySelectorAll('.lang-link');
    langLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('🌐 Language link clicked:', this.getAttribute('data-lang'));
            // Let the link work naturally
        });
    });
    
    // *** REMOVED: Submit button interference that was preventing form submission ***
    // The original code was adding event listeners that might prevent form submission
    
    console.log('✅ App.js loaded successfully - no form interference');
});