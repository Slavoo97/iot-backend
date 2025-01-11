<?php

declare(strict_types=1);

namespace App;

use Contributte\Bootstrap\ExtraConfigurator;


class Bootstrap
{
    public static function boot(): ExtraConfigurator
    {
        $configurator = new ExtraConfigurator();
        $appDir = dirname(__DIR__);

        //$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
        // According to NETTE_DEBUG env
        $configurator->setEnvDebugMode();

        $configurator->enableTracy(__DIR__ . '/../log');

        $configurator->setTimeZone('UTC');
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->addConfig(__DIR__ . '/../config/config.neon');

        if (file_exists(__DIR__ . '/../config/config.local.neon')) {
            $configurator->addConfig(__DIR__ . '/../config/config.local.neon');
        }

        return $configurator;
    }
}

//return $configurator->createContainer();
