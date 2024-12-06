document.addEventListener('DOMContentLoaded', function() {
    const semesterRadios = document.querySelectorAll('input[name="semester"]');
    const courseSearchInput = document.getElementById('courseSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const addCourseButton = document.getElementById('addCourseButton');

    let selectedSemester = 'Fall';
    let searchTimeout = null;

    function loadUserSchedule() {
        fetch('get_user_schedule.php?semester=' + encodeURIComponent(selectedSemester))
            .then(response => response.json())
            .then(offerings => {
                clearScheduleTable();
                offerings.forEach(o => {
                    populateCourseBlock(o.code, o.day_of_week, o.start_time, o.end_time, o.location);
                });
            })
            .catch(err => console.error('Error loading user schedule:', err));
    }

    function clearScheduleTable() {
        document.querySelectorAll('.course-block').forEach(block => block.remove());
    }

    // Populate a course block for a given course/time slot
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

    // Update selectedSemester when a different semester radio is chosen
    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value;
            // Load the user's schedule for the newly selected semester
            loadUserSchedule();
        });
    });

    // Autocomplete search on keyup in the course search input
    courseSearchInput.addEventListener('keyup', function() {
        const query = courseSearchInput.value.trim();
        if (query.length >= 2) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetch('search_courses.php?query=' + encodeURIComponent(query) + '&semester=' + encodeURIComponent(selectedSemester))
                    .then(response => response.json())
                    .then(courses => {
                        searchSuggestions.innerHTML = '';
                        if (courses.length > 0) {
                            searchSuggestions.style.display = 'block';
                            courses.forEach(c => {
                                const item = document.createElement('div');
                                item.className = 'suggestion-item';
                                item.style.padding = '5px';
                                item.style.cursor = 'pointer';
                                item.textContent = c.code + ' - ' + c.name;
                                item.addEventListener('click', () => {
                                    courseSearchInput.value = c.code;
                                    searchSuggestions.style.display = 'none';
                                });
                                searchSuggestions.appendChild(item);
                            });
                        } else {
                            searchSuggestions.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching courses:', err);
                        searchSuggestions.style.display = 'none';
                    });
            }, 300); // debounce
        } else {
            searchSuggestions.style.display = 'none';
        }
    });

    // On "Go" button click, add the selected course to user's schedule (DB) and reload
    addCourseButton.addEventListener('click', function() {
        const courseCode = courseSearchInput.value.trim();
        if (!courseCode) {
            alert("Please enter or select a course code.");
            return;
        }

        // Add course to the user's schedule
        fetch('add_course_to_schedule.php?code=' + encodeURIComponent(courseCode) + '&semester=' + encodeURIComponent(selectedSemester))
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                // After adding to DB, reload user schedule from DB
                loadUserSchedule();
            })
            .catch(err => console.error('Error adding course to schedule:', err));
    });

    // On page load, fetch the user's current schedule
    loadUserSchedule();
});
