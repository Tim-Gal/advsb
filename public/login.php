<?php
$pageTitle = "Visual Schedule Builder | Login";
$pageCSS = [
    '../assets/css/login.css'
];
$pageJS = [
    '../assets/js/login.js'
];

include '../includes/header.php';
?>

<section class="login-section">
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Login</h2>

            <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                    unset($_SESSION['success']);
                }

                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
            ?>

            <form action="authenticate.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="input-field" id="email" name="email" placeholder="Enter your school email" required>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="input-field" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" class="checkbox" id="rememberMe" name="remember_me">
                    <label class="checkbox-label" for="rememberMe">Remember Me</label>
                </div>
                <button type="submit" class="btn submit-btn">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="link">Don't have an account? Register here</a>
            </div>
            <div class="text-center mt-2">
                <a href="#" class="link">Forgot Password?</a>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>
