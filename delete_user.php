<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

$sql= "DELETE FROM user_form WHERE id='$user_id'";
if ($conn->query($sql) === TRUE) {
    echo "Your account has been deleted successfully";
     header('location:login.php');
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


?>