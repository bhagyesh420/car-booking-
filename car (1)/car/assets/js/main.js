/**
 * DriveRent - Executive Luxury Front-end Animation & Logic Engine v3.0
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Preloader Handler - Smooth Fade Out
    const preloader = document.getElementById('preloader');
    if (preloader) {
        const dismissPreloader = () => {
            preloader.classList.add('fade-out');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        };
        if (document.readyState === 'complete') {
            dismissPreloader();
        } else {
            window.addEventListener('load', dismissPreloader, { once: true });
            setTimeout(dismissPreloader, 600);
        }
    }

    // 2. Navbar Scroll Shift & Glassmorphism Blur
    const mainNavbar = document.getElementById('mainNavbar');
    if (mainNavbar) {
        let lastScrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                mainNavbar.classList.add('scrolled');
            } else {
                mainNavbar.classList.remove('scrolled');
            }
            lastScrollY = window.scrollY;
        }, { passive: true });
    }

    // 3. Scroll Reveal Observer with Stagger Support
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    if (revealElements.length > 0) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('active');
                    }, index * 60);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealElements.forEach(el => revealObserver.observe(el));
    }

    // 4. Animated Counters (High Performance requestAnimationFrame Ticker)
    const counters = document.querySelectorAll('.counter-val, [data-target]');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const targetStr = counter.getAttribute('data-target') || counter.innerText;
                    const target = parseFloat(targetStr.replace(/[^0-9.]/g, ''));
                    if (isNaN(target)) return;

                    const duration = 2000; // ms
                    const startTime = performance.now();

                    const updateCount = (currentTime) => {
                        const elapsedTime = currentTime - startTime;
                        const progress = Math.min(elapsedTime / duration, 1);
                        // Ease-out expo curve for smooth speed deceleration
                        const easeProgress = 1 - Math.pow(2, -10 * progress);
                        const currentCount = Math.floor(easeProgress * target);

                        counter.innerText = currentCount.toLocaleString();

                        if (progress < 1) {
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target.toLocaleString() + (targetStr.includes('+') ? '+' : '');
                        }
                    };

                    requestAnimationFrame(updateCount);
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.3 });

        counters.forEach(c => counterObserver.observe(c));
    }

    // 5. Interactive 3D Card Tilt Effect on Mouse Move
    const tiltCards = document.querySelectorAll('.car-card, .category-card, .feature-box, .step-card, .top-filter-card');
    tiltCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -5;
            const rotateY = ((x - centerX) / centerX) * 5;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
        });
    });

    // 6. Interactive Ripple Effect on Buttons
    const rippleButtons = document.querySelectorAll('.btn-gradient, .btn-primary, .btn-accent, .btn-pill');
    rippleButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const circle = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height);
            const radius = diameter / 2;

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - radius}px`;
            circle.style.top = `${e.clientY - rect.top - radius}px`;
            circle.classList.add('btn-ripple');

            const existingRipple = this.querySelector('.btn-ripple');
            if (existingRipple) existingRipple.remove();

            this.appendChild(circle);

            setTimeout(() => {
                circle.remove();
            }, 600);
        });
    });

    // 7. Back to Top Smooth Scroll Button
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 8. Robust Navbar Dropdown Click Handler
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-bs-toggle="dropdown"]');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            const parentDropdown = toggleBtn.closest('.dropdown');
            if (parentDropdown) {
                const dropdownMenu = parentDropdown.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    const isShown = dropdownMenu.classList.contains('show');
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(b => b.classList.remove('show'));

                    if (!isShown) {
                        dropdownMenu.classList.add('show');
                        toggleBtn.classList.add('show');
                        toggleBtn.setAttribute('aria-expanded', 'true');
                    } else {
                        toggleBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        } else if (!e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(b => b.classList.remove('show'));
        }
    });
});

/**
 * Global Toast Helper Component
 */
function showToast(title, message, type = 'info') {
    const toastEl = document.getElementById('appToast');
    if (toastEl) {
        const toastTitle = document.getElementById('toastTitle');
        const toastBody = document.getElementById('toastBody');
        const toastIcon = document.getElementById('toastIcon');

        if (toastTitle) toastTitle.innerText = title;
        if (toastBody) toastBody.innerText = message;

        if (toastIcon) {
            toastIcon.className = 'fas me-2 ' + (
                type === 'success' ? 'fa-check-circle text-success' :
                type === 'danger' ? 'fa-exclamation-triangle text-danger' :
                type === 'warning' ? 'fa-exclamation-circle text-warning' : 'fa-info-circle text-info'
            );
        }

        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
    }
}
