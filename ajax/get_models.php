<?php
global $CFG_GLPI;

header("Content-Type: application/json");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/models");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'Failed to fetch models from OpenRouter API.']);
    exit;
}

$response = json_decode($result, true);
if (isset($response['data'])) {
    echo json_encode($response['data']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from OpenRouter API.']);
}
