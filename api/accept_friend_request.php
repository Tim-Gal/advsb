<?php
// public/api/accept_friend_request.php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve and sanitize POST parameters
$data = json_decode(file_get_contents('php://input'), true);
$requester_id = isset($data['requester_id']) ? intval($data['requester_id']) : 0;

if (empty($requester_id)) {
    echo json_encode(['error' => 'Requester ID is required.']);
    exit();
}

// Check if there is a pending friend request from requester_id to user_id
$sql = "SELECT id, status FROM friendrequests WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ii", $requester_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No pending friend request found from this user.']);
    $stmt->close();
    exit();
}

$friendship = $result->fetch_assoc();
$friendship_id = $friendship['id'];
$stmt->close();

// Update the friendship status to 'accepted'
$sql = "UPDATE friendrequests SET status = 'accepted' WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $friendship_id);

if ($stmt->execute()) {
    // Insert into friendswith table for mutual friendship
    $sql_insert = "INSERT INTO friendswith (student_id1, student_id2) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit();
    }

    $stmt_insert->bind_param("ii", $requester_id, $user_id);
    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Friend request accepted.']);
    } else {
        echo json_encode(['error' => 'Failed to establish friendship.']);
    }
    $stmt_insert->close();
} else {
    echo json_encode(['error' => 'Failed to accept friend request.']);
}

$stmt->close();
?>
