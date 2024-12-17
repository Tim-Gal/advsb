document.addEventListener('DOMContentLoaded', function () {
    const registrationForm = document.querySelector('.register-form');
    const majorSelect = document.getElementById('major');
    const minorSelect = document.getElementById('minor');

    // Function to disable the selected major in the minor dropdown
    function disableSelectedMajorInMinor() {
        const selectedMajor = majorSelect.value;

        Array.from(minorSelect.options).forEach(option => {
            if (option.value === selectedMajor) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });

        // If minor was previously set to the new major, reset it
        if (minorSelect.value === selectedMajor) {
            minorSelect.value = '';
        }
    }

    // Initial disable on page load
    disableSelectedMajorInMinor();

    // Listen for changes in the major select
    majorSelect.addEventListener('change', disableSelectedMajorInMinor);

    // Form submission validation
    registrationForm.addEventListener('submit', function (e) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const major = majorSelect.value;
        const minor = minorSelect.value;

        const requiredDomain = '@mail.mcgill.ca';

        if (username.length < 3) {
            e.preventDefault();
            alert('Username must be at least 3 characters long.');
            return;
        }

        if (!email.endsWith(requiredDomain)) {
            e.preventDefault();
            alert('Email must end with ' + requiredDomain);
            return;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            return;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return;
        }

        if (minor && minor === major) {
            e.preventDefault();
            alert('Minor cannot be the same as Major.');
            return;
        }
    });
});
