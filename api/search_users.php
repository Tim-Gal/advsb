<?php
include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$query = $_GET['query'] ?? '';
if (strlen($query) < 1) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT student_id, fname, lname 
FROM students
WHERE (fname LIKE ? OR lname LIKE ? OR email LIKE ?)
LIMIT 10
";
$stmt = $conn->prepare($sql);
$like = "%$query%";
$stmt->bind_param("sss", $like, $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$users = [];
while ($row = $res->fetch_assoc()) {
    // exclude current user from results
    if ($row['student_id'] != $user_id) {
        $users[] = $row;
    }
}

echo json_encode($users);
