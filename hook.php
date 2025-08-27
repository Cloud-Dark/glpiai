<?php

function plugin_openrouter_install() {
    \Config::setConfigurationValues('plugin:openrouter', [
        'openrouter_api_key' => '',
        'openrouter_model_name' => '',
        'openrouter_bot_user_id' => 2
    ]);
    return true;
}

function plugin_openrouter_uninstall() {
    $config = new \Config();
    $config->deleteByCriteria(['context' => 'plugin:openrouter']);
    return true;
}

function plugin_openrouter_item_add($item) {
    if (!in_array($item->getType(), ['Ticket', 'TicketFollowup'])) {
        return;
    }

    $config = \GlpiPlugin\Openrouter\Config::getConfig();
    $api_key = $config['openrouter_api_key'] ?? '';
    $model_name = $config['openrouter_model_name'] ?? '';
    $bot_user_id = $config['openrouter_bot_user_id'] ?? 0;

    if (empty($api_key) || empty($model_name) || empty($bot_user_id)) {
        return;
    }

    // Do not process private items
    if (isset($item->fields['is_private']) && $item->fields['is_private']) {
        return;
    }

    // Do not process items created by the bot
    if ($item->getType() === 'Ticket' && $item->fields['users_id_recipient'] == $bot_user_id) {
        return;
    }
    if ($item->getType() === 'TicketFollowup' && $item->fields['users_id'] == $bot_user_id) {
        return;
    }

    $content = $item->fields['content'] ?? '';
    if (empty($content)) {
        return;
    }

    $system_prompt = "You are a basic system and network technician. Your role is to provide initial support for user requests. If you can solve the problem, provide a clear and concise solution. If the problem is outside your scope of knowledge or requires manual intervention, you must escalate the ticket to a human system administrator by responding with the following exact phrase and nothing else: 'I am unable to resolve this issue and have escalated it to a system administrator.'";

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
    if (curl_errno($ch)) {
        // handle curl error
        return;
    }
    curl_close($ch);

    $response = json_decode($result, true);
    $response_content = $response['choices'][0]['message']['content'] ?? '';

    if (!empty($response_content)) {
        $followup = new \TicketFollowup();
        $followup_data = [
            'tickets_id' => ($item->getType() === 'Ticket') ? $item->getID() : $item->fields['tickets_id'],
            'content' => $response_content,
            'is_private' => 0,
            'users_id' => $bot_user_id,
        ];
        $followup->add($followup_data);
    }
}
