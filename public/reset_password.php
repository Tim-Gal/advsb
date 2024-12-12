<?php
// reset_password.php

include '../includes/functions.php';

$pageTitle = "Reset Password";
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

// Determine the current step based on session variables
$step = 1; // Step 1: Enter Reset Code

if (isset($_POST['verify_code'])) {
    // Handle reset code verification
    $reset_code = isset($_POST['reset_code']) ? sanitizeInput($_POST['reset_code']) : '';

    // Validate reset code format (6-digit)
    if (!preg_match('/^\d{6}$/', $reset_code)) {
        $_SESSION['fp_error'] = "Invalid reset code format. Please enter a 6-digit code.";
        header("Location: ../public/reset_password.php");
        exit();
    }

    // Check the reset code against the database
    include '../includes/config.php'; // Ensure database connection

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE password_reset_code = ? AND password_reset_expires >= NOW() LIMIT 1");
    if (!$stmt) {
        $_SESSION['fp_error'] = "Database error: " . $conn->error;
        header("Location: ../public/reset_password.php");
        exit();
    }

    $stmt->bind_param("s", $reset_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['fp_error'] = "Invalid or expired reset code.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/reset_password.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Set the user_id in session to allow password reset
    $_SESSION['reset_user_id'] = $user['student_id'];

    // Proceed to password reset step
    $step = 2;
} elseif (isset($_SESSION['reset_user_id'])) {
    // User has verified the reset code, proceed to set new password
    $step = 2;
}
?>

<?php if ($step == 1): ?>
    <div class="main-content container mt-5">
        <h2 class="text-center mb-4">Verify Reset Code</h2>

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

        <form action="../public/reset_password.php" method="POST" class="verify-code-form mb-5">
            <div class="mb-3">
                <label for="reset_code" class="form-label">Reset Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="reset_code" name="reset_code" placeholder="Enter the 6-digit code sent to your email" required maxlength="6">
            </div>
            <button type="submit" name="verify_code" class="btn btn-primary">Verify Code</button>
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="link">Back to Forgot Password</a>
            </div>
        </form>
    </div>
<?php elseif ($step == 2): ?>
    <div class="main-content container mt-5">
        <h2 class="text-center mb-4">Set New Password</h2>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['fp_error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['fp_error']) . '</div>';
            unset($_SESSION['fp_error']);
        }
        ?>

        <form action="../api/update_password.php" method="POST" class="reset-password-form mb-5">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
            <div class="text-center mt-3">
                <a href="login.php" class="link">Back to Login</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>
