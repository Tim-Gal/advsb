<?php

$pageTitle = "Login";
$pageCSS = [
    '/assets/css/global.css',
    '/assets/css/login.css'
];
$pageJS = [
    '/assets/js/login.js'
];

include '/includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="main-content container mt-5">
    <h2 class="text-center mb-4">Login</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="caution caution-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div class="caution caution-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="/api/authenticate.php" method="POST" class="login-form mb-5">
        <div class="mb-3">
            <label for="identifier" class="form-label">Username or Email <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="identifier" name="identifier" placeholder="Enter your Username or Email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
            <label class="form-check-label" for="remember_me">Remember Me</label>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <div class="text-center mt-3">
            <a href="register.php" class="link">Don't have an account? Register here</a>
        </div>
        <div class="text-center mt-2">
            <a href="forgot_password.php" class="link">Forgot Password?</a>
        </div>
    </form>
</div>

<?php
include '/includes/footer.php';
?>
