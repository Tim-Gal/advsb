<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = strtolower(sanitizeInput($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $major_id = isset($_POST['major_id']) ? intval($_POST['major_id']) : 0;
    $minor_id = isset($_POST['minor_id']) ? intval($_POST['minor_id']) : null;

    // Define the required email domain
    $required_domain = '@mail.mcgill.ca';

    // Validate required fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($major_id)) {
        $_SESSION['error'] = "All required fields must be filled out.";
        header("Location: ../public/register.php");
        exit();
    }

    // Validate email format and domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../public/register.php");
        exit();
    }

    if (substr($email, -strlen($required_domain)) !== $required_domain) {
        $_SESSION['error'] = "Email must end with " . htmlspecialchars($required_domain);
        header("Location: ../public/register.php");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM students WHERE username = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username already taken. Please choose another.";
        $stmt->close();
        $conn->close();
        header("Location: ../public/register.php");
        exit();
    }
    $stmt->close();

    // Check if email already exists
    $stmt_email = $conn->prepare("SELECT email FROM students WHERE email = ? LIMIT 1");
    if (!$stmt_email) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();

    if ($stmt_email->num_rows > 0) {
        $_SESSION['error'] = "An account with this email already exists.";
        $stmt_email->close();
        $conn->close();
        header("Location: ../public/register.php");
        exit();
    }
    $stmt_email->close();

    // Validate password strength (optional but recommended)
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../public/register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/register.php");
        exit();
    }

    // Validate Major
    $stmt_major = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Major'");
    if ($stmt_major) {
        $stmt_major->bind_param("i", $major_id);
        $stmt_major->execute();
        $res_major = $stmt_major->get_result();
        if ($res_major->num_rows === 0) {
            $_SESSION['error'] = "Selected Major is invalid.";
            $stmt_major->close();
            header("Location: ../public/register.php");
            exit();
        }
        $stmt_major->close();
    } else {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: ../public/register.php");
        exit();
    }

    // Validate Minor (if selected)
    if (!empty($minor_id)) {
        $stmt_minor = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Minor'");
        if ($stmt_minor) {
            $stmt_minor->bind_param("i", $minor_id);
            $stmt_minor->execute();
            $res_minor = $stmt_minor->get_result();
            if ($res_minor->num_rows === 0) {
                $_SESSION['error'] = "Selected Minor is invalid.";
                $stmt_minor->close();
                header("Location: ../public/register.php");
                exit();
            }
            $stmt_minor->close();
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: ../public/register.php");
            exit();
        }
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generate a 6-digit verification code
    $verification_code = generateVerificationCode(); // Ensure this generates 6 digits

    // Prepare the verification email
    $subject = "Verify Your Advanced Schedule Builder Account";
    $verification_message = "Hello $username,\n\nYour verification code is: $verification_code\n\nPlease enter this 6-digit code on the verification page to activate your account.\n\nIf you did not request this, please ignore this email.";
    $headers = "From: noreply@advsb.com\r\n"; 

    // Send verification email
    $mail_sent = mail($email, $subject, $verification_message, $headers);

    if ($mail_sent) {
        // Insert the user into the database **after** successful email sending
        $stmt_insert = null;
        if (empty($minor_id)) {
            $stmt_insert = $conn->prepare("INSERT INTO students (username, email, password_hash, verification_code, major_id, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt_insert->bind_param("sssii", $username, $email, $password_hash, $verification_code, $major_id);
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO students (username, email, password_hash, verification_code, major_id, minor_id, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
            $stmt_insert->bind_param("sssiii", $username, $email, $password_hash, $verification_code, $major_id, $minor_id);
        }

        if ($stmt_insert->execute()) {
            $_SESSION['success'] = "Registration successful! A 6-digit verification code has been sent to your email. Please verify your account.";
        } else {
            $_SESSION['error'] = "Registration failed due to a database error. Please try again.";
        }
        $stmt_insert->close();
    } else {
        $_SESSION['error'] = "Failed to send verification email. Please check your email address and try again.";
    }

    $conn->close();
    header("Location: ../public/register.php");
    exit();
} else {
    header("Location: ../public/register.php");
    exit();
}
?>
