<?php
// handle_progress.php

session_start();
header('Content-Type: text/html; charset=utf-8');

// Include necessary configuration and function files
include '../includes/config.php';
include '../includes/functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to manage your progress.";
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine the action based on the POST parameters
    if (isset($_POST['course_code'])) {
        // Handle adding a completed course
        $course_code = strtoupper(trim($_POST['course_code']));

        // Validate course code format (e.g., COMP-101)
        if (empty($course_code)) {
            $_SESSION['add_course_error'] = "Course code cannot be empty.";
            header("Location: ../public/myprogress.php");
            exit();
        }

        if (!preg_match('/^[A-Z]{2,4}-\d{3}$/', $course_code)) {
            $_SESSION['add_course_error'] = "Invalid course code format. Please enter in the format XXXX-XXX.";
            header("Location: ../public/myprogress.php");
            exit();
        }

        // Begin Transaction
        $conn->begin_transaction();

        try {
            /**
             * Step 1: Check if the course exists
             */
            $sqlCheckCourse = "SELECT course_code FROM courses WHERE course_code = ?";
            $stmtCheckCourse = $conn->prepare($sqlCheckCourse);
            if (!$stmtCheckCourse) {
                throw new Exception("Database error while checking course existence: " . $conn->error);
            }
            $stmtCheckCourse->bind_param("s", $course_code);
            $stmtCheckCourse->execute();
            $resultCheckCourse = $stmtCheckCourse->get_result();
            if ($resultCheckCourse->num_rows === 0) {
                $stmtCheckCourse->close();
                throw new Exception("The course code '{$course_code}' does not exist.");
            }
            $stmtCheckCourse->close();

            /**
             * Step 2: Insert into coursescompleted
             * Prevent duplicate entries
             */
            $sqlInsertCompleted = "INSERT INTO coursescompleted (student_id, course_code) VALUES (?, ?)";
            $stmtInsertCompleted = $conn->prepare($sqlInsertCompleted);
            if (!$stmtInsertCompleted) {
                throw new Exception("Database error while preparing to insert completed course: " . $conn->error);
            }
            $stmtInsertCompleted->bind_param("is", $user_id, $course_code);

            if (!$stmtInsertCompleted->execute()) {
                // Check for duplicate entry error (assuming UNIQUE constraint on (student_id, course_code))
                if ($conn->errno === 1062) { // MySQL error code for duplicate entry
                    $stmtInsertCompleted->close();
                    throw new Exception("You have already marked '{$course_code}' as completed.");
                } else {
                    throw new Exception("Database error while inserting completed course: " . $stmtInsertCompleted->error);
                }
            }
            $stmtInsertCompleted->close();

            /**
             * Step 3: Check for existing enrollments in the same course across all semesters
             */
            $sqlCheckEnrollment = "
                SELECT ce.section_code, s.semester, c.course_name
                FROM coursesenrolled ce
                JOIN sections s ON ce.section_code = s.section_code
                JOIN courses c ON s.course_code = c.course_code
                WHERE ce.student_id = ? AND c.course_code = ?
            ";
            $stmtCheckEnrollment = $conn->prepare($sqlCheckEnrollment);
            if (!$stmtCheckEnrollment) {
                throw new Exception("Database error while checking existing enrollments: " . $conn->error);
            }
            $stmtCheckEnrollment->bind_param("is", $user_id, $course_code);
            $stmtCheckEnrollment->execute();
            $resultEnrollment = $stmtCheckEnrollment->get_result();

            $existingEnrollments = [];
            while ($row = $resultEnrollment->fetch_assoc()) {
                $existingEnrollments[] = [
                    'section_code' => $row['section_code'],
                    'semester' => ucfirst(strtolower($row['semester'])),
                    'course_name' => $row['course_name']
                ];
            }
            $stmtCheckEnrollment->close();

            /**
             * Step 4: Remove existing enrollments if any
             */
            if (!empty($existingEnrollments)) {
                $section_codes = array_column($existingEnrollments, 'section_code');
                // Prepare placeholders for IN clause
                $placeholders = implode(',', array_fill(0, count($section_codes), '?'));
                $types = str_repeat('i', count($section_codes));
                $sqlDeleteEnrollments = "DELETE FROM coursesenrolled WHERE student_id = ? AND section_code IN ($placeholders)";
                $stmtDeleteEnrollments = $conn->prepare($sqlDeleteEnrollments);
                if (!$stmtDeleteEnrollments) {
                    throw new Exception("Database error while deleting enrollments: " . $conn->error);
                }

                // Bind parameters dynamically
                $params = array_merge([$user_id], $section_codes);
                $types_bind = 'i' . $types;

                // Create a reference array
                $refs = [];
                foreach ($params as $key => $value) {
                    $refs[$key] = &$params[$key];
                }

                // Bind parameters
                array_unshift($refs, $types_bind);
                call_user_func_array([$stmtDeleteEnrollments, 'bind_param'], $refs);

                if (!$stmtDeleteEnrollments->execute()) {
                    throw new Exception("Database error while deleting enrollments: " . $stmtDeleteEnrollments->error);
                }
                $stmtDeleteEnrollments->close();

                // Optionally, log the removal
                foreach ($existingEnrollments as $enrollment) {
                    error_log("Auto-removed enrollment: User ID {$user_id} removed from course '{$course_code}' ({$enrollment['course_name']}) in {$enrollment['semester']} semester (Section Code: {$enrollment['section_code']}).");
                }

                // Prepare a success message including removal info
                $removedCourses = array_map(function($enrollment) use ($course_code) {
                    return "{$course_code} ({$enrollment['course_name']}) in {$enrollment['semester']} semester (Section Code: {$enrollment['section_code']})";
                }, $existingEnrollments);

                $_SESSION['add_course_success'] = "Course '{$course_code}' marked as completed successfully. Existing enrollments for this course have been removed from your schedule: " . implode(', ', $removedCourses) . ".";
            } else {
                // No existing enrollments to remove
                $_SESSION['add_course_success'] = "Course '{$course_code}' marked as completed successfully.";
            }

            // Commit Transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback Transaction on Error
            $conn->rollback();

            // Log the error
            error_log("Error in handle_progress.php (add): " . $e->getMessage());

            // Set error message
            $_SESSION['add_course_error'] = $e->getMessage();
        }

        // Redirect back to myprogress.php
        header("Location: ../public/myprogress.php");
        exit();
    } elseif (isset($_POST['remove_course_code'])) {
        // Handle removing a completed course
        $course_code = strtoupper(trim($_POST['remove_course_code']));

        // Validate course code format (e.g., COMP-101)
        if (empty($course_code)) {
            $_SESSION['remove_course_error'] = "Course code cannot be empty.";
            header("Location: ../public/myprogress.php");
            exit();
        }

        if (!preg_match('/^[A-Z]{2,4}-\d{3}$/', $course_code)) {
            $_SESSION['remove_course_error'] = "Invalid course code format. Please enter in the format XXXX-XXX.";
            header("Location: ../public/myprogress.php");
            exit();
        }

        try {
            // Check if the course is marked as completed by the user
            $stmt_check = $conn->prepare("SELECT course_code FROM coursescompleted WHERE student_id = ? AND course_code = ?");
            if (!$stmt_check) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt_check->bind_param("is", $user_id, $course_code);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows === 0) {
                $stmt_check->close();
                throw new Exception("The course '{$course_code}' is not marked as completed.");
            }
            $stmt_check->close();

            // Delete the completed course
            $stmt_delete = $conn->prepare("DELETE FROM coursescompleted WHERE student_id = ? AND course_code = ?");
            if (!$stmt_delete) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt_delete->bind_param("is", $user_id, $course_code);
            if ($stmt_delete->execute()) {
                $_SESSION['remove_course_success'] = "Course '{$course_code}' has been successfully removed from your completed courses.";
            } else {
                throw new Exception("Failed to remove the course. Please try again.");
            }
            $stmt_delete->close();
        } catch (Exception $e) {
            // Log the error
            error_log("Error in handle_progress.php (remove): " . $e->getMessage());

            // Set error message
            $_SESSION['remove_course_error'] = $e->getMessage();
        }

        // Redirect back to myprogress.php
        header("Location: ../public/myprogress.php");
        exit();
    } else {
        // If accessed without POST data, redirect to myprogress.php
        header("Location: ../public/myprogress.php");
        exit();
    }
}
?>
