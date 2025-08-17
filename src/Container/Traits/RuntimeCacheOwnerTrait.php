<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Container\Traits;

use Charcoal\App\Kernel\Cache\Runtime\RuntimeCache;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;

/**
 * Trait RuntimeCacheOwnerTrait
 * @package Charcoal\App\Kernel\Container\Traits
 */
trait RuntimeCacheOwnerTrait
{
    public readonly RuntimeCache $runtimeMemory;

    public function initializePrivateRuntimeCache(): true
    {
        /** @var RuntimeCacheOwnerInterface $this */
        $this->runtimeMemory = new RuntimeCache($this);
        return true;
    }

    public function getRuntimeMemory(): RuntimeCache
    {
        return $this->runtimeMemory;
    }
}