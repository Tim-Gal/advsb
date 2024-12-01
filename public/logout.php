<?php

session_start();


include '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

$_SESSION = array();
session_destroy();

if (isset($_COOKIE['remember_me'])) {
    setcookie("remember_me", "", time() - 3600, "/", "", false, true);
}

header("Location: login.php");
exit();
?>
