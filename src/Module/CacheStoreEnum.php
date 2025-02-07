<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Module;

/**
 * Interface CacheStoreEnum
 * @package Charcoal\App\Kernel\Module
 */
interface CacheStoreEnum
{
    public function getServerKey(): string;
}