<?php
include '../includes/config.php';

$code = $_GET['code'];
$semesterName = $_GET['semester'];

$sql_sem = "SELECT id FROM semesters WHERE name=? ORDER BY year LIMIT 1";
$stmt_sem = $conn->prepare($sql_sem);
$stmt_sem->bind_param("s", $semesterName);
$stmt_sem->execute();
$res_sem = $stmt_sem->get_result();
$semester_id = $res_sem->fetch_assoc()['id'];

$sql = "SELECT co.day_of_week, co.start_time, co.end_time, co.location
        FROM course_offerings co
        JOIN courses c ON co.course_id = c.id
        WHERE c.code = ? AND co.semester_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $code, $semester_id);
$stmt->execute();
$result = $stmt->get_result();
$offerings = [];
while($row = $result->fetch_assoc()) {
    $offerings[] = $row;
}
header('Content-Type: application/json');
echo json_encode($offerings);
