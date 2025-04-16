<?php

include 'config.php';
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['user_id']);
    session_destroy();
    header('location:login.php');
    exit;
}

if (isset($_POST['confirm_delete'])) {
    // delete shopping cart first
    mysqli_query($conn, "DELETE FROM shopping_cart WHERE user_id = '$user_id'");

    // now delete user
    $delete_query = mysqli_query($conn, "DELETE FROM user_form WHERE id = '$user_id'");

    if ($delete_query) {
        unset($_SESSION['user_id']);
        session_destroy();
        header('location:login.php');
        exit;
    } else {
        $message = "Error deleting account: " . mysqli_error($conn);
        echo "DEBUG: $message";
    }
}



if (isset($_POST['delete_selected'])) {
    if (isset($_POST['delete_selected']) && !empty($_POST['user_ids']) && is_array($_POST['user_ids'])) {
        foreach ($_POST['user_ids'] as $user_id_to_delete) {
            $user_id_to_delete = mysqli_real_escape_string($conn, $user_id_to_delete);
            mysqli_query($conn, "DELETE FROM shopping_cart WHERE user_id = '$user_id_to_delete'");
            mysqli_query($conn, "DELETE FROM user_form WHERE id = '$user_id_to_delete'");
        }

        $_SESSION['delete_success'] = true;
        header('Location: profile.php');
        exit;
    }
}



function displayUsers($conn, $fetch)
{
    $sql = "SELECT * FROM user_form";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<div class='card shadow-sm mb-5 p-4'>";
        echo "<h3 class='text-center mb-4'>Table of Users</h3>";
        echo "<form method='post' id='deleteUsersForm'>";
        echo "<div class='table-responsive'>";
        echo "<table id='userTable' class='table table-bordered'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Type of User</th><th>Select</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";

            if ($row['user_type'] == 'admin' || $row['user_type'] == 'owner') {
                if ($fetch['user_type'] == 'admin') {
                    echo "<td>RESTRICTED</td><td></td>";
                } else {
                    echo "<td>" . $row["user_type"] . "</td>";
                    echo "<td><input type='checkbox' name='user_ids[]' value='" . $row["id"] . "'></td>";
                }
            } else {
                echo "<td>" . $row["user_type"] . "</td>";
                echo "<td><input type='checkbox' name='user_ids[]' value='" . $row["id"] . "'></td>";
            }
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        echo "<button type='button' id='deleteSelectedBtn' class='btn btn-outline-danger'>Delete Selected Users</button>";
        echo "</form>";
        echo "</div>";

        echo <<<EOD
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteBtn = document.getElementById('deleteSelectedBtn');
            const form = document.getElementById('deleteUsersForm');
        
            if (deleteBtn && form) {
                deleteBtn.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Delete selected users?',
                        text: "This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete them!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Ensure hidden input is added for server-side check
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'delete_selected';
                            input.value = '1';
                            form.appendChild(input);
        
                            form.submit();
                        }
                    });
                });
            }
        });
        </script>
        EOD;



        if ($fetch['user_type'] == 'admin' || $fetch['user_type'] == 'owner') {
            echo "
            <div class='card mt-4 p-4 shadow-sm'>
                <h4 class='text-center mb-4'>Add New User</h4>
                <form id='addUserForm'>
                    <input type='text' name='name' placeholder='Name' class='form-control mb-2' required>
                    <input type='email' name='email' placeholder='Email' class='form-control mb-2' required>
                    <select name='user_type' class='form-control mb-2'>
                        <option value='user'>User</option>
                        <option value='admin'>Admin</option>
                    </select>
                    <button type='submit' class='btn btn-outline-primary w-100'>Add User</button>
                </form>
                <div id='addUserMsg' class='mt-2'></div>
            </div>";
        }
    } else {
        echo "0 results";
    }
}



function displayproduct($conn, $fetch)
{
    $user_id = $fetch['id'];

    if (!isset($_SESSION["shopping_cart"])) {
        $query = "SELECT * FROM shopping_cart WHERE user_id = '$user_id'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $_SESSION["shopping_cart"] = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $_SESSION["shopping_cart"] = [];
        }
    }

    // ADD TO CART
    if (isset($_POST["add"])) {
        if (isset($_SESSION["shopping_cart"])) {
            $item_array_id = array_column($_SESSION["shopping_cart"], "product_id");
            if (!in_array($_GET["id"], $item_array_id)) {
                $count = count($_SESSION["shopping_cart"]);
                $item_array = array(
                    'product_id' => $_GET["id"],
                    'product_name' => $_POST["hidden_name"],
                    'product_price' => $_POST["hidden_price"],
                    'product_quantity' => $_POST["quantity"],
                );
                $_SESSION["shopping_cart"][$count] = $item_array;
                saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Product added to cart',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                setTimeout(() => {
                    window.location = 'profile.php';
                }, 2100);
                </script>";
            } else {
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Product is already in the cart',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                setTimeout(() => {
                    window.location = 'profile.php';
                }, 2100);
                </script>";
            }
        } else {
            $item_array = array(
                'product_id' => $_GET["id"],
                'product_name' => $_POST["hidden_name"],
                'product_price' => $_POST["hidden_price"],
                'product_quantity' => $_POST["quantity"],
            );
            $_SESSION["shopping_cart"][0] = $item_array;
            saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);
        }
    }

    // REMOVE ITEM
    if (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"])) {
        foreach ($_SESSION["shopping_cart"] as $keys => $value) {
            if ($value["product_id"] == $_GET["id"]) {
                unset($_SESSION["shopping_cart"][$keys]);
                $_SESSION["shopping_cart"] = array_values($_SESSION["shopping_cart"]); // reindex
                break;
            }
        }

        saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);

        $_SESSION['product_removed'] = true;
        header("Location: profile.php");
        exit;
    }



    // UPDATE QUANTITY
    if (isset($_GET["action"]) && $_GET["action"] == "update" && isset($_POST["quantities"])) {
        foreach ($_POST["quantities"] as $productId => $newQty) {
            foreach ($_SESSION["shopping_cart"] as &$item) {
                if ($item["product_id"] == $productId) {
                    $item["product_quantity"] = max(1, intval($newQty));
                    break;
                }
            }
        }
        saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);
        header("Location: profile.php");
        exit;
    }
}


function saveShoppingCart($conn, $user_id, $shopping_cart)
{
    foreach ($shopping_cart as $item) {
        $product_id = $item['product_id'];
        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
        $product_price = floatval($item['product_price']);
        $product_quantity = intval($item['product_quantity']);

        $query = "INSERT INTO shopping_cart (user_id, product_id, product_name, product_price, product_quantity)
                  VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity')
                  ON DUPLICATE KEY UPDATE 
                      product_quantity = VALUES(product_quantity),
                      product_name = VALUES(product_name),
                      product_price = VALUES(product_price)";

        mysqli_query($conn, $query);
    }
}

function adminProducts($conn, $fetch)
{
    $sql = "SELECT * FROM product";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<div class='card shadow-sm p-4'>";
        echo "<h3 class='text-center mb-4'>Table of Products</h3>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered'>";
        echo "<tr>
                <th>ID</th>
                <th>Description</th>
                <th>Price</th>
                <th>Image</th>
                <th>Options</th>
            </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>" . $row["price"] . "</td>";
            echo "<td><img src='products_img/" . $row["image"] . "' width='100px' height='100px'></td>";
            echo "<td class='text-center'>
                    <a href='edit_product.php?id=" . $row["id"] . "' class='btn btn-outline-primary w-100 mb-2'>Edit</a>
                    <form method='post' class='delete-product-form'>
                        <input type='hidden' name='delete_id' value='" . $row["id"] . "'>
                        <button type='submit' name='delete' class='btn btn-outline-danger w-100'>Delete</button>
                    </form>
                </td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        echo "<a href='add_product.php' class='btn btn-outline-primary w-100'>Add Product</a>";
        echo "</div>";
    } else {
        echo "0 results";
    }
}


if (isset($_POST['delete'])) {
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];

        $conn->query("DELETE FROM shopping_cart WHERE product_id = $id");

        // Then delete the product itself
        $sql = "DELETE FROM product WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['product_deleted'] = true;
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['product_error'] = "Error deleting product: " . $conn->error;
            header("Location: profile.php");
            exit;
        }
    } else {
        $_SESSION['product_error'] = "Product ID not provided for deletion.";
        header("Location: profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>profile</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        .profile-avatar {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="main-container">
        <?php if (isset($_SESSION['delete_success'])): ?>
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Selected users deleted successfully',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            </script>
            <?php unset($_SESSION['delete_success']); ?>
        <?php endif; ?>


        <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100 px-4">
            <div class="container-fluid position-relative">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item mx-2">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link active" href="">Profile</a>
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

        <?php if (isset($_SESSION['product_removed'])): ?>
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Product has been removed',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            </script>
            <?php unset($_SESSION['product_removed']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['product_deleted'])): ?>
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Product deleted successfully',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            </script>
            <?php unset($_SESSION['product_deleted']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['product_error'])): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo $_SESSION["product_error"]; ?>'
                });
            </script>
            <?php unset($_SESSION['product_error']); ?>
        <?php endif; ?>



        <div class="px-4 py-5 w-100" style="max-width: 85%; margin: 0 auto;">
            <?php
            $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
            $fetch = mysqli_fetch_assoc($select);
            $imagePath = !empty($fetch['Image']) ? 'uploaded_img/' . $fetch['Image'] : 'images/default-avatar.png';
            ?>

            <?php if ($fetch['user_type'] == 'user'): ?>
                <!-- Centered layout for normal users -->
                <div class="d-flex justify-content-center align-items-center w-100" style="min-height: 70vh;">
                    <div class="card text-center shadow-sm p-4" style="width: 100%; max-width: 400px;">
                        <img src="<?= $imagePath ?>" class="profile-avatar mx-auto" />
                        <h3 class="mt-3">
                            <?= htmlspecialchars($fetch['name']) ?>
                            <small class="text-muted d-block"
                                style="font-size: 0.95rem;"><?= ucfirst($fetch['user_type']) ?></small>
                        </h3>
                        <a href="update_profile.php" class="btn btn-outline-primary w-100 my-2">Update Profile</a>

                        <?php if ($fetch['user_type'] != 'owner'): ?>
                            <button type="button" id="deleteAccountBtn" class="btn btn-outline-danger w-100 my-2">Delete Your
                                Account</button>
                            <form id="deleteAccountForm" method="post" action="" style="display: none;">
                                <input type="hidden" name="confirm_delete" value="1">
                            </form>
                        <?php endif; ?>

                        <a href="profile.php?logout=<?= $user_id ?>" class="btn btn-outline-secondary w-100 my-2">Logout</a>
                        <p class="mt-3">New <a href="login.php">Login</a> or <a href="register.php">Register</a></p>
                    </div>
                </div>
                <?php displayproduct($conn, $fetch); ?>
            <?php else: ?>
                <!-- Split layout for admin/owner -->
                <div class="row gx-5 gy-4">
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="card text-center shadow-sm p-4">
                            <img src="<?= $imagePath ?>" class="img-fluid rounded-circle mx-auto" width="120"
                                height="120" />
                            <h3 class="mt-3">
                                <?= htmlspecialchars($fetch['name']) ?>
                                <small class="text-muted d-block"
                                    style="font-size: 0.95rem;"><?= ucfirst($fetch['user_type']) ?></small>
                            </h3>
                            <a href="update_profile.php" class="btn btn-outline-primary w-100 my-2">Update Profile</a>

                            <?php if ($fetch['user_type'] != 'owner'): ?>
                                <button type="button" id="deleteAccountBtn" class="btn btn-outline-danger w-100 my-2">Delete
                                    Your Account</button>
                                <form id="deleteAccountForm" method="post" action="" style="display: none;">
                                    <input type="hidden" name="confirm_delete" value="1">
                                </form>
                            <?php endif; ?>

                            <a href="profile.php?logout=<?= $user_id ?>"
                                class="btn btn-outline-secondary w-100 my-2">Logout</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-9">
                        <?php
                        displayUsers($conn, $fetch);
                        echo "<br><br>";
                        adminProducts($conn, $fetch);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Chatbot Button -->
    <button id="chatbot-toggle">💬</button>

    <!-- Chatbot UI -->
    <div id="chat-container">
        <h3>AI Assistant</h3>
        <div id="chat-box"></div>
        <div class="input-container">
            <input type="text" id="user-input" placeholder="Type a message...">
            <button class="send-btn" onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        document.getElementById('chatbot-toggle').addEventListener('click', function () {
            const chatContainer = document.getElementById('chat-container');
            chatContainer.style.display = (chatContainer.style.display === 'none' || chatContainer.style.display === '') ? 'flex' : 'none';
        });

        function sendMessage() {
            const userInput = document.getElementById('user-input').value.trim();
            if (userInput === "") return;

            const chatBox = document.getElementById('chat-box');

            const userMessage = document.createElement('div');
            userMessage.className = 'user-message';
            userMessage.textContent = userInput;
            chatBox.appendChild(userMessage);

            fetch("chatbot.php", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: userInput })
            })
                .then(response => response.json())
                .then(data => {
                    const botMessage = document.createElement('div');
                    botMessage.className = 'bot-message';
                    botMessage.textContent = data.error ? `Bot: ${data.error}` : `Bot: ${data.response}`;
                    chatBox.appendChild(botMessage);
                    document.getElementById('user-input').value = '';
                    chatBox.scrollTop = chatBox.scrollHeight;
                })
                .catch(error => {
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'bot-message';
                    errorMessage.textContent = 'Bot: Failed to fetch response.';
                    chatBox.appendChild(errorMessage);
                });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#addUserForm").on("submit", function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: "ajax_add_user.php",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            $("#addUserMsg").html("<div class=\"alert alert-success\">User added successfully.</div>");
                            const newRow = "<tr>" +
                                "<td>" + response.id + "</td>" +
                                "<td>" + response.name + "</td>" +
                                "<td>" + response.email + "</td>" +
                                "<td>" + response.user_type + "</td>" +
                                "<td><input type='checkbox' name='user_ids[]' value='" + response.id + "'></td>" +
                                "</tr>";
                            $("#userTable tbody").append(newRow);
                            $("#addUserForm")[0].reset();
                        } else {
                            $("#addUserMsg").html("<div class=\"alert alert-danger\">" + response.message + "</div>");
                        }
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-delete-product').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.getAttribute('data-product-id');
                    Swal.fire({
                        title: 'Delete this product?',
                        text: "This cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'delete_selected';
                            hiddenInput.value = '1';
                            form.appendChild(hiddenInput);

                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

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
                    data: {
                        product_id: productId,
                        quantity: newQty
                    },
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
    </script>

    <script>
        document.getElementById('clearCartBtn').addEventListener('click', function () {
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
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
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

    <script>
        document.getElementById('deleteAccountBtn')?.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete your account?',
                text: "This will permanently delete your account and cart!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteAccountForm').submit();
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>