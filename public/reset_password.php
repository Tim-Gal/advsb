<?php
// reset_password.php


include_once '../includes/functions.php';

$pageTitle = "Reset Password";
$pageCSS = [
    '../assets/css/global.css',
    '../assets/css/login.css'
];
$pageJS = [
    '../assets/js/login.js'
];

include_once '../includes/header.php';

// Redirect to forgot_password.php if reset_user_id is not set
if (!isset($_SESSION['reset_user_id'])) {
    $_SESSION['fp_error'] = "Unauthorized access. Please verify your reset code first.";
    header("Location: forgot_password.php");
    exit();
}

// Determine the current step based on session variables
$step = 2; // Step 2: Set New Password

if (isset($_POST['reset_password'])) {
    // Handle password reset
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    $errors = [];

    // Validate new password
    if (empty($new_password)) {
        $errors[] = "Please enter a new password.";
    }

    if (empty($confirm_password)) {
        $errors[] = "Please confirm your new password.";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Enforce minimum length of 8 characters
    if (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (!empty($errors)) {
        $_SESSION['fp_error'] = implode(' ', $errors);
        header("Location: reset_password.php");
        exit();
    }

    // Fetch the existing password_hash
    include_once '../includes/config.php'; // Ensure database connection

    $user_id = (int)$_SESSION['reset_user_id'];

    $stmt_fetch = $conn->prepare("SELECT password_hash FROM students WHERE student_id = ?");
    if (!$stmt_fetch) {
        $_SESSION['fp_error'] = "Database error: " . $conn->error;
        header("Location: reset_password.php");
        exit();
    }

    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 0) {
        $_SESSION['fp_error'] = "User not found.";
        $stmt_fetch->close();
        $conn->close();
        header("Location: reset_password.php");
        exit();
    }

    $user = $result_fetch->fetch_assoc();
    $existing_password_hash = $user['password_hash'];
    $stmt_fetch->close();

    // Check if the new password is the same as the current password
    if (!empty($existing_password_hash) && password_verify($new_password, $existing_password_hash)) {
        $_SESSION['fp_error'] = "The new password cannot be the same as the current password.";
        $conn->close();
        header("Location: reset_password.php");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the user's password_hash and clear password_reset_code and password_reset_expires
    $stmt_update = $conn->prepare("UPDATE students SET password_hash = ?, password_reset_code = NULL, password_reset_expires = NULL WHERE student_id = ?");
    if (!$stmt_update) {
        $_SESSION['fp_error'] = "Database error: " . $conn->error;
        header("Location: reset_password.php");
        exit();
    }

    $stmt_update->bind_param("si", $hashed_password, $user_id);
    if ($stmt_update->execute()) {
        // Password updated successfully
        $_SESSION['fp_success'] = "Your password has been reset successfully. You can now log in.";
        // Unset the reset_user_id
        unset($_SESSION['reset_user_id']);
    } else {
        $_SESSION['fp_error'] = "Failed to reset password. Please try again.";
    }

    $stmt_update->close();
    $conn->close();

    header("Location: login.php");
    exit();
}
?>

<?php if ($step == 2): ?>
    <div class="main-content container mt-5">
        <h2 class="text-center mb-4">Set New Password</h2>

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

        <form action="" method="POST" class="reset-password-form mb-5">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
            <div class="text-center mt-3">
                <a href="login.php" class="link">Back to Login</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
include_once '../includes/footer.php';
?>
