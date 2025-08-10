<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Entity;

/**
 * Interface CacheableEntityInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Entity
 */
interface CacheableEntityInterface
{
    public function getCacheableClone(): static;

    public function setCachedOn(int $timestamp): void;

    public function getCachedOn(): ?int;

    public function isFromCache(): bool;
}