
<!--  Coded by Efe-->


<?php
session_start();
include '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    $student_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE students SET remember_token=NULL WHERE student_id=?");
    if ($stmt) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();
    }
}

session_unset();
session_destroy();

setcookie("remember_me", "", time() - 3600, "/");




session_start();
$_SESSION['logout_success'] = "You have been logged out successfully.";
header("Location: ../index.php");
exit();
?>
