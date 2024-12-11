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

$user_id = $_SESSION['user_id'];

// Get the semester parameter, default to 'Fall'
$semesterName = trim($_GET['semester'] ?? 'Fall'); 

if (empty($semesterName)) {
    echo json_encode(["error" => "Semester parameter is required."]);
    exit();
}

// Prepare the SQL query to fetch enrolled courses and their lectures
$sql = "
SELECT 
    ce.section_code,
    l.day_of_week, 
    l.start_time, 
    l.end_time, 
    l.location, 
    c.course_code AS code,
    c.course_name AS course_name
FROM coursesenrolled ce
JOIN sections s ON ce.section_code = s.section_code
JOIN courses c ON s.course_code = c.course_code
JOIN lectures l ON l.section_code = s.section_code
WHERE ce.student_id = ?
  AND LOWER(s.semester) = LOWER(?)
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching schedule."]);
    exit();
}

$stmt->bind_param("is", $user_id, $semesterName);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the course offerings
$offerings = [];
while ($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}

$stmt->close();

// Return the offerings as JSON
echo json_encode($offerings);
?>
