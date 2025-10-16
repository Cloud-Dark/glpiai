<?php

require_once __DIR__ . '/src/Config.php';
use Config;
use GlpiPlugin\Openrouter\Config as OpenrouterConfig;
use Toolbox;
use ITILFollowup;

function plugin_openrouter_install()
{
    global $DB;

    $table_name = 'glpi_plugin_openrouter_disabled_tickets';
    if (!$DB->tableExists($table_name)) {
        $query = "CREATE TABLE `$table_name` (
                      `tickets_id` INT(11) NOT NULL,
                      PRIMARY KEY  (`tickets_id`)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->doQuery($query);
    }

    Config::setConfigurationValues('plugin:openrouter', [
        'provider'               => 'openrouter',  // Default provider
        'openrouter_api_key'     => '',
        'openrouter_model_name'  => '',
        'ollama_api_key'         => '',            // Ollama API key (optional)
        'ollama_model_name'      => 'llama2',      // Default Ollama model
        'ollama_api_url'         => 'http://localhost:11434', // Default Ollama URL
        'gemini_api_key'         => '',            // Gemini API key
        'gemini_model_name'      => 'gemini-pro',  // Default Gemini model
        'global_bot_user_id'     => 2,             // Global bot user ID
        'global_system_prompt'   => '',            // Global system prompt
        'global_max_api_usage_count' => 50,        // Global usage limit
        'global_api_usage_count' => 0,             // Current usage count
        'global_api_reset_day'   => (new DateTime('now'))->add(new DateInterval('P1D'))->format('Y-m-d H:i:s') // Reset time
    ]);
    return true;
}

function plugin_openrouter_uninstall()
{
    global $DB;

    $table_name = 'glpi_plugin_openrouter_disabled_tickets';

    $migration = new Migration(PLUGIN_OPENROUTER_VERSION);
    if ($DB->tableExists($table_name)) {
        $migration->dropTable($table_name);
    }
    $migration->executeMigration();

    $config = new Config();
    $config->deleteByCriteria(['context' => 'plugin:openrouter']);
    return true;
}

function plugin_openrouter_pre_item_update($item) {
    global $DB;
    $ticket_id = (int) $item->getID();

    if ($ticket_id > 0) {
        $table_name = 'glpi_plugin_openrouter_disabled_tickets';

        if (isset($_POST['openrouter_bot_disabled']) && $_POST['openrouter_bot_disabled'] == '1') {
            // Checkbox is checked, so add to disabled table
            if (countElementsInTable($table_name, ['tickets_id' => $ticket_id]) == 0) {
                $DB->insert($table_name, ['tickets_id' => $ticket_id]);
            }
        } else {
            // Checkbox is not checked, so remove from disabled table
            $DB->delete($table_name, ['tickets_id' => $ticket_id]);
        }
    }
}



function plugin_openrouter_item_add($item)
{
    // This function is disabled as the logic has been moved to an asynchronous AJAX call.
    // See js/ticket.js and ajax/create_followup.php for the new implementation.
    return;
}

function plugin_openrouter_check_config($verbose = false) {
    $config = \GlpiPlugin\Openrouter\Config::getConfig();
    $provider = $config['provider'] ?? 'openrouter';
    
    // Check configuration based on the selected provider
    switch ($provider) {
        case 'openrouter':
            $api_key = $config['openrouter_api_key'] ?? '';
            $model_name = $config['openrouter_model_name'] ?? '';
            $bot_user_id = $config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0;
            break;
            
        case 'ollama':
            $api_key = null; // Ollama doesn't require an API key
            $model_name = $config['ollama_model_name'] ?? '';
            $bot_user_id = $config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0;
            break;
            
        case 'gemini':
            $api_key = $config['gemini_api_key'] ?? '';
            $model_name = $config['gemini_model_name'] ?? '';
            $bot_user_id = $config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0;
            break;
            
        default:
            $api_key = $config['openrouter_api_key'] ?? '';
            $model_name = $config['openrouter_model_name'] ?? '';
            $bot_user_id = $config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0;
            break;
    }

    // Check if required fields are filled
    $has_required_config = !empty($model_name) && !empty($bot_user_id);
    
    // API key is required for OpenRouter and Gemini, but not for Ollama
    if ($provider !== 'ollama') {
        $has_required_config = $has_required_config && !empty($api_key);
    }

    if ($has_required_config) {
        return true;
    }

    if ($verbose) {
        $provider_name = ucfirst($provider);
        if ($provider === 'ollama') {
            _e("Plugin not configured. Please provide model name and bot user ID for $provider_name.", 'openrouter');
        } else {
            _e("Plugin not configured. Please provide API key, model name and bot user ID for $provider_name.", 'openrouter');
        }
    }
    return false;
}
