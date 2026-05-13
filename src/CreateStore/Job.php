<?php

declare(strict_types=1);

namespace Medas\JsonStorageMigrationBuilder\CreateStore;

use Medas\JsonStorage\StorageDirectory;
use Medas\MigrationBuilder\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

class Job
{
    public ActionSet $actionSet;

    public function __construct(
        public readonly StorageDirectory $directory,
        public readonly Blueprint        $blueprint,
    )
    {
        $this->actionSet = new ActionSet();
    }
}
