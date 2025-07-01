<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (empty($data['accessCode']) || empty($data['username']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Check if access code is valid (in a real system, this would be a predefined code)
$valid_access_code = "ADMIN123"; // This should be stored securely in your database

if ($data['accessCode'] !== $valid_access_code) {
    echo json_encode(['success' => false, 'message' => 'Invalid access code']);
    exit();
}

// Check if username already exists
$username = $conn->real_escape_string($data['username']);
$check_query = "SELECT * FROM admin WHERE username = '$username'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit();
}

// Generate admin ID
$admin_id = 'A' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

// Hash password
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

// Insert admin
$insert_query = "INSERT INTO admin (admin_id, username, password, access_code) 
                 VALUES ('$admin_id', 
                         '$username', 
                         '$hashed_password', 
                         '" . $conn->real_escape_string($data['accessCode']) . "')";

if ($conn->query($insert_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
}

$conn->close();
?>