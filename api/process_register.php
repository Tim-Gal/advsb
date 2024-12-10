<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = strtoupper(sanitizeInput($_POST['student_id']));
    $email = strtolower(sanitizeInput($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $major_id = isset($_POST['major_id']) ? intval($_POST['major_id']) : 0;
    $minor_id = isset($_POST['minor_id']) ? intval($_POST['minor_id']) : NULL;

    // Validate required fields
    if (empty($student_id) || empty($email) || empty($password) || empty($confirm_password) || empty($major_id)) {
        $_SESSION['error'] = "All required fields are filled out.";
        header("Location: ../public/register.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../public/register.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/register.php");
        exit();
    }

    // Check if the student exists with is_verified=0 and password_hash=NULL
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id=? AND email=? AND is_verified=0 AND password_hash IS NULL LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->bind_param("is", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Invalid Student ID or Email, or account already activated.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->close();

    // Validate Major and Minor
    // Verify that the major_id exists and is of type 'Major'
    $stmt_major = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Major'");
    $stmt_major->bind_param("i", $major_id);
    $stmt_major->execute();
    $res_major = $stmt_major->get_result();
    if ($res_major->num_rows === 0) {
        $_SESSION['error'] = "Selected Major is invalid.";
        $stmt_major->close();
        header("Location: ../public/register.php");
        exit();
    }
    $stmt_major->close();

    // If minor_id is provided, verify it exists and is of type 'Minor'
    if (!empty($minor_id)) {
        $stmt_minor = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Minor'");
        $stmt_minor->bind_param("i", $minor_id);
        $stmt_minor->execute();
        $res_minor = $stmt_minor->get_result();
        if ($res_minor->num_rows === 0) {
            $_SESSION['error'] = "Selected Minor is invalid.";
            $stmt_minor->close();
            header("Location: ../public/register.php");
            exit();
        }
        $stmt_minor->close();
    }

    // Generate 6-digit verification code
    $verification_code = generateVerificationCode();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare email content
    $subject = "Verify Your Advanced Schedule Builder Account";
    $verification_message = "Hello,\n\nYour verification code is: $verification_code\n\nPlease enter this 6-digit code on the verification modal to activate your account.\n\nIf you did not request this, please ignore this email.";
    $headers = "From: noreply@localhost\r\n"; // Replace 'localhost' with your domain if applicable

    // Attempt to send the verification email
    if (mail($email, $subject, $verification_message, $headers)) {
        // Email sent successfully, update the student's record with password_hash, verification_code, major_id, and minor_id
        $upd = $conn->prepare("UPDATE students SET password_hash=?, verification_code=?, major_id=?, minor_id=? WHERE student_id=? AND email=?");
        if (!$upd) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: ../public/register.php");
            exit();
        }
        $upd->bind_param("ssiiii", $password_hash, $verification_code, $major_id, $minor_id, $student_id, $email);
        if ($upd->execute()) {
            $_SESSION['success'] = "Registration initiated! A 6-digit verification code has been sent to your email. Please verify your account.";
            header("Location: ../public/register.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update your account. Please try again.";
            header("Location: ../public/register.php");
            exit();
        }
    } else {
        // Email failed to send, do not update the database
        $_SESSION['error'] = "Failed to send verification email. Please try again.";
        header("Location: ../public/register.php");
        exit();
    }

    $conn->close();
} else {
    header("Location: ../public/register.php");
    exit();
}
?>
