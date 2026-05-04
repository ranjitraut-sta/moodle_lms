function initPageScripts() {
  // --- 1. Navbar header top JS ---
  const mainHeader = document.getElementById("mainHeader");
  const mainNavbar = mainHeader.querySelector(".amd-lms-navbar");

  // Expanding Search Logic
  const searchTriggers = document.querySelectorAll(".amd-lms-search-trigger");
  const searchClose = document.querySelector(".amd-lms-search-close");
  const searchInput = document.getElementById("expanded-search-input");
  const searchForm = document.getElementById("searchForm");

  const openSearch = (e) => {
    e.preventDefault();
    mainNavbar.classList.add("search-active");
    setTimeout(() => searchInput.focus(), 350);
  };
  const closeSearch = () => mainNavbar.classList.remove("search-active");

  searchTriggers.forEach((trigger) =>
    trigger.addEventListener("click", openSearch),
  );
  searchClose.addEventListener("click", closeSearch);

  document.addEventListener(
    "keyup",
    (e) => e.key === "Escape" && closeSearch(),
  );
  document.addEventListener("click", (e) => {
    if (
      mainNavbar.classList.contains("search-active") &&
      !searchForm.contains(e.target) &&
      !e.target.closest(".amd-lms-search-trigger")
    ) {
      closeSearch();
    }
  });

  // Multi-level Dropdown for Mobile
  if (window.innerWidth < 992) {
    document
      .querySelectorAll(".amd-lms-dropdown-submenu a.dropdown-toggle")
      .forEach((element) => {
        element.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          const parent = element.closest(".amd-lms-dropdown-submenu");
          const submenu = parent.querySelector(".dropdown-menu");
          submenu.classList.toggle("show");
        });
      });
  }

  // Hide Header + Background on Scroll
  let lastScrollTop = 0;
  window.addEventListener(
    "scroll",
    function () {
      if (mainNavbar.classList.contains("search-active")) return;

      let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

      if (scrollTop > 10) {
        mainHeader.classList.add("header-bg-active");
      } else {
        mainHeader.classList.remove("header-bg-active");
      }

      if (scrollTop > lastScrollTop && scrollTop > mainHeader.offsetHeight) {
        mainHeader.classList.add("header-hidden");
      } else {
        mainHeader.classList.remove("header-hidden");
      }

      lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    },
    false,
  );

  // --- 2. Preloader JS ---
  const preloader = document.getElementById("preloader");
  const mainCarouselElement = document.getElementById("heroBgCarousel");
  const mainCarousel = new bootstrap.Carousel(mainCarouselElement, {
    interval: 8000,
    ride: false,
  });

  setTimeout(function () {
    preloader.classList.add("hidden");
    mainCarousel.cycle();
  }, 2500);

  // Video Modal Logic
  const videoModal = document.getElementById("videoModal");
  const videoIframe = document.getElementById("videoIframe");
  const originalVideoSrc = videoIframe.src;

  videoModal.addEventListener("shown.bs.modal", function () {
    videoIframe.src = originalVideoSrc + "?autoplay=1&mute=0";
    mainCarousel.pause();
  });

  videoModal.addEventListener("hidden.bs.modal", function () {
    videoIframe.src = originalVideoSrc;
    mainCarousel.cycle();
  });

  // --- 3. Course section filter ---
  const filterDropdownButton = document.getElementById("courseFilterDropdown");
  const filterDropdownItems = document.querySelectorAll(
    '.dropdown-menu[aria-labelledby="courseFilterDropdown"] .dropdown-item',
  );

  filterDropdownItems.forEach((item) => {
    item.addEventListener("click", function (event) {
      event.preventDefault();
      filterDropdownButton.textContent = this.textContent;
      const activeItem = document.querySelector(
        ".dropdown-menu .dropdown-item.active",
      );
      if (activeItem) activeItem.classList.remove("active");
      this.classList.add("active");
      // TODO: Add real course filtering logic here
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const root = document.documentElement;
  const btns = document.querySelectorAll(".themeToggle, .amd-theme-toggle");

  if (!btns.length) return;

  // load theme (saved or system)
  const theme =
    localStorage.theme ||
    (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");

  root.dataset.theme = theme;
  updateIcons();

  // click event (works for all buttons)
  btns.forEach((btn) =>
    btn.addEventListener("click", () => {
      root.dataset.theme = root.dataset.theme === "dark" ? "light" : "dark";

      localStorage.theme = root.dataset.theme;
      updateIcons();
    }),
  );

  function updateIcons() {
    btns.forEach(
      (btn) =>
        (btn.innerHTML =
          root.dataset.theme === "dark"
            ? '<i class="fas fa-sun"></i>'
            : '<i class="fas fa-moon"></i>'),
    );
  }
});

// Call the init function on DOMContentLoaded
document.addEventListener("DOMContentLoaded", () => {
  initPageScripts();
  initThemeToggle();
});

//    submenu dropdown js
document.addEventListener("DOMContentLoaded", () => {
  if (window.innerWidth < 992) {
    document
      .querySelectorAll(".amd-lms-dropdown-submenu > a.dropdown-toggle")
      .forEach((toggle) => {
        toggle.addEventListener("click", function (e) {
          e.preventDefault();
          const parent = this.parentElement;
          parent.classList.toggle("open");
        });
      });
  }
});

// course list page view switcher js
document.addEventListener("DOMContentLoaded", () => {
  const viewSwitcher = document.getElementById(
    "amd-lms-course-list-view-switcher",
  );
  const courseGrid = document.getElementById("amd-lms-course-list-grid");

  if (viewSwitcher && courseGrid) {
    const viewButtons = viewSwitcher.querySelectorAll("button");

    viewButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const columns = button.dataset.columns;
        courseGrid.style.setProperty("--amd-lms-grid-columns", columns);
        viewButtons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");
      });
    });
  }
});

// course detail page
document.addEventListener("DOMContentLoaded", () => {
  const allDetails = document.querySelectorAll(
    ".amd-lms-course-detail-accordion__item",
  );
  allDetails.forEach((details) => {
    details.addEventListener("toggle", (event) => {
      if (details.open) {
        allDetails.forEach((otherDetails) => {
          if (otherDetails !== details) {
            otherDetails.open = false;
          }
        });
      }
    });
  });
});
