
<!--  Coded by Efe-->

<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $verification_code = sanitizeInput($_POST['verification_code']);

    if (empty($verification_code)) {
        $_SESSION['error'] = "Please enter the verification code.";
        header("Location: ../register.php");
        exit();
    }

    if (!preg_match('/^\d{6}$/', $verification_code)) {
        $_SESSION['error'] = "Invalid verification code format. Please enter a 6-digit code.";
        header("Location: ../register.php");
        exit();
    }



    $stmt = $conn->prepare("SELECT student_id FROM students WHERE verification_code = ? AND is_verified = 0 LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../register.php");
        exit();
    }

    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Invalid or expired verification code.";
        $stmt->close();
        $conn->close();
        header("Location: ../register.php");
        exit();
    }

    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();

    $stmt_update = $conn->prepare("UPDATE students SET is_verified = 1, verification_code = NULL WHERE student_id = ?");
    if (!$stmt_update) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../register.php");
        exit();
    }

    $stmt_update->bind_param("i", $student_id);
    if ($stmt_update->execute()) {
        $_SESSION['success'] = "Your account has been verified! You can now log in.";
    } else {
        $_SESSION['error'] = "Failed to verify your account. Please try again.";
    }
    $stmt_update->close();
    $conn->close();

    header("Location: ../login.php");
    exit();
} else {
    header("Location: ../register.php");
    exit();
}
?>
