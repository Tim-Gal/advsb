<?php
// public/get_course_offerings.php

include '../includes/config.php';
include '../includes/functions.php';

// Start session if not already started (if required for authentication)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Retrieve and sanitize GET parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$semester = isset($_GET['semester']) ? trim($_GET['semester']) : '';

// Validate inputs
if (empty($query) || empty($semester)) {
    echo json_encode(['error' => 'Invalid parameters. Both query and semester are required.']);
    exit();
}

// Normalize semester input (e.g., convert to uppercase)
$semester = strtoupper($semester);

// Updated SQL Query to join courses and sections
$sql = "
    SELECT DISTINCT c.course_code AS code, c.course_name AS name
    FROM courses c
    JOIN sections s ON c.course_code = s.course_code
    WHERE (c.course_code LIKE ? OR c.course_name LIKE ?) AND s.semester = ?
    LIMIT 20
";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Handle statement preparation error
    echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
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

$stmt->close();

// Check if any courses were found
if (empty($courses)) {
    echo json_encode(['error' => 'No courses found matching your query for the specified semester.']);
    exit();
}

// Return the courses as JSON
echo json_encode(['success' => true, 'courses' => $courses]);
