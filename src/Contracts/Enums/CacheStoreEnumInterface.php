<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

/**
 * Interface CacheStoreEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface CacheStoreEnumInterface
{
    public function getServerKey(): string;
}