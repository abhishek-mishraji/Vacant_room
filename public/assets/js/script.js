// Enhanced script.js for Find Vacant Room Application

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    
    if (menuToggle && mobileMenuOverlay) {
        menuToggle.addEventListener('click', function() {
            document.body.classList.toggle('menu-open');
            mobileMenuOverlay.classList.toggle('active');
        });
        
        // Close mobile menu when clicking on a link
        const mobileMenuLinks = document.querySelectorAll('.mobile-menu a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                document.body.classList.remove('menu-open');
                mobileMenuOverlay.classList.remove('active');
            });
        });
        
        // Close mobile menu when clicking outside
        mobileMenuOverlay.addEventListener('click', function(e) {
            if (e.target === mobileMenuOverlay) {
                document.body.classList.remove('menu-open');
                mobileMenuOverlay.classList.remove('active');
            }
        });
    }
    
    // Building page enhancements
    if (document.querySelector('.building-page')) {
        // Add entrance animation for room cards
        const roomCards = document.querySelectorAll('.room-card');
        roomCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 + (index * 30)); // Staggered animation
            
            // Add hover sound effect (optional)
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-4px) scale(1.03)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Floor section animations
        const floorSections = document.querySelectorAll('.floor-section');
        floorSections.forEach((section, index) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }, 300 + (index * 100)); // Staggered animation for floor sections
        });
        
        // Quick filter by status (if applicable)
        const legendItems = document.querySelectorAll('.legend-item');
        legendItems.forEach(item => {
            item.style.cursor = 'pointer';
            
            item.addEventListener('click', () => {
                const status = item.textContent.trim().toLowerCase();
                
                // Toggle active class for visual feedback
                item.classList.toggle('active');
                
                // Filter rooms based on status
                roomCards.forEach(card => {
                    if (item.classList.contains('active')) {
                        // Show only rooms with matching status
                        if (!card.classList.contains(status)) {
                            card.style.opacity = '0.3';
                            card.style.transform = 'scale(0.95)';
                        } else {
                            card.style.opacity = '1';
                            card.style.transform = 'scale(1)';
                        }
                    } else {
                        // Reset all to visible
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }
                });
            });
        });
    }

    // Animate buildings on homepage with staggered entrance
    const buildings = document.querySelectorAll('.building-card');
    if (buildings.length) {
        buildings.forEach((building, index) => {
            building.style.opacity = '0';
            building.style.transform = 'translateY(20px)';
            setTimeout(() => {
                building.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                building.style.opacity = '1';
                building.style.transform = 'translateY(0)';
            }, 100 + (index * 50)); // Staggered animation
        });
    }

    // Add hover effects to buildings
    buildings.forEach(building => {
        building.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.04)';
            this.style.boxShadow = '0 12px 36px rgba(0,123,255,0.18)';
            this.style.borderColor = '#007bff';
            this.style.background = 'linear-gradient(135deg, #e3f0ff 60%, #f8faff 100%)';
        });
        
        building.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
            this.style.borderColor = '';
            this.style.background = '';
        });
    });

    // Animate info cards on homepage
    const infoCards = document.querySelectorAll('.info-card');
    if (infoCards.length) {
        infoCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 300 + (index * 100)); // Staggered animation after buildings
        });
    }

    // Add subtle pulse animation to hero section
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        setTimeout(() => {
            heroSection.classList.add('animated');
        }, 500);
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Account for header
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add confirmation for status changes in admin panel
    document.addEventListener('click', function(e) {
        if (e.target.matches('form.inline-update button')) {
            if (!confirm('Are you sure you want to change the room status?')) {
                e.preventDefault();
            }
        }
    });

    // Add active state to current navigation item
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a, .mobile-menu a');
    navLinks.forEach(link => {
        const linkPath = new URL(link.href, window.location.origin).pathname;
        if (currentPath === linkPath || 
            (currentPath.includes(linkPath) && linkPath !== '/index.php' && linkPath !== '/')) {
            link.classList.add('active');
        }
    });

    // Add fade-in animation to rooms in building view
    const rooms = document.querySelectorAll('.room');
    if (rooms.length) {
        rooms.forEach((room, index) => {
            room.style.opacity = '0';
            room.style.transform = 'scale(0.95)';
            setTimeout(() => {
                room.style.transition = 'all 0.3s ease';
                room.style.opacity = '1';
                room.style.transform = 'scale(1)';
            }, 50 + (index * 20)); // Quicker staggered animation
        });
    }
    
    // Add animation to header and footer elements
    const logoContainer = document.querySelector('.logo-container');
    const footerBranding = document.querySelector('.footer-branding');
    
    if (logoContainer) {
        logoContainer.style.opacity = '0';
        logoContainer.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            logoContainer.style.transition = 'all 0.5s ease';
            logoContainer.style.opacity = '1';
            logoContainer.style.transform = 'translateY(0)';
        }, 100);
    }
    
    if (footerBranding) {
        footerBranding.style.opacity = '0';
        footerBranding.style.transform = 'translateY(10px)';
        setTimeout(() => {
            footerBranding.style.transition = 'all 0.5s ease';
            footerBranding.style.opacity = '1';
            footerBranding.style.transform = 'translateY(0)';
        }, 300);
    }
});
