<?php
// public/network.php

$pageTitle = "Network";
$pageCSS = [
    '../assets/css/global.css',
    '../assets/css/network.css'
];
$pageJS = [
    '../assets/js/network.js'
];

include '../includes/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current friends
$sql_friends = "
SELECT s.student_id, s.fname, s.lname
FROM FriendsWith fw
JOIN students s ON (
    (fw.student_id1 = ? AND fw.student_id2 = s.student_id)
)
OR (
    (fw.student_id2 = ? AND fw.student_id1 = s.student_id)
)
";

$stmt_friends = $conn->prepare($sql_friends);
$stmt_friends->bind_param("ii", $user_id, $user_id);
$stmt_friends->execute();
$res_friends = $stmt_friends->get_result();
$friendsList = [];
while ($row = $res_friends->fetch_assoc()) {
    $friendsList[] = $row;
}
$stmt_friends->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content is handled in header.php -->
</head>
<body>
    <div class="main-content network-container container py-5">
        <h1 class="mb-4">Network</h1>
        
        <!-- Search for Friends -->
        <div class="search-container mb-5">
            <h3>Search and Add Friends</h3>
            <div class="input-group">
                <input type="text" id="userSearchInput" class="form-control" placeholder="Enter name or email...">
                <button id="searchUserButton" class="btn btn-primary">Search</button>
            </div>
            <div id="userSearchResults" class="suggestions-dropdown mt-2"></div>
        </div>

        <!-- Incoming Friend Invitations -->
        <div class="invitations-container mb-5">
            <h3>Incoming Friend Requests</h3>
            <div id="incomingInvitations" class="list-group">
                <!-- Friend requests will be dynamically loaded here -->
            </div>
        </div>

        <!-- Your Friends List -->
        <div class="friends-list-container">
            <h3>Your Friends</h3>
            <?php if (count($friendsList) > 0): ?>
                <ul class="list-group">
                    <?php foreach ($friendsList as $friend): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><?php echo htmlspecialchars($friend['fname'] . ' ' . $friend['lname']); ?></div>
                            <div>
                                <button class="btn btn-sm btn-info viewFriendScheduleBtn" data-friend-id="<?php echo $friend['student_id']; ?>">View Schedule</button>
                                <button class="btn btn-sm btn-danger removeFriendBtn" data-friend-id="<?php echo $friend['student_id']; ?>">Remove Friend</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You have no friends yet.</p>
            <?php endif; ?>
        </div>

        <!-- Friend's Schedule Modal -->
        <div class="modal fade" id="friendScheduleModal" tabindex="-1" aria-labelledby="friendScheduleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="friendScheduleModalLabel">Friend's Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered schedule-table" id="friendScheduleTable">
                            <thead class="table-dark">
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
            </div>
        </div>

    </div>

    <script src="../assets/js/network.js"></script>
</body>
</html>
<?php
include '../includes/footer.php';
?>
