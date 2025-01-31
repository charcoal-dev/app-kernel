<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

/**
 * Trait CacheableEntityTrait
 * @package Charcoal\App\Kernel\Orm\Entity
 */
trait CacheableEntityTrait
{
    /**
     * Default behaviour implementing CacheableEntityInterface interface, allowing it to be overridden
     * @return $this
     */
    public function returnCacheableObject(): static
    {
        return $this->cloneEntity();
    }

    /**
     * @return $this
     */
    protected function cloneEntity(): static
    {
        return clone $this;
    }
}