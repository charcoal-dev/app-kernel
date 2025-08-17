<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Cache\CacheDriver;

/**
 * Class CacheStoreConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class CacheStoreConfig
{
    public function __construct(
        public CacheDriver $driver,
        public string      $hostname,
        public int         $port,
        public int         $timeout,
    )
    {
    }
}