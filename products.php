<?php
include 'config.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('location:login.php');
    exit;
}

$select = mysqli_query($conn, "SELECT * FROM user_form WHERE id = '$user_id'");
$fetch = mysqli_fetch_assoc($select);

if (!isset($_SESSION["shopping_cart"])) {
    $query = "SELECT * FROM shopping_cart WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $_SESSION["shopping_cart"] = mysqli_num_rows($result) > 0 ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

// Handle Add to Cart
if (isset($_POST["add"])) {
    $product_id = $_GET["id"];
    $item_array_id = array_column($_SESSION["shopping_cart"], "product_id");

    if (!in_array($product_id, $item_array_id)) {
        $item_array = [
            'product_id' => $product_id,
            'product_name' => $_POST["hidden_name"],
            'product_price' => $_POST["hidden_price"],
            'product_quantity' => $_POST["quantity"],
        ];
        $_SESSION["shopping_cart"][] = $item_array;
        saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);
        $_SESSION['cart_added'] = true;
    } else {
        $_SESSION['cart_duplicate'] = true;
    }

    header('Location: products.php');
    exit;
}

function saveShoppingCart($conn, $user_id, $shopping_cart)
{
    foreach ($shopping_cart as $item) {
        $product_id = $item['product_id'];
        $name = mysqli_real_escape_string($conn, $item['product_name']);
        $price = floatval($item['product_price']);
        $qty = intval($item['product_quantity']);

        $query = "INSERT INTO shopping_cart (user_id, product_id, product_name, product_price, product_quantity)
                  VALUES ('$user_id', '$product_id', '$name', '$price', '$qty')
                  ON DUPLICATE KEY UPDATE 
                      product_quantity = VALUES(product_quantity),
                      product_name = VALUES(product_name),
                      product_price = VALUES(product_price)";
        mysqli_query($conn, $query);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap + Icons + SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100 px-4">
        <div class="container-fluid position-relative">
            <ul class="navbar-nav mx-auto text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="products.php">Products</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
            </ul>

            <div class="dropdown position-absolute end-0 me-3">
                <button class="cart-btn d-flex align-items-center" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa fa-shopping-cart me-2"></i>
                    <span>Cart</span>
                    <span class="cart-badge"><?= count($_SESSION["shopping_cart"] ?? []) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-3 shadow cart-dropdown" style="width: 320px;">
                    <div class="cart-scroll-wrapper" style="max-height: 250px; overflow-y: auto;">
                        <?php if (!empty($_SESSION["shopping_cart"])): ?>
                            <?php foreach ($_SESSION["shopping_cart"] as $item): ?>
                                <li class="mb-2 border-bottom pb-2">
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                    <small>Qty: <?= $item['product_quantity'] ?> —
                                        £<?= number_format($item['product_price'], 2) ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted text-center">Your cart is empty.</li>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($_SESSION["shopping_cart"])): ?>
                        <li class="text-center mt-3">
                            <a href="cart.php" class="btn btn-outline-primary btn-sm w-100">View Full Cart</a>
                        </li>
                    <?php endif; ?>
                </ul>

            </div>

        </div>
    </nav>


    <div class="container py-5">
        <div class="d-flex flex-row gap-4 align-start">
            <!-- Filter Sidebar -->
            <div class="flex-shrink-0" style="width: 280px;">
                <form method="get" class="p-4 shadow-sm rounded bg-white">
                    <h5 class="mb-3">Filter</h5>

                    <div class="mb-3">
                        <label for="search" class="form-label">Search Products</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Enter product name" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="brand" class="form-label">Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="">All</option>
                            <option value="Samsung" <?= ($_GET['brand'] ?? '') === 'Samsung' ? 'selected' : '' ?>>Samsung</option>
                            <option value="Google" <?= ($_GET['brand'] ?? '') === 'Google' ? 'selected' : '' ?>>Google</option>
                            <option value="Apple" <?= ($_GET['brand'] ?? '') === 'Apple' ? 'selected' : '' ?>>Apple</option>
                            <option value="Honor" <?= ($_GET['brand'] ?? '') === 'Honor' ? 'selected' : '' ?>>Honor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="max_price" class="form-label">Max Price (£)</label>
                        <input type="number" name="max_price" id="max_price" class="form-control"
                            value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" placeholder="e.g. 1000">

                    </div>

                    <button type="submit" class="btn btn-outline-primary w-100">Apply Filter</button>
                    <button type="button" class="btn btn-outline-danger w-100" onclick="window.location.href='products.php'">Clear Filter</button>
                </form>
            </div>

            <!-- Product Grid -->
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap gap-4">
                    <?php
                    $query = "SELECT * FROM product WHERE 1=1";

                    if (!empty($_GET['brand'])) {
                        $brand = mysqli_real_escape_string($conn, $_GET['brand']);
                        $query .= " AND description LIKE '%$brand%'";
                    }

                    if (!empty($_GET['max_price'])) {
                        $price = floatval($_GET['max_price']);
                        $query .= " AND price <= $price";
                    }

                    if (!empty($_GET['search'])) {
                        $search = mysqli_real_escape_string($conn, $_GET['search']);
                        $query .= " AND description LIKE '%$search%'";
                    }

                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <div style="width: 220px; flex: 0 0 auto;">
                                <form method="post" action="products.php?action=add&id=<?= $row["id"] ?>" class="h-100">
                                    <div class="card h-100 shadow-sm p-3">
                                        <img src="products_img/<?= $row["image"] ?>" height="200"
                                            class="card-img-top rounded mb-2" style="object-fit: cover;">
                                        <h5 style="color:black;"><?= $row["description"] ?></h5>
                                        <h6 class="text-danger">£<?= $row["price"] ?></h6>
                                        <input type="text" name="quantity" class="form-control mb-2" value="1">
                                        <input type="hidden" name="hidden_name" value="<?= $row["description"] ?>">
                                        <input type="hidden" name="hidden_price" value="<?= $row["price"] ?>">
                                        <input type="submit" name="add" class="btn-cart w-100" value="Add to cart">
                                    </div>
                                </form>
                            </div>
                        <?php endwhile;
                    else: ?>
                        <p class="text-center">No products found matching your filters.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>



        <script>
            <?php if (isset($_SESSION['cart_added'])): ?>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Product added to cart',
                    showConfirmButton: false,
                    timer: 2000
                });
                <?php unset($_SESSION['cart_added']); endif; ?>

            <?php if (isset($_SESSION['cart_duplicate'])): ?>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Product is already in the cart',
                    showConfirmButton: false,
                    timer: 2000
                });
                <?php unset($_SESSION['cart_duplicate']); endif; ?>
        </script>

        <!-- js -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>