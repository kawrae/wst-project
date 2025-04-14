<?php
session_start();
include 'config.php';

$cart = $_SESSION["shopping_cart"] ?? [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
    <style>
        .card {
            max-width: 600px;
            margin: 40px auto;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link disabled" href="#">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="profile.php">Profile</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link disabled" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid bg-light min-vh-100 py-5 d-flex justify-content-center align-items-start">
        <div class="card shadow p-4">
            <h3 class="mb-4 text-center">Checkout Summary</h3>
            <?php if (!empty($cart)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item):
                            $item_total = $item['product_quantity'] * $item['product_price'];
                            $total += $item_total;
                            ?>
                            <tr>
                                <td><?= $item['product_name']; ?></td>
                                <td><?= $item['product_quantity']; ?></td>
                                <td>£<?= number_format($item['product_price'], 2); ?></td>
                                <td>£<?= number_format($item_total, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td><strong>£<?= number_format($total, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                <div class="alert alert-info text-center mt-3">
                    This is a placeholder checkout page.<br>
                    Checkout functionality is not yet implemented.
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">Your cart is empty.</div>
            <?php endif; ?>
            <div class="text-center mt-3">
                <a href="profile.php" class="btn btn-outline-secondary">Back to Shop</a>
            </div>
        </div>
    </div>
</body>

</html>