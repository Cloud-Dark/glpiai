<?php

namespace GlpiPlugin\Openrouter;

use CommonGLPI;
use Config as GlpiConfig;
use Session;

class Config extends GlpiConfig
{
    static function getTypeName($nb = 0)
    {
        return __('OpenRouter', 'openrouter');
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
