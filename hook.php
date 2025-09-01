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
        'openrouter_api_key'     => '',
        'openrouter_model_name'  => '',
        'openrouter_bot_user_id' => 2,
        'openrouter_system_prompt' => ''
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

function plugin_openrouter_save_disabled_state($ticket)
{
    global $DB;
    $ticket_id = (int) $ticket->getID();

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
