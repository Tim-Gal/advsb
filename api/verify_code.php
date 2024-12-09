<?php
$pageTitle = "Verify Your Account";
$pageCSS = [
    '../assets/css/verify_code.css'
];
$pageJS = [
    '../assets/js/verify_code.js'
];

include '../includes/header.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $verification_code = sanitizeInput($_POST['verification_code']);

    if (empty($verification_code)) {
        $_SESSION['error'] = "Please enter the verification code.";
        header("Location: ../public/register.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE verification_code=? AND is_verified=0 LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Invalid or expired verification code.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/register.php");
        exit();
    }

    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();

    $upd = $conn->prepare("UPDATE students SET is_verified=1, verification_code=NULL WHERE student_id=?");
    if (!$upd) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }
    $upd->bind_param("i", $student_id);
    if ($upd->execute()) {
        $_SESSION['user_id'] = $student_id;
        $_SESSION['success'] = "Your account has been verified and you are now logged in.";
        header("Location: ../public/dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to verify your account. Please try again.";
        header("Location: ../public/register.php");
        exit();
    }

    $conn->close();
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Account Verification</h2>

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

    <p class="text-center">If you haven't entered the verification code yet, please check your email and use the modal on the registration page.</p>
</div>

<?php
include '../includes/footer.php';
?>
