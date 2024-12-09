document.addEventListener('DOMContentLoaded', function () {
    const semesterRadios = document.querySelectorAll('input[name="semester"]');
    const courseSearchInput = document.getElementById('courseSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const addCourseButton = document.getElementById('addCourseButton');

    let selectedSemester = 'Fall';
    let searchTimeout = null;

    /**
     * Function to display a Bootstrap Toast notification
     * @param {string} message - The message to display in the toast
     * @param {string} type - The type of toast ('success', 'error', 'warning', 'info')
     */
    function showToast(message, type = 'success') {
        const toastElement = document.getElementById('notificationToast');
        const toastBody = document.getElementById('toastBody');

        if (!toastElement || !toastBody) {
            console.error("Toast elements not found in the DOM.");
            return;
        }

        // Remove existing background classes
        toastElement.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-primary');

        // Assign new background class based on type
        switch (type) {
            case 'success':
                toastElement.classList.add('bg-success');
                break;
            case 'error':
                toastElement.classList.add('bg-danger');
                break;
            case 'warning':
                toastElement.classList.add('bg-warning');
                break;
            case 'info':
                toastElement.classList.add('bg-info');
                break;
            default:
                toastElement.classList.add('bg-primary');
        }

        // Set the toast message
        toastBody.textContent = message;

        // Initialize and show the toast
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }

    /**
     * Load the user's schedule from the server
     */
    function loadUserSchedule() {
        fetch('get_user_schedule.php?semester=' + encodeURIComponent(selectedSemester))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(offerings => {
                clearScheduleTable();
                if (!offerings.error) {
                    offerings.forEach(o => {
                        populateCourseBlock(o.code, o.day_of_week, o.start_time, o.end_time, o.location);
                    });
                } else {
                    console.error("Error fetching schedule:", offerings.error);
                    showToast("Error fetching schedule: " + offerings.error, 'error');
                }
            })
            .catch(err => {
                console.error('Error loading user schedule:', err);
                showToast('Error loading your schedule. Please try again.', 'error');
            });
    }

    function clearScheduleTable() {
        document.querySelectorAll('.course-block').forEach(block => block.remove());
    }

    /**
     * Populate the schedule table with course blocks
     * @param {string} courseCode 
     * @param {string} day 
     * @param {string} startTime 
     * @param {string} endTime 
     * @param {string} location 
     */
    function populateCourseBlock(courseCode, day, startTime, endTime, location) {
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        for (let h = startHour; h < endHour; h++) {
            const cellClass = `${day}-${h}`;
            const cell = document.querySelector(`.${cellClass}`);
            if (cell) {
                const block = document.createElement('div');
                block.className = 'course-block';
                block.textContent = `${courseCode}\n${location}\n(${startTime}-${endTime})`;
                block.style.whiteSpace = 'pre-wrap';
                block.style.backgroundColor = '#B3E5FC';
                block.style.margin = '2px 0';
                block.style.padding = '5px';
                block.style.border = '1px solid #ccc';
                block.style.fontSize = '0.9em';
                cell.appendChild(block);
            }
        }
    }

    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value;
            loadUserSchedule();
        });
    });

    courseSearchInput.addEventListener('keyup', function () {
        const query = courseSearchInput.value.trim();
        if (query.length >= 2) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetch('get_course_offerings.php?query=' + encodeURIComponent(query) + '&semester=' + encodeURIComponent(selectedSemester))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(courses => {
                        searchSuggestions.innerHTML = '';
                        if (!courses.error && courses.length > 0) {
                            searchSuggestions.style.display = 'block';
                            courses.forEach(c => {
                                const item = document.createElement('div');
                                item.className = 'suggestion-item';
                                item.textContent = `${c.code} - ${c.name}`;
                                item.addEventListener('click', () => {
                                    courseSearchInput.value = c.code;
                                    searchSuggestions.style.display = 'none';
                                });
                                searchSuggestions.appendChild(item);
                            });
                        } else {
                            searchSuggestions.style.display = 'none';
                            if (courses.error) {
                                showToast(courses.error, 'info');
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching courses:', err);
                        searchSuggestions.style.display = 'none';
                        showToast('Error fetching courses. Please try again.', 'error');
                    });
            }, 300);
        } else {
            searchSuggestions.style.display = 'none';
        }
    });

    addCourseButton.addEventListener('click', function () {
        const courseCode = courseSearchInput.value.trim();
        if (!courseCode) {
            showToast("Please enter or select a course code.", 'warning');
            return;
        }

        fetch('add_course_to_schedule.php?code=' + encodeURIComponent(courseCode) + '&semester=' + encodeURIComponent(selectedSemester))
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                console.log("Response:", data);
                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }
                showToast(`Successfully added ${data.inserted} section(s) to your schedule.`, 'success');
                loadUserSchedule();
            })
            .catch(err => {
                console.error('Error adding course to schedule:', err);
                showToast('Error adding course to schedule. Please try again.', 'error');
            });
    });

    loadUserSchedule();
});
