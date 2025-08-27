<?php

namespace GlpiPlugin\Openrouter;

use CommonGLPI;
use Config as GlpiConfig;
use Session;

class Config extends GlpiConfig
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('OpenRouter', 'openrouter');
    }

    static function getTypeName($nb = 0)
    {
        return __('OpenRouter', 'openrouter');
    }

    static function getConfig()
    {
        return GlpiConfig::getConfigurationValues('plugin:openrouter');
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if ($item->getType() != 'Config') {
            return;
        }

        if (!self::canView()) {
            return false;
        }

        $current_config = self::getConfig();
        $canedit        = Session::haveRight(self::$rightname, UPDATE);
        $models         = self::getModels();

        if ($canedit) {
            echo "<form name='form' action='" . \Config::getFormURL() . "' method='post'>";
            echo "<input type='hidden' name='config_class' value='GlpiPlugin\Openrouter\Config'>";
            echo "<input type='hidden' name='config_context' value='plugin:openrouter'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='2'>" . __('OpenRouter Settings', 'openrouter') . "</th></tr>";

            // API Key
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('OpenRouter API Key', 'openrouter') . "</td>";
            echo "<td><input type='text' name='openrouter_api_key' value='" . ($current_config['openrouter_api_key'] ?? '') . "'></td>";
            echo "</tr>";

            // Model Name
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('OpenRouter Model Name', 'openrouter') . "</td>";
            echo "<td>";
            echo "<select name='openrouter_model_name'>";
            foreach ($models as $model) {
                $selected = ($model['id'] == ($current_config['openrouter_model_name'] ?? '')) ? 'selected' : '';
                echo "<option value='" . $model['id'] . "' " . $selected . ">" . $model['name'] . "</option>";
            }
            echo "</select>";
            echo "</td>";
            echo "</tr>";

            // System Prompt
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('OpenRouter System Prompt', 'openrouter') . "</td>";
            echo "<td><textarea name='openrouter_system_prompt'>" . ($current_config['openrouter_system_prompt'] ?? '') . "</textarea></td>";
            echo "</tr>";

            // Bot User ID
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Bot User ID', 'openrouter') . "</td>";
            echo "<td><input type='number' name='openrouter_bot_user_id' value='" . ($current_config['openrouter_bot_user_id'] ?? 0) . "'></td>";
            echo "</tr>";

            // Save Button
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2' class='center'>";
            echo "<input type='submit' name='update' class='submit' value='" . _sx('button', 'Save') . "'>";
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            \Html::closeForm();
        }

        return true;
    }

    static function getModels()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/models");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);
        if (isset($response['data'])) {
            return $response['data'];
        }
        return [];
    }
}
