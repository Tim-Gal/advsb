<?php
// public/get_course_offerings.php
include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
$semesterName = $_GET['semester'] ?? '';

// Validate inputs
if (empty($code) || empty($semesterName)) {
    echo json_encode([]); 
    exit();
}

// In the new schema, we have no `semesters` table. `semester` is stored directly in `sections`.
$sql = "
SELECT l.day_of_week, l.start_time, l.end_time, l.location
FROM lectures l
JOIN sections s ON l.section_code = s.section_code
JOIN courses c ON s.course_code = c.course_code
WHERE c.course_code = ? AND s.semester = ?
";

// Prepare and execute statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $code, $semesterName);
$stmt->execute();
$result = $stmt->get_result();

$offerings = [];
while ($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}

echo json_encode($offerings);
