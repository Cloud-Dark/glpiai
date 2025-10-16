<?php

namespace GlpiPlugin\Openrouter;

class AIProvider
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get the currently selected provider
     */
    public function getCurrentProvider()
    {
        return $this->config['provider'] ?? 'openrouter';
    }

    /**
     * Get the API key for the current provider
     */
    public function getApiKey()
    {
        $provider = $this->getCurrentProvider();
        $key_map = [
            'openrouter' => 'openrouter_api_key',
            'ollama' => 'ollama_api_key',
            'gemini' => 'gemini_api_key'
        ];
        
        return $this->config[$key_map[$provider]] ?? '';
    }

    /**
     * Get the model name for the current provider
     */
    public function getModelName()
    {
        $provider = $this->getCurrentProvider();
        $model_map = [
            'openrouter' => 'openrouter_model_name',
            'ollama' => 'ollama_model_name',
            'gemini' => 'gemini_model_name'
        ];
        
        return $this->config[$model_map[$provider]] ?? '';
    }

    /**
     * Get the API URL for the current provider
     */
    public function getApiUrl()
    {
        $provider = $this->getCurrentProvider();
        
        switch ($provider) {
            case 'openrouter':
                return 'https://openrouter.ai/api/v1/chat/completions';
            case 'ollama':
                return $this->config['ollama_api_url'] ?? 'http://localhost:11434/api/chat';
            case 'gemini':
                $model = $this->getModelName();
                return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $this->getApiKey();
            default:
                return 'https://openrouter.ai/api/v1/chat/completions'; // Default to OpenRouter
        }
    }

    /**
     * Prepare the request payload for the current provider
     */
    public function preparePayload($system_prompt, $content)
    {
        $provider = $this->getCurrentProvider();
        
        switch ($provider) {
            case 'openrouter':
                return [
                    'model' => $this->getModelName(),
                    'messages' => [
                        ['role' => 'system', 'content' => $system_prompt],
                        ['role' => 'user', 'content' => $content]
                    ]
                ];
            case 'ollama':
                return [
                    'model' => $this->getModelName(),
                    'messages' => [
                        ['role' => 'system', 'content' => $system_prompt],
                        ['role' => 'user', 'content' => $content]
                    ],
                    'stream' => false
                ];
            case 'gemini':
                return [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $content
                                ]
                            ]
                        ]
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            'text' => $system_prompt
                        ]
                    ]
                ];
            default:
                return [
                    'model' => $this->getModelName(),
                    'messages' => [
                        ['role' => 'system', 'content' => $system_prompt],
                        ['role' => 'user', 'content' => $content]
                    ]
                ];
        }
    }

    /**
     * Prepare the headers for the API request
     */
    public function prepareHeaders()
    {
        $provider = $this->getCurrentProvider();
        $api_key = $this->getApiKey();
        
        switch ($provider) {
            case 'openrouter':
                return [
                    'Authorization: Bearer ' . $api_key,
                    'Content-Type: application/json'
                ];
            case 'ollama':
                return [
                    'Content-Type: application/json'
                ];
            case 'gemini':
                // For Gemini, the API key is part of the URL, so no need for Authorization header
                return [
                    'Content-Type: application/json'
                ];
            default:
                return [
                    'Authorization: Bearer ' . $api_key,
                    'Content-Type: application/json'
                ];
        }
    }

    /**
     * Process the API response based on the provider
     */
    public function processResponse($response)
    {
        $provider = $this->getCurrentProvider();
        
        switch ($provider) {
            case 'openrouter':
            case 'ollama':
                $data = json_decode($response, true);
                return $data['choices'][0]['message']['content'] ?? '';
            case 'gemini':
                $data = json_decode($response, true);
                if (isset($data['candidates']) && is_array($data['candidates'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                }
                return '';
            default:
                $data = json_decode($response, true);
                return $data['choices'][0]['message']['content'] ?? '';
        }
    }

    /**
     * Get the HTTP method for the API request
     */
    public function getHttpMethod()
    {
        $provider = $this->getCurrentProvider();
        
        if ($provider === 'gemini') {
            return 'POST';
        }
        
        return 'POST';
    }
}