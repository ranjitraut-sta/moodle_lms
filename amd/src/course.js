

(() => {
    // Existing sidebar toggle logic
    const amdcoursesidebar = document.getElementById("amdcoursesidebar");
    const toggleBtn = document.getElementById("amdcoursesidebarToggle");
    const amdcoursesidebarOpenBtn = document.getElementById("amdcoursesidebarOpenBtn");

    const updateamdcoursesidebarButtons = () => {
        if (!amdcoursesidebar || !amdcoursesidebarOpenBtn) return;
        const isCollapsed = amdcoursesidebar.classList.contains("collapsed");
        amdcoursesidebarOpenBtn.style.display = isCollapsed ? "flex" : "none";
    };
    updateamdcoursesidebarButtons();

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            if (!amdcoursesidebar) return;
            amdcoursesidebar.classList.toggle("collapsed");
            const isCollapsed = amdcoursesidebar.classList.contains("collapsed");
            toggleBtn.setAttribute("aria-expanded", !isCollapsed);

            const icon = toggleBtn.querySelector("i");
            if (icon) icon.className = isCollapsed ? "ri-arrow-right-s-line" : "ri-arrow-left-s-line";

            toggleBtn.setAttribute("aria-label", isCollapsed ? "Expand amdcoursesidebar" : "Collapse amdcoursesidebar");
            updateamdcoursesidebarButtons();
        });
    }

    if (amdcoursesidebarOpenBtn) {
        amdcoursesidebarOpenBtn.addEventListener("click", () => {
            if (!amdcoursesidebar || !toggleBtn) return;
            amdcoursesidebar.classList.remove("collapsed");
            toggleBtn.setAttribute("aria-expanded", true);

            const icon = toggleBtn.querySelector("i");
            if (icon) icon.className = "ri-arrow-left-s-line";

            toggleBtn.setAttribute("aria-label", "Collapse amdcoursesidebar");
            updateamdcoursesidebarButtons();
        });
    }

   // Accordion open/close for amd-course-chapters
document.querySelectorAll(".amd-course-chapter-header").forEach(header => {
    header.addEventListener("click", () => {
        const isExpanded = header.classList.contains("expanded");
        const list = header.nextElementSibling;
        const icon = header.querySelector("i");

        if (isExpanded) {
            header.classList.remove("expanded");
            header.classList.add("collapsed");
            if (list) list.classList.remove("expanded");
            header.setAttribute("aria-expanded", "false");
        } else {
            header.classList.remove("collapsed");
            header.classList.add("expanded");
            if (list) list.classList.add("expanded");
            header.setAttribute("aria-expanded", "true");
        }
        if (icon) icon.className = "ri-arrow-down-s-line";
    });
});


    // ask Q&A Modal Logic
    const askBtn = document.getElementById("askQuestionBtn");
    const qOverlay = document.getElementById("qOverlay");
    const qCloseBtn = document.getElementById("qCloseBtn");
    const modalBackdrop = document.getElementById("modalBackdrop");

    if (askBtn && qOverlay && modalBackdrop) {
        askBtn.addEventListener("click", () => {
            if (qOverlay.classList.contains("show")) {
                closeModal();
            } else {
                openModal();
            }
        });
    }

    if (qCloseBtn && qOverlay && modalBackdrop && askBtn) {
        qCloseBtn.addEventListener("click", closeModal);
    }

    // Close modal function
    function closeModal() {
        qOverlay.classList.remove("show");
        modalBackdrop.classList.remove("show");
        askBtn.setAttribute("aria-expanded", "false");
        document.body.classList.remove('theme-modal-open'); // enable scroll
    }

    // Open modal function
    function openModal() {
        qOverlay.classList.add("show");
        modalBackdrop.classList.add("show");
        askBtn.setAttribute("aria-expanded", "true");
        const input = document.getElementById("amd-course-qInput");
        if (input) input.focus();
        document.body.classList.add('theme-modal-open'); // disable scroll via CSS class
    }



    // Next, Previous, and Review buttons alerts
    const nextBtn = document.getElementById("nextLessonBtn");
    if (nextBtn) nextBtn.addEventListener("click", () => alert("Next lesson clicked (static demo)"));

    const prevBtn = document.getElementById("prevLessonBtn");
    if (prevBtn) prevBtn.addEventListener("click", () => alert("Previous lesson clicked (static demo)"));

    // Review modal logic
    const reviewBtn = document.getElementById("reviewBtn");
    const reviewModalElement = document.getElementById('reviewModal');
    let reviewModal;
    if (reviewBtn && reviewModalElement && window.bootstrap) {
        reviewModal = new bootstrap.Modal(reviewModalElement);
        reviewBtn.addEventListener("click", () => reviewModal.show());
    }

    const reviewForm = document.getElementById("reviewForm");
    if (reviewForm && reviewModal) {
        reviewForm.addEventListener("submit", e => {
            e.preventDefault();
            alert("Thank you for your review!");
            reviewModal.hide();
            e.target.reset();
        });
    }

    // ===== NEW: Right Canvas Toggle Logic =====
    const rightCanvas = document.getElementById("rightCanvas");
    const fabToggleBtn = document.getElementById("fabToggleBtn");
    const closeCanvasBtn = document.getElementById("closeCanvasBtn");

    if (fabToggleBtn && rightCanvas) {
        fabToggleBtn.addEventListener("click", () => {
            rightCanvas.classList.add("show");
            rightCanvas.setAttribute("aria-hidden", "false");
            fabToggleBtn.setAttribute("aria-expanded", "true");
        });
    }

    if (closeCanvasBtn && rightCanvas && fabToggleBtn) {
        closeCanvasBtn.addEventListener("click", () => {
            rightCanvas.classList.remove("show");
            rightCanvas.setAttribute("aria-hidden", "true");
            fabToggleBtn.setAttribute("aria-expanded", "false");
        });
    }

    // Optional: Close right canvas on Escape key press (skip Moodle filepicker)
    document.addEventListener("keydown", (e) => {
        if (e.target.closest('.fp-repo, .filepicker, [data-filepicker], .moodle-dialogue, .yui-panel')) return; // Skip Moodle file picker
        if (e.key === "Escape" && rightCanvas.classList.contains("show")) {
            rightCanvas.classList.remove("show");
            rightCanvas.setAttribute("aria-hidden", "true");
            fabToggleBtn.setAttribute("aria-expanded", "false");
        }
    });
})();



// video fullscreen toggle login 
// Get the video element and fullscreen button
document.querySelector('.amd-course-video-fullscreen-btn').addEventListener('click', () => {
    const container = document.querySelector('.amd-lesson-video-area');

    if (!document.fullscreenElement &&
        !document.webkitFullscreenElement &&
        !document.mozFullScreenElement &&
        !document.msFullscreenElement) {

        if (container.requestFullscreen) {
            container.requestFullscreen();
        } else if (container.mozRequestFullScreen) {
            container.mozRequestFullScreen();
        } else if (container.webkitRequestFullscreen) {
            container.webkitRequestFullscreen();
        } else if (container.msRequestFullscreen) {
            container.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
});
// fullscreen toggle end


// lms tooltip position js  
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Bootstrap tooltips for all elements with [data-bs-toggle="tooltip"]
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Blur the sidebar toggle button on click to hide tooltip immediately
    const toggleBtn = document.getElementById('amdcoursesidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            toggleBtn.blur();
        });
    }
});



// tooltip end


// progress bar scroll top sticky bg change
// document.addEventListener('DOMContentLoaded', () => {
//     const lessonProgress = document.querySelector('.amd-lesson-top-progress');

//     window.addEventListener('scroll', () => {
//         if (window.scrollY > 0) {
//             lessonProgress.classList.add('sticky-active');
//         } else {
//             lessonProgress.classList.remove('sticky-active');
//         }
//     });
// });
// progress bar video inner scroll sticky and bg color change