<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

/**
 * Interface CacheableEntityInterface
 * @package Charcoal\App\Kernel\Orm\Entity
 */
interface CacheableEntityInterface
{
    public function returnCacheableObject(): static;
}