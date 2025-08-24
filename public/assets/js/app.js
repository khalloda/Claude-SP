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

// Add this JavaScript to your public/assets/js/app.js file or create it if it doesn't exist

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Handler
    function initializeMobileNavigation() {
        // Check if we're on mobile
        function isMobile() {
            return window.innerWidth <= 768;
        }
        
        // Get all dropdown elements
        const dropdowns = document.querySelectorAll('.dropdown');
        
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (!toggle || !menu) return;
            
            // Remove existing event listeners
            toggle.removeEventListener('click', handleDropdownClick);
            
            // Add click handler for mobile
            if (isMobile()) {
                toggle.addEventListener('click', handleDropdownClick);
                // Prevent default link behavior on mobile
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                });
            }
        });
        
        function handleDropdownClick(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown.active').forEach(activeDropdown => {
                if (activeDropdown !== dropdown) {
                    activeDropdown.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active', !isActive);
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (isMobile() && !e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown.active').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                // Remove mobile-specific classes when switching to desktop
                document.querySelectorAll('.dropdown.active').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    }
    
    // Initialize mobile navigation
    initializeMobileNavigation();
    
    // Reinitialize on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(initializeMobileNavigation, 250);
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-hide mobile navigation when scrolling down
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.innerWidth <= 768) {
                const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (currentScrollTop > lastScrollTop && currentScrollTop > 100) {
                    // Scrolling down - hide navbar
                    navbar.style.transform = 'translateY(-100%)';
                    navbar.style.transition = 'transform 0.3s ease-in-out';
                } else {
                    // Scrolling up - show navbar
                    navbar.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
            } else {
                // Reset on desktop
                navbar.style.transform = 'translateY(0)';
            }
        });
    }
    
    // Touch gestures for mobile navigation
    let startY = 0;
    let startX = 0;
    
    document.addEventListener('touchstart', function(e) {
        startY = e.touches[0].clientY;
        startX = e.touches[0].clientX;
    });
    
    document.addEventListener('touchmove', function(e) {
        if (!startY || !startX) return;
        
        const currentY = e.touches[0].clientY;
        const currentX = e.touches[0].clientX;
        const diffY = startY - currentY;
        const diffX = startX - currentX;
        
        // If horizontal swipe is more significant than vertical
        if (Math.abs(diffX) > Math.abs(diffY)) {
            // Horizontal swipe detected - could be used for menu gestures in the future
            return;
        }
        
        // Vertical swipe - let default scroll behavior handle it
    });
    
    // Fix viewport height for mobile browsers
    function setViewportHeight() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
    window.addEventListener('orientationchange', function() {
        setTimeout(setViewportHeight, 500);
    });
    
    console.log('Mobile navigation initialized');
});

// Additional utility functions for mobile
window.mobileUtils = {
    isMobile: function() {
        return window.innerWidth <= 768;
    },
    
    closeAllDropdowns: function() {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    },
    
    scrollToTop: function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
};
