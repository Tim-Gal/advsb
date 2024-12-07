document.addEventListener('DOMContentLoaded', function() {
    const userSearchInput = document.getElementById('userSearchInput');
    const searchUserButton = document.getElementById('searchUserButton');
    const userSearchResults = document.getElementById('userSearchResults');

    const friendScheduleTable = document.getElementById('friendScheduleTable');

    searchUserButton.addEventListener('click', function() {
        const query = userSearchInput.value.trim();
        if (query.length >= 1) {
            fetch('search_users.php?query=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(users => {
                    userSearchResults.innerHTML = '';
                    if (users.length > 0) {
                        userSearchResults.style.display = 'block';
                        users.forEach(u => {
                            const item = document.createElement('div');
                            item.textContent = u.fname + ' ' + u.lname; // Adjust field names if needed
                            item.addEventListener('click', () => {
                                fetch('add_friend.php?friend_id=' + u.student_id)
                                    .then(r => r.json())
                                    .then(d => {
                                        if (d.error) {
                                            alert(d.error);
                                            return;
                                        }
                                        location.reload();
                                    });
                                userSearchResults.style.display = 'none';
                            });
                            userSearchResults.appendChild(item);
                        });
                    } else {
                        userSearchResults.style.display = 'none';
                    }
                });
        } else {
            userSearchResults.style.display='none';
        }
    });

    document.querySelectorAll('.viewFriendScheduleBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const friendId = this.getAttribute('data-friend-id');
            // Clear friend schedule first
            friendScheduleTable.querySelectorAll('.course-block').forEach(b => b.remove());

            // Load their schedule
            // Assuming semester is 'F24' or similar. Hard-coded or add a semester selector
            fetch('get_user_schedule.php?user_id=' + friendId + '&semester=F24')
                .then(r => r.json())
                .then(offerings => {
                    friendScheduleTable.style.display='table';
                    offerings.forEach(o => {
                        populateTableBlock(friendScheduleTable, o.course_code, o.day_of_week, o.start_time, o.end_time, o.location);
                    });
                });
        });
    });

    document.querySelectorAll('.removeFriendBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const friendId = this.getAttribute('data-friend-id');
            fetch('remove_friend.php?friend_id=' + friendId)
                .then(r => r.json())
                .then(d => {
                    if (d.error) {alert(d.error); return;}
                    location.reload();
                })
        });
    });

    function populateTableBlock(tableElem, courseCode, day, startTime, endTime, location) {
        const startHour = parseInt(startTime.split(':')[0]);
        const endHour = parseInt(endTime.split(':')[0]);
        for (let h = startHour; h < endHour; h++) {
            const cellClass = `${day}-${h}`;
            const cell = tableElem.querySelector(`.${cellClass}`);
            if (cell) {
                const block = document.createElement('div');
                block.className = 'course-block';
                block.textContent = `${courseCode}\n${location}\n(${startTime}-${endTime})`;
                cell.appendChild(block);
            }
        }
    }
});
