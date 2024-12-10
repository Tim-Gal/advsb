<?php
// public/api/get_user_schedule.php

include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

// Determine the user ID to fetch schedule for
$requested_user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
if (!ctype_digit($requested_user_id)) {
    echo json_encode(["error" => "Invalid user_id"]);
    exit();
}
$requested_user_id = (int)$requested_user_id;

// Get the semester parameter, default to 'Fall'
$semesterName = trim($_GET['semester'] ?? 'Fall'); 

// Prepare the SQL query to fetch enrolled courses
$sql = "
SELECT 
    ce.section_code,
    l.day_of_week, 
    l.start_time, 
    l.end_time, 
    l.location, 
    c.course_code AS code
FROM coursesenrolled ce
JOIN sections s ON ce.section_code = s.section_code
JOIN courses c ON s.course_code = c.course_code
JOIN lectures l ON l.section_code = s.section_code
WHERE ce.student_id = ?
  AND LOWER(s.semester) = LOWER(?)
";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching schedule."]);
    exit();
}
$stmt->bind_param("is", $requested_user_id, $semesterName);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the course offerings
$offerings = [];
while ($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}

echo json_encode($offerings);
?>
