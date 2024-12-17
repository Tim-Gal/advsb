<?php
include '../includes/config.php';
include '../includes/functions.php';

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
$receiver_username = isset($data['receiver_username']) ? trim($data['receiver_username']) : '';

if (empty($receiver_username)) {
    echo json_encode(['error' => 'Receiver username is required.']);
    exit();
}



$sql = "SELECT student_id FROM students WHERE email = ?";
$stmt = $conn->prepare($sql);


$stmt->bind_param("s", $receiver_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found.']);
    $stmt->close();
    exit();
}

$receiver = $result->fetch_assoc();
$receiver_id = $receiver['student_id'];
$stmt->close();



if ($receiver_id == $user_id) {
    echo json_encode(['error' => 'You cannot send a friend request to yourself.']);
    exit();
}

$sql = "SELECT status FROM friendrequests WHERE 
        (sender_id = ? AND receiver_id = ?) OR 
        (sender_id = ? AND receiver_id = ?)";
$stmt = $conn->prepare($sql);


$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    if ($existing['status'] == 'pending') {
        echo json_encode(['error' => 'A friend request is already pending between you and this user.']);
    } elseif ($existing['status'] == 'accepted') {
        echo json_encode(['error' => 'You are already friends with this user.']);
    }
    $stmt->close();
    exit();
}
$stmt->close();


$sql = "INSERT INTO friendrequests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')";
$stmt = $conn->prepare($sql);


$stmt->bind_param("ii", $user_id, $receiver_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Friend request sent successfully.']);
} else {
    echo json_encode(['error' => 'Failed to send friend request.']);
}

$stmt->close();
?>
