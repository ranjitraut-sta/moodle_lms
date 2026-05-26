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
      if (icon)
        icon.className = isCollapsed
          ? "ri-arrow-right-s-line"
          : "ri-arrow-left-s-line";

      toggleBtn.setAttribute(
        "aria-label",
        isCollapsed ? "Expand amdcoursesidebar" : "Collapse amdcoursesidebar",
      );
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
  document.querySelectorAll(".amd-course-chapter-header").forEach((header) => {
    header.addEventListener("click", () => {
      const isExpanded = header.classList.contains("expanded");
      const listId = header.getAttribute("aria-controls");
      const list = listId ? document.getElementById(listId) : null;

      if (isExpanded) {
        header.classList.remove("expanded");
        header.classList.add("collapsed");
        if (list) list.classList.remove("expanded");
        header.setAttribute("aria-expanded", "false");

        const icon = header.querySelector("i");
        if (icon) icon.className = "ri-arrow-right-s-line";
      } else {
        header.classList.remove("collapsed");
        header.classList.add("expanded");
        if (list) list.classList.add("expanded");
        header.setAttribute("aria-expanded", "true");

        const icon = header.querySelector("i");
        if (icon) icon.className = "ri-arrow-down-s-line";
      }
    });
  });

  // --- NAYA UMEDWAR: Active element focus ra scroll logic ---
  // DOM element fully load huna sath run garne
window.addEventListener("DOMContentLoaded", () => {

  // Main scrollable sidebar
  const sidebar = document.querySelector(".amdcoursesidebar-inner");

  // Current active lesson
  const activeLesson = document.querySelector(".lesson.active");

  if (!sidebar || !activeLesson) return;

  // Parent chapter
  const chapter = activeLesson.closest(".amd-course-chapter");

  // Chapter header
  const header = chapter?.querySelector(".amd-course-chapter-header");

  // Focus header first
  if (header) {
    //header.focus();

    // Scroll sidebar so clicked/open chapter stays visible
    const sidebarTop = sidebar.getBoundingClientRect().top;
    const headerTop = header.getBoundingClientRect().top;

    sidebar.scrollTo({
      top:
        sidebar.scrollTop +
        (headerTop - sidebarTop) -
        20,
      behavior: "smooth"
    });
  }

  // Then bring active lesson nicely into center view
  setTimeout(() => {
   // activeLesson.focus();

    activeLesson.scrollIntoView({
      behavior: "smooth",
      block: "center",
      inline: "nearest"
    });

  }, 300);

});



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

    // Optional: Close right canvas on Escape key press
    document.addEventListener("keydown", (e) => {
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
    const container = document.querySelector('.amd-app-wrap');

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


