<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['settings_error'] = "You must be logged in to update your settings.";
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $major_id = isset($_POST['major_id']) ? intval($_POST['major_id']) : 0;
    $minor_id = isset($_POST['minor_id']) ? $_POST['minor_id'] : NULL;

    // Initialize error array
    $errors = [];

    // Validate Major
    if (empty($major_id)) {
        $errors[] = "Major selection is required.";
    } else {
        $stmt_major = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Major'");
        if (!$stmt_major) {
            $errors[] = "Database error: " . $conn->error;
        } else {
            $stmt_major->bind_param("i", $major_id);
            $stmt_major->execute();
            $res_major = $stmt_major->get_result();
            if ($res_major->num_rows === 0) {
                $errors[] = "Selected Major is invalid.";
            }
            $stmt_major->close();
        }
    }

    // Validate Minor if provided
    if (!empty($minor_id)) {
        $minor_id = intval($minor_id);
        $stmt_minor = $conn->prepare("SELECT degree_id FROM degrees WHERE degree_id = ? AND type = 'Minor'");
        if (!$stmt_minor) {
            $errors[] = "Database error: " . $conn->error;
        } else {
            $stmt_minor->bind_param("i", $minor_id);
            $stmt_minor->execute();
            $res_minor = $stmt_minor->get_result();
            if ($res_minor->num_rows === 0) {
                $errors[] = "Selected Minor is invalid.";
            }
            $stmt_minor->close();
        }
    } else {
        $minor_id = NULL; // Explicitly set to NULL if no minor is selected
    }

    if (!empty($errors)) {
        $_SESSION['settings_error'] = implode(' ', $errors);
        header("Location: ../public/settings.php");
        exit();
    }

    // Begin Transaction
    $conn->begin_transaction();

    try {
        // Update the student's major and minor
        $stmt_update = $conn->prepare("UPDATE students SET major_id = ?, minor_id = ? WHERE student_id = ?");
        if (!$stmt_update) {
            throw new Exception("Database error: " . $conn->error);
        }

        // Bind parameters, using NULL for minor_id if it's NULL
        if ($minor_id === NULL) {
            $stmt_update->bind_param("iii", $major_id, $minor_id, $user_id);
        } else {
            $stmt_update->bind_param("iii", $major_id, $minor_id, $user_id);
        }

        if (!$stmt_update->execute()) {
            throw new Exception("Failed to update settings: " . $stmt_update->error);
        }

        $stmt_update->close();

        // Optional: Notify user about missing required courses
        // Fetch required courses for the new major
        $stmt_required = $conn->prepare("SELECT dc.course_code FROM degree_courses dc WHERE dc.degree_id = ? AND dc.course_type = 'required'");
        if ($stmt_required) {
            $stmt_required->bind_param("i", $major_id);
            $stmt_required->execute();
            $res_required = $stmt_required->get_result();
            $required_courses = [];
            while ($row = $res_required->fetch_assoc()) {
                $required_courses[] = $row['course_code'];
            }
            $stmt_required->close();

            // Fetch student's completed courses
            $stmt_completed = $conn->prepare("SELECT course_code FROM coursescompleted WHERE student_id = ?");
            if ($stmt_completed) {
                $stmt_completed->bind_param("i", $user_id);
                $stmt_completed->execute();
                $res_completed = $stmt_completed->get_result();
                $completed_courses = [];
                while ($row = $res_completed->fetch_assoc()) {
                    $completed_courses[] = $row['course_code'];
                }
                $stmt_completed->close();

                $missing_courses = array_diff($required_courses, $completed_courses);

                if (!empty($missing_courses)) {
                    $missing_list = implode(', ', $missing_courses);
                    $_SESSION['settings_success'] = "Settings updated successfully.";
                } else {
                    $_SESSION['settings_success'] = "Settings updated successfully.";
                }
            } else {
                $_SESSION['settings_success'] = "Settings updated successfully.";
            }
        } else {
            $_SESSION['settings_success'] = "Settings updated successfully.";
        }

        $conn->commit();
        header("Location: ../public/settings.php");
        exit();
    } catch (Exception $e) {
        // Rollback Transaction
        $conn->rollback();
        error_log("Error updating settings: " . $e->getMessage());
        $_SESSION['settings_error'] = "Failed to update settings. Please try again later.";
        header("Location: ../public/settings.php");
        exit();
    }
} else {
    header("Location: ../public/settings.php");
    exit();
}
?>
