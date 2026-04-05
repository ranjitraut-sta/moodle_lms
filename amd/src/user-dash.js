// user lms dash sidebar 
document.addEventListener('DOMContentLoaded', function () {

    // --- Sidebar Toggle Logic ---
    const desktopToggleBtn = document.getElementById('amd-lms-desktop-toggle-btn');
    const mobileToggleBtn = document.getElementById('amd-lms-mobile-toggle-btn');
    const mobileOverlay = document.getElementById('amd-lms-mobile-overlay');
    document.body.classList.remove('amd-lms-sidebar-collapsed');
    if (desktopToggleBtn) {
        desktopToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('amd-lms-sidebar-collapsed');
        });
    }
    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', () => {
            const body = document.body;
            if (body.classList.contains('amd-lms-sidebar-mobile-open')) {
                // If open, close it
                body.classList.remove('amd-lms-sidebar-mobile-open');
                body.classList.add('amd-lms-sidebar-mobile-open');
                body.classList.remove('amd-lms-sidebar-collapsed');
            } else {
                // If closed, open it
                body.classList.add('amd-lms-sidebar-mobile-open');
                body.classList.remove('amd-lms-sidebar-mobile-collapsed');
            }
        });
    }





    // --- Search Overlay Logic ---
    const searchOpenBtn = document.getElementById('amd-lms-search-open-btn');
    const searchCloseBtn = document.getElementById('amd-lms-search-close-btn');
    const searchInput = document.getElementById('amd-lms-search-input');

    if (searchOpenBtn) {
        searchOpenBtn.addEventListener('click', () => {
            document.body.classList.add('amd-lms-search-active');
            setTimeout(() => searchInput.focus(), 300);
        });
    }
    if (searchCloseBtn) {
        searchCloseBtn.addEventListener('click', () => {
            document.body.classList.remove('amd-lms-search-active');
        });
    }
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && document.body.classList.contains('amd-lms-search-active')) {
            document.body.classList.remove('amd-lms-search-active');
        }
    });




    // main content part js grade circle animated
    // --- Grade Chart Animation ---
    function gradeChart() {
        const circle = document.querySelector('.amd-lms-user-dash-circle'); if (!circle) return;
        const score = circle.dataset.score;
        const radius = 15.9155;
        const circumference = 2 * Math.PI * radius;
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        circle.style.strokeDashoffset = circumference;
        setTimeout(() => {
            const offset = circumference - (score / 100) * circumference;
            circle.style.strokeDashoffset = offset;
        }, 100);
        const svg = document.querySelector('.amd-lms-user-dash-grade-chart');
        if (svg.querySelector('defs')) return; // Prevent adding gradient multiple times
        const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
        defs.innerHTML = `<linearGradient id="amd-lms-grade-gradient" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:var(--amd-lms-grade-color-start);stop-opacity:1" /><stop offset="100%" style="stop-color:var(--amd-lms-grade-color-end);stop-opacity:1" /></linearGradient>`;
        svg.prepend(defs);
    };

    // --- Upcoming Events Slider ---
    function eventSlider() {
        const sliderContainer = document.getElementById('amd-lms-event-slider'); if (!sliderContainer) return;
        const events = [
            { title: "Design System II", instructor: "Ekemini Mark", date: "25 July, 2024", time: "7:00 pm WAT", image: "https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=500&q=80", live: true },
            { title: "Intro to Product Management", instructor: "Jane Doe", date: "28 July, 2024", time: "5:00 pm WAT", image: "https://images.unsplash.com/photo-1557804506-669a67965ba0?w=500&q=80", live: false },
            { title: "Advanced CSS Techniques", instructor: "John Smith", date: "30 July, 2024", time: "8:00 pm WAT", image: "https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=500&q=80", live: false },
            { title: "Agile & Scrum Basics", instructor: "Amina Yusuf", date: "02 Aug, 2024", time: "6:00 pm WAT", image: "https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=500&q=80", live: false }
        ];
        let currentEventIndex = 0;
        const renderEvent = () => {
            const event = events[currentEventIndex];
            sliderContainer.innerHTML = `
                        ${event.live ? '<p class="text-primary fw-bold mb-2"><i class="bi bi-broadcast"></i> Live class</p>' : '<p class="mb-2" style="visibility: hidden;">&nbsp;</p>'}
                        <div class="amd-lms-user-dash-event-item">
                            <img src="${event.image}" alt="${event.title}">
                            <div class="amd-lms-user-dash-event-info flex-grow-1">
                                <h4>${event.title}</h4>
                                <p>by ${event.instructor}</p>
                                <p><i class="bi bi-calendar-event"></i> ${event.date} &nbsp; <i class="bi bi-clock"></i> ${event.time}</p>
                                <div class="amd-lms-user-dash-event-controls">
                                    <button class="btn btn-primary px-4">Join Class</button>
                                    <div class="amd-lms-user-dash-event-slider-nav">
                                        <a id="prev-event"><i class="bi bi-chevron-left"></i></a>
                                        <span>${currentEventIndex + 1}/${events.length}</span>
                                        <a id="next-event"><i class="bi bi-chevron-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            document.getElementById('prev-event').addEventListener('click', () => { currentEventIndex = (currentEventIndex > 0) ? currentEventIndex - 1 : events.length - 1; renderEvent(); });
            document.getElementById('next-event').addEventListener('click', () => { currentEventIndex = (currentEventIndex < events.length - 1) ? currentEventIndex + 1 : 0; renderEvent(); });
        };
        renderEvent();
    };

    // Initialize all functions
    gradeChart();
    eventSlider();
});




// course list 2 page js 
document.addEventListener('DOMContentLoaded', function () {

    // --- TAB SWITCHING LOGIC ---
    const tabs = document.querySelectorAll('.amd-user-course-list2-tabs .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to the clicked tab
            this.classList.add('active');
            // In a real app, you would load content based on the clicked tab
            console.log('Switched to tab:', this.dataset.tab);
            alert('Switched to ' + this.textContent.trim() + ' tab. \n(Content does not change in this demo)');
        });
    });

    // --- FILTERING LOGIC ---
    const filterDropdownItems = document.querySelectorAll('.dropdown-menu .dropdown-item');
    const filterButton = document.getElementById('filter-btn');
    const courseItems = document.querySelectorAll('.amd-user-course-list2-item');

    filterDropdownItems.forEach(item => {
        item.addEventListener('click', function (event) {
            event.preventDefault();
            const filterValue = this.dataset.filter;

            // Update button text
            filterButton.textContent = this.textContent;

            // Filter the course items
            courseItems.forEach(course => {
                const courseStatus = course.dataset.status;
                if (filterValue === 'all' || filterValue === courseStatus) {
                    course.style.display = ''; // Reset to default display from CSS
                } else {
                    course.style.display = 'none';
                }
            });
        });
    });

    // --- VIEW TOGGLE LOGIC ---
    const viewToggleButtons = document.querySelectorAll('#view-toggle .btn');
    const courseListContainer = document.getElementById('course-list-container');

    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Update active state on buttons
            viewToggleButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Toggle the class on the container
            const view = this.dataset.view;
            if (view === 'grid') {
                courseListContainer.classList.add('grid-view');
            } else {
                courseListContainer.classList.remove('grid-view');
            }
        });
    });

});
// course list 2 page end



// message page js
// select trigger and select all 

document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const messageModal = new bootstrap.Modal(document.getElementById('messageDetailModal'));

    function updateActionUI() {
        const allCheckboxes = messageList.querySelectorAll('.amd-lms-message-item .form-check-input');
        const checkedCheckboxes = Array.from(allCheckboxes).filter(cb => cb.checked);
        const selectedCount = checkedCheckboxes.length;

        allCheckboxes.forEach(cb => {
            cb.closest('.amd-lms-message-item').classList.toggle('selected', cb.checked);
        });

        messageListHeader.classList.toggle('bulk-actions-active', selectedCount > 1);

        const visibleItems = Array.from(allCheckboxes).filter(cb => cb.closest('.amd-lms-message-item').style.display !== 'none');
        if (visibleItems.length > 0 && checkedCheckboxes.length === visibleItems.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCheckboxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    messageList.addEventListener('click', function (e) {
        const target = e.target;
        const messageItem = target.closest('.amd-lms-message-item');
        if (!messageItem) return;

        if (target.closest('.amd-lms-single-delete-btn')) {
            if (confirm('Are you sure you want to delete this message?')) {
                messageItem.remove();
                updateActionUI();
            }
        } else if (target.closest('.amd-lms-message-main-content')) {
            const modal = document.getElementById('messageDetailModal');
            modal.querySelector('.modal-title').textContent = messageItem.dataset.senderName;
            modal.querySelector('.modal-body').textContent = messageItem.dataset.fullMessage;
            messageModal.show();
        }
    });

    messageList.addEventListener('change', function (e) {
        if (e.target.matches('.form-check-input')) {
            updateActionUI();
        }
    });

    selectAllCheckbox.addEventListener('change', function () {
        const visibleCheckboxes = Array.from(messageList.querySelectorAll('.amd-lms-message-item'))
            .filter(item => item.style.display !== 'none')
            .map(item => item.querySelector('.form-check-input'));

        visibleCheckboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateActionUI();
    });

    bulkDeleteBtn.addEventListener('click', function () {
        const checkedItems = messageList.querySelectorAll('.amd-lms-message-item.selected');
        if (confirm(`Are you sure you want to delete ${checkedItems.length} messages?`)) {
            checkedItems.forEach(item => item.remove());
            updateActionUI();
        }
    });

    const searchInput = document.getElementById('messageSearchInput');
    searchInput.addEventListener('input', () => {
        updateActionUI();
    });

    updateActionUI();
});




// user setting and profile page js 

        document.addEventListener('DOMContentLoaded', () => {
            // --- APP OBJECT FOR MANAGEABILITY ---
            const App = {
                // Store all DOM element references in one place
                elements: {
                    avatarWrapper: document.getElementById('avatarWrapper'),
                    avatarUpload: document.getElementById('avatarUpload'),
                    avatarPreview: document.getElementById('avatarPreview'),
                    updateCoverBtn: document.getElementById('updateCoverBtn'),
                    coverUpload: document.getElementById('coverUpload'),
                    coverPreview: document.getElementById('coverPhotoPreview'),
                    toastContainer: document.querySelector('.amd-lms-account-toast-container'),
                    forms: document.querySelectorAll('form[data-toast-message]'),
                    exportDataBtn: document.getElementById('exportDataBtn'),
                    deleteConfirmInput: document.getElementById('deleteConfirmationInput'),
                    finalDeleteBtn: document.getElementById('finalDeleteBtn'),
                },

                // Main initialization function
                init() {
                    this.initImageUploads();
                    this.initFormSubmissions();
                    this.initDeleteModal();
                    this.elements.exportDataBtn.addEventListener('click', () => {
                        this.showToast('Data export request received. We will email you a download link.', 'info');
                    });
                },

                // Setup for all image upload interactions
                initImageUploads() {
                    this.elements.avatarWrapper.addEventListener('click', () => this.elements.avatarUpload.click());
                    this.elements.updateCoverBtn.addEventListener('click', () => this.elements.coverUpload.click());

                    this.elements.avatarUpload.addEventListener('change', e => this.previewImage(e, this.elements.avatarPreview));
                    this.elements.coverUpload.addEventListener('change', e => this.previewImage(e, this.elements.coverPreview));
                },

                // Generic handler for form submissions to show toasts
                initFormSubmissions() {
                    this.elements.forms.forEach(form => {
                        form.addEventListener('submit', (e) => {
                            e.preventDefault();
                            const message = form.dataset.toastMessage || 'Changes saved successfully!';
                            this.showToast(message, 'success');
                        });
                    });
                },

                // Logic for the delete account confirmation modal
                initDeleteModal() {
                    const confirmationText = 'delete my account';
                    this.elements.deleteConfirmInput.addEventListener('input', () => {
                        this.elements.finalDeleteBtn.disabled = this.elements.deleteConfirmInput.value.trim() !== confirmationText;
                    });
                    this.elements.finalDeleteBtn.addEventListener('click', () => {
                        alert('Account would be deleted. This is a front-end demo.');
                        bootstrap.Modal.getInstance(document.getElementById('deleteAccountModal')).hide();
                        this.elements.deleteConfirmInput.value = ''; // Reset for next time
                        this.elements.finalDeleteBtn.disabled = true;
                    });
                },

                // Helper function to preview an uploaded image
                previewImage(event, previewElement) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => { previewElement.src = e.target.result; }
                        reader.readAsDataURL(file);
                    }
                },

                // Helper function to create and show toast notifications
                showToast(message, type = 'success') {
                    const toastId = 'toast-' + Date.now();
                    const toastHTML = `
                        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">${message}</div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>`;
                    this.elements.toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                    const toastEl = document.getElementById(toastId);
                    new bootstrap.Toast(toastEl, { delay: 4000 }).show();
                    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
                }
            };

            // Run the app
            App.init();
        });
    // user setting and profile page js end