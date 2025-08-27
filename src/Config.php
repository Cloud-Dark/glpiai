<?php

namespace GlpiPlugin\Openrouter;

use CommonGLPI;
use Config as GlpiConfig;
use Glpi\Application\View\TemplateRenderer;
use Session;

class Config extends GlpiConfig
{
    static function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Config') {
            return self::getTypeName();
        }
        return '';
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

        TemplateRenderer::getInstance()->display('@openrouter/config.html.twig', [
            'current_config' => $current_config,
            'can_edit'       => $canedit,
            'models'         => $models
        ]);

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
