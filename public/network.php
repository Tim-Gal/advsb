<?php

$pageTitle = "Network";
$pageCSS = [
    '../assets/css/network.css'
];


include '../includes/header.php';

$user_id = $_SESSION['user_id'];

$sql_friends = "
SELECT s.student_id, s.fname, s.lname
FROM FriendsWith fw
JOIN students s ON fw.student_id2 = s.student_id
WHERE fw.student_id1 = $user_id
";
$res_friends = $conn->query($sql_friends);
$friendsList = [];
while ($row = $res_friends->fetch_assoc()) {
    $friendsList[] = $row;
}
?>

<div class="network-container">
    <h1>Network</h1>
    <p>Search for other students to add as friends:</p>
    <div class="search-container">
        <input type="text" id="userSearchInput" placeholder="Enter name or email...">
        <button id="searchUserButton">Search</button>
        <div id="userSearchResults" class="suggestions-dropdown"></div>
    </div>

    <h3>Your Friends</h3>
    <?php if (count($friendsList) > 0): ?>
        <ul class="friends-list">
            <?php foreach ($friendsList as $friend): ?>
                <li>
                    <?php echo htmlspecialchars($friend['fname'].' '.$friend['lname']); ?>
                    <button class="viewFriendScheduleBtn" data-friend-id="<?php echo $friend['student_id']; ?>">View Schedule</button>
                    <button class="removeFriendBtn" data-friend-id="<?php echo $friend['student_id']; ?>">Remove Friend</button>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no friends yet.</p>
    <?php endif; ?>

    <h3>Friend's Schedule</h3>
    <div id="friendScheduleContainer">
        <p>Select a friend and click "View Schedule" to see their schedule.</p>
        <table class="schedule-table" id="friendScheduleTable" style="display:none;">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $hours = range(8, 18);
                foreach ($hours as $h) {
                    $timeLabel = sprintf("%02d:00", $h);
                    echo "<tr>";
                    echo "<td class='time-cell'>$timeLabel</td>";
                    echo "<td class='Mon-$h day-cell'></td>";
                    echo "<td class='Tue-$h day-cell'></td>";
                    echo "<td class='Wed-$h day-cell'></td>";
                    echo "<td class='Thu-$h day-cell'></td>";
                    echo "<td class='Fri-$h day-cell'></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../assets/js/network.js"></script>

<?php
include '../includes/footer.php';
?>
