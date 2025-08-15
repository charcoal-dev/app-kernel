<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Cache;

use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;
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
    public function store(string $key, mixed $value): void
    {
        $this->storage[$this->normalizeStorageKey($key)] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->storage[$this->normalizeStorageKey($key)] ?? null;
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->storage[$this->normalizeStorageKey($key)]);
    }

    /**
     * Cleans the entire repository
     * @return void
     */
    public function purge(): void
    {
        unset($this->storage);
        $this->storage = [];
    }
}