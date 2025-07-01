<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];

$query = "SELECT * FROM member WHERE username = '$username'";
$result = $conn->query($query);

if ($result->num_rows === 1) {
    $member = $result->fetch_assoc();
    if (password_verify($password, $member['password'])) {
        $_SESSION['member_id'] = $member['member_id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Credentials']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Credentials']);
}

$conn->close();
?>