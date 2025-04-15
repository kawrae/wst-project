<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid request'];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_type FROM user_form WHERE id = '$user_id'"));

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$user_type = $user['user_type'];

// ---- ADD USER ----
if ($action === 'add_user' && in_array($user_type, ['admin', 'owner'])) {
    $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
    $user_type_input = mysqli_real_escape_string($conn, $data['user_type'] ?? 'user');
    $defaultPassword = md5("password123");

    $insert = mysqli_query($conn, "INSERT INTO user_form (name, email, password, user_type)
                                   VALUES ('$name', '$email', '$defaultPassword', '$user_type_input')");

    if ($insert) {
        $response = ['status' => 'success', 'message' => "User '$name' added successfully."];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to add user.'];
    }
}

// ---- ADD PRODUCT ----
else if ($action === 'add_product' && in_array($user_type, ['admin', 'owner'])) {
    $description = mysqli_real_escape_string($conn, $data['description'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $image = mysqli_real_escape_string($conn, $data['image'] ?? '');

    $insert = mysqli_query($conn, "INSERT INTO product (description, price, image)
                                   VALUES ('$description', '$price', '$image')");

    if ($insert) {
        $response = ['status' => 'success', 'message' => "Product added: $description (£$price)"];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to add product.'];
    }
}

// ---- LIST USERS ----
else if ($action === 'list_users' && in_array($user_type, ['admin', 'owner'])) {
    $users = [];
    $query = mysqli_query($conn, "SELECT name, email, user_type FROM user_form");
    while ($row = mysqli_fetch_assoc($query)) {
        $users[] = $row;
    }
    $response = ['status' => 'success', 'users' => $users];
}

// ---- LIST PRODUCTS ----
else if ($action === 'list_products') {
    $products = [];
    $query = mysqli_query($conn, "SELECT description, price, image FROM product");
    while ($row = mysqli_fetch_assoc($query)) {
        $products[] = $row;
    }
    $response = ['status' => 'success', 'products' => $products];
}

// ---- Invalid or Unauthorized ----
else if (!in_array($action, ['list_products', 'list_users', 'add_user', 'add_product'])) {
    $response = ['status' => 'error', 'message' => 'Unknown or unauthorized action'];
}

echo json_encode($response);
