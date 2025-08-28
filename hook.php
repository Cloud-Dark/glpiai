<?php

require_once __DIR__ . '/src/Config.php';
use Config;
use GlpiPlugin\Openrouter\Config as OpenrouterConfig;
use Toolbox;
use ITILFollowup;

function plugin_openrouter_install()
{
    Config::setConfigurationValues('plugin:openrouter', [
        'openrouter_api_key'     => '',
        'openrouter_model_name'  => '',
        'openrouter_bot_user_id' => 2,
        'openrouter_system_prompt' => ''
    ]);
    return true;
}

function plugin_openrouter_uninstall()
{
    $config = new Config();
    $config->deleteByCriteria(['context' => 'plugin:openrouter']);
    return true;
}

function plugin_openrouter_item_add($item)
{
    if (!in_array($item->getType(), ['Ticket', 'TicketFollowup'])) {
        return;
    }

    $config = OpenrouterConfig::getConfig();
    $api_key = $config['openrouter_api_key'] ?? '';
    $model_name = $config['openrouter_model_name'] ?? '';
    $bot_user_id = $config['openrouter_bot_user_id'] ?? 0;
    $system_prompt_config = $config['openrouter_system_prompt'] ?? '';

    if (empty($api_key) || empty($model_name) || empty($bot_user_id)) {
        return;
    }

    // Do not process private items
    if (isset($item->fields['is_private']) && $item->fields['is_private']) {
        return;
    }

    $content = $item->fields['content'] ?? '';
    if (empty($content) || strpos($content, '<!-- openrouter_bot_response -->') !== false) {
        return;
    }

    $system_prompt = "You are an AI assistant acting as a Level 1 IT support technician for the company. Your name is 'OpenRouter Bot'. You must be professional and courteous in all your responses. Your primary goal is to resolve common user issues based on the provided ticket information.\n\nWhen responding to a user, please follow these guidelines:\n1.  Analyze the user's request carefully.\n2.  If the request is clear and you can provide a solution, offer a step-by-step guide.\n3.  If the request is unclear, ask for more information. Be specific about what you need.\n4.  If the issue is complex or requires administrative privileges you don't have, you must escalate the ticket. To do so, respond with the following exact phrase and nothing else: 'I am unable to resolve this issue and have escalated it to a system administrator.'\n5.  Do not invent solutions or provide information you are not sure about.\n6.  Always sign your responses with your name, 'OpenRouter Bot'.";

    if (!empty($system_prompt_config)) {
        $system_prompt = $system_prompt_config;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $postdata = [
        'model' => $model_name,
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $content]
        ]
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));

    $headers = [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch) || $http_code != 200) {
        $error_message = "OpenRouter API error: " . curl_error($ch) . " (HTTP code: " . $http_code . "). Response: " . $result;
        curl_close($ch);
        return;
    }
    curl_close($ch);

    $response = json_decode($result, true);
    $response_content = $response['choices'][0]['message']['content'] ?? '';

    if (!empty($response_content)) {
        $ticketId = ($item->getType() === 'Ticket') ? $item->getID() : $item->fields['tickets_id'];
        $followUp = new ITILFollowup();
        $toAdd = ['type' => "new",
                'items_id' => $ticketId,
                'itemstype' => 'Ticket',
                'content' => $reponse_content . "\n\n<!-- openrouter_bot_response -->"
        ];
        $followUp->add($toAdd);
    }
}
