<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Container;

/**
 * Interface RuntimeCacheOwnerInterface
 * @package Charcoal\App\Kernel\Contracts\Container
 */
interface RuntimeCacheOwnerInterface
{
    public function initializePrivateRuntimeCache(): true;

    public function normalizeStorageKey(string $key): string;
}