<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();
include 'config.php';

$show_reg_form = true;
$show_otp_form = false;
$message = [];

function sendOTP($email, $code)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'yergransphatrhymes@gmail.com';
        $mail->Password = 'dzpn wdbi gzru jjuy';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('youremail@gmail.com', 'QuickHand');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "Your new verification code is <b>$code</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// pegistration
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
    $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
    $image = $_FILES['image']['name'] ?? '';
    $image_size = $_FILES['image']['size'] ?? 0;
    $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
    $image_folder = 'uploaded_img/' . $image;
    $code = rand(111111, 999999);

    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['pass'] = $pass;
    $_SESSION['image'] = $image;
    $_SESSION['image_size'] = $image_size;
    $_SESSION['image_tmp_name'] = $image_tmp_name;
    $_SESSION['image_folder'] = $image_folder;
    $_SESSION['code'] = $code;

    $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email'") or die('query failed');

    if (mysqli_num_rows($select) > 0) {
        $message[] = 'User already exists!';
    } elseif ($pass != $cpass) {
        $message[] = 'Confirm password not matched!';
    } elseif ($image_size > 2000000) {
        $message[] = 'Image size is too large!';
    } else {
        if (sendOTP($email, $code)) {
            $message[] = 'Check your email and enter the OTP to verify.';
            $show_reg_form = false;
            $show_otp_form = true;
        } else {
            $message[] = 'Failed to send OTP!';
        }
    }
}

// OTP
if (isset($_POST['check'])) {
    $OTP = mysqli_real_escape_string($conn, $_POST['OTP']);
    if ($_SESSION['code'] == $OTP) {

        // Double check email doesn't already exist
        $email = mysqli_real_escape_string($conn, $_SESSION['email']);
        $check_duplicate = mysqli_query($conn, "SELECT id FROM user_form WHERE email = '$email'");
        if (mysqli_num_rows($check_duplicate) > 0) {
            $message[] = 'User already exists. Please login instead.';
            $show_reg_form = true;
            $show_otp_form = false;
        } else {
            $insert = mysqli_query($conn, "INSERT INTO `user_form`(name, email, password, image, code) 
                VALUES('{$_SESSION['name']}', '$email', '{$_SESSION['pass']}', '{$_SESSION['image']}', '{$_SESSION['code']}')")
                or die('Query failed: ' . mysqli_error($conn));
            if ($insert) {
                move_uploaded_file($_SESSION['image_tmp_name'], $_SESSION['image_folder']);
                echo "<script>alert('Registration successful! Redirecting to login...'); window.location.href='login.php';</script>";
                exit();
            } else {
                $message[] = 'Registration failed!';
            }
        }

    } else {
        $message[] = "Wrong OTP entered!";
        $show_reg_form = false;
        $show_otp_form = true;
    }
}

// resend OTP
if (isset($_POST['resend'])) {
    $new_code = rand(111111, 999999);
    $_SESSION['code'] = $new_code;
    if (sendOTP($_SESSION['email'], $new_code)) {
        $message[] = 'A new OTP has been sent to your email!';
    } else {
        $message[] = 'Failed to resend OTP!';
    }
    $show_reg_form = false;
    $show_otp_form = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function () {
            const resendBtn = document.getElementById("resendBtn");
            if (resendBtn) {
                resendBtn.disabled = true;
                let timeLeft = 60;
                const timer = setInterval(() => {
                    resendBtn.innerText = "Resend OTP (" + timeLeft + "s)";
                    timeLeft--;
                    if (timeLeft < 0) {
                        clearInterval(timer);
                        resendBtn.innerText = "Resend OTP";
                        resendBtn.disabled = false;
                    }
                }, 1000);
            }
        }
    </script>
</head>

<body>
    <div class="form-container">

        <form action="" method="post" enctype="multipart/form-data"
            style="display:<?= $show_reg_form ? 'block' : 'none'; ?>">
            <h3>Step 1: Register Now</h3>
            <?php
            if (!empty($message)) {
                foreach ($message as $msg) {
                    $type = (stripos($msg, 'success') !== false || stripos($msg, 'sent') !== false || stripos($msg, 'check your email') !== false) ? 'success' : 'error';
                    echo "<div class='message $type'>$msg</div>";
                }
            }
            ?>

            <input type="text" name="name" placeholder="Username" class="box" required>
            <input type="email" name="email" placeholder="Email" class="box" required>
            <input type="password" name="password" placeholder="Password" class="box" required>
            <input type="password" name="cpassword" placeholder="Confirm Password" class="box" required>
            <input type="file" name="image" accept="image/*">
            <input type="submit" name="submit" value="Submit to Receive OTP" class="btn btn-outline-primary">
            <p>Already have an account? <a href="login.php">Login Now</a></p>
        </form>

        <form action="" method="post" style="display:<?= $show_otp_form ? 'block' : 'none'; ?>">
            <h3>Step 2: Verify with OTP</h3>
            <?php
            if (!empty($message)) {
                foreach ($message as $msg) {
                    $type = (stripos($msg, 'success') !== false || stripos($msg, 'sent') !== false || stripos($msg, 'check your email') !== false) ? 'success' : 'error';
                    echo "<div class='message $type'>$msg</div>";
                }
            }
            ?>
            <input type="text" name="OTP" placeholder="Enter OTP" class="box" required>
            <input type="submit" name="check" value="Verify & Register" class="btn btn-outline-primary">
            <button type="submit" id="resendBtn" name="resend" class="btn btn-outline-primary">Resend OTP</button>
            <p>Already have an account? <a href="login.php">Login Now</a></p>
        </form>

    </div>
</body>

</html>
