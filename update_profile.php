<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

$alerts = [];

$select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
if (mysqli_num_rows($select) > 0) {
   $fetch = mysqli_fetch_assoc($select);
}

if (isset($_POST['update_profile'])) {

   $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   $update_email = mysqli_real_escape_string($conn, $_POST['update_email']);

   mysqli_query($conn, "UPDATE `user_form` SET name = '$update_name', email = '$update_email' WHERE id = '$user_id'") or die('query failed');
   $alerts[] = ['success', 'Username and email updated successfully!'];

   $old_pass = $_POST['old_pass'] ?? '';
   $update_pass = mysqli_real_escape_string($conn, md5($_POST['update_pass'] ?? ''));
   $new_pass = mysqli_real_escape_string($conn, md5($_POST['new_pass'] ?? ''));
   $confirm_pass = mysqli_real_escape_string($conn, md5($_POST['confirm_pass'] ?? ''));
   $has_password_update = !empty($_POST['update_pass']) || !empty($_POST['new_pass']) || !empty($_POST['confirm_pass']);

   $user_has_password = !empty($fetch['password']) && $fetch['password'] !== md5('');

   if ($has_password_update) {
      if (!$user_has_password) {
         if ($new_pass != $confirm_pass) {
            $alerts[] = ['error', 'Confirm password not matched!'];
         } else {
            mysqli_query($conn, "UPDATE `user_form` SET password = '$confirm_pass' WHERE id = '$user_id'") or die('query failed');
            $alerts[] = ['success', 'Password added successfully!'];
         }
      } else {
         if ($update_pass != $old_pass) {
            $alerts[] = ['error', 'Old password not matched!'];
         } elseif ($new_pass != $confirm_pass) {
            $alerts[] = ['error', 'Confirm password not matched!'];
         } else {
            mysqli_query($conn, "UPDATE `user_form` SET password = '$confirm_pass' WHERE id = '$user_id'") or die('query failed');
            $alerts[] = ['success', 'Password updated successfully!'];
         }
      }
   }

   $update_image = $_FILES['update_image']['name'] ?? '';
   $update_image_size = $_FILES['update_image']['size'] ?? 0;
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'] ?? '';
   $update_image_folder = 'uploaded_img/' . $update_image;

   if (!empty($update_image)) {
      if ($update_image_size > 2000000) {
         $alerts[] = ['error', 'Image is too large'];
      } else {
         $image_update_query = mysqli_query($conn, "UPDATE `user_form` SET image = '$update_image' WHERE id = '$user_id'") or die('query failed');
         if ($image_update_query) {
            if (move_uploaded_file($update_image_tmp_name, $update_image_folder)) {
               $_SESSION['Image'] = $update_image;
               $alerts[] = ['success', 'Image updated successfully!'];
            } else {
               $alerts[] = ['error', 'Failed to move uploaded image!'];
            }
         }
      }
   }

   $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
   if (mysqli_num_rows($select) > 0) {
      $fetch = mysqli_fetch_assoc($select);
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>
   <link rel="stylesheet" href="style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
   <style>
      button.btn:not(.btn-outline-secondary):not(.btn-outline-danger):not(.btn-outline-primary),
      a.btn:not(.btn-outline-secondary):not(.btn-outline-danger):not(.btn-outline-primary) {
         background: #3498db !important;
         color: white !important;
         border: none !important;
      }

      .btn-outline-danger {
         border: 2px solid #dc3545 !important;
         color: #dc3545 !important;
         background-color: transparent !important;
      }

      .btn-outline-danger:hover {
         background-color: #dc3545 !important;
         color: white !important;
      }
   </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link" href="index.php">Home</a>
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

   <div class="update-profile">
      <form action="" method="post" enctype="multipart/form-data">
         <?php
         if (!empty($fetch['Image'])) {
            echo '<img src="uploaded_img/' . $fetch['Image'] . '?v=' . time() . '">';
         } else {
            echo '<img src="images/default-avatar.png">';
         }
         ?>
         <div class="flex">
            <div class="inputBox">
               <span>username :</span>
               <input type="text" name="update_name" value="<?php echo $fetch['name']; ?>" class="box">
               <span>your email :</span>
               <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box">
               <span>update your pic :</span>
               <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
            </div>
            <div class="inputBox">
               <input type="hidden" name="old_pass" value="<?php echo $fetch['password']; ?>">
               <span>old password :</span>
               <input 
                  type="password" 
                  name="update_pass" 
                  placeholder="<?php echo (!empty($fetch['password']) && $fetch['password'] !== md5('')) ? 'enter previous password' : 'no password currently set'; ?>" 
                  class="box" 
                  <?php echo (empty($fetch['password']) || $fetch['password'] === md5('')) ? 'disabled' : ''; ?>
               >
               <span>new password :</span>
               <input type="password" name="new_pass" placeholder="enter new password" class="box">
               <span>confirm password :</span>
               <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
            </div>
         </div>
         <input type="submit" value="Update Profile" name="update_profile" class="btn btn-outline-primary w-100 mb-2">
         <a href="profile.php" class="btn btn-outline-danger w-100">Go Back</a>
      </form>
   </div>

   <?php if (!empty($alerts)): ?>
      <script>
         document.addEventListener('DOMContentLoaded', () => {
            <?php foreach ($alerts as [$type, $msg]): ?>
               Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: '<?php echo $type; ?>',
                  title: '<?php echo $msg; ?>',
                  showConfirmButton: false,
                  timer: 3000,
                  timerProgressBar: true
               });
            <?php endforeach; ?>
         });
      </script>
   <?php endif; ?>

</body>

</html>
