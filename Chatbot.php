<?php
$prompt = $_POST['prompt'];

$data = json_encode([
  "model" => "llama3", // Use the exact name from your list
    "prompt" => $prompt,
    "stream" => false
]);

$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
echo json_encode(['response' => $result['response']]);
?>