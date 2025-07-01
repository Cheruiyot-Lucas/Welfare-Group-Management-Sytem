<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (empty($data['firstName']) || empty($data['lastName']) || empty($data['nationalId']) || 
    empty($data['phoneNumber']) || empty($data['email']) || empty($data['dob']) || 
    empty($data['username']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Check if username or email already exists
$username = $conn->real_escape_string($data['username']);
$email = $conn->real_escape_string($data['email']);
$nationalId = $conn->real_escape_string($data['nationalId']);
$phoneNumber = $conn->real_escape_string($data['phoneNumber']);

$check_query = "SELECT * FROM member WHERE username = '$username' OR email = '$email' OR national_id = '$nationalId' OR phone_number = '$phoneNumber'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username, email, national ID or phone number already exists']);
    exit();
}

// Generate member ID
$member_id = 'M' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

// Hash password
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

// Insert member
$insert_query = "INSERT INTO member (member_id, first_name, last_name, national_id, phone_number, email, username, password, dob) 
                 VALUES ('$member_id', 
                         '" . $conn->real_escape_string($data['firstName']) . "', 
                         '" . $conn->real_escape_string($data['lastName']) . "', 
                         '$nationalId', 
                         '$phoneNumber', 
                         '$email', 
                         '$username', 
                         '$hashed_password', 
                         '" . $conn->real_escape_string($data['dob']) . "')";

if ($conn->query($insert_query)) {
    // Create account for member
    $account_id = 'ACC' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $account_query = "INSERT INTO account (account_id, member_id, balance) VALUES ('$account_id', '$member_id', 0)";
    $conn->query($account_query);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
}

$conn->close();
?>