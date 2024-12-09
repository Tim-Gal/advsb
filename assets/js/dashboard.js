/* assets/js/dashboard.js */

document.addEventListener('DOMContentLoaded', function () {
    const semesterRadios = document.querySelectorAll('input[name="semester"]');
    const courseSearchInput = document.getElementById('courseSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const addCourseButton = document.getElementById('addCourseButton');
    const selectedCourseContainer = document.getElementById('selectedCourseContainer');
    const selectedCourseText = document.getElementById('selectedCourseText');
    const removeSelectedCourse = document.getElementById('removeSelectedCourse');
    const confirmAddCourseButton = document.getElementById('confirmAddCourse');

    let selectedSemester = 'FALL'; // Stored in uppercase to match database
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
     * (Assuming this function works correctly and is unrelated to the current issue)
     */
    function loadUserSchedule() {
        fetch('../api/get_user_schedule.php?semester=' + encodeURIComponent(selectedSemester))
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

    /**
     * Clear the existing schedule table
     */
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
                block.style.borderRadius = '4px';
                block.style.fontSize = '0.9em';
                cell.appendChild(block);
            }
        }
    }

    /**
     * Event listener for semester selection changes
     */
    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value.toUpperCase(); // Ensure uppercase
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
        // For example, send a POST request to add_course_to_schedule.php
        fetch(`../api/add_course_to_schedule.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                course_code: selectedCourse.code,
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
                    showToast(data.error || 'Failed to add course.', 'error');
                }
            })
            .catch(err => {
                console.error('Error adding course:', err);
                showToast('Error adding course. Please try again.', 'error');
            });
    });

    /**
     * Function to populate the schedule table with course blocks
     * (Assuming this function works correctly)
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
                block.style.borderRadius = '4px';
                block.style.fontSize = '0.9em';
                cell.appendChild(block);
            }
        }
    }

    /**
     * Function to initialize Google Map
     * (Assuming this function works correctly and is unrelated to the current issue)
     */
    function initMap(locations) {
        // Default center (e.g., University Main Campus)
        // Replace with the central point relevant to your institution
        const defaultCenter = { lat: 40.7128, lng: -74.0060 }; // Example: New York City

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: defaultCenter
        });

        // Add markers for each location
        locations.forEach(location => {
            if (location.latitude && location.longitude) {
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) },
                    map: map,
                    title: location.name // Assuming each location has a 'name' property
                });

                // Add info windows
                const infoWindow = new google.maps.InfoWindow({
                    content: `<strong>${location.name}</strong><br>${location.address}`
                });

                marker.addListener('click', function () {
                    infoWindow.open(map, marker);
                });
            }
        });

        // Adjust map bounds to fit all markers
        const bounds = new google.maps.LatLngBounds();
        locations.forEach(location => {
            if (location.latitude && location.longitude) {
                bounds.extend({ lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) });
            }
        });
        map.fitBounds(bounds);
    }

    /**
     * Event listener for Google Maps Button
     * (Assuming this works correctly and is unrelated to the current issue)
     */
    const mapButton = document.getElementById('mapButton');
    const mapsModal = new bootstrap.Modal(document.getElementById('mapsModal'), {
        keyboard: false
    });

    let mapInitialized = false;
    let map;

    mapButton.addEventListener('click', function () {
        // Fetch class locations from the server
        fetch(`../api/get_class_locations.php?semester=${encodeURIComponent(selectedSemester)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch class locations.');
                }
                return response.json();
            })
            .then(locations => {
                if (locations.error) {
                    showToast(locations.error, 'error');
                    return;
                }

                // Initialize map if not already done
                if (!mapInitialized) {
                    initMap(locations);
                    mapInitialized = true;
                } else {
                    // Re-center and add markers if map is already initialized
                    initMap(locations);
                }

                // Show the modal
                mapsModal.show();
            })
            .catch(err => {
                console.error('Error fetching class locations:', err);
                showToast('Error fetching class locations. Please try again.', 'error');
            });
    });

    /**
     * Download PDF Functionality
     * (Assuming this works correctly and is unrelated to the current issue)
     */
    const downloadPdfButton = document.getElementById('downloadPdfButton');

    downloadPdfButton.addEventListener('click', function () {
        // Use html2canvas to capture the schedule table
        const scheduleTable = document.getElementById('scheduleTable');

        html2canvas(scheduleTable, { scale: 2 }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape', 'pt', 'a4');

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth - 20, pdfHeight - 20);
            pdf.save('my_schedule.pdf');
        }).catch(err => {
            console.error('Error generating PDF:', err);
            showToast('Error generating PDF. Please try again.', 'error');
        });
    });

    /**
     * Initialize the user's schedule upon page load
     */
    loadUserSchedule();
});
