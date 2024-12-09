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

    // Event listener for semester selection changes
    semesterRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            selectedSemester = radio.value;
            loadUserSchedule();
        });
    });

    // Event listener for course search input
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
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
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

    // Event listener for adding a course to the schedule
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

    // Google Maps Functionality
    const mapButton = document.getElementById('mapButton');
    const mapsModal = new bootstrap.Modal(document.getElementById('mapsModal'), {
        keyboard: false
    });

    let mapInitialized = false;
    let map;

    // Function to initialize Google Map
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

    // Event listener for Google Maps Button
    mapButton.addEventListener('click', function () {
        // Fetch class locations from the server
        fetch('get_class_locations.php?semester=' + encodeURIComponent(selectedSemester))
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

    // Download PDF Functionality
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

    // Initial load of the user's schedule
    loadUserSchedule();
});
