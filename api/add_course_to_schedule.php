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

$input = json_decode(file_get_contents('php://input'), true);

$section_code = isset($input['section_code']) ? intval($input['section_code']) : 0;
$semester = isset($input['semester']) ? strtoupper(trim($input['semester'])) : '';

if ($section_code <= 0 || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit();
}

$sql_section = "
    SELECT course_code
    FROM sections
    WHERE section_code = ? AND LOWER(semester) = LOWER(?)
    LIMIT 1
";

$stmt_section = $conn->prepare($sql_section);
if (!$stmt_section) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_section->bind_param("is", $section_code, $semester);
$stmt_section->execute();
$result_section = $stmt_section->get_result();

if ($result_section->num_rows === 0) {
    echo json_encode(['error' => 'Course not found for the specified semester.']);
    $stmt_section->close();
    exit();
}

$section_data = $result_section->fetch_assoc();
$course_code = $section_data['course_code'];
$stmt_section->close();

$sql_completed = "
    SELECT COUNT(*) AS count
    FROM coursescompleted
    WHERE student_id = ? AND course_code = ?
";

$stmt_completed = $conn->prepare($sql_completed);
if (!$stmt_completed) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt_completed->bind_param("is", $user_id, $course_code);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
$row_completed = $result_completed->fetch_assoc();
$stmt_completed->close();

if ($row_completed['count'] > 0) {
    echo json_encode(['error' => 'You have already completed this course and cannot add it to your schedule.']);
    exit();
}

$sql = "
    SELECT s.section_code
    FROM sections s
    WHERE s.section_code = ? AND s.semester = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("is", $section_code, $semester);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Course section not found for the specified semester.']);
    $stmt->close();
    exit();
}

$stmt->close();

$sql_check = "
    SELECT COUNT(*) as count 
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
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$stmt_check->close();

if ($row_check['count'] > 0) {
    echo json_encode(['error' => 'You have already added this course to your schedule.']);
    exit();
}

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
