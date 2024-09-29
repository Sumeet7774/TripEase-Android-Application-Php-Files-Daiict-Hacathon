<?php
require "connection2.php";
header('Content-Type: application/json');

// Get the incoming JSON request
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Extract user message and user ID from the request
$userId = $data['user_id'] ?? null;
$userMessage = $data['message'] ?? null;

if (empty($userId) || empty($userMessage)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid input"
    ]);
    exit();
}

// Insert the user's message into the chat_history table
$insertQuery = $conn->prepare("INSERT INTO chat_history (user_id, user_message) VALUES (?, ?)");
$insertQuery->bind_param("is", $userId, $userMessage);
$insertQuery->execute();
$chatId = $conn->insert_id;

// Call the external bot API (Groq) with the user's message
$groqRequest = [
    "messages" => [["role" => "user", "content" => $userMessage]],
    "model" => "llama3-70b-8192",
    "temperature" => 1,
    "max_tokens" => 1024,
    "top_p" => 1,
    "stream" => false,
    "stop" => null
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer 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' // Hardcoded API key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($groqRequest));

$groqResponse = curl_exec($ch);
curl_close($ch);

$groqData = json_decode($groqResponse, true);

// Get the bot's response from the Groq API
$botResponse = '';
if (isset($groqData['choices'][0]['message']['content'])) {
    $botResponse = $groqData['choices'][0]['message']['content'];
} else {
    $botResponse = "Sorry, I couldn't understand that.";
}

// Update the chat_history table with the bot's response
$updateQuery = $conn->prepare("UPDATE chat_history SET bot_response = ? WHERE chat_id = ?");
$updateQuery->bind_param("si", $botResponse, $chatId);
$updateQuery->execute();

// Return the bot's response in the JSON response
echo json_encode([
    "status" => "success",
    "user_message" => $userMessage,
    "bot_response" => $botResponse
]);