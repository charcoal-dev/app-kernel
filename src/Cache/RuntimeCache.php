<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\Contracts\Container\RuntimeCacheOwnerInterface;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Base\Traits\NotSerializableTrait;

/**
 * Class RuntimeCache
 * @package Charcoal\App\Kernel\Cache
 */
final class RuntimeCache
{
    private array $storage = [];

    use NotSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    /**
     * @param RuntimeCacheOwnerInterface $owner
     */
    public function __construct(private readonly RuntimeCacheOwnerInterface $owner)
    {
    }

    /**
     * Normalizes the storage item identifier
     * @param string $key
     * @return string
     */
    protected function normalizeStorageKey(string $key): string
    {
        return $this->owner->normalizeStorageKey($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function storeInMemory(string $key, mixed $value): void
    {
        $this->storage[$this->normalizeStorageKey($key)] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getFromMemory(string $key): mixed
    {
        return $this->storage[$this->normalizeStorageKey($key)] ?? null;
    }

    /**
     * @param string $key
     * @return void
     */
    public function deleteFromMemory(string $key): void
    {
        unset($this->storage[$this->normalizeStorageKey($key)]);
    }

    /**
     * Cleans the entire repository
     * @return void
     */
    public function purgeRuntimeMemory(): void
    {
        unset($this->storage);
        $this->storage = [];
    }
}