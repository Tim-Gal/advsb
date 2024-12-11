<?php
// remove_completed_course.php

session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Ensure the user is logged in and verified
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to remove completed courses.";
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's verification status
$sql_verification = "SELECT is_verified FROM students WHERE student_id = ?";
$stmt_verification = $conn->prepare($sql_verification);
if (!$stmt_verification) {
    $_SESSION['add_course_error'] = "Database error: " . $conn->error;
    header("Location: ../public/myprogress.php");
    exit();
}
$stmt_verification->bind_param("i", $user_id);
$stmt_verification->execute();
$stmt_verification->bind_result($is_verified);
$stmt_verification->fetch();
$stmt_verification->close();

if ($is_verified != 1) {
    $_SESSION['add_course_error'] = "Your account is not verified. Please verify your account before removing completed courses.";
    header("Location: ../public/myprogress.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = strtoupper(sanitizeInput($_POST['course_code']));

    // Validate course code format (e.g., CS101)
    if (!preg_match('/^[A-Z]{2,4}\d{3}$/', $course_code)) {
        $_SESSION['add_course_error'] = "Invalid course code format. Please enter a valid course code (e.g., CS101).";
        header("Location: ../public/myprogress.php");
        exit();
    }

    // Check if the course is marked as completed by the user
    $stmt_check = $conn->prepare("SELECT course_code FROM coursescompleted WHERE student_id = ? AND course_code = ?");
    if (!$stmt_check) {
        $_SESSION['add_course_error'] = "Database error: " . $conn->error;
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_check->bind_param("is", $user_id, $course_code);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $_SESSION['add_course_error'] = "The course '$course_code' is not marked as completed.";
        $stmt_check->close();
        $conn->close();
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_check->close();

    // Delete the completed course
    $stmt_delete = $conn->prepare("DELETE FROM coursescompleted WHERE student_id = ? AND course_code = ?");
    if (!$stmt_delete) {
        $_SESSION['add_course_error'] = "Database error: " . $conn->error;
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_delete->bind_param("is", $user_id, $course_code);
    if ($stmt_delete->execute()) {
        $_SESSION['add_course_success'] = "Course '$course_code' has been successfully removed from your completed courses.";
    } else {
        $_SESSION['add_course_error'] = "Failed to remove the course. Please try again.";
    }
    $stmt_delete->close();
    $conn->close();

    header("Location: ../public/myprogress.php");
    exit();
} else {
    header("Location: ../public/myprogress.php");
    exit();
}
?>
