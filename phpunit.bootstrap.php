<?php

declare(strict_types=1);

use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\JsonStorageMigrationBuilder\JsonStorageMigrationBuilderPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig(ObjectInstantiator::class);

    $config->addPackages([
        JsonStorageMigrationBuilderPackage::instance(),
    ]);

    return $config;
});
