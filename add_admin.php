<?php

include 'config.php';
session_start();
$user_id = isset ($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('location:login.php');
    exit;
}

$sql = "SELECT * FROM user_form WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    if ($user['user_type'] != 'owner' && $user['user_type'] != 'admin') {
        echo "You do not have permission to view this page.";
        exit;
    }
} else {
    echo "User not found.";
    exit;
}

echo "<h3>Add Admin User</h3>";
echo "<form action='' method='post'>";
echo "Name: <input type='text' name='name'><br>";
echo "Email: <input type='text' name='email'><br>";
echo "Password: <input type='text' name='password'><br>";
echo "<input type='submit' name='add_admin' value='Add User'>";
echo "</form>";


if(isset($_POST['add_admin']))  {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $user_type = 'admin';
    $sql = "INSERT INTO user_form (name, email, password, user_type) VALUES ('$name', '$email', '$password', '$user_type')";
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
         header('location:profile.php');
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
