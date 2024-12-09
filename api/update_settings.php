<?php
session_start();

include '../includes/config.php';
include '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$fname = trim($_POST['fname'] ?? '');
$lname = trim($_POST['lname'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($fname) || empty($lname) || empty($email)) {
    $_SESSION['error'] = "First name, last name, and email are required.";
    header("Location: ../public/settings.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: ../public/settings.php");
    exit();
}

if (!empty($password) || !empty($confirm_password)) {
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/settings.php");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE students SET fname=?, lname=?, email=?, password_hash=? WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fname, $lname, $email, $password_hash, $user_id);
} else {
    $sql = "UPDATE students SET fname=?, lname=?, email=? WHERE student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fname, $lname, $email, $user_id);
}

if (!$stmt->execute()) {
    $_SESSION['error'] = "Failed to update settings. Please try again.";
    $stmt->close();
    $conn->close();
    header("Location: ../public/settings.php");
    exit();
}

$stmt->close();
$conn->close();

$_SESSION['success'] = "Settings updated successfully.";
header("Location: ../public/settings.php");
exit();
