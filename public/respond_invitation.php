<?php
// public/respond_invitation.php

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
$request_id = $_POST['request_id'] ?? '';
$action = $_POST['action'] ?? '';

// Validate request_id and action
if (!ctype_digit($request_id)) {
    echo json_encode(["error" => "Invalid request_id"]);
    exit();
}

$allowed_actions = ['accept', 'reject'];
if (!in_array(strtolower($action), $allowed_actions)) {
    echo json_encode(["error" => "Invalid action"]);
    exit();
}

$request_id = (int)$request_id;
$action = strtolower($action);

// Fetch the friend request
$stmt = $conn->prepare("SELECT sender_id, receiver_id, status FROM FriendRequests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$stmt->bind_result($sender_id, $receiver_id, $status);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(["error" => "Friend request not found"]);
    exit();
}
$stmt->close();

// Ensure the current user is the receiver
if ($receiver_id !== $user_id) {
    echo json_encode(["error" => "You are not authorized to respond to this friend request"]);
    exit();
}

// Ensure the request is pending
if ($status !== 'pending') {
    echo json_encode(["error" => "This friend request has already been responded to"]);
    exit();
}

if ($action === 'accept') {
    // Begin transaction
    $conn->begin_transaction();
    try {
        // Update the FriendRequests status
        $update_stmt = $conn->prepare("UPDATE FriendRequests SET status = 'accepted' WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Failed to prepare statement");
        }
        $update_stmt->bind_param("i", $request_id);
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update friend request status");
        }
        $update_stmt->close();

        // Insert into FriendsWith (only one entry to maintain symmetry)
        $student_id1 = min($sender_id, $receiver_id);
        $student_id2 = max($sender_id, $receiver_id);

        $insert_stmt = $conn->prepare("INSERT INTO FriendsWith (student_id1, student_id2) VALUES (?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Failed to prepare statement for friendship");
        }
        $insert_stmt->bind_param("ii", $student_id1, $student_id2);
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create friendship");
        }
        $insert_stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode(["success" => true, "message" => "Friend request accepted"]);
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(["error" => "Failed to accept friend request"]);
    }
} elseif ($action === 'reject') {
    // Update the FriendRequests status to 'rejected'
    $stmt = $conn->prepare("UPDATE FriendRequests SET status = 'rejected' WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $request_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Friend request rejected"]);
        } else {
            echo json_encode(["error" => "Failed to reject friend request"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["error" => "Database error"]);
    }
}
?>
