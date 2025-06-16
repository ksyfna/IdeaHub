<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["message"]) || trim($data["message"]) === "") {
    echo json_encode(["response" => "Message is empty"]);
    exit;
}

$apiKey = 'sk-cbf0755260fa4e4381d8fafc42839fad';

$payload = [
    "model" => "deepseek-chat",
    "messages" => [
        ["role" => "user", "content" => $data["message"]]
    ]
];

$ch = curl_init("https://api.deepseek.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);

if (!$response) {
    echo json_encode(["response" => "cURL error: " . curl_error($ch)]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if (!isset($result["choices"][0]["message"]["content"])) {
    echo json_encode(["response" => "Invalid API response", "raw" => $response]);
    exit;
}

$aiMessage = $result["choices"][0]["message"]["content"];
echo json_encode(["response" => $aiMessage]);
