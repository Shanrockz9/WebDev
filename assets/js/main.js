// ==========================================================
// S PARFUM - CLIENT INTERACTIVITY (assets/js/main.js)
// Purpose: Handles alert auto-dismiss, mobile hamburger menu,
// user dropdowns, checkout payment radios, and hero carousel.
// ==========================================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });

    // 2. Mobile Menu Toggle
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.querySelector('.nav-links');
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            if (navLinks.style.display === 'flex') {
                navLinks.style.display = 'none';
            } else {
                navLinks.style.display = 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '75px';
                navLinks.style.left = '0';
                navLinks.style.width = '100%';
                navLinks.style.background = '#fbf8f5';
                navLinks.style.padding = '20px';
                navLinks.style.borderBottom = '1px solid #d8a06f';
            }
        });
    }

    // 3. User Dropdown Toggle for Mobile / Touch
    const userDropdownContainer = document.querySelector('.user-dropdown-container');
    const userBtn = document.getElementById('userDropdownBtn');
    const userMenu = document.querySelector('.user-dropdown-menu');
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('show');
        });
        document.addEventListener('click', () => {
            userMenu.classList.remove('show');
        });
    }

    // 4. Payment Option Selector (Checkout)
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        option.addEventListener('click', () => {
            paymentOptions.forEach(o => o.classList.remove('active'));
            option.classList.add('active');
            const radio = option.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // 5. Hero Carousel simulation (dots & arrows)
    const heroDots = document.querySelectorAll('.hero-dot');
    const heroLeftArrow = document.getElementById('heroPrevBtn');
    const heroRightArrow = document.getElementById('heroNextBtn');
    let currentHeroSlide = 0;

    function updateHeroSlide(index) {
        if (!heroDots.length) return;
        currentHeroSlide = (index + heroDots.length) % heroDots.length;
        heroDots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentHeroSlide);
        });
    }

    if (heroDots.length) {
        heroDots.forEach((dot, i) => {
            dot.addEventListener('click', () => updateHeroSlide(i));
        });
    }
    if (heroLeftArrow) {
        heroLeftArrow.addEventListener('click', () => updateHeroSlide(currentHeroSlide - 1));
    }
    if (heroRightArrow) {
        heroRightArrow.addEventListener('click', () => updateHeroSlide(currentHeroSlide + 1));
    }
});

// Quantity Modifier in Cart
function adjustQuantity(inputName, delta, maxStock) {
    const input = document.getElementById(inputName);
    if (!input) return;
    let currentVal = parseInt(input.value, 10) || 1;
    let newVal = currentVal + delta;
    if (newVal < 1) newVal = 1;
    if (maxStock && newVal > maxStock) {
        alert('Cannot exceed available stock of ' + maxStock + ' units.');
        newVal = maxStock;
    }
    input.value = newVal;
}

