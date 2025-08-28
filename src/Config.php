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
