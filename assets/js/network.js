/* assets/js/network.js */

document.addEventListener('DOMContentLoaded', function () {
    const userSearchInput = document.getElementById('userSearchInput');
    const searchUserButton = document.getElementById('searchUserButton');
    const userSearchResults = document.getElementById('userSearchResults');
    const incomingInvitations = document.getElementById('incomingInvitations');
    const friendScheduleModal = new bootstrap.Modal(document.getElementById('friendScheduleModal'));
    const friendScheduleTable = document.getElementById('friendScheduleTable');
    const friendScheduleModalLabel = document.getElementById('friendScheduleModalLabel');

    /**
     * Function to fetch and display incoming friend requests
     */
    function loadIncomingInvitations() {
        fetch('get_invitations.php')
            .then(response => response.json())
            .then(data => {
                incomingInvitations.innerHTML = '';
                if (data.success && data.invitations.length > 0) {
                    data.invitations.forEach(invitation => {
                        const listItem = document.createElement('div');
                        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        
                        const infoDiv = document.createElement('div');
                        infoDiv.innerHTML = `<strong>${invitation.name}</strong> (${invitation.email})`;

                        const buttonsDiv = document.createElement('div');
                        const acceptBtn = document.createElement('button');
                        acceptBtn.className = 'btn btn-sm btn-success me-2';
                        acceptBtn.textContent = 'Accept';
                        acceptBtn.dataset.requestId = invitation.request_id;
                        acceptBtn.addEventListener('click', respondInvitation);

                        const rejectBtn = document.createElement('button');
                        rejectBtn.className = 'btn btn-sm btn-danger';
                        rejectBtn.textContent = 'Reject';
                        rejectBtn.dataset.requestId = invitation.request_id;
                        rejectBtn.addEventListener('click', respondInvitation);

                        buttonsDiv.appendChild(acceptBtn);
                        buttonsDiv.appendChild(rejectBtn);

                        listItem.appendChild(infoDiv);
                        listItem.appendChild(buttonsDiv);

                        incomingInvitations.appendChild(listItem);
                    });
                } else {
                    incomingInvitations.innerHTML = '<p class="text-muted">No incoming friend requests.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching invitations:', error);
                incomingInvitations.innerHTML = '<p class="text-danger">Failed to load invitations.</p>';
            });
    }

    /**
     * Function to handle accepting or rejecting invitations
     */
    function respondInvitation(event) {
        const requestId = event.target.dataset.requestId;
        const action = event.target.textContent.toLowerCase();

        fetch('respond_invitation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `request_id=${encodeURIComponent(requestId)}&action=${encodeURIComponent(action)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadIncomingInvitations();
                loadFriendsList();
                showToast(data.message, 'success');
            } else {
                showToast(data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Error responding to invitation:', error);
            showToast('Failed to respond to the invitation.', 'danger');
        });
    }

    /**
     * Function to search for users
     */
    function searchUsers() {
        const query = userSearchInput.value.trim();
        if (query.length < 2) {
            userSearchResults.innerHTML = '';
            userSearchResults.style.display = 'none';
            return;
        }

        fetch(`search_users.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                userSearchResults.innerHTML = '';
                if (data.success && data.users.length > 0) {
                    data.users.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        
                        const userInfo = document.createElement('div');
                        userInfo.innerHTML = `<strong>${user.name}</strong> (${user.email})`;

                        const actionBtn = document.createElement('button');
                        actionBtn.className = 'btn btn-sm';
                        if (user.status === 'friends') {
                            actionBtn.classList.add('btn-secondary');
                            actionBtn.textContent = 'Friends';
                            actionBtn.disabled = true;
                        } else {
                            actionBtn.classList.add('btn-primary');
                            actionBtn.textContent = 'Add Friend';
                            actionBtn.dataset.friendId = user.student_id;
                            actionBtn.addEventListener('click', sendFriendRequest);
                        }

                        item.appendChild(userInfo);
                        item.appendChild(actionBtn);

                        userSearchResults.appendChild(item);
                    });
                    userSearchResults.style.display = 'block';
                } else {
                    userSearchResults.innerHTML = '<p class="text-muted">No users found.</p>';
                    userSearchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching users:', error);
                userSearchResults.innerHTML = '<p class="text-danger">Error searching users.</p>';
                userSearchResults.style.display = 'block';
            });
    }

    /**
     * Function to send a friend request
     */
    function sendFriendRequest(event) {
        const friendId = event.target.dataset.friendId;

        fetch('send_invitation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `friend_id=${encodeURIComponent(friendId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                searchUsers(); // Refresh search results
                showToast(data.message, 'success');
                loadFriendsList(); // Refresh friends list
            } else {
                showToast(data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Error sending friend request:', error);
            showToast('Failed to send friend request.', 'danger');
        });
    }

    /**
     * Function to load the friends list
     */
    function loadFriendsList() {
        fetch('get_friends.php')
            .then(response => response.json())
            .then(data => {
                const friendsListContainer = document.querySelector('.friends-list-container');
                if (data.success) {
                    if (data.friends.length > 0) {
                        let listHtml = '<ul class="list-group">';
                        data.friends.forEach(friend => {
                            listHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>${friend.name}</div>
                                    <div>
                                        <button class="btn btn-sm btn-info viewFriendScheduleBtn" data-friend-id="${friend.student_id}">View Schedule</button>
                                        <button class="btn btn-sm btn-danger removeFriendBtn" data-friend-id="${friend.student_id}">Remove Friend</button>
                                    </div>
                                </li>
                            `;
                        });
                        listHtml += '</ul>';
                        friendsListContainer.innerHTML = listHtml;
                    } else {
                        friendsListContainer.innerHTML = '<p>You have no friends yet.</p>';
                    }
                } else {
                    friendsListContainer.innerHTML = `<p class="text-danger">${data.error}</p>`;
                }
            })
            .catch(error => {
                console.error('Error loading friends list:', error);
                const friendsListContainer = document.querySelector('.friends-list-container');
                friendsListContainer.innerHTML = '<p class="text-danger">Failed to load friends list.</p>';
            });
    }

    /**
     * Function to handle viewing a friend's schedule
     */
    function viewFriendSchedule(event) {
        const friendId = event.target.dataset.friendId;

        // Fetch the friend's schedule
        fetch(`get_friend_schedule.php?friend_id=${encodeURIComponent(friendId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear existing schedule
                    friendScheduleTable.querySelector('tbody').innerHTML = '';

                    // Populate schedule
                    data.schedule.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${row.time}</td>
                            <td>${row.Monday || ''}</td>
                            <td>${row.Tuesday || ''}</td>
                            <td>${row.Wednesday || ''}</td>
                            <td>${row.Thursday || ''}</td>
                            <td>${row.Friday || ''}</td>
                        `;
                        friendScheduleTable.querySelector('tbody').appendChild(tr);
                    });

                    // Update modal title
                    const friendName = data.friend_name;
                    friendScheduleModalLabel.textContent = `${friendName}'s Schedule`;

                    // Show the modal
                    friendScheduleModal.show();
                } else {
                    showToast(data.error, 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching friend schedule:', error);
                showToast('Failed to fetch friend schedule.', 'danger');
            });
    }

    /**
     * Function to remove a friend
     */
    function removeFriend(event) {
        const friendId = event.target.dataset.friendId;

        if (!confirm('Are you sure you want to remove this friend?')) {
            return;
        }

        fetch(`remove_friend.php?friend_id=${encodeURIComponent(friendId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadFriendsList();
                    showToast('Friend removed successfully.', 'success');
                } else {
                    showToast(data.error, 'danger');
                }
            })
            .catch(error => {
                console.error('Error removing friend:', error);
                showToast('Failed to remove friend.', 'danger');
            });
    }

    /**
     * Function to show toast notifications
     * @param {string} message 
     * @param {string} type - 'success', 'danger', 'info', 'warning'
     */
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        // Create the toast element
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-bg-${type} border-0`;
        toastEl.role = 'alert';
        toastEl.ariaLive = 'assertive';
        toastEl.ariaAtomic = 'true';
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        // Initialize and show the toast
        const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
        bsToast.show();

        // Remove the toast after it hides
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    /**
     * Event listeners
     */
    searchUserButton.addEventListener('click', searchUsers);
    userSearchInput.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            searchUsers();
        }
    });

    // Event delegation for dynamically added buttons
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('viewFriendScheduleBtn')) {
            viewFriendSchedule(event);
        }
        if (event.target.classList.contains('removeFriendBtn')) {
            removeFriend(event);
        }
    });

    // Initial load
    loadIncomingInvitations();
    loadFriendsList();
});
