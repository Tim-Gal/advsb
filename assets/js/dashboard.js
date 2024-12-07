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
                if (!offerings.error) {
                    offerings.forEach(o => {
                        populateCourseBlock(o.code, o.day_of_week, o.start_time, o.end_time, o.location);
                    });
                }
            })
            .catch(err => console.error('Error loading user schedule:', err));
    }

    function clearScheduleTable() {
        document.querySelectorAll('.course-block').forEach(block => block.remove());
    }

    function populateCourseBlock(courseCode, day, startTime, endTime, location) {
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        for (let h = startHour; h < endHour; h++) {
            const cellClass = `${day}-${h}`;
            const cell = document.querySelector(`.${cellClass}`);
            if (cell) {
                const block = document.createElement('div');
                block.className = 'course-block';
                // We rely on CSS for styling now
                block.textContent = `${courseCode}\n${location}\n(${startTime}-${endTime})`;
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
                                // No inline styles, rely on dashboard.css
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
            }, 300);
        } else {
            searchSuggestions.style.display = 'none';
        }
    });

    addCourseButton.addEventListener('click', function() {
        const courseCode = courseSearchInput.value.trim();
        if (!courseCode) {
            alert("Please enter or select a course code.");
            return;
        }

        fetch('add_course_to_schedule.php?code=' + encodeURIComponent(courseCode) + '&semester=' + encodeURIComponent(selectedSemester))
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                loadUserSchedule();
            })
            .catch(err => console.error('Error adding course to schedule:', err));
    });

    loadUserSchedule();
});
