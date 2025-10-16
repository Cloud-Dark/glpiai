<?php
global $CFG_GLPI;

use GlpiPlugin\Openrouter\Config;

header("Content-Type: application/json");

if (!isset($_GET['provider'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Provider parameter is required.']);
    exit;
}

$provider = $_GET['provider'];
$config = Config::getConfig();

switch ($provider) {
    case 'openrouter':
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
        break;
        
    case 'ollama':
        $ollama_url = $config['ollama_api_url'] ?? 'http://localhost:11434';
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
        break;
        
    case 'gemini':
        $api_key = $config['gemini_api_key'] ?? '';

        if (empty($api_key)) {
            http_response_code(400);
            echo json_encode(['error' => 'Gemini API key not configured.']);
            exit;
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $api_key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            http_response_code($http_code);
            echo json_encode(['error' => 'Failed to fetch models from Gemini API.']);
            exit;
        }

        $response = json_decode($result, true);
        if (isset($response['models'])) {
            $formatted_models = [];
            foreach ($response['models'] as $model) {
                // Only include models that support the generateContent API
                if (strpos($model['name'], 'gemini') !== false) {
                    $formatted_models[] = [
                        'id' => str_replace('models/', '', $model['name']),
                        'name' => $model['displayName'] ?? $model['name']
                    ];
                }
            }
            echo json_encode($formatted_models);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Invalid response from Gemini API.']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid provider.']);
        exit;
}
