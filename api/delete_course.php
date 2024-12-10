<?php

ob_start();

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$section_code = isset($input['section_code']) ? intval($input['section_code']) : 0;

if ($section_code <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid section code.']);
    exit();
}

$sql_check = "SELECT COUNT(*) as count FROM coursesenrolled WHERE section_code = ? AND student_id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    error_log("Prepare failed in delete_course.php: (" . $conn->errno . ") " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'An internal server error occurred. Please try again later.']);
    exit();
}

$stmt_check->bind_param("ii", $section_code, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$stmt_check->close();

if ($row_check['count'] == 0) {
    echo json_encode(['success' => false, 'error' => 'Course not found in your schedule.']);
    exit();
}

$sql_delete = "DELETE FROM coursesenrolled WHERE section_code = ? AND student_id = ?";
$stmt_delete = $conn->prepare($sql_delete);
if (!$stmt_delete) {
    error_log("Prepare failed in delete_course.php: (" . $conn->errno . ") " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'An internal server error occurred. Please try again later.']);
    exit();
}

$stmt_delete->bind_param("ii", $section_code, $user_id);
if ($stmt_delete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Course removed successfully.']);
} else {
    error_log("Execution failed in delete_course.php: (" . $stmt_delete->errno . ") " . $stmt_delete->error);
    echo json_encode(['success' => false, 'error' => 'Failed to remove the course. Please try again.']);
}

$stmt_delete->close();

ob_end_flush();
?>
