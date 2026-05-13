<?php

declare(strict_types=1);

namespace Medas\JsonStorageMigrationBuilder\DeleteStore;

use Medas\Core\Attributes\Service;
use Medas\JsonStorage\Actions\DeleteStore\DeleteStore;
use Medas\JsonStorage\IO\PathBuilder;
use Medas\JsonStorage\StorageFile;
use Medas\StorageManager\{Interfaces\Store, UnitOfWork\ActionSet};

#[Service]
readonly class DeleteStoreBuilder
{
    public function __construct(
        private PathBuilder $pathBuilder,
    )
    {
    }

    public function build(Store $store): ActionSet
    {
        /** @var StorageFile $store */
        $path = $this->pathBuilder->build($store->storage(), $store->name());

        return ActionSet::fromAction(new DeleteStore($store, $path));
    }
}
