<?php
include 'config.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$user_type = $_POST['user_type'] ?? 'user';

if (empty($name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Name and email are required.']);
    exit;
}

// prevent duplicate users
$check = mysqli_query($conn, "SELECT * FROM user_form WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
    exit;
}

$insert = mysqli_query($conn, "INSERT INTO user_form (name, email, user_type) VALUES ('$name', '$email', '$user_type')");
if ($insert) {
    echo json_encode([
        'status' => 'success',
        'id' => mysqli_insert_id($conn),
        'name' => $name,
        'email' => $email,
        'user_type' => $user_type
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert user.']);
}
