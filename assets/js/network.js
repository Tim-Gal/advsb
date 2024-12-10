/* assets/js/network.js */

document.addEventListener('DOMContentLoaded', function () {
    const friendsList = document.getElementById('friendsList');
    const pendingReceivedList = document.getElementById('pendingReceivedList');
    const sendFriendInput = document.getElementById('sendFriendInput');
    const sendFriendButton = document.getElementById('sendFriendButton');
    const sendFriendFeedback = document.getElementById('sendFriendFeedback');
    const notificationToast = new bootstrap.Toast(document.getElementById('notificationToast'));
    const toastBody = document.getElementById('toastBody');

    const viewScheduleModal = new bootstrap.Modal(document.getElementById('viewScheduleModal'), {
        keyboard: false
    });
    const friendSemesterSelect = document.getElementById('friendSemesterSelect');
    const friendScheduleTableBody = document.querySelector('#friendScheduleTable tbody');

    // Confirmation Modal Elements
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
        keyboard: false
    });
    const confirmationModalLabel = document.getElementById('confirmationModalLabel');
    const confirmationModalBody = document.getElementById('confirmationModalBody');
    const confirmActionButton = document.getElementById('confirmActionButton');

    let selectedAction = null; // To store the action to be confirmed
    let selectedFriendData = {}; // To store data related to the action

    /**
     * Function to display toast notifications
     * @param {string} message 
     * @param {string} type - 'success', 'error', 'warning', 'info'
     */
    function showToast(message, type = 'primary') {
        const toastElement = document.getElementById('notificationToast');
        const toastBody = document.getElementById('toastBody');

        // Remove existing bg classes
        toastElement.classList.remove('bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info');

        // Add new bg class based on type
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

        // Set the message
        toastBody.textContent = message;

        // Show the toast
        notificationToast.show();
    }

    /**
     * Function to fetch and display friends and pending requests
     */
    function fetchFriends() {
        fetch('../api/get_friends.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateFriendsList(data.friends);
                    populatePendingReceivedList(data.pending_received);
                    // Optionally, handle pending_sent if you want to display sent requests
                } else {
                    showToast(data.error || 'Failed to fetch friends.', 'error');
                }
            })
            .catch(err => {
                console.error('Error fetching friends:', err);
                showToast('Error fetching friends. Please try again.', 'error');
            });
    }

    /**
     * Function to populate the Friends List
     * @param {Array} friends 
     */
    function populateFriendsList(friends) {
        friendsList.innerHTML = '';
        if (friends.length === 0) {
            friendsList.innerHTML = '<li class="list-group-item">You have no friends yet.</li>';
            return;
        }

        friends.forEach(friend => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.textContent = friend.name;

            const buttonsDiv = document.createElement('div');

            // View Schedule Button
            const viewButton = document.createElement('button');
            viewButton.className = 'btn btn-sm btn-info me-2';
            viewButton.textContent = 'View Schedule';
            viewButton.addEventListener('click', () => {
                viewFriendSchedule(friend.id, friend.name);
            });

            // Remove Friend Button
            const removeButton = document.createElement('button');
            removeButton.className = 'btn btn-sm btn-danger';
            removeButton.textContent = 'Remove Friend';
            removeButton.addEventListener('click', () => {
                // Set the action and data
                selectedAction = 'removeFriend';
                selectedFriendData = { id: friend.id, name: friend.name };
                // Configure and show the confirmation modal
                confirmationModalLabel.textContent = 'Confirm Removal';
                confirmationModalBody.textContent = `Are you sure you want to remove ${friend.name} from your friends?`;
                confirmActionButton.textContent = 'Remove';
                confirmActionButton.classList.remove('btn-primary');
                confirmActionButton.classList.add('btn-danger');
                confirmationModal.show();
            });

            buttonsDiv.appendChild(viewButton);
            buttonsDiv.appendChild(removeButton);
            listItem.appendChild(buttonsDiv);

            friendsList.appendChild(listItem);
        });
    }

    /**
     * Function to populate the Pending Received Friend Requests List
     * @param {Array} pending 
     */
    function populatePendingReceivedList(pending) {
        pendingReceivedList.innerHTML = '';
        if (pending.length === 0) {
            pendingReceivedList.innerHTML = '<li class="list-group-item">No pending friend requests.</li>';
            return;
        }

        pending.forEach(request => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.textContent = request.name;

            const buttonsDiv = document.createElement('div');

            // Accept Friend Request Button
            const acceptButton = document.createElement('button');
            acceptButton.className = 'btn btn-sm btn-success me-2';
            acceptButton.textContent = 'Accept';
            acceptButton.addEventListener('click', () => {
                // Set the action and data
                selectedAction = 'acceptFriendRequest';
                selectedFriendData = { id: request.id, name: request.name };
                // Configure and show the confirmation modal
                confirmationModalLabel.textContent = 'Confirm Acceptance';
                confirmationModalBody.textContent = `Do you want to accept the friend request from ${request.name}?`;
                confirmActionButton.textContent = 'Accept';
                confirmActionButton.classList.remove('btn-danger');
                confirmActionButton.classList.add('btn-primary');
                confirmationModal.show();
            });

            // Optionally, you can add a "Reject" button here

            buttonsDiv.appendChild(acceptButton);
            listItem.appendChild(buttonsDiv);
            pendingReceivedList.appendChild(listItem);
        });
    }

    /**
     * Function to send a friend request
     */
    function sendFriendRequest() {
        const receiverUsername = sendFriendInput.value.trim();

        if (receiverUsername === '') {
            showToast('Please enter an email to send a friend request.', 'warning');
            return;
        }

        // Simple email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(receiverUsername)) {
            showToast('Please enter a valid email address.', 'warning');
            return;
        }

        // Disable the button to prevent multiple clicks
        sendFriendButton.disabled = true;
        sendFriendFeedback.innerHTML = '';

        fetch('../api/send_friend_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ receiver_username: receiverUsername })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    sendFriendInput.value = '';
                    fetchFriends();
                } else {
                    showToast(data.error || 'Failed to send friend request.', 'error');
                }
            })
            .catch(err => {
                console.error('Error sending friend request:', err);
                showToast('Error sending friend request. Please try again.', 'error');
            })
            .finally(() => {
                sendFriendButton.disabled = false;
            });
    }

    /**
     * Function to accept a friend request
     * @param {number} requesterId 
     * @param {string} requesterName 
     */
    function acceptFriendRequest(requesterId, requesterName) {
        // This function is no longer directly called; confirmation is handled via the modal
        // The actual logic is handled in the confirmActionButton event listener
    }

    /**
     * Function to remove a friend
     * @param {number} friendId 
     * @param {string} friendName 
     */
    function removeFriend(friendId, friendName) {
        // This function is no longer directly called; confirmation is handled via the modal
        // The actual logic is handled in the confirmActionButton event listener
    }

    /**
     * Function to view a friend's schedule
     * @param {number} friendId 
     * @param {string} friendName 
     */
    function viewFriendSchedule(friendId, friendName) {
        selectedFriendId = friendId;
        document.getElementById('viewScheduleModalLabel').textContent = `${friendName}'s Schedule`;
        friendSemesterSelect.value = '';
        friendScheduleTableBody.innerHTML = '';
        viewScheduleModal.show();
    }

    /**
     * Function to fetch and display a friend's schedule based on selected semester
     */
    function fetchFriendSchedule() {
        const semester = friendSemesterSelect.value;
        if (semester === '') {
            showToast('Please select a semester to view the schedule.', 'warning');
            return;
        }

        // Clear existing schedule
        friendScheduleTableBody.innerHTML = '';

        fetch(`../api/view_friend_schedule.php?friend_id=${encodeURIComponent(selectedFriendId)}&semester=${encodeURIComponent(semester)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    populateFriendSchedule(data.schedule);
                } else {
                    showToast(data.error || 'Failed to fetch friend\'s schedule.', 'error');
                }
            })
            .catch(err => {
                console.error('Error fetching friend\'s schedule:', err);
                showToast('Error fetching friend\'s schedule. Please try again.', 'error');
            });
    }

    /**
     * Function to populate the friend's schedule table
     * @param {Array} schedule 
     */
    function populateFriendSchedule(schedule) {
        if (schedule.length === 0) {
            friendScheduleTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No courses scheduled for this semester.</td></tr>';
            return;
        }

        // Initialize a map to track schedule blocks
        const scheduleMap = {};

        schedule.forEach(course => {
            const day = course.day_of_week;
            const startHour = parseInt(course.start_time.split(':')[0]);
            const endHour = parseInt(course.end_time.split(':')[0]);

            for (let h = startHour; h < endHour; h++) {
                const cellClass = `${day}-${h}`;
                if (!scheduleMap[cellClass]) {
                    scheduleMap[cellClass] = [];
                }
                scheduleMap[cellClass].push({
                    course_code: course.course_code,
                    course_name: course.course_name,
                    start_time: course.start_time,
                    end_time: course.end_time,
                    location: course.location
                });
            }
        });

        // Populate the table
        for (let row of friendScheduleTableBody.parentElement.children) {
            if (row.tagName.toLowerCase() !== 'tr') continue;
            const timeCell = row.querySelector('.time-cell');
            const time = timeCell.textContent.trim();
            const hour = parseInt(time.split(':')[0]);

            ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].forEach(day => {
                const cellClass = `${day}-${hour}`;
                const cell = row.querySelector(`.${cellClass}`);
                if (scheduleMap[cellClass]) {
                    scheduleMap[cellClass].forEach(course => {
                        const block = document.createElement('div');
                        block.className = 'course-block';
                        block.textContent = `${course.course_code} - ${course.course_name}\n${course.location}\n(${course.start_time}-${course.end_time})`;
                        block.style.whiteSpace = 'pre-wrap';
                        block.style.backgroundColor = '#FFC107';
                        block.style.margin = '2px 0';
                        block.style.padding = '5px';
                        block.style.border = '1px solid #ccc';
                        block.style.borderRadius = '4px';
                        block.style.fontSize = '0.9em';
                        cell.appendChild(block);
                    });
                }
            });
        }
    }

    /**
     * Event listener for Send Friend Request button
     */
    sendFriendButton.addEventListener('click', sendFriendRequest);

    /**
     * Event listener for Enter key in Send Friend Request input
     */
    sendFriendInput.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            sendFriendRequest();
        }
    });

    /**
     * Event listener for Semester Selection in View Schedule Modal
     */
    friendSemesterSelect.addEventListener('change', fetchFriendSchedule);

    /**
     * Event listener for Confirm Action Button in Confirmation Modal
     */
    confirmActionButton.addEventListener('click', function () {
        if (!selectedAction || !selectedFriendData.id) {
            showToast('No action selected.', 'error');
            confirmationModal.hide();
            return;
        }

        if (selectedAction === 'removeFriend') {
            // Proceed with removing the friend
            const friendId = selectedFriendData.id;
            const friendName = selectedFriendData.name;

            fetch('../api/remove_friend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ friend_id: friendId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        fetchFriends();
                    } else {
                        showToast(data.error || 'Failed to remove friend.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error removing friend:', err);
                    showToast('Error removing friend. Please try again.', 'error');
                })
                .finally(() => {
                    // Reset the action and data
                    selectedAction = null;
                    selectedFriendData = {};
                    confirmationModal.hide();
                });

        } else if (selectedAction === 'acceptFriendRequest') {
            // Proceed with accepting the friend request
            const requesterId = selectedFriendData.id;
            const requesterName = selectedFriendData.name;

            fetch('../api/accept_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ requester_id: requesterId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        fetchFriends();
                    } else {
                        showToast(data.error || 'Failed to accept friend request.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error accepting friend request:', err);
                    showToast('Error accepting friend request. Please try again.', 'error');
                })
                .finally(() => {
                    // Reset the action and data
                    selectedAction = null;
                    selectedFriendData = {};
                    confirmationModal.hide();
                });
        } else {
            showToast('Unknown action.', 'error');
            confirmationModal.hide();
        }
    });

    // Initial fetch of friends and pending requests
    fetchFriends();
});
