document.addEventListener('DOMContentLoaded', function () {
    const friendsList = document.getElementById('friendsList');
    const pendingReceivedList = document.getElementById('pendingReceivedList');
    const sendFriendInput = document.getElementById('sendFriendInput');
    const sendFriendButton = document.getElementById('sendFriendButton');
    const sendFriendFeedback = document.getElementById('sendFriendFeedback');

    let currentTimeout = null;
    let isNotificationVisible = false;

    const viewScheduleModal = new bootstrap.Modal(document.getElementById('viewScheduleModal'), {
        keyboard: false
    });
    const friendSemesterSelect = document.getElementById('friendSemesterSelect');
    const friendScheduleList = document.getElementById('friendScheduleList');

    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
        keyboard: false
    });
    const confirmationModalLabel = document.getElementById('confirmationModalLabel');
    const confirmationModalBody = document.getElementById('confirmationModalBody');
    const confirmActionButton = document.getElementById('confirmActionButton');

    let selectedAction = null; // store action to be confirmed
    let selectedFriendData = {}; // store data related to the action
    let selectedFriendId = null; // store the ID of the friend whose schedule is being viewed


    function getFriendsAndRequests() {
        fetch('../api/get_friends.php') // Ensure this path is correct
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateFriendsList(data.friends);
                    populatePendingFriendsList(data.pending_received);
                    // Optionally, handle pending_sent if you want to display sent requests
                } else {
                    displayNotification(data.error || 'Failed to fetch friends.', 'error');
                }
            })
            .catch(err => {
                console.error('Error fetching friends:', err);
                displayNotification('Error fetching friends. Please try again.', 'error');
            });
    }


    // takes array as input parameter
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

    // takes array as input parameter
    function populatePendingFriendsList(pending) {
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

            // accept Friend Request Button
            const acceptButton = document.createElement('button');
            acceptButton.className = 'btn btn-sm btn-success me-2';
            acceptButton.textContent = 'Accept';
            acceptButton.addEventListener('click', () => {
                selectedAction = 'acceptFriendRequest';
                selectedFriendData = { id: request.id, name: request.name };
                confirmationModalLabel.textContent = 'Confirm Acceptance';
                confirmationModalBody.textContent = `Do you want to accept the friend request from ${request.name}?`;
                confirmActionButton.textContent = 'Accept';
                confirmActionButton.classList.remove('btn-danger');
                confirmActionButton.classList.add('btn-primary');
                confirmationModal.show();
            });

            // add "Reject" button here?

            buttonsDiv.appendChild(acceptButton);
            listItem.appendChild(buttonsDiv);
            pendingReceivedList.appendChild(listItem);
        });
    }

    function sendFriendRequest() {
        const receiverUsername = sendFriendInput.value.trim();

        if (receiverUsername === '') {
            displayNotification('Please enter an email to send a friend request.', 'warning');
            return;
        }

        // simple email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(receiverUsername)) {
            displayNotification('Please enter a valid email address.', 'warning');
            return;
        }

        // disable button to prevent multiple clicks
        sendFriendButton.disabled = true;
        sendFriendFeedback.innerHTML = '';

        fetch('../api/send_friend_request.php', { // ensure this path is correct
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ receiver_username: receiverUsername })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayNotification(data.message, 'success');
                    sendFriendInput.value = '';
                    getFriendsAndRequests();
                } else {
                    displayNotification(data.error || 'Failed to send friend request.', 'error');
                }
            })
            .catch(err => {
                console.error('Error sending friend request:', err);
                displayNotification('Error sending friend request. Please try again.', 'error');
            })
            .finally(() => {
                sendFriendButton.disabled = false;
            });
    }

    function viewFriendSchedule(friendId, friendName) {
        selectedFriendId = friendId;
        document.getElementById('viewScheduleModalLabel').textContent = `${friendName}'s Schedule`;
        friendSemesterSelect.value = '';
        friendScheduleList.innerHTML = '';
        viewScheduleModal.show();
    }

    function getFriendSchedule() {
        const semester = friendSemesterSelect.value;
        console.log(`Selected Semester: ${semester}`);
        if (semester === '') {
            displayNotification('Please select a semester to view the schedule.', 'warning');
            return;
        }

        // Show loading spinner
        document.getElementById('scheduleLoading').style.display = 'block';
        // Clear existing schedule
        friendScheduleList.innerHTML = '';

        console.log(`Fetching schedule for Friend ID: ${selectedFriendId}, Semester: ${semester}`);

        fetch(`../api/view_friend_schedule.php?friend_id=${encodeURIComponent(selectedFriendId)}&semester=${encodeURIComponent(semester)}`)
            .then(res => res.json())
            .then(data => {
                console.log('API Response:', data);
                if (data.success) {
                    populateFriendSchedule(data.schedule);
                } else {
                    displayNotification(data.error || 'Failed to fetch friend\'s schedule.', 'error');
                    friendScheduleList.innerHTML = `<div class="caution caution-danger">${data.error || 'Failed to fetch schedule.'}</div>`;
                }
            })
            .catch(err => {
                console.error('Error fetching friend\'s schedule:', err);
                displayNotification('Error fetching friend\'s schedule. Please try again.', 'error');
                friendScheduleList.innerHTML = '<div class="caution caution-danger">An error occurred while fetching the schedule.</div>';
            })
            .finally(() => {
                // Hide loading spinner
                document.getElementById('scheduleLoading').style.display = 'none';
            });
    }

    // takes array as input parameter
    function populateFriendSchedule(schedule) {
        console.log('Populating Schedule:', schedule);
        const friendScheduleList = document.getElementById('friendScheduleList');
        friendScheduleList.innerHTML = ''; // clears any existing content

        if (schedule.length === 0) {
            friendScheduleList.innerHTML = '<div class="alert alert-info">No courses scheduled for this semester.</div>';
            return;
        }

        // iterate through each course and create bs card for each one
        schedule.forEach(course => {
            const courseCard = document.createElement('div');
            courseCard.className = 'list-group-item list-group-item-action flex-column align-items-start mb-2';

            const courseHeader = document.createElement('div');
            courseHeader.className = 'd-flex w-100 justify-content-between';

            const courseTitle = document.createElement('h5');
            courseTitle.textContent = `${course.course_code} - ${course.course_name}`;

            const courseTime = document.createElement('small');
            courseTime.textContent = `${course.day_of_week} | ${course.start_time} - ${course.end_time}`;

            courseHeader.appendChild(courseTitle);
            courseHeader.appendChild(courseTime);

            const courseLocation = document.createElement('p');
            courseLocation.className = 'mb-1';
            courseLocation.textContent = `Location: ${course.location}`;

            courseCard.appendChild(courseHeader);
            courseCard.appendChild(courseLocation);

            friendScheduleList.appendChild(courseCard);
        });
    }

    sendFriendButton.addEventListener('click', sendFriendRequest);


    // event listener for Enter key in Send Friend Request input
    sendFriendInput.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            sendFriendRequest();
        }
    });

    // listener for semester selection in view schedule modal
    friendSemesterSelect.addEventListener('change', getFriendSchedule);


    // event listener in confirmation modal
    confirmActionButton.addEventListener('click', function () {
        if (!selectedAction || !selectedFriendData.id) {
            displayNotification('No action selected.', 'error');
            confirmationModal.hide();
            return;
        }

        if (selectedAction === 'removeFriend') {
            const friendId = selectedFriendData.id;
            const friendName = selectedFriendData.name;

            fetch('../api/remove_friend.php', { // ensure this path is correct
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ friend_id: friendId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayNotification(data.message, 'success');
                        getFriendsAndRequests();
                    } else {
                        displayNotification(data.error || 'Failed to remove friend.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error removing friend:', err);
                    displayNotification('Error removing friend. Please try again.', 'error');
                })
                .finally(() => {
                    selectedAction = null;
                    selectedFriendData = {};
                    confirmationModal.hide();
                });

        } else if (selectedAction === 'acceptFriendRequest') {
            const requesterId = selectedFriendData.id;
            const requesterName = selectedFriendData.name;

            fetch('../api/accept_friend_request.php', { // ensure this path is correct
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ requester_id: requesterId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayNotification(data.message, 'success');
                        getFriendsAndRequests();
                    } else {
                        displayNotification(data.error || 'Failed to accept friend request.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error accepting friend request:', err);
                    displayNotification('Error accepting friend request. Please try again.', 'error');
                })
                .finally(() => {
                    selectedAction = null;
                    selectedFriendData = {};
                    confirmationModal.hide();
                });
        } else {
            displayNotification('Unknown action.', 'error');
            confirmationModal.hide();
        }
    });


    function displayNotification(message, type = 'success') {
        // Clear any existing timeout
        if (currentTimeout) {
            clearTimeout(currentTimeout);
            currentTimeout = null;
        }

        const notification = document.getElementById('notification');
        const notificationText = document.getElementById('notificationText');
        const notificationClose = document.getElementById('notificationClose');

        // If a hide animation is in progress, wait for it to complete
        if (notification.classList.contains('hide')) {
            notification.addEventListener('animationend', function handler() {
                notification.removeEventListener('animationend', handler);
                showNewNotification();
            }, { once: true });
        } else {
            showNewNotification();
        }

        function showNewNotification() {
            // Remove all existing classes
            notification.classList.remove('success', 'error', 'warning', 'info', 'hide');

            // Add the appropriate type class and show class
            notification.classList.add(type);
            notification.classList.add('show');

            // Set the message
            notificationText.textContent = message;

            // Set new timeout
            currentTimeout = setTimeout(() => {
                hideNotification();
            }, 5000);

            // Update visibility flag
            isNotificationVisible = true;
        }

        // Close button handler
        notificationClose.onclick = () => {
            if (currentTimeout) {
                clearTimeout(currentTimeout);
                currentTimeout = null;
            }
            hideNotification();
        };
    }

    function hideNotification() {
        const notification = document.getElementById('notification');

        // Only proceed if notification is actually visible
        if (!isNotificationVisible) return;

        notification.classList.add('hide');
        isNotificationVisible = false;

        // Clear any existing timeout
        if (currentTimeout) {
            clearTimeout(currentTimeout);
            currentTimeout = null;
        }

        // Remove classes after animation completes
        notification.addEventListener('animationend', function handler() {
            notification.removeEventListener('animationend', handler);
            notification.classList.remove('show', 'hide');
        }, { once: true });
    }


    // initial fetch of friends and pending requests
    getFriendsAndRequests();
});
