
<!--  Coded by Efe-->

<?php
include '../../includes/config.php';
include '../../includes/functions.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized. Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$friend_id = isset($data['friend_id']) ? intval($data['friend_id']) : 0;


if (empty($friend_id)) {
    echo json_encode(['error' => 'Friend ID is required.']);
    exit();
}

$sql = "
    SELECT id, student_id1, student_id2
    FROM friendswith
    WHERE (student_id1 = ? AND student_id2 = ?) OR 
    (student_id1 = ? AND student_id2 = ?)
    LIMIT 1";
$stmt = $conn->prepare($sql);

$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Friendship does not exist.']);
    $stmt->close();
    exit();
}

$friendship = $result->fetch_assoc();
$friendship_id = $friendship['id'];
$stmt->close();

$sql_delete = "DELETE FROM friendswith WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);


$stmt_delete->bind_param("i", $friendship_id);

if ($stmt_delete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Friend removed successfully.']);
} else {
    echo json_encode(['error' => 'Failed to remove friend.']);
}

$stmt_delete->close();
?>
