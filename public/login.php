<?php
// public/login.php
$pageTitle = "Login";
$pageCSS = [
    '../assets/css/login.css'
];
$pageJS = [
    '../assets/js/login.js'
];

include '../includes/header.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Login</h2>

    <?php
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="authenticate.php" method="POST" class="login-form">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student ID</label>
            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter your Student ID" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
            <label class="form-check-label" for="remember_me">Remember Me</label>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="text-center mt-3">
        <a href="register.php" class="link">Don't have an account? Register here</a>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
