<?php

declare(strict_types=1);

namespace Medas\JsonStorageMigrationBuilder\CreateStore;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\Json\{JsonEncoder, Settings};
use Medas\JsonStorage\Actions\CreateStore\CreateStore;
use Medas\JsonStorage\IO\PathBuilder;
use Medas\JsonStorage\StorageDirectory;
use Medas\MigrationBuilder\{OriginalClassStorageStrategyActionBuilder, Structure\Blueprint};
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\DefaultStrategy;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Type;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class CreateStoreBuilder
{
    public function __construct(
        private JsonEncoder                               $encoder,

        #[ConfigValue(DefaultStrategy::class)]
        private OriginalClassStorageStrategy              $originalClassStorageStrategy,
        private OriginalClassStorageStrategyActionBuilder $actionBuilder,
        private PathBuilder                               $pathBuilder,
    )
    {
    }

    public function build(Storage $storage, Blueprint $blueprint): ActionSet
    {
        /** @var StorageDirectory $storage */
        $job = new Job($storage, $blueprint);

        $this->addBasicStore($job);
        $this->handleOriginalEntityType($job);

        return $job->actionSet;
    }

    private function addBasicStore(Job $job): void
    {
        $path = $this->pathBuilder->build($job->directory, $job->blueprint->name);

        $job->actionSet[] = new CreateStore(
            $job->directory->name(),
            $path,
            $this->encoder->encode($this->createContent($job->blueprint), new Settings(true))
        );
    }

    private function createContent(Blueprint $blueprint): array
    {
        $fieldNames = [];
        $defaults = [];

        foreach ($blueprint->fields as $field) {
            if ($field->store === $blueprint->name) {
                $fieldNames[] = $field->name;
                $defaults[$field->name] = $field->type === Type::Collection ? [] : $field->default;
            }
        }

        return [
            'keyName' => $blueprint->primaryIndex()?->fields()[0]?->name,
            'fieldNames' => $fieldNames,
            'defaults' => $defaults,
            'data' => [],
        ];
    }

    private function handleOriginalEntityType(Job $job): void
    {
        if (!$job->blueprint->storeOriginalClass) {
            return;
        }

        if ($job->blueprint->storeRequestingOriginalClassStorage !== $job->blueprint->name) {
            return;
        }

        $queries = $this->actionBuilder->build(
            $this->originalClassStorageStrategy,
            $job->blueprint,
            $job->directory
        );

        foreach ($queries as $query) {
            $job->actionSet[] = $query;
        }
    }
}
