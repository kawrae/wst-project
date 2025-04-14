<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

mysqli_query($conn, "DELETE FROM shopping_cart WHERE user_id = '$user_id'");

unset($_SESSION['shopping_cart']);

echo json_encode(['status' => 'success']);
exit;
?>
