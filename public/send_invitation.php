<?php
// public/send_invitation.php

include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'] ?? '';

// Validate friend_id
if (!ctype_digit($friend_id)) {
    echo json_encode(["error" => "Invalid friend_id"]);
    exit();
}

$friend_id = (int)$friend_id;

// Prevent users from sending friend requests to themselves
if ($friend_id === $user_id) {
    echo json_encode(["error" => "You cannot send a friend request to yourself"]);
    exit();
}

// Check if a friendship already exists
$student_id1 = min($user_id, $friend_id);
$student_id2 = max($user_id, $friend_id);

$friend_stmt = $conn->prepare("SELECT * FROM FriendsWith WHERE student_id1 = ? AND student_id2 = ?");
$friend_stmt->bind_param("ii", $student_id1, $student_id2);
$friend_stmt->execute();
$friend_stmt->store_result();

if ($friend_stmt->num_rows > 0) {
    $friend_stmt->close();
    echo json_encode(["error" => "You are already friends with this user"]);
    exit();
}
$friend_stmt->close();

// Check if a friend request already exists (either sent or received)
$request_stmt = $conn->prepare("SELECT * FROM FriendRequests WHERE 
    (sender_id = ? AND receiver_id = ?) OR 
    (sender_id = ? AND receiver_id = ?) AND status = 'pending'");
$request_stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$request_stmt->execute();
$request_stmt->store_result();

if ($request_stmt->num_rows > 0) {
    $request_stmt->close();
    echo json_encode(["error" => "A friend request is already pending between you and this user"]);
    exit();
}
$request_stmt->close();

// Insert the friend request
$insert_stmt = $conn->prepare("INSERT INTO FriendRequests (sender_id, receiver_id) VALUES (?, ?)");
if (!$insert_stmt) {
    echo json_encode(["error" => "Database error"]);
    exit();
}
$insert_stmt->bind_param("ii", $user_id, $friend_id);

if ($insert_stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Friend request sent successfully"]);
} else {
    echo json_encode(["error" => "Failed to send friend request"]);
}
$insert_stmt->close();
?>
