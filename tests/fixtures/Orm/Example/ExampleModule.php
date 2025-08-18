<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Orm\Example;

use Charcoal\App\Kernel\Contracts\Orm\Module\CacheStoreAwareInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\App\Kernel\Orm\Module\OrmModuleBase;
use Charcoal\App\Kernel\Orm\Module\CacheStoreAwareTrait;
use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\Cache\CacheClient;
use Charcoal\Cipher\Cipher;
use Charcoal\Semaphore\Filesystem\FilesystemSemaphore;
use Charcoal\Tests\App\Fixtures\Enums\CacheStore;
use Charcoal\Tests\App\Sandbox\TestApp\TestApp;

/**
 * Class ExampleModule
 * @package Charcoal\Tests\App\Fixtures\Orm\Example
 */
final class ExampleModule extends OrmModuleBase implements CacheStoreAwareInterface
{
    use CacheStoreAwareTrait;

    public ExampleRepository $repository;

    public function __construct(TestApp $app)
    {
        parent::__construct($app);

        $this->repository = new ExampleRepository($this);
    }

    protected function collectSerializableData(): array
    {
        $data = parent::collectSerializableData();
        $data["repository"] = $this->repository;
        return $data;
    }

    public function normalizeStorageKey(string $key): string
    {
        return strtolower(trim($key));
    }

    public function getCacheStore(): ?CacheClient
    {
        return null;
    }

    protected function declareDatabaseTables(TableRegistry $tables): void
    {
        $tables->register(new ExampleTableBase($this));
    }

    public function getCipherFor(OrmRepositoryBase $resolveFor): ?Cipher
    {
        return null;
    }

    public function getSemaphore(): FilesystemSemaphore
    {
        throw new \Exception("Not implemented");
    }

    public function declareCacheStoreEnum(): ?CacheStore
    {
        return CacheStore::Secondary;
    }
}