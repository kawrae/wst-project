<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

$user_query = mysqli_query($conn, "SELECT * FROM user_form WHERE id = '$user_id'");
$fetch = mysqli_fetch_assoc($user_query);

if (!isset($fetch['user_type']) || !in_array($fetch['user_type'], ['owner', 'admin'])) {
    header('location:profile.php');
    exit;
}

$message = [];
$product_id = $_GET['id'] ?? null;

if ($product_id) {
    $product_query = mysqli_query($conn, "SELECT * FROM product WHERE id = '$product_id'");
    if (mysqli_num_rows($product_query) === 1) {
        $product = mysqli_fetch_assoc($product_query);
    } else {
        $message[] = "Product not found!";
    }
} else {
    header('location:profile.php');
    exit;
}

if (isset($_POST['update_product'])) {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_folder = 'products_img/' . $image;

    if (empty($description) || empty($price)) {
        $message[] = 'Please fill out all required fields!';
    } else {
        if (!empty($image)) {
            move_uploaded_file($image_tmp, $image_folder);
            $update = mysqli_query($conn, "UPDATE product SET description='$description', price='$price', image='$image' WHERE id='$product_id'");
        } else {
            $update = mysqli_query($conn, "UPDATE product SET description='$description', price='$price' WHERE id='$product_id'");
        }

        if ($update) {
            $message[] = 'Product updated successfully!';
            header('Location: profile.php');
            exit;
        } else {
            $message[] = 'Failed to update product!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
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

    <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data">
            <h3>Edit Product</h3>

            <?php
            if (!empty($message)) {
                foreach ($message as $msg) {
                    echo "<div class='message'>" . htmlspecialchars($msg) . "</div>";
                }
            }
            ?>

            <input type="text" name="description" value="<?= htmlspecialchars($product['description']) ?>"
                placeholder="Product Description" class="box" required>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>"
                placeholder="Product Price" class="box" required>

            <div style="margin: 10px 0;">
                <img src="products_img/<?= htmlspecialchars($product['image']) ?>" width="100"
                    style="border-radius: 5px;">
            </div>

            <input type="file" name="image" accept="image/*" class="box">

            <input type="submit" name="update_product" value="Update Product" class="btn btn-outline-primary">
            <a href="profile.php" class="btn btn-outline-primary">Cancel</a>
        </form>
    </div>

</body>

</html>