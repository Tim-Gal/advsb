<?php
// add_completed_course.php

session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Ensure the user is logged in and verified
if (!isset($_SESSION['user_id'])) {
    $_SESSION['add_course_error'] = "You must be logged in to add completed courses.";
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
    $_SESSION['add_course_error'] = "Your account is not verified. Please verify your account before adding completed courses.";
    header("Location: ../public/myprogress.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = strtoupper(sanitizeInput($_POST['course_code']));

    // Validate course code format (e.g., CS101)
    if (!preg_match('/^[A-Z]{2,4}-\d{3}$/', $course_code)) {
        $_SESSION['add_course_error'] = "Invalid course code format. Please enter a valid course code (e.g., COMP-101).";
        header("Location: ../public/myprogress.php");
        exit();
    }

    // Check if the course exists
    $stmt_course = $conn->prepare("SELECT course_code FROM courses WHERE course_code = ?");
    if (!$stmt_course) {
        $_SESSION['add_course_error'] = "Database error: " . $conn->error;
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_course->bind_param("s", $course_code);
    $stmt_course->execute();
    $stmt_course->store_result();

    if ($stmt_course->num_rows === 0) {
        $_SESSION['add_course_error'] = "The course code '$course_code' does not exist.";
        $stmt_course->close();
        $conn->close();
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_course->close();

    // Check if the course is already marked as completed
    $stmt_duplicate = $conn->prepare("SELECT course_code FROM coursescompleted WHERE student_id = ? AND course_code = ?");
    if (!$stmt_duplicate) {
        $_SESSION['add_course_error'] = "Database error: " . $conn->error;
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_duplicate->bind_param("is", $user_id, $course_code);
    $stmt_duplicate->execute();
    $stmt_duplicate->store_result();

    if ($stmt_duplicate->num_rows > 0) {
        $_SESSION['add_course_error'] = "You have already marked '$course_code' as completed.";
        $stmt_duplicate->close();
        $conn->close();
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_duplicate->close();

    // Insert the completed course
    $stmt_insert = $conn->prepare("INSERT INTO coursescompleted (student_id, course_code) VALUES (?, ?)");
    if (!$stmt_insert) {
        $_SESSION['add_course_error'] = "Database error: " . $conn->error;
        header("Location: ../public/myprogress.php");
        exit();
    }
    $stmt_insert->bind_param("is", $user_id, $course_code);
    if ($stmt_insert->execute()) {
        $_SESSION['add_course_success'] = "Course '$course_code' has been successfully marked as completed.";
    } else {
        $_SESSION['add_course_error'] = "Failed to add the course. Please try again.";
    }
    $stmt_insert->close();
    $conn->close();

    header("Location: ../public/myprogress.php");
    exit();
} else {
    header("Location: ../public/myprogress.php");
    exit();
}
?>
