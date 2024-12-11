<?php
// api/delete_course.php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);

$section_code = isset($input['section_code']) ? intval($input['section_code']) : 0;

if ($section_code <= 0) {
    echo json_encode(['error' => 'Invalid section code.']);
    exit();
}

// Verify that the section exists and is enrolled by the user
$sql_verify = "
    SELECT COUNT(*) AS count
    FROM coursesenrolled
    WHERE student_id = ? AND section_code = ?
";
$stmt_verify = $conn->prepare($sql_verify);
if (!$stmt_verify) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_verify->bind_param("ii", $user_id, $section_code);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();
$row_verify = $result_verify->fetch_assoc();
$stmt_verify->close();

if ($row_verify['count'] === 0) {
    echo json_encode(['error' => 'Course not found in your enrolled courses.']);
    exit();
}

// Proceed to delete the course enrollment
$sql_delete = "
    DELETE FROM coursesenrolled
    WHERE student_id = ? AND section_code = ?
";
$stmt_delete = $conn->prepare($sql_delete);
if (!$stmt_delete) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_delete->bind_param("ii", $user_id, $section_code);

if ($stmt_delete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Course removed successfully.']);
} else {
    echo json_encode(['error' => 'Failed to remove the course. Please try again.']);
}

$stmt_delete->close();
$conn->close();
?>
