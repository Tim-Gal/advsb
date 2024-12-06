<?php
include '../includes/config.php'; 
$semester = $_GET['semester'];
$query = $_GET['query'];

$sql = "SELECT code, name FROM courses 
        WHERE code LIKE ? OR name LIKE ?
        LIMIT 10";
$stmt = $conn->prepare($sql);
$like = "%{$query}%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$courses = [];
while($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
header('Content-Type: application/json');
echo json_encode($courses);
