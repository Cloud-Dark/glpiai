<?php

namespace GlpiPlugin\Openrouter;

use CommonGLPI;
use Session;
use Glpi\Application\View\TemplateRenderer;

class Config extends \Config
{

    static function getTypeName($nb = 0)
    {
        return __('OpenRouter', 'openrouter');
    }

    static function getConfig()
    {
        return \Config::getConfigurationValues('plugin:openrouter');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case \Config::class:
                return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        switch ($item->getType()) {
            case \Config::class:
                self::showForConfig($item);
                break;
        }

        return true;
    }

    static function showForConfig(\Config $config) {
        global $CFG_GLPI;

        if (!self::canView()) {
            return false;
        }

        $current_config = self::getConfig();
        $canedit        = Session::haveRight(self::$rightname, UPDATE);

        TemplateRenderer::getInstance()->display('@openrouter/config.html.twig', [
            'current_config' => $current_config,
            'can_edit'       => $canedit
        ]);
    }
}
