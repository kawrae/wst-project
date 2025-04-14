<?php

$conn = mysqli_connect('localhost','root','','user_db') or die('connection failed');

//Include Google Client Library for PHP autoload file
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Make object of Google API Client for call Google API
$google_client = new Google_Client();

//Set the OAuth 2.0 Client ID
$google_client->setClientId($_ENV['GOOGLE_CLIENT_ID']);

//Set the OAuth 2.0 Client Secret key
$google_client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);

//Set the OAuth 2.0 Redirect URI
$google_client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);

// to get the email and profile
$google_client->addScope('email');
$google_client->addScope('profile');

$login_button = $google_client->createAuthUrl();

?>