<?php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');



if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$friend_id = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;
$semester = isset($_GET['semester']) ? strtoupper(trim($_GET['semester'])) : '';

if (empty($friend_id) || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit();
}



$sql = "
    SELECT COUNT(*) as count
    FROM friendswith
    WHERE (student_id1 = ? AND student_id2 = ?) OR 
    (student_id1 = ? AND student_id2 = ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed in view_friend_schedule.php: (" . $conn->errno . ") " . $conn->error);
    echo json_encode(['error' => 'An internal server error occurred. Please try again later.']);
    exit();
}


$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$friendship = $result->fetch_assoc();
$stmt->close();

if ($friendship['count'] == 0) {
    echo json_encode(['error' => 'You are not friends with this user.']);
    exit();
}


$sql = "
    SELECT c.course_code, c.course_name, l.day_of_week, l.start_time, l.end_time, l.location FROM coursesenrolled ce
    JOIN sections s ON ce.section_code = s.section_code
    JOIN courses c ON s.course_code = c.course_code
    JOIN lectures l ON l.section_code = s.section_code
    WHERE ce.student_id = ? AND s.semester = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed in view_friend_schedule.php: (" . $conn->errno . ") " . $conn->error);
    echo json_encode(['error' => 'An internal server error occurred. Please try again later.']);
    exit();
}

$stmt->bind_param("is", $friend_id, $semester);
$stmt->execute();
$result = $stmt->get_result();

$schedule = [];

while ($row = $result->fetch_assoc()) {
    $schedule[] = [
        'course_code' => $row['course_code'],
        'course_name' => $row['course_name'],
        'day_of_week' => $row['day_of_week'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time'],
        'location' => $row['location']
    ];
    

}
    

$stmt->close();

echo json_encode(['success' => true, 'schedule' => $schedule]);
