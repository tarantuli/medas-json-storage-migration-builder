<?php

declare(strict_types=1);

use Medas\JsonStorageMigrationBuilder\JsonStorageMigrationBuilderPackage;
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        JsonStorageMigrationBuilderPackage::instance(),
    ]);

    return $config;
});
