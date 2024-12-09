<?php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$semester = isset($_GET['semester']) ? trim($_GET['semester']) : '';

// Validate inputs
if (empty($query) || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters.']);
    exit();
}

$sql = "
    SELECT c.course_code AS code, c.course_name AS name
    FROM courses c
    WHERE (c.course_code LIKE ? OR c.course_name LIKE ?) AND c.semester = ?
    LIMIT 20
";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Handle statement preparation error
    echo json_encode(['error' => 'Database query preparation failed.']);
    exit();
}

$searchTerm = '%' . $query . '%';
$stmt->bind_param("sss", $searchTerm, $searchTerm, $semester);
$stmt->execute();
$result = $stmt->get_result();

// Fetch matching courses
$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Check if any courses were found
if (empty($courses)) {
    echo json_encode(['error' => 'No courses found matching your query.']);
    exit();
}

// Return the courses as JSON
echo json_encode($courses);
?>
