
document.addEventListener('DOMContentLoaded', function () {
    const settingsForm = document.querySelector('.settings-form');
    const majorSelect = document.getElementById('major');
    const minorSelect = document.getElementById('minor');
    if (!majorSelect || !minorSelect) {
        return;
    }
    function disableSelectedMajorInMinor() {
        const selectedMajor = majorSelect.name;
       
        Array.from(minorSelect.options).forEach(option => {
            if (option.value === selectedMajor) {
                option.disabled = true;
            } else {
                option.disabled = false;
            }
        });

        if (minorSelect.value === selectedMajor) {

            minorSelect.value = '';
        }
    }

    disableSelectedMajorInMinor();

    majorSelect.addEventListener('change', disableSelectedMajorInMinor);

    settingsForm.addEventListener('submit', function (e) {


        
        const major = majorSelect.value;
        const minor = minorSelect.value;
        const newUsername = document.getElementById('new_username').value.trim();

        const usernameRegex = /^[A-Za-z0-9_.]{3,20}$/;

        if (newUsername && !usernameRegex.test(newUsername)) {
            e.preventDefault();
            alert('Username must be 3-20 characters long and can include letters, numbers, underscores, and periods.');
            return;
        }

        if (minor && minor === major) {
            e.preventDefault();
            alert('Minor cannot be the same as Major.');
            return;
        }
    });
});
