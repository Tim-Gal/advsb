<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = strtoupper(sanitizeInput($_POST['student_id']));
    $email = strtolower(sanitizeInput($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($student_id) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id=? AND email=? AND is_verified=0 AND password_hash IS NULL LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: register.php");
        exit();
    }
    $stmt->bind_param("is", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Invalid Student ID or Email, or account already activated.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Generate 6-digit verification code
    $verification_code = generateVerificationCode();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare email content
    $subject = "Verify Your Advanced Schedule Builder Account";
    $verification_message = "Hello,\n\nYour verification code is: $verification_code\n\nPlease enter this 6-digit code on the verification modal to activate your account.\n\nIf you did not request this, please ignore this email.";
    $headers = "From: noreply@localhost\r\n"; // Replace 'localhost' with your domain if applicable

    // Attempt to send the verification email
    if (mail($email, $subject, $verification_message, $headers)) {
        // Email sent successfully, update the student's record with password_hash and verification_code
        $upd = $conn->prepare("UPDATE students SET password_hash=?, verification_code=? WHERE student_id=? AND email=?");
        if (!$upd) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: register.php");
            exit();
        }
        $upd->bind_param("ssis", $password_hash, $verification_code, $student_id, $email);
        if ($upd->execute()) {
            $_SESSION['success'] = "Registration initiated! A 6-digit verification code has been sent to your email. Please verify your account.";
            header("Location: register.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update your account. Please try again.";
            header("Location: register.php");
            exit();
        }
    } else {
        // Email failed to send, do not update the database
        $_SESSION['error'] = "Failed to send verification email. Please try again.";
        header("Location: register.php");
        exit();
    }

    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
