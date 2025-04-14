<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$product_id = $_POST['product_id'];
$quantity = trim($_POST['quantity']);

if (!is_numeric($quantity) || intval($quantity) < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity']);
    exit;
}

$newQty = intval($quantity);
$itemTotal = 0;

foreach ($_SESSION['shopping_cart'] as &$item) {
    if ((string) $item['product_id'] === (string) $product_id) {
        $item['product_quantity'] = $newQty;
        $itemTotal = $item['product_price'] * $newQty;
        break;
    }
}
$_SESSION['shopping_cart'] = array_values($_SESSION['shopping_cart']); // reindex

function saveShoppingCart($conn, $user_id, $shopping_cart) {
    foreach ($shopping_cart as $item) {
        $product_id = $item['product_id'];
        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
        $product_price = floatval($item['product_price']);
        $product_quantity = intval($item['product_quantity']);

        $update = mysqli_query($conn, "UPDATE shopping_cart 
            SET product_quantity = '$product_quantity',
                product_name = '$product_name',
                product_price = '$product_price'
            WHERE user_id = '$user_id' AND product_id = '$product_id'");

        if (mysqli_affected_rows($conn) === 0) {
            $insert = "INSERT INTO shopping_cart (user_id, product_id, product_name, product_price, product_quantity)
                       VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity')";
            mysqli_query($conn, $insert);
        }
    }
}

function reloadCartFromDB($conn, $user_id) {
    $result = mysqli_query($conn, "SELECT * FROM shopping_cart WHERE user_id = '$user_id'");
    $_SESSION['shopping_cart'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

saveShoppingCart($conn, $user_id, $_SESSION['shopping_cart']);
reloadCartFromDB($conn, $user_id);

$cartTotal = 0;
foreach ($_SESSION['shopping_cart'] as $item) {
    $cartTotal += $item['product_quantity'] * $item['product_price'];
}

echo json_encode([
    'status' => 'success',
    'itemTotal' => number_format($itemTotal, 2),
    'cartTotal' => number_format($cartTotal, 2)
]);
exit;
