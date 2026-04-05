document.addEventListener('DOMContentLoaded', () => {
    // Form toggle buttons and forms
    const controlButtons = document.querySelectorAll('.amd-lms-login-control-btn');
    const forms = document.querySelectorAll('.amd-lms-login-form');

    // Attach click event to each control button to switch forms
    controlButtons.forEach(button => {
        button.addEventListener('click', () => {
            const formId = button.dataset.form;

            // Remove active class from all buttons and add to the clicked one
            controlButtons.forEach(btn => btn.classList.remove('amd-lms-login-active-btn'));
            button.classList.add('amd-lms-login-active-btn');

            // Show the selected form and hide others
            forms.forEach(form => {
                if (form.id === `${formId}-form`) {
                    form.classList.add('amd-lms-login-active');
                } else {
                    form.classList.remove('amd-lms-login-active');
                }
            });
        });
    });

    // Password toggle icons (book icons) for showing/hiding password
    document.querySelectorAll('.togglePassword').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const parent = this.closest('.amd-lms-login-input-group');
            const passwordInput = parent.querySelector('input');  // just input, no type selector
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-regular', 'fa-book');
                icon.classList.add('fa-solid', 'fa-book-open');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-solid', 'fa-book-open');
                icon.classList.add('fa-regular', 'fa-book');
            }
        });
    });

});
