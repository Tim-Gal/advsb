<?php
// api/update_password.php

include '../includes/config.php';
include '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['fp_error'] = "Invalid request method.";
    header("Location: ../public/reset_password.php");
    exit();
}

// Check if the user has passed the reset code verification
if (!isset($_SESSION['reset_user_id'])) {
    $_SESSION['fp_error'] = "Unauthorized access. Please verify your reset code first.";
    header("Location: ../public/forgot_password.php");
    exit();
}

$user_id = (int)$_SESSION['reset_user_id'];
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$errors = [];

// Validate new password
if (empty($new_password)) {
    $errors[] = "Please enter a new password.";
}

if (empty($confirm_password)) {
    $errors[] = "Please confirm your new password.";
}

if ($new_password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Enforce minimum length of 8 characters
if (strlen($new_password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if (!empty($errors)) {
    $_SESSION['fp_error'] = implode(' ', $errors);
    header("Location: ../public/reset_password.php");
    exit();
}

// Fetch the existing password_hash
$stmt_fetch = $conn->prepare("SELECT password_hash FROM students WHERE student_id = ?");
if (!$stmt_fetch) {
    $_SESSION['fp_error'] = "Database error: " . $conn->error;
    header("Location: ../public/reset_password.php");
    exit();
}

$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

if ($result_fetch->num_rows === 0) {
    $_SESSION['fp_error'] = "User not found.";
    $stmt_fetch->close();
    $conn->close();
    header("Location: ../public/reset_password.php");
    exit();
}

$user = $result_fetch->fetch_assoc();
$existing_password_hash = $user['password_hash'];
$stmt_fetch->close();

// Check if the new password is the same as the current password
if (!empty($existing_password_hash) && password_verify($new_password, $existing_password_hash)) {
    $_SESSION['fp_error'] = "The new password cannot be the same as the current password.";
    $conn->close();
    header("Location: ../public/reset_password.php");
    exit();
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Update the user's password_hash and clear password_reset_code and password_reset_expires
$stmt_update = $conn->prepare("UPDATE students SET password_hash = ?, password_reset_code = NULL, password_reset_expires = NULL WHERE student_id = ?");
if (!$stmt_update) {
    $_SESSION['fp_error'] = "Database error: " . $conn->error;
    header("Location: ../public/reset_password.php");
    exit();
}

$stmt_update->bind_param("si", $hashed_password, $user_id);
if ($stmt_update->execute()) {
    // Password updated successfully
    $_SESSION['fp_success'] = "Your password has been reset successfully. You can now log in.";
    // Unset the reset_user_id
    unset($_SESSION['reset_user_id']);
} else {
    $_SESSION['fp_error'] = "Failed to reset password. Please try again.";
}

$stmt_update->close();
$conn->close();

header("Location: ../public/login.php");
exit();
?>
