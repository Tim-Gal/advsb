<?php
// public/api/add_course_to_schedule.php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve and decode the JSON payload
$input = json_decode(file_get_contents('php://input'), true);

$course_code = isset($input['course_code']) ? trim($input['course_code']) : '';
$semester = isset($input['semester']) ? strtoupper(trim($input['semester'])) : '';

// Validate inputs
if (empty($course_code) || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit();
}

// Check if the course exists for the specified semester
$sql = "
    SELECT s.section_code
    FROM sections s
    WHERE s.course_code = ? AND s.semester = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $course_code, $semester);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Course not found for the specified semester.']);
    $stmt->close();
    exit();
}

$row = $result->fetch_assoc();
$section_code = $row['section_code'];
$stmt->close();

// Check if the course is already added to the user's schedule
$sql_check = "
    SELECT *
    FROM coursesenrolled
    WHERE student_id = ? AND section_code = ?
";

$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_check->bind_param("ii", $user_id, $section_code);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode(['error' => 'You have already added this course to your schedule.']);
    $stmt_check->close();
    exit();
}

$stmt_check->close();

// Add the course to the user's schedule
$sql_insert = "
    INSERT INTO coursesenrolled (student_id, section_code)
    VALUES (?, ?)
";

$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_insert->bind_param("ii", $user_id, $section_code);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'inserted' => 1]);
} else {
    echo json_encode(['error' => 'Failed to add course to schedule.']);
}

$stmt_insert->close();
?>
