<?php
// process_register.php

session_start();

// Include configuration and functions
include '../includes/config.php';
include '../includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input
    $student_id = strtoupper(trim($_POST['student_id']));
    $email = strtolower(trim($_POST['email']));
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validations
    if (empty($student_id) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    // Check if student_id and email match a prepopulated record
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE student_id = ? AND email = ?");
    $stmt->bind_param("ss", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $_SESSION['error'] = "Invalid Student ID or Email.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    }

    $stmt->bind_result($user_id, $existing_username, $existing_password_hash);
    $stmt->fetch();

    // Check if the account is already registered
    if (!is_null($existing_username) || !is_null($existing_password_hash)) {
        $_SESSION['error'] = "This account has already been registered.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    }

    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert username and password_hash into the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, password_hash = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $password_hash, $user_id);

    if ($stmt->execute()) {
        // Log in the user by setting session
        $_SESSION['user_id'] = $user_id;

        // Optionally handle "Remember Me" functionality if desired
        // For simplicity, we'll skip it here

        $_SESSION['success'] = "Registration successful! You are now logged in.";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
    }

    $stmt->close();
    $conn->close();

    header("Location: register.php");
    exit();
} else {
    // If not a POST request, redirect to register
    header("Location: register.php");
    exit();
}
?>
