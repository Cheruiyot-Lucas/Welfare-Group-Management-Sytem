<?php
session_start();
include 'config.php';
include'db.php';
header('Content-Type: application/json');

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];

$query = "SELECT * FROM admin WHERE username = '$username'";
$result = $conn->query($query);

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        echo json_encode(['success' => true]);
    } 
  
else {
        echo json_encode(['success' => false, 'message' => 'Invalid Credentials']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Credentials']);
}

$conn->close();
?>