<?php
// public/process_register.php

session_start();

include '../includes/config.php';
include '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fname) || empty($lname) || empty($email) || empty($password) || empty($confirm_password)) {
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

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header("Location: register.php");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email already in use
        $_SESSION['error'] = "Email is already registered.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new student record
    $stmt = $conn->prepare("INSERT INTO students (fname, lname, email, password_hash) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        $conn->close();
        header("Location: register.php");
        exit();
    }
    $stmt->bind_param("ssss", $fname, $lname, $email, $password_hash);

    if ($stmt->execute()) {
        $new_student_id = $stmt->insert_id;

        // Log in the user by setting session
        $_SESSION['user_id'] = $new_student_id;

        $_SESSION['success'] = "Registration successful! You are now logged in.";
        $stmt->close();
        $conn->close();
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit();
    }
} else {
    // If not a POST request, redirect to register
    header("Location: register.php");
    exit();
}
