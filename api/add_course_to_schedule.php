<?php
include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$semesterName = trim($_GET['semester'] ?? '');
$courseCode = strtoupper(trim($_GET['code'] ?? ''));

// Log received parameters for debugging
error_log("Received courseCode: $courseCode, semesterName: $semesterName");

if (empty($semesterName) || empty($courseCode)) {
    echo json_encode(["error" => "Invalid parameters."]);
    exit();
}

$sql_prerequisites = "
    SELECT p.prerequisite_course_code
    FROM prerequisiteof p
    WHERE LOWER(p.course_code) = LOWER(?)
";
$stmt = $conn->prepare($sql_prerequisites);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching prerequisites."]);
    exit();
}
$stmt->bind_param("s", $courseCode);
$stmt->execute();
$res_prerequisites = $stmt->get_result();

while ($row = $res_prerequisites->fetch_assoc()) {
    $prerequisite = $row['prerequisite_course_code'];

    $sql_check_completed = "
        SELECT * 
        FROM coursescompleted 
        WHERE student_id = ? AND LOWER(course_code) = LOWER(?)
    ";
    $check_stmt = $conn->prepare($sql_check_completed);
    if (!$check_stmt) {
        error_log("Query preparation failed: " . $conn->error);
        echo json_encode(["error" => "Database error while checking prerequisites."]);
        exit();
    }
    $check_stmt->bind_param("is", $user_id, $prerequisite);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();

    if ($check_res->num_rows === 0) {
        echo json_encode(["error" => "Missing prerequisite: $prerequisite"]);
        exit();
    }
}

$sql_sections = "
    SELECT section_code
    FROM sections
    WHERE LOWER(course_code) = LOWER(?) AND LOWER(semester) = LOWER(?)
";

$stmt = $conn->prepare($sql_sections);
if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    echo json_encode(["error" => "Database error while fetching sections."]);
    exit();
}

$stmt->bind_param("ss", $courseCode, $semesterName);
$stmt->execute();
$res_sections = $stmt->get_result();

$section_count = $res_sections->num_rows;
error_log("Number of sections found: $section_count for courseCode: $courseCode and semester: $semesterName");

if ($section_count === 0) {
    echo json_encode(["error" => "No sections available for this course in this semester."]);
    exit();
}

$insertCount = 0;

while ($row = $res_sections->fetch_assoc()) {
    $section_code = $row['section_code'];

    $sql_check_enrollment = "
        SELECT * 
        FROM coursesenrolled 
        WHERE student_id = ? AND section_code = ?
    ";
    $check_stmt = $conn->prepare($sql_check_enrollment);
    if (!$check_stmt) {
        error_log("Query preparation failed: " . $conn->error);
        continue;
    }
    $check_stmt->bind_param("ii", $user_id, $section_code);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();

    if ($check_res->num_rows === 0) {
        $sql_insert_enrollment = "
            INSERT INTO coursesenrolled (student_id, section_code)
            VALUES (?, ?)
        ";
        $insert_stmt = $conn->prepare($sql_insert_enrollment);
        if (!$insert_stmt) {
            error_log("Insert query preparation failed: " . $conn->error);
            continue;
        }
        $insert_stmt->bind_param("ii", $user_id, $section_code);
        $insert_stmt->execute();

        if ($insert_stmt->affected_rows > 0) {
            $insertCount++;
        }
    }
}

echo json_encode(["success" => true, "inserted" => $insertCount]);
?>
