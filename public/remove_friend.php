<?php
// public/remove_friend.php
include '../includes/config.php';
include '../includes/functions.php';

session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    http_response_code(403);
    echo json_encode(["error"=>"Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = $_GET['friend_id'] ?? '';
if (!ctype_digit($friend_id)){
    echo json_encode(["error"=>"Invalid friend_id"]);
    exit();
}

$friend_id = (int)$friend_id;

$del = $conn->prepare("DELETE FROM FriendsWith WHERE student_id1=? AND student_id2=?");
$del->bind_param("ii",$user_id,$friend_id);
$del->execute();

echo json_encode(["success"=>true]);
