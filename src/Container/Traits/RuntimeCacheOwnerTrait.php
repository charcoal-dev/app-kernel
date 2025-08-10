<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container\Traits;

use Charcoal\App\Kernel\Cache\RuntimeCache;
use Charcoal\App\Kernel\Container\AppAwareContainer;
use Charcoal\App\Kernel\Contracts\Container\RuntimeCacheOwnerInterface;

/**
 * Trait RuntimeCacheOwnerTrait
 * @package Charcoal\App\Kernel\Contracts\Container
 * @mixin AppAwareContainer
 */
trait RuntimeCacheOwnerTrait
{
    public readonly RuntimeCache $memoryCache;

    abstract public function normalizeStorageKey(string $key): string;

    public function initializePrivateRuntimeCache(): true
    {
        /** @var RuntimeCacheOwnerInterface $this */
        $this->memoryCache = new RuntimeCache($this);
        return true;
    }
}