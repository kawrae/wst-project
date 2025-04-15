<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('location:login.php');
    exit;
}

if (!isset($_SESSION["shopping_cart"])) {
    $query = "SELECT * FROM shopping_cart WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $_SESSION["shopping_cart"] = mysqli_num_rows($result) > 0 ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap, Font Awesome, SweetAlert2, jQuery -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="cart.php">Cart</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4 text-center">Your Shopping Cart</h2>

        <?php if (!empty($_SESSION["shopping_cart"])): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Product Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION["shopping_cart"] as $item):
                            $subtotal = $item["product_quantity"] * $item["product_price"];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?= $item["product_name"] ?></td>
                            <td>
                                <input type="number" class="form-control quantity-input" data-id="<?= $item["product_id"] ?>" value="<?= $item["product_quantity"] ?>" min="1">
                            </td>
                            <td>£<?= number_format($item["product_price"], 2) ?></td>
                            <td><span class="item-total" data-id="<?= $item["product_id"] ?>">£<?= number_format($subtotal, 2) ?></span></td>
                            <td><a href="cart.php?action=delete&id=<?= $item["product_id"] ?>" class="text-danger">Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total</td>
                            <td id="cart-total" colspan="2">£<?= number_format($total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-end align-items-stretch mt-3" style="gap: 1rem;">
                <form method="post" id="clearCartForm" class="w-100 w-md-auto">
                    <button type="button" class="btn btn-outline-danger w-100" id="clearCartBtn">Clear Cart</button>
                </form>
                <a href="checkout.php" class="btn btn-outline-success w-100 w-md-auto">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <p class="text-center">Your cart is currently empty.</p>
        <?php endif; ?>
    </div>

    <!-- SweetAlert2 Logic -->
    <script>
    let debounceTimeout;
    $('.quantity-input').on('input', function () {
        clearTimeout(debounceTimeout);
        const input = $(this);
        debounceTimeout = setTimeout(() => {
            const productId = input.data('id');
            const newQty = parseInt(input.val(), 10);
            if (isNaN(newQty) || newQty < 1) return;
            input.prop('disabled', true);

            $.ajax({
                url: 'update_quantity.php',
                method: 'POST',
                data: { product_id: productId, quantity: newQty },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('span.item-total[data-id="' + productId + '"]').text('£' + response.itemTotal);
                        $('#cart-total').text('£' + response.cartTotal);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Quantity updated',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                complete: function () {
                    input.prop('disabled', false);
                }
            });
        }, 500);
    });

    $('#clearCartBtn').on('click', function () {
        Swal.fire({
            title: 'Clear your cart?',
            text: "This will remove all products from your cart.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('clear_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                }).then(res => res.json()).then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Cart cleared',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        setTimeout(() => location.reload(), 1600);
                    } else {
                        Swal.fire('Oops!', data.message || 'Failed to clear cart.', 'error');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>
