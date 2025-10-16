<?php

error_reporting(E_ERROR);
global $CFG_GLPI;

use GlpiPlugin\Openrouter\Config;
use GlpiPlugin\Openrouter\AIProvider;
use ITILFollowup;
use Ticket;

header("Content-Type: application/json");

if (!isset($_POST['ticket_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ticket_id']);
    exit;
}

$ticket_id = $_POST['ticket_id'];

$config = Config::getConfig();

// Check and update API usage limits (only for providers that have usage limits)
$provider = $config['provider'] ?? 'openrouter';

// Use provider-specific usage limits if they exist, otherwise use global limits
$usage_limit_config_prefix = '';
if (isset($config[$provider . '_max_api_usage_count'])) {
    $usage_limit_config_prefix = $provider;
} else {
    $usage_limit_config_prefix = 'global';
}

if(isset($config[$usage_limit_config_prefix . '_max_api_usage_count']) && isset($config[$usage_limit_config_prefix . '_api_usage_count']) && isset($config[$usage_limit_config_prefix . '_api_reset_day']))
{
    $now = new DateTime();
    $reset_day = new DateTime($config[$usage_limit_config_prefix . '_api_reset_day']);

    if($now >= $reset_day)
    {
        $config[$usage_limit_config_prefix . '_api_usage_count'] = 0;
        // Set next reset day to the same time tomorrow
        $new = (new DateTime('now'))->add(new DateInterval('P1D'));
        $old = new DateTime($config[$usage_limit_config_prefix . '_api_reset_day']); // reconvertir en DateTime

        $new->setTime(
            (int)$old->format('H'),
            (int)$old->format('i'),
            (int)$old->format('s')
        );
        $config[$usage_limit_config_prefix . '_api_reset_day'] = $new->format('Y-m-d H:i:s');
        Config::setConfig($config);
    }
    if($config[$usage_limit_config_prefix . '_api_usage_count'] >= $config[$usage_limit_config_prefix . '_max_api_usage_count'])
    {
        http_response_code(429);
        echo json_encode(['error' => 'API usage limit reached for today']);
        exit;
    }
    else
    {
        // Increment usage count
        $config[$usage_limit_config_prefix . '_api_usage_count']++;
        Config::setConfig($config);
    }
}
else
{
    // Initialize usage count and reset day if not set
    if(!isset($config[$usage_limit_config_prefix . '_api_usage_count']))
    {
        $config[$usage_limit_config_prefix . '_api_usage_count'] = 1;
    }
    if(!isset($config[$usage_limit_config_prefix . '_api_reset_day']))
    {
        $newval = (new DateTime('now'))->add(new DateInterval('P1D'));
        $config[$usage_limit_config_prefix . '_api_reset_day'] = $newval->format('Y-m-d H:i:s');
    }
    Config::setConfig($config);
}

// Use AIProvider to get the correct configuration values
$aiProvider = new AIProvider($config);
$api_key = $aiProvider->getApiKey();
$model_name = $aiProvider->getModelName();
$bot_user_id = $config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0;
$system_prompt_config = $config['global_system_prompt'] ?? $config['openrouter_system_prompt'] ?? '';

if (empty($api_key) && $provider !== 'ollama') {  // Ollama doesn't require an API key
    http_response_code(500);
    echo json_encode(['error' => 'Plugin not configured: API key is required']);
    exit;
}

if (empty($model_name)) {
    http_response_code(500);
    echo json_encode(['error' => 'Plugin not configured: Model name is required']);
    exit;
}

if (empty($bot_user_id)) {
    http_response_code(500);
    echo json_encode(['error' => 'Plugin not configured: Bot user ID is required']);
    exit;
}

$ticket = new Ticket();
if (!$ticket->getFromDB($ticket_id)) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket not found']);
    exit;
}

// Get the last user message as content
$content = $ticket->fields['content']; // Fallback to initial content
$timeline = $ticket->getTimelineItems();
foreach ($timeline as $item) {
    if ($item->fields['itemtype'] == 'TicketFollowup' && $item->fields['users_id'] != $bot_user_id) {
        $content = $item->fields['content']; // Get the latest user followup
    }
}


$system_prompt = "You are an AI assistant acting as a Level 1 IT support technician for the company. Your name is 'AI Assistant Bot'. You must be professional and courteous in all your responses. Your primary goal is to resolve common user issues based on the provided ticket information.\n\nWhen responding to a user, please follow these guidelines:\n1.  Analyze the user's request carefully.\n2.  If the request is clear and you can provide a solution, offer a step-by-step guide.\n3.  If the request is unclear, ask for more information. Be specific about what you need.\n4.  If the issue is complex or requires administrative privileges you don't have, you must escalate the ticket. To do so, respond with the following exact phrase and nothing else: 'I am unable to resolve this issue and have escalated it to a system administrator.'\n5.  Do not invent solutions or provide information you are not sure about.\n6.  Always sign your responses with your name, 'AI Assistant Bot'.";

if (!empty($system_prompt_config)) {
    $system_prompt = $system_prompt_config;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $aiProvider->getApiUrl());
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aiProvider->preparePayload($system_prompt, $content)));

$headers = $aiProvider->prepareHeaders();
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Set timeout for the request
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch) || ($http_code != 200 && $http_code != 201)) {
    $error_message = ucfirst($provider) . " API error: " . curl_error($ch) . " (HTTP code: " . $http_code . "). Response: " . $result;
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['error' => $error_message]);
    exit;
}
curl_close($ch);

$response_content = $aiProvider->processResponse($result);

if (!empty($response_content)) {
    $followUp = new ITILFollowup();
    $toAdd = [
        'type' => 'new',
        'items_id' => $ticket_id,
        'itemtype' => 'Ticket',
        'content' => $response_content . "\n\n<!-- openrouter_bot_response -->",
        'users_id' => $bot_user_id
    ];
    if ($followUp->add($toAdd)) {
        echo json_encode(['success' => true, 'message' => 'Followup added.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add followup.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No response from bot.']);
}
