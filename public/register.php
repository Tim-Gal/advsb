<?php
$pageTitle = "Visual Schedule Builder | Register";
$pageCSS = [
    '../assets/css/register.css'
];
$pageJS = [
    '../assets/js/register.js'
];

include '../includes/header.php';
?>

<div class="register-container">
    <h2 class="register-title">Register</h2>

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

    <form action="process_register.php" method="POST" id="registerForm" class="register-form">
        <div class="form-group">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" class="input-field" id="fname" name="fname" placeholder="Enter your first name" required>
        </div>
        <div class="form-group">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" class="input-field" id="lname" name="lname" placeholder="Enter your last name" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="input-field" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="input-field" id="password" name="password" placeholder="Create a password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" class="input-field" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>
        <button type="submit" class="btn submit-btn">Register</button>
    </form>
    <div class="text-center mt-3">
        <a href="login.php" class="link">Already have an account? Login here</a>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
