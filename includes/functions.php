<?php


function generateVerificationCode($length = 50) {
    return bin2hex(random_bytes($length / 2));
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../public/login.php");
        exit();
    }
}
?>
