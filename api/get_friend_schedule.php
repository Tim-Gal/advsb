<?php
// public/get_friend_schedule.php

include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'] ?? '';

// Validate friend_id
if (!ctype_digit($friend_id)) {
    echo json_encode(["error" => "Invalid friend_id"]);
    exit();
}

$friend_id = (int)$friend_id;

// Determine the order for FriendsWith table
$student_id1 = min($user_id, $friend_id);
$student_id2 = max($user_id, $friend_id);

// Verify that the friend_id is indeed a friend
$check_stmt = $conn->prepare("SELECT * FROM FriendsWith WHERE student_id1 = ? AND student_id2 = ?");
if (!$check_stmt) {
    echo json_encode(["error" => "Database error: Failed to prepare statement"]);
    exit();
}
$check_stmt->bind_param("ii", $student_id1, $student_id2);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    $check_stmt->close();
    echo json_encode(["error" => "User is not your friend"]);
    exit();
}
$check_stmt->close();

// Fetch friend's name
$name_stmt = $conn->prepare("SELECT fname, lname FROM students WHERE student_id = ? LIMIT 1");
if (!$name_stmt) {
    echo json_encode(["error" => "Database error: Failed to prepare name statement"]);
    exit();
}
$name_stmt->bind_param("i", $friend_id);
$name_stmt->execute();
$name_stmt->bind_result($fname, $lname);
if (!$name_stmt->fetch()) {
    $name_stmt->close();
    echo json_encode(["error" => "Friend not found"]);
    exit();
}
$friend_name = htmlspecialchars($fname . ' ' . $lname);
$name_stmt->close();

// Fetch friend's schedule
// Assuming you have a 'schedule' table with fields: student_id, day_of_week, start_time, end_time, course_name
$schedule_stmt = $conn->prepare("
    SELECT day_of_week, start_time, end_time, course_name
    FROM schedule
    WHERE student_id = ?
    ORDER BY start_time ASC
");
if (!$schedule_stmt) {
    echo json_encode(["error" => "Database error: Failed to prepare schedule statement"]);
    exit();
}
$schedule_stmt->bind_param("i", $friend_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

// Initialize schedule array
$schedule = [];

// Define days and hours for consistency
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$hours = range(8, 18); // 8 AM to 6 PM

// Initialize empty schedule slots
foreach ($hours as $h) {
    $timeLabel = sprintf("%02d:00", $h);
    $schedule[] = [
        "time" => $timeLabel,
        "Monday" => "",
        "Tuesday" => "",
        "Wednesday" => "",
        "Thursday" => "",
        "Friday" => ""
    ];
}

// Populate schedule
while ($row = $schedule_result->fetch_assoc()) {
    $day = $row['day_of_week'];
    $start_hour = (int)explode(':', $row['start_time'])[0];
    $end_hour = (int)explode(':', $row['end_time'])[0];
    $course = htmlspecialchars($row['course_name']);

    for ($h = $start_hour; $h < $end_hour; $h++) {
        foreach ($schedule as &$slot) {
            if ($slot['time'] === sprintf("%02d:00", $h)) {
                if (in_array($day, $days)) {
                    $slot[$day] = $course;
                }
                break;
            }
        }
        unset($slot);
    }
}

echo json_encode([
    "success" => true,
    "friend_name" => $friend_name,
    "schedule" => $schedule
]);

$schedule_stmt->close();
?>
