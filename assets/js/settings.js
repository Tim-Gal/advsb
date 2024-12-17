document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.settings-form');
    const majorSelect = document.getElementById('major');
    const minorSelect = document.getElementById('minor');

    form.addEventListener('submit', function (event) {
        if (majorSelect.value === minorSelect.value && majorSelect.value !== '') {
            event.preventDefault();
            alert('Major and Minor cannot be the same. Please select different values.');
        }
    });
});
