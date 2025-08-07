<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

/**
 * Trait CacheableEntityTrait
 * @package Charcoal\App\Kernel\Orm\Entity
 */
trait CacheableEntityTrait
{
    protected ?int $entityCachedOn = null;

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
        return $this->entityCachedOn;
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return is_int($this->entityCachedOn) && $this->entityCachedOn > 0;
    }
}