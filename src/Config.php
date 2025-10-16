<?php

namespace GlpiPlugin\Openrouter;

use CommonGLPI;
use Config as GlpiConfig;
use Session;

class Config extends GlpiConfig
{
    static function getTypeName($nb = 0)
    {
        // Return plugin name that works for all providers
        return __('AI Assistant', 'openrouter');
    }

    static function getConfig()
    {
        return GlpiConfig::getConfigurationValues('plugin:openrouter');
    }

    static function setConfig($values)
    {
        return GlpiConfig::setConfigurationValues('plugin:openrouter',$values);
    }

}
