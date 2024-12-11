/* assets/js/dashboard.js */

document.addEventListener('DOMContentLoaded', function () {
    const semesterRadios = document.querySelectorAll('input[name="semester"]');
    const courseSearchInput = document.getElementById('courseSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const selectedCourseContainer = document.getElementById('selectedCourseContainer');
    const selectedCourseText = document.getElementById('selectedCourseText');
    const removeSelectedCourse = document.getElementById('removeSelectedCourse');
    const confirmAddCourseButton = document.getElementById('confirmAddCourse');
    const downloadPdfButton = document.getElementById('downloadPdfButton'); // PDF Download Button
    const sidebarSemesterSelect = document.getElementById('sidebarSemesterSelect');
    const enrolledCoursesList = document.getElementById('enrolledCoursesList');
    const enrolledCoursesLoading = document.getElementById('enrolledCoursesLoading');
    const csrfToken = document.getElementById('csrfToken').value;

    let selectedSemester = 'Fall'; // Default semester
    let searchTimeout = null;
    let selectedCourse = null; // To store the selected course object

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
        fetch(`../api/get_user_schedule.php?semester=${encodeURIComponent(selectedSemester)}`)
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
                        populateCourseBlock(o.section_code, o.code, o.day_of_week, o.start_time, o.end_time, o.location);
                    });
                    // Attach delete listeners after populating
                    attachDeleteListeners();
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

    /**
     * Clear the existing schedule table
     */
    function clearScheduleTable() {
        document.querySelectorAll('.course-block').forEach(block => block.remove());
    }

    /**
     * Populate the schedule table with course blocks
     * @param {number} sectionCode - The unique code of the section enrollment
     * @param {string} courseCode 
     * @param {string} day 
     * @param {string} startTime 
     * @param {string} endTime 
     * @param {string} location 
     */
    function populateCourseBlock(sectionCode, courseCode, day, startTime, endTime, location) {
        const startHour = parseInt(startTime.split(':')[0], 10);
        const startMinute = parseInt(startTime.split(':')[1], 10);
        const endHour = parseInt(endTime.split(':')[0], 10);
        const endMinute = parseInt(endTime.split(':')[1], 10);

        // Calculate duration in hours (assuming 1 hour per course)
        // Since we're reverting to 1-hour courses, duration is always 1
        const duration = 1; 

        // Loop through the duration (1 hour)
        for (let i = 0; i < duration; i++) {
            const currentHour = startHour + i;
            const cellClass = `${day}-${currentHour}`;
            const cell = document.querySelector(`.${cellClass}`);
            if (cell) {
                const courseBlockContainer = cell.querySelector('.course-block-container');

                // Create the course block div
                const courseBlock = document.createElement('div');
                courseBlock.classList.add('course-block');
                courseBlock.setAttribute('data-section-code', sectionCode);
                courseBlock.setAttribute('data-course-code', courseCode); // Store course_code for reference

                // Populate course information
                const courseInfo = document.createElement('div');
                courseInfo.innerHTML = `<strong>${courseCode}</strong> - ${location}<br><small>${startTime} - ${endTime}</small>`;

                // Create the delete button
                const deleteBtn = document.createElement('button');
                deleteBtn.classList.add('delete-course-btn'); // Consistent class name
                deleteBtn.setAttribute('aria-label', 'Remove Course');
                deleteBtn.innerHTML = '&times;'; // HTML entity for multiplication sign (×)

                // Assemble the course block
                courseBlock.appendChild(deleteBtn);
                courseBlock.appendChild(courseInfo);

                // Append to the container
                courseBlockContainer.appendChild(courseBlock);
            }
        }
    }

    /**
     * Event listener for semester selection changes in main content
     */
    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value; // Values are 'Fall', 'Winter', 'Summer'
            loadUserSchedule();
            // Clear any selected course when semester changes
            if (selectedCourse) {
                removeSelectedCourseFunction();
            }
            // Also update the sidebar to match the main semester selection
            sidebarSemesterSelect.value = selectedSemester;
            loadEnrolledCourses();
        });
    });

    /**
     * Event listener for semester selection changes in sidebar
     */
    sidebarSemesterSelect.addEventListener('change', function () {
        selectedSemester = this.value;
        loadEnrolledCourses();
        loadUserSchedule();
        // Also update the main semester selection to match the sidebar
        semesterRadios.forEach(radio => {
            if (radio.value === selectedSemester) {
                radio.checked = true;
            }
        });
    });

    /**
     * Event listener for course search input
     */
    courseSearchInput.addEventListener('keyup', function (e) {
        const query = courseSearchInput.value.trim();
        if (query.length >= 2) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetch(`../api/get_course_offerings.php?query=${encodeURIComponent(query)}&semester=${encodeURIComponent(selectedSemester)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchSuggestions.innerHTML = '';
                        if (data.success && data.courses.length > 0) {
                            displayCourseSuggestions(data.courses);
                        } else {
                            displayNoSuggestions(data.error || 'No courses found.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching courses:', error);
                        displayNoSuggestions('Error fetching courses. Please try again.');
                    });
            }, 300);
        } else {
            searchSuggestions.innerHTML = '';
            searchSuggestions.style.display = 'none';
        }
    });

    /**
     * Display course suggestions in the suggestions dropdown
     * @param {Array} courses 
     */
    function displayCourseSuggestions(courses) {
        searchSuggestions.innerHTML = '';
        courses.forEach(course => {
            const suggestionItem = document.createElement('button');
            suggestionItem.type = 'button';
            suggestionItem.className = 'list-group-item list-group-item-action suggestion-item';
            suggestionItem.textContent = `${course.code} - ${course.name}`;
            suggestionItem.setAttribute('data-section-code', course.section_code); // Store section_code
            suggestionItem.addEventListener('click', () => {
                selectCourse(course);
            });
            searchSuggestions.appendChild(suggestionItem);
        });
        searchSuggestions.style.display = 'block';
    }

    /**
     * Display a message when no suggestions are found
     * @param {string} message 
     */
    function displayNoSuggestions(message) {
        searchSuggestions.innerHTML = `<div class="list-group-item list-group-item-action disabled">${message}</div>`;
        searchSuggestions.style.display = 'block';
    }

    /**
     * Function to select a course from suggestions
     * @param {Object} course 
     */
    function selectCourse(course) {
        selectedCourse = {
            code: course.code,
            section_code: course.section_code
        };
        selectedCourseText.textContent = `${course.code}`;
        selectedCourseContainer.style.display = 'block';

        // Disable the input and semester select
        courseSearchInput.value = `${course.code}`;
        courseSearchInput.disabled = true;
        semesterRadios.forEach(radio => {
            radio.disabled = true;
        });

        // Hide suggestions
        searchSuggestions.innerHTML = '';
        searchSuggestions.style.display = 'none';
    }

    /**
     * Function to remove the selected course
     */
    function removeSelectedCourseFunction() {
        selectedCourse = null;
        selectedCourseText.textContent = '';
        selectedCourseContainer.style.display = 'none';

        // Enable the input and semester select
        courseSearchInput.value = '';
        courseSearchInput.disabled = false;
        semesterRadios.forEach(radio => {
            radio.disabled = false;
        });
    }

    /**
     * Event listener for removing the selected course
     */
    removeSelectedCourse.addEventListener('click', function () {
        removeSelectedCourseFunction();
        showToast('Selected course removed.', 'info');
    });

    /**
     * Function to highlight conflicting lectures
     * @param {Array} conflicts - Array of conflicting courses
     */
    function highlightConflicts(conflicts) {
        conflicts.forEach(conflict => {
            const courseCode = conflict.course_code;
            const courseName = conflict.course_name;

            // Select all lecture blocks for the conflicting course
            // Assuming lecture blocks have a data attribute like data-course-code
            const lectureBlocks = document.querySelectorAll(`[data-course-code="${courseCode}"]`);

            lectureBlocks.forEach(block => {
                // Add a CSS class to highlight the block (e.g., red background)
                block.classList.add('conflict-lecture');

                // Add a tooltip with the conflict message
                block.setAttribute('title', `This lecture conflicts with ${courseName}.`);
                block.classList.add('has-tooltip');

                // Initialize Bootstrap tooltip
                new bootstrap.Tooltip(block);
            });
        });
    }

    /**
     * Event listener for confirming the addition of a course
     */
    confirmAddCourseButton.addEventListener('click', function () {
        if (!selectedCourse || !selectedCourse.section_code) {
            showToast('No course selected to add.', 'warning');
            return;
        }

        // Disable the button and show loading spinner
        confirmAddCourseButton.disabled = true;
        const enrollmentLoading = document.getElementById('enrollmentLoading');
        if (enrollmentLoading) {
            enrollmentLoading.style.display = 'inline-block';
        }

        // Send the enrollment request
        fetch(`../api/add_course_to_schedule.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                section_code: selectedCourse.section_code, // Use section_code
                semester: selectedSemester,
                csrf_token: csrfToken
            })
        })
            .then(res => res.json())
            .then(data => {
                // Re-enable the button and hide loading spinner
                confirmAddCourseButton.disabled = false;
                if (enrollmentLoading) {
                    enrollmentLoading.style.display = 'none';
                }

                if (data.success) {
                    showToast(`Course ${selectedCourse.code} added successfully!`, 'success');
                    loadUserSchedule();
                    loadEnrolledCourses(); // Refresh enrolled courses in sidebar
                    removeSelectedCourseFunction();

                    // Check for warnings about missing prerequisites
                    if (data.warning) {
                        // Display the warning message to the user
                        showToast(data.warning, 'warning');
                    }

                    // Check for schedule conflicts
                    if (data.conflicts && Array.isArray(data.conflicts)) {
                        highlightConflicts(data.conflicts);
                    }
                } else {
                    // Handle specific error cases
                    showToast(data.error || 'Failed to add course.', 'error');
                }
            })
            .catch(err => {
                // Re-enable the button and hide loading spinner in case of error
                confirmAddCourseButton.disabled = false;
                const enrollmentLoading = document.getElementById('enrollmentLoading');
                if (enrollmentLoading) {
                    enrollmentLoading.style.display = 'none';
                }

                console.error('Error adding course:', err);
                showToast('Error adding course. Please try again.', 'error');
            });
    });

    /**
     * Function to handle course deletion from the main schedule
     * @param {number} sectionCode - The unique code of the section enrollment
     * @param {HTMLElement} courseBlock - The DOM element representing the course block
     */
    function removeCourseFromSchedule(sectionCode, courseBlock) {
        // Confirmation handled here
        if (!confirm('Are you sure you want to remove this course from your schedule?')) {
            return;
        }

        fetch(`../api/delete_course.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                section_code: sectionCode,
                csrf_token: csrfToken 
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add fade-out class for animation
                    courseBlock.classList.add('fade-out');
                    // Remove the course block after the animation completes
                    courseBlock.addEventListener('animationend', function () {
                        courseBlock.remove();
                    });
                    showToast('Course removed successfully.', 'success');
                    loadUserSchedule();
                    loadEnrolledCourses(); // Refresh enrolled courses in sidebar
                } else {
                    showToast(data.error || 'Failed to remove the course.', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting course:', error);
                showToast('An error occurred while removing the course. Please try again.', 'error');
            });
    }

    /**
     * Function to handle course deletion from the sidebar
     * @param {string} courseCode - The course code to delete
     * @param {HTMLElement} enrolledCourseElement - The DOM element representing the enrolled course
     */
    function removeEnrolledCourse(courseCode, enrolledCourseElement) {
        // Confirmation handled here
        if (!confirm('Are you sure you want to remove this course from your enrolled list?')) {
            return;
        }

        fetch(`../api/delete_course_by_code.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                course_code: courseCode,
                csrf_token: csrfToken 
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add fade-out class for animation
                    enrolledCourseElement.classList.add('fade-out');
                    // Remove the enrolled course element after the animation completes
                    enrolledCourseElement.addEventListener('animationend', function () {
                        enrolledCourseElement.remove();
                    });
                    showToast('Course removed successfully.', 'success');
                    loadUserSchedule();
                    loadEnrolledCourses(); // Refresh enrolled courses in sidebar
                } else {
                    showToast(data.error || 'Failed to remove the course.', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting course:', error);
                showToast('An error occurred while removing the course. Please try again.', 'error');
            });
    }

    /**
     * Function to attach delete event listeners to all delete buttons in the main schedule
     */
    function attachDeleteListeners() {
        const deleteButtons = document.querySelectorAll('.delete-course-btn');
        deleteButtons.forEach(button => {
            // To prevent attaching multiple listeners to the same button
            if (!button.dataset.listenerAttached) {
                button.addEventListener('click', function (e) {
                    e.stopPropagation(); // Prevent triggering other click events
                    const courseBlock = button.closest('.course-block');
                    const sectionCode = parseInt(courseBlock.getAttribute('data-section-code'), 10);
                    if (isNaN(sectionCode) || sectionCode <= 0) {
                        showToast('Invalid section code.', 'error');
                        return;
                    }
                    removeCourseFromSchedule(sectionCode, courseBlock);
                });
                // Mark the button as having an attached listener
                button.dataset.listenerAttached = 'true';
            }
        });
    }

    /**
     * Function to download the schedule as a PDF
     */
    function downloadScheduleAsPDF() {
        const scheduleTable = document.getElementById('scheduleTable');
        if (!scheduleTable) {
            showToast('Schedule table not found.', 'error');
            return;
        }

        // Use html2canvas to capture the schedule table
        html2canvas(scheduleTable, { scale: 2 }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf; // Correct namespace
            const pdf = new jsPDF('p', 'mm', 'a4');

            // Calculate width and height to fit A4 size
            const imgWidth = 210; // A4 width in mm
            const pageHeight = pdf.internal.pageSize.getHeight();
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = 0;

            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            // Add extra pages if necessary
            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            pdf.save('my_schedule.pdf');
            showToast('Schedule downloaded as PDF.', 'success');
        }).catch(err => {
            console.error('Error generating PDF:', err);
            showToast('Failed to generate PDF. Please try again.', 'error');
        });
    }

    /**
     * Function to load enrolled courses from the server
     */
    function loadEnrolledCourses() {
        enrolledCoursesList.innerHTML = '';
        enrolledCoursesLoading.style.display = 'block';

        fetch(`../api/get_enrolled_courses.php?semester=${encodeURIComponent(selectedSemester)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(courses => {
                enrolledCoursesLoading.style.display = 'none';
                displayEnrolledCourses(courses);
            })
            .catch(err => {
                enrolledCoursesLoading.style.display = 'none';
                console.error('Error fetching enrolled courses:', err);
                showToast('Error fetching enrolled courses. Please try again.', 'error');
            });
    }

    /**
     * Function to display enrolled courses in the sidebar
     * @param {Array} courses 
     */
    function displayEnrolledCourses(courses) {
        enrolledCoursesList.innerHTML = '';
        if (courses.length === 0) {
            enrolledCoursesList.innerHTML = '<p>No enrolled courses for this semester.</p>';
            return;
        }

        courses.forEach(course => {
            const enrolledCourseDiv = document.createElement('div');
            enrolledCourseDiv.classList.add('enrolled-course');

            const courseInfoDiv = document.createElement('div');
            courseInfoDiv.classList.add('course-info');
            courseInfoDiv.textContent = `${course.code}`; // Display only course code

            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('delete-enrolled-course-btn');
            deleteBtn.setAttribute('aria-label', 'Remove Enrolled Course');
            deleteBtn.innerHTML = '&times;';

            // Attach delete event
            deleteBtn.addEventListener('click', function () {
                removeEnrolledCourse(course.code, enrolledCourseDiv);
            });

            enrolledCourseDiv.appendChild(courseInfoDiv);
            enrolledCourseDiv.appendChild(deleteBtn);

            enrolledCoursesList.appendChild(enrolledCourseDiv);
        });
    }

    /**
     * Initial function calls
     */
    loadUserSchedule();
    loadEnrolledCourses();
    const style = document.createElement('style');
    style.innerHTML = `
        .conflict-lecture {
            background-color: rgba(255, 0, 0, 0.3) !important; /* Red background */
            border: 2px solid red !important;
            cursor: pointer; /* Change cursor to pointer to indicate tooltip */
        }

        /* Tooltip Styling (Optional) */
        .has-tooltip {
            position: relative;
        }

        /* Ensure tooltips appear above other elements */
        .tooltip {
            z-index: 2000;
        }
    `;
    document.head.appendChild(style);

    /**
     * Event listener for PDF download button
     */
    if (downloadPdfButton) {
        downloadPdfButton.addEventListener('click', downloadScheduleAsPDF);
    } else {
        console.error('Download PDF button not found.');
    }
});
