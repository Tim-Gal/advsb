<?php
// public/get_user_schedule.php
include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$requested_user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
if (!ctype_digit($requested_user_id)) {
    echo json_encode(["error"=>"Invalid user_id"]);
    exit();
}
$requested_user_id = (int)$requested_user_id;

// In the new schema, 'semesterName' must match `sections.semester` directly
$semesterName = $_GET['semester'] ?? 'F24'; 
// Ensure the frontend sends something like 'F24', 'W25', etc. If 'Fall', 'Winter', 'Summer' are used,
// you must map them to actual semester codes stored in `sections.semester`.
// For now, assume the user provides a correct code that matches `sections.semester`.

$sql = "
SELECT l.day_of_week, l.start_time, l.end_time, l.location, c.course_code AS code
FROM coursesEnrolled ce
JOIN sections s ON ce.section_code = s.section_code
JOIN courses c ON s.course_code = c.course_code
JOIN lectures l ON l.section_code = s.section_code
WHERE ce.student_id = ?
  AND s.semester = ?
";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $requested_user_id, $semesterName);
$stmt->execute();
$result = $stmt->get_result();

$offerings = [];
while ($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}

echo json_encode($offerings);
