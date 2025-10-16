<?php
// Basic test to just check if the classes can be loaded
echo "Testing class loading...\n";

// Test if the AI provider class exists
if (file_exists('src/AIProvider.php')) {
    include_once 'src/AIProvider.php';
    echo "AIProvider class loaded successfully\n";
} else {
    echo "AIProvider.php not found\n";
}

// Test if the Config class exists
if (file_exists('src/Config.php')) {
    include_once 'src/Config.php';
    echo "Config class loaded successfully\n";
} else {
    echo "Config.php not found\n";
}

echo "Test completed.\n";