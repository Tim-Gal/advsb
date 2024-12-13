<?php
session_start(); 

include_once '../includes/config.php';
include_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['fp_error'] = "Invalid request method.";
    header("Location: ../public/forgot_password.php");
    exit();
}

$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';

if (empty($email)) {
    $_SESSION['fp_error'] = "Please enter your registered email.";
    header("Location: ../public/forgot_password.php");
    exit();
}



if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['fp_error'] = "Please enter a valid email address.";
    header("Location: ../public/forgot_password.php");
    exit();
}

$stmt = $conn->prepare("SELECT student_id, email FROM students WHERE email = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['fp_error'] = "Database error: " . $conn->error;
    header("Location: ../public/forgot_password.php");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['fp_error'] = "No account found with that email.";
    $stmt->close();
    $conn->close();
    header("Location: ../public/forgot_password.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

$user_id = $user['student_id'];
$user_email = $user['email'];

$reset_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);


$reset_expires = date("Y-m-d H:i:s", strtotime("+1 hour"));


$stmt_update = $conn->prepare("UPDATE students SET password_reset_code = ?, password_reset_expires = ? WHERE student_id = ?");
if (!$stmt_update) {
    $_SESSION['fp_error'] = "Database error: " . $conn->error;
    header("Location: ../public/forgot_password.php");
    exit();
}

$stmt_update->bind_param("ssi", $reset_code, $reset_expires, $user_id);
if (!$stmt_update->execute()) {
    $_SESSION['fp_error'] = "Failed to set reset code. Please try again.";
    $stmt_update->close();
    $conn->close();
    header("Location: ../public/forgot_password.php");
    exit();
}
$stmt_update->close();
$conn->close();

$subject = "Your Password Reset Code";
$message = "
    <p>Hello,</p>
    <p>You have requested to reset your password. Please use the 6-digit code below to reset your password. This code is valid for 1 hour.</p>
    <h2>{$reset_code}</h2>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thank you.</p>
";

$headers = "From: no-reply@advsb.com\r\n"; 
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";


$mail_sent = mail($user_email, $subject, $message, $headers);

if ($mail_sent) {
    $_SESSION['reset_code_sent'] = true;
    $_SESSION['fp_success'] = "A password reset code has been sent to your email.";
    header("Location: ../public/forgot_password.php");
    exit();
} else {
    $_SESSION['fp_error'] = "Failed to send reset email. Please try again.";
    header("Location: ../public/forgot_password.php");
    exit();
}
?>
