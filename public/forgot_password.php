<?php
// forgot_password.php

include '../includes/functions.php';

$pageTitle = "Forgot Password";
$pageCSS = [
    '../assets/css/global.css',
    '../assets/css/login.css'
];
$pageJS = [
    '../assets/js/login.js'
];

include '../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="main-content container mt-5">
    <h2 class="text-center mb-4">Forgot Password</h2>

    <?php
    if (isset($_SESSION['fp_success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['fp_success']) . '</div>';
        unset($_SESSION['fp_success']);
    }

    if (isset($_SESSION['fp_error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['fp_error']) . '</div>';
        unset($_SESSION['fp_error']);
    }
    ?>

    <form action="../api/request_password_reset.php" method="POST" class="forgot-password-form mb-5">
        <div class="mb-3">
            <label for="email" class="form-label">Registered Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your registered email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Reset Code</button>
        <div class="text-center mt-3">
            <a href="login.php" class="link">Back to Login</a>
        </div>
    </form>
</div>

<?php
include '../includes/footer.php';
?>
