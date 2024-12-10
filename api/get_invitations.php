<?php
// public/get_invitations.php

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

// Fetch incoming friend requests
$stmt = $conn->prepare("
    SELECT fr.id, s.student_id, s.fname, s.lname, s.email, fr.created_at
    FROM FriendRequests fr
    JOIN students s ON fr.sender_id = s.student_id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
    ORDER BY fr.created_at DESC
");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $invitations = [];
    while ($row = $result->fetch_assoc()) {
        $invitations[] = [
            "request_id" => $row['id'],
            "student_id" => $row['student_id'],
            "name" => htmlspecialchars($row['fname'] . ' ' . $row['lname']),
            "email" => htmlspecialchars($row['email']),
            "created_at" => $row['created_at']
        ];
    }

    echo json_encode(["success" => true, "invitations" => $invitations]);
    $stmt->close();
} else {
    echo json_encode(["error" => "Database error"]);
}
?>
