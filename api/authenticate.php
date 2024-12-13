<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = strtolower(sanitizeInput($_POST['identifier'])); 
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;

    if (empty($identifier) || empty($password)) {
        $_SESSION['error'] = "Please enter both Username/Email and Password.";
        header("Location: ../public/login.php");
        exit();
    }
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT student_id, password_hash, is_verified FROM students WHERE email = ? LIMIT 1");
    } else {
        $stmt = $conn->prepare("SELECT student_id, password_hash, is_verified FROM students WHERE username = ? LIMIT 1");
    }
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "No account found with that Username/Email.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/login.php");
        exit();
    }
    $stmt->bind_result($student_id, $password_hash, $is_verified);
    $stmt->fetch();




    if (!password_verify($password, $password_hash)) {
        $_SESSION['error'] = "Incorrect password.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/login.php");
        exit();
    }

    if ($is_verified != 1) {
        $_SESSION['error'] = "Your account is not verified. Please verify your account before logging in.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/login.php");
        exit();
    }
    $_SESSION['user_id'] = $student_id;
    $_SESSION['success'] = "You are now logged in.";
    if ($remember_me) {
        $remember_token = bin2hex(random_bytes(16));
        $stmt->close();
        
        $upd = $conn->prepare("UPDATE students SET remember_token = ? WHERE student_id = ?");
        if ($upd) {
            $upd->bind_param("si", $remember_token, $student_id);
            $upd->execute();
            $upd->close();
            setcookie("remember_me", $remember_token, time() + (86400 * 30), "/", "", isset($_SERVER['HTTPS']), true);
        }
    } else {
        setcookie("remember_me", "", time() - 3600, "/");
    }
    $conn->close();
    header("Location: ../public/dashboard.php");
    exit();
} else {
    header("Location: ../public/login.php");
    exit();
}
?>
