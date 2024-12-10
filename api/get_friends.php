<?php
// public/get_friends.php

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

// Fetch current friends
$stmt = $conn->prepare("
    SELECT s.student_id, s.fname, s.lname
    FROM FriendsWith fw
    JOIN students s ON fw.student_id2 = s.student_id
    WHERE fw.student_id1 = ?
    UNION
    SELECT s.student_id, s.fname, s.lname
    FROM FriendsWith fw
    JOIN students s ON fw.student_id1 = s.student_id
    WHERE fw.student_id2 = ?
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$res_friends = $stmt->get_result();
$friendsList = [];
while ($row = $res_friends->fetch_assoc()) {
    $friendsList[] = [
        "student_id" => $row['student_id'],
        "name" => htmlspecialchars($row['fname'] . ' ' . $row['lname'])
    ];
}
$stmt->close();

echo json_encode(["success" => true, "friends" => $friendsList]);
?>
