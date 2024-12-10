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
        // Assuming start_time and end_time are in full-hour format
        // e.g., "13:00", "15:00"

        const cellClass = `${day}-${startHour}`;
        const cell = document.querySelector(`.${cellClass}`);
        if (cell) {
            const courseBlockContainer = cell.querySelector('.course-block-container');

            // Create the course block div
            const courseBlock = document.createElement('div');
            courseBlock.classList.add('course-block');
            courseBlock.setAttribute('data-section-code', sectionCode);
            courseBlock.style.position = 'relative';
            courseBlock.style.backgroundColor = '#d1ecf1'; // Light blue background
            courseBlock.style.border = '1px solid #bee5eb'; // Border color
            courseBlock.style.borderRadius = '5px';
            courseBlock.style.padding = '5px 10px';
            courseBlock.style.marginBottom = '5px';
            courseBlock.style.cursor = 'pointer';
            courseBlock.style.transition = 'background-color 0.3s';
            courseBlock.style.height = '100%'; // Fill the cell vertically

            // Create the delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.classList.add('delete-course-btn');
            deleteBtn.setAttribute('aria-label', 'Remove Course');
            deleteBtn.innerHTML = '&times;'; // HTML entity for multiplication sign (×)
            deleteBtn.style.position = 'absolute';
            deleteBtn.style.top = '2px';
            deleteBtn.style.right = '5px';
            deleteBtn.style.background = 'none';
            deleteBtn.style.border = 'none';
            deleteBtn.style.color = '#dc3545'; // Bootstrap danger color
            deleteBtn.style.fontWeight = 'bold';
            deleteBtn.style.cursor = 'pointer';
            deleteBtn.style.fontSize = '1rem';
            deleteBtn.style.lineHeight = '1';

            // Populate course information
            const courseInfo = document.createElement('div');
            courseInfo.innerHTML = `<strong>${courseCode}</strong> - ${location}<br><small>${startTime} - ${endTime}</small>`;

            // Assemble the course block
            courseBlock.appendChild(deleteBtn);
            courseBlock.appendChild(courseInfo);

            // Append to the container
            courseBlockContainer.appendChild(courseBlock);
        }
    }

    /**
     * Event listener for semester selection changes
     */
    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value; // Values are 'Fall', 'Winter', 'Summer'
            loadUserSchedule();
            // Clear any selected course when semester changes
            if (selectedCourse) {
                removeSelectedCourseFunction();
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
                    .catch(err => {
                        console.error('Error fetching courses:', err);
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
            suggestionItem.className = 'list-group-item list-group-item-action';
            suggestionItem.textContent = `${course.code} - ${course.name}`;
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
        selectedCourse = course;
        selectedCourseText.textContent = `${course.code} - ${course.name}`;
        selectedCourseContainer.style.display = 'block';

        // Disable the input and semester select
        courseSearchInput.value = `${course.code} - ${course.name}`;
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
     * Event listener for confirming the addition of a course
     */
    confirmAddCourseButton.addEventListener('click', function () {
        if (!selectedCourse) {
            showToast('No course selected to add.', 'warning');
            return;
        }

        // Implement the logic to add the course to the user's schedule
        // Send a POST request to add_course_to_schedule.php with section_code
        fetch(`../api/add_course_to_schedule.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                section_code: selectedCourse.section_code, // Ensure section_code is available
                semester: selectedSemester
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Course ${selectedCourse.code} - ${selectedCourse.name} added successfully!`, 'success');
                    loadUserSchedule();
                    removeSelectedCourseFunction();
                } else {
                    // Handle specific error when course is already completed
                    if (data.error.includes('already completed')) {
                        showToast(data.error, 'error');
                    } else {
                        showToast(data.error || 'Failed to add course.', 'error');
                    }
                }
            })
            .catch(err => {
                console.error('Error adding course:', err);
                showToast('Error adding course. Please try again.', 'error');
            });
    });

    /**
     * Function to handle course deletion
     * @param {number} sectionCode - The unique code of the section enrollment
     * @param {HTMLElement} courseBlock - The DOM element representing the course block
     */
    function removeCourseFromSchedule(sectionCode, courseBlock) {
        // Confirmation handled here
        if (!confirm('Are you sure you want to remove this course from your schedule?')) {
            return;
        }

        fetch(`../api/delete_course.php`, { // Correct relative path
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ section_code: sectionCode })
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
     * Function to attach delete event listeners to all delete buttons
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
            const pdf = new jspdf.jsPDF('p', 'mm', 'a4');

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
     * Event listener for PDF download button
     */
    if (downloadPdfButton) {
        downloadPdfButton.addEventListener('click', downloadScheduleAsPDF);
    } else {
        console.error('Download PDF button not found.');
    }

    // Initial load of user schedule
    loadUserSchedule();
});
