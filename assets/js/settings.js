document.addEventListener('DOMContentLoaded', function () {
    console.log("settings.js loaded"); // Debugging

    const settingsForm = document.querySelector('.settings-form');
    const majorSelect = document.getElementById('major');
    const minorSelect = document.getElementById('minor');

    if (!majorSelect || !minorSelect || !settingsForm) {
        console.error("Major, Minor select elements or settings form not found.");
        return;
    }

    // Function to disable minor options that have the same name as the selected major
    function disableSelectedMajorInMinor() {
        const selectedMajorName = majorSelect.options[majorSelect.selectedIndex].getAttribute('data-name') || '';

        Array.from(minorSelect.options).forEach(option => {
            const minorName = option.getAttribute('data-name') || '';
            if (minorName === selectedMajorName && option.value !== "") {
                option.disabled = true;
                console.log(`Disabled minor option: ${minorName}`);
            } else {
                option.disabled = false;
            }
        });

        // If minor was previously set to the same as major, reset it
        const currentMinorName = minorSelect.options[minorSelect.selectedIndex].getAttribute('data-name') || '';
        if (currentMinorName === selectedMajorName) {
            minorSelect.value = '';
            console.log("Minor reset because it matched the major.");
        }
    }

    // Initial disable on page load
    disableSelectedMajorInMinor();

    // Listen for changes in the major select
    majorSelect.addEventListener('change', disableSelectedMajorInMinor);
    console.log("Event listener added to major select"); // Debugging

    // Form submission validation
    settingsForm.addEventListener('submit', function (e) {
        console.log("Settings form submitted"); // Debugging

        const major = majorSelect.value;
        const minor = minorSelect.value;
        const newUsername = document.getElementById('new_username').value.trim();

        const usernameRegex = /^[A-Za-z0-9_.]{3,20}$/;

        if (newUsername && !usernameRegex.test(newUsername)) {
            e.preventDefault();
            alert('Username must be 3-20 characters long and can include letters, numbers, underscores, and periods.');
            return;
        }

        // Fetch selected major and minor names
        const selectedMajorName = majorSelect.options[majorSelect.selectedIndex].getAttribute('data-name') || '';
        const selectedMinorName = minorSelect.options[minorSelect.selectedIndex].getAttribute('data-name') || '';

        if (selectedMinorName && selectedMinorName === selectedMajorName) {
            e.preventDefault();
            alert('Minor cannot be the same as Major.');
            return;
        }
    });
});
