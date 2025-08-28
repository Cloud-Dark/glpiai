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
    // This function is disabled as the logic has been moved to an asynchronous AJAX call.
    // See js/ticket.js and ajax/create_followup.php for the new implementation.
    return;
}
