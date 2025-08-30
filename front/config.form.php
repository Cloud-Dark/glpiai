<?php

include ("../../../inc/includes.php");

use Glpi\Plugin\Hooks;
use GlpiPlugin\Openrouter\Config;


$plugin_config = new Config();
$config = Config::getConfig();

if (isset($_POST['update'])) {
    // Convert reset day to DateTime object if provided
    if (!empty($_POST['openrouter_api_reset_day'])) {
        $resetDay = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['openrouter_api_reset_day']);
        if ($resetDay) {
            $_POST['openrouter_api_reset_day'] = $resetDay;
        }
    }
   $plugin_config->setConfig($_POST);
   Html::redirect($_SERVER['REQUEST_URI']);
}

Html::header(
   __('OpenRouter', 'openrouter'),
   $_SERVER['PHP_SELF'],
   'config',
   'plugins',
   'openrouter'
);

$canedit = Session::haveRight('config', UPDATE);

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
   echo "<select name='openrouter_model_name' id='openrouter_model_name'>";
   echo "<option value=''>" . __('Loading...') . "</option>";
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

   // Max Api Usage Per Day
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Max api usage per day', 'openrouter') . "</td>";
   echo "<td><input type='number' name='openrouter_max_api_usage_count' value='" . ($config['openrouter_max_api_usage_count'] ?? 50) . "'></td>";
   echo "</tr>";
   // Reset Day
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('API Usage Reset Time', 'openrouter') . "</td>";
   echo "<td><input type='datetime-local' name='openrouter_api_reset_day' value='" . ($config['openrouter_api_reset_day'] ?? new DateTime()->format('Y-m-d\TH:i')) . "'></td>";
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
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modelSelect = document.getElementById('openrouter_model_name');
    const currentModel = '<?php echo $config['openrouter_model_name'] ?? ''; ?>';

    fetch('../ajax/get_models.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(models => {
            modelSelect.innerHTML = '<option value=""><?php echo __('Select a model'); ?></option>';
            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model.id;
                option.textContent = model.name;
                if (model.id === currentModel) {
                    option.selected = true;
                }
                modelSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching models:', error);
            modelSelect.innerHTML = '<option value=""><?php echo __('Error loading models'); ?></option>';
        });
});
</script>

<?php
Html::footer();
