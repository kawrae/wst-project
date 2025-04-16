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

function success($msg)
{
    echo json_encode(['status' => 'success', 'message' => $msg]);
    exit;
}

function fail($msg)
{
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

switch ($action) {
    // ---- ADD USER ----
    case 'add_user':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");

        $id = mysqli_real_escape_string($conn, $data['id'] ?? '');
        $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
        $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
        $password = mysqli_real_escape_string($conn, md5($data['password'] ?? ''));
        $user_type_input = mysqli_real_escape_string($conn, $data['user_type'] ?? 'user');

        if (!$id || !$name || !$email || !$password || !$user_type_input) {
            fail("Usage: add user \"id\" \"name\" \"email\" \"password\" \"user_type\"");
        }

        if ($user_type_input === 'owner' && $user_type !== 'owner') {
            fail("Only owners can add another owner.");
        }

        $check = mysqli_query($conn, "SELECT id FROM user_form WHERE id = '$id'");
        if (mysqli_num_rows($check) > 0) {
            fail("User ID '$id' already exists.");
        }

        $insert = mysqli_query($conn, "INSERT INTO user_form (id, name, email, password, user_type)
                                       VALUES ('$id', '$name', '$email', '$password', '$user_type_input')");
        $insert ? success("User '$name' added with password.") : fail("Failed to add user.");
        break;

    // ---- DELETE USER ----
    case 'delete_user':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");
        $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
        $email = mysqli_real_escape_string($conn, $data['email'] ?? '');
        if (!$name || !$email)
            fail("Missing name or email.");
        $check = mysqli_query($conn, "SELECT * FROM user_form WHERE name='$name' AND email='$email'");
        if (mysqli_num_rows($check) === 0)
            fail("User '$name' not found.");
        $delete = mysqli_query($conn, "DELETE FROM user_form WHERE name='$name' AND email='$email'");
        $delete ? success("User '$name' deleted.") : fail("Failed to delete user.");
        break;

    // ---- ADD PRODUCT ----
    case 'add_product':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");
        $id = trim(mysqli_real_escape_string($conn, $data['id'] ?? ''));
        $description = trim(mysqli_real_escape_string($conn, $data['description'] ?? ''));
        $price = $data['price'] ?? null;
        $image = trim(mysqli_real_escape_string($conn, $data['image'] ?? ''));
        if ($id === '' || $description === '' || $image === '' || !is_numeric($price) || floatval($price) <= 0)
            fail("Invalid product input.");
        $price = floatval($price);
        $insert = mysqli_query($conn, "INSERT INTO product (id, description, price, image)
                                       VALUES ('$id', '$description', '$price', '$image')");
        $insert ? success("Product '$description' added.") : fail("Failed to add product.");
        break;

    // ---- EDIT PRODUCT ----
    case 'edit_product':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");
        $id = trim(mysqli_real_escape_string($conn, $data['id'] ?? ''));
        $description = trim(mysqli_real_escape_string($conn, $data['description'] ?? ''));
        $price = $data['price'] ?? null;
        $image = trim(mysqli_real_escape_string($conn, $data['image'] ?? ''));
        if ($id === '' || $description === '' || $image === '' || !is_numeric($price) || floatval($price) <= 0)
            fail("Invalid product input.");
        $price = floatval($price);
        $update = mysqli_query($conn, "UPDATE product 
                                       SET description='$description', price='$price', image='$image' 
                                       WHERE id='$id'");
        $update ? success("Product ID '$id' updated.") : fail("Failed to update product.");
        break;

    // ---- DELETE PRODUCT ----
    case 'delete_product':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");
        $name = mysqli_real_escape_string($conn, $data['name'] ?? '');
        if (!$name)
            fail("Missing product name.");
        $check = mysqli_query($conn, "SELECT * FROM product WHERE description='$name'");
        if (mysqli_num_rows($check) === 0)
            fail("Product '$name' not found.");
        $delete = mysqli_query($conn, "DELETE FROM product WHERE description='$name'");
        $delete ? success("Product '$name' deleted.") : fail("Failed to delete product.");
        break;

    // ---- LIST USERS ----
    case 'list_users':
        if (!in_array($user_type, ['admin', 'owner']))
            fail("Unauthorized");
        $users = [];
        $query = mysqli_query($conn, "SELECT name, email, user_type FROM user_form");
        while ($row = mysqli_fetch_assoc($query))
            $users[] = $row;
        echo json_encode(['status' => 'success', 'users' => $users]);
        break;

    // ---- LIST PRODUCTS ----
    case 'list_products':
        $products = [];
        $query = mysqli_query($conn, "SELECT description, price, image FROM product");
        while ($row = mysqli_fetch_assoc($query))
            $products[] = $row;
        echo json_encode(['status' => 'success', 'products' => $products]);
        break;

    // ---- Fallback ----
    default:
        fail("Unknown or unauthorized action.");
}
?>