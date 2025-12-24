<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

/**
 * Trait CacheableEntityTrait
 * @package Charcoal\App\Kernel\Orm\Entity
 */
trait CacheableEntityTrait
{
    protected int $entityCachedOn;

    /**
     * Default behavior implementing CacheableEntityInterface interface, allowing it to be overridden
     * @return $this
     */
    public function getCacheableClone(): static
    {
        $cacheable = $this->cloneForCache();
        $cacheable->setCachedOn(time());
        return $cacheable;
    }

    /**
     * @return $this
     */
    protected function cloneForCache(): static
    {
        return clone $this;
    }

    /**
     * @param int $timestamp
     * @return void
     */
    public function setCachedOn(int $timestamp): void
    {
        $this->entityCachedOn = $timestamp;
    }

    /**
     * @return int|null
     */
    public function getCachedOn(): ?int
    {
        return $this->entityCachedOn ?? null;
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return isset($this->entityCachedOn) && $this->entityCachedOn > 0;
    }
}