<?php
include('config.php');

$token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

if (!isset($token['error'])) {

    $google_client->setAccessToken($token['access_token']);
    $google_service = new Google_Service_Oauth2($google_client);
    $data = $google_service->userinfo->get();

    $name = $email = $image = '';
    $image_url = $data['picture'] ?? '';

    if (!empty($data['given_name'])) {
        $name = $data['given_name'];
    }

    if (!empty($data['email'])) {
        $email = $data['email'];
    }

    $check = $conn->prepare("SELECT * FROM user_form WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
    } else {
        if (!empty($image_url)) {
            $image_name = uniqid() . '.jpg';
            file_put_contents('uploaded_img/' . $image_name, file_get_contents($image_url));
            $image = $image_name;
        }

        $stmt = $conn->prepare("INSERT INTO user_form (name, email, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $image);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();
    }

    $check->close();

    session_start();
    $_SESSION['user_id'] = $user_id;

    header('location:profile.php');
    exit;

} else {
    echo "Error: " . $token['error'];
}
