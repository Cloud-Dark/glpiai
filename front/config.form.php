<?php

include ("../../../inc/includes.php");

use Glpi\Plugin\Hooks;
use GlpiPlugin\Openrouter\Config;


$plugin_config = new Config();
$config = Config::getConfig();

if (isset($_POST['update'])) {
   $plugin_config->setConfig($_POST);
   Html::redirect($_SERVER['REQUEST_URI']);
}

Html::header(
   __('AI Assistant', 'openrouter'),
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
   echo "<tr><th colspan='2'>" . __('AI Assistant Settings', 'openrouter') . "</th></tr>";

   // Provider Selection
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('AI Provider', 'openrouter') . "</td>";
   echo "<td>";
   echo "<select name='provider' id='provider_select'>";
   echo "<option value='openrouter' " . (($config['provider'] ?? 'openrouter') === 'openrouter' ? 'selected' : '') . ">OpenRouter</option>";
   echo "<option value='ollama' " . (($config['provider'] ?? '') === 'ollama' ? 'selected' : '') . ">Ollama</option>";
   echo "<option value='gemini' " . (($config['provider'] ?? '') === 'gemini' ? 'selected' : '') . ">Google Gemini</option>";
   echo "</select>";
   echo "</td>";
   echo "</tr>";

   // OpenRouter Settings (show if selected)
   echo "<tbody id='openrouter_settings' style='display:" . ((($config['provider'] ?? 'openrouter') === 'openrouter') ? 'table-row-group' : 'none') . ";'>";
   // OpenRouter API Key
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('OpenRouter API Key', 'openrouter') . "</td>";
   echo "<td><input type='text' name='openrouter_api_key' value='" . ($config['openrouter_api_key'] ?? '') . "'></td>";
   echo "</tr>";

   // OpenRouter Model Name
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('OpenRouter Model Name', 'openrouter') . "</td>";
   echo "<td>";
   echo "<select name='openrouter_model_name' id='openrouter_model_name'>";
   echo "<option value=''>" . __('Loading...') . "</option>";
   // We'll populate this with JavaScript
   echo "</select>";
   echo "</td>";
   echo "</tr>";
   echo "</tbody>";

   // Ollama Settings (show if selected)
   echo "<tbody id='ollama_settings' style='display:" . ((($config['provider'] ?? 'openrouter') === 'ollama') ? 'table-row-group' : 'none') . ";'>";
   // Ollama API URL
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Ollama API URL', 'openrouter') . "</td>";
   echo "<td><input type='text' name='ollama_api_url' value='" . ($config['ollama_api_url'] ?? 'http://localhost:11434') . "'></td>";
   echo "</tr>";

   // Ollama API Key (optional)
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Ollama API Key (optional)', 'openrouter') . "</td>";
   echo "<td><input type='password' name='ollama_api_key' value='" . ($config['ollama_api_key'] ?? '') . "'></td>";
   echo "</tr>";

   // Ollama Model Name
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Ollama Model Name', 'openrouter') . "</td>";
   echo "<td>";
   echo "<select name='ollama_model_name' id='ollama_model_name'>";
   echo "<option value=''>" . __('Loading...') . "</option>";
   // We'll populate this with JavaScript
   echo "</select>";
   echo "</td>";
   echo "</tr>";
   echo "</tbody>";

   // Gemini Settings (show if selected)
   echo "<tbody id='gemini_settings' style='display:" . ((($config['provider'] ?? 'openrouter') === 'gemini') ? 'table-row-group' : 'none') . ";'>";
   // Gemini API Key
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Gemini API Key', 'openrouter') . "</td>";
   echo "<td><input type='password' name='gemini_api_key' value='" . ($config['gemini_api_key'] ?? '') . "'></td>";
   echo "</tr>";

   // Gemini Model Name
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Gemini Model Name', 'openrouter') . "</td>";
   echo "<td>";
   echo "<select name='gemini_model_name' id='gemini_model_name'>";
   echo "<option value=''>" . __('Loading...') . "</option>";
   // We'll populate this with JavaScript
   echo "</select>";
   echo "</td>";
   echo "</tr>";
   echo "</tbody>";

   // Global System Prompt (used by all providers)
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('System Prompt (for all providers)', 'openrouter') . "</td>";
   echo "<td><textarea name='global_system_prompt'>" . ($config['global_system_prompt'] ?? $config['openrouter_system_prompt'] ?? '') . "</textarea></td>";
   echo "</tr>";

   // Global Bot User ID (used by all providers)
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Bot User ID (for all providers)', 'openrouter') . "</td>";
   echo "<td><input type='number' name='global_bot_user_id' value='" . ($config['global_bot_user_id'] ?? $config['openrouter_bot_user_id'] ?? 0) . "'></td>";
   echo "</tr>";

   // Global Max Api Usage Per Day (for providers that support usage limits)
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Max API usage per day (global)', 'openrouter') . "</td>";
   echo "<td><input type='number' name='global_max_api_usage_count' value='" . ($config['global_max_api_usage_count'] ?? $config['openrouter_max_api_usage_count'] ?? 50) . "'></td>";
   echo "</tr>";

   if (!empty($config['global_api_reset_day']) || !empty($config['openrouter_api_reset_day'])) {
        $value = $config['global_api_reset_day'] ?? $config['openrouter_api_reset_day'];
    } else {
        // Default to current date/time
        $value = (new DateTime())->format('Y-m-d\TH:i');
    }

   // Global Reset Day
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('API Usage Reset Time (global)', 'openrouter') . "</td>";
   echo "<td><input type='datetime-local' name='global_api_reset_day' value='" . htmlspecialchars($value) . "'></td>";
   echo "</tr>";
   
   // Current Usage Count (read-only)
   echo "<tr class='tab_bg_1'>";
   echo "<td>" . __('Current API Usage Count (global)', 'openrouter') . "</td>";
   echo "<td><span>" . ($config['global_api_usage_count'] ?? $config['openrouter_api_usage_count'] ?? 0) . "</span></td>";
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider_select');
    const openrouterSettings = document.getElementById('openrouter_settings');
    const ollamaSettings = document.getElementById('ollama_settings');
    const geminiSettings = document.getElementById('gemini_settings');
    const openrouterModelSelect = document.getElementById('openrouter_model_name');
    const ollamaModelSelect = document.getElementById('ollama_model_name');
    const geminiModelSelect = document.getElementById('gemini_model_name');
    
    // Function to show/hide provider settings based on selection
    function toggleProviderSettings() {
        const selectedProvider = providerSelect.value;
        
        // Hide all provider settings
        openrouterSettings.style.display = 'none';
        ollamaSettings.style.display = 'none';
        geminiSettings.style.display = 'none';
        
        // Show settings for selected provider
        if (selectedProvider === 'openrouter') {
            openrouterSettings.style.display = 'table-row-group';
            loadOpenRouterModels();
        } else if (selectedProvider === 'ollama') {
            ollamaSettings.style.display = 'table-row-group';
            loadOllamaModels();
        } else if (selectedProvider === 'gemini') {
            geminiSettings.style.display = 'table-row-group';
            loadGeminiModels();
        }
    }
    
    // Load OpenRouter models
    function loadOpenRouterModels() {
        openrouterModelSelect.innerHTML = '<option value=""><?php echo __('Loading...'); ?></option>';
        const currentModel = '<?php echo $config['openrouter_model_name'] ?? ''; ?>';
        
        fetch('../ajax/get_models.php?provider=openrouter')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(models => {
                openrouterModelSelect.innerHTML = '<option value=""><?php echo __('Select a model'); ?></option>';
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.textContent = model.name || model.id;
                    if (model.id === currentModel) {
                        option.selected = true;
                    }
                    openrouterModelSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching OpenRouter models:', error);
                openrouterModelSelect.innerHTML = '<option value=""><?php echo __('Error loading models'); ?></option>';
            });
    }
    
    // Load Ollama models
    function loadOllamaModels() {
        ollamaModelSelect.innerHTML = '<option value=""><?php echo __('Loading...'); ?></option>';
        const currentModel = '<?php echo $config['ollama_model_name'] ?? ''; ?>';
        
        fetch('../ajax/get_models.php?provider=ollama')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(models => {
                ollamaModelSelect.innerHTML = '<option value=""><?php echo __('Select a model'); ?></option>';
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.textContent = model.name || model.id;
                    if (model.id === currentModel) {
                        option.selected = true;
                    }
                    ollamaModelSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching Ollama models:', error);
                ollamaModelSelect.innerHTML = '<option value=""><?php echo __('Error loading models'); ?></option>';
            });
    }
    
    // Load Gemini models
    function loadGeminiModels() {
        geminiModelSelect.innerHTML = '<option value=""><?php echo __('Loading...'); ?></option>';
        const currentModel = '<?php echo $config['gemini_model_name'] ?? ''; ?>';
        
        fetch('../ajax/get_models.php?provider=gemini')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(models => {
                geminiModelSelect.innerHTML = '<option value=""><?php echo __('Select a model'); ?></option>';
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.textContent = model.name || model.id;
                    if (model.id === currentModel) {
                        option.selected = true;
                    }
                    geminiModelSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching Gemini models:', error);
                geminiModelSelect.innerHTML = '<option value=""><?php echo __('Error loading models'); ?></option>';
            });
    }
    
    // Event listener for provider selection change
    providerSelect.addEventListener('change', toggleProviderSettings);
    
    // Initial call to show settings for currently selected provider
    toggleProviderSettings();
});
</script>

<?php
Html::footer();
