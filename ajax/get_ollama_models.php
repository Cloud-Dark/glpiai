<?php
global $CFG_GLPI;

use GlpiPlugin\Openrouter\Config;

header("Content-Type: application/json");

$config = Config::getConfig();
$ollama_url = $config['ollama_api_url'] ?? 'http://localhost:11434';

// Fetch models from Ollama
$url = rtrim($ollama_url, '/') . '/api/tags';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code != 200) {
    http_response_code($http_code);
    echo json_encode(['error' => 'Failed to fetch models from Ollama API.']);
    exit;
}

$response = json_decode($result, true);
if (isset($response['models'])) {
    $formatted_models = [];
    foreach ($response['models'] as $model) {
        $formatted_models[] = [
            'id' => $model['name'],
            'name' => $model['name'] . ' (' . ($model['size'] ?? 'Unknown size') . ')'
        ];
    }
    echo json_encode($formatted_models);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from Ollama API.']);
}