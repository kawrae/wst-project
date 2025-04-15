<?php
include 'config.php';
session_start();

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = mysqli_real_escape_string($conn, md5($_POST['password']));

    $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email' AND password = '$pass'") or die('query failed');

    if (mysqli_num_rows($select) > 0) {
        $row = mysqli_fetch_assoc($select);
        $_SESSION['user_id'] = $row['id'];
        header('location:profile.php');
    } else {
        $message[] = 'Incorrect email or password!';
    }
}

$github_client_id = 'Ov23liiFTsdHU5kVjsiW';
$github_login_url = "https://github.com/login/oauth/authorize?client_id={$github_client_id}&scope=user:email";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        .login-wrapper {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .form-container form {
            max-width: 400px;
        }

        .oauth-btn {
            width: 100%;
            margin-top: 10px;
            padding: 10px 0;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .github-btn {
            background-color: #333;
            color: white;
        }

        .github-btn:hover {
            background-color: #555;
        }

        .google-btn {
            background-color: #4285F4;
            color: white;
        }

        .google-btn:hover {
            background-color: #357ae8;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <div class="login-wrapper">

            <form action="" method="post">
                <h3>Login with Email</h3>
                <?php if (isset($message)) {
                    foreach ($message as $msg) {
                        echo '<div class="message error">' . $msg . '</div>';
                    }
                } ?>
                <input type="email" name="email" placeholder="Enter email" class="box" required autocomplete="off">
                <input type="password" name="password" placeholder="Enter password" class="box" required
                    autocomplete="off">
                <input type="submit" name="submit" value="Login Now" class="btn btn-outline-primary">
                <p>Don't have an account? <a href="register.php">Register now</a></p>
            </form>

            <form>
                <h3>Or Login with</h3>

                <!-- google login button -->
                <button type="button" class="btn btn-google w-100 mb-2"
                    onclick="window.location='<?php echo $login_button; ?>'">
                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg"
                        alt="Google" width="20" class="me-2">
                     Login with Google
                </button>

                <!-- gitHub login button -->
                <button type="button" class="btn btn-github w-100"
                    onclick="window.location='<?php echo $github_login_url; ?>'">
                    <i class="fab fa-github me-2"> </i>  Login with GitHub
                </button>

            </form>

        </div>
    </div>

</body>

</html>