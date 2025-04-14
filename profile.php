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
    $query = "SELECT * FROM shopping_cart WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION["shopping_cart"] = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    if (isset($_GET["action"]) && $_GET["action"] == "delete") {
        foreach ($_SESSION["shopping_cart"] as $keys => $value) {
            if ($value["product_id"] == $_GET["id"]) {
                unset($_SESSION["shopping_cart"][$keys]);
                saveShoppingCart($conn, $user_id, $_SESSION["shopping_cart"]);
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
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
                setTimeout(() => {
                    window.location = 'profile.php';
                }, 2100);
                </script>";
                break;
            }
        }
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
    mysqli_query($conn, "DELETE FROM shopping_cart WHERE user_id = '$user_id'");

    foreach ($shopping_cart as $item) {
        $product_id = $item['product_id'];
        $product_name = mysqli_real_escape_string($conn, $item['product_name']);
        $product_price = floatval($item['product_price']);
        $product_quantity = intval($item['product_quantity']);

        $result = mysqli_query($conn, "INSERT INTO shopping_cart (user_id, product_id, product_name, product_price, product_quantity)
        VALUES ('$user_id', '$product_id', '$product_name', '$product_price', '$product_quantity')");

        if (!$result) {
            error_log("Insert Error: " . mysqli_error($conn));
        }
    }
}

function adminProducts($conn, $fetch)
{
    $sql = "SELECT * FROM product";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<div class='card shadow-sm mt-5 p-4'>";
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

        $sql = "DELETE FROM product WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "Product deleted successfully";
        } else {
            echo "Error deleting product: " . $conn->error;
        }
    } else {
        echo "Product ID not provided for deletion.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
</head>

<body>

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

    <div class="container-fluid px-4 py-5 bg-light">
        <div class="row gx-5 gy-4">

            <!-- Profile Column -->
            <div class="col-12 col-md-4 col-lg-3">
                <div class="card text-center shadow-sm p-4">
                    <?php
                    $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');

                    if (mysqli_num_rows($select) > 0) {
                        $fetch = mysqli_fetch_assoc($select);
                        $imagePath = !empty($fetch['Image']) ? 'uploaded_img/' . $fetch['Image'] : 'images/default-avatar.png';
                        echo '<img src="' . $imagePath . '" class="img-fluid rounded-circle mx-auto" width="120" height="120" />';
                    } else {
                        echo '<img src="images/default-avatar.png" class="img-fluid rounded-circle mx-auto" width="120" height="120" />';
                    }
                    ?>
                    <h3 class="mt-3"><?php echo $fetch['name']; ?></h3>

                    <a href="update_profile.php" class="btn btn-outline-primary w-100 my-2">Update Profile</a>

                    <?php
                    if (isset($fetch['user_type']) && ($fetch['user_type'] == 'user' || $fetch['user_type'] == 'admin')) {
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="confirm_delete" value="1">';
                        echo '<button type="submit" id="deleteAccountBtn" name="confirm_delete" class="btn btn-outline-danger w-100 my-2">Delete Your Account</button>';

                        echo '</form>';
                    }
                    ?>

                    <a href="profile.php?logout=<?php echo $user_id; ?>"
                        class="btn btn-outline-secondary w-100 my-2">Logout</a>

                    <p class="mt-3">New <a href="login.php">Login</a> or <a href="register.php">Register</a></p>
                </div>
            </div>

            <!-- Main Content Column -->
            <div class="col-12 col-md-8 col-lg-9">
                <?php
                if (isset($fetch['user_type']) && ($fetch['user_type'] == 'owner' || $fetch['user_type'] == 'admin')) {
                    displayUsers($conn, $fetch);
                    echo "<br><br>";
                    adminProducts($conn, $fetch);
                }

                if (isset($fetch['user_type']) && $fetch['user_type'] == 'user') {
                    displayproduct($conn, $fetch);
                    ?>

                    <div>
                        <h2 class="mb-3 shoppingcart">Shopping Cart</h2>
                        <div class="row g-4">
                            <?php
                            $query = "select * from product order by id asc";
                            $result = mysqli_query($conn, $query);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_array($result)) {
                                    ?>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <form method="post" action="profile.php?action=add&id=<?php echo $row["id"]; ?>">
                                            <div class="card h-100 shadow-sm p-3">
                                                <img src="products_img/<?php echo $row["image"]; ?>" width="100%" height="200px"
                                                    class="card-img-top rounded mb-2" style="object-fit: cover;">
                                                <h5 class="text-info"><?php echo $row["description"]; ?></h5>
                                                <h6 class="text-danger">£<?php echo $row["price"]; ?></h6>
                                                <input type="text" name="quantity" class="form-control mb-2" value="1">
                                                <input type="hidden" name="hidden_name" value="<?php echo $row["description"]; ?>">
                                                <input type="hidden" name="hidden_price" value="<?php echo $row["price"]; ?>">
                                                <input type="submit" name="add" class="btn-cart w-100" value="Add to cart">
                                            </div>
                                        </form>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <div class="table-responsive mt-5">
                            <h3 class="mb-3">Shopping Cart Details</h3>
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
                                    if (!empty($_SESSION["shopping_cart"])) {
                                        $total = 0;
                                        foreach ($_SESSION["shopping_cart"] as $key => $value) {
                                            ?>
                                            <tr>
                                                <td><?php echo $value["product_name"]; ?></td>
                                                <td>
                                                    <input type="number" class="form-control quantity-input"
                                                        data-id="<?php echo $value["product_id"]; ?>"
                                                        value="<?php echo $value["product_quantity"]; ?>" min="1">
                                                </td>
                                                <td>£<?php echo number_format($value["product_price"], 2); ?></td>
                                                <td>
                                                    <span class="item-total" data-id="<?php echo $value['product_id']; ?>">
                                                        £<?php echo number_format($value["product_quantity"] * $value["product_price"], 2); ?>
                                                    </span>
                                                </td>
                                                <td><a href="profile.php?action=delete&id=<?php echo $value["product_id"]; ?>"
                                                        class="text-danger">Remove Item</a></td>
                                            </tr>
                                            <?php
                                            $total += $value["product_quantity"] * $value["product_price"];
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total</td>
                                            <td id="cart-total" colspan="2">£<?php echo number_format($total, 2); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="text-end mt-3">
                                <a href="checkout.php" class="btn btn-outline-success">Proceed to Checkout</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
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
        $(document).ready(function () {
            $('.quantity-input').on('keypress', function (e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    let input = $(this);
                    let productId = input.data('id');
                    let newQty = input.val();

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
                                let itemTotalSpan = $('span.item-total[data-id="' + productId + '"]');
                                itemTotalSpan.text('£' + response.total);

                                let cartTotal = 0;
                                $('.item-total').each(function () {
                                    let amount = parseFloat($(this).text().replace('£', ''));
                                    if (!isNaN(amount)) cartTotal += amount;
                                });
                                $('#cart-total').text('£' + cartTotal.toFixed(2));
                            } else {
                                console.error('Update failed');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                }
            });
        });
    </script>

</body>


</html>