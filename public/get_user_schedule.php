<?php
include '../includes/config.php';
include '../includes/functions.php';

session_start();
// Ensure user is logged in, assuming user_id is stored in session
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$semesterName = $_GET['semester'] ?? 'Fall';

// Find semester_id from semesterName
$sql_sem = "SELECT id FROM semesters WHERE name=? ORDER BY year LIMIT 1";
$stmt_sem = $conn->prepare($sql_sem);
$stmt_sem->bind_param("s", $semesterName);
$stmt_sem->execute();
$res_sem = $stmt_sem->get_result();
if ($res_sem->num_rows === 0) {
    echo json_encode([]);
    exit();
}
$semester_id = $res_sem->fetch_assoc()['id'];

// Now fetch user's chosen schedule
$sql = "
SELECT co.day_of_week, co.start_time, co.end_time, co.location, c.code
FROM users_schedules us
JOIN course_offerings co ON us.course_offering_id = co.id
JOIN courses c ON co.course_id = c.id
WHERE us.user_id = ? AND us.semester_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $semester_id);
$stmt->execute();
$result = $stmt->get_result();

$offerings = [];
while ($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}

header('Content-Type: application/json');
echo json_encode($offerings);
