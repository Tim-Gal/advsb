<?php
// public/api/get_course_offerings.php

include '../includes/config.php';
include '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Retrieve and sanitize input parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$semester = isset($_GET['semester']) ? trim($_GET['semester']) : '';

if (empty($query) || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters. Both query and semester are required.']);
    exit();
}

$semester = strtoupper($semester);

// Get the user ID from the session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Fetch completed courses to exclude them from search results
$completed_courses = [];
if ($user_id > 0) {
    $sql_completed = "
        SELECT course_code
        FROM coursescompleted
        WHERE student_id = ?
    ";
    $stmt_completed = $conn->prepare($sql_completed);
    if ($stmt_completed) {
        $stmt_completed->bind_param("i", $user_id);
        $stmt_completed->execute();
        $result_completed = $stmt_completed->get_result();
        while ($row_completed = $result_completed->fetch_assoc()) {
            $completed_courses[] = $row_completed['course_code'];
        }
        $stmt_completed->close();
    }
}

// Prepare the SQL query to search for courses
$sql = "
    SELECT DISTINCT c.course_code AS code, c.course_name AS name, s.section_code
    FROM courses c
    JOIN sections s ON c.course_code = s.course_code
    WHERE (c.course_code LIKE ? OR c.course_name LIKE ?) 
      AND UPPER(s.semester) = ?
";

// Add exclusion for completed courses if any
if (!empty($completed_courses)) {
    $placeholders = implode(',', array_fill(0, count($completed_courses), '?'));
    $sql .= " AND c.course_code NOT IN ($placeholders)";
}

$sql .= " LIMIT 20";

// Prepare the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
    exit();
}

// Bind parameters
$searchTerm = '%' . $query . '%';
$types = "sss" . str_repeat('s', count($completed_courses));
$params = [$searchTerm, $searchTerm, $semester];
if (!empty($completed_courses)) {
    foreach ($completed_courses as $cc) {
        $params[] = $cc;
    }
}
$stmt->bind_param($types, ...$params);

// Execute and fetch results
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();

// Return the results
if (empty($courses)) {
    echo json_encode(['error' => 'No courses found matching your query for the specified semester.']);
    exit();
}

echo json_encode(['success' => true, 'courses' => $courses]);
?>
