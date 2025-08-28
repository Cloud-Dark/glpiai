<?php

include ("../../../inc/includes.php");

use Glpi\Plugin\Hooks;
use GlpiPlugin\Openrouter\Config;


$plugin_config = new Config();
$config = Config::getConfig();

if (isset($_POST['update'])) {
   if (isset($_POST['config_context'])) {
      $plugin_config->update(
         [
            'config_context' => $_POST['config_context'],
            'openrouter_api_key' => $_POST['openrouter_api_key'],
            'openrouter_model_name' => $_POST['openrouter_model_name'],
            'openrouter_system_prompt' => $_POST['openrouter_system_prompt'],
            'openrouter_bot_user_id' => $_POST['openrouter_bot_user_id'],
         ]
      );
   }

   // Redirect to the same page to avoid form resubmission
   Html::redirect($plugin_config->getFormURL());
}

Html::header(
   __('OpenRouter', 'openrouter'),
   $_SERVER['PHP_SELF'],
   'config',
   'plugins',
   'openrouter'
);

$canedit = Session::haveRight('config', UPDATE);
$models = Config::getModels();

if ($canedit) {
   echo "<form name='form' action='" . $plugin_config->getFormURL() . "' method='post'>";
   echo "<input type='hidden' name='config_context' value='plugin:openrouter'>";

   echo "<div class='center' id='tabsbody'>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th colspan='2'>" . __('OpenRouter Settings', 'openrouter') . "</th></tr>";

   // API Key
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('OpenRouter API Key', 'openrouter') . "</td>";
   echo "<td><input type='text' name='openrouter_api_key' value='" . ($config['openrouter_api_key'] ?? '') . "'></td>";
   echo "</tr>";

   // Model Name
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('OpenRouter Model Name', 'openrouter') . "</td>";
   echo "<td>";
   echo "<select name='openrouter_model_name'>";
   foreach ($models as $model) {
       $selected = ($model['id'] == ($config['openrouter_model_name'] ?? '')) ? 'selected' : '';
       echo "<option value='" . $model['id'] . "' " . $selected . ">" . $model['name'] . "</option>";
   }
   echo "</select>";
   echo "</td>";
   echo "</tr>";

   // System Prompt
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('OpenRouter System Prompt', 'openrouter') . "</td>";
   echo "<td><textarea name='openrouter_system_prompt'>" . ($config['openrouter_system_prompt'] ?? '') . "</textarea></td>";
   echo "</tr>";

   // Bot User ID
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Bot User ID', 'openrouter') . "</td>";
   echo "<td><input type='number' name='openrouter_bot_user_id' value='" . ($config['openrouter_bot_user_id'] ?? 0) . "'></td>";
   echo "</tr>";

   // Save Button
   echo "<tr class='tab_bg_1'>";
   echo "<td colspan='2' class='center'>";
   echo "<input type='submit' name='update' class='submit' value='" . _sx('button', 'Save') . "'>";
   echo "</td>";
   echo "</tr>";

   echo "</table>";
   echo "</div>";
   Html::closeForm();
}

Html::footer();
