<?php
// public/api/add_course_to_schedule.php

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

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);
$section_code = isset($input['section_code']) ? intval($input['section_code']) : 0;
$semesterName = trim($input['semester'] ?? 'Fall');


if ($section_code <= 0 || empty($semesterName)) {
    echo json_encode(["error" => "Invalid parameters."]);
    exit();
}

// Check if the section exists and is available
$sqlCheck = "SELECT course_code FROM sections WHERE section_code = ? AND LOWER(semester) = LOWER(?)";
$stmtCheck = $conn->prepare($sqlCheck);
if (!$stmtCheck) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while checking section availability."]);
    exit();
}

$stmtCheck->bind_param("is", $section_code, $semesterName);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    $stmtCheck->close();
    echo json_encode(["error" => "Section not found or not available for the selected semester."]);
    exit();
}

$sectionData = $resultCheck->fetch_assoc();
$course_code = $sectionData['course_code'];

$stmtCheck->close();

// Check if the course is already enrolled
$sqlEnrolled = "SELECT * FROM coursesenrolled WHERE student_id = ? AND section_code = ?";
$stmtEnrolled = $conn->prepare($sqlEnrolled);
if (!$stmtEnrolled) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while checking enrollment."]);
    exit();
}

$stmtEnrolled->bind_param("ii", $user_id, $section_code);
$stmtEnrolled->execute();
$resultEnrolled = $stmtEnrolled->get_result();

if ($resultEnrolled->num_rows > 0) {
    $stmtEnrolled->close();
    echo json_encode(["error" => "You have already enrolled in this course section."]);
    exit();
}

$stmtEnrolled->close();

// **Start of Prerequisite Check**

/**
 * Fetch prerequisites for the course
 */
$sqlPrereq = "SELECT prerequisite_course_code FROM prerequisiteof WHERE course_code = ?";
$stmtPrereq = $conn->prepare($sqlPrereq);
if (!$stmtPrereq) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching prerequisites."]);
    exit();
}

$stmtPrereq->bind_param("s", $course_code);
$stmtPrereq->execute();
$resultPrereq = $stmtPrereq->get_result();

$prerequisites = [];
while ($row = $resultPrereq->fetch_assoc()) {
    $prerequisites[] = $row['prerequisite_course_code'];
}

$stmtPrereq->close();

/**
 * Check which prerequisites are missing
 */
$missingPrereqs = [];
if (!empty($prerequisites)) {
    // Prepare placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($prerequisites), '?'));
    $types = str_repeat('s', count($prerequisites));

    $sqlCompleted = "SELECT course_code FROM coursescompleted WHERE student_id = ? AND course_code IN ($placeholders)";
    $stmtCompleted = $conn->prepare($sqlCompleted);
    if (!$stmtCompleted) {
        error_log("Query preparation failed: " . $conn->error);
        echo json_encode(["error" => "Database error while checking completed prerequisites."]);
        exit();
    }

    // Dynamically bind parameters using call_user_func_array
    $types_bind = 'i' . $types;
    $params = array_merge([$user_id], $prerequisites);
    $bind_params = [$types_bind];

    foreach ($params as $param) {
        $bind_params[] = &$param; // Note: parameters must be passed by reference
    }

    call_user_func_array([$stmtCompleted, 'bind_param'], $bind_params);

    $stmtCompleted->execute();
    $resultCompleted = $stmtCompleted->get_result();

    $completedCourses = [];
    while ($row = $resultCompleted->fetch_assoc()) {
        $completedCourses[] = $row['course_code'];
    }

    $stmtCompleted->close();

    // Determine which prerequisites are missing
    foreach ($prerequisites as $prereq) {
        if (!in_array($prereq, $completedCourses)) {
            $missingPrereqs[] = $prereq;
        }
    }
}

// **End of Prerequisite Check**

/**
 * **Start of Conflict Check**
 * Fetch lectures for the new section and existing enrollments to check for schedule conflicts.
 */

/**
 * Function to check if two time intervals overlap
 * Returns true if overlapping, false otherwise
 */
function timesOverlap($day1, $start1, $end1, $day2, $start2, $end2) {
    if ($day1 !== $day2) {
        return false;
    }

    return ($start1 < $end2) && ($start2 < $end1);
}

/**
 * Fetch lectures for the new section
 */
$sqlNewLectures = "SELECT day_of_week, start_time, end_time FROM lectures WHERE section_code = ?";
$stmtNewLectures = $conn->prepare($sqlNewLectures);
if (!$stmtNewLectures) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching lectures for the new course."]);
    exit();
}

$stmtNewLectures->bind_param("i", $section_code);
$stmtNewLectures->execute();
$resultNewLectures = $stmtNewLectures->get_result();

$newLectures = [];
while ($row = $resultNewLectures->fetch_assoc()) {
    $newLectures[] = [
        'day_of_week' => $row['day_of_week'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time']
    ];
}

$stmtNewLectures->close();

/**
 * Fetch existing enrollments for the student in the same semester
 */
$sqlExistingEnrollments = "
    SELECT ce.section_code, c.course_code, c.course_name
    FROM coursesenrolled ce
    JOIN sections s ON ce.section_code = s.section_code
    JOIN courses c ON s.course_code = c.course_code
    WHERE ce.student_id = ? AND LOWER(s.semester) = LOWER(?)
";
$stmtExistingEnrollments = $conn->prepare($sqlExistingEnrollments);
if (!$stmtExistingEnrollments) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching existing enrollments."]);
    exit();
}

$stmtExistingEnrollments->bind_param("is", $user_id, $semesterName);
$stmtExistingEnrollments->execute();
$resultExistingEnrollments = $stmtExistingEnrollments->get_result();

$existingEnrollments = [];
while ($row = $resultExistingEnrollments->fetch_assoc()) {
    $existingEnrollments[] = [
        'section_code' => $row['section_code'],
        'course_code' => $row['course_code'],
        'course_name' => $row['course_name']
    ];
}

$stmtExistingEnrollments->close();

/**
 * Fetch lectures for existing enrollments
 */
$conflictingCourses = [];

foreach ($existingEnrollments as $enrollment) {
    $sqlExistingLectures = "SELECT day_of_week, start_time, end_time FROM lectures WHERE section_code = ?";
    $stmtExistingLectures = $conn->prepare($sqlExistingLectures);
    if (!$stmtExistingLectures) {
        error_log("Query preparation failed: " . $conn->error);
        continue; // Skip this enrollment if there's an error
    }

    $stmtExistingLectures->bind_param("i", $enrollment['section_code']);
    $stmtExistingLectures->execute();
    $resultExistingLectures = $stmtExistingLectures->get_result();

    while ($row = $resultExistingLectures->fetch_assoc()) {
        $existingLecture = [
            'day_of_week' => $row['day_of_week'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        ];

        // Compare with each new lecture
        foreach ($newLectures as $newLecture) {
            if (timesOverlap(
                $newLecture['day_of_week'],
                $newLecture['start_time'],
                $newLecture['end_time'],
                $existingLecture['day_of_week'],
                $existingLecture['start_time'],
                $existingLecture['end_time']
            )) {
                // Add to conflicting courses if not already added
                if (!in_array($enrollment['course_code'], array_column($conflictingCourses, 'course_code'))) {
                    $conflictingCourses[] = [
                        'course_code' => $enrollment['course_code'],
                        'course_name' => $enrollment['course_name']
                    ];
                }
            }
        }
    }

    $stmtExistingLectures->close();
}

/**
 * **End of Conflict Check**
 */

/**
 * Insert the enrollment
 */
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
    // Prepare the response
    $response = [
        "success" => true,
        "inserted" => 1
    ];

    // Add warning if prerequisites are missing
    if (!empty($missingPrereqs)) {
        // Fetch course names for missing prerequisites for better readability
        $placeholders = implode(',', array_fill(0, count($missingPrereqs), '?'));
        $types_prereq = str_repeat('s', count($missingPrereqs));
        $sqlCourseNames = "SELECT course_name FROM courses WHERE course_code IN ($placeholders)";
        $stmtCourseNames = $conn->prepare($sqlCourseNames);
        if ($stmtCourseNames) {
            // Dynamically bind parameters using call_user_func_array
            $types_bind_prereq = $types_prereq;
            $bind_course_params = [];
            $bind_course_params[] = $types_prereq;
            foreach ($missingPrereqs as $prereq) {
                $bind_course_params[] = &$prereq; // Pass by reference
            }

            call_user_func_array([$stmtCourseNames, 'bind_param'], $bind_course_params);

            $stmtCourseNames->execute();
            $resultCourseNames = $stmtCourseNames->get_result();
            $missingCourseNames = [];
            while ($row = $resultCourseNames->fetch_assoc()) {
                $missingCourseNames[] = $row['course_name'];
            }
            $stmtCourseNames->close();

            $response["warning"] = "You have not completed the following prerequisite(s): " . 
            implode(', ', array_map(function($code, $name) {
                return "$code - $name";
            }, $missingPrereqs, $missingCourseNames)) . ".";
                } else {
            // If fetching course names fails, return the codes
            $response["warning"] = "You have not completed the following prerequisite(s): " . implode(', ', $missingPrereqs) . ".";
        }
    }

    // Add conflict information if any
    if (!empty($conflictingCourses)) {
        $response["conflicts"] = $conflictingCourses; // Array of conflicting courses
    }

    echo json_encode($response);
} else {
    echo json_encode(["error" => "Failed to add course to schedule."]);
}

$stmt_insert->close();
?>
