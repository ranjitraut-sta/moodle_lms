(() => {
  document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // 1. SIDEBAR TOGGLE LOGIC (Open / Close)
    // ==========================================
    const sidebar = document.getElementById("amdcoursesidebar");
    const toggleBtn = document.getElementById("amdcoursesidebarToggle");
    const openBtn = document.getElementById("amdcoursesidebarOpenBtn");

    const updateSidebarButtons = () => {
      if (!sidebar || !openBtn) return;
      const isCollapsed = sidebar.classList.contains("collapsed");
      openBtn.style.display = isCollapsed ? "flex" : "none";
    };

    updateSidebarButtons();

    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => {
        if (!sidebar) return;
        sidebar.classList.toggle("collapsed");
        const isCollapsed = sidebar.classList.contains("collapsed");
        
        toggleBtn.setAttribute("aria-expanded", !isCollapsed);
        toggleBtn.setAttribute("aria-label", isCollapsed ? "Expand sidebar" : "Collapse sidebar");
        
        const icon = toggleBtn.querySelector("i");
        if (icon) {
          icon.className = isCollapsed ? "ri-arrow-right-s-line" : "ri-arrow-left-s-line";
        }
        updateSidebarButtons();
      });
    }

    if (openBtn) {
      openBtn.addEventListener("click", () => {
        if (!sidebar) return;
        sidebar.classList.remove("collapsed");
        openBtn.classList.remove("show");
        
        if (toggleBtn) {
          toggleBtn.setAttribute("aria-expanded", "true");
          toggleBtn.setAttribute("aria-label", "Collapse sidebar");
          const icon = toggleBtn.querySelector("i");
          if (icon) icon.className = "ri-arrow-left-s-line";
        }
        updateSidebarButtons();
      });
    }

    // ==========================================
    // 2. CHAPTER/SECTION TOGGLE (Accordion Logic)
    // ==========================================
    const chapterHeaders = document.querySelectorAll('.amd-course-chapter-header');

    chapterHeaders.forEach(header => {
      header.addEventListener('click', function() {
        const lessonsList = this.nextElementSibling; // .amd-course-lessons-list
        if (!lessonsList) return;

        // क्लिक गर्दा अरु सबै खुला सेक्सनहरू बन्द गर्ने (Accordion Effect)
        document.querySelectorAll('.amd-course-lessons-list').forEach(list => {
          if (list !== lessonsList) {
            list.classList.remove('expanded');
            if (list.previousElementSibling) {
              list.previousElementSibling.classList.remove('expanded');
              list.previousElementSibling.setAttribute('aria-expanded', 'false');
            }
          }
        });

        // हालको सेक्सन टोगल गर्ने
        const isExpanded = lessonsList.classList.toggle('expanded');
        this.classList.toggle('expanded');
        this.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
      });
    });

    // ==========================================
    // 3. ACTIVE ELEMENT FOCUS, AUTO-OPEN & SCROLL LOGIC (सच्चाइएको भाग)
    // ==========================================
    const sidebarInner = document.querySelector(".amdcoursesidebar-inner");
    const activeLesson = document.querySelector(".lesson.active");

    // पहिले सबै च्याप्टर बन्द गर्ने (डिफल्टमा CSS बाट खुला छ भने पनि बन्द होस्)
    document.querySelectorAll('.amd-course-lessons-list').forEach(list => {
      list.classList.remove('expanded');
      if (list.previousElementSibling) {
        list.previousElementSibling.classList.remove('expanded');
        list.previousElementSibling.setAttribute('aria-expanded', 'false');
      }
    });

    if (sidebarInner && activeLesson) {
      const chapter = activeLesson.closest(".amd-course-chapter");
      const header = chapter?.querySelector(".amd-course-chapter-header");
      const lessonsList = header?.nextElementSibling; // .amd-course-lessons-list

      // एक्टिभ लेसन भएको च्याप्टरलाई मात्र अटोमेटिक खोल्ने
      if (header && lessonsList) {
        header.classList.add('expanded');
        header.setAttribute('aria-expanded', 'true');
        lessonsList.classList.add('expanded');
      }

      // एक्टिभ च्याप्टरको हेडरमा स्क्रोल गर्ने
      if (header) {
        const sidebarTop = sidebarInner.getBoundingClientRect().top;
        const headerTop = header.getBoundingClientRect().top;

        sidebarInner.scrollTo({
          top: sidebarInner.scrollTop + (headerTop - sidebarTop) - 20,
          behavior: "smooth",
        });
      }

      // एक्टिभ लेसनलाई सेन्टरमा स्क्रोल गर्ने
      setTimeout(() => {
        activeLesson.scrollIntoView({
          behavior: "smooth",
          block: "center",
          inline: "nearest",
        });
      }, 300);
    }

    // ==========================================
    // 4. NAVIGATION BUTTONS (Next, Prev, Review)
    // ==========================================
    const nextBtn = document.getElementById("nextLessonBtn");
    if (nextBtn) {
      nextBtn.addEventListener("click", () => alert("Next lesson clicked (static demo)"));
    }

    const prevBtn = document.getElementById("prevLessonBtn");
    if (prevBtn) {
      prevBtn.addEventListener("click", () => alert("Previous lesson clicked (static demo)"));
    }

    const reviewBtn = document.getElementById("reviewBtn");
    const reviewModalElement = document.getElementById("reviewModal");
    let reviewModal;
    
    if (reviewBtn && reviewModalElement && window.bootstrap) {
      reviewModal = new bootstrap.Modal(reviewModalElement);
      reviewBtn.addEventListener("click", () => reviewModal.show());
    }

    const reviewForm = document.getElementById("reviewForm");
    if (reviewForm && reviewModal) {
      reviewForm.addEventListener("submit", (e) => {
        e.preventDefault();
        alert("Thank you for your review!");
        reviewModal.hide();
        e.target.reset();
      });
    }

    // ==========================================
    // 5. RIGHT CANVAS TOGGLE LOGIC
    // ==========================================
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

    const closeRightCanvas = () => {
      if (!rightCanvas) return;
      rightCanvas.classList.remove("show");
      rightCanvas.setAttribute("aria-hidden", "true");
      if (fabToggleBtn) fabToggleBtn.setAttribute("aria-expanded", "false");
    };

    if (closeCanvasBtn) {
      closeCanvasBtn.addEventListener("click", closeRightCanvas);
    }

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && rightCanvas && rightCanvas.classList.contains("show")) {
        closeRightCanvas();
      }
    });

    // ==========================================
    // 6. VIDEO FULLSCREEN TOGGLE LOGIC
    // ==========================================
    const fullscreenBtn = document.querySelector(".amd-course-video-fullscreen-btn");
    if (fullscreenBtn) {
      fullscreenBtn.addEventListener("click", () => {
        const container = document.querySelector(".amd-app-wrap");
        if (!container) return;

        if (!document.fullscreenElement &&
            !document.webkitFullscreenElement &&
            !document.mozFullScreenElement &&
            !document.msFullscreenElement) {
          
          if (container.requestFullscreen) container.requestFullscreen();
          else if (container.mozRequestFullScreen) container.mozRequestFullScreen();
          else if (container.webkitRequestFullscreen) container.webkitRequestFullscreen();
          else if (container.msRequestFullscreen) container.msRequestFullscreen();
          
        } else {
          
          if (document.exitFullscreen) document.exitFullscreen();
          else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
          else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
          else if (document.msExitFullscreen) document.msExitFullscreen();
          
        }
      });
    }

  });
})();