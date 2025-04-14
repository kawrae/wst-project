<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$product_id = $_POST['product_id'];
$newQty = max(1, intval($_POST['quantity']));
$total = 0;

foreach ($_SESSION['shopping_cart'] as &$item) {
    if ($item['product_id'] == $product_id) {
        $item['product_quantity'] = $newQty;
        $total = $item['product_price'] * $newQty;
        break;
    }
}

function saveShoppingCart($conn, $user_id, $shopping_cart) {
    mysqli_query($conn, "DELETE FROM shopping_cart WHERE user_id = '$user_id'");

    foreach ($shopping_cart as $item) {
        $product_id = $item['product_id'];
        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
        $product_price = floatval($item['product_price']);
        $product_quantity = intval($item['product_quantity']);

        $query = "INSERT INTO shopping_cart (user_id, product_id, product_name, product_price, product_quantity)
                  VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity')";

        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Insert error: " . mysqli_error($conn));
        }
    }
}

saveShoppingCart($conn, $user_id, $_SESSION['shopping_cart']);

echo json_encode([
    'status' => 'success',
    'total' => number_format($total, 2)
]);
exit;
