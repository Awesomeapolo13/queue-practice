<?php

declare(strict_types=1);

namespace Alogachev\Homework\Config;

class ConfigService
{
    private const string BASE_CONFIG_PATH = __DIR__ . '/../../config';

    public function get(string $configFilePath): array
    {
        $resultPath = self::BASE_CONFIG_PATH . '/' . $configFilePath . '.php';
        if (!file_exists($resultPath)) {
            return [];
        }

        $config = include $resultPath;

        return is_array($config) ? $config : [];
    }
}
