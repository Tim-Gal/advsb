<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header("Location: ../public/settings.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    $_SESSION['error'] = 'User not logged in.';
    header("Location: ../public/login.php");
    exit();
}

$verification_code = sanitizeInput($_POST['verification_code'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($verification_code) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = 'All fields are required.';
    header("Location: ../public/settings.php");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: ../public/settings.php");
    exit();
}

// Verify the code
$stmt = $conn->prepare("SELECT password_change_code FROM students WHERE student_id = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['error'] = 'Database error: ' . $conn->error;
    header("Location: ../public/settings.php");
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($stored_code);
$stmt->fetch();
$stmt->close();

if ($verification_code !== $stored_code) {
    $_SESSION['error'] = 'Invalid verification code.';
    header("Location: ../public/settings.php");
    exit();
}

// Hash the new password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Update the password and clear the verification code
$upd = $conn->prepare("UPDATE students SET password_hash=?, password_change_code=NULL WHERE student_id=?");
if (!$upd) {
    $_SESSION['error'] = 'Database error: ' . $conn->error;
    header("Location: ../public/settings.php");
    exit();
}
$upd->bind_param("si", $password_hash, $user_id);
if ($upd->execute()) {
    $_SESSION['success'] = 'Password updated successfully.';
    header("Location: ../public/settings.php");
    exit();
} else {
    $_SESSION['error'] = 'Failed to update password. Please try again.';
    header("Location: ../public/settings.php");
    exit();
}

$conn->close();
?>
