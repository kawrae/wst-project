<?php
session_start();
include 'config.php';

$client_id = 'Ov23liiFTsdHU5kVjsiW';
$client_secret = '088d518cd4ff51e78262cc4df7fbf49f9ec820a0';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $token_url = 'https://github.com/login/oauth/access_token';
    $data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($token_url, false, $context);
    $token_data = json_decode($result, true);
    $access_token = $token_data['access_token'];

    $user_info = file_get_contents("https://api.github.com/user", false, stream_context_create([
        'http' => [
            'header' => "User-Agent: YourAppName\r\nAuthorization: token $access_token\r\n"
        ]
    ]));
    $user = json_decode($user_info, true);

    $email_info = file_get_contents("https://api.github.com/user/emails", false, stream_context_create([
        'http' => [
            'header' => "User-Agent: YourAppName\r\nAuthorization: token $access_token\r\n"
        ]
    ]));
    $emails = json_decode($email_info, true);
    $primary_email = $emails[0]['email'] ?? '';

    $github_id = $user['id'];
    $name = $user['name'] ?? $user['login'];

    $check_user = mysqli_query($conn, "SELECT * FROM user_form WHERE email = '$primary_email' LIMIT 1");
    if (mysqli_num_rows($check_user) > 0) {
        $row = mysqli_fetch_assoc($check_user);
        $_SESSION['user_id'] = $row['id'];
    } else {
        mysqli_query($conn, "INSERT INTO user_form (name, email, password) VALUES ('$name', '$primary_email', '')");
        $_SESSION['user_id'] = mysqli_insert_id($conn);
    }

    header("Location: profile.php");
    exit();
} else {
    echo "GitHub login failed!";
}
