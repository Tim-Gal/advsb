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



$sql = "
    SELECT u.student_id, u.username, u.email
    FROM friendswith fw
    JOIN students u ON 
        (fw.student_id1 = u.student_id AND fw.student_id2 = ?) OR 
        (fw.student_id2 = u.student_id AND fw.student_id1 = ?)
";
$stmt = $conn->prepare($sql);

$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
while ($row = $result->fetch_assoc()) {
    $friends[] = [
        'id' => $row['student_id'],'name' => $row['username'],
        'email' => $row['email']
    ];
}
$stmt->close();
$sql = "
    SELECT u.student_id, u.username, u.email
    FROM friendrequests fr
    JOIN students u ON fr.sender_id = u.student_id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pending_received = [];
while ($row = $result->fetch_assoc()) {
    $pending_received[] = [
        'id' => $row['student_id'],
        'name' => $row['username'],
        'email' => $row['email']
    ];
}
$stmt->close();

$sql = "
    SELECT u.student_id, u.username, u.email FROM friendrequests fr
    JOIN students u ON fr.receiver_id = u.student_id
    WHERE fr.sender_id = ? AND fr.status = 'pending'
";
$stmt = $conn->prepare($sql);


$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();




$pending_sent = [];
while ($row = $result->fetch_assoc()) {
    $pending_sent[] = [
        'id' => $row['student_id'],
        'name' => $row['username'],
        'email' => $row['email']
    ];
}
$stmt->close();

echo json_encode([
    'success' => true,
    'friends' => $friends,
    'pending_received' => $pending_received,
    'pending_sent' => $pending_sent
]);
?>
