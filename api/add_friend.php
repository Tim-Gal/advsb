<?php
// public/add_friend.php
include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id']; // This should match a row in students(student_id)

$friend_id = $_GET['friend_id'] ?? '';
if (!ctype_digit($friend_id)) {
    echo json_encode(["error" => "Invalid friend_id"]);
    exit();
}

$friend_id = (int)$friend_id;
if ($friend_id == $user_id) {
    echo json_encode(["error" => "Cannot befriend yourself"]);
    exit();
}

// Check if already friends
$check = $conn->prepare("SELECT student_id1, student_id2 FROM FriendsWith WHERE student_id1=? AND student_id2=?");
$check->bind_param("ii", $user_id, $friend_id);
$check->execute();
$checkRes = $check->get_result();
if ($checkRes->num_rows > 0) {
    echo json_encode(["error" => "Already friends"]);
    exit();
}

$ins = $conn->prepare("INSERT INTO FriendsWith (student_id1, student_id2) VALUES (?,?)");
$ins->bind_param("ii", $user_id, $friend_id);
$ins->execute();

if ($ins->affected_rows > 0) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to add friend"]);
}
