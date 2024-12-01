<?php

session_start();

include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);
    $remember_me = isset($_POST['remember_me']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: login.php");
        exit();
    }

    if (empty($password)) {
        $_SESSION['error'] = "Password cannot be empty.";
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header("Location: login.php");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;

            if ($remember_me) {
                $token = bin2hex(random_bytes(16));

                $stmt_update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                if ($stmt_update) {
                    $stmt_update->bind_param("si", $token, $user_id);
                    $stmt_update->execute();
                    $stmt_update->close();

                    setcookie("remember_me", $token, time() + (86400 * 30), "/", "", false, true);
                }
            }

            $_SESSION['success'] = "You are now logged in.";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password.";
            $stmt->close();
            $conn->close();
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found with that email.";
        $stmt->close();
        $conn->close();
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
