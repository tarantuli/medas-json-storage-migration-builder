<?php

declare(strict_types=1);

namespace Medas\JsonStorageMigrationBuilder;

use Medas\Core\Attributes\Service;
use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\Json\{JsonEncoder, Settings};
use Medas\JsonStorage\Actions\CreateStore\CreateStore;
use Medas\JsonStorage\IO\PathBuilder;
use Medas\JsonStorage\StorageDirectory;
use Medas\MigrationBuilder\{MigrationBuilder as MigrationBuilderAlias, Structure\Blueprint};
use Medas\StorageManager\{Interfaces\Storage, UnitOfWork\ActionSet};

#[Service]
readonly class MigrationBuilder implements MigrationBuilderAlias
{
    public function __construct(
        private CreateStore\CreateStoreBuilder $createStoreBuilder,
        private JsonEncoder                    $encoder,
        private PathBuilder                    $pathBuilder,
    )
    {
    }

    public function build(
        Storage          $storage,
        Blueprint        $expectedStructure,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod,
        bool             $ignoreExistingStructure = false,
    ): bool
    {
        $path = $this->pathBuilder->build($storage, $expectedStructure->name);

        if (!$ignoreExistingStructure && file_exists($path)) {
            return false;
        }

        $actionClass = CreateStore::class;
        $actions = $this->buildActions($storage, $expectedStructure);

        foreach ($actions as $action) {
            /** @var CreateStore $action */
            $storageName = addcslashes($storage->name(), '"');
            $path = addcslashes($action->path, '"\\');
            $content = $this->encoder->encode($action->content, new Settings(true));
            $migrateMethod->body .= <<<PHP
\$unitOfWork->addAction(new \\$actionClass(
    "$storageName",
    "$path",
    $content
));
PHP;
        }

        return true;
    }

    public function buildActions(
        Storage   $storage,
        Blueprint $blueprint,
        bool      $ignoreExistingStructure = false,
    ): ActionSet
    {
        /** @var StorageDirectory $storage */
        return $this->createStoreBuilder->build($storage, $blueprint);
    }

    public function handles(Storage $storage): bool
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $storage instanceof StorageDirectory;
    }
}
