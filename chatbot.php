<?php

// === Load Environment Variables ===
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// === Start Session & Set Headers ===
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// === Input Validation ===
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['message'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user_message = strtolower(trim($input['message']));

// === Get API Key from .env ===
$api_key = $_ENV['GOOGLE_API_KEY'] ?? null;
if (!$api_key) {
    echo json_encode(['error' => 'API key not loaded from .env (via $_ENV)']);
    exit;
}

// === Connect to Database ===
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// === Fetch Product Data ===
$product_data = [];
$result = $conn->query("SELECT description, price FROM product");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $product_data[] = "Product: " . $row['description'] . " | Price: £" . $row['price'];
    }
}
$conn->close();

$database_info = "Here is the product list from the shop:\n" . implode("\n", $product_data);

// === Construct Gemini Prompt ===
$gemini_prompt = "Welcome to Corey's Shop! 😊 You are an AI assistant for an online shop. You have access to the shop's product database. Answer all the user's questions, even unrelated ones. But when users ask about products, provide information based on the following list: 

$database_info 

User question: $user_message";

// === Send Request to Google Gemini API ===

if (!$api_key) {
    echo json_encode(['error' => 'GOOGLE_API_KEY not loaded from .env']);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api_key";


$data = [
    "contents" => [
        [
            "parts" => [
                ['text' => $gemini_prompt]
            ]
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_msg = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode([
        'error' => 'Google Gemini API error',
        'http_code' => $http_code,
        'curl_error' => $error_msg,
        'raw_response' => $response
    ]);
    exit;
}

$response_data = json_decode($response, true);

if (!isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['error' => 'Unexpected API response format']);
    exit;
}

$ai_response = trim($response_data['candidates'][0]['content']['parts'][0]['text']);
echo json_encode(['response' => $ai_response]);
