document.addEventListener('DOMContentLoaded', function () {

    // --- Navbar scroll behavior ---
    const mainHeader = document.getElementById('mainHeader');
    if (mainHeader) {
        const mainNavbar = mainHeader.querySelector('.amd-lms-navbar');
        let lastScrollTop = 0;

        window.addEventListener('scroll', function () {
            if (mainNavbar && mainNavbar.classList.contains('search-active')) return;
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > 10) {
                mainHeader.classList.add('header-bg-active');
            } else {
                mainHeader.classList.remove('header-bg-active');
            }
            if (scrollTop > lastScrollTop && scrollTop > mainHeader.offsetHeight) {
                mainHeader.classList.add('header-hidden');
            } else {
                mainHeader.classList.remove('header-hidden');
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    }

    // --- Preloader + Carousel ---
    const preloader = document.getElementById('preloader');
    const mainCarouselElement = document.getElementById('heroBgCarousel');
    if (preloader && mainCarouselElement && typeof bootstrap !== 'undefined') {
        const mainCarousel = new bootstrap.Carousel(mainCarouselElement, { interval: 8000, ride: false });
        setTimeout(function () {
            preloader.classList.add('hidden');
            mainCarousel.cycle();
        }, 2500);

        // Video Modal
        const videoModal = document.getElementById('videoModal');
        const videoIframe = document.getElementById('videoIframe');
        if (videoModal && videoIframe) {
            const originalVideoSrc = videoIframe.src;
            videoModal.addEventListener('shown.bs.modal', function () {
                videoIframe.src = originalVideoSrc + '?autoplay=1&mute=0';
                mainCarousel.pause();
            });
            videoModal.addEventListener('hidden.bs.modal', function () {
                videoIframe.src = originalVideoSrc;
                mainCarousel.cycle();
            });
        }
    }

    // --- Mobile submenu ---
    if (window.innerWidth < 992) {
        document.querySelectorAll('.amd-lms-dropdown-submenu > a.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                this.parentElement.classList.toggle('open');
            });
        });
    }

    // --- Course list view switcher ---
    const viewSwitcher = document.getElementById('amd-lms-course-list-view-switcher');
    const courseGrid = document.getElementById('amd-lms-course-list-grid');
    if (viewSwitcher && courseGrid) {
        viewSwitcher.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                courseGrid.style.setProperty('--amd-lms-grid-columns', button.dataset.columns);
                viewSwitcher.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    }

    // --- Course detail accordion ---
    const allDetails = document.querySelectorAll('.amd-lms-course-detail-accordion__item');
    allDetails.forEach(details => {
        details.addEventListener('toggle', () => {
            if (details.open) {
                allDetails.forEach(other => { if (other !== details) other.open = false; });
            }
        });
    });

});
