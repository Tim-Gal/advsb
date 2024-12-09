<?php
// public/add_friend.php

include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'] ?? '';

if (!ctype_digit($friend_id)) {
    echo json_encode(["error" => "Invalid friend_id"]);
    exit();
}

$friend_id = (int)$friend_id;

if ($friend_id === $user_id) {
    echo json_encode(["error" => "Cannot add yourself as a friend"]);
    exit();
}

// Determine the order
$student_id1 = min($user_id, $friend_id);
$student_id2 = max($user_id, $friend_id);

// Check if already friends
$stmt = $conn->prepare("SELECT * FROM FriendsWith WHERE student_id1 = ? AND student_id2 = ?");
$stmt->bind_param("ii", $student_id1, $student_id2);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(["error" => "You are already friends with this user"]);
    exit();
}
$stmt->close();

// Insert the friendship
$ins = $conn->prepare("INSERT INTO FriendsWith (student_id1, student_id2) VALUES (?, ?)");
$ins->bind_param("ii", $student_id1, $student_id2);

if ($ins->execute()) {
    echo json_encode(["success" => true, "message" => "Friend added successfully"]);
} else {
    echo json_encode(["error" => "Failed to add friend"]);
}
$ins->close();
?>
