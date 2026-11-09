/* ====================================================================
   PestaGo — App JavaScript
   ==================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ===== Navbar Scroll Effect =====
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });
    }

    // ===== Mobile Nav Toggle =====
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-open');
            const icon = navToggle.querySelector('i');
            icon.classList.toggle('bi-list');
            icon.classList.toggle('bi-x-lg');
        });
    }

    // ===== Nav Dropdowns (User, Notification, etc.) =====
    document.querySelectorAll('.nav-dropdown').forEach(wrap => {
        const trigger = wrap.querySelector('#userDropdownBtn, #notifBellBtn, .nav-icon-btn, .emox-signin-btn');
        if (trigger) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                document.querySelectorAll('.nav-dropdown.open').forEach(other => {
                    if (other !== wrap) other.classList.remove('open');
                });
                wrap.classList.toggle('open');
            });
        }
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.nav-dropdown.open').forEach(wrap => {
            wrap.classList.remove('open');
        });
    });

    // ===== Auto-dismiss alerts after 5s =====
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // ===== Format currency helper =====
    window.formatRupiah = function (number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    };

    // ===== Lazy intersection observer for animations =====
    const animateOnScroll = document.querySelectorAll('[data-animate]');
    if (animateOnScroll.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease both';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateOnScroll.forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    }

    // ===== Initialize Leaflet Maps =====
    window.initMap = function(elementId, lat, lng, zoom, popupText) {
        if (typeof L === 'undefined') return null;
        const map = L.map(elementId).setView([lat, lng], zoom || 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        if (popupText) {
            L.marker([lat, lng]).addTo(map).bindPopup(popupText).openPopup();
        }

        return map;
    };

    // ===== Initialize map picker (for forms with lat/lng) =====
    window.initMapPicker = function(elementId, latInput, lngInput, defaultLat, defaultLng) {
        if (typeof L === 'undefined') return null;

        const lat = defaultLat || -6.2088;
        const lng = defaultLng || 106.8456;

        const map = L.map(elementId).setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        let marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            document.getElementById(latInput).value = pos.lat.toFixed(6);
            document.getElementById(lngInput).value = pos.lng.toFixed(6);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            document.getElementById(latInput).value = e.latlng.lat.toFixed(6);
            document.getElementById(lngInput).value = e.latlng.lng.toFixed(6);
        });

        return { map, marker };
    };
});
