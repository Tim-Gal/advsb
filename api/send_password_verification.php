<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$stmt = $conn->prepare("SELECT email FROM students WHERE student_id = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'User email not found.']);
    exit();
}

$verification_code = generateVerificationCode();

$upd = $conn->prepare("UPDATE students SET password_change_code=? WHERE student_id=?");
if (!$upd) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}
$upd->bind_param("si", $verification_code, $user_id);
if (!$upd->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to store verification code.']);
    exit();
}
$upd->close();

$subject = "Verify Your Password Change";
$verification_message = "Hello,\n\nYou have requested to change your password. Please use the following verification code to confirm the change:\n\n$verification_code\n\nIf you did not request this, please ignore this email.";

$headers = "From: noreply@yourdomain.com\r\n"; 

if (mail($email, $subject, $verification_message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Verification code sent to your email.']);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send verification email.']);
    exit();
}
?>
