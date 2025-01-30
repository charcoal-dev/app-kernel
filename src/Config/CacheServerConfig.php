<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

/**
 * Class CacheConfig
 * @package Charcoal\App\Kernel\Config
 */
readonly class CacheServerConfig
{
    public function __construct(
        public CacheDriver $driver,
        public string      $hostname,
        public int         $port,
        public int         $timeOut,
    )
    {
    }
}