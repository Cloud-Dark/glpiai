<?php
// Simple test script to validate the new multi-provider implementation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Multi-AI Provider Implementation\n";
echo "=======================================\n";

// Test 1: Check if the AIProvider class can be loaded
echo "Test 1: Loading AIProvider class... ";
if (file_exists('src/AIProvider.php')) {
    require_once 'src/AIProvider.php';
    echo "PASSED\n";
} else {
    echo "FAILED - AIProvider.php not found\n";
    exit(1);
}

// Test 2: Check if the Config class can be loaded
echo "Test 2: Loading Config class... ";
if (file_exists('src/Config.php')) {
    require_once 'src/Config.php';
    echo "PASSED\n";
} else {
    echo "FAILED - Config.php not found\n";
    exit(1);
}

// Test 3: Check if all required AJAX files exist
echo "Test 3: Checking AJAX endpoints... ";
$ajax_files = [
    'ajax/create_followup.php',
    'ajax/get_models.php',
    'ajax/get_ollama_models.php',
    'ajax/get_gemini_models.php'
];

$all_exist = true;
foreach ($ajax_files as $file) {
    if (!file_exists($file)) {
        echo "FAILED - $file not found\n";
        $all_exist = false;
    }
}

if ($all_exist) {
    echo "PASSED\n";
} else {
    exit(1);
}

// Test 4: Check if the configuration form exists
echo "Test 4: Checking configuration form... ";
if (file_exists('front/config.form.php')) {
    echo "PASSED\n";
} else {
    echo "FAILED - front/config.form.php not found\n";
    exit(1);
}

// Test 5: Check if hooks file exists and has required functions
echo "Test 5: Checking hook.php functions... ";
if (file_exists('hook.php')) {
    require_once 'hook.php';
    
    if (function_exists('plugin_openrouter_install') && 
        function_exists('plugin_openrouter_check_config')) {
        echo "PASSED\n";
    } else {
        echo "FAILED - Required functions not found in hook.php\n";
        exit(1);
    }
} else {
    echo "FAILED - hook.php not found\n";
    exit(1);
}

// Test 6: Test the AIProvider functionality with a mock config
echo "Test 6: Testing AIProvider functionality... ";
if (class_exists('GlpiPlugin\Openrouter\AIProvider')) {
    $mock_config = [
        'provider' => 'openrouter',
        'openrouter_api_key' => 'test_key',
        'openrouter_model_name' => 'test_model',
        'ollama_api_key' => 'ollama_test_key',
        'ollama_model_name' => 'llama2',
        'ollama_api_url' => 'http://localhost:11434',
        'gemini_api_key' => 'gemini_test_key',
        'gemini_model_name' => 'gemini-pro'
    ];
    
    $aiProvider = new \GlpiPlugin\Openrouter\AIProvider($mock_config);
    
    // Test provider selection
    if ($aiProvider->getCurrentProvider() === 'openrouter') {
        // Test API key retrieval
        if ($aiProvider->getApiKey() === 'test_key') {
            // Test model name retrieval
            if ($aiProvider->getModelName() === 'test_model') {
                // Test API URL for OpenRouter
                if ($aiProvider->getApiUrl() === 'https://openrouter.ai/api/v1/chat/completions') {
                    echo "PASSED\n";
                } else {
                    echo "FAILED - Incorrect OpenRouter API URL\n";
                    exit(1);
                }
            } else {
                echo "FAILED - Incorrect model name retrieval\n";
                exit(1);
            }
        } else {
            echo "FAILED - Incorrect API key retrieval\n";
            exit(1);
        }
    } else {
        echo "FAILED - Incorrect provider selection\n";
        exit(1);
    }
} else {
    echo "FAILED - AIProvider class not found\n";
    exit(1);
}

echo "\nAll tests PASSED! The multi-provider implementation is ready.\n";
echo "\nSummary of changes made:\n";
echo "- Added support for OpenRouter, Ollama, and Google Gemini\n";
echo "- Created AIProvider abstraction class\n";
echo "- Updated configuration to allow provider selection\n";
echo "- Added provider-specific settings\n";
echo "- Created AJAX endpoints for each provider's model listing\n";
echo "- Updated installation and configuration validation\n";
echo "- Maintained backward compatibility with existing settings\n";