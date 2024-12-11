<?php

include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);
$courseCode = trim($input['course_code'] ?? '');

if (empty($courseCode)) {
    echo json_encode(["error" => "Course code is required."]);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // First, retrieve all section_codes associated with the course_code for the user
    $sqlSections = "
    SELECT ce.section_code
    FROM coursesenrolled ce
    JOIN sections s ON ce.section_code = s.section_code
    WHERE ce.student_id = ?
      AND s.course_code = ?
    ";
    
    $stmtSections = $conn->prepare($sqlSections);
    if (!$stmtSections) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    
    $stmtSections->bind_param("is", $user_id, $courseCode);
    $stmtSections->execute();
    $resultSections = $stmtSections->get_result();
    
    $sectionCodes = [];
    while ($row = $resultSections->fetch_assoc()) {
        $sectionCodes[] = $row['section_code'];
    }
    
    $stmtSections->close();
    
    if (empty($sectionCodes)) {
        throw new Exception("No enrolled sections found for the specified course.");
    }
    
    // Delete all entries from coursesenrolled for these section_codes
    $placeholders = implode(',', array_fill(0, count($sectionCodes), '?'));
    $types = str_repeat('i', count($sectionCodes));
    $sqlDelete = "DELETE FROM coursesenrolled WHERE student_id = ? AND section_code IN ($placeholders)";
    
    $stmtDelete = $conn->prepare($sqlDelete);
    if (!$stmtDelete) {
        throw new Exception("Delete query preparation failed: " . $conn->error);
    }
    
    // Bind parameters dynamically
    $stmtDelete->bind_param('i' . $types, $user_id, ...$sectionCodes);
    $stmtDelete->execute();
    
    if ($stmtDelete->affected_rows === 0) {
        throw new Exception("Failed to delete enrolled courses.");
    }
    
    $stmtDelete->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(["success" => "Course deleted successfully."]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error deleting course: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
