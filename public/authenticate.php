<?php
// public/authenticate.php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = strtoupper(sanitizeInput($_POST['student_id']));
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Basic validation
    if (empty($student_id) || empty($password)) {
        $_SESSION['error'] = "Please enter both Student ID and Password.";
        header("Location: login.php");
        exit();
    }

    // Fetch user data
    $stmt = $conn->prepare("SELECT password_hash, is_verified FROM students WHERE student_id=? LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: login.php");
        exit();
    }
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "No account found with that Student ID.";
        $stmt->close();
        $conn->close();
        header("Location: login.php");
        exit();
    }

    $stmt->bind_result($password_hash, $is_verified);
    $stmt->fetch();

    if (!password_verify($password, $password_hash)) {
        $_SESSION['error'] = "Incorrect password.";
        $stmt->close();
        $conn->close();
        header("Location: login.php");
        exit();
    }

    if ($is_verified != 1) {
        $_SESSION['error'] = "Your account is not verified. Please verify your account before logging in.";
        $stmt->close();
        $conn->close();
        header("Location: login.php");
        exit();
    }

    // Successful login
    $_SESSION['user_id'] = $student_id;
    $_SESSION['success'] = "You are now logged in.";

    // Handle "Remember Me" functionality
    if ($remember_me) {
        $remember_token = bin2hex(random_bytes(16));
        $stmt->close();
        
        $upd = $conn->prepare("UPDATE students SET remember_token=? WHERE student_id=?");
        if ($upd) {
            $upd->bind_param("si", $remember_token, $student_id);
            $upd->execute();
            $upd->close();
            setcookie("remember_me", $remember_token, time() + (86400 * 30), "/"); // 30 days
        }
    } else {
        // If "Remember Me" is not checked, ensure the cookie is cleared
        setcookie("remember_me", "", time() - 3600, "/");
    }

    $conn->close();
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
