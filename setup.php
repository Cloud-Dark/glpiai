<?php

use Glpi\Plugin\Hooks;

define('PLUGIN_OPENROUTER_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define("PLUGIN_OPENROUTER_MIN_GLPI_VERSION", "11.0.0");

// Maximum GLPI version, exclusive
define("PLUGIN_OPENROUTER_MAX_GLPI_VERSION", "11.1.0");

function plugin_init_openrouter() {
   global $PLUGIN_HOOKS;

    //add a tab to configuration
    Plugin::registerClass(\GlpiPlugin\Openrouter\Config::class, ['addtabon' => \Config::class]);

    $PLUGIN_HOOKS[Hooks::ITEM_ADD]['openrouter'] = [
        'Ticket'         => 'plugin_openrouter_item_add',
        'TicketFollowup' => 'plugin_openrouter_item_add',
    ];
}

function plugin_version_openrouter() {
    return [
        'name'           => 'OpenRouter',
        'version'        => PLUGIN_OPENROUTER_VERSION,
        'author'         => 'Brice FOURIe',
        'license'        => 'Apache 2.0',
        'homepage'       => 'https://github.com/bricefourie/glpiai-openrouter',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_OPENROUTER_MIN_GLPI_VERSION,
                'max' => PLUGIN_OPENROUTER_MAX_GLPI_VERSION,
            ]
    ]
   ];
}

function plugin_openrouter_check_config($verbose = false) {
    $config = \GlpiPlugin\Openrouter\Config::getConfig();
    $api_key = $config['openrouter_api_key'] ?? '';
    $model_name = $config['openrouter_model_name'] ?? '';
    $bot_user_id = $config['openrouter_bot_user_id'] ?? 0;

    if (!empty($api_key) && !empty($model_name) && !empty($bot_user_id)) {
        return true;
    }

    if ($verbose) {
        _e('Plugin not configured. Please provide API key, model name and bot user ID.', 'openrouter');
    }
    return false;
}
