<?php

use Plugin;
use Glpi\Plugin\Hooks;

define('PLUGIN_OPENROUTER_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define("PLUGIN_OPENROUTER_MIN_GLPI_VERSION", "10.0.0");

// Maximum GLPI version, exclusive
define("PLUGIN_OPENROUTER_MAX_GLPI_VERSION", "10.0.99");

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
        'author'         => 'Jules',
        'license'        => 'MIT',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_OPENROUTER_MIN_GLPI_VERSION,
                'max' => PLUGIN_OPENROUTER_MAX_GLPI_VERSION,
            ]
    ];
}

function plugin_openrouter_check_config($verbose = false) {
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo "Installed / not configured";
    }
    return false;
}
